<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detallepedido';
    protected $primaryKey = 'detallepedidoid';
    public $timestamps = false;

    protected $fillable = [
        'pedidoid',
        'nombre_planta',
        'cultivo_personalizado',
        'cantidad',          // Se asume en kilos
        'observaciones',
    ];

    protected $casts = [
        'detallepedidoid' => 'integer',
        'pedidoid'        => 'integer',
        'cantidad'        => 'float',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }
}