<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvioPendiente extends Model
{
    protected $table = 'envios_pendientes';

    protected $fillable = [
        'datos_envio',
        'estado',
        'intentos',
        'ultimo_error',
        'ultimo_intento',
        'enviado_at',
        'usuarioid',
    ];

    protected $casts = [
        'datos_envio' => 'array',
        'ultimo_intento' => 'datetime',
        'enviado_at' => 'datetime',
    ];

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeFallidos($query)
    {
        return $query->where('estado', 'fallido')->where('intentos', '<', 5);
    }

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuarioid');
    }
}