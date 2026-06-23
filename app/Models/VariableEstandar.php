<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariableEstandar extends Model
{
    protected $table = 'variable_estandar';
    protected $primaryKey = 'variableestandarid';

    protected $fillable = ['codigo', 'nombre', 'unidad', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function variablesProceso(): HasMany
    {
        return $this->hasMany(VariableProcesoMaquinaPlanta::class, 'variableestandarid', 'variableestandarid');
    }

    public function variablesMaquina(): HasMany
    {
        return $this->hasMany(MaquinaVariablePlanta::class, 'variableestandarid', 'variableestandarid');
    }
}
