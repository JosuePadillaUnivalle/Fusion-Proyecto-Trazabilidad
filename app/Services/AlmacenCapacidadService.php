<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenajeLoteProduccion;
use App\Models\Insumo;
use App\Models\InventarioPresentacionLote;
use App\Models\LoteProduccionPedido;
use App\Models\ProduccionAlmacenamiento;
use App\Models\UnidadMedida;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use App\Support\ProductoPlantaCatalogo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AlmacenCapacidadService
{
    public function convertirAKg(float $cantidad, ?UnidadMedida $unidad, ?string $producto = null): float
    {
        if (! $unidad) {
            return $cantidad;
        }

        $abbr = strtolower(trim($unidad->abreviatura ?? $unidad->nombre ?? ''));

        $factores = [
            'kg' => 1,
            'kilogramo' => 1,
            'kilogramos' => 1,
            'g' => 0.001,
            'gr' => 0.001,
            'gramo' => 0.001,
            't' => 1000,
            'ton' => 1000,
            'tonelada' => 1000,
            'toneladas' => 1000,
            'qq' => 46,
            'quintal' => 46,
            'quintales' => 46,
            'lb' => 0.453592,
            'libra' => 0.453592,
        ];

        foreach ($factores as $clave => $factor) {
            if (str_contains($abbr, $clave)) {
                return $cantidad * $factor;
            }
        }

        return $cantidad;
    }

    public function convertirDesdeKg(float $cantidadKg, ?UnidadMedida $unidad): float
    {
        if (! $unidad || $cantidadKg <= 0) {
            return $cantidadKg;
        }

        $factor = $this->convertirAKg(1.0, $unidad);

        return $factor > 0 ? $cantidadKg / $factor : $cantidadKg;
    }

    public function ocupadoKg(Almacen $almacen): float
    {
        $almacen->loadMissing('unidadMedida');

        if (($almacen->ambito ?? '') === AlmacenAmbito::MAYORISTA) {
            $presentacionKg = $this->inventarioPresentacionKg($almacen);
            if ($presentacionKg > 0) {
                return $presentacionKg;
            }
        }

        $cosechaKg = (float) ProduccionAlmacenamiento::query()
            ->with('unidadMedida')
            ->where('almacenid', $almacen->almacenid)
            ->whereNull('fechasalida')
            ->get()
            ->sum(fn (ProduccionAlmacenamiento $row) => $this->convertirAKg((float) $row->cantidad, $row->unidadMedida));

        $insumoKg = (float) $this->insumosEnAlmacen($almacen)
            ->sum(fn (Insumo $insumo) => $this->convertirAKg((float) $insumo->stock, $insumo->unidadMedida));

        $productoPlantaKg = $this->productoPlantaKgEnAlmacen($almacen);

        return $cosechaKg + $insumoKg + $productoPlantaKg;
    }

    private function inventarioPresentacionKg(Almacen $almacen): float
    {
        if (! Schema::hasTable('inventario_presentacion_lote')) {
            return 0.0;
        }

        return (float) InventarioPresentacionLote::query()
            ->where('almacenid', $almacen->almacenid)
            ->where(function ($q) {
                $q->where('cantidad_kg', '>', 0)
                    ->orWhere('cantidad_unidades', '>', 0);
            })
            ->sum('cantidad_kg');
    }

    public function productoPlantaKgEnAlmacen(Almacen $almacen): float
    {
        $almacenajes = AlmacenajeLoteProduccion::query()
            ->with(['loteProduccionPedido.unidadMedida', 'loteProduccionPedido.materiasPrimas.insumo.unidadMedida'])
            ->whereNull('fecha_retiro')
            ->where('almacenid', $almacen->almacenid)
            ->get();

        $total = 0.0;

        foreach ($almacenajes as $row) {
            $lote = $row->loteProduccionPedido;
            if ($lote === null) {
                continue;
            }

            if (Schema::hasTable('inventario_presentacion_lote')) {
                $invRows = InventarioPresentacionLote::query()
                    ->where('almacenid', $almacen->almacenid)
                    ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
                    ->get();

                if ($invRows->isNotEmpty()) {
                    $total += (float) $invRows->sum('cantidad_kg');

                    continue;
                }
            }

            $kg = ProductoPlantaCatalogo::kgParaAlmacenaje($lote, $this);
            if ($kg > 0) {
                $total += $kg;
            } else {
                $total += $this->convertirAKg((float) $row->cantidad, $lote->unidadMedida);
            }
        }

        return $total;
    }

    public function capacidadKg(Almacen $almacen): float
    {
        $almacen->loadMissing('unidadMedida');

        return $this->convertirAKg((float) ($almacen->capacidad ?? 0), $almacen->unidadMedida);
    }

    /**
     * @return array{ocupado_kg: float, capacidad_kg: float, disponible_kg: float, porcentaje: float}
     */
    public function resumen(Almacen $almacen): array
    {
        $capacidadKg = $this->capacidadKg($almacen);
        $ocupadoKg = $this->ocupadoKg($almacen);
        $disponibleKg = max(0, $capacidadKg - $ocupadoKg);
        $porcentaje = $capacidadKg > 0
            ? min(100, round(($ocupadoKg / $capacidadKg) * 100, 1))
            : 0;

        return [
            'ocupado_kg' => $ocupadoKg,
            'capacidad_kg' => $capacidadKg,
            'disponible_kg' => $disponibleKg,
            'porcentaje' => $porcentaje,
        ];
    }

    public function convertirLoteProduccionAKg(float $cantidad, LoteProduccionPedido $lote): float
    {
        $kg = ProductoPlantaCatalogo::kgParaAlmacenaje($lote, $this);
        if ($kg > 0) {
            return $kg;
        }

        return $this->convertirAKg($cantidad, $lote->unidadMedida);
    }

    public function validarIngresoKg(Almacen $almacen, float $cantidadKg, string $campo = 'cantidad'): void
    {
        if ($cantidadKg <= 0) {
            return;
        }

        $capacidadKg = $this->capacidadKg($almacen);
        if ($capacidadKg <= 0) {
            return;
        }

        $resumen = $this->resumen($almacen);
        if ($cantidadKg > $resumen['disponible_kg'] + 0.001) {
            throw ValidationException::withMessages([
                $campo => 'La cantidad supera la capacidad disponible del almacén «'.$almacen->nombre.'». '
                    .'Capacidad: '.number_format($capacidadKg, 2).' kg · Ocupado: '
                    .number_format($resumen['ocupado_kg'], 2).' kg · Disponible: '
                    .number_format($resumen['disponible_kg'], 2).' kg.',
            ]);
        }
    }

    public function validarOcupacionTrasCambioInsumo(
        Almacen $almacen,
        Insumo $insumo,
        float $nuevoStock,
        string $campo = 'stock'
    ): void {
        if ($nuevoStock < 0) {
            throw ValidationException::withMessages([
                $campo => 'El stock no puede ser negativo.',
            ]);
        }

        $insumo->loadMissing('unidadMedida');
        $stockActualKg = $this->convertirAKg((float) $insumo->stock, $insumo->unidadMedida);
        $nuevoStockKg = $this->convertirAKg($nuevoStock, $insumo->unidadMedida);
        $resumen = $this->resumen($almacen);
        $ocupadoNuevo = $resumen['ocupado_kg'] - $stockActualKg + $nuevoStockKg;
        $capacidadKg = $resumen['capacidad_kg'];

        if ($capacidadKg <= 0 || $ocupadoNuevo <= $capacidadKg + 0.001) {
            return;
        }

        $maximoProductoKg = $resumen['disponible_kg'] + $stockActualKg;

        throw ValidationException::withMessages([
            $campo => 'El stock indicado supera la capacidad del punto de venta. '
                .'Capacidad: '.number_format($capacidadKg, 2).' kg · Ocupado: '
                .number_format($resumen['ocupado_kg'], 2).' kg · Máximo para este producto: '
                .number_format($maximoProductoKg, 2).' kg.',
        ]);
    }

    private function insumosEnAlmacen(Almacen $almacen)
    {
        $query = Insumo::query()
            ->with('unidadMedida')
            ->where('almacenid', $almacen->almacenid);

        if (($almacen->ambito ?? '') === AlmacenAmbito::MAYORISTA) {
            $query = InsumoCatalogo::aplicarFiltroProductoTerminado($query);
        } elseif (($almacen->ambito ?? '') === AlmacenAmbito::PUNTO_VENTA) {
            // Inventario PDV: productos terminados recibidos del mayorista
        } elseif (($almacen->ambito ?? '') === AlmacenAmbito::PLANTA) {
            // Producto empaquetado se contabiliza vía inventario_presentacion_lote / almacenaje de lote
            $query = InsumoCatalogo::aplicarFiltroOperativo($query);
            $query = InsumoCatalogo::aplicarFiltroExcluirProductoTerminado($query);
        } else {
            $query = InsumoCatalogo::aplicarFiltroOperativo($query);
        }

        return $query->get();
    }

    private function nombreProductoLote(?LoteProduccionPedido $lote): ?string
    {
        if ($lote === null) {
            return null;
        }

        return ProductoPlantaCatalogo::nombreProducto($lote);
    }
}
