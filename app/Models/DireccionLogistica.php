<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DireccionLogistica extends Model
{
    protected $table = 'direccion_logistica';
    protected $primaryKey = 'direccionlogisticaid';

    protected $fillable = [
        'nombre', 'direccion_completa', 'ciudad', 'departamento', 'pais',
        'latitud', 'longitud', 'referencia', 'activo',
    ];

    protected $casts = [
        'latitud'  => 'float',
        'longitud' => 'float',
        'activo'   => 'boolean',
    ];

    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'direccionlogisticaid', 'direccionlogisticaid');
    }
}
