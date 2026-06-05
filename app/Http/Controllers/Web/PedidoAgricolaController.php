<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
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
    public function index(): View
    {
        $pedidos = Pedido::query()
            ->with([
                'detalles.insumo',
                'detalles.cosechaAlmacen.almacen',
                'aceptadoPor',
                'envioAsignacion.transportista.perfilTransportista.vehiculo.tipoVehiculo',
            ])
            ->orderByDesc('pedidoid')
            ->get();

        $pendientes = $pedidos->filter(fn (Pedido $p) => PedidoCatalogo::pendienteAprobacionAgricola($p));
        $procesados = $pedidos->reject(fn (Pedido $p) => PedidoCatalogo::pendienteAprobacionAgricola($p));

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

    public function aceptar(Request $request, Pedido $pedido): RedirectResponse
    {
        if (! PedidoCatalogo::pendienteAprobacionAgricola($pedido)) {
            return back()->with('warning', 'Este pedido ya fue procesado por producción agrícola.');
        }

        $reserva = app(PedidoReservaService::class);

        try {
            DB::transaction(function () use ($pedido, $reserva) {
                $reserva->reservar($pedido);

                $pedido->update([
                    'estado' => PedidoCatalogo::ESTADO_CONFIRMADO,
                    'fecha_aceptacion_agricola' => now(),
                    'aceptado_por_usuarioid' => auth()->id(),
                ]);

                EnvioAsignacionMultiple::query()
                    ->where(function ($q) use ($pedido) {
                        $q->where('pedidoid', $pedido->pedidoid)
                            ->orWhere('externo_envio_id', $pedido->numero_solicitud);
                    })
                    ->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
                        'estado' => 'pendiente',
                    ]));
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('agricola.pedidos.show', $pedido)
            ->with('success', 'Pedido aceptado. Se reservó material del almacén agrícola. Ya se puede asignar un transportista.');
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
