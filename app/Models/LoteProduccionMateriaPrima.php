<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteProduccionMateriaPrima extends Model
{
    protected $table = 'lote_produccion_materia_prima';
    protected $primaryKey = 'loteproduccionmateriaid';

    protected $fillable = [
        'loteproduccionpedidoid',
        'materiaprimaloteid',
        'cantidad_planificada',
        'cantidad_usada',
    ];

    protected $casts = [
        'cantidad_planificada' => 'float',
        'cantidad_usada'       => 'float',
    ];

    public function loteProduccionPedido(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function materiaPrimaLote(): BelongsTo
    {
        return $this->belongsTo(MateriaPrimaLote::class, 'materiaprimaloteid', 'materiaprimaloteid');
    }
}
