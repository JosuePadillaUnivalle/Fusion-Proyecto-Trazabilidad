<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActorAbastecimiento extends Model
{
    protected $table = 'actor_abastecimiento';
    protected $primaryKey = 'actorid';

    protected $fillable = [
        'nombre',
        'tipo_actor',
        'email',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'actorid' => 'integer',
        'activo' => 'boolean',
    ];

    public function insumos(): HasMany
    {
        return $this->hasMany(Insumo::class, 'actorid', 'actorid');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'actorid', 'actorid');
    }
}

