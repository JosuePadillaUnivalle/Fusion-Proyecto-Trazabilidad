<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogoTamanoConteo extends Model
{
    protected $table = 'catalogo_tamano_conteo';

    protected $primaryKey = 'catalogotamanoconteoid';

    protected $fillable = [
        'insumoid',
        'nombre',
        'conteo_por_empaque',
        'peso_promedio_kg',
        'tipoempaqueid',
        'activo',
    ];

    protected $casts = [
        'conteo_por_empaque' => 'integer',
        'peso_promedio_kg' => 'decimal:4',
        'activo' => 'boolean',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumoid', 'insumoid');
    }

    public function tipoEmpaque(): BelongsTo
    {
        return $this->belongsTo(TipoEmpaque::class, 'tipoempaqueid', 'tipoempaqueid');
    }
}
