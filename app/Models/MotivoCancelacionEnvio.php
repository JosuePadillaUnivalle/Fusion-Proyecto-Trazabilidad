<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoCancelacionEnvio extends Model
{
    protected $table = 'motivo_cancelacion_envio';
    protected $primaryKey = 'motivocancelacionid';

    protected $fillable = [
        'codigo',
        'titulo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(EnvioAsignacionMultiple::class, 'motivocancelacionid', 'motivocancelacionid');
    }
}
