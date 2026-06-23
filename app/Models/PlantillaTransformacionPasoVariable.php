<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaTransformacionPasoVariable extends Model
{
    protected $table = 'plantilla_transformacion_paso_variable';
    protected $primaryKey = 'plantillapasovariableid';

    protected $fillable = [
        'plantillapasoid', 'variableestandarid', 'valor_minimo', 'valor_maximo', 'valor_objetivo', 'obligatorio',
    ];

    protected $casts = [
        'valor_minimo' => 'float',
        'valor_maximo' => 'float',
        'valor_objetivo' => 'float',
        'obligatorio' => 'boolean',
    ];

    public function paso(): BelongsTo
    {
        return $this->belongsTo(PlantillaTransformacionPaso::class, 'plantillapasoid', 'plantillapasoid');
    }

    public function variableEstandar(): BelongsTo
    {
        return $this->belongsTo(VariableEstandar::class, 'variableestandarid', 'variableestandarid');
    }
}
