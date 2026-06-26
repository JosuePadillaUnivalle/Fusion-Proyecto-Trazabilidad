<?php

namespace App\Console\Commands;

use App\Models\ChecklistCondicionLogistica;
use App\Models\ChecklistIncidenteEnvio;
use App\Models\DetalleTrasladoPlantaMayorista;
use App\Models\DocumentoEntrega;
use App\Models\FirmaRecepcionEnvio;
use App\Models\FirmaTransportistaEnvio;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Support\RutaDistribucionCatalogo;
use App\Support\TrasladoPlantaMayoristaPresentacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LimpiarTrasladosPlantaVaciosCommand extends Command
{
    protected $signature = 'traslados-planta:limpiar-vacios {--dry-run : Solo listar sin eliminar}';

    protected $description = 'Elimina traslados planta→mayorista sin productos registrados';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $eliminados = 0;

        RutaDistribucion::query()
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->orderBy('rutadistribucionid')
            ->each(function (RutaDistribucion $ruta) use ($dryRun, &$eliminados) {
                if (TrasladoPlantaMayoristaPresentacion::tieneCargaRegistrada($ruta)) {
                    return;
                }

                $this->line(($dryRun ? '[dry-run] ' : '').'Eliminar '.$ruta->codigo.' (sin carga)');

                if ($dryRun) {
                    $eliminados++;

                    return;
                }

                DB::transaction(function () use ($ruta) {
                    $rutaId = $ruta->rutadistribucionid;

                    $checklistIds = ChecklistCondicionLogistica::query()
                        ->where('rutadistribucionid', $rutaId)
                        ->pluck('checklistcondicionid');
                    if ($checklistIds->isNotEmpty() && Schema::hasTable('checklist_condicion_logistica_detalle')) {
                        DB::table('checklist_condicion_logistica_detalle')
                            ->whereIn('checklistcondicionid', $checklistIds)
                            ->delete();
                    }
                    ChecklistCondicionLogistica::query()->where('rutadistribucionid', $rutaId)->delete();

                    $incidenteIds = ChecklistIncidenteEnvio::query()
                        ->where('rutadistribucionid', $rutaId)
                        ->pluck('checklistincidenteenvioid');
                    if ($incidenteIds->isNotEmpty() && Schema::hasTable('checklist_incidente_envio_detalle')) {
                        DB::table('checklist_incidente_envio_detalle')
                            ->whereIn('checklistincidenteenvioid', $incidenteIds)
                            ->delete();
                    }
                    ChecklistIncidenteEnvio::query()->where('rutadistribucionid', $rutaId)->delete();

                    FirmaTransportistaEnvio::query()->where('rutadistribucionid', $rutaId)->delete();
                    FirmaRecepcionEnvio::query()->where('rutadistribucionid', $rutaId)->delete();

                    DocumentoEntrega::query()
                        ->where('metadata->rutadistribucionid', $rutaId)
                        ->get()
                        ->each(function (DocumentoEntrega $doc) {
                            $path = trim((string) $doc->archivo_path);
                            if ($path !== '' && Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                            $doc->delete();
                        });

                    DetalleTrasladoPlantaMayorista::query()->where('rutadistribucionid', $rutaId)->delete();
                    RutaDistribucionParada::query()->where('rutadistribucionid', $rutaId)->delete();
                    $ruta->delete();
                });

                $eliminados++;
            });

        $this->info($dryRun
            ? "Traslados vacíos detectados: {$eliminados}"
            : "Traslados vacíos eliminados: {$eliminados}");

        return self::SUCCESS;
    }
}
