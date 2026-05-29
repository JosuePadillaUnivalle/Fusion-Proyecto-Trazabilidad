<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoAsignacionMultipleCatalogo extends Model
{
    public $timestamps = false;

    protected $table = 'estado_asignacion_multiple_catalogo';
    protected $primaryKey = 'estadoasignacioncatalogoid';

    protected $fillable = ['nombre'];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(EnvioAsignacionMultiple::class, 'estadoasignacioncatalogoid', 'estadoasignacioncatalogoid');
    }
}
