<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoDistribucion extends Model
{
    protected $table = 'producto_distribucion';
    protected $primaryKey = 'productodistribucionid';

    protected $fillable = [
        'nombre', 'codigo', 'categoriaproductoid', 'descripcion', 'unidadmedidaid', 'precio_unitario', 'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'float',
        'activo'          => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoriaproductoid', 'categoriaproductoid');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function stocksAlmacen(): HasMany
    {
        return $this->hasMany(AlmacenProducto::class, 'productodistribucionid', 'productodistribucionid');
    }
}
