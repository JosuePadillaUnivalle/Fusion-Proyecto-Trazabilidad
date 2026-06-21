<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleTrasladoPlantaMayorista extends Model
{
    protected $table = 'detalle_traslado_planta_mayorista';

    protected $primaryKey = 'detalletrasladoid';

    public $timestamps = false;

    protected $fillable = [
        'rutadistribucionid',
        'insumoid',
        'insumo_presentacionid',
        'inventario_presentacion_loteid',
        'loteproduccionpedidoid',
        'presentacion_nombre',
        'producto_nombre',
        'cantidad',
        'cantidad_unidades',
        'observaciones',
    ];

    protected $casts = [
        'detalletrasladoid' => 'integer',
        'rutadistribucionid' => 'integer',
        'insumoid' => 'integer',
        'insumo_presentacionid' => 'integer',
        'inventario_presentacion_loteid' => 'integer',
        'loteproduccionpedidoid' => 'integer',
        'cantidad' => 'float',
        'cantidad_unidades' => 'float',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(RutaDistribucion::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumoid', 'insumoid');
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(InsumoPresentacion::class, 'insumo_presentacionid', 'insumo_presentacionid');
    }

    public function inventarioLote(): BelongsTo
    {
        return $this->belongsTo(InventarioPresentacionLote::class, 'inventario_presentacion_loteid', 'inventario_presentacion_loteid');
    }

    public function loteProduccion(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }
}
