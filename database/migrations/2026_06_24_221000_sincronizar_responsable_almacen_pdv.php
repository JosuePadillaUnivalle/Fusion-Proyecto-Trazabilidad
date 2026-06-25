<?php

use App\Models\Almacen;
use App\Models\PuntoVenta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            return;
        }

        if (! Schema::hasTable('puntoventa')) {
            return;
        }

        PuntoVenta::query()
            ->whereNotNull('almacenid')
            ->whereNotNull('usuarioid')
            ->orderBy('puntoventaid')
            ->each(function (PuntoVenta $punto): void {
                Almacen::query()
                    ->where('almacenid', $punto->almacenid)
                    ->where(function ($q) use ($punto) {
                        $q->whereNull('responsable_usuarioid')
                            ->orWhere('responsable_usuarioid', 0)
                            ->orWhere('responsable_usuarioid', '!=', (int) $punto->usuarioid);
                    })
                    ->update(['responsable_usuarioid' => (int) $punto->usuarioid]);
            });
    }

    public function down(): void
    {
        // Datos demo: no revertir automáticamente.
    }
};
