<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificacionLote extends Model
{
    public const RAZON_CERTIFICADO = 'Certificado';

    public const RAZON_NO_CONFORME = 'No conforme';

    public const BLOCKCHAIN_PENDIENTE = 'pendiente';

    public const BLOCKCHAIN_CERTIFICADA = 'certificada';

    public const BLOCKCHAIN_RECHAZADA = 'rechazada';

    public const BLOCKCHAIN_ERROR = 'error';

    public const BLOCKCHAIN_OMITIDA = 'omitida';

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
        'blockchain_estado',
        'blockchain_dato_id',
        'blockchain_solicitud_id',
        'blockchain_operacion_id',
        'blockchain_hash',
        'blockchain_payload_version',
        'blockchain_txid',
        'blockchain_error',
        'blockchain_enviado_en',
        'blockchain_certificado_en',
        'blockchain_intentos',
    ];

    protected $casts = [
        'certificacionid' => 'integer',
        'loteid' => 'integer',
        'usuarioid' => 'integer',
        'fecha_certificacion' => 'datetime',
        'blockchain_enviado_en' => 'datetime',
        'blockchain_certificado_en' => 'datetime',
        'blockchain_intentos' => 'integer',
        'blockchain_payload_version' => 'integer',
    ];

    public function blockchainConfirmada(): bool
    {
        return in_array($this->blockchain_estado, [self::BLOCKCHAIN_CERTIFICADA, 'confirmado'], true)
            && filled($this->blockchain_txid);
    }

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
