<?php

use App\Models\Almacen;
use App\Models\Insumo;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * El material de siembra pertenece al inventario agrícola, no a almacenes de planta.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasTable('almacen') || ! Schema::hasColumn('insumo', 'almacenid')) {
            return;
        }

        $tipoIds = InsumoCatalogo::tiposOrdenados()
            ->filter(fn ($t) => InsumoCatalogo::slugFromNombreTipo($t->nombre) === 'material_siembra')
            ->pluck('tipoinsumoid')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($tipoIds === []) {
            return;
        }

        $almacenesInvalidos = Almacen::query()
            ->where('activo', true)
            ->get()
            ->filter(fn (Almacen $a) => in_array(AlmacenAmbito::resolverAmbito($a), [
                AlmacenAmbito::PLANTA,
                AlmacenAmbito::MAYORISTA,
                AlmacenAmbito::PUNTO_VENTA,
            ], true))
            ->pluck('almacenid')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($almacenesInvalidos === []) {
            return;
        }

        Insumo::query()
            ->whereIn('tipoinsumoid', $tipoIds)
            ->whereIn('almacenid', $almacenesInvalidos)
            ->update(['almacenid' => null]);
    }

    public function down(): void
    {
        // Limpieza operativa; no reversible.
    }
};
