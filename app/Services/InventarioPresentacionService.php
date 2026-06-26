<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\InventarioPresentacionLote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class InventarioPresentacionService
{
    /** @return Collection<int, InventarioPresentacionLote> */
    public function lotesDisponibles(int $almacenId, int $presentacionId): Collection
    {
        return InventarioPresentacionLote::query()
            ->with(['presentacion.tipoEmpaque', 'loteProduccion'])
            ->where('almacenid', $almacenId)
            ->where('insumo_presentacionid', $presentacionId)
            ->where('cantidad_unidades', '>', 0)
            ->orderBy('referencia_lote')
            ->orderBy('inventario_presentacion_loteid')
            ->get();
    }

    public function stockTotalUnidades(int $almacenId, int $presentacionId): float
    {
        return (float) InventarioPresentacionLote::query()
            ->where('almacenid', $almacenId)
            ->where('insumo_presentacionid', $presentacionId)
            ->sum('cantidad_unidades');
    }

    public function stockTotalKg(int $almacenId, int $presentacionId): float
    {
        return (float) InventarioPresentacionLote::query()
            ->where('almacenid', $almacenId)
            ->where('insumo_presentacionid', $presentacionId)
            ->sum('cantidad_kg');
    }

    public function obtenerLote(int $inventarioId, int $almacenId, int $presentacionId): InventarioPresentacionLote
    {
        $lote = InventarioPresentacionLote::query()
            ->with('presentacion')
            ->where('inventario_presentacion_loteid', $inventarioId)
            ->where('almacenid', $almacenId)
            ->where('insumo_presentacionid', $presentacionId)
            ->first();

        if ($lote === null) {
            throw new InvalidArgumentException('El lote de inventario seleccionado no es válido.');
        }

        return $lote;
    }

    public function validarDisponibilidad(InventarioPresentacionLote $inventario, float $unidades, float $kg): void
    {
        if ($unidades <= 0) {
            throw new InvalidArgumentException('La cantidad en unidades debe ser mayor a cero.');
        }

        if (! $inventario->tieneStockSuficiente($unidades)) {
            $etiqueta = $inventario->presentacion?->nombre ?? 'presentación';
            throw new InvalidArgumentException(
                'Stock insuficiente en lote «'.$inventario->etiquetaLote().'» para «'.$etiqueta.'»: '
                .'solicitado '.number_format($unidades, 0).' unidades, '
                .'disponible '.number_format((float) $inventario->cantidad_unidades, 0).' unidades.'
            );
        }

        $stockUnidades = (float) $inventario->cantidad_unidades;
        if ($unidades >= $stockUnidades - 0.0001) {
            return;
        }

        if ($kg > (float) $inventario->cantidad_kg + 0.0001) {
            throw new InvalidArgumentException(
                'Stock insuficiente en kg para el lote «'.$inventario->etiquetaLote().'».'
            );
        }
    }

    public function descontar(InventarioPresentacionLote $inventario, float $unidades, float $kg): void
    {
        $this->validarDisponibilidad($inventario, $unidades, $kg);

        $stockUnidades = (float) $inventario->cantidad_unidades;
        $kgDescontar = $unidades >= $stockUnidades - 0.0001
            ? (float) $inventario->cantidad_kg
            : min($kg, (float) $inventario->cantidad_kg);

        $inventario->decrement('cantidad_unidades', $unidades);
        $inventario->decrement('cantidad_kg', $kgDescontar);

        $this->sincronizarStockAgregadoInsumo((int) $inventario->insumoid);
    }

    public function ingresar(
        int $almacenId,
        int $insumoId,
        int $presentacionId,
        ?int $loteProduccionId,
        ?string $referenciaLote,
        float $unidades,
        float $kg
    ): InventarioPresentacionLote {
        if ($unidades <= 0 || $kg <= 0) {
            throw new InvalidArgumentException('Las cantidades de ingreso deben ser mayores a cero.');
        }

        $query = InventarioPresentacionLote::query()
            ->where('almacenid', $almacenId)
            ->where('insumo_presentacionid', $presentacionId);

        if ($loteProduccionId !== null) {
            $query->where('loteproduccionpedidoid', $loteProduccionId);
        } elseif ($referenciaLote !== null && $referenciaLote !== '') {
            $query->where('referencia_lote', $referenciaLote)->whereNull('loteproduccionpedidoid');
        } else {
            $query->whereNull('loteproduccionpedidoid')->whereNull('referencia_lote');
        }

        $existente = $query->first();

        if ($existente !== null) {
            $existente->increment('cantidad_unidades', $unidades);
            $existente->increment('cantidad_kg', $kg);
            $this->sincronizarStockAgregadoInsumo($insumoId);

            return $existente->fresh();
        }

        $inventario = InventarioPresentacionLote::create([
            'almacenid' => $almacenId,
            'insumoid' => $insumoId,
            'insumo_presentacionid' => $presentacionId,
            'loteproduccionpedidoid' => $loteProduccionId,
            'referencia_lote' => $referenciaLote,
            'cantidad_unidades' => $unidades,
            'cantidad_kg' => $kg,
        ]);

        $this->sincronizarStockAgregadoInsumo($insumoId);

        return $inventario;
    }

    public function sincronizarStockAgregadoInsumo(int $insumoId): void
    {
        $insumo = Insumo::query()->find($insumoId);
        if ($insumo === null || ! $insumo->almacenid) {
            return;
        }

        $totalKg = (float) InventarioPresentacionLote::query()
            ->where('insumoid', $insumoId)
            ->where('almacenid', $insumo->almacenid)
            ->sum('cantidad_kg');

        if ($totalKg > 0) {
            $insumo->update(['stock' => round($totalKg, 4)]);

            return;
        }

        $presentacionesActivas = InsumoPresentacion::query()
            ->where('insumoid', $insumoId)
            ->where('activo', true)
            ->exists();

        if ($presentacionesActivas) {
            $insumo->update(['stock' => 0]);
        }
    }

    /**
     * Si hay presentaciones activas pero sin filas de inventario, reparte el stock agregado del insumo.
     */
    public function asegurarInventarioDesdeStock(int $almacenId, int $insumoId): void
    {
        if (! Schema::hasTable('inventario_presentacion_lote')) {
            return;
        }

        $insumo = Insumo::query()->find($insumoId);
        if ($insumo === null || (int) $insumo->almacenid !== $almacenId || (float) $insumo->stock <= 0) {
            return;
        }

        $presentaciones = InsumoPresentacion::query()
            ->where('insumoid', $insumoId)
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        if ($presentaciones->isEmpty()) {
            return;
        }

        $tieneInventario = InventarioPresentacionLote::query()
            ->where('almacenid', $almacenId)
            ->where('insumoid', $insumoId)
            ->where('cantidad_unidades', '>', 0)
            ->exists();

        if ($tieneInventario) {
            return;
        }

        $shareKg = (float) $insumo->stock / $presentaciones->count();
        $distribucion = [];

        foreach ($presentaciones->values() as $idx => $presentacion) {
            $unidades = (int) floor($shareKg / $presentacion->pesoNetoKg());
            if ($unidades <= 0) {
                continue;
            }
            $distribucion[] = [
                'presentacion' => $presentacion,
                'unidades' => (float) $unidades,
                'referencia_lote' => 'AUTO-'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                'partes' => 2,
            ];
        }

        if ($distribucion === []) {
            return;
        }

        $this->bootstrapDesdeStockAgregado($almacenId, $insumo, $distribucion);
    }

    /**
     * Reparte el stock agregado del insumo en filas de inventario por presentación (demo / bootstrap).
     *
     * @param  list<array{presentacion: InsumoPresentacion, unidades: float}>  $distribucion
     */
    public function bootstrapDesdeStockAgregado(int $almacenId, Insumo $insumo, array $distribucion): void
    {
        DB::transaction(function () use ($almacenId, $insumo, $distribucion) {
            InventarioPresentacionLote::query()
                ->where('almacenid', $almacenId)
                ->where('insumoid', $insumo->insumoid)
                ->delete();

            foreach ($distribucion as $item) {
                /** @var InsumoPresentacion $presentacion */
                $presentacion = $item['presentacion'];
                $unidades = (float) $item['unidades'];
                if ($unidades <= 0) {
                    continue;
                }

                $kg = round($unidades * $presentacion->pesoNetoKg(), 4);
                $referencia = $item['referencia_lote'] ?? null;
                $partes = $item['partes'] ?? 1;

                if ($partes > 1 && $unidades >= $partes) {
                    $base = (int) floor($unidades / $partes);
                    $resto = (int) round($unidades - ($base * $partes));
                    for ($i = 0; $i < $partes; $i++) {
                        $u = $base + ($i === $partes - 1 ? $resto : 0);
                        if ($u <= 0) {
                            continue;
                        }
                        $this->ingresar(
                            $almacenId,
                            (int) $insumo->insumoid,
                            (int) $presentacion->insumo_presentacionid,
                            null,
                            $referencia ? $referencia.'-'.chr(65 + $i) : 'LOTE-'.($i + 1),
                            (float) $u,
                            round($u * $presentacion->pesoNetoKg(), 4)
                        );
                    }

                    continue;
                }

                $this->ingresar(
                    $almacenId,
                    (int) $insumo->insumoid,
                    (int) $presentacion->insumo_presentacionid,
                    null,
                    $referencia,
                    $unidades,
                    $kg
                );
            }

            $this->sincronizarStockAgregadoInsumo((int) $insumo->insumoid);
        });
    }

    public function replicarPresentacionEnInsumo(InsumoPresentacion $origen, Insumo $insumoDestino): InsumoPresentacion
    {
        return InsumoPresentacion::query()->updateOrCreate(
            [
                'insumoid' => $insumoDestino->insumoid,
                'nombre' => $origen->nombre,
            ],
            [
                'tipoempaqueid' => $origen->tipoempaqueid,
                'tipo_envase' => $origen->tipo_envase,
                'peso_neto_kg' => $origen->peso_neto_kg,
                'unidades_por_caja' => $origen->unidades_por_caja,
                'sku' => $origen->sku,
                'codigo_barras' => $origen->codigo_barras,
                'orden' => $origen->orden,
                'activo' => true,
            ]
        );
    }
}
