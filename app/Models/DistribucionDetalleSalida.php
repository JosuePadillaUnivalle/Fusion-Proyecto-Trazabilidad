<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribucionDetalleSalida extends Model
{
    protected $table = 'distribucion_detalle_salida';

    protected $primaryKey = 'distribuciondetallesalidaid';

    protected $fillable = [
        'distribucionsalidaid', 'productodistribucionid', 'cant_salida', 'precio',
    ];

    protected $casts = [
        'cant_salida' => 'float',
        'precio' => 'float',
    ];

    public function salida(): BelongsTo
    {
        return $this->belongsTo(DistribucionSalida::class, 'distribucionsalidaid', 'distribucionsalidaid');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoDistribucion::class, 'productodistribucionid', 'productodistribucionid');
    }
}
