<?php

namespace App\Console\Commands;

use App\Models\ChecklistCondicionLogistica;
use App\Models\ChecklistIncidenteEnvio;
use App\Models\DocumentoEntrega;
use App\Models\FirmaRecepcionEnvio;
use App\Models\FirmaTransportistaEnvio;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EliminarRutasDistribucionCommand extends Command
{
    protected $signature = 'rutas:eliminar {codigos* : Códigos de ruta (ej. RD-20260621-0001)}';

    protected $description = 'Elimina rutas de distribución PDV y sus datos relacionados por código';

    public function handle(): int
    {
        $codigos = $this->argument('codigos');

        foreach ($codigos as $codigo) {
            $ruta = RutaDistribucion::query()->where('codigo', $codigo)->first();
            if ($ruta === null) {
                $this->warn("No se encontró la ruta {$codigo}.");

                continue;
            }

            DB::transaction(function () use ($ruta, $codigo) {
                $rutaId = $ruta->rutadistribucionid;

                $checklistIds = ChecklistCondicionLogistica::query()
                    ->where('rutadistribucionid', $rutaId)
                    ->pluck('checklistcondicionid');
                if ($checklistIds->isNotEmpty()) {
                    DB::table('checklist_condicion_logistica_detalle')
                        ->whereIn('checklistcondicionid', $checklistIds)
                        ->delete();
                }
                ChecklistCondicionLogistica::query()->where('rutadistribucionid', $rutaId)->delete();

                $incidenteIds = ChecklistIncidenteEnvio::query()
                    ->where('rutadistribucionid', $rutaId)
                    ->pluck('checklistincidenteenvioid');
                if ($incidenteIds->isNotEmpty()) {
                    DB::table('checklist_incidente_envio_detalle')
                        ->whereIn('checklistincidenteenvioid', $incidenteIds)
                        ->delete();
                }
                ChecklistIncidenteEnvio::query()->where('rutadistribucionid', $rutaId)->delete();

                FirmaTransportistaEnvio::query()->where('rutadistribucionid', $rutaId)->delete();
                FirmaRecepcionEnvio::query()->where('rutadistribucionid', $rutaId)->delete();

                DocumentoEntrega::query()
                    ->where(function ($q) use ($rutaId) {
                        $q->where('metadata->rutadistribucionid', $rutaId);
                    })
                    ->orWhereIn('pedidoid', PedidoDistribucion::query()
                        ->where('rutadistribucionid', $rutaId)
                        ->pluck('pedidodistribucionid'))
                    ->get()
                    ->each(function (DocumentoEntrega $doc) {
                        $path = trim((string) $doc->archivo_path);
                        if ($path !== '' && Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                        $doc->delete();
                    });

                $pedidos = PedidoDistribucion::query()->where('rutadistribucionid', $rutaId)->get();

                RutaDistribucionParada::query()->where('rutadistribucionid', $rutaId)->delete();

                foreach ($pedidos as $pedido) {
                    if (Schema::hasTable('solicitud_produccion_planta')) {
                        DB::table('solicitud_produccion_planta')
                            ->where('pedidodistribucionid', $pedido->pedidodistribucionid)
                            ->delete();
                    }
                    if (Schema::hasTable('almacen_movimiento')) {
                        DB::table('almacen_movimiento')
                            ->where('referencia', $pedido->numero_solicitud)
                            ->delete();
                    }
                    DB::table('detalle_pedido_distribucion')
                        ->where('pedidodistribucionid', $pedido->pedidodistribucionid)
                        ->delete();
                    $pedido->delete();
                }

                $ruta->delete();

                $this->info("Ruta {$codigo} eliminada.");
            });
        }

        return self::SUCCESS;
    }
}
