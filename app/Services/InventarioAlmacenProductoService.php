<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\InventarioPresentacionLote;
use App\Support\InsumoImagenCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class InventarioAlmacenProductoService
{
    public function __construct(
        private readonly InventarioPresentacionService $presentaciones
    ) {}

    /**
     * Elimina un producto del almacén sin afectar otros insumos que compartan presentaciones por error de datos.
     */
    public function eliminarProducto(Almacen $almacen, Insumo $insumo): void
    {
        $almacenId = (int) $almacen->almacenid;
        $insumoId = (int) $insumo->insumoid;

        DB::transaction(function () use ($almacenId, $insumoId, $insumo) {
            $presentacionIds = InsumoPresentacion::query()
                ->where('insumoid', $insumoId)
                ->pluck('insumo_presentacionid');

            foreach ($presentacionIds as $presentacionId) {
                $this->desvincularPresentacionDeOtrosInsumos((int) $presentacionId, $insumoId);
            }

            InventarioPresentacionLote::query()
                ->where('almacenid', $almacenId)
                ->where('insumoid', $insumoId)
                ->delete();

            InsumoPresentacion::query()
                ->where('insumoid', $insumoId)
                ->each(function (InsumoPresentacion $presentacion) use ($insumoId) {
                    if ($this->presentacionSoloDelInsumo($presentacion, $insumoId)) {
                        $presentacion->delete();
                    }
                });

            AlmacenMovimiento::query()
                ->where('almacenid', $almacenId)
                ->where('insumoid', $insumoId)
                ->delete();

            if (Schema::hasTable('catalogo_tamano_conteo')) {
                DB::table('catalogo_tamano_conteo')->where('insumoid', $insumoId)->delete();
            }

            if (Schema::hasTable('detalle_pedido_distribucion') && Schema::hasColumn('detalle_pedido_distribucion', 'insumo_planta_referenciaid')) {
                DB::table('detalle_pedido_distribucion')
                    ->where('insumo_planta_referenciaid', $insumoId)
                    ->update(['insumo_planta_referenciaid' => null]);
            }

            if (Schema::hasTable('solicitud_produccion_planta') && Schema::hasColumn('solicitud_produccion_planta', 'insumo_planta_referenciaid')) {
                DB::table('solicitud_produccion_planta')
                    ->where('insumo_planta_referenciaid', $insumoId)
                    ->update(['insumo_planta_referenciaid' => null]);
            }

            if (Schema::hasTable('punto_venta_stock') && Schema::hasColumn('punto_venta_stock', 'insumoid')) {
                DB::table('punto_venta_stock')->where('insumoid', $insumoId)->update(['insumoid' => null]);
            }

            $this->eliminarImagenSubida($insumo->imagenurl);

            Insumo::query()->whereKey($insumoId)->delete();
        });
    }

    /**
     * Evita que al borrar la presentación del insumo objetivo se eliminen lotes de otros productos (cascada FK).
     */
    private function desvincularPresentacionDeOtrosInsumos(int $presentacionId, int $insumoObjetivoId): void
    {
        $presentacion = InsumoPresentacion::query()->find($presentacionId);
        if ($presentacion === null) {
            return;
        }

        InventarioPresentacionLote::query()
            ->where('insumo_presentacionid', $presentacionId)
            ->where('insumoid', '!=', $insumoObjetivoId)
            ->with('insumo')
            ->get()
            ->each(function (InventarioPresentacionLote $lote) use ($presentacion) {
                $insumoAjeno = $lote->insumo;
                if ($insumoAjeno === null) {
                    return;
                }

                $reemplazo = InsumoPresentacion::query()
                    ->where('insumoid', $insumoAjeno->insumoid)
                    ->where('insumo_presentacionid', '!=', $presentacion->insumo_presentacionid)
                    ->where('nombre', $presentacion->nombre)
                    ->where('activo', true)
                    ->first();

                if ($reemplazo === null) {
                    $reemplazo = $this->presentaciones->replicarPresentacionEnInsumo($presentacion, $insumoAjeno);
                }

                $lote->update(['insumo_presentacionid' => $reemplazo->insumo_presentacionid]);
            });
    }

    private function presentacionSoloDelInsumo(InsumoPresentacion $presentacion, int $insumoId): bool
    {
        if ((int) $presentacion->insumoid !== $insumoId) {
            return false;
        }

        return ! InventarioPresentacionLote::query()
            ->where('insumo_presentacionid', $presentacion->insumo_presentacionid)
            ->where('insumoid', '!=', $insumoId)
            ->exists();
    }

    private function eliminarImagenSubida(?string $imagenurl): void
    {
        $ruta = InsumoImagenCatalogo::rutaAlmacenamiento($imagenurl);
        if ($ruta !== null && Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }
}
