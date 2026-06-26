<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\InventarioPresentacionLote;
use App\Models\ProduccionAlmacenamiento;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Services\AlmacenCapacidadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AlmacenEliminacionCatalogo
{
    /** @return array{ok: bool, titulo: string, mensaje: string} */
    public static function evaluar(Almacen $almacen): array
    {
        $ocupadoKg = app(AlmacenCapacidadService::class)->ocupadoKg($almacen);

        if ($ocupadoKg > 0.001) {
            return [
                'ok' => false,
                'titulo' => 'Almacén con stock',
                'mensaje' => 'No se puede eliminar «'.$almacen->nombre.'» mientras tenga inventario ('
                    .number_format($ocupadoKg, 2, ',', '.').' kg). '
                    .'Traslade o consuma todos los productos antes de eliminar el almacén.',
            ];
        }

        return ['ok' => true, 'titulo' => '', 'mensaje' => ''];
    }

    public static function eliminar(Almacen $almacen): void
    {
        $eval = self::evaluar($almacen);
        if (! $eval['ok']) {
            throw new \RuntimeException($eval['mensaje']);
        }

        $almacenId = (int) $almacen->almacenid;

        DB::transaction(function () use ($almacen, $almacenId): void {
            self::desvincularReferenciasHistoricas($almacenId);

            $almacen->delete();
        });
    }

    private static function desvincularReferenciasHistoricas(int $almacenId): void
    {
        AlmacenMovimiento::query()
            ->where('almacenid', $almacenId)
            ->delete();

        if (Schema::hasTable('inventario_presentacion_lote')) {
            InventarioPresentacionLote::query()
                ->where('almacenid', $almacenId)
                ->delete();
        }

        ProduccionAlmacenamiento::query()
            ->where('almacenid', $almacenId)
            ->where(function ($q) {
                $q->where('cantidad', '<=', 0)
                    ->orWhereNotNull('fechasalida');
            })
            ->delete();

        if (Schema::hasTable('ruta_distribucion_parada')) {
            RutaDistribucionParada::query()
                ->where('almacenid', $almacenId)
                ->update(['almacenid' => null]);
        }

        if (Schema::hasTable('ruta_distribucion')) {
            RutaDistribucion::query()
                ->where('almacen_mayorista_origenid', $almacenId)
                ->update(['almacen_mayorista_origenid' => null]);

            RutaDistribucion::query()
                ->where('almacen_planta_origenid', $almacenId)
                ->update(['almacen_planta_origenid' => null]);
        }

        if (Schema::hasTable('distribucion_ingreso')) {
            DB::table('distribucion_ingreso')->where('almacenid', $almacenId)->delete();
        }

        if (Schema::hasTable('distribucion_salida')) {
            DB::table('distribucion_salida')->where('almacenid', $almacenId)->delete();
        }
    }
}
