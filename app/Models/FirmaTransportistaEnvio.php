<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaTransportistaEnvio extends Model
{
    protected $table = 'firma_transportista_envio';
    protected $primaryKey = 'firmatransportistaid';

    protected $fillable = [
        'envioasignacionmultipleid',
        'imagenfirma',
        'fechafirma',
    ];

    protected $casts = [
        'fechafirma' => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }
}
