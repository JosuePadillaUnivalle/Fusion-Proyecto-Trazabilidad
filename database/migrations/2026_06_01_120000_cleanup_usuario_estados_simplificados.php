<?php

use App\Models\Usuario;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuario')) {
            return;
        }

        Usuario::query()->where('estado_cuenta', 'rechazado')->delete();

        Usuario::query()
            ->where(function ($q) {
                $q->whereNull('estado_cuenta')->orWhere('estado_cuenta', 'aprobado');
            })
            ->where('activo', false)
            ->update(['activo' => true]);
    }

    public function down(): void
    {
        // Sin reversión: usuarios rechazados eliminados no se restauran.
    }
};
