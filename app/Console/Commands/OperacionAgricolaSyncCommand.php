<?php

namespace App\Console\Commands;

use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Console\Command;

class OperacionAgricolaSyncCommand extends Command
{
    protected $signature = 'operacion:sync';

    protected $description = 'Sincroniza automáticamente clima por lote, actividades (insumos, cosechas, riego, alertas)';

    public function handle(OperacionAgricolaAutomaticaService $service): int
    {
        $this->info('Sincronizando operación agrícola automática...');

        $r = $service->sincronizarTodo();

        $this->table(
            ['Proceso', 'Registros'],
            collect($r)->map(fn ($v, $k) => [str_replace('_', ' ', $k), $v])->values()->all()
        );

        $this->info('Operación agrícola sincronizada.');

        return self::SUCCESS;
    }
}
