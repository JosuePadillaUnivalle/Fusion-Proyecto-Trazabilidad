<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MateriaPrimaBase extends Model
{
    protected $table = 'materia_prima_base';
    protected $primaryKey = 'materiaprimabaseid';

    protected $fillable = [
        'categoriamateriaprimaid', 'unidadmedidaid', 'codigo', 'nombre', 'descripcion',
        'cantidad_disponible', 'stock_minimo', 'stock_maximo', 'activo',
    ];

    protected $casts = [
        'cantidad_disponible' => 'float',
        'stock_minimo'        => 'float',
        'stock_maximo'        => 'float',
        'activo'              => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaMateriaPrima::class, 'categoriamateriaprimaid', 'categoriamateriaprimaid');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(MateriaPrimaLote::class, 'materiaprimabaseid', 'materiaprimabaseid');
    }

    public function detallesSolicitudMaterial(): HasMany
    {
        return $this->hasMany(DetalleSolicitudMaterial::class, 'materiaprimabaseid', 'materiaprimabaseid');
    }
}
