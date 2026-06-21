<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariableProcesoMaquinaPlanta extends Model
{
    protected $table = 'variable_proceso_maquina_planta';
    protected $primaryKey = 'variableprocesomaquinaid';

    protected $fillable = [
        'procesomaquinaplantaid', 'variableestandarid', 'valor_minimo', 'valor_maximo', 'valor_objetivo', 'obligatorio',
    ];

    protected $casts = [
        'valor_minimo'   => 'float',
        'valor_maximo'   => 'float',
        'valor_objetivo' => 'float',
        'obligatorio'    => 'boolean',
    ];

    public function procesoMaquina(): BelongsTo
    {
        return $this->belongsTo(ProcesoMaquinaPlanta::class, 'procesomaquinaplantaid', 'procesomaquinaplantaid');
    }

    public function variableEstandar(): BelongsTo
    {
        return $this->belongsTo(VariableEstandar::class, 'variableestandarid', 'variableestandarid');
    }
}
