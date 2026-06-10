<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaTransformacionPaso extends Model
{
    protected $table = 'plantilla_transformacion_paso';
    protected $primaryKey = 'plantillapasoid';

    protected $fillable = [
        'plantillatransformacionid',
        'orden',
        'procesoplantaid',
        'maquinaplantaid',
        'notas',
    ];

    protected $casts = [
        'orden' => 'integer',
        'procesoplantaid' => 'integer',
        'maquinaplantaid' => 'integer',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaTransformacion::class, 'plantillatransformacionid', 'plantillatransformacionid');
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(ProcesoPlanta::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(MaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }
}
