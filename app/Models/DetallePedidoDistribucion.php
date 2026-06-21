<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedidoDistribucion extends Model
{
    protected $table = 'detalle_pedido_distribucion';

    protected $primaryKey = 'detallepedidodistribucionid';

    public $timestamps = false;

    protected $fillable = [
        'pedidodistribucionid',
        'insumoid',
        'producto_nombre',
        'cantidad',
        'observaciones',
    ];

    protected $casts = [
        'detallepedidodistribucionid' => 'integer',
        'pedidodistribucionid' => 'integer',
        'insumoid' => 'integer',
        'cantidad' => 'float',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoDistribucion::class, 'pedidodistribucionid', 'pedidodistribucionid');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumoid', 'insumoid');
    }
}
