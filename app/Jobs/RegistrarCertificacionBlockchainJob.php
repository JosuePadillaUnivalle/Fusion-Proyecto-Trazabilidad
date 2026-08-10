<?php

namespace App\Jobs;

use App\Services\Blockchain\CertificacionBlockchainService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RegistrarCertificacionBlockchainJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $certificacionId,
    ) {}

    public function handle(CertificacionBlockchainService $service): void
    {
        $ok = $service->enviar($this->certificacionId);
        if (! $ok) {
            Log::info('RegistrarCertificacionBlockchainJob: pendiente/error, reintentará sync', [
                'certificacionid' => $this->certificacionId,
            ]);
        }
    }
}
