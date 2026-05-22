<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaquinaPlanta extends Model
{
    protected $table = 'maquina_planta';
    protected $primaryKey = 'maquinaplantaid';

    protected $fillable = ['nombre', 'codigo', 'descripcion', 'activo'];

    protected $casts = [
        'maquinaplantaid' => 'integer',
        'activo' => 'boolean',
    ];

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function procesoMaquinas(): HasMany
    {
        return $this->hasMany(ProcesoMaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }
}

