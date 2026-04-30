<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificacionLote extends Model
{
    protected $table = 'certificacion_lote';
    protected $primaryKey = 'certificacionid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'usuarioid',
        'codigo_certificado',
        'observaciones',
        'fecha_certificacion',
    ];

    protected $casts = [
        'certificacionid' => 'integer',
        'loteid' => 'integer',
        'usuarioid' => 'integer',
        'fecha_certificacion' => 'datetime',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'loteid', 'loteid');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }
}

