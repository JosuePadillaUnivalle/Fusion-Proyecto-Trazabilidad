<?php

namespace App\Console\Commands;

use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Deja documentos de entrega SOLO si el envío/ruta tiene firmas con imagen real.
 * Si no hay firmas dibujadas, la lista queda vacía.
 *
 * php artisan agrofusion:solo-documentos-firmados --apply
 */
class SoloDocumentosFirmadosCommand extends Command
{
    protected $signature = 'agrofusion:solo-documentos-firmados {--apply : Persistir}';

    protected $description = 'Elimina documentos sin firma gráfica real; no inventa documentos';

    public function handle(): int
    {
        if (! Schema::hasTable('documento_entrega')) {
            $this->warn('Sin tabla documento_entrega');

            return self::SUCCESS;
        }

        $apply = (bool) $this->option('apply');
        $docs = DocumentoEntrega::query()->get();
        $mantener = 0;
        $borrar = 0;

        foreach ($docs as $doc) {
            if ($this->tieneFirmasGraficas($doc)) {
                $mantener++;
                $this->line('OK  '.$doc->documentoentregaid.' '.$doc->titulo);

                continue;
            }

            $borrar++;
            $this->warn('DEL '.$doc->documentoentregaid.' '.$doc->titulo.' (sin firma gráfica)');

            if ($apply) {
                $path = trim((string) ($doc->archivo_path ?? ''));
                if ($path !== '') {
                    try {
                        Storage::disk('public')->delete($path);
                    } catch (\Throwable) {
                    }
                }
                $doc->delete();
            }
        }

        $this->info(($apply ? 'Aplicado' : 'Simulación').": mantener={$mantener} borrar={$borrar}");
        if (! $apply) {
            $this->comment('Usa --apply para persistir.');
        }

        return self::SUCCESS;
    }

    private function tieneFirmasGraficas(DocumentoEntrega $doc): bool
    {
        $meta = is_array($doc->metadata) ? $doc->metadata : [];

        if (! empty($meta['envio_cierre_agricola'])) {
            $envio = null;
            if (! empty($meta['envioasignacionmultipleid'])) {
                $envio = EnvioAsignacionMultiple::query()
                    ->with(['firmaTransportista', 'firmaRecepcion'])
                    ->find($meta['envioasignacionmultipleid']);
            }
            if ($envio === null && $doc->externo_envio_id) {
                $envio = EnvioAsignacionMultiple::query()
                    ->with(['firmaTransportista', 'firmaRecepcion'])
                    ->where('externo_envio_id', $doc->externo_envio_id)
                    ->first();
            }

            return $this->firmasValidas($envio?->firmaTransportista?->imagenfirma, $envio?->firmaRecepcion?->imagenfirma);
        }

        $rutaId = (int) ($meta['rutadistribucionid'] ?? 0);
        if ($rutaId > 0 && (
            ! empty($meta['envio_cierre_planta_mayorista'])
            || ! empty($meta['envio_cierre_mayorista_pdv'])
        )) {
            $ruta = RutaDistribucion::query()
                ->with(['firmaTransportista', 'firmaRecepcion'])
                ->find($rutaId);

            return $this->firmasValidas($ruta?->firmaTransportista?->imagenfirma, $ruta?->firmaRecepcion?->imagenfirma);
        }

        return false;
    }

    private function firmasValidas(?string $ft, ?string $fr): bool
    {
        return $this->esImagenFirma($ft) && $this->esImagenFirma($fr);
    }

    private function esImagenFirma(?string $img): bool
    {
        $img = trim((string) $img);
        if ($img === '') {
            return false;
        }

        return str_starts_with($img, 'data:image/') || strlen($img) > 80;
    }
}
