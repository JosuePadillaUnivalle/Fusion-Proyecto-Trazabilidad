<?php

namespace App\Support;

use App\Models\MaquinaPlanta;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use Illuminate\Support\Collection;

/**
 * Relación coherente máquina ↔ proceso de planta (transformación).
 */
class MaquinaProcesoCompatibilidad
{
    /** @var array<string, list<string>> código máquina => procesos permitidos */
    private const MAPA_CODIGO = [
        'L-100' => ['Preparación de Materias Primas'],
        'MX-200' => ['Mezclado'],
        'EX-300' => ['Extrusión'],
        'MD-400' => ['Moldeo'],
        'SC-500' => ['Secado'],
        'TR-600' => ['Tratamiento Térmico'],
        'EV-700' => ['Envasado'],
        'ET-800' => ['Etiquetado'],
        'SE-10' => ['Empaquetado'],
        'BC-20' => ['Preparación de Materias Primas'],
        'BD-500' => [],
    ];

    /** Códigos excluidos de transformación (solo certificación u otros módulos). */
    private const EXCLUIDAS_TRANSFORMACION = ['BD-500'];

    public static function compatible(int $procesoplantaid, int $maquinaplantaid): bool
    {
        $proceso = ProcesoPlanta::query()->find($procesoplantaid);
        $maquina = MaquinaPlanta::query()->find($maquinaplantaid);

        if (! $proceso || ! $maquina) {
            return false;
        }

        if (in_array($maquina->codigo ?? '', self::EXCLUIDAS_TRANSFORMACION, true)) {
            return false;
        }

        $vinculo = ProcesoMaquinaPlanta::query()
            ->where('procesoplantaid', $procesoplantaid)
            ->where('maquinaplantaid', $maquinaplantaid)
            ->exists();

        if ($vinculo) {
            return true;
        }

        $codigo = strtoupper(trim((string) ($maquina->codigo ?? '')));
        $permitidos = self::MAPA_CODIGO[$codigo] ?? [];

        return in_array($proceso->nombre, $permitidos, true);
    }

    /**
     * @return Collection<int, MaquinaPlanta>
     */
    public static function maquinasParaProceso(int $procesoplantaid): Collection
    {
        $proceso = ProcesoPlanta::query()->find($procesoplantaid);
        if (! $proceso) {
            return collect();
        }

        $idsVinculados = ProcesoMaquinaPlanta::query()
            ->where('procesoplantaid', $procesoplantaid)
            ->pluck('maquinaplantaid');

        return MaquinaPlanta::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->filter(function (MaquinaPlanta $m) use ($proceso, $idsVinculados) {
                if (in_array($m->codigo ?? '', self::EXCLUIDAS_TRANSFORMACION, true)) {
                    return false;
                }
                if ($idsVinculados->contains($m->maquinaplantaid)) {
                    return true;
                }
                $codigo = strtoupper(trim((string) ($m->codigo ?? '')));
                $permitidos = self::MAPA_CODIGO[$codigo] ?? [];

                return in_array($proceso->nombre, $permitidos, true);
            })
            ->values();
    }

    /**
     * @return array{proceso_maquina: array<int, list<int>>, maquina_proceso: array<int, list<int>>}
     */
    public static function mapaSelectores(): array
    {
        $procesoMaquina = [];
        $maquinaProceso = [];

        foreach (ProcesoPlantaCatalogo::paraTransformacion() as $proceso) {
            $ids = self::maquinasParaProceso((int) $proceso->procesoplantaid)
                ->pluck('maquinaplantaid')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
            $procesoMaquina[(int) $proceso->procesoplantaid] = $ids;
        }

        foreach (MaquinaPlanta::query()->where('activo', true)->orderBy('nombre')->get() as $maq) {
            if (in_array($maq->codigo ?? '', self::EXCLUIDAS_TRANSFORMACION, true)) {
                continue;
            }
            $procesosIds = [];
            foreach (ProcesoPlantaCatalogo::paraTransformacion() as $proc) {
                if (self::compatible((int) $proc->procesoplantaid, (int) $maq->maquinaplantaid)) {
                    $procesosIds[] = (int) $proc->procesoplantaid;
                }
            }
            if ($procesosIds !== []) {
                $maquinaProceso[(int) $maq->maquinaplantaid] = $procesosIds;
            }
        }

        return ['proceso_maquina' => $procesoMaquina, 'maquina_proceso' => $maquinaProceso];
    }

    /** @return array<string, list<string>> */
    public static function mapaCodigoProcesos(): array
    {
        return self::MAPA_CODIGO;
    }
}
