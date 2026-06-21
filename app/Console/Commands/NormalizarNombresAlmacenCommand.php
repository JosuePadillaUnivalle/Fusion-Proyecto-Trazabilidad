<?php

namespace App\Console\Commands;

use App\Services\AlmacenRenombradoService;
use Illuminate\Console\Command;

class NormalizarNombresAlmacenCommand extends Command
{
    protected $signature = 'almacenes:normalizar-nombres';

    protected $description = 'Renombra todos los almacenes al formato estándar y sincroniza referencias';

    public function handle(AlmacenRenombradoService $service): int
    {
        $resultado = $service->normalizarTodos();

        if ($resultado['actualizados'] === 0) {
            $this->info('Todos los almacenes ya usan el formato estándar.');

            return self::SUCCESS;
        }

        $this->info('Almacenes renombrados: '.$resultado['actualizados']);
        foreach ($resultado['mapa'] as $id => $cambio) {
            $this->line("  #{$id}: {$cambio['anterior']} → {$cambio['nuevo']}");
        }

        return self::SUCCESS;
    }
}
