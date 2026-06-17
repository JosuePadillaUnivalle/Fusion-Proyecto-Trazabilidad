<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificacionLote extends Model
{
    public const RAZON_CERTIFICADO = 'Certificado';

    public const RAZON_NO_CONFORME = 'No conforme';

    /** @var list<string> */
    public const RAZONES = [
        self::RAZON_CERTIFICADO,
        self::RAZON_NO_CONFORME,
    ];

    protected $table = 'certificacion_lote';
    protected $primaryKey = 'certificacionid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'usuarioid',
        'codigo_certificado',
        'resultado',
        'observaciones',
        'fecha_certificacion',
        'recomendaciones',
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

    public function esCertificado(): bool
    {
        return $this->resultado === self::RAZON_CERTIFICADO;
    }

    public function esNoConforme(): bool
    {
        return $this->resultado === self::RAZON_NO_CONFORME;
    }
}
