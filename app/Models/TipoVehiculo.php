<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tipos de vehículo (columna codigo añadida en Bloque A para referencia estable).
 */
class TipoVehiculo extends Model
{
    protected $table = 'tipo_vehiculo';

    protected $primaryKey = 'tipovehiculoid';

    protected $fillable = ['nombre', 'descripcion', 'capacidad_kg', 'capacidad_m3', 'activo', 'codigo'];

    protected $casts = [
        'capacidad_kg' => 'decimal:2',
        'capacidad_m3' => 'decimal:2',
        'activo' => 'boolean',
    ];
}
