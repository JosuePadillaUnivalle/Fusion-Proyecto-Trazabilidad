<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Tipos de vehículo (columna codigo añadida en Bloque A para referencia estable).
 */
class TipoVehiculo extends Model
{
    protected $table = 'tipo_vehiculo';

    protected $primaryKey = 'tipovehiculoid';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tamano',
        'licencia_requerida',
        'capacidad_kg',
        'capacidad_m3',
        'largo_m',
        'ancho_m',
        'alto_m',
        'factor_volumen_util',
        'activo',
        'codigo',
    ];

    protected $casts = [
        'capacidad_kg' => 'decimal:2',
        'capacidad_m3' => 'decimal:2',
        'largo_m' => 'decimal:2',
        'ancho_m' => 'decimal:2',
        'alto_m' => 'decimal:2',
        'factor_volumen_util' => 'decimal:3',
        'activo' => 'boolean',
    ];

    public function tiposTransporte(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoTransporte::class,
            'tipo_vehiculo_tipo_transporte',
            'tipovehiculoid',
            'tipotransporteid'
        );
    }
}
