<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutaParada extends Model
{
    protected $table = 'ruta_parada';
    protected $primaryKey = 'rutaparadaid';

    protected $fillable = [
        'rutamultientregaid',
        'pedidoid',
        'externo_envio_id',
        'orden',
        'destino',
        'latitud',
        'longitud',
        'estado',
        'eta',
        'fecha_entrega',
    ];

    protected $casts = [
        'rutaparadaid' => 'integer',
        'rutamultientregaid' => 'integer',
        'pedidoid' => 'integer',
        'orden' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
        'eta' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(RutaMultiEntrega::class, 'rutamultientregaid', 'rutamultientregaid');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }
}

