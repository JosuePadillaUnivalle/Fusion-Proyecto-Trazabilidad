<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoQrtoken extends Model
{
    public $timestamps = false;

    protected $table = 'estado_qrtoken';
    protected $primaryKey = 'estadoqrtokenid';

    protected $fillable = ['nombre'];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(QrTokenAsignacion::class, 'estadoqrtokenid', 'estadoqrtokenid');
    }
}
