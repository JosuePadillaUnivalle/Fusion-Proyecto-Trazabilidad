<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperadorPlanta extends Model
{
    protected $table = 'operador_planta';
    protected $primaryKey = 'operadorplantaid';

    protected $fillable = [
        'nombre',
        'apellido',
        'usuario',
        'password_hash',
        'email',
        'usuarioid',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function usuarioFusion(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }
}
