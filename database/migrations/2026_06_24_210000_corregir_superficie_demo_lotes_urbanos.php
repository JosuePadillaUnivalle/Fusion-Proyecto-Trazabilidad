<?php

use App\Models\Lote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Superficies demo infladas (~15 ha) → parcelas urbanas realistas en ha. */
    private const AJUSTES = [
        'Lote de Zanahoria' => 0.85,
        'Lote Zanahoria Imperator' => 0.92,
        'Lote Lechuga Crespa Equipetrol' => 1.10,
        'Cebolla Amarilla' => 0.95,
        'Tomate' => 0.78,
        'Tomate 2' => 0.88,
    ];

    public function up(): void
    {
        if (! Schema::hasTable('lote')) {
            return;
        }

        foreach (self::AJUSTES as $nombre => $hectareas) {
            Lote::query()
                ->where('nombre', $nombre)
                ->where('superficie', '>=', 5)
                ->update([
                    'superficie' => $hectareas,
                    'fechamodificacion' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Datos demo: no revertir automáticamente.
    }
};
