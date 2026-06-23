<?php

use App\Models\Almacen;
use App\Models\Insumo;
use App\Support\AlmacenAmbito;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasTable('almacen')) {
            return;
        }

        $almacenIds = Almacen::query()
            ->where('activo', true)
            ->where(function ($q) {
                AlmacenAmbito::scope($q, AlmacenAmbito::AGRICOLA);
            })
            ->pluck('almacenid')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($almacenIds === []) {
            return;
        }

        Insumo::query()
            ->whereIn('almacenid', $almacenIds)
            ->where(function ($q) {
                $q->whereNull('descripcion')
                    ->orWhere('descripcion', 'not like', 'Recepción pedido%');
            })
            ->update(['almacenid' => null]);
    }

    public function down(): void
    {
        // Limpieza operativa; no reversible.
    }
};
