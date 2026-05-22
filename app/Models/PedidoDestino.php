<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoDestino extends Model
{
    protected $table = 'pedido_destino';
    protected $primaryKey = 'pedidodestinoid';

    protected $fillable = [
        'pedidoid', 'direccion', 'referencia', 'latitud', 'longitud',
        'nombre_contacto', 'telefono_contacto', 'instrucciones_entrega',
        'almacen_origenid', 'almacen_origen_nombre', 'almacen_destinoid', 'almacen_destino_nombre',
        'almacen_externo_psii_id',
    ];

    protected $casts = [
        'latitud'  => 'float',
        'longitud' => 'float',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoEnvioPedido::class, 'pedidodestinoid', 'pedidodestinoid');
    }

    public function productosDestinoPedido(): HasMany
    {
        return $this->hasMany(ProductoDestinoPedido::class, 'pedidodestinoid', 'pedidodestinoid');
    }
}
