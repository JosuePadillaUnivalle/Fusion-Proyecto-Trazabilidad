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
        'clientecomercialid',
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
        'clientecomercialid' => 'integer',
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

    public function clienteComercial()
    {
        return $this->belongsTo(ClienteComercial::class, 'clientecomercialid', 'clientecomercialid');
    }

    public function destinos()
    {
        return $this->hasMany(PedidoDestino::class, 'pedidoid', 'pedidoid');
    }

    public function seguimientosEnvio()
    {
        return $this->hasMany(SeguimientoEnvioPedido::class, 'pedidoid', 'pedidoid');
    }

    public function asignacionesMultipleEnvio()
    {
        return $this->hasMany(EnvioAsignacionMultiple::class, 'pedidoid', 'pedidoid');
    }

    public function calificacionesEnvio()
    {
        return $this->hasMany(CalificacionEnvio::class, 'pedidoid', 'pedidoid');
    }

    public function lotesProduccionPedido()
    {
        return $this->hasMany(LoteProduccionPedido::class, 'pedidoid', 'pedidoid');
    }

    public function solicitudesMaterial()
    {
        return $this->hasMany(SolicitudMaterialPedido::class, 'pedidoid', 'pedidoid');
    }
}