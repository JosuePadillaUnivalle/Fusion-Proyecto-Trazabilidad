<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoCarga extends Model
{
    protected $table = 'catalogo_carga';
    protected $primaryKey = 'catalogocargaid';

    protected $fillable = [
        'tipo',
        'variedad',
        'empaque',
        'descripcion',
    ];

    public function cargasEnvio(): HasMany
    {
        return $this->hasMany(CargaEnvio::class, 'catalogocargaid', 'catalogocargaid');
    }
}
