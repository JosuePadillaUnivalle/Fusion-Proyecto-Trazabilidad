<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoMovimientoMateria extends Model
{
    protected $table = 'tipo_movimiento_materia';
    protected $primaryKey = 'tipomovimientomateriaid';

    protected $fillable = ['codigo', 'nombre', 'afecta_stock', 'es_entrada', 'activo'];

    protected $casts = [
        'afecta_stock' => 'boolean',
        'es_entrada'   => 'boolean',
        'activo'       => 'boolean',
    ];

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroMovimientoMateria::class, 'tipomovimientomateriaid', 'tipomovimientomateriaid');
    }
}
