<?php

use App\Support\EstadoLoteCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('estadolote_tipo')) {
            return;
        }

        $hasDesc = Schema::hasColumn('estadolote_tipo', 'descripcion');
        $canonicalIds = [];

        foreach (EstadoLoteCatalogo::ESTADOS as $slug => $meta) {
            $candidates = DB::table('estadolote_tipo')
                ->orderBy('estadolotetipoid')
                ->get()
                ->filter(fn ($row) => EstadoLoteCatalogo::mapLegacyNombre($row->nombre) === $slug);

            if ($candidates->isEmpty()) {
                $insert = ['nombre' => $meta['label']];
                if ($hasDesc) {
                    $insert['descripcion'] = $meta['descripcion'];
                }
                $canonicalIds[$slug] = (int) DB::table('estadolote_tipo')->insertGetId($insert, 'estadolotetipoid');

                continue;
            }

            $keepId = (int) $candidates->sortBy('estadolotetipoid')->first()->estadolotetipoid;
            $canonicalIds[$slug] = $keepId;

            foreach ($candidates as $dup) {
                $dupId = (int) $dup->estadolotetipoid;
                if ($dupId === $keepId) {
                    continue;
                }

                DB::table('lote')->where('estadolotetipoid', $dupId)->update(['estadolotetipoid' => $keepId]);

                if (Schema::hasTable('historial_estados_lote')) {
                    DB::table('historial_estados_lote')
                        ->where('estadolotetipoid', $dupId)
                        ->update(['estadolotetipoid' => $keepId]);
                }

                DB::table('estadolote_tipo')->where('estadolotetipoid', $dupId)->delete();
            }
        }

        foreach (EstadoLoteCatalogo::ESTADOS as $slug => $meta) {
            $update = ['nombre' => $meta['label']];
            if ($hasDesc) {
                $update['descripcion'] = $meta['descripcion'];
            }
            DB::table('estadolote_tipo')
                ->where('estadolotetipoid', $canonicalIds[$slug])
                ->update($update);
        }

        DB::table('estadolote_tipo')
            ->whereNotIn('estadolotetipoid', array_values($canonicalIds))
            ->delete();
    }

    public function down(): void
    {
        // No revertir consolidación de catálogo.
    }
};
