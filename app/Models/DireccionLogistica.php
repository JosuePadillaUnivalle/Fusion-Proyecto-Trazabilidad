<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DireccionLogistica extends Model
{
    protected $table = 'direccion_logistica';
    protected $primaryKey = 'direccionlogisticaid';

    public function getRouteKeyName(): string
    {
        return 'direccionlogisticaid';
    }

    protected $fillable = [
        'nombre', 'tipo_punto', 'direccion_completa', 'ciudad', 'departamento', 'pais',
        'latitud', 'longitud', 'referencia', 'activo',
    ];

    public function etiquetaTipo(): string
    {
        return match (strtolower((string) ($this->tipo_punto ?? ''))) {
            'origen' => 'Origen',
            'destino' => 'Destino',
            default => 'Punto logístico',
        };
    }

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
