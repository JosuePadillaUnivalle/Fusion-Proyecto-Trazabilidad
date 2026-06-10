<?php

namespace App\Models;

use App\Support\PlantillaTransformacionDisponibilidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PlantillaTransformacion extends Model
{
    protected $table = 'plantilla_transformacion';
    protected $primaryKey = 'plantillatransformacionid';

    protected $fillable = [
        'nombre',
        'descripcion',
        'producto_ejemplo',
        'palabras_clave',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function pasos(): HasMany
    {
        return $this->hasMany(PlantillaTransformacionPaso::class, 'plantillatransformacionid', 'plantillatransformacionid')
            ->orderBy('orden');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(LoteProduccionPedido::class, 'plantillatransformacionid', 'plantillatransformacionid');
    }

    /** @return list<string> */
    public function palabrasClaveLista(): array
    {
        if ($this->palabras_clave === null || trim($this->palabras_clave) === '') {
            return [];
        }

        $decoded = json_decode($this->palabras_clave, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded)));
        }

        return array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $this->palabras_clave) ?: [])));
    }

    public function scopeOperativas(Builder $query): Builder
    {
        return PlantillaTransformacionDisponibilidad::scopeOperativas($query);
    }

    public function scopeBloqueadasPorMantenimiento(Builder $query): Builder
    {
        return PlantillaTransformacionDisponibilidad::scopeBloqueadasPorMantenimiento($query);
    }

    public function bloqueadaPorMantenimiento(): bool
    {
        return PlantillaTransformacionDisponibilidad::plantillaBloqueada($this);
    }

    /** @return Collection<int, MaquinaPlanta> */
    public function maquinasEnMantenimiento(): Collection
    {
        $this->loadMissing(['pasos.proceso', 'pasos.maquina']);

        return $this->pasos
            ->flatMap(fn (PlantillaTransformacionPaso $paso) => PlantillaTransformacionDisponibilidad::maquinasQueBloqueanPaso($paso))
            ->unique('maquinaplantaid')
            ->values();
    }

    public function estaOperativa(): bool
    {
        return ! $this->bloqueadaPorMantenimiento();
    }

    public function motivoInactivo(): ?string
    {
        return $this->bloqueadaPorMantenimiento() ? 'mantenimiento' : null;
    }

    public function etiquetaEstado(): string
    {
        return $this->bloqueadaPorMantenimiento() ? 'En mantenimiento' : 'Activa';
    }
}
