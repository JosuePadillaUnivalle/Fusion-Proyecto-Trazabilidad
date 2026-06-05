<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\Almacen;
use App\Services\RecepcionPlantaEnvioService;
use App\Support\AlmacenAmbito;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\PedidoCatalogo;
use App\Support\RutaPorCallesService;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $esTransportista = UsuarioRol::esTransportista($user);
        $filtroTransportista = (int) $request->query('transportista', 0);

        $query = Pedido::query()
            ->with(['detalles.insumo', 'envioAsignacion.transportista.perfilTransportista.vehiculo', 'envioAsignacion.asignadoPor'])
            ->orderByDesc('pedidoid');

        if ($esTransportista) {
            $query->whereHas('envioAsignacion', function ($q) use ($user) {
                $q->where('transportista_usuarioid', $user->usuarioid)
                    ->whereNotNull('transportista_usuarioid');
            });
        } else {
            if ($filtroTransportista > 0) {
                $query->whereHas('envioAsignacion', function ($q) use ($filtroTransportista) {
                    $q->where('transportista_usuarioid', $filtroTransportista);
                });
            }

            if ($request->filled('transportista_nombre')) {
                $nombre = $request->string('transportista_nombre')->trim()->toString();
                $query->whereHas('envioAsignacion.transportista', function ($q) use ($nombre) {
                    $q->where('nombre', 'like', "%{$nombre}%")
                        ->orWhere('apellido', 'like', "%{$nombre}%")
                        ->orWhere('nombreusuario', 'like', "%{$nombre}%")
                        ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$nombre}%"]);
                });
            }

            if ($request->boolean('sin_asignar')) {
                $query->where(function ($q) {
                    $q->whereDoesntHave('envioAsignacion')
                        ->orWhereHas('envioAsignacion', fn ($a) => $a->whereNull('transportista_usuarioid'));
                });
            }
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function ($w) use ($term) {
                $w->where('numero_solicitud', 'like', "%{$term}%")
                    ->orWhere('nombre_planta', 'like', "%{$term}%")
                    ->orWhere('direccion_texto', 'like', "%{$term}%")
                    ->orWhereHas('detalles', fn ($d) => $d->where('cultivo_personalizado', 'like', "%{$term}%"))
                    ->orWhereHas('envioAsignacion.transportista', function ($t) use ($term) {
                        $t->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('nombreusuario', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado')->toString());
        }

        if ($request->filled('desde')) {
            $query->whereDate('fechapedido', '>=', $request->string('desde')->toString());
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fechapedido', '<=', $request->string('hasta')->toString());
        }

        $pedidos = $query->get();

        $transportistas = $esTransportista
            ? collect()
            : Usuario::query()
                ->where('role', 'transportista')
                ->where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellido')
                ->get();

        $estadosPedido = PedidoCatalogo::opcionesEstadoEnSelector();

        return view('pedidos.index', compact(
            'pedidos',
            'transportistas',
            'filtroTransportista',
            'esTransportista',
            'estadosPedido'
        ));
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
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'origen_latitud' => 'required|numeric|between:-90,90',
            'origen_longitud' => 'required|numeric|between:-180,180',
            'origen_direccion' => 'nullable|string|max:255',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'direccion_texto' => 'nullable|string|max:255',
            'fechaEntregaDeseada' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_ref' => ['required', 'string', 'regex:/^(insumo|cosecha|cultivo):\d+$/'],
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string',
        ], [
            'detalles.*.producto_ref.regex' => 'Seleccione un producto válido de producción agrícola.',
        ]);

        DB::transaction(function () use ($data) {
            $detallesInput = $data['detalles'];
            unset($data['detalles']);

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

            EnvioAsignacionMultiple::firstOrCreate(
                ['externo_envio_id' => $pedido->numero_solicitud],
                EnvioAsignacionEstadoCatalogo::applyToAttributes([
                    'pedidoid' => $pedido->pedidoid,
                    'estado' => 'pendiente',
                ])
            );
        });

        return redirect()->route('pedidos.index')->with('success', 'Pedido registrado. Producción agrícola debe aceptarlo y reservar stock antes de asignar transportista.');
    }

    public function show($id)
    {
        $pedido = Pedido::with(['detalles', 'envioAsignacion.transportista.perfilTransportista.vehiculo.tipoVehiculo', 'envioAsignacion.asignadoPor', 'aceptadoPor'])->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
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

        return redirect()->route('pedidos.index');
    }

    public function asignarTransportista(Request $request, Pedido $pedido): RedirectResponse
    {
        $data = $request->validate([
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'vehiculoid' => ['required', 'integer', 'exists:vehiculo,vehiculoid'],
        ]);

        try {
            EnvioPedidoService::asignarTransportistaYVehiculo(
                $pedido,
                (int) $data['transportista_usuarioid'],
                (int) $data['vehiculoid'],
                (int) auth()->id(),
                false
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
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
}
