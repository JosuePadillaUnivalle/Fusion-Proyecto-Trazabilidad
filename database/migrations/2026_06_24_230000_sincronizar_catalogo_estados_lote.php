<?php

use App\Models\EstadoLoteTipo;
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

        $hasDescripcion = Schema::hasColumn('estadolote_tipo', 'descripcion');

        foreach (EstadoLoteCatalogo::ESTADOS as $slug => $meta) {
            $data = ['nombre' => $meta['label']];
            if ($hasDescripcion) {
                $data['descripcion'] = $meta['descripcion'];
            }

            $existente = EstadoLoteTipo::query()
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($meta['label'])])
                ->orWhereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(str_replace('_', ' ', $slug))])
                ->first();

            if ($existente) {
                $existente->update($data);

                continue;
            }

            $legacy = match ($slug) {
                'planificado' => ['planificado', 'disponible', 'planificación', 'planificacion'],
                'en_crecimiento' => ['en producción', 'en produccion'],
                'finalizado' => ['en descanso', 'archivado'],
                default => [],
            };

            $renombrado = false;
            foreach ($legacy as $nombreLegacy) {
                $fila = EstadoLoteTipo::query()
                    ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombreLegacy)])
                    ->first();
                if ($fila) {
                    $fila->update($data);
                    $renombrado = true;
                    break;
                }
            }

            if (! $renombrado) {
                EstadoLoteTipo::query()->create($data);
            }
        }

        DB::table('estadolote_tipo')
            ->whereRaw('LOWER(TRIM(nombre)) IN (?, ?, ?)', ['disponible', 'en preparación', 'en preparacion'])
            ->update([
                'nombre' => EstadoLoteCatalogo::label('planificado'),
                'descripcion' => EstadoLoteCatalogo::descripcion('planificado'),
            ]);
    }

    public function down(): void
    {
        // Catálogo operativo: no revertir automáticamente.
    }
};
