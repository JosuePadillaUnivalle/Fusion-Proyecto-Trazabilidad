<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteProduccionRutaPaso extends Model
{
    protected $table = 'lote_produccion_ruta_paso';

    protected $primaryKey = 'loteproduccionrutapasoid';

    protected $fillable = [
        'loteproduccionpedidoid',
        'orden',
        'procesoplantaid',
        'maquinaplantaid',
        'notas',
        'plantillapasoid',
    ];

    protected $casts = [
        'orden' => 'integer',
        'procesoplantaid' => 'integer',
        'maquinaplantaid' => 'integer',
        'plantillapasoid' => 'integer',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(ProcesoPlanta::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(MaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function pasoPlantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaTransformacionPaso::class, 'plantillapasoid', 'plantillapasoid');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(LoteProduccionRutaPasoVariable::class, 'loteproduccionrutapasoid', 'loteproduccionrutapasoid');
    }
}
