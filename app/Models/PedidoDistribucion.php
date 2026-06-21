<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoDistribucion extends Model
{
    protected $table = 'pedido_distribucion';

    protected $primaryKey = 'pedidodistribucionid';

    public $timestamps = false;

    protected $fillable = [
        'numero_solicitud',
        'puntoventaid',
        'almacen_planta_origenid',
        'almacen_mayorista_origenid',
        'rutadistribucionid',
        'transportista_usuarioid',
        'vehiculoid',
        'estado',
        'fechapedido',
        'fecha_entrega_deseada',
        'observaciones',
        'fecha_aceptacion',
        'aceptado_por_usuarioid',
        'fecha_envio',
        'fecha_recepcion',
        'creado_por_usuarioid',
    ];

    protected $casts = [
        'pedidodistribucionid' => 'integer',
        'puntoventaid' => 'integer',
        'almacen_planta_origenid' => 'integer',
        'almacen_mayorista_origenid' => 'integer',
        'rutadistribucionid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'vehiculoid' => 'integer',
        'fechapedido' => 'datetime',
        'fecha_entrega_deseada' => 'date',
        'fecha_aceptacion' => 'datetime',
        'fecha_envio' => 'datetime',
        'fecha_recepcion' => 'datetime',
        'aceptado_por_usuarioid' => 'integer',
        'creado_por_usuarioid' => 'integer',
    ];

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'puntoventaid', 'puntoventaid');
    }

    public function almacenPlantaOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_planta_origenid', 'almacenid');
    }

    public function almacenMayoristaOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_mayorista_origenid', 'almacenid');
    }

    public function aceptadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'aceptado_por_usuarioid', 'usuarioid');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por_usuarioid', 'usuarioid');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedidoDistribucion::class, 'pedidodistribucionid', 'pedidodistribucionid');
    }

    public function rutaDistribucion(): BelongsTo
    {
        return $this->belongsTo(RutaDistribucion::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function transportista(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'transportista_usuarioid', 'usuarioid');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculoid', 'vehiculoid');
    }

    public function getRouteKeyName(): string
    {
        return 'pedidodistribucionid';
    }
}
