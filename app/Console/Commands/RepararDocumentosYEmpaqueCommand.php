<?php

namespace App\Console\Commands;

use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use App\Support\DocumentoEntregaArchivo;
use App\Support\EmpaquePlantaCatalogo;
use App\Support\RutaDistribucionCatalogo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Limpia documentos inventados (nota/guía/confirmación demo) y deja solo
 * comprobantes reales de cierre de envío (tipo guia_transporte) con PDF firmado.
 * Además sincroniza medidas de tipos de empaque.
 *
 * php artisan agrofusion:reparar-documentos-empaque --apply
 */
class RepararDocumentosYEmpaqueCommand extends Command
{
    protected $signature = 'agrofusion:reparar-documentos-empaque {--apply : Persistir cambios}';

    protected $description = 'Elimina docs inventados, regenera POD de cierres reales y completa medidas de empaque';

    /** @var list<string> */
    private const TITULOS_BASURA = [
        'Nota entrega almacén central',
        'Nota entrega planta de procesamiento',
        'Guía de transporte Carlos Mamani',
        'Confirmación de entrega cliente norte',
        'Guía de entrega ENV-01 — Carlos Mamani',
        'Confirmación de entrega ENV-03 — Carlos Mamani',
        'Guía de transporte ENV-MOD-26-01',
        'Nota de entrega ENV-MOD-26-03',
        'Confirmación de entrega ENV-MOD-26-04',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        DB::beginTransaction();
        try {
            $borrados = $this->limpiarDocumentosBasura();
            $pods = $this->asegurarDocumentosCierre();
            EmpaquePlantaCatalogo::asegurarTiposEmpaqueEnBd();

            $empaques = DB::table('tipo_empaque')
                ->whereIn('nombre', array_column(EmpaquePlantaCatalogo::TIPOS_PREDEFINIDOS, 'nombre'))
                ->get(['nombre', 'largo_cm', 'ancho_cm', 'alto_cm', 'tara_kg', 'capacidad_unidades', 'unidades_por_pallet']);

            if (! $apply) {
                DB::rollBack();
                $this->warn('Simulación (sin --apply). Cambios revertidos.');
            } else {
                DB::commit();
            }

            $this->info('Documentos basura eliminados: '.$borrados);
            $this->info('Comprobantes de cierre asegurados: '.count($pods));
            foreach ($empaques as $e) {
                $this->line(sprintf(
                    '  Empaque %-16s L=%s A=%s H=%s tara=%s cap=%s pallet=%s',
                    $e->nombre,
                    $e->largo_cm,
                    $e->ancho_cm,
                    $e->alto_cm,
                    $e->tara_kg,
                    $e->capacidad_unidades,
                    $e->unidades_por_pallet
                ));
            }

            if ($apply) {
                $okPdf = 0;
                foreach ($pods as $id) {
                    $doc = DocumentoEntrega::find($id);
                    if ($doc && DocumentoEntregaArchivo::materializarPdfDocumento($doc)) {
                        $okPdf++;
                    }
                }
                $this->info("PDFs materializados: {$okPdf}/".count($pods));
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function limpiarDocumentosBasura(): int
    {
        if (! Schema::hasTable('documento_entrega')) {
            return 0;
        }

        $candidatos = DocumentoEntrega::query()
            ->where(function ($w) {
                $w->whereIn('titulo', self::TITULOS_BASURA)
                    ->orWhereIn('tipo_documento', ['nota_entrega', 'guia_entrega', 'confirmacion_entrega', 'acta_salida', 'acuse_entrega'])
                    ->orWhere('metadata->origen', 'recuperacion_catalogo_solicitada')
                    ->orWhere('metadata->sin_archivo_real', true)
                    ->orWhere('metadata->mod_log', true)
                    ->orWhere('metadata->mod_env', true)
                    ->orWhere('metadata->mod_panel', true)
                    ->orWhere('archivo_path', 'like', 'demo/%')
                    ->orWhere('archivo_path', 'like', 'documentos-entrega/recuperados/%');
            })
            ->get();

        $ids = [];
        foreach ($candidatos as $doc) {
            $meta = is_array($doc->metadata) ? $doc->metadata : [];
            $esCierreReal = $doc->tipo_documento === 'guia_transporte' && (
                ! empty($meta['envio_cierre_agricola'])
                || ! empty($meta['envio_cierre_planta_mayorista'])
                || ! empty($meta['envio_cierre_mayorista_pdv'])
            );
            if ($esCierreReal) {
                continue;
            }
            $ids[] = (int) $doc->documentoentregaid;
            $path = trim((string) ($doc->archivo_path ?? ''));
            if ($path !== '' && ! str_starts_with($path, 'demo/')) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable) {
                    // ignore
                }
            }
        }

        if ($ids === []) {
            return 0;
        }

        return DocumentoEntrega::query()->whereIn('documentoentregaid', $ids)->delete();
    }

    /**
     * @return list<int>
     */
    private function asegurarDocumentosCierre(): array
    {
        $admin = Usuario::query()->where('email', 'admin@agrofusion.com')->first()
            ?? Usuario::query()->where('role', 'admin')->first();

        if ($admin === null) {
            throw new \RuntimeException('No hay usuario admin para asociar documentos.');
        }

        $ids = [];

        // 1) Envíos agrícolas ya recibidos en planta (con firmas)
        $envios = EnvioAsignacionMultiple::query()
            ->with(['firmaTransportista', 'firmaRecepcion'])
            ->whereNotNull('fecha_recepcion_planta')
            ->whereNotNull('externo_envio_id')
            ->orderByDesc('envioasignacionmultipleid')
            ->limit(30)
            ->get();

        foreach ($envios as $envio) {
            $codigo = $envio->externo_envio_id;
            $doc = DocumentoEntrega::query()
                ->where('externo_envio_id', $codigo)
                ->where('tipo_documento', 'guia_transporte')
                ->where('metadata->envio_cierre_agricola', true)
                ->first();

            if ($doc === null) {
                $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $codigo) ?: 'envio';
                $doc = DocumentoEntrega::create([
                    'externo_envio_id' => $codigo,
                    'pedidoid' => $envio->pedidoid,
                    'usuarioid' => $admin->usuarioid,
                    'tipo_documento' => 'guia_transporte',
                    'titulo' => 'Documento de transporte de carga — '.$codigo,
                    'archivo_path' => 'documentos/entrega/'.$slug.'_transporte_'.now()->format('Ymd_His').'.pdf',
                    'almacenid' => $envio->almacenid,
                    'metadata' => [
                        'envio_cierre_agricola' => true,
                        'envioasignacionmultipleid' => $envio->envioasignacionmultipleid,
                        'reparado_en' => now()->toIso8601String(),
                    ],
                ]);
            }

            $ids[] = (int) $doc->documentoentregaid;
        }

        // 2) Rutas planta→mayorista completadas
        $rutasPm = RutaDistribucion::query()
            ->whereNotNull('almacen_planta_origenid')
            ->where(function ($q) {
                $q->where('estado', RutaDistribucionCatalogo::ESTADO_COMPLETADA)
                    ->orWhereNotNull('fecha_aprobacion_mayorista')
                    ->orWhereNotNull('llegada_confirmada_at');
            })
            ->orderByDesc('rutadistribucionid')
            ->limit(30)
            ->get();

        foreach ($rutasPm as $ruta) {
            $codigo = $ruta->codigo ?? ('TRASL-'.$ruta->rutadistribucionid);
            $doc = DocumentoEntrega::query()
                ->where('metadata->rutadistribucionid', $ruta->rutadistribucionid)
                ->where('tipo_documento', 'guia_transporte')
                ->where('metadata->envio_cierre_planta_mayorista', true)
                ->first();

            if ($doc === null) {
                $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $codigo) ?: 'traslado';
                $doc = DocumentoEntrega::create([
                    'externo_envio_id' => $codigo,
                    'usuarioid' => $admin->usuarioid,
                    'tipo_documento' => 'guia_transporte',
                    'titulo' => 'Documento de transporte de carga — '.$codigo,
                    'archivo_path' => 'documentos/entrega/'.$slug.'_transporte_'.now()->format('Ymd_His').'.pdf',
                    'almacenid' => $ruta->almacen_planta_origenid,
                    'metadata' => [
                        'envio_cierre_planta_mayorista' => true,
                        'rutadistribucionid' => $ruta->rutadistribucionid,
                        'reparado_en' => now()->toIso8601String(),
                    ],
                ]);
            }

            $ids[] = (int) $doc->documentoentregaid;
        }

