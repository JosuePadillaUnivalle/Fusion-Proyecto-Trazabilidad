<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Services\PedidoDistribucionMayoristaService;
use App\Services\RecepcionPuntoVentaService;
use App\Services\SimulacionRutaService;
use App\Support\AlmacenAmbito;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\PuntoVentaAccess;
use App\Support\SimulacionRutaCatalogo;
use App\Support\UbicacionGpsParser;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoDistribucionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = PuntoVentaAccess::scopePedidosDelUsuario(
            PedidoDistribucion::query()->with(['puntoVenta.minorista', 'detalles.insumo.unidadMedida', 'creadoPor', 'rutaDistribucion']),
            $user
        );

        if ($request->filled('estado_grupo')) {
            $query = PedidoDistribucionCatalogo::aplicarFiltroGrupoEstado(
                $query,
                $request->string('estado_grupo')->toString()
            );
        } elseif ($request->filled('estado')) {
            $query->where('estado', $request->string('estado')->toString());
        }

        if ($request->filled('puntoventaid')) {
            $query->where('puntoventaid', (int) $request->input('puntoventaid'));
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function ($w) use ($term) {
                $w->where('numero_solicitud', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%")
                    ->orWhereHas('puntoVenta', fn ($p) => $p->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('detalles', fn ($d) => $d->where('producto_nombre', 'like', "%{$term}%"));
            });
        }

        $pedidos = $query->orderByDesc('pedidodistribucionid')->get();

        $pendientes = $pedidos->filter(fn (PedidoDistribucion $p) => PedidoDistribucionCatalogo::pendienteAprobacionMayorista($p));
        $procesados = $pedidos->reject(fn (PedidoDistribucion $p) => PedidoDistribucionCatalogo::pendienteAprobacionMayorista($p));
        $enRutaTiempoReal = $pedidos->filter(fn (PedidoDistribucion $p) => PedidoDistribucionCatalogo::estaEnRutaTiempoReal($p));

        $puntosVenta = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->orderBy('nombre'),
            $user
        )->get();

        $filtroPdvId = $request->integer('puntoventaid') ?: null;
        $filtroPdvNombre = $filtroPdvId
            ? ($puntosVenta->firstWhere('puntoventaid', $filtroPdvId)?->nombre ?? '')
            : '';

        $puedeCrear = UsuarioRol::esMinorista($user) || UsuarioRol::esAdminGlobal($user);
        $puedeGestionarMayorista = UsuarioRol::puedeGestionarDistribucionMayorista($user);
        $esMinorista = UsuarioRol::esMinorista($user);

        return view('punto_venta.pedidos.index', compact(
            'pedidos',
            'pendientes',
            'procesados',
            'enRutaTiempoReal',
            'puntosVenta',
            'filtroPdvId',
            'filtroPdvNombre',
            'puedeCrear',
            'puedeGestionarMayorista',
            'esMinorista'
        ));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless(UsuarioRol::esMinorista($user) || UsuarioRol::esAdminGlobal($user), 403);

        AlmacenAmbito::asegurarAmbitosEnRegistros();

        $esMinorista = UsuarioRol::esMinorista($user);
        $esAdmin = UsuarioRol::esAdminGlobal($user);

        $puntosMinorista = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->with('minorista')->orderBy('nombre'),
            $user
        )->get();

        $oldPunto = old('puntoventaid') ? PuntoVenta::with('minorista')->find(old('puntoventaid')) : null;
        if ($oldPunto === null && $esMinorista && $puntosMinorista->count() === 1) {
            $oldPunto = $puntosMinorista->first();
        }
        $oldMinoristaId = old('minorista_usuarioid');
        $oldMinoristaLabel = '';
        if ($esAdmin) {
            if ($oldMinoristaId) {
                $minoristaUser = Usuario::find((int) $oldMinoristaId);
                $oldMinoristaLabel = $minoristaUser
                    ? trim($minoristaUser->nombre.' '.($minoristaUser->apellido ?? ''))
                    : '';
            } elseif ($oldPunto?->minorista) {
                $oldMinoristaId = (int) $oldPunto->usuarioid;
                $oldMinoristaLabel = trim($oldPunto->minorista->nombre.' '.($oldPunto->minorista->apellido ?? ''));
            }
        }
        $oldAlmacen = old('almacen_mayorista_origenid') ? Almacen::find(old('almacen_mayorista_origenid')) : null;
        $oldInsumo = old('insumoid') ? Insumo::with('unidadMedida', 'almacen')->find(old('insumoid')) : null;

        $puntosVentaMapa = $puntosMinorista->map(fn (PuntoVenta $pv) => [
            'id' => $pv->puntoventaid,
            'label' => $pv->nombre,
            'minorista_usuarioid' => (int) $pv->usuarioid,
            'lat' => $pv->latitud,
            'lng' => $pv->longitud,
            'resumen' => $pv->resumenUbicacion(),
            'direccion' => $pv->direccionParaMostrar(),
        ])->values()->all();

        return view('punto_venta.pedidos.create', [
            'numeroSolicitud' => PedidoDistribucionCatalogo::generarNumeroSolicitud(),
            'puntosMinorista' => $puntosMinorista,
            'puntosVentaMapa' => $puntosVentaMapa,
            'esMinorista' => $esMinorista,
            'esAdmin' => $esAdmin,
            'oldMinoristaId' => $oldMinoristaId,
            'oldMinoristaLabel' => $oldMinoristaLabel,
            'oldPuntoLabel' => $oldPunto?->nombre ?? '',
            'oldPuntoResumen' => $oldPunto?->resumenUbicacion() ?? '',
            'oldPuntoId' => $oldPunto?->puntoventaid,
            'oldAlmacenLabel' => $oldAlmacen?->nombre ?? '',
            'oldProductoLabel' => $oldInsumo
                ? $oldInsumo->nombre.' · Stock '.number_format((float) $oldInsumo->stock, 2).' '.($oldInsumo->unidadMedida?->abreviatura ?? '')
                : '',
            'oldProductoUnidad' => $oldInsumo?->unidadMedida?->abreviatura ?? $oldInsumo?->unidadMedida?->nombre ?? '',
            'oldProductoStock' => $oldInsumo ? (float) $oldInsumo->stock : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(UsuarioRol::esMinorista($user) || UsuarioRol::esAdminGlobal($user), 403);

        $esAdmin = UsuarioRol::esAdminGlobal($user);

        $data = $request->validate([
            'puntoventaid' => 'required|integer|exists:punto_venta,puntoventaid',
            'minorista_usuarioid' => [
                $esAdmin ? 'required' : 'nullable',
                'integer',
                'exists:usuario,usuarioid',
            ],
            'almacen_mayorista_origenid' => [
                $esAdmin ? 'required' : 'nullable',
                'integer',
                'exists:almacen,almacenid',
            ],
            'fecha_entrega_deseada' => 'nullable|date',
            'observaciones' => 'nullable|string|max:2000',
            'insumoid' => 'required|integer|exists:insumo,insumoid',
            'cantidad' => 'required|numeric|gt:0',
        ], [
            'puntoventaid.required' => 'Seleccione un punto de venta destino.',
            'minorista_usuarioid.required' => 'Seleccione el minorista destino.',
            'almacen_mayorista_origenid.required' => 'Seleccione el almacén mayorista de origen.',
            'insumoid.required' => 'Seleccione un producto del centro mayorista.',
            'cantidad.required' => 'Indique la cantidad solicitada.',
            'cantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'cantidad.numeric' => 'La cantidad debe ser un número válido.',
        ]);

        $punto = PuntoVenta::query()->findOrFail($data['puntoventaid']);
        if (UsuarioRol::esMinorista($user) && (int) $punto->usuarioid !== (int) $user->usuarioid) {
            return back()->withInput()->with('error', 'Solo puede solicitar productos para sus propios puntos de venta.');
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            if ((int) $punto->usuarioid !== (int) $data['minorista_usuarioid']) {
                return back()->withInput()->with('error', 'El punto de venta no pertenece al minorista seleccionado.');
            }
        }

        if (! $punto->activo) {
            return back()->withInput()->with('error', 'El punto de venta seleccionado está inactivo.');
        }

        if ((float) $data['cantidad'] <= 0) {
            return back()->withInput()->with('error', 'La cantidad debe ser mayor que cero.');
        }

        $insumo = Insumo::query()->with('almacen')->findOrFail($data['insumoid']);
        $almacenMayoristaId = (int) ($data['almacen_mayorista_origenid'] ?? $insumo->almacenid);
        $almacenMayorista = Almacen::query()->find($almacenMayoristaId);

        if ($almacenMayorista === null || $almacenMayorista->ambito !== AlmacenAmbito::MAYORISTA) {
            return back()->withInput()->with('error', 'El producto debe pertenecer a un almacén mayorista con stock disponible.');
        }

        if ((int) $insumo->almacenid !== $almacenMayoristaId) {
            return back()->withInput()->with('error', 'El producto seleccionado no corresponde al almacén mayorista indicado.');
        }

        if ((float) $data['cantidad'] > (float) $insumo->stock) {
            return back()->withInput()->with('error', 'La cantidad supera el stock disponible en el almacén mayorista.');
        }

        $pedido = DB::transaction(function () use ($data, $insumo, $almacenMayoristaId, $request) {
            $pedido = PedidoDistribucion::create([
                'numero_solicitud' => PedidoDistribucionCatalogo::generarNumeroSolicitud(),
                'puntoventaid' => (int) $data['puntoventaid'],
                'almacen_mayorista_origenid' => $almacenMayoristaId,
                'estado' => PedidoDistribucionCatalogo::ESTADO_PENDIENTE,
                'fechapedido' => now(),
                'fecha_entrega_deseada' => $data['fecha_entrega_deseada'] ?? now()->toDateString(),
                'observaciones' => $data['observaciones'] ?? null,
                'creado_por_usuarioid' => $request->user()->usuarioid,
            ]);

            DetallePedidoDistribucion::create([
                'pedidodistribucionid' => $pedido->pedidodistribucionid,
                'insumoid' => $insumo->insumoid,
                'producto_nombre' => $insumo->nombre,
                'cantidad' => (float) $data['cantidad'],
            ]);

            return $pedido;
        });

        return redirect()
            ->route('punto-venta.pedidos.show', $pedido)
            ->with('success', 'Solicitud enviada. El centro mayorista revisará el pedido y preparará el envío.');
    }

    public function show(PedidoDistribucion $pedido): View
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $pedido->load(['puntoVenta.minorista', 'detalles.insumo.unidadMedida', 'detalles.insumo.almacen', 'almacenMayoristaOrigen', 'aceptadoPor', 'creadoPor', 'transportista', 'vehiculo.tipoVehiculo', 'rutaDistribucion.transportista', 'rutaDistribucion.vehiculo']);

        $user = auth()->user();
        $puedeGestionarMayorista = UsuarioRol::puedeGestionarDistribucionMayorista($user);
        $esMinoristaDueño = UsuarioRol::esMinorista($user)
            && (int) $pedido->puntoVenta?->usuarioid === (int) auth()->id();
        $puedeAnunciarLlegada = $esMinoristaDueño
            && PedidoDistribucionCatalogo::puedeConfirmarRecepcion($pedido);

        $ruta = $pedido->rutaDistribucion;
        $pendienteMayorista = PedidoDistribucionCatalogo::pendienteAprobacionMayorista($pedido);
        $puedeDesignarTransportista = $puedeGestionarMayorista
            && PedidoDistribucionCatalogo::puedeDesignarTransportista($pedido);
        $transportistaDesignado = PedidoDistribucionCatalogo::tieneTransportistaDesignado($pedido);
        $puedeEmpezarRuta = $ruta !== null
            && SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($user, $ruta);
        $simulacionActiva = $ruta !== null
            && SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
        $esTransportistaAsignado = UsuarioRol::esTransportista($user)
            && (
                (int) $pedido->transportista_usuarioid === (int) $user->usuarioid
                || (int) $ruta?->transportista_usuarioid === (int) $user->usuarioid
            );
        $puedeEditarFlujo = PedidoDistribucionCatalogo::puedeEditarFlujoAntesDeRuta($pedido);
        $puedeEditarSolicitud = $puedeEditarFlujo
            && (UsuarioRol::esAdminGlobal($user) || $esMinoristaDueño);
        $puedeReabrirRevision = ($puedeGestionarMayorista ?? false)
            && PedidoDistribucionCatalogo::puedeReabrirRevision($pedido);
        $pasoActualFlujo = $pendienteMayorista ? 2 : (
            $puedeDesignarTransportista ? 3 : (
                ($transportistaDesignado && $pedido->estado === 'confirmado') ? 4 : (
                    $pedido->estado === 'en_transito' ? 4 : ($pedido->estado === 'recibido' ? 5 : 3)
                )
            )
        );
        $pasoInicial = max(1, min(5, (int) request()->query('paso', 0) ?: $pasoActualFlujo));
        if (! $puedeEditarFlujo && $pasoInicial < $pasoActualFlujo) {
            $pasoInicial = $pasoActualFlujo;
        }

        $erroresStock = $puedeGestionarMayorista
            ? app(PedidoDistribucionMayoristaService::class)->verificarDisponibilidad($pedido)
            : [];

        $esAdmin = UsuarioRol::esAdminGlobal($user);
        $puedeVerRutaTiempoReal = $esAdmin
            || UsuarioRol::esJefeMayorista($user)
            || UsuarioRol::esJefePlanta($user);
        $urlTiempoRealPedido = $simulacionActiva && $ruta
            ? route('logistica.rutas-tiempo-real.show', ['tipo' => 'distribucion', 'id' => $ruta->rutadistribucionid])
            : null;

        return view('punto_venta.pedidos.show', compact(
            'pedido',
            'puedeGestionarMayorista',
            'esMinoristaDueño',
            'puedeAnunciarLlegada',
            'erroresStock',
            'ruta',
            'puedeDesignarTransportista',
            'transportistaDesignado',
            'puedeEmpezarRuta',
            'simulacionActiva',
            'esTransportistaAsignado',
            'puedeEditarFlujo',
            'puedeEditarSolicitud',
            'puedeReabrirRevision',
            'pasoInicial',
            'pasoActualFlujo',
            'esAdmin',
            'puedeVerRutaTiempoReal',
            'urlTiempoRealPedido',
        ));
    }

    public function ubicacionPuntoVenta(Request $request, PedidoDistribucion $pedido): View
    {
        return $this->vistaUbicacionContexto($request, $pedido, 'punto-venta');
    }

    public function ubicacionAlmacen(Request $request, PedidoDistribucion $pedido): View
    {
        return $this->vistaUbicacionContexto($request, $pedido, 'almacen');
    }

    private function vistaUbicacionContexto(Request $request, PedidoDistribucion $pedido, string $tipo): View
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $pedido->load(['puntoVenta', 'almacenMayoristaOrigen', 'detalles.insumo.almacen']);

        $pasoRetorno = max(1, min(5, (int) $request->query('paso', 0) ?: 3));
        $volverUrl = route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => $pasoRetorno]);

        if ($tipo === 'punto-venta') {
            $punto = $pedido->puntoVenta;
            abort_unless($punto, 404);

            $ubicacion = $punto->ubicacionVisible();

            $contexto = [
                'titulo' => 'Punto de venta',
                'nombre' => $punto->nombre,
                'direccion' => $ubicacion['direccion'],
                'estimada' => $ubicacion['estimada'],
                'lat' => $punto->latitud,
                'lng' => $punto->longitud,
                'icono' => 'fa-store',
            ];
        } else {
            $almacen = $pedido->almacenMayoristaOrigen ?? $pedido->detalles->first()?->insumo?->almacen;
            abort_unless($almacen, 404);

            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );

            $contexto = [
                'titulo' => 'Origen mayorista',
                'nombre' => $almacen->nombre,
                'direccion' => $resuelto['direccion'] ?: 'Sin ubicación registrada',
                'estimada' => $resuelto['estimada'] ?? false,
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'icono' => 'fa-warehouse',
            ];
        }

        return view('punto_venta.pedidos.contexto.ubicacion', compact('pedido', 'contexto', 'volverUrl', 'tipo'));
    }

    public function aceptar(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionMayorista(auth()->user()), 403);

        if (! PedidoDistribucionCatalogo::puedeAceptarMayorista($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado.');
        }

        $errores = app(PedidoDistribucionMayoristaService::class)->verificarDisponibilidad($pedido);
        if ($errores !== []) {
            return back()->with('error', 'No se puede aceptar: '.$errores[0]);
        }

        $pedido->update([
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'fecha_aceptacion' => now(),
            'aceptado_por_usuarioid' => auth()->id(),
        ]);

        return back()->with('success', 'Pedido aceptado. Designe el transportista cuando el producto esté listo para salir.');
    }

    public function rechazar(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionMayorista(auth()->user()), 403);

        if (! PedidoDistribucionCatalogo::puedeAceptarMayorista($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado.');
        }

        $data = $request->validate(['motivo_rechazo' => 'nullable|string|max:500']);

        $obs = trim(($pedido->observaciones ?? '')."\n[Rechazado mayorista] ".($data['motivo_rechazo'] ?? 'Sin motivo.'));

        $pedido->update([
            'estado' => PedidoDistribucionCatalogo::ESTADO_RECHAZADO,
            'observaciones' => $obs,
        ]);

        return redirect()
            ->route('punto-venta.pedidos.index')
            ->with('success', 'Solicitud rechazada.');
    }

    public function designarTransportista(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionMayorista(auth()->user()), 403);

        $data = $request->validate([
            'transportista_usuarioid' => 'required|integer|exists:usuario,usuarioid',
            'vehiculoid' => 'required|integer|exists:vehiculo,vehiculoid',
        ]);

        try {
            app(PedidoDistribucionMayoristaService::class)->designarTransportista(
                $pedido,
                (int) $data['transportista_usuarioid'],
                (int) $data['vehiculoid'],
                (int) auth()->id()
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transportista y vehículo asignados. Marque en ruta cuando el vehículo salga hacia el punto de venta.');
    }

    /** @deprecated Alias de designarTransportista — conserva la ruta histórica. */
    public function marcarEnviado(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        return $this->designarTransportista($request, $pedido);
    }

    public function empezarRuta(PedidoDistribucion $pedido, SimulacionRutaService $simulacion): RedirectResponse
    {
        $pedido->loadMissing('rutaDistribucion');
        $ruta = $pedido->rutaDistribucion;
        abort_unless($ruta !== null, 404);

        $user = auth()->user();
        if (! UsuarioRol::esAdminGlobal($user)
            && (int) $ruta->transportista_usuarioid !== (int) $user?->usuarioid) {
            abort(403);
        }

        try {
            $simulacion->empezarDistribucion($ruta);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $redirect = redirect()
            ->route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => 4])
            ->with('success', 'Pedido marcado en ruta. El recorrido simulado está en marcha hacia el punto de venta.');

        if (UsuarioRol::esAdminGlobal($user)) {
            $redirect->with(
                'info',
                'Puede seguir el vehículo en tiempo real desde Logística → Ruta en tiempo real.'
            );
        } elseif (UsuarioRol::esTransportista($user)) {
            $redirect->with(
                'info',
                'Recorrido iniciado. Puede seguir su ruta en el panel de transportista.'
            );
        }

        return $redirect;
    }

    public function update(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $user = $request->user();
        $esAdmin = UsuarioRol::esAdminGlobal($user);

        $data = $request->validate([
            'puntoventaid' => [$esAdmin ? 'required' : 'nullable', 'integer', 'exists:punto_venta,puntoventaid'],
            'minorista_usuarioid' => [$esAdmin ? 'required' : 'nullable', 'integer', 'exists:usuario,usuarioid'],
            'almacen_mayorista_origenid' => [$esAdmin ? 'required' : 'nullable', 'integer', 'exists:almacen,almacenid'],
            'insumoid' => 'required|integer|exists:insumo,insumoid',
            'cantidad' => 'required|numeric|gt:0',
            'fecha_entrega_deseada' => 'nullable|date',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        try {
            app(PedidoDistribucionMayoristaService::class)->actualizarSolicitud($pedido, $data, $user);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => 1])
            ->with('success', 'Solicitud actualizada correctamente.');
    }

    public function reabrirRevision(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionMayorista(auth()->user()), 403);

        try {
            app(PedidoDistribucionMayoristaService::class)->reabrirRevision($pedido);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => 2])
            ->with('success', 'Pedido devuelto a revisión mayorista. Puede ajustar la solicitud y volver a aceptar.');
    }

    public function confirmarRecepcion(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);
        abort_unless(
            UsuarioRol::esMinorista(auth()->user())
            && (int) $pedido->puntoVenta?->usuarioid === (int) auth()->id(),
            403
        );

        try {
            app(RecepcionPuntoVentaService::class)->confirmar($pedido, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('punto-venta.puntos.show', $pedido->puntoventaid)
            ->with('success', 'Llegada del pedido confirmada. El inventario del punto de venta fue actualizado.');
    }
}
