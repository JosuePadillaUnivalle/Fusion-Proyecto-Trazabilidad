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

    public function resumenUbicacion(): string
    {
        $dir = $this->direccionParaMostrar();
        if ($dir) {
            return Str::limit($dir, 60);
        }
        if ($this->latitud && $this->longitud) {
            return 'Ubicación en mapa';
        }

        return 'Sin dirección registrada';
    }

    /**
     * @return array{direccion: string, estimada: bool}
     */
    public function ubicacionVisible(): array
    {
        $dir = $this->direccionParaMostrar();
        if ($dir) {
            return [
                'direccion' => $dir,
                'estimada' => false,
            ];
        }

        if ($this->latitud && $this->longitud) {
            return [
                'direccion' => 'Ubicación en mapa',
                'estimada' => true,
            ];
        }

        return [
            'direccion' => 'Sin ubicación registrada',
            'estimada' => false,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'puntoventaid';
    }
}
