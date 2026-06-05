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

        if ($request->filled('estado')) {
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

        $puedeCrear = UsuarioRol::esMinorista($user) || UsuarioRol::esAdminGlobal($user);
        $puedeGestionarPlanta = UsuarioRol::puedeGestionarDistribucionPlanta($user);
        $esMinorista = UsuarioRol::esMinorista($user);

        return view('punto_venta.pedidos.index', compact(
            'pedidos',
            'pendientes',
            'procesados',
            'puntosVenta',
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

        $puntosMinorista = PuntoVentaAccess::scopePuntosDelUsuario(
            PuntoVenta::query()->where('activo', true)->with('minorista')->orderBy('nombre'),
            $user
        )->get();

        $oldPunto = old('puntoventaid') ? PuntoVenta::with('minorista')->find(old('puntoventaid')) : null;
        $oldAlmacen = old('almacen_planta_origenid') ? Almacen::find(old('almacen_planta_origenid')) : null;
        $oldInsumo = old('insumoid') ? Insumo::with('unidadMedida', 'almacen')->find(old('insumoid')) : null;

        $minoristasFiltro = Usuario::query()
            ->where('role', 'minorista')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['usuarioid', 'nombre', 'apellido']);

        $esMinorista = UsuarioRol::esMinorista($user);
        $esAdmin = UsuarioRol::esAdminGlobal($user);

        return view('punto_venta.pedidos.create', [
            'numeroSolicitud' => PedidoDistribucionCatalogo::generarNumeroSolicitud(),
            'puntosMinorista' => $puntosMinorista,
            'esMinorista' => $esMinorista,
            'esAdmin' => $esAdmin,
            'oldPuntoLabel' => $oldPunto
                ? $oldPunto->nombre.' — '.trim($oldPunto->minorista?->nombre.' '.$oldPunto->minorista?->apellido)
                : '',
            'oldAlmacenLabel' => $oldAlmacen?->nombre ?? '',
            'oldProductoLabel' => $oldInsumo
                ? $oldInsumo->nombre.' · Stock '.number_format((float) $oldInsumo->stock, 2).' '.($oldInsumo->unidadMedida?->abreviatura ?? '')
                : '',
            'oldProductoUnidad' => $oldInsumo?->unidadMedida?->abreviatura ?? $oldInsumo?->unidadMedida?->nombre ?? '',
            'oldProductoStock' => $oldInsumo ? (float) $oldInsumo->stock : null,
            'minoristasFiltro' => $minoristasFiltro,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(UsuarioRol::esMinorista($user) || UsuarioRol::esAdminGlobal($user), 403);

        $data = $request->validate([
            'puntoventaid' => 'required|integer|exists:punto_venta,puntoventaid',
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
                'fecha_entrega_deseada' => $data['fecha_entrega_deseada'] ?? null,
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

        $pedido->load(['puntoVenta.minorista', 'detalles.insumo.unidadMedida', 'almacenPlantaOrigen', 'aceptadoPor', 'creadoPor']);

        $puedeGestionarPlanta = UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user())
            || UsuarioRol::esAdminGlobal(auth()->user());
        $esMinoristaDueño = UsuarioRol::esMinorista(auth()->user())
            && (int) $pedido->puntoVenta?->usuarioid === (int) auth()->id();

        $erroresStock = $puedeGestionarPlanta
            ? app(PedidoDistribucionPlantaService::class)->verificarDisponibilidad($pedido)
            : [];

        return view('punto_venta.pedidos.show', compact(
            'pedido',
            'puedeGestionarPlanta',
            'esMinoristaDueño',
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

    public function marcarEnviado(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user()) || UsuarioRol::esAdminGlobal(auth()->user()), 403);

        if (! PedidoDistribucionCatalogo::puedeMarcarEnviado($pedido)) {
            return back()->with('warning', 'El pedido debe estar aceptado por planta antes de marcar el envío.');
        }

        $pedido->update([
            'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
            'fecha_envio' => now(),
        ]);

        return back()->with('success', 'Pedido marcado en tránsito hacia el punto de venta.');
    }

    public function confirmarRecepcion(PedidoDistribucion $pedido): RedirectResponse
    {
        abort_unless(PuntoVentaAccess::puedeVerPedido(auth()->user(), $pedido), 403);
        abort_unless(UsuarioRol::esMinorista(auth()->user()), 403);

        try {
            app(RecepcionPuntoVentaService::class)->confirmar($pedido, auth()->user());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('punto-venta.puntos.show', $pedido->puntoventaid)
            ->with('success', 'Recepción confirmada. El inventario del punto de venta fue actualizado.');
    }
}
