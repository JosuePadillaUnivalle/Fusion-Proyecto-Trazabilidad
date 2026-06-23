<?php

namespace Database\Seeders;

use App\Models\Prioridad;
use App\Models\TipoActividad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogos mínimos para operar lotes (siembra, riego, etc.).
 * Evita errores 404 cuando falta el tipo «Siembra» tras clonar sin SQLite demo.
 */
class CatalogosOperacionAgricolaSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('tipoactividad')) {
            $hasDescripcion = Schema::hasColumn('tipoactividad', 'descripcion');
            foreach (['Siembra', 'Riego', 'Fertilización', 'Cosecha', 'Control de plagas'] as $nombre) {
                $data = ['nombre' => $nombre];
                if ($hasDescripcion) {
                    $data['descripcion'] = $nombre;
                }
                TipoActividad::updateOrCreate(['nombre' => $nombre], $data);
            }
        }

        if (Schema::hasTable('prioridad')) {
            foreach (['Baja', 'Media', 'Alta', 'Urgente'] as $nombre) {
                Prioridad::firstOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
            }
        }
    }
}
