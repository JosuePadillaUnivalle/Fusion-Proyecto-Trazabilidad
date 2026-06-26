<?php

namespace App\Console\Commands;

use App\Services\CierreEnvioPlantaMayoristaService;
use Illuminate\Console\Command;

class RepararTrasladosPlantaMetadataCommand extends Command
{
    protected $signature = 'traslados-planta:reparar-metadata';

    protected $description = 'Completa destino y líneas de producto en metadata de documentos TPM existentes';

    public function handle(CierreEnvioPlantaMayoristaService $service): int
    {
        $resultado = $service->repararMetadataDocumentos();

        $this->info('Traslados revisados: '.$resultado['revisados']);
        $this->info('Documentos actualizados: '.$resultado['actualizados']);

        return self::SUCCESS;
    }
}
