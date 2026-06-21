<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    protected $table = 'vehiculo';
    protected $primaryKey = 'vehiculoid';

    public function getRouteKeyName(): string
    {
        return 'vehiculoid';
    }

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'tipovehiculoid',
        'estadovehiculoid',
        'color',
        'activo',
        'ambito_flota',
        'capacidad_kg_override',
        'capacidad_m3_override',
        'largo_m_override',
        'ancho_m_override',
        'alto_m_override',
    ];

    protected $casts = [
        'anio'   => 'integer',
        'activo' => 'boolean',
        'capacidad_kg_override' => 'decimal:2',
        'capacidad_m3_override' => 'decimal:2',
        'largo_m_override' => 'decimal:2',
        'ancho_m_override' => 'decimal:2',
        'alto_m_override' => 'decimal:2',
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

    public function tiposTransporte(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoTransporte::class,
            'vehiculo_tipo_transporte',
            'vehiculoid',
            'tipotransporteid'
        );
    }

    /** Tipos de transporte de la unidad; si no tiene, los del tipo de vehículo. */
    public function tiposTransporteEfectivos(): \Illuminate\Support\Collection
    {
        $this->loadMissing(['tiposTransporte', 'tipoVehiculo.tiposTransporte']);

        if ($this->tiposTransporte->isNotEmpty()) {
            return $this->tiposTransporte;
        }

        return $this->tipoVehiculo?->tiposTransporte ?? collect();
    }
}
