<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcesoPlanta extends Model
{
    protected $table = 'proceso_planta';
    protected $primaryKey = 'procesoplantaid';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected $casts = [
        'procesoplantaid' => 'integer',
        'activo' => 'boolean',
    ];

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function procesoMaquinas(): HasMany
    {
        return $this->hasMany(ProcesoMaquinaPlanta::class, 'procesoplantaid', 'procesoplantaid')
            ->orderBy('orden_paso');
    }

    /** Pasos del proceso (línea proceso–máquina), usado en registro de planta. */
    public function pasos(): HasMany
    {
        return $this->hasMany(ProcesoMaquinaPlanta::class, 'procesoplantaid', 'procesoplantaid')
            ->orderBy('orden_paso');
    }
}

