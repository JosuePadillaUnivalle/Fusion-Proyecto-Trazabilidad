<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoVehiculo extends Model
{
    public $timestamps = false;

    protected $table = 'estado_vehiculo';
    protected $primaryKey = 'estadovehiculoid';

    protected $fillable = ['nombre'];

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'estadovehiculoid', 'estadovehiculoid');
    }
}
