<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de tipos de transporte (semillas Bloque A / tabla tipo_transporte).
 */
class TipoTransporte extends Model
{
    protected $table = 'tipo_transporte';

    protected $primaryKey = 'tipotransporteid';

    protected $fillable = ['nombre', 'descripcion', 'codigo'];

    public function asignacionesEnvio(): HasMany
    {
        return $this->hasMany(EnvioAsignacionMultiple::class, 'tipotransporteid', 'tipotransporteid');
    }

    public function tiposVehiculo(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoVehiculo::class,
            'tipo_vehiculo_tipo_transporte',
            'tipotransporteid',
            'tipovehiculoid'
        );
    }
}
