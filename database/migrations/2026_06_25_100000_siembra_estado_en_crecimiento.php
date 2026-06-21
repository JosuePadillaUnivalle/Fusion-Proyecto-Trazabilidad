<?php

use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Support\EstadoLoteCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote') || ! Schema::hasTable('estadolote_tipo')) {
            return;
        }

        $idSembrado = EstadoLoteTipo::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', ['sembrado'])
            ->value('estadolotetipoid');

        $idEnCrecimiento = EstadoLoteTipo::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(EstadoLoteCatalogo::label('en_crecimiento'))])
            ->value('estadolotetipoid');

        if (! $idSembrado || ! $idEnCrecimiento || (int) $idSembrado === (int) $idEnCrecimiento) {
            return;
        }

        Lote::query()
            ->where('estadolotetipoid', (int) $idSembrado)
            ->where(function ($q) {
                $q->whereNotNull('fechasiembra');
                if (Schema::hasTable('actividad')) {
                    $q->orWhereHas('actividades', function ($act) {
                        $act->whereNotNull('fechafin')
                            ->whereHas('tipoActividad', fn ($tipo) => $tipo->whereRaw('LOWER(nombre) LIKE ?', ['%siembra%']));
                    });
                }
            })
            ->update([
                'estadolotetipoid' => (int) $idEnCrecimiento,
                'fechamodificacion' => now(),
            ]);
    }

    public function down(): void
    {
        // No reversible de forma segura.
    }
};
