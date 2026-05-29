<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoDestinoPedido extends Model
{
    protected $table = 'producto_destino_pedido';
    protected $primaryKey = 'productodestinopedidoid';

    protected $fillable = [
        'pedidodestinoid',
        'detallepedidoid',
        'cantidad',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'float',
    ];

    public function destino(): BelongsTo
    {
        return $this->belongsTo(PedidoDestino::class, 'pedidodestinoid', 'pedidodestinoid');
    }

    public function detallePedido(): BelongsTo
    {
        return $this->belongsTo(DetallePedido::class, 'detallepedidoid', 'detallepedidoid');
    }
}
