<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioNotificacion extends Model
{
    protected $table = 'usuario_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'usuarioid',
        'tipo',
        'titulo',
        'mensaje',
        'enlace',
        'referencia_tipo',
        'referencia_id',
        'leida_at',
        'creado_en',
    ];

    protected $casts = [
        'usuarioid' => 'integer',
        'referencia_id' => 'integer',
        'leida_at' => 'datetime',
        'creado_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function noLeida(): bool
    {
        return $this->leida_at === null;
    }
}
