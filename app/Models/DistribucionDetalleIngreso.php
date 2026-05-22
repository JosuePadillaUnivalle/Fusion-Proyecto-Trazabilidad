<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribucionDetalleIngreso extends Model
{
    protected $table = 'distribucion_detalle_ingreso';

    protected $primaryKey = 'distribuciondetalleingresoid';

    protected $fillable = [
        'distribucioningresoid', 'productodistribucionid', 'cant_ingreso', 'precio',
    ];

    protected $casts = [
        'cant_ingreso' => 'float',
        'precio' => 'float',
    ];

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(DistribucionIngreso::class, 'distribucioningresoid', 'distribucioningresoid');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoDistribucion::class, 'productodistribucionid', 'productodistribucionid');
    }
}
