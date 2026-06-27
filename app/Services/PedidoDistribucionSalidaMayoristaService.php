<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\DetallePedidoDistribucion;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\TipoMovimientoAlmacen;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PedidoDistribucionSalidaMayoristaService
{
    public function __construct(
        private readonly InventarioPresentacionService $inventarioPresentacion,
    ) {}

    public function descontarPedidosDeRuta(RutaDistribucion $ruta, Usuario $usuario): void
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            return;
        }

        $ruta->loadMissing([
            'pedidos.detalles.insumo.unidadMedida',
            'pedidos.detalles.presentacion.tipoEmpaque',
            'pedidos.detalles.inventarioPresentacionLote',
            'pedidos.puntoVenta.almacen',
        ]);

        $tipoSalida = TipoMovimientoAlmacen::activosPorNaturaleza('salida')->first();
        if ($tipoSalida === null) {
            return;
        }

        DB::transaction(function () use ($ruta, $usuario, $tipoSalida) {
            foreach ($ruta->pedidos as $pedido) {
                $destino = $pedido->puntoVenta?->almacen?->nombre
                    ?? $pedido->puntoVenta?->nombre
                    ?? 'Punto de venta';

                foreach ($pedido->detalles as $detalle) {
                    if ($this->yaDescontado($pedido, $detalle)) {
                        $this->descontarSoloInventarioSiPendiente($detalle, $pedido);

                        continue;
                    }

                    $this->descontarDetalle($detalle, $pedido, $usuario, $tipoSalida, $destino);
                }
            }
        });
    }

    public function yaDescontado(PedidoDistribucion $pedido, DetallePedidoDistribucion $detalle): bool
    {
        $insumoId = (int) $detalle->insumoid;
        if ($insumoId <= 0) {
            return false;
        }

        return AlmacenMovimiento::query()
            ->where('insumoid', $insumoId)
            ->where('referencia', $pedido->numero_solicitud)
            ->where('observaciones', 'like', '%Distribución PDV — salida mayorista%')
            ->exists();
    }

    public function descontarDetalle(
        DetallePedidoDistribucion $detalle,
        PedidoDistribucion $pedido,
        Usuario $usuario,
        ?TipoMovimientoAlmacen $tipoSalida = null,
        ?string $destinoMotivo = null,
    ): void {
        $cantidadUnidades = (float) $detalle->cantidad;
        if ($cantidadUnidades <= 0) {
            throw new InvalidArgumentException('Cantidad inválida en el detalle del pedido.');
        }

        $detalle->loadMissing('presentacion.tipoEmpaque', 'insumo.unidadMedida', 'inventarioPresentacionLote');
        $presentacion = $detalle->presentacion;
        $kgMovimiento = $presentacion
            ? round($cantidadUnidades * $presentacion->pesoNetoKg(), 4)
            : $cantidadUnidades;

        $insumoOrigen = $detalle->insumo;
        if ($insumoOrigen === null) {
            throw new InvalidArgumentException('Producto de origen no encontrado.');
        }

        $almacenOrigenId = (int) ($detalle->almacen_mayorista_origenid ?? $insumoOrigen->almacenid);
        if ($almacenOrigenId <= 0) {
            throw new InvalidArgumentException('No se pudo determinar el almacén mayorista de origen.');
        }

        $this->validarStockDisponible($detalle, $insumoOrigen, $almacenOrigenId, $presentacion, $cantidadUnidades, $kgMovimiento);

        if ($detalle->inventario_presentacion_loteid && $detalle->inventarioPresentacionLote) {
            $this->inventarioPresentacion->descontar(
                $detalle->inventarioPresentacionLote,
                $cantidadUnidades,
                $kgMovimiento
            );
        } elseif ($presentacion !== null) {
            $this->inventarioPresentacion->descontarFifo(
                $almacenOrigenId,
                (int) $presentacion->insumo_presentacionid,
                $cantidadUnidades,
                $kgMovimiento
            );
        } else {
            if (! $insumoOrigen->tieneStockSuficiente($cantidadUnidades)) {
                throw new InvalidArgumentException(
                    "Stock insuficiente en origen para «{$insumoOrigen->nombre}». Disponible: {$insumoOrigen->stock}."
                );
            }
            $insumoOrigen->decrementarStock($kgMovimiento);
        }

        $tipoSalida ??= TipoMovimientoAlmacen::activosPorNaturaleza('salida')->firstOrFail();
        $ref = $pedido->numero_solicitud;
        $obsUnidades = $presentacion
            ? number_format($cantidadUnidades, 0).' '.$presentacion->etiquetaUnidad().' ('.number_format($kgMovimiento, 2).' kg)'
            : number_format($cantidadUnidades, 2).' ud';

        AlmacenMovimiento::create([
            'almacenid' => $almacenOrigenId,
            'insumoid' => $insumoOrigen->insumoid,
            'tipo_movimiento_almacenid' => $tipoSalida->tipo_movimiento_almacenid,
            'usuarioid' => $usuario->usuarioid,
            'fecha' => now()->toDateString(),
            'cantidad' => $kgMovimiento,
            'referencia' => $ref,
            'destino_motivo' => $destinoMotivo ?? 'Punto de venta',
            'observaciones' => '[Distribución PDV — salida mayorista] '.$ref.' · '.$obsUnidades,
        ]);

        if ($presentacion !== null) {
            $this->inventarioPresentacion->sincronizarStockAgregadoInsumo((int) $insumoOrigen->insumoid);
        }
    }

    /**
     * Corrige envíos cuyo movimiento de salida existe pero el inventario por presentación no se descontó.
     */
    public function reconciliarSalidasPendientesAlmacen(Almacen $almacen, Usuario $usuario): void
    {
        if (($almacen->ambito ?? '') !== \App\Support\AlmacenAmbito::MAYORISTA) {
            return;
        }

        $estadosConSalida = [
            \App\Support\PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
            \App\Support\PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
            \App\Support\PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
        ];

        $pedidos = PedidoDistribucion::query()
            ->whereIn('estado', $estadosConSalida)
            ->where(function ($q) use ($almacen) {
                $q->where('almacen_mayorista_origenid', $almacen->almacenid)
                    ->orWhereHas('detalles', fn ($d) => $d->where('almacen_mayorista_origenid', $almacen->almacenid));
            })
            ->with([
                'detalles.insumo.unidadMedida',
                'detalles.presentacion.tipoEmpaque',
                'detalles.inventarioPresentacionLote',
                'puntoVenta.almacen',
            ])
            ->orderByDesc('pedidodistribucionid')
            ->get();

        foreach ($pedidos as $pedido) {
            $destino = $pedido->puntoVenta?->almacen?->nombre
                ?? $pedido->puntoVenta?->nombre
                ?? 'Punto de venta';

            foreach ($pedido->detalles as $detalle) {
                $almacenDetalle = (int) ($detalle->almacen_mayorista_origenid ?? $pedido->almacen_mayorista_origenid ?? 0);
                if ($almacenDetalle !== (int) $almacen->almacenid) {
                    continue;
                }

                if ($this->yaDescontado($pedido, $detalle)) {
                    $this->descontarSoloInventarioSiPendiente($detalle, $pedido);

                    continue;
                }

                try {
                    $this->descontarDetalle($detalle, $pedido, $usuario, null, $destino);
                } catch (InvalidArgumentException) {
                    // Si el stock ya no alcanza, no bloquear la vista del almacén.
                }
            }
        }
    }

    /** Aplica el descuento de inventario cuando el movimiento ya existe pero las filas por presentación quedaron desfasadas. */
    public function descontarSoloInventarioSiPendiente(
        DetallePedidoDistribucion $detalle,
        PedidoDistribucion $pedido,
    ): void {
        if (! $this->inventarioPendienteDeDescuento($pedido, $detalle)) {
            return;
        }

        $cantidadUnidades = (float) $detalle->cantidad;
        if ($cantidadUnidades <= 0) {
            return;
        }

        $detalle->loadMissing('presentacion.tipoEmpaque', 'insumo.unidadMedida', 'inventarioPresentacionLote');
        $presentacion = $detalle->presentacion;
        $kgMovimiento = $this->kgMovimientoRegistrado($pedido, $detalle)
            ?? ($presentacion
                ? round($cantidadUnidades * $presentacion->pesoNetoKg(), 4)
                : $cantidadUnidades);

        $almacenOrigenId = (int) ($detalle->almacen_mayorista_origenid ?? $detalle->insumo?->almacenid ?? 0);
        if ($almacenOrigenId <= 0) {
            return;
        }

        try {
            if ($detalle->inventario_presentacion_loteid && $detalle->inventarioPresentacionLote) {
                $this->inventarioPresentacion->descontar(
                    $detalle->inventarioPresentacionLote,
                    $cantidadUnidades,
                    $kgMovimiento
                );
            } elseif ($presentacion !== null) {
                $this->inventarioPresentacion->descontarFifo(
                    $almacenOrigenId,
                    (int) $presentacion->insumo_presentacionid,
                    $cantidadUnidades,
                    $kgMovimiento
                );
            }
        } catch (InvalidArgumentException) {
            return;
        }

        if ($presentacion !== null && $detalle->insumo !== null) {
            $this->inventarioPresentacion->sincronizarStockAgregadoInsumo((int) $detalle->insumoid);
        }
    }

    public function inventarioPendienteDeDescuento(
        PedidoDistribucion $pedido,
        DetallePedidoDistribucion $detalle,
    ): bool {
        if (! $this->yaDescontado($pedido, $detalle)) {
            return false;
        }

        $detalle->loadMissing('presentacion', 'insumo', 'inventarioPresentacionLote');
        $movKg = $this->kgMovimientoRegistrado($pedido, $detalle);
        if ($movKg === null || $movKg <= 0) {
            return false;
        }

        $almacenOrigenId = (int) ($detalle->almacen_mayorista_origenid ?? $detalle->insumo?->almacenid ?? 0);
        if ($almacenOrigenId <= 0) {
            return false;
        }

        $invKg = $this->kgInventarioParaDetalle($detalle, $almacenOrigenId);
        $insumoStock = (float) ($detalle->insumo?->stock ?? 0);

        if ($invKg <= $insumoStock + 0.05) {
            return false;
        }

        return abs(($insumoStock + $movKg) - $invKg) < 0.15;
    }

    private function kgMovimientoRegistrado(
        PedidoDistribucion $pedido,
        DetallePedidoDistribucion $detalle,
    ): ?float {
        $almacenOrigenId = (int) ($detalle->almacen_mayorista_origenid ?? $detalle->insumo?->almacenid ?? 0);
        if ($almacenOrigenId <= 0 || (int) $detalle->insumoid <= 0) {
            return null;
        }

        $kg = AlmacenMovimiento::query()
            ->where('insumoid', (int) $detalle->insumoid)
            ->where('almacenid', $almacenOrigenId)
            ->where('referencia', $pedido->numero_solicitud)
            ->where('observaciones', 'like', '%Distribución PDV — salida mayorista%')
            ->value('cantidad');

        return $kg !== null ? (float) $kg : null;
    }

    private function kgInventarioParaDetalle(DetallePedidoDistribucion $detalle, int $almacenOrigenId): float
    {
        if ($detalle->inventario_presentacion_loteid && $detalle->inventarioPresentacionLote) {
            return (float) $detalle->inventarioPresentacionLote->cantidad_kg;
        }

        $presentacion = $detalle->presentacion;
        if ($presentacion === null) {
            return 0.0;
        }

        return $this->inventarioPresentacion->stockTotalKg(
            $almacenOrigenId,
            (int) $presentacion->insumo_presentacionid
        );
    }

    private function validarStockDisponible(
        DetallePedidoDistribucion $detalle,
        $insumoOrigen,
        int $almacenOrigenId,
        $presentacion,
        float $cantidadUnidades,
        float $kgMovimiento,
    ): void {
        if ($detalle->inventario_presentacion_loteid && $detalle->inventarioPresentacionLote) {
            $this->inventarioPresentacion->validarDisponibilidad(
                $detalle->inventarioPresentacionLote,
                $cantidadUnidades,
                $kgMovimiento
            );

            return;
        }

        if ($presentacion !== null) {
            $disponibleUnidades = $this->inventarioPresentacion->stockTotalUnidades(
                $almacenOrigenId,
                (int) $presentacion->insumo_presentacionid
            );
            if ($disponibleUnidades + 0.0001 < $cantidadUnidades) {
                throw new InvalidArgumentException(
                    'Stock insuficiente en origen para «'.$insumoOrigen->nombre.'»: solicitado '
                    .number_format($cantidadUnidades, 0).' '.$presentacion->etiquetaUnidad()
                    .', disponible '.number_format($disponibleUnidades, 0).' '.$presentacion->etiquetaUnidad().'.'
                );
            }

            return;
        }

        if ($kgMovimiento > (float) $insumoOrigen->stock + 0.0001) {
            throw new InvalidArgumentException(
                "Stock insuficiente en origen para «{$insumoOrigen->nombre}». Disponible: {$insumoOrigen->stock} kg."
            );
        }
    }
}
