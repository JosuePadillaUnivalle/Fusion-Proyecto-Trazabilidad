<?php

use App\Models\Almacen;
use App\Models\Lote;
use App\Models\Usuario;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuario') || ! Schema::hasTable('lote')) {
            return;
        }

        $luis = Usuario::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', ['luisguerrero123@gmail.com'])
            ->first();

        $jannik = Usuario::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(email)) = ?', ['janniksinner@gmail.com'])
                    ->orWhereRaw('LOWER(TRIM(email)) = ?', ['janniksinener@gmail.com']);
            })
            ->orWhere(function ($q) {
                $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['jannik'])
                    ->where(function ($q2) {
                        $q2->whereRaw('LOWER(TRIM(apellido)) IN (?, ?)', ['sinner', 'sinener']);
                    });
            })
            ->first();

        if (! $luis || ! $jannik) {
            return;
        }

        if (strcasecmp(trim($jannik->apellido ?? ''), 'Sinner') !== 0) {
            DB::table('usuario')
                ->where('usuarioid', $jannik->usuarioid)
                ->update(['apellido' => 'Sinner', 'fechamodificacion' => now()]);
        }

        Lote::query()
            ->where('usuarioid', $luis->usuarioid)
            ->update([
                'usuarioid' => $jannik->usuarioid,
                'fechamodificacion' => now(),
            ]);

        if (Schema::hasColumn('usuario', 'supervisor_usuarioid')) {
            DB::table('usuario')
                ->where('usuarioid', $luis->usuarioid)
                ->update(['supervisor_usuarioid' => $jannik->usuarioid]);
        }

        if (Schema::hasTable('almacen') && Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            Almacen::query()
                ->where('responsable_usuarioid', $luis->usuarioid)
                ->update(['responsable_usuarioid' => $jannik->usuarioid]);
        }
    }

    public function down(): void
    {
        // Datos demo: no revertir automáticamente.
    }
};
