<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaquinaVariablePlanta extends Model
{
    protected $table = 'maquina_variable_planta';
    protected $primaryKey = 'maquinavariableid';

    protected $fillable = [
        'maquinaplantaid', 'variableestandarid', 'valor_minimo', 'valor_maximo', 'valor_objetivo', 'obligatorio',
    ];

    protected $casts = [
        'valor_minimo' => 'float',
        'valor_maximo' => 'float',
        'valor_objetivo' => 'float',
        'obligatorio' => 'boolean',
    ];

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(MaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function variableEstandar(): BelongsTo
    {
        return $this->belongsTo(VariableEstandar::class, 'variableestandarid', 'variableestandarid');
    }
}
