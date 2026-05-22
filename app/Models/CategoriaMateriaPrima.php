<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaMateriaPrima extends Model
{
    protected $table = 'categoria_materia_prima';
    protected $primaryKey = 'categoriamateriaprimaid';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function materiasBase(): HasMany
    {
        return $this->hasMany(MateriaPrimaBase::class, 'categoriamateriaprimaid', 'categoriamateriaprimaid');
    }
}