        // 3) Rutas mayorista→PDV completadas
        $rutasPdv = RutaDistribucion::query()
            ->where(function ($q) {
                $q->where('tipo_ruta', 'mayorista_pdv')
                    ->orWhere(function ($w) {
                        $w->whereNull('almacen_planta_origenid')
                            ->whereNotNull('almacen_mayorista_origenid');
                    });
            })
            ->where(function ($q) {
                $q->where('estado', RutaDistribucionCatalogo::ESTADO_COMPLETADA)
                    ->orWhereNotNull('llegada_confirmada_at');
            })
            ->orderByDesc('rutadistribucionid')
            ->limit(30)
            ->get();

        foreach ($rutasPdv as $ruta) {
            $codigo = $ruta->codigo ?? ('DIST-'.$ruta->rutadistribucionid);
            $doc = DocumentoEntrega::query()
                ->where('metadata->rutadistribucionid', $ruta->rutadistribucionid)
                ->where('tipo_documento', 'guia_transporte')
                ->where('metadata->envio_cierre_mayorista_pdv', true)
                ->first();

            if ($doc === null) {
                $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $codigo) ?: 'distribucion';
                $primerPedido = $ruta->pedidos()->first();
                $doc = DocumentoEntrega::create([
                    'externo_envio_id' => $codigo,
                    'usuarioid' => $admin->usuarioid,
                    'tipo_documento' => 'guia_transporte',
                    'titulo' => 'Comprobante de entrega PDV — '.$codigo,
                    'archivo_path' => 'documentos/entrega/'.$slug.'_pdv_'.now()->format('Ymd_His').'.pdf',
                    'almacenid' => $ruta->almacen_mayorista_origenid,
                    'metadata' => [
                        'envio_cierre_mayorista_pdv' => true,
                        'rutadistribucionid' => $ruta->rutadistribucionid,
                        'pedidodistribucionid' => $primerPedido?->pedidodistribucionid,
                        'reparado_en' => now()->toIso8601String(),
                    ],
                ]);
            }

            $ids[] = (int) $doc->documentoentregaid;
        }

        return array_values(array_unique($ids));
    }
}
