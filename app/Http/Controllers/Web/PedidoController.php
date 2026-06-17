<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Almacen;
use App\Services\CostoEnvioRutaService;
use App\Services\NotificacionUsuarioService;
use App\Services\RecepcionPlantaEnvioService;
use App\Support\AlmacenAmbito;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\EnvioListadoService;
use App\Support\PedidoCatalogo;
use App\Support\RutaPorCallesService;
use App\Support\UbicacionGpsParser;
use App\Support\UsuarioRol;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()?->can('asignaciones.view')) {
            return redirect()->route('logistica.asignaciones.listado', $request->query());
        }

        return view('logistica.envios.index', EnvioListadoService::prepararListado($request));
    }

    public function create()
    {
        AlmacenAmbito::asegurarAmbitosEnRegistros();

        $numeroSolicitud = PedidoCatalogo::generarNumeroSolicitud();

        $filtroAlmacenesAgricola = AlmacenAmbito::scope(
            Almacen::query()->where('activo', true),
            AlmacenAmbito::AGRICOLA
        )->orderBy('nombre')->get()->map(fn (Almacen $a) => [
            'value' => (string) $a->almacenid,
            'label' => $a->nombre,
        ])->values()->all();

        $filtroAlmacenesPlanta = AlmacenAmbito::scope(
            Almacen::query()->where('activo', true),
            AlmacenAmbito::PLANTA
        )->orderBy('nombre')->get()->map(fn (Almacen $a) => [
            'value' => (string) $a->almacenid,
            'label' => $a->nombre,
        ])->values()->all();

        return view('pedidos.create', [
            'numeroSolicitud' => $numeroSolicitud,
            'hubLat' => RutaPorCallesService::HUB_LAT,
            'hubLng' => RutaPorCallesService::HUB_LNG,
            'filtroAlmacenesAgricola' => $filtroAlmacenesAgricola,
            'filtroAlmacenesPlanta' => $filtroAlmacenesPlanta,
            'almacenesMapa' => $this->almacenesParaMapaEnvio(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function almacenesParaMapaEnvio(): array
    {
        $items = [];

        foreach ([AlmacenAmbito::AGRICOLA, AlmacenAmbito::PLANTA] as $ambito) {
            $almacenes = AlmacenAmbito::scope(
                Almacen::query()->where('activo', true),
                $ambito
            )->orderBy('nombre')->get();

            foreach ($almacenes as $almacen) {
                $resuelto = UbicacionGpsParser::resolverAlmacen(
                    (int) $almacen->almacenid,
                    $almacen->nombre,
                    $almacen->ubicacion
                );

                $items[] = [
                    'id' => $almacen->almacenid,
                    'label' => $almacen->nombre,
                    'extra' => [
                        'lat' => $resuelto['lat'],
                        'lng' => $resuelto['lng'],
                        'direccion' => $resuelto['direccion'],
                        'ambito' => $almacen->ambito ?? $ambito,
                    ],
                ];
            }
        }

        return $items;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'origen_latitud' => 'required|numeric|between:-90,90',
            'origen_longitud' => 'required|numeric|between:-180,180',
            'origen_direccion' => 'nullable|string|max:255',
            'origen_almacenid' => 'nullable|integer|exists:almacen,almacenid',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'direccion_texto' => 'nullable|string|max:255',
            'fechaEntregaDeseada' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'transportista_usuarioid' => 'required|integer|exists:usuario,usuarioid',
            'vehiculoid' => 'required|integer|exists:vehiculo,vehiculoid',
            'costo_bs' => 'required|integer|min:1|max:99999999',
            'recogidas' => 'nullable|array|max:5',
            'recogidas.*.latitud' => 'required|numeric|between:-90,90',
            'recogidas.*.longitud' => 'required|numeric|between:-180,180',
            'recogidas.*.direccion' => 'nullable|string|max:255',
            'recogidas.*.almacenid' => 'nullable|integer|exists:almacen,almacenid',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_ref' => ['required', 'string', 'regex:/^(insumo|cosecha|cultivo):\d+$/'],
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string',
        ], [
            'detalles.*.producto_ref.regex' => 'Seleccione un producto válido de producción agrícola.',
        ]);

        if (empty($data['fechaEntregaDeseada'])) {
            $data['fechaEntregaDeseada'] = now()->toDateString();
        }

        $erroresStock = PedidoCatalogo::validarStockDetallesPedido(
            $data['detalles'],
            isset($data['origen_almacenid']) ? (int) $data['origen_almacenid'] : null,
            array_values($data['recogidas'] ?? [])
        );
        if ($erroresStock !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages($erroresStock);
        }

        $transportistaId = (int) $data['transportista_usuarioid'];
        $vehiculoId = (int) $data['vehiculoid'];
        $costoBs = (float) $data['costo_bs'];
        $recogidasExtra = array_values($data['recogidas'] ?? []);

        $pedido = null;

        try {
            DB::transaction(function () use ($data, $transportistaId, $vehiculoId, $costoBs, $recogidasExtra, &$pedido) {
            $detallesInput = $data['detalles'];
            unset($data['detalles'], $data['transportista_usuarioid'], $data['vehiculoid'], $data['recogidas'], $data['costo_bs']);

            $pedido = Pedido::create([
                ...$data,
                'numero_solicitud' => PedidoCatalogo::generarNumeroSolicitud(),
                'nombre_planta' => null,
                'estado' => PedidoCatalogo::ESTADO_INICIAL,
                'fechapedido' => now(),
            ]);

            foreach ($detallesInput as $detalle) {
                $producto = PedidoCatalogo::resolverProductoPedido($detalle['producto_ref']);

                $pedido->detalles()->create([
                    'insumoid' => $producto['insumoid'],
                    'producto_ref' => $detalle['producto_ref'],
                    'produccionalmacenamientoid' => str_starts_with($detalle['producto_ref'], 'cosecha:')
                        ? (int) substr($detalle['producto_ref'], 8)
                        : null,
                    'cultivo_personalizado' => $producto['cultivo'],
                    'cantidad' => $detalle['cantidad'],
                    'observaciones' => $detalle['observaciones'] ?? null,
                ]);
            }

            $envio = EnvioPedidoService::programarTransportista(
                $pedido,
                $transportistaId,
                $vehiculoId,
                (int) auth()->id(),
                $costoBs
            );

            if ($recogidasExtra !== []) {
                EnvioPedidoService::crearRutaRecogidasMultiples(
                    $pedido,
                    $envio,
                    $recogidasExtra,
                    $transportistaId,
                    (int) auth()->id()
                );
            }
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors([
                'vehiculoid' => $e->getMessage(),
            ]);
        }

        app(NotificacionUsuarioService::class)->pedidoPendienteAgricola($pedido->fresh(['detalles']));

        $mensaje = 'Envío registrado con transportista asignado.';
        if ($recogidasExtra !== []) {
            $totalRecogidas = count($recogidasExtra) + 1;
            $mensaje .= " Ruta con {$totalRecogidas} punto(s) de recogida hacia planta.";
        } else {
            $mensaje .= ' Producción agrícola debe aceptarlo para activar la salida.';
        }

        return redirect()->route('logistica.asignaciones.listado')->with('success', $mensaje);
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'detalles',
            'envioAsignacion.transportista.perfilTransportista.vehiculo.tipoVehiculo',
            'envioAsignacion.asignadoPor',
            'envioAsignacion.ruta.paradas',
            'aceptadoPor',
        ])->findOrFail($id);

        $trayectoPartes = EnvioPedidoService::trayectoPartesPedido($pedido);
        if ($pedido->envioAsignacion) {
            $pedido->envioAsignacion->setRelation('pedido', $pedido);
            $paradasMapa = EnvioPedidoService::paradasMapaEnvio($pedido->envioAsignacion);
        } else {
            $paradasMapa = [];
        }

        return view('pedidos.show', compact('pedido', 'trayectoPartes', 'paradasMapa'));
    }

    public function edit(Pedido $pedido)
    {
        $pedido->load([
            'detalles',
            'envioAsignacion.transportista.perfilTransportista.vehiculo.tipoVehiculo',
            'envioAsignacion.asignadoPor',
        ]);

        $logistica = EnvioPedidoService::datosLogistica($pedido->envioAsignacion);
        $puedeAsignarLogistica = PedidoCatalogo::puedeAsignarTransportista($pedido);

        return view('pedidos.edit', compact('pedido', 'logistica', 'puedeAsignarLogistica'));
    }

    public function update(Request $request, Pedido $pedido)
    {
        if ($request->has('estado') && ! $request->has('fechaEntregaDeseada') && ! $request->has('observaciones')) {
            $data = $request->validate([
                'estado' => 'required|in:sin asignacion,pendiente,confirmado,en produccion,rechazado',
            ]);

            if (in_array($data['estado'], PedidoCatalogo::estadosListosParaLogistica(), true)
                && PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
                return back()->with('error', 'Solo producción agrícola puede aceptar el pedido y reservar stock del almacén.');
            }

            $pedido->update($data);

            return back()->with('success', 'Estado actualizado.');
        }

        $data = $request->validate([
            'nombre_planta' => 'nullable|string|max:255',
            'fechaEntregaDeseada' => 'nullable|date',
            'direccion_texto' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:3000',
            'estado' => 'required|in:sin asignacion,pendiente,confirmado,en produccion,rechazado',
            'transportista_usuarioid' => 'nullable|integer|exists:usuario,usuarioid',
            'vehiculoid' => 'nullable|integer|exists:vehiculo,vehiculoid',
        ]);

        if (in_array($data['estado'], PedidoCatalogo::estadosListosParaLogistica(), true)
            && PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
            return back()->with('error', 'Solo producción agrícola puede aceptar el pedido y reservar stock del almacén.');
        }

        $transportistaId = isset($data['transportista_usuarioid']) ? (int) $data['transportista_usuarioid'] : 0;
        $vehiculoId = isset($data['vehiculoid']) ? (int) $data['vehiculoid'] : 0;
        unset($data['transportista_usuarioid'], $data['vehiculoid']);

        $pedido->update($data);

        if ($transportistaId > 0 && $vehiculoId > 0 && PedidoCatalogo::puedeAsignarTransportista($pedido)) {
            try {
                EnvioPedidoService::asignarTransportistaYVehiculo(
                    $pedido,
                    $transportistaId,
                    $vehiculoId,
                    (int) auth()->id(),
                    true
                );
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        } elseif ($transportistaId > 0 xor $vehiculoId > 0) {
            return back()->withInput()->with('error', 'Debe seleccionar transportista y vehículo juntos.');
        }

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido actualizado correctamente.');
    }

    public function destroy($id)
    {
        Pedido::findOrFail($id)->delete();

        return redirect()->route('logistica.asignaciones.listado');
    }

    public function asignarTransportista(Request $request, Pedido $pedido): RedirectResponse
    {
        $data = $request->validate([
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'vehiculoid' => ['required', 'integer', 'exists:vehiculo,vehiculoid'],
            'costo_bs' => ['required', 'integer', 'min:1', 'max:99999999'],
        ]);

        $envio = null;

        try {
            $envio = EnvioPedidoService::asignarTransportistaYVehiculo(
                $pedido,
                (int) $data['transportista_usuarioid'],
                (int) $data['vehiculoid'],
                (int) auth()->id(),
                false,
                (float) $data['costo_bs']
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($envio) {
            app(NotificacionUsuarioService::class)->envioListoParaRecoger($envio->fresh(['pedido.detalles']));
        }

        $transportista = Usuario::find($data['transportista_usuarioid']);
        $vehiculo = \App\Models\Vehiculo::find($data['vehiculoid']);
        $nombre = trim(($transportista->nombre ?? '').' '.($transportista->apellido ?? ''));

        return back()->with('success', "Transportista {$nombre} asignado con vehículo {$vehiculo->placa} al pedido {$pedido->numero_solicitud}.");
    }

    public function confirmarCargaEnvio(Pedido $pedido): RedirectResponse
    {
        $envio = EnvioAsignacionMultiple::query()
            ->with('pedido')
            ->where(function ($q) use ($pedido) {
                $q->where('pedidoid', $pedido->pedidoid)
                    ->orWhere('externo_envio_id', $pedido->numero_solicitud);
            })
            ->first();

        if (! $envio) {
            return back()->with('error', 'Este pedido no tiene envío registrado.');
        }

        try {
            EnvioPedidoService::confirmarCargaHaciaPlanta($envio);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Carga confirmada. El envío está en camino hacia planta.');
    }

    public function confirmarLlegadaPlanta(Pedido $pedido, RecepcionPlantaEnvioService $recepcionService): RedirectResponse
    {
        try {
            $recepcionService->confirmarDesdePedido($pedido, auth()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Pedido {$pedido->numero_solicitud} recibido en planta. La carga se registró en el almacén de destino.");
    }

    public function calcularCostoEnvio(Request $request, CostoEnvioRutaService $costoEnvio): JsonResponse
    {
        $data = $request->validate([
            'paradas' => ['required', 'array', 'min:2'],
            'paradas.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'paradas.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'distancia_m' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json($costoEnvio->calcular(
            $data['paradas'],
            isset($data['distancia_m']) ? (float) $data['distancia_m'] : null
        ));
    }
}
