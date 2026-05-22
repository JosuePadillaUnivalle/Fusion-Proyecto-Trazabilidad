<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteProduccionPedido extends Model
{
    protected $table = 'lote_produccion_pedido';
    protected $primaryKey = 'loteproduccionpedidoid';

    protected $fillable = [
        'pedidoid',
        'codigo_lote',
        'nombre',
        'fecha_creacion',
        'hora_inicio',
        'hora_fin',
        'cantidad_objetivo',
        'cantidad_producida',
        'observaciones',
    ];

    protected $casts = [
        'fecha_creacion'      => 'date',
        'hora_inicio'         => 'datetime',
        'hora_fin'            => 'datetime',
        'cantidad_objetivo'   => 'float',
        'cantidad_producida'  => 'float',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function materiasPrimas(): HasMany
    {
        return $this->hasMany(LoteProduccionMateriaPrima::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function evaluacionesFinales(): HasMany
    {
        return $this->hasMany(EvaluacionFinalLoteProduccion::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function almacenajes(): HasMany
    {
        return $this->hasMany(AlmacenajeLoteProduccion::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }
}
