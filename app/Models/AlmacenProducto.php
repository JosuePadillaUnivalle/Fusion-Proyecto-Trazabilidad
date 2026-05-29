<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlmacenProducto extends Model
{
    protected $table = 'almacen_producto';
    protected $primaryKey = 'almacenproductoid';

    protected $fillable = [
        'productodistribucionid', 'almacenid', 'stock', 'stock_minimo', 'en_pedido',
    ];

    protected $casts = [
        'stock'        => 'float',
        'stock_minimo' => 'float',
        'en_pedido'    => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoDistribucion::class, 'productodistribucionid', 'productodistribucionid');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }
}
