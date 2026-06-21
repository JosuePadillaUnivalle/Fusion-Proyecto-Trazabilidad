<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsumoPresentacion extends Model
{
    protected $table = 'insumo_presentacion';

    protected $primaryKey = 'insumo_presentacionid';

    public $timestamps = false;

    protected $fillable = [
        'insumoid',
        'tipoempaqueid',
        'nombre',
        'tipo_envase',
        'peso_neto_kg',
        'unidades_por_caja',
        'sku',
        'codigo_barras',
        'orden',
        'activo',
    ];

    protected $casts = [
        'insumo_presentacionid' => 'integer',
        'insumoid' => 'integer',
        'tipoempaqueid' => 'integer',
        'peso_neto_kg' => 'float',
        'unidades_por_caja' => 'integer',
        'orden' => 'integer',
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

    public function inventariosLote(): HasMany
    {
        return $this->hasMany(InventarioPresentacionLote::class, 'insumo_presentacionid', 'insumo_presentacionid');
    }

    public function etiquetaUnidad(): string
    {
        if ($this->relationLoaded('tipoEmpaque') || $this->tipoempaqueid) {
            $this->loadMissing('tipoEmpaque');
            if ($this->tipoEmpaque?->nombre) {
                return TipoEmpaque::etiquetaUnidadPlural($this->tipoEmpaque->nombre);
            }
        }

        return match ($this->tipo_envase) {
            'lata' => 'latas',
            'frasco' => 'frascos',
            'bidon' => 'bidones',
            'caja' => 'cajas',
            default => 'unidades',
        };
    }

    public function pesoNetoKg(): float
    {
        return max(0.0001, (float) $this->peso_neto_kg);
    }
}
