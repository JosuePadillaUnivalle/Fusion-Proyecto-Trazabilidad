<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PuntoVenta extends Model
{
    protected $table = 'punto_venta';

    protected $primaryKey = 'puntoventaid';

    public $timestamps = false;

    protected $fillable = [
        'usuarioid',
        'almacenid',
        'nombre',
        'direccion',
        'latitud',
        'longitud',
        'activo',
        'observaciones',
        'fechacreacion',
    ];

    protected $casts = [
        'puntoventaid' => 'integer',
        'usuarioid' => 'integer',
        'almacenid' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
        'activo' => 'boolean',
        'fechacreacion' => 'datetime',
    ];

    public function minorista(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function pedidosDistribucion(): HasMany
    {
        return $this->hasMany(PedidoDistribucion::class, 'puntoventaid', 'puntoventaid');
    }

    public function nombreMinorista(): string
    {
        $u = $this->minorista;

        return trim(($u?->nombre ?? '').' '.($u?->apellido ?? '')) ?: '—';
    }

    public function getRouteKeyName(): string
    {
        return 'puntoventaid';
    }
}
