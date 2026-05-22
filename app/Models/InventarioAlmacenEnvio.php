<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inventario de producto de distribución vinculado a envío (tabla inventario_almacen_envio, Bloque B).
 */
class InventarioAlmacenEnvio extends Model
{
    protected $table = 'inventario_almacen_envio';

    protected $primaryKey = 'inventarioalmacenenvioid';

    protected $fillable = [
        'almacenid',
        'productodistribucionid',
        'envioasignacionmultipleid',
        'externo_envio_id',
        'cantidad',
        'peso_total',
        'fecha_ingreso',
        'estado',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'peso_total' => 'float',
        'fecha_ingreso' => 'datetime',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoDistribucion::class, 'productodistribucionid', 'productodistribucionid');
    }

    public function envioAsignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }
}
