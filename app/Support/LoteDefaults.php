<?php

namespace App\Support;

use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\Schema;

class LoteDefaults
{
    public static function enrich(array $data, bool $isNew = true): array
    {
        $haId = self::unidadHectareaId();
        if ($haId) {
            $data['unidadsuperficieid'] = $haId;
        }

        if (empty($data['codigo_trazabilidad'])) {
            $data['codigo_trazabilidad'] = 'TRAZ-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
        }

        if (empty($data['estadolotetipoid'])) {
            $data['estadolotetipoid'] = self::estadoDisponibleId();
        }

        if (empty($data['fechasiembra'])) {
            $data['fechasiembra'] = now()->toDateString();
        }

        $data['fechamodificacion'] = now();
        if ($isNew) {
            $data['fechacreacion'] = now();
        }

        return $data;
    }

    public static function registrarHistorialInicial(Lote $lote): void
    {
        if (! Schema::hasTable('historial_estados_lote') || ! $lote->estadolotetipoid) {
            return;
        }

        HistorialEstadoLote::firstOrCreate(
            [
                'loteid' => $lote->loteid,
                'estadolotetipoid' => $lote->estadolotetipoid,
                'observaciones' => 'Registro inicial del lote',
            ],
            [
                'fecha_cambio' => $lote->fechasiembra ?? now(),
                'usuarioid' => $lote->usuarioid,
            ]
        );
    }

    public static function unidadHectareaId(): ?int
    {
        $id = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['hectárea'])->value('unidadmedidaid');

        return $id ? (int) $id : UnidadMedida::where('nombre', 'Hectárea')->value('unidadmedidaid');
    }

    public static function estadoDisponibleId(): ?int
    {
        $id = EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', ['disponible'])->value('estadolotetipoid');

        return $id ? (int) $id : EstadoLoteTipo::query()->orderBy('estadolotetipoid')->value('estadolotetipoid');
    }
}
