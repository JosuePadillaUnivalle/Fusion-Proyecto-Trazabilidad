<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioPresentacionLote extends Model
{
    protected $table = 'inventario_presentacion_lote';

    protected $primaryKey = 'inventario_presentacion_loteid';

    protected $fillable = [
        'almacenid',
        'insumoid',
        'insumo_presentacionid',
        'loteproduccionpedidoid',
        'referencia_lote',
        'cantidad_unidades',
        'cantidad_kg',
    ];

    protected $casts = [
        'inventario_presentacion_loteid' => 'integer',
        'almacenid' => 'integer',
        'insumoid' => 'integer',
        'insumo_presentacionid' => 'integer',
        'loteproduccionpedidoid' => 'integer',
        'cantidad_unidades' => 'float',
        'cantidad_kg' => 'float',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumoid', 'insumoid');
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(InsumoPresentacion::class, 'insumo_presentacionid', 'insumo_presentacionid');
    }

    public function loteProduccion(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function etiquetaLote(): string
    {
        if ($this->loteProduccion?->codigo_lote) {
            return $this->loteProduccion->codigo_lote;
        }

        return $this->referencia_lote ?: 'Sin lote';
    }

    public function tieneStockSuficiente(float $unidades): bool
    {
        return (float) $this->cantidad_unidades >= $unidades - 0.0001;
    }
}
