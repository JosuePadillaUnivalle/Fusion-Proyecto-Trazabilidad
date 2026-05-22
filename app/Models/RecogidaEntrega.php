<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RecogidaEntrega extends Model
{
    protected $table = 'recogida_entrega';
    protected $primaryKey = 'recogidaentregaid';

    protected $fillable = [
        'fecha_recogida',
        'hora_recogida',
        'hora_entrega',
        'instrucciones_recogida',
        'instrucciones_entrega',
    ];

    protected $casts = [
        'fecha_recogida' => 'date',
    ];

    public function asignacionEnvio(): HasOne
    {
        return $this->hasOne(EnvioAsignacionMultiple::class, 'recogidaentregaid', 'recogidaentregaid');
    }
}
