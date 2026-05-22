<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoEnvioPedido extends Model
{
    protected $table = 'seguimiento_envio_pedido';
    protected $primaryKey = 'seguimientoenviopedidoid';

    protected $fillable = [
        'pedidoid', 'pedidodestinoid', 'externo_envio_id', 'codigo_envio', 'estado',
        'mensaje_error', 'datos_solicitud', 'datos_respuesta',
    ];

    protected $casts = [
        'datos_solicitud'  => 'array',
        'datos_respuesta' => 'array',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(PedidoDestino::class, 'pedidodestinoid', 'pedidodestinoid');
    }
}
