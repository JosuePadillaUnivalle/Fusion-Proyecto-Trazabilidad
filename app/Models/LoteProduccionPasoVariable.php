<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteProduccionPasoVariable extends Model
{
    protected $table = 'lote_produccion_paso_variable';

    protected $primaryKey = 'loteproduccionpasovariableid';

    protected $fillable = [
        'loteproduccionpedidoid',
        'plantillapasoid',
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

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function pasoPlantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaTransformacionPaso::class, 'plantillapasoid', 'plantillapasoid');
    }

    public function variableEstandar(): BelongsTo
    {
        return $this->belongsTo(VariableEstandar::class, 'variableestandarid', 'variableestandarid');
    }
}
