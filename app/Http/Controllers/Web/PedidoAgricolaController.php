<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Services\NotificacionUsuarioService;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\PedidoCatalogo;
use App\Support\PedidoReservaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoAgricolaController extends Controller
{
    public function index(Request $request): View
    {
        $query = PedidoCatalogo::queryOperativosLogistica()
            ->with([
                'detalles.insumo',
                'detalles.cosechaAlmacen.almacen',
                'aceptadoPor',
                'envioAsignacion.transportista.perfilTransportista.vehiculo.tipoVehiculo',
            ]);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim()->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('numero_solicitud', 'like', $term)
                    ->orWhereHas('detalles', fn ($d) => $d->where('cultivo_personalizado', 'like', $term))
                    ->orWhereHas('envioAsignacion.transportista', function ($t) use ($term) {
                        $t->where('nombre', 'like', $term)
                            ->orWhere('apellido', 'like', $term)
                            ->orWhere('nombreusuario', 'like', $term);
                    });
            });
        }

        if ($request->filled('estado')) {
            match ($request->string('estado')->toString()) {
                'pendiente_agricola' => $query->whereIn('estado', ['sin asignacion', 'pendiente']),
                'aceptado' => $query->whereIn('estado', ['confirmado', 'en produccion']),
                'rechazado' => $query->where('estado', 'rechazado'),
                default => null,
            };
        }

        if ($request->filled('transporte')) {
            if ($request->string('transporte')->toString() === 'con') {
                $query->whereHas('envioAsignacion', fn ($e) => $e->whereNotNull('transportista_usuarioid'));
            } elseif ($request->string('transporte')->toString() === 'sin') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('envioAsignacion')
                        ->orWhereHas('envioAsignacion', fn ($e) => $e->whereNull('transportista_usuarioid'));
                });
            }
        }

        if ($request->filled('fase_envio')) {
            match ($request->string('fase_envio')->toString()) {
                'pendiente_salida' => $query->whereHas('envioAsignacion', fn ($e) => $e
                    ->whereNotNull('transportista_usuarioid')
                    ->whereIn('estado', ['asignado', 'asignada', 'pendiente', 'creada'])),
                'en_camino' => $query->whereHas('envioAsignacion', fn ($e) => $e
                    ->whereIn('estado', ['en_transporte_planta', 'en_ruta', 'en_transito'])),
                'recibido' => $query->whereHas('envioAsignacion', fn ($e) => $e
                    ->where(function ($s) {
                        $s->whereIn('estado', ['recibido_planta', 'entregado', 'entregada'])
                            ->orWhereNotNull('fecha_recepcion_planta');
                    })),
                default => null,
            };
        }

        $pedidos = $query
            ->orderByRaw("CASE WHEN estado IN ('sin asignacion', 'pendiente') THEN 0 ELSE 1 END")
            ->orderByDesc('pedidoid')
            ->get();

        $todosOperativos = PedidoCatalogo::queryOperativosLogistica()
            ->with('envioAsignacion')
            ->orderByDesc('pedidoid')
            ->get();

        $pendientes = $todosOperativos->filter(fn (Pedido $p) => PedidoCatalogo::pendienteAprobacionAgricola($p));
        $procesados = $todosOperativos->reject(fn (Pedido $p) => PedidoCatalogo::pendienteAprobacionAgricola($p));

        return view('agricola.pedidos.index', compact('pedidos', 'pendientes', 'procesados'));
    }

    public function show(Pedido $pedido): View
    {
        $pedido->load([
            'detalles.insumo',
            'detalles.cosechaAlmacen.produccion.lote.cultivo',
            'aceptadoPor',
            'envioAsignacion.transportista.perfilTransportista.vehiculo.tipoVehiculo',
            'envioAsignacion.asignadoPor',
        ]);
        $erroresStock = app(PedidoReservaService::class)->verificarDisponibilidad($pedido);

        return view('agricola.pedidos.show', compact('pedido', 'erroresStock'));
    }

    public function aceptar(Request $request, Pedido $pedido, NotificacionUsuarioService $notificaciones): RedirectResponse
    {
        if (! PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado por producción agrícola.');
        }

        $reserva = app(PedidoReservaService::class);
        $envioActivado = null;

        try {
            DB::transaction(function () use ($pedido, $reserva, &$envioActivado) {
                $reserva->reservar($pedido);

                $pedido->update([
                    'estado' => PedidoCatalogo::ESTADO_CONFIRMADO,
                    'fecha_aceptacion_agricola' => now(),
                    'aceptado_por_usuarioid' => auth()->id(),
                ]);

                EnvioPedidoService::activarTransportistaProgramado($pedido->fresh());

                $envio = EnvioAsignacionMultiple::query()
                    ->where(function ($q) use ($pedido) {
                        $q->where('pedidoid', $pedido->pedidoid)
                            ->orWhere('externo_envio_id', $pedido->numero_solicitud);
                    })
                    ->first();

                if ($envio && ! $envio->transportista_usuarioid) {
                    $envio->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
                        'estado' => 'pendiente',
                    ]));
                }

                if ($envio?->transportista_usuarioid) {
                    $envioActivado = $envio->fresh(['pedido.detalles']);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($envioActivado) {
            $notificaciones->envioListoParaRecoger($envioActivado);
        }

        $tieneChofer = EnvioAsignacionMultiple::query()
            ->where(function ($q) use ($pedido) {
                $q->where('pedidoid', $pedido->pedidoid)
                    ->orWhere('externo_envio_id', $pedido->numero_solicitud);
            })
            ->whereNotNull('transportista_usuarioid')
            ->exists();

        $mensaje = $tieneChofer
            ? 'Pedido aceptado. Se reservó material del almacén agrícola y el transportista programado quedó asignado.'
            : 'Pedido aceptado. Se reservó material del almacén agrícola. Ya se puede asignar un transportista.';

        return redirect()
            ->route('agricola.pedidos.show', $pedido)
            ->with('success', $mensaje);
    }

    public function rechazar(Request $request, Pedido $pedido): RedirectResponse
    {
        if (! PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado.');
        }

        $data = $request->validate([
            'motivo_rechazo' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($pedido, $data) {
            $obs = trim(($pedido->observaciones ?? '')."\n[Rechazado agrícola] ".($data['motivo_rechazo'] ?? 'Sin motivo.'));

            $pedido->update([
                'estado' => PedidoCatalogo::ESTADO_RECHAZADO,
                'observaciones' => $obs,
            ]);

            EnvioAsignacionMultiple::query()
                ->where(function ($q) use ($pedido) {
                    $q->where('pedidoid', $pedido->pedidoid)
                        ->orWhere('externo_envio_id', $pedido->numero_solicitud);
                })
                ->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
                    'estado' => 'cancelado',
                ]));
        });

        return redirect()
            ->route('agricola.pedidos.index')
            ->with('success', 'Pedido rechazado. Logística no podrá asignar este envío.');
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

        return redirect()
            ->route('agricola.pedidos.show', $pedido)
            ->with('success', 'Carga confirmada en almacén agrícola. El envío está en camino hacia planta.');
    }
}
