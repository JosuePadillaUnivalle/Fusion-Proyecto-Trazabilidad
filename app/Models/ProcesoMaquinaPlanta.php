<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcesoMaquinaPlanta extends Model
{
    protected $table = 'proceso_maquina_planta';
    protected $primaryKey = 'procesomaquinaplantaid';

    protected $fillable = [
        'procesoplantaid', 'maquinaplantaid', 'orden_paso', 'nombre', 'descripcion', 'tiempo_estimado',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(ProcesoPlanta::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(MaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(VariableProcesoMaquinaPlanta::class, 'procesomaquinaplantaid', 'procesomaquinaplantaid');
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroProcesoMaquinaPlanta::class, 'procesomaquinaplantaid', 'procesomaquinaplantaid');
    }
}
