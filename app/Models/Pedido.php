<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedido';
    protected $primaryKey = 'pedidoid';
    public $timestamps = false;

    protected $fillable = [
        'numero_solicitud',
        'nombre_planta',
        'cultivo_personalizado',
        'latitud',
        'longitud',
        'direccion_texto',
        'estado',
        'fechapedido',
        'fechaEntregaDeseada',
        'observaciones',
    ];

    protected $casts = [
        'pedidoid'            => 'integer',
        'numero_solicitud'    => 'string',
        'latitud'             => 'float',
        'longitud'            => 'float',
        'fechapedido'         => 'datetime',
        'fechaEntregaDeseada' => 'date',
    ];

    /* ================= RELACIONES ================= */

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedidoid', 'pedidoid');
    }
}