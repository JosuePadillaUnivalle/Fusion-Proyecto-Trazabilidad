<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribucionDetallePedidoAlmacen extends Model
{
    protected $table = 'distribucion_detalle_pedido_almacen';

    protected $primaryKey = 'distribuciondetallepedidoid';

    protected $fillable = [
        'distribucionpedidoid', 'productodistribucionid', 'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'float',
    ];

    public function pedidoAlmacen(): BelongsTo
    {
        return $this->belongsTo(DistribucionPedidoAlmacen::class, 'distribucionpedidoid', 'distribucionpedidoid');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoDistribucion::class, 'productodistribucionid', 'productodistribucionid');
    }
}
