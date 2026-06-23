<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Services\DisponibilidadMayoristaPdvService;
use App\Services\NotificacionUsuarioService;
use App\Services\PedidoDistribucionMayoristaService;
use App\Services\PuntoVentaAlmacenService;
use App\Services\RecepcionPuntoVentaService;
use App\Services\SimulacionRutaService;
use App\Services\TransporteCapacidadService;
use App\Support\AlmacenAmbito;
use App\Support\EnvioTrayectoCatalogo;
use App\Support\MayoristaAccess;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\PedidoDistribucionVista;
use App\Support\PuntoVentaAccess;
use App\Support\SimulacionRutaCatalogo;
use App\Support\UbicacionGpsParser;
use App\Support\UsuarioRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\View\View;

class PedidoDistribucionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $esBandejaMayorista = PedidoDistribucionVista::esBandejaMayorista($request, $user);

        $query = PuntoVentaAccess::scopePedidosDelUsuario(
            PedidoDistribucion::query()->with([
                'puntoVenta.minorista',
                'detalles.insumo.unidadMedida',
                'detalles.presentacion',
                'almacenMayoristaOrigen',
                'creadoPor',
                'rutaDistribucion',
            ]),
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

        if ($esBandejaMayorista && $request->filled('almacenid')) {
            $query->where('almacen_mayorista_origenid', (int) $request->input('almacenid'));
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function ($w) use ($term) {
                $w->where('numero_solicitud', 'like', "%{$term}%")
                    ->orWhere('observaciones', 'like', "%{$term}%")
                    ->orWhereHas('puntoVenta', fn ($p) => $p->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('puntoVenta.minorista', function ($m) use ($term) {
                        $m->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%");
                    })
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

        $puedeCrear = PedidoDistribucionVista::puedeCrearSolicitud($request, $user);
        $puedeGestionarMayorista = UsuarioRol::puedeGestionarDistribucionMayorista($user);
        $esMinorista = UsuarioRol::esMinorista($user);

        $almacenesMayorista = $esBandejaMayorista
            ? AlmacenAmbito::scope(Almacen::query()->where('activo', true), AlmacenAmbito::MAYORISTA)->orderBy('nombre')->get()
            : collect();

        $viewData = compact(
            'pedidos',
            'pendientes',
            'procesados',
            'enRutaTiempoReal',
            'puntosVenta',
            'filtroPdvId',
            'filtroPdvNombre',
            'puedeCrear',
            'puedeGestionarMayorista',
            'esMinorista',
            'esBandejaMayorista',
            'almacenesMayorista'
        );

        return view(
            $esBandejaMayorista ? 'punto_venta.pedidos.index-mayorista' : 'punto_venta.pedidos.index',
            $viewData
        );
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless(PedidoDistribucionVista::puedeCrearSolicitud($request, $user), 403);

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

    public function store(Request $request, DisponibilidadMayoristaPdvService $disponibilidadMayorista, PuntoVentaAlmacenService $almacenPdv, NotificacionUsuarioService $notificaciones): RedirectResponse
    {
        $user = $request->user();
        abort_unless(PedidoDistribucionVista::puedeCrearSolicitud($request, $user), 403);

        $esAdmin = UsuarioRol::esAdminGlobal($user);
        $esMayorista = UsuarioRol::esMayorista($user) && ! UsuarioRol::esMinorista($user);
        $esMinorista = UsuarioRol::esMinorista($user) && ! $esAdmin;

        if (! $esMinorista) {
            EnvioTrayectoCatalogo::autorizarTrayecto($user, EnvioTrayectoCatalogo::TRAYECTO_PDV);
        }

        $request->merge(['tipo_solicitud' => PedidoDistribucionCatalogo::TIPO_SOLICITUD_STOCK]);

        $data = $request->validate([
            'puntoventaid' => 'required|integer|exists:punto_venta,puntoventaid',
            'minorista_usuarioid' => [
                $esAdmin ? 'required' : 'nullable',
                'integer',
                'exists:usuario,usuarioid',
            ],
            'almacen_mayorista_origenid' => [
                $esMinorista ? 'required' : 'nullable',
                'integer',
                'exists:almacen,almacenid',
            ],
            'tipo_solicitud' => 'required|in:stock',
            'fecha_entrega_deseada' => 'required|date|after_or_equal:today',
            'hora_entrega_deseada' => 'nullable|date_format:H:i',
            'observaciones' => 'nullable|string|max:2000',
            'insumoid' => 'required|integer|exists:insumo,insumoid',
            'insumo_presentacionid' => 'required|integer|exists:insumo_presentacion,insumo_presentacionid',
            'cantidad' => 'required|numeric|gt:0',
        ], [
            'puntoventaid.required' => 'Seleccione un punto de venta destino.',
            'minorista_usuarioid.required' => 'Seleccione el minorista destino.',
            'fecha_entrega_deseada.required' => 'Indique la fecha de entrega deseada.',
            'almacen_mayorista_origenid.required' => 'Seleccione el mayorista desde el catálogo de productos.',
            'insumoid.required' => 'Seleccione un producto disponible en almacén mayorista.',
            'insumo_presentacionid.required' => 'Seleccione la presentación del producto.',
            'cantidad.required' => 'Indique la cantidad solicitada.',
            'cantidad.gt' => 'La cantidad debe ser mayor que cero.',
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

        $tipoSolicitud = PedidoDistribucionCatalogo::TIPO_SOLICITUD_STOCK;

        $almacenOrigenId = null;
        if ($esMayorista || $esAdmin) {
            try {
                $almacenOrigenId = EnvioTrayectoCatalogo::resolverAlmacenMayoristaOrigen(
                    $user,
                    isset($data['almacen_mayorista_origenid']) ? (int) $data['almacen_mayorista_origenid'] : null
                );
            } catch (InvalidArgumentException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        } elseif ($esMinorista && ! empty($data['almacen_mayorista_origenid'])) {
            $almacenOrigenId = (int) $data['almacen_mayorista_origenid'];
        }

        $detallePayload = [
            'cantidad' => (float) $data['cantidad'],
            'es_solicitud_custom' => false,
        ];

        $insumoRef = Insumo::query()->with('almacen')->findOrFail((int) $data['insumoid']);
        if ($insumoRef->almacen?->ambito !== AlmacenAmbito::MAYORISTA) {
            return back()->withInput()->with('error', 'El producto debe estar en almacén mayorista.');
        }

        if ($almacenOrigenId !== null && (int) $insumoRef->almacenid !== (int) $almacenOrigenId) {
            return back()->withInput()->with('error', 'El producto no corresponde al mayorista seleccionado.');
        }

        if ($almacenOrigenId === null) {
            $almacenOrigenId = (int) $insumoRef->almacenid;
        }

        $presentacionId = (int) $data['insumo_presentacionid'];
        $presentacion = \App\Models\InsumoPresentacion::query()
            ->where('insumo_presentacionid', $presentacionId)
            ->where('activo', true)
            ->first();

        if ($presentacion === null) {
            return back()->withInput()->with('error', 'La presentación seleccionada no es válida.');
        }

        if (! $disponibilidadMayorista->presentacionValidaParaProducto((int) $data['insumoid'], $presentacionId)) {
            return back()->withInput()->with('error', 'La presentación no corresponde al producto seleccionado.');
        }

        if (! $disponibilidadMayorista->productoEnCatalogoMayorista((int) $data['insumoid'])) {
            return back()->withInput()->with('error', 'El producto no está disponible en la red mayorista.');
        }

        if ($disponibilidadMayorista->necesitaEsperaStock(
            (int) $data['insumoid'],
            $presentacionId,
            (float) $data['cantidad']
        )) {
            return back()->withInput()->with(
                'error',
                'La cantidad supera el stock disponible. Reduzca la cantidad o elija otra presentación con stock.'
            );
        }

        try {
            $disponibilidadMayorista->resolverOrigenStock(
                (int) $data['insumoid'],
                $presentacionId,
                (float) $data['cantidad']
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $kgPedido = (float) $data['cantidad'] * $presentacion->pesoNetoKg();
        try {
            $almacenPdv->validarIngresoPedido($punto, $kgPedido);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->with('error', $e->validator->errors()->first() ?? 'El pedido supera la capacidad del almacén del punto de venta.');
        }

        $detallePayload['insumoid'] = $insumoRef->insumoid;
        $detallePayload['insumo_presentacionid'] = $presentacion->insumo_presentacionid;
        $detallePayload['tipo_envase'] = $presentacion->tipo_envase;
        $detallePayload['producto_nombre'] = $insumoRef->nombre.' · '.$presentacion->nombre;

        $pedido = DB::transaction(function () use ($data, $tipoSolicitud, $detallePayload, $request, $almacenOrigenId) {
            $pedido = PedidoDistribucion::create([
                'numero_solicitud' => PedidoDistribucionCatalogo::generarNumeroSolicitud(),
                'puntoventaid' => (int) $data['puntoventaid'],
                'almacen_mayorista_origenid' => $almacenOrigenId,
                'estado' => PedidoDistribucionCatalogo::ESTADO_PENDIENTE,
                'tipo_solicitud' => $tipoSolicitud,
                'espera_stock' => false,
                'requiere_coordinacion_planta' => false,
                'coordinacion_planta_resuelta' => false,
                'fechapedido' => now(),
                'fecha_entrega_deseada' => $data['fecha_entrega_deseada'],
                'hora_entrega_deseada' => $data['hora_entrega_deseada'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'creado_por_usuarioid' => $request->user()->usuarioid,
            ]);

            DetallePedidoDistribucion::create(array_merge([
                'pedidodistribucionid' => $pedido->pedidodistribucionid,
            ], $detallePayload));

            return $pedido;
        });

        if ($esMinorista) {
            $pedido->load(['detalles.insumo', 'puntoVenta.minorista', 'almacenMayoristaOrigen']);
            $notificaciones->solicitudPedidoMinoristaMayorista($pedido);
        }

        $ctxRedirect = $esMayorista ? 'mayorista' : 'pdv';
        $mensajeExito = $esMayorista
            ? 'Envío registrado. Revise stock y asigne transporte cuando esté listo para salir.'
            : 'Solicitud enviada. El centro mayorista revisará el pedido y preparará el envío.';

        return redirect()
            ->route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => $ctxRedirect])
            ->with('success', $mensajeExito);
    }

    public function show(Request $request, PedidoDistribucion $pedido): View
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $pedido->load([
            'puntoVenta.minorista',
            'detalles.insumo.unidadMedida',
            'detalles.insumo.almacen',
            'detalles.insumoPlantaReferencia.unidadMedida',
            'detalles.presentacion.tipoEmpaque',
            'almacenMayoristaOrigen',
            'aceptadoPor',
            'creadoPor',
            'transportista',
            'vehiculo.tipoVehiculo',
            'rutaDistribucion.transportista',
            'rutaDistribucion.vehiculo',
            'solicitudesProduccionPlanta.creadoPor',
        ]);

        $user = auth()->user();
        $ruta = $pedido->rutaDistribucion;
        $puedeGestionarMayorista = UsuarioRol::puedeGestionarDistribucionMayorista($user);
        $esMinoristaDueño = UsuarioRol::esMinorista($user)
            && (int) $pedido->puntoVenta?->usuarioid === (int) auth()->id();
        $puedeAnunciarLlegada = false;
        $estadoRecepcionPdv = [];
        $resumenCierrePdv = null;
        if ($ruta !== null && ! $ruta->esTrasladoPlantaMayorista()) {
            $resumenCierrePdv = app(\App\Services\CierreEnvioDistribucionPdvService::class)->resumenPasos($ruta);
            $estadoRecepcionPdv = app(\App\Services\RecepcionPdvMinoristaService::class)->estadoRecepcion($ruta, $pedido);
            $puedeAnunciarLlegada = $esMinoristaDueño && ($estadoRecepcionPdv['puede_firmar'] ?? false);
        } elseif ($esMinoristaDueño && PedidoDistribucionCatalogo::puedeConfirmarRecepcion($pedido)) {
            $puedeAnunciarLlegada = true;
        }

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

        $puedeSolicitarProduccionPlanta = false;
        $solicitudActivaPlanta = $pedido->solicitudesProduccionPlanta
            ->sortByDesc('solicitudproduccionplantaid')
            ->first();

        $puedeEliminarSolicitud = $pedido->estado === PedidoDistribucionCatalogo::ESTADO_PENDIENTE
            && (UsuarioRol::esAdminGlobal($user) || $esMinoristaDueño);

        $esBandejaMayorista = PedidoDistribucionVista::esBandejaMayorista($request, $user);
        $ctxVolver = $esBandejaMayorista ? 'mayorista' : 'pdv';

        $capacidadSvc = app(TransporteCapacidadService::class);
        $resumenCargaPedido = [
            'peso_kg' => $capacidadSvc->pesoPedidosDistribucion([$pedido]),
            'volumen_m3' => $capacidadSvc->volumenPedidosDistribucion([$pedido]),
        ];

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
            'puedeSolicitarProduccionPlanta',
            'solicitudActivaPlanta',
            'puedeEliminarSolicitud',
            'esBandejaMayorista',
            'ctxVolver',
            'estadoRecepcionPdv',
            'resumenCierrePdv',
            'resumenCargaPedido',
        ));
    }

    public function validarCapacidadVehiculo(Request $request, PedidoDistribucion $pedido, TransporteCapacidadService $capacidadSvc): JsonResponse
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $data = $request->validate([
            'vehiculoid' => 'required|integer|exists:vehiculo,vehiculoid',
        ]);

        $vehiculo = \App\Models\Vehiculo::query()->with('tipoVehiculo')->findOrFail((int) $data['vehiculoid']);
        $pesoKg = $capacidadSvc->pesoPedidosDistribucion([$pedido]);
        $volumenM3 = $capacidadSvc->volumenPedidosDistribucion([$pedido]);
        $cap = $capacidadSvc->capacidadEfectiva($vehiculo);
        $ok = true;
        $mensaje = '';

        try {
            $capacidadSvc->validarCarga($vehiculo, $pesoKg, $volumenM3);
        } catch (InvalidArgumentException $e) {
            $ok = false;
            $mensaje = $e->getMessage();
        }

        $pctKg = $cap['kg'] > 0 ? round(($pesoKg / $cap['kg']) * 100, 1) : null;
        $pctM3 = ($cap['m3'] > 0 && $volumenM3 !== null && $volumenM3 > 0)
            ? round(($volumenM3 / $cap['m3']) * 100, 1)
            : null;
        $pctUso = max($pctKg ?? 0, $pctM3 ?? 0);

        return response()->json([
            'ok' => $ok,
            'mensaje' => $mensaje,
            'capacidad_kg' => $cap['kg'],
            'capacidad_m3' => $cap['m3'],
            'peso_kg' => round($pesoKg, 2),
            'volumen_m3' => $volumenM3 !== null ? round($volumenM3, 3) : null,
            'porcentaje_uso' => $pctUso > 0 ? $pctUso : $pctKg,
            'porcentaje_peso' => $pctKg,
            'porcentaje_volumen' => $pctM3,
            'vehiculo' => $vehiculo->placa,
            'recomendacion' => $ok
                ? 'El vehículo seleccionado cubre peso y volumen de este pedido.'
                : 'Elija otro vehículo o reduzca la cantidad solicitada.',
        ]);
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

    public function destroy(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $user = auth()->user();
        $esMinoristaDueño = UsuarioRol::esMinorista($user)
            && (int) $pedido->puntoVenta?->usuarioid === (int) $user->usuarioid;

        if ($pedido->estado !== PedidoDistribucionCatalogo::ESTADO_PENDIENTE) {
            return back()->with([
                'error' => 'Solo puede eliminar solicitudes que aún no fueron aceptadas por el mayorista.',
                'error_modal' => true,
                'error_modal_titulo' => 'No se puede eliminar',
            ]);
        }

        if (! UsuarioRol::esAdminGlobal($user) && ! $esMinoristaDueño) {
            abort(403);
        }

        $pedido->detalles()->delete();
        $pedido->delete();

        return redirect()
            ->route('punto-venta.pedidos.index', ['ctx' => 'pdv'])
            ->with('success', 'Solicitud eliminada.');
    }

    public function aceptar(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionMayorista(auth()->user()), 403);
        MayoristaAccess::asegurarPuedeVerPedido(auth()->user(), $pedido);

        if (! PedidoDistribucionCatalogo::puedeAceptarMayorista($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado.');
        }

        try {
            app(PedidoDistribucionMayoristaService::class)->aceptarPedido($pedido, (int) auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $pedido->refresh();
        $mensaje = $pedido->requiere_coordinacion_planta
            ? 'Pedido aceptado. Debe solicitar producción a planta antes de asignar transportista.'
            : 'Pedido aceptado. Designe el transportista cuando el producto esté listo para salir.';

        return back()->with('success', $mensaje);
    }

    public function rechazar(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionMayorista(auth()->user()), 403);
        MayoristaAccess::asegurarPuedeVerPedido(auth()->user(), $pedido);

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
        MayoristaAccess::asegurarPuedeVerPedido(auth()->user(), $pedido);

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

        return redirect()
            ->route('punto-venta.pedidos.show', [
                'pedido' => $pedido->fresh(),
                'ctx' => 'mayorista',
                'paso' => 4,
            ])
            ->with('success', 'Transportista y vehículo asignados. El chofer debe completar el cierre operativo (condiciones del vehículo, incidentes y firmas) antes de salir en ruta.');
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
        if (! UsuarioRol::puedeMarcarEnRutaDistribucion($user, $ruta)) {
            abort(403);
        }

        try {
            $simulacion->empezarDistribucion($ruta);
        } catch (\InvalidArgumentException $e) {
            if (! app(\App\Services\CierreEnvioDistribucionPdvService::class)->tieneCondicionesVehiculo($ruta)) {
                return redirect()
                    ->route('punto-venta.rutas.cierre.panel', $ruta)
                    ->with('info', 'Registre las condiciones del vehículo en el cierre operativo antes de marcar en ruta.');
            }

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
        MayoristaAccess::asegurarPuedeVerPedido(auth()->user(), $pedido);

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

        $pedido->loadMissing('rutaDistribucion');
        $ruta = $pedido->rutaDistribucion;
        if ($ruta !== null && ! $ruta->esTrasladoPlantaMayorista()) {
            $cierre = app(\App\Services\CierreEnvioDistribucionPdvService::class);
            if ($cierre->tieneCondicionesVehiculo($ruta) || $ruta->llegada_confirmada_at) {
                return redirect()
                    ->route('punto-venta.rutas.cierre.panel', $ruta)
                    ->with('info', 'Use el cierre operativo con firmas para registrar la recepción en punto de venta.');
            }
        }

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
