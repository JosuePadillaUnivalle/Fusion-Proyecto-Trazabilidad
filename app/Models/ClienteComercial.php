<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteComercial extends Model
{
    protected $table = 'cliente_comercial';
    protected $primaryKey = 'clientecomercialid';

    protected $fillable = [
        'razon_social', 'nombre_comercial', 'nit', 'direccion', 'telefono', 'email', 'contacto', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'clientecomercialid', 'clientecomercialid');
    }
}
