<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionQrEnvio extends Model
{
    protected $table = 'recepcion_qr_envio';

    protected $primaryKey = 'recepcionqrenvioid';

    protected $fillable = [
        'token',
        'rutadistribucionid',
        'envioasignacionmultipleid',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(RutaDistribucion::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }
}
