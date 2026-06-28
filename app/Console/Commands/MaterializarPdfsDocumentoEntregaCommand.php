<?php

namespace App\Console\Commands;

use App\Models\DocumentoEntrega;
use App\Support\DocumentoEntregaArchivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MaterializarPdfsDocumentoEntregaCommand extends Command
{
    protected $signature = 'agrofusion:materializar-pdfs-documentos
                            {--id= : ID concreto de documento_entrega}
                            {--forzar : Regenerar aunque el archivo exista}';

    protected $description = 'Genera o regenera los PDF de documentos de entrega (útil en Railway tras un cierre logístico)';

    public function handle(): int
    {
        $id = $this->option('id');
        $forzar = (bool) $this->option('forzar');

        $query = DocumentoEntrega::query()
            ->whereNotNull('archivo_path')
            ->orderBy('documentoentregaid');

        if ($id !== null && $id !== '') {
            $query->where('documentoentregaid', (int) $id);
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('No hay documentos para procesar.');

            return self::SUCCESS;
        }

        $this->info("Procesando {$total} documento(s)...");

        $ok = 0;
        $fallos = 0;

        $query->chunkById(20, function ($documentos) use (&$ok, &$fallos, $forzar) {
            foreach ($documentos as $documento) {
                $path = trim((string) $documento->archivo_path);
                $existe = $path !== '' && Storage::disk('public')->exists($path);

                if ($existe && ! $forzar) {
                    $this->line("  #{$documento->documentoentregaid} ya tiene PDF — omitido");
                    $ok++;

                    continue;
                }

                $this->line("  #{$documento->documentoentregaid} generando {$path}...");
                $inicio = microtime(true);

                $generado = $forzar
                    ? DocumentoEntregaArchivo::generarPdfOperativo($documento)
                    : DocumentoEntregaArchivo::asegurarPdfOperativo($documento);

                $segundos = round(microtime(true) - $inicio, 1);

                if ($generado) {
                    $ok++;
                    $this->info("    OK ({$segundos}s)");
                } else {
                    $fallos++;
                    $this->error("    FALLO ({$segundos}s)");
                }
            }
        }, 'documentoentregaid');

        $this->newLine();
        $this->info("Listo: {$ok} correcto(s), {$fallos} fallo(s).");

        return $fallos > 0 ? self::FAILURE : self::SUCCESS;
    }
}
