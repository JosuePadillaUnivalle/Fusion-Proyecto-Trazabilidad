<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaRecepcionEnvio extends Model
{
    protected $table = 'firma_recepcion_envio';
    protected $primaryKey = 'firmarecepcionid';

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
