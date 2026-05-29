<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribucionPedidoAlmacen extends Model
{
    protected $table = 'distribucion_pedido_almacen';

    protected $primaryKey = 'distribucionpedidoid';

    protected $fillable = [
        'codigo_comprobante', 'fecha', 'estado', 'almacenid', 'operador_usuarioid',
        'transportista_usuarioid', 'proveedor_actorid', 'administrador_usuarioid',
    ];

    protected $casts = [
        'fecha' => 'date',
        'estado' => 'integer',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DistribucionDetallePedidoAlmacen::class, 'distribucionpedidoid', 'distribucionpedidoid');
    }
}
