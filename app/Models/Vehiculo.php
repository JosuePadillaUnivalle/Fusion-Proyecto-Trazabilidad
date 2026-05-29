<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    protected $table = 'vehiculo';
    protected $primaryKey = 'vehiculoid';

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'tipovehiculoid',
        'estadovehiculoid',
        'color',
        'activo',
    ];

    protected $casts = [
        'anio'   => 'integer',
        'activo' => 'boolean',
    ];

    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipovehiculoid', 'tipovehiculoid');
    }

    public function estadoVehiculo(): BelongsTo
    {
        return $this->belongsTo(EstadoVehiculo::class, 'estadovehiculoid', 'estadovehiculoid');
    }

    public function perfilesTransportista(): HasMany
    {
        return $this->hasMany(PerfilTransportista::class, 'vehiculoid', 'vehiculoid');
    }
}
