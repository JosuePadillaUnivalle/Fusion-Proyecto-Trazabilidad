<?php

use App\Models\Almacen;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            return;
        }

        $jannik = Usuario::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(email)) IN (?, ?)', ['janniksinner@gmail.com', 'janniksinener@gmail.com'])
                    ->orWhere(function ($q2) {
                        $q2->whereRaw('LOWER(TRIM(nombre)) = ?', ['jannik'])
                            ->whereRaw('LOWER(TRIM(apellido)) IN (?, ?)', ['sinner', 'sinener']);
                    });
            })
            ->first();

        if ($jannik === null) {
            return;
        }

        AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::AGRICOLA)
            ->update([
                'responsable_usuarioid' => (int) $jannik->usuarioid,
            ]);
    }

    public function down(): void
    {
        // Datos demo: no revertir automáticamente.
    }
};
