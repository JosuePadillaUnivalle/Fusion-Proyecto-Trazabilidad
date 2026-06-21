<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoEnvio extends Model
{
    protected $table = 'historial_estado_envio';
    protected $primaryKey = 'historialestadoenvioid';

    protected $fillable = [
        'envioasignacionmultipleid',
        'externo_envio_id',
        'estadoenviocatalogoid',
        'fecha',
    ];

    protected $casts = [
        'historialestadoenvioid'        => 'integer',
        'envioasignacionmultipleid'     => 'integer',
        'estadoenviocatalogoid'         => 'integer',
        'fecha'                         => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function estadoCatalogo(): BelongsTo
    {
        return $this->belongsTo(EstadoEnvioCatalogo::class, 'estadoenviocatalogoid', 'estadoenviocatalogoid');
    }
}
