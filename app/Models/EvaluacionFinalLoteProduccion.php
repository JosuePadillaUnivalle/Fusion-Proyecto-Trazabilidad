<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionFinalLoteProduccion extends Model
{
    public const RAZON_CERTIFICADO = 'Certificado';

    public const RAZON_NO_CONFORME = 'No conforme';

    /** @var list<string> */
    public const RAZONES = [
        self::RAZON_CERTIFICADO,
        self::RAZON_NO_CONFORME,
    ];

    protected $table = 'evaluacion_final_lote_produccion';
    protected $primaryKey = 'evaluacionfinalloteid';

    protected $fillable = [
        'loteproduccionpedidoid',
        'inspector_usuarioid',
        'razon',
        'observaciones',
        'recomendaciones',
        'fecha_evaluacion',
    ];

    protected $casts = [
        'fecha_evaluacion' => 'datetime',
    ];

    public function loteProduccionPedido(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'inspector_usuarioid', 'usuarioid');
    }

    public function esCertificado(): bool
    {
        return $this->razon === self::RAZON_CERTIFICADO;
    }

    public function esNoConforme(): bool
    {
        return $this->razon === self::RAZON_NO_CONFORME;
    }
}
