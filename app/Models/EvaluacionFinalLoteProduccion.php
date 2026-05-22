<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluacionFinalLoteProduccion extends Model
{
    protected $table = 'evaluacion_final_lote_produccion';
    protected $primaryKey = 'evaluacionfinalloteid';

    protected $fillable = [
        'loteproduccionpedidoid',
        'inspector_usuarioid',
        'razon',
        'observaciones',
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
}
