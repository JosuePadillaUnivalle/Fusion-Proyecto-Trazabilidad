<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrTokenAsignacion extends Model
{
    protected $table = 'qrtoken_asignacion';
    protected $primaryKey = 'qrtokenasignacionid';

    protected $fillable = [
        'envioasignacionmultipleid',
        'estadoqrtokenid',
        'token',
        'imagenqr',
        'fecha_creacion',
        'fecha_expiracion',
    ];

    protected $casts = [
        'fecha_creacion'   => 'datetime',
        'fecha_expiracion' => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoQrtoken::class, 'estadoqrtokenid', 'estadoqrtokenid');
    }
}
