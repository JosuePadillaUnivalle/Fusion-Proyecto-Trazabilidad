<?php

namespace App\Console\Commands;

use App\Support\LoteProduccionRutaService;
use Illuminate\Console\Command;

class MigrarRutasLotesCommand extends Command
{
    protected $signature = 'agrofusion:migrar-rutas-lotes';

    protected $description = 'Inicializa la ruta de transformación en lotes existentes (desde plantilla o registros previos)';

    public function handle(LoteProduccionRutaService $rutaService): int
    {
        $this->info('Migrando rutas de lotes al sistema actual…');

        $stats = $rutaService->migrarTodosLosLotes();

        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Lotes revisados', $stats['procesados']],
                ['Rutas creadas', $stats['creados']],
                ['Desde plantilla', $stats['desde_plantilla']],
                ['Desde registros (manual)', $stats['desde_registros']],
            ]
        );

        $this->info('Migración de rutas completada.');

        return self::SUCCESS;
    }
}
