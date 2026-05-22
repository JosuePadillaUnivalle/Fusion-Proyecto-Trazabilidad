<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaProveedorSolicitud extends Model
{
    protected $table = 'respuesta_proveedor_solicitud';
    protected $primaryKey = 'respuestaproveedorid';

    protected $fillable = [
        'solicitudmaterialid',
        'proveedor_actorid',
        'fecha_respuesta',
        'cantidad_confirmada',
        'fecha_entrega',
        'observaciones',
        'precio',
    ];

    protected $casts = [
        'fecha_respuesta'     => 'datetime',
        'fecha_entrega'       => 'date',
        'cantidad_confirmada' => 'float',
        'precio'              => 'float',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudMaterialPedido::class, 'solicitudmaterialid', 'solicitudmaterialid');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ActorAbastecimiento::class, 'proveedor_actorid', 'actorid');
    }
}
