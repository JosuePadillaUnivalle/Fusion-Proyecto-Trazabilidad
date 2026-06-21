<?php

use App\Models\Insumo;
use App\Services\InsumoEliminacionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<int, int> duplicado => canónico */
    private array $fusiones = [
        54 => 60,  // Cebolla
        70 => 62,  // Lechuga
        61 => 55,  // Tomate
        69 => 47,  // Zanahoria
        26 => 22,  // Papa industrial Monalisa
        20 => 51,  // Fungicida cobre hidróxido
        68 => 67,  // Papas fritas (producto terminado)
    ];

    /** @var list<int> frutas / cultivos fuera de alcance verduras */
    private array $eliminarIds = [
        36, // Mango Tommy
        35, // Naranja Valencia
        34, // Maíz grano amarillo
        33, // Mandioca fresca
    ];

    public function up(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        $svc = app(InsumoEliminacionService::class);

        foreach ($this->fusiones as $desdeId => $haciaId) {
            $desde = Insumo::query()->find($desdeId);
            $hacia = Insumo::query()->find($haciaId);
            if (! $desde || ! $hacia) {
                continue;
            }

            $hacia->stock = (float) $hacia->stock + (float) $desde->stock;
            if ($hacia->dosis_por_ha === null && $desde->dosis_por_ha !== null) {
                $hacia->dosis_por_ha = $desde->dosis_por_ha;
                $hacia->dosis_unidad = $desde->dosis_unidad;
            }
            $hacia->save();

            $svc->fusionarEn($desdeId, $haciaId);
        }

        foreach ($this->eliminarIds as $insumoId) {
            $insumo = Insumo::query()->find($insumoId);
            if ($insumo) {
                $svc->eliminar($insumo);
            }
        }

        Insumo::query()->each(function (Insumo $insumo): void {
            InsumoEliminacionService::aplicarDosisReferencia($insumo, true);
        });
    }

    public function down(): void
    {
        // Limpieza de datos demo; no reversible.
    }
};
