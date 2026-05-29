<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IntegracionEnviosService;

class SincronizarEnviosCommand extends Command
{
    protected $signature = 'envios:sincronizar';
    protected $description = 'Sincroniza envíos pendientes con la API externa cuando está disponible';

    public function handle(IntegracionEnviosService $service)
    {
        $this->info('Verificando conexión con API de envíos...');

        if (!$service->verificarConexion()) {
            $this->warn('❌ API no disponible. Los envíos permanecen en cola local.');
            return 1;
        }

        $this->info('✅ API disponible. Sincronizando envíos pendientes...');

        $resultado = $service->sincronizarPendientes();

        if ($resultado['sincronizados'] > 0) {
            $this->info("✅ {$resultado['sincronizados']} envíos sincronizados correctamente");
        }

        if ($resultado['fallidos'] > 0) {
            $this->warn("⚠️ {$resultado['fallidos']} envíos fallaron");
        }

        if ($resultado['pendientes_restantes'] > 0) {
            $this->info("📋 {$resultado['pendientes_restantes']} envíos aún pendientes");
        }

        return 0;
    }
}