<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoEnvioGps extends Model
{
    public $timestamps = false;

    protected $table = 'seguimiento_envio_gps';
    protected $primaryKey = 'seguimientogpsid';

    protected $fillable = [
        'envioasignacionmultipleid', 'externo_envio_id', 'latitud', 'longitud', 'velocidad', 'registrado_en',
    ];

    protected $casts = [
        'envioasignacionmultipleid' => 'integer',
        'latitud'                   => 'float',
        'longitud'                  => 'float',
        'velocidad'                 => 'float',
        'registrado_en'             => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }
}
