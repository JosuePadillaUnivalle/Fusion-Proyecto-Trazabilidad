<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlmacenajeLoteProduccion extends Model
{
    protected $table = 'almacenaje_lote_produccion';
    protected $primaryKey = 'almacenajeloteid';

    protected $fillable = [
        'loteproduccionpedidoid',
        'ubicacion',
        'condicion',
        'cantidad',
        'observaciones',
        'latitud_recojo',
        'longitud_recojo',
        'direccion_recojo',
        'referencia_recojo',
        'fecha_almacenaje',
        'fecha_retiro',
    ];

    protected $casts = [
        'cantidad'          => 'float',
        'latitud_recojo'    => 'float',
        'longitud_recojo'   => 'float',
        'fecha_almacenaje'  => 'datetime',
        'fecha_retiro'      => 'datetime',
    ];

    public function loteProduccionPedido(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }
}
