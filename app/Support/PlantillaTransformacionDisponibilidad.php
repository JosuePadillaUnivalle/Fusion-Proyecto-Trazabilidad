<?php

namespace App\Support;

use App\Models\PlantillaTransformacion;
use App\Models\PlantillaTransformacionPaso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PlantillaTransformacionDisponibilidad
{
    public static function pasoBloqueadoPorMantenimiento(PlantillaTransformacionPaso $paso): bool
    {
        $paso->loadMissing(['proceso', 'maquina']);

        if ($paso->maquinaplantaid && $paso->maquina) {
            return ! $paso->maquina->activo;
        }

        if (! $paso->procesoplantaid) {
            return false;
        }

        $compatibles = MaquinaProcesoCompatibilidad::todasMaquinasParaProceso((int) $paso->procesoplantaid);
        if ($compatibles->isEmpty()) {
            return false;
        }

        return $compatibles->every(fn ($m) => ! $m->activo);
    }

    public static function plantillaBloqueada(PlantillaTransformacion $plantilla): bool
    {
        $plantilla->loadMissing(['pasos.proceso', 'pasos.maquina']);

        return $plantilla->pasos->contains(fn (PlantillaTransformacionPaso $paso) => self::pasoBloqueadoPorMantenimiento($paso));
    }

    /** @return Collection<int, \App\Models\MaquinaPlanta> */
    public static function maquinasQueBloqueanPaso(PlantillaTransformacionPaso $paso): Collection
    {
        $paso->loadMissing(['proceso', 'maquina']);

        if ($paso->maquinaplantaid && $paso->maquina && ! $paso->maquina->activo) {
            return collect([$paso->maquina]);
        }

        if ($paso->maquinaplantaid) {
            return collect();
        }

        if (! $paso->procesoplantaid) {
            return collect();
        }

        $compatibles = MaquinaProcesoCompatibilidad::todasMaquinasParaProceso((int) $paso->procesoplantaid);

        return $compatibles->filter(fn ($m) => ! $m->activo)->values();
    }

    /** @return list<int> */
    public static function idsBloqueadas(): array
    {
        return PlantillaTransformacion::query()
            ->with(['pasos.proceso', 'pasos.maquina'])
            ->get()
            ->filter(fn (PlantillaTransformacion $p) => self::plantillaBloqueada($p))
            ->pluck('plantillatransformacionid')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function scopeOperativas(Builder $query): Builder
    {
        $bloqueadas = self::idsBloqueadas();
        if ($bloqueadas === []) {
            return $query;
        }

        return $query->whereNotIn('plantillatransformacionid', $bloqueadas);
    }

    public static function scopeBloqueadasPorMantenimiento(Builder $query): Builder
    {
        $bloqueadas = self::idsBloqueadas();
        if ($bloqueadas === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('plantillatransformacionid', $bloqueadas);
    }

}
