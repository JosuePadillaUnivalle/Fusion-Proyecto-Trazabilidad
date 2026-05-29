<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudMaterialPedido extends Model
{
    protected $table = 'solicitud_material_pedido';
    protected $primaryKey = 'solicitudmaterialid';

    protected $fillable = [
        'pedidoid',
        'numero_solicitud',
        'fecha_solicitud',
        'fecha_requerida',
        'observaciones',
        'direccion',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'fecha_solicitud'  => 'date',
        'fecha_requerida'  => 'date',
        'latitud'          => 'float',
        'longitud'         => 'float',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleSolicitudMaterial::class, 'solicitudmaterialid', 'solicitudmaterialid');
    }

    public function respuestasProveedor(): HasMany
    {
        return $this->hasMany(RespuestaProveedorSolicitud::class, 'solicitudmaterialid', 'solicitudmaterialid');
    }
}
