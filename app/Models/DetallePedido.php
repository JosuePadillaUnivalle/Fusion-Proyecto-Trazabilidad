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
        'insumoid',
        'producto_ref',
        'produccionalmacenamientoid',
        'nombre_planta',
        'cultivo_personalizado',
        'cantidad',
        'observaciones',
    ];

    protected $casts = [
        'detallepedidoid' => 'integer',
        'pedidoid'        => 'integer',
        'insumoid'        => 'integer',
        'produccionalmacenamientoid' => 'integer',
        'cantidad'        => 'float',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumoid', 'insumoid');
    }

    public function cosechaAlmacen()
    {
        return $this->belongsTo(ProduccionAlmacenamiento::class, 'produccionalmacenamientoid', 'produccionalmacenamientoid');
    }
}