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
        'origen_latitud',
        'origen_longitud',
        'origen_direccion',
        'latitud',
        'longitud',
        'direccion_texto',
        'estado',
        'fechapedido',
        'fechaEntregaDeseada',
        'hora_recogida',
        'hora_entrega_estimada',
        'instrucciones_recogida',
        'instrucciones_entrega',
        'observaciones',
        'fecha_aceptacion_agricola',
        'aceptado_por_usuarioid',
    ];

    protected $casts = [
        'pedidoid'            => 'integer',
        'numero_solicitud'    => 'string',
        'origen_latitud'      => 'float',
        'origen_longitud'     => 'float',
        'latitud'             => 'float',
        'longitud'            => 'float',
        'fechapedido'         => 'datetime',
        'fechaEntregaDeseada' => 'date',
        'fecha_aceptacion_agricola' => 'datetime',
    ];

    /* ================= RELACIONES ================= */

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedidoid', 'pedidoid');
    }

    public function aceptadoPor()
    {
        return $this->belongsTo(Usuario::class, 'aceptado_por_usuarioid', 'usuarioid');
    }

    public function envioAsignacion()
    {
        return $this->hasOne(EnvioAsignacionMultiple::class, 'pedidoid', 'pedidoid');
    }
}