<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroMovimientoMateria extends Model
{
    public $timestamps = false;

    protected $table = 'registro_movimiento_materia';
    protected $primaryKey = 'registromovimientomateriaid';

    protected $fillable = [
        'materiaprimabaseid', 'tipomovimientomateriaid', 'usuarioid', 'cantidad',
        'saldo_anterior', 'saldo_nuevo', 'descripcion', 'observaciones', 'fecha_movimiento',
    ];

    protected $casts = [
        'cantidad'        => 'float',
        'saldo_anterior'  => 'float',
        'saldo_nuevo'     => 'float',
        'fecha_movimiento' => 'datetime',
    ];

    public function materiaBase(): BelongsTo
    {
        return $this->belongsTo(MateriaPrimaBase::class, 'materiaprimabaseid', 'materiaprimabaseid');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoMovimientoMateria::class, 'tipomovimientomateriaid', 'tipomovimientomateriaid');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }
}
