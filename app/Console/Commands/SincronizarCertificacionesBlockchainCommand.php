<?php

namespace App\Console\Commands;

use App\Services\Blockchain\BlockchainClient;
use App\Services\Blockchain\CertificacionBlockchainService;
use Illuminate\Console\Command;

class SincronizarCertificacionesBlockchainCommand extends Command
{
    protected $signature = 'blockchain:sincronizar-certificaciones {--limite=50 : Máximo de registros a procesar}';

    protected $description = 'Reenvía certificaciones de campo pendientes/erróneas al BaaS blockchain';

    public function handle(CertificacionBlockchainService $service, BlockchainClient $client): int
    {
        if (! $client->enabled()) {
            $this->warn('Blockchain deshabilitada (BLOCKCHAIN_ENABLED / API_URL / API_KEY).');

            return self::SUCCESS;
        }

        $this->info('Sincronizando certificaciones con '.$client->baseUrl().' ...');

        $resultado = $service->sincronizarPendientes((int) $this->option('limite'));

        $this->info("Procesados: {$resultado['procesados']}");
        $this->info("Certificadas: {$resultado['certificadas']}");
        if ($resultado['pendientes'] > 0) {
            $this->line("Pendientes: {$resultado['pendientes']}");
        }
        if ($resultado['rechazadas'] > 0) {
            $this->warn("Rechazadas: {$resultado['rechazadas']}");
        }
        if ($resultado['fallidos'] > 0) {
            $this->warn("Fallidos: {$resultado['fallidos']}");
        }

        return self::SUCCESS;
    }
}
