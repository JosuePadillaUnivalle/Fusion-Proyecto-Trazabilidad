<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteProduccionRutaPasoVariable extends Model
{
    protected $table = 'lote_produccion_ruta_paso_variable';

    protected $primaryKey = 'loteproduccionrutapasovariableid';

    protected $fillable = [
        'loteproduccionrutapasoid',
        'variableestandarid',
        'valor_minimo',
        'valor_maximo',
        'obligatorio',
    ];

    protected $casts = [
        'valor_minimo' => 'float',
        'valor_maximo' => 'float',
        'obligatorio' => 'boolean',
    ];

    public function pasoRuta(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionRutaPaso::class, 'loteproduccionrutapasoid', 'loteproduccionrutapasoid');
    }

    public function variableEstandar(): BelongsTo
    {
        return $this->belongsTo(VariableEstandar::class, 'variableestandarid', 'variableestandarid');
    }
}
