<?php

namespace App\Support;

use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class DocumentoEntregaArchivo
{
    private const PDF_VERSION = 2;

    /** @var array<string, string> */
    private const TIPOS_ETIQUETA = [
        'guia_entrega' => 'Guía de entrega',
        'guia_transporte' => 'Guía de transporte',
        'confirmacion_entrega' => 'Confirmación de entrega',
        'nota_entrega' => 'Nota de entrega',
        'pod' => 'POD / comprobante de entrega',
    ];

    public static function asegurarPdfOperativo(DocumentoEntrega $documento): bool
    {
        $path = trim((string) $documento->archivo_path);
        if ($path === '') {
            return false;
        }

        if (
            ! Storage::disk('public')->exists($path)
            || self::esArchivoPlaceholder($path)
            || ($documento->metadata['pdf_version'] ?? null) !== self::PDF_VERSION
        ) {
            return self::generarPdfOperativo($documento);
        }

        return true;
    }

    public static function materializarSiFalta(DocumentoEntrega $documento): bool
    {
        return self::asegurarPdfOperativo($documento);
    }

    public static function materializarTodosFaltantes(bool $forzarRegeneracion = false): int
    {
        if (! Schema::hasTable('documento_entrega')) {
            return 0;
        }

        $procesados = 0;

        DocumentoEntrega::query()
            ->whereNotNull('archivo_path')
            ->orderBy('documentoentregaid')
            ->chunkById(50, function ($documentos) use (&$procesados, $forzarRegeneracion) {
                foreach ($documentos as $documento) {
                    $ok = $forzarRegeneracion
                        ? self::generarPdfOperativo($documento)
                        : self::asegurarPdfOperativo($documento);

                    if ($ok) {
                        $procesados++;
                    }
                }
            }, 'documentoentregaid');

        return $procesados;
    }

    public static function generarPdfOperativo(DocumentoEntrega $documento): bool
    {
        $path = trim((string) $documento->archivo_path);
        if ($path === '') {
            return false;
        }

        $disk = Storage::disk('public');
        $dir = dirname(str_replace('\\', '/', $path));
        if ($dir !== '.' && $dir !== '') {
            $disk->makeDirectory($dir);
        }

        $contexto = self::contextoPdf($documento);
        $pdf = Pdf::loadView('logistica.documentos.pdf.comprobante', $contexto)
            ->setPaper('a4', 'portrait');

        $guardado = (bool) $disk->put($path, $pdf->output());

        if ($guardado) {
            $metadata = array_merge($documento->metadata ?? [], [
                'pdf_version' => self::PDF_VERSION,
                'pdf_generado_en' => now()->toIso8601String(),
            ]);
            $documento->update(['metadata' => $metadata]);
        }

        return $guardado;
    }

    /** @return array<string, mixed> */
    public static function contextoPdf(DocumentoEntrega $documento): array
    {
        $documento->loadMissing(['usuario', 'pedido.detalles', 'almacen']);

        $envio = null;
        if ($documento->externo_envio_id) {
            $envio = EnvioAsignacionMultiple::query()
                ->with(['transportista', 'pedido.detalles', 'almacen', 'ruta'])
                ->where('externo_envio_id', $documento->externo_envio_id)
                ->first();
        }

        $pedido = $documento->pedido ?? $envio?->pedido;
        $transportista = $envio?->transportista ?? $documento->usuario;
        $transportistaNombre = trim(($transportista?->nombre ?? '').' '.($transportista?->apellido ?? '')) ?: '—';

        $lineasProducto = [];
        foreach ($pedido?->detalles ?? [] as $detalle) {
            $lineasProducto[] = [
                'producto' => $detalle->cultivo_personalizado
                    ?? $detalle->producto?->nombre
                    ?? 'Producto',
                'cantidad' => number_format((float) $detalle->cantidad, 2, '.', '').' u.',
                'observaciones' => $detalle->observaciones,
            ];
        }

        $tipo = (string) $documento->tipo_documento;
        $tipoEtiqueta = self::TIPOS_ETIQUETA[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));

        $textoObservaciones = match ($tipo) {
            'guia_entrega', 'guia_transporte' => 'Documento de salida y trazabilidad del envío hacia el punto de entrega indicado.',
            'confirmacion_entrega', 'pod' => 'Comprobante de recepción conforme en destino.',
            'nota_entrega' => 'Registro de recepción en almacén o punto de despacho.',
            default => 'Comprobante logístico asociado al envío.',
        };

        if ($envio?->estado === 'entregado') {
            $textoObservaciones .= ' Envío marcado como entregado en el sistema.';
        }

        return [
            'documento' => $documento,
            'envio' => $envio,
            'pedido' => $pedido,
            'tipoEtiqueta' => $tipoEtiqueta,
            'transportistaNombre' => $transportistaNombre,
            'estadoEnvio' => ucfirst(str_replace('_', ' ', (string) ($envio?->estado ?? 'pendiente'))),
            'lineasProducto' => $lineasProducto,
            'textoObservaciones' => $textoObservaciones,
        ];
    }

    private static function esArchivoPlaceholder(string $path): bool
    {
        if (! Storage::disk('public')->exists($path)) {
            return false;
        }

        $contenido = Storage::disk('public')->get($path);

        return strlen($contenido) < 2000
            && str_contains($contenido, 'Documento generado por AgroFusion');
    }
}
