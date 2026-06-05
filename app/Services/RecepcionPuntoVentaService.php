<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\PedidoDistribucion;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\Usuario;
use App\Support\InsumoCatalogo;
use App\Support\PedidoDistribucionCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecepcionPuntoVentaService
{
    public function confirmar(PedidoDistribucion $pedido, Usuario $usuario): void
    {
        if (! PedidoDistribucionCatalogo::puedeConfirmarRecepcion($pedido)) {
            throw new \InvalidArgumentException('El pedido no está en tránsito o ya fue recibido.');
        }

        $pedido->load(['detalles.insumo.unidadMedida', 'puntoVenta.almacen']);

        $puntoVenta = $pedido->puntoVenta;
        if ($puntoVenta === null) {
            throw new \InvalidArgumentException('Pedido sin punto de venta asociado.');
        }

        app(PuntoVentaAlmacenService::class)->crearAlmacenParaPuntoVenta($puntoVenta);
        $puntoVenta->refresh();

        $almacenPdv = $puntoVenta->almacen;
        if ($almacenPdv === null) {
            throw new \InvalidArgumentException('No se pudo vincular el almacén del punto de venta.');
        }

        $tipoIngreso = TipoMovimientoAlmacen::activosPorNaturaleza('ingreso')->firstOrFail();
        $tipoSalida = TipoMovimientoAlmacen::activosPorNaturaleza('salida')->firstOrFail();

        DB::transaction(function () use ($pedido, $usuario, $almacenPdv, $tipoIngreso, $tipoSalida) {
            foreach ($pedido->detalles as $detalle) {
                $this->transferirDetalle($detalle, $pedido, $usuario, $almacenPdv, $tipoIngreso, $tipoSalida);
            }

            $pedido->update([
                'estado' => PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
                'fecha_recepcion' => now(),
            ]);
        });
    }

    private function transferirDetalle(
        DetallePedidoDistribucion $detalle,
        PedidoDistribucion $pedido,
        Usuario $usuario,
        Almacen $almacenPdv,
        TipoMovimientoAlmacen $tipoIngreso,
        TipoMovimientoAlmacen $tipoSalida
    ): void {
        $cantidad = (float) $detalle->cantidad;
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('Cantidad inválida en el detalle del pedido.');
        }

        $insumoOrigen = $detalle->insumo;
        if ($insumoOrigen === null) {
            throw new \InvalidArgumentException('Producto de planta no encontrado.');
        }

        if (! $insumoOrigen->tieneStockSuficiente($cantidad)) {
            throw new \InvalidArgumentException(
                "Stock insuficiente en planta para «{$insumoOrigen->nombre}». Disponible: {$insumoOrigen->stock}."
            );
        }

        $insumoDestino = Insumo::query()
            ->where('almacenid', $almacenPdv->almacenid)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($insumoOrigen->nombre))])
            ->first();

        if ($insumoDestino === null) {
            $insumoDestino = Insumo::create([
                'nombre' => $insumoOrigen->nombre,
                'tipoinsumoid' => $insumoOrigen->tipoinsumoid ?? TipoInsumo::query()->value('tipoinsumoid'),
                'unidadmedidaid' => $insumoOrigen->unidadmedidaid,
                'stock' => 0,
                'stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
                'descripcion' => 'Producto recibido desde planta — '.$pedido->numero_solicitud,
                'almacenid' => $almacenPdv->almacenid,
            ]);
        }

        $ref = $pedido->numero_solicitud;

        AlmacenMovimiento::create([
            'almacenid' => $insumoOrigen->almacenid,
            'insumoid' => $insumoOrigen->insumoid,
            'tipo_movimiento_almacenid' => $tipoSalida->tipo_movimiento_almacenid,
            'usuarioid' => $usuario->usuarioid,
            'fecha' => now()->toDateString(),
            'cantidad' => $cantidad,
            'referencia' => $ref,
            'destino_motivo' => $almacenPdv->nombre,
            'observaciones' => '[Distribución PDV — salida planta] '.$ref,
        ]);

        AlmacenMovimiento::create([
            'almacenid' => $almacenPdv->almacenid,
            'insumoid' => $insumoDestino->insumoid,
            'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
            'usuarioid' => $usuario->usuarioid,
            'fecha' => now()->toDateString(),
            'cantidad' => $cantidad,
            'referencia' => $ref,
            'destino_motivo' => $almacenPdv->nombre,
            'observaciones' => '[Recepción PDV] '.$ref,
        ]);

        $insumoOrigen->decrementarStock($cantidad);
        $insumoDestino->incrementarStock($cantidad);
    }
}
