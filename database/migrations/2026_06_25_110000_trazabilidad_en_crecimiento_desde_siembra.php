<?php

use App\Models\Actividad;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Support\EstadoLoteCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('historial_estados_lote') || ! Schema::hasTable('lote')) {
            return;
        }

        $idEnCrecimiento = EstadoLoteTipo::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(EstadoLoteCatalogo::label('en_crecimiento'))])
            ->value('estadolotetipoid');

        if (! $idEnCrecimiento) {
            return;
        }

        Lote::query()->with(['actividades.tipoActividad', 'historialEstados.estadoTipo'])->chunkById(100, function ($lotes) use ($idEnCrecimiento) {
            foreach ($lotes as $lote) {
                $siembra = $lote->actividades->first(function (Actividad $actividad) {
                    $nombre = mb_strtolower(trim($actividad->tipoActividad->nombre ?? ''));

                    return str_contains($nombre, 'siembra') && $actividad->fechafin !== null;
                });

                $fechaSiembra = $siembra?->fechafin ?? $lote->fechasiembra;
                if (! $fechaSiembra) {
                    continue;
                }

                $yaTieneHistorialSiembra = $lote->historialEstados->contains(function ($historial) use ($idEnCrecimiento) {
                    if ((int) $historial->estadolotetipoid !== (int) $idEnCrecimiento) {
                        return false;
                    }

                    return str_contains(mb_strtolower((string) ($historial->observaciones ?? '')), 'siembra');
                });

                if ($yaTieneHistorialSiembra) {
                    continue;
                }

                $historialRedundante = $lote->historialEstados->first(function ($historial) use ($idEnCrecimiento) {
                    if ((int) $historial->estadolotetipoid !== (int) $idEnCrecimiento) {
                        return false;
                    }
                    $obs = mb_strtolower((string) ($historial->observaciones ?? ''));

                    return str_contains($obs, 'riego')
                        || str_contains($obs, 'fertiliz')
                        || str_contains($obs, 'plaga')
                        || str_contains($obs, 'fumig');
                });

                if ($historialRedundante) {
                    HistorialEstadoLote::query()
                        ->where('historial_estado_id', $historialRedundante->historial_estado_id)
                        ->update([
                            'fecha_cambio' => $fechaSiembra,
                            'observaciones' => 'Actividad «Siembra» completada',
                            'usuarioid' => $siembra?->usuarioid ?? $historialRedundante->usuarioid,
                            'updated_at' => now(),
                        ]);

                    continue;
                }

                HistorialEstadoLote::query()->create([
                    'loteid' => $lote->loteid,
                    'estadolotetipoid' => (int) $idEnCrecimiento,
                    'fecha_cambio' => $fechaSiembra,
                    'observaciones' => 'Actividad «Siembra» completada',
                    'usuarioid' => $siembra?->usuarioid ?? $lote->usuarioid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // No reversible.
    }
};
