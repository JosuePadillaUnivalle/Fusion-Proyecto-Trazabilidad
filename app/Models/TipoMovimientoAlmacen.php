<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMovimientoAlmacen extends Model
{
    use HasFactory;

    protected $table = 'tipo_movimiento_almacen';
    protected $primaryKey = 'tipo_movimiento_almacenid';

    protected $fillable = [
        'nombre',
        'naturaleza',
        'activo',
    ];

    protected $casts = [
        'tipo_movimiento_almacenid' => 'integer',
        'activo' => 'boolean',
    ];
}
