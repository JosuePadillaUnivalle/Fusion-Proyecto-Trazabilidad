<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosPlanta extends Model
{
    protected $table = 'datos_planta';
    protected $primaryKey = 'datosplantaid';

    protected $fillable = [
        'nombre', 'direccion', 'ciudad', 'departamento', 'pais',
        'latitud', 'longitud', 'telefono', 'email',
    ];

    protected $casts = [
        'latitud'  => 'float',
        'longitud' => 'float',
    ];
}
