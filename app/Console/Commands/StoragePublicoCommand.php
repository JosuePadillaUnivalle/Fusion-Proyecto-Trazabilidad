<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class StoragePublicoCommand extends Command
{
    protected $signature = 'agrofusion:storage-publico
                            {action : exportar|importar}
                            {--archivo=storage/app/public-export.tar.gz : Ruta del archivo comprimido}';

    protected $description = 'Empaqueta o restaura storage/app/public (fotos, PDFs, evidencias) para migrar a Railway';

    private string $directorio = 'storage/app/public';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'exportar' => $this->exportar(),
            'importar' => $this->importar(),
            default => $this->errorAccion(),
        };
    }

    private function exportar(): int
    {
        $origen = base_path($this->directorio);
        if (! is_dir($origen)) {
            $this->error("No existe {$origen}");

            return self::FAILURE;
        }

        $archivo = base_path((string) $this->option('archivo'));
        if (is_file($archivo)) {
            unlink($archivo);
        }

        $archivos = count(File::allFiles($origen));
        $proceso = new Process(
            ['tar', '-czf', $archivo, '-C', base_path('storage/app'), 'public'],
            base_path()
        );
        $proceso->run();

        if (! $proceso->isSuccessful()) {
            $this->error('No se pudo crear el archivo: '.$proceso->getErrorOutput());

            return self::FAILURE;
        }

        $mb = round(filesize($archivo) / 1024 / 1024, 2);
        $this->info("Archivo creado: {$archivo}");
        $this->line("  ~{$archivos} archivos, {$mb} MB");

        return self::SUCCESS;
    }

    private function importar(): int
    {
        $archivo = base_path((string) $this->option('archivo'));
        if (! is_file($archivo)) {
            $this->error("No existe el archivo: {$archivo}");

            return self::FAILURE;
        }

        $proceso = new Process(
            ['tar', '-xzf', $archivo, '-C', base_path('storage/app')],
            base_path()
        );
        $proceso->run();

        if (! $proceso->isSuccessful()) {
            $this->error('No se pudo extraer: '.$proceso->getErrorOutput());

            return self::FAILURE;
        }

        $this->call('storage:link', ['--force' => true]);
        $this->info('storage/app/public restaurado correctamente.');

        return self::SUCCESS;
    }

    private function errorAccion(): int
    {
        $this->error('Acción inválida. Use: exportar o importar');

        return self::FAILURE;
    }
}
