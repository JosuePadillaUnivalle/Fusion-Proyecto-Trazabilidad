<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutaDistribucionParada extends Model
{
    protected $table = 'ruta_distribucion_parada';

    protected $primaryKey = 'rutadistribucionparadaid';

    protected $fillable = [
        'rutadistribucionid',
        'orden',
        'tipo',
        'almacenid',
        'puntoventaid',
        'pedidodistribucionid',
        'destino',
        'latitud',
        'longitud',
        'estado',
    ];

    protected $casts = [
        'rutadistribucionparadaid' => 'integer',
        'rutadistribucionid' => 'integer',
        'orden' => 'integer',
        'almacenid' => 'integer',
        'puntoventaid' => 'integer',
        'pedidodistribucionid' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(RutaDistribucion::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoDistribucion::class, 'pedidodistribucionid', 'pedidodistribucionid');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'puntoventaid', 'puntoventaid');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }
}
