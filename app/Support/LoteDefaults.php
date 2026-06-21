<?php

namespace App\Support;

use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\UnidadMedida;
use App\Support\EstadoLoteCatalogo;
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

        if ($isNew && empty($data['nombre']) && ! empty($data['insumosemillaid'])) {
            $producto = LoteAgricolaNombre::productoDesdeInsumo((int) $data['insumosemillaid']);
            if ($producto) {
                $data['nombre'] = LoteAgricolaNombre::siguienteNombre($producto);
            }
        }

        if ($isNew && empty($data['estadolotetipoid'])) {
            $data['estadolotetipoid'] = self::estadoPlanificadoId();
        }

        if ($isNew) {
            unset($data['fechasiembra']);
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
                'fecha_cambio' => $lote->fechacreacion ?? now(),
                'usuarioid' => $lote->usuarioid,
            ]
        );
    }

    public static function unidadHectareaId(): ?int
    {
        $id = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['hectárea'])->value('unidadmedidaid');

        return $id ? (int) $id : UnidadMedida::where('nombre', 'Hectárea')->value('unidadmedidaid');
    }

    public static function estadoPlanificadoId(): ?int
    {
        return EstadoLoteCatalogo::idPorSlug('planificado')
            ?? EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['planificado', 'disponible'])->value('estadolotetipoid');
    }

    /** @deprecated Use estadoPlanificadoId() */
    public static function estadoDisponibleId(): ?int
    {
        return self::estadoPlanificadoId();
    }
}
