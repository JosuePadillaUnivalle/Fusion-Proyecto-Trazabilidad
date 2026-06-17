<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Services\PedidoDistribucionPlantaService;
use App\Services\RecepcionPuntoVentaService;
use App\Support\AlmacenAmbito;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\PuntoVentaAccess;
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
            PedidoDistribucion::query()->with(['puntoVenta.minorista', 'detalles.insumo.unidadMedida', 'creadoPor']),
            $user
        );

        if ($request->filled('estado_grupo')) {
            $estados = PedidoDistribucionCatalogo::estadosDeGrupoFiltro(
                $request->string('estado_grupo')->toString()
            );
            if ($estados !== []) {
                $query->whereIn('estado', $estados);
            }
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

        $pendientes = $pedidos->filter(fn (PedidoDistribucion $p) => PedidoDistribucionCatalogo::pendienteAprobacionPlanta($p));
        $procesados = $pedidos->reject(fn (PedidoDistribucion $p) => PedidoDistribucionCatalogo::pendienteAprobacionPlanta($p));

        $puntosVenta = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->orderBy('nombre'),
            $user
        )->get();

        $filtroPdvId = $request->integer('puntoventaid') ?: null;
        $filtroPdvNombre = $filtroPdvId
            ? ($puntosVenta->firstWhere('puntoventaid', $filtroPdvId)?->nombre ?? '')
            : '';

        $puedeCrear = UsuarioRol::esMinorista($user) || UsuarioRol::esAdminGlobal($user);
        $puedeGestionarPlanta = UsuarioRol::puedeGestionarDistribucionPlanta($user);
        $esMinorista = UsuarioRol::esMinorista($user);

        return view('punto_venta.pedidos.index', compact(
            'pedidos',
            'pendientes',
            'procesados',
            'puntosVenta',
            'filtroPdvId',
            'filtroPdvNombre',
            'puedeCrear',
            'puedeGestionarPlanta',
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
        $oldAlmacen = old('almacen_planta_origenid') ? Almacen::find(old('almacen_planta_origenid')) : null;
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

        $data = $request->validate([
            'puntoventaid' => 'required|integer|exists:punto_venta,puntoventaid',
            'minorista_usuarioid' => [
                UsuarioRol::esAdminGlobal($user) ? 'required' : 'nullable',
                'integer',
                'exists:usuario,usuarioid',
            ],
            'almacen_planta_origenid' => 'nullable|integer|exists:almacen,almacenid',
            'fecha_entrega_deseada' => 'nullable|date',
            'observaciones' => 'nullable|string|max:2000',
            'insumoid' => 'required|integer|exists:insumo,insumoid',
            'cantidad' => 'required|numeric|min:0.01',
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

        $insumo = Insumo::query()->with('almacen')->findOrFail($data['insumoid']);

        $pedido = DB::transaction(function () use ($data, $insumo, $request) {
            $pedido = PedidoDistribucion::create([
                'numero_solicitud' => PedidoDistribucionCatalogo::generarNumeroSolicitud(),
                'puntoventaid' => (int) $data['puntoventaid'],
                'almacen_planta_origenid' => $data['almacen_planta_origenid'] ?? $insumo->almacenid,
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
            ->with('success', 'Solicitud enviada. Planta revisará el pedido y preparará el envío.');
    }

    public function show(PedidoDistribucion $pedido): View
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);

        $pedido->load(['puntoVenta.minorista', 'detalles.insumo.unidadMedida', 'almacenPlantaOrigen', 'aceptadoPor', 'creadoPor', 'transportista', 'vehiculo.tipoVehiculo', 'rutaDistribucion.transportista', 'rutaDistribucion.vehiculo']);

        $puedeGestionarPlanta = UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user())
            || UsuarioRol::esAdminGlobal(auth()->user());
        $esMinoristaDueño = UsuarioRol::esMinorista(auth()->user())
            && (int) $pedido->puntoVenta?->usuarioid === (int) auth()->id();
        $puedeAnunciarLlegada = $esMinoristaDueño
            && PedidoDistribucionCatalogo::puedeConfirmarRecepcion($pedido);

        $erroresStock = $puedeGestionarPlanta
            ? app(PedidoDistribucionPlantaService::class)->verificarDisponibilidad($pedido)
            : [];

        return view('punto_venta.pedidos.show', compact(
            'pedido',
            'puedeGestionarPlanta',
            'esMinoristaDueño',
            'puedeAnunciarLlegada',
            'erroresStock'
        ));
    }

    public function aceptar(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user()) || UsuarioRol::esAdminGlobal(auth()->user()), 403);

        if (! PedidoDistribucionCatalogo::puedeAceptarPlanta($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado.');
        }

        $errores = app(PedidoDistribucionPlantaService::class)->verificarDisponibilidad($pedido);
        if ($errores !== []) {
            return back()->with('error', 'No se puede aceptar: '.$errores[0]);
        }

        $pedido->update([
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'fecha_aceptacion' => now(),
            'aceptado_por_usuarioid' => auth()->id(),
        ]);

        return back()->with('success', 'Pedido aceptado. Marque el envío cuando el producto salga hacia el punto de venta.');
    }

    public function rechazar(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user()) || UsuarioRol::esAdminGlobal(auth()->user()), 403);

        if (! PedidoDistribucionCatalogo::puedeAceptarPlanta($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado.');
        }

        $data = $request->validate(['motivo_rechazo' => 'nullable|string|max:500']);

        $obs = trim(($pedido->observaciones ?? '')."\n[Rechazado planta] ".($data['motivo_rechazo'] ?? 'Sin motivo.'));

        $pedido->update([
            'estado' => PedidoDistribucionCatalogo::ESTADO_RECHAZADO,
            'observaciones' => $obs,
        ]);

        return redirect()
            ->route('punto-venta.pedidos.index')
            ->with('success', 'Solicitud rechazada.');
    }

    public function marcarEnviado(Request $request, PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user()) || UsuarioRol::esAdminGlobal(auth()->user()), 403);

        $data = $request->validate([
            'transportista_usuarioid' => 'required|integer|exists:usuario,usuarioid',
            'vehiculoid' => 'required|integer|exists:vehiculo,vehiculoid',
        ]);

        try {
            app(PedidoDistribucionPlantaService::class)->marcarEnTransito(
                $pedido,
                (int) $data['transportista_usuarioid'],
                (int) $data['vehiculoid']
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pedido marcado en tránsito. Transportista y vehículo de planta asignados.');
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
