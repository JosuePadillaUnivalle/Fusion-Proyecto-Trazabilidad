<?php

namespace App\Models;

use App\Support\UbicacionGpsParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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

    public function direccionParaMostrar(): ?string
    {
        return UbicacionGpsParser::direccionLegible($this->direccion);
    }

    /** @return array{direccion: string, estimada: bool} */
    public function ubicacionVisible(): array
    {
        return UbicacionGpsParser::referenciaPuntoVenta(
            $this->direccion,
            $this->latitud,
            $this->longitud,
            $this->puntoventaid ? (int) $this->puntoventaid : null,
            $this->nombre
        );
    }

    public function resumenUbicacion(): string
    {
        $ubicacion = $this->ubicacionVisible();

        return Str::limit($ubicacion['direccion'], 60);
    }

    public function getRouteKeyName(): string
    {
        return 'puntoventaid';
    }
}
