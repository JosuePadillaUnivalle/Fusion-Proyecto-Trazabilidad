<?php

namespace App\Support;

use App\Models\AlmacenMovimiento;
use App\Models\DetallePedido;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\ProduccionAlmacenamiento;
use App\Models\TipoMovimientoAlmacen;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PedidoReservaService
{
    /**
     * @return array<int, string>
     */
    public function verificarDisponibilidad(Pedido $pedido): array
    {
        $pedido->loadMissing('detalles.insumo');
        $errores = [];

        foreach ($pedido->detalles as $detalle) {
            $error = $this->verificarDetalle($detalle);
            if ($error !== null) {
                $errores[] = $error;
            }
        }

        return $errores;
    }

    public function reservar(Pedido $pedido): void
    {
        $errores = $this->verificarDisponibilidad($pedido);
        if ($errores !== []) {
            throw new InvalidArgumentException(implode(' ', $errores));
        }

        DB::transaction(function () use ($pedido) {
            foreach ($pedido->detalles as $detalle) {
                $this->reservarDetalle($detalle, $pedido->numero_solicitud);
            }
        });
    }

    private function verificarDetalle(DetallePedido $detalle): ?string
    {
        $ref = $detalle->producto_ref ?? '';
        $cantidad = (float) $detalle->cantidad;
        $label = $detalle->cultivo_personalizado ?: 'Producto';

        if (str_starts_with($ref, 'insumo:') || $detalle->insumoid) {
            $insumo = $detalle->insumoid
                ? Insumo::query()->find($detalle->insumoid)
                : Insumo::query()->find((int) substr($ref, 7));

            if (! $insumo) {
                return "No se encontró el insumo solicitado para «{$label}».";
            }
            if (! $insumo->tieneStockSuficiente($cantidad)) {
                $disp = number_format((float) $insumo->stock, 2);

                return "Stock insuficiente de «{$insumo->nombre}» (disponible: {$disp}).";
            }

            return null;
        }

        if (str_starts_with($ref, 'cosecha:') || $detalle->produccionalmacenamientoid) {
            $cosecha = $this->resolverCosecha($detalle);
            if (! $cosecha) {
                return "No se encontró la cosecha en almacén para «{$label}».";
            }
            if ($cosecha->fechasalida !== null) {
                return "La cosecha «{$label}» ya fue retirada del almacén agrícola.";
            }
            if ((float) $cosecha->cantidad < $cantidad) {
                $disp = number_format((float) $cosecha->cantidad, 2);

                return "Cantidad insuficiente de cosecha «{$label}» (disponible: {$disp}).";
            }

            return null;
        }

        return null;
    }

    private function reservarDetalle(DetallePedido $detalle, string $numeroSolicitud): void
    {
        $ref = $detalle->producto_ref ?? '';
        $cantidad = (float) $detalle->cantidad;

        if (str_starts_with($ref, 'insumo:') || $detalle->insumoid) {
            $insumo = $detalle->insumoid
                ? Insumo::query()->lockForUpdate()->findOrFail($detalle->insumoid)
                : Insumo::query()->lockForUpdate()->findOrFail((int) substr($ref, 7));

            $this->registrarSalidaInsumo($insumo, $cantidad, $numeroSolicitud);

            return;
        }

        if (str_starts_with($ref, 'cosecha:') || $detalle->produccionalmacenamientoid) {
            $cosecha = $this->resolverCosecha($detalle);
            if (! $cosecha) {
                return;
            }

            $cosecha = ProduccionAlmacenamiento::query()->lockForUpdate()->findOrFail($cosecha->produccionalmacenamientoid);
            $nuevaCantidad = (float) $cosecha->cantidad - $cantidad;

            if ($nuevaCantidad <= 0.0001) {
                $cosecha->update([
                    'cantidad' => 0,
                    'fechasalida' => now(),
                    'observaciones' => trim(($cosecha->observaciones ?? '')." Reservado para pedido {$numeroSolicitud}."),
                ]);
            } else {
                $cosecha->update([
                    'cantidad' => $nuevaCantidad,
                    'observaciones' => trim(($cosecha->observaciones ?? '')." Reserva parcial ({$cantidad}) pedido {$numeroSolicitud}."),
                ]);
            }
        }
    }

    private function registrarSalidaInsumo(Insumo $insumo, float $cantidad, string $numeroSolicitud): void
    {
        $tipoSalida = TipoMovimientoAlmacen::activosPorNaturaleza('salida')->first();

        if ($tipoSalida && $insumo->almacenid) {
            AlmacenMovimiento::create([
                'almacenid' => $insumo->almacenid,
                'insumoid' => $insumo->insumoid,
                'tipo_movimiento_almacenid' => $tipoSalida->tipo_movimiento_almacenid,
                'fecha' => now(),
                'cantidad' => $cantidad,
                'referencia' => $numeroSolicitud,
                'destino_motivo' => 'Reserva pedido planta',
                'observaciones' => 'Salida automática al aceptar pedido de planta.',
                'usuarioid' => auth()->id(),
            ]);
        }

        $insumo->decrementarStock($cantidad);
    }

    private function resolverCosecha(DetallePedido $detalle): ?ProduccionAlmacenamiento
    {
        if ($detalle->produccionalmacenamientoid) {
            return ProduccionAlmacenamiento::query()->find($detalle->produccionalmacenamientoid);
        }

        $ref = $detalle->producto_ref ?? '';
        if (! str_starts_with($ref, 'cosecha:')) {
            return null;
        }

        return ProduccionAlmacenamiento::query()->find((int) substr($ref, 8));
    }
}
