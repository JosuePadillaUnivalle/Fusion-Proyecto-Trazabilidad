<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaquinaPlanta extends Model
{
    protected $table = 'maquina_planta';
    protected $primaryKey = 'maquinaplantaid';

    protected $fillable = ['nombre', 'codigo', 'descripcion', 'imagenurl', 'activo'];

    protected $casts = [
        'maquinaplantaid' => 'integer',
        'activo' => 'boolean',
    ];

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function procesoMaquinas(): HasMany
    {
        return $this->hasMany(ProcesoMaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function variablesSugeridas(): HasMany
    {
        return $this->hasMany(MaquinaVariablePlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function tieneImagen(): bool
    {
        return filled($this->imagenurl);
    }

    /** @return array<string, string> código => descripción breve */
    public static function descripcionesPorCodigo(): array
    {
        return [
            'L-100' => 'Lava y enjuaga el producto antes del procesado en planta.',
            'BC-20' => 'Transporta y clasifica el producto por tamaño en la línea.',
            'SE-10' => 'Sella envases y bolsas para empaque listo al despacho.',
            'BD-500' => 'Pesa lotes y verifica el peso en control de calidad.',
        ];
    }

    public function descripcionMostrar(): string
    {
        $desc = trim((string) ($this->descripcion ?? ''));
        if ($desc !== '' && ! preg_match('/Equipo de planta.*MOD-PROD/i', $desc)) {
            return $desc;
        }

        return self::descripcionesPorCodigo()[$this->codigo ?? '']
            ?? ($desc !== '' ? preg_replace('/\s*·\s*\[MOD-PROD\]\s*/', '', $desc) : 'Equipo de la línea de producción.');
    }

    public function enMantenimiento(): bool
    {
        return ! $this->activo;
    }

    public function etiquetaEstado(): string
    {
        return $this->activo ? 'Activa' : 'En mantenimiento';
    }

    /** URL pública para mostrar la foto en vistas (o null si no hay). */
    public function imagenSrc(): ?string
    {
        $img = trim((string) ($this->imagenurl ?? ''));
        if ($img === '') {
            return null;
        }

        if (preg_match('#^(https?://|data:)#i', $img)) {
            return $img;
        }

        $rel = $img;
        if (str_starts_with($rel, '/storage/')) {
            $rel = substr($rel, 9);
        } elseif (str_starts_with($rel, 'storage/')) {
            $rel = substr($rel, 8);
        }

        $fullPath = storage_path('app/public/'.ltrim($rel, '/'));
        if (! is_file($fullPath)) {
            return null;
        }

        return asset('storage/'.ltrim($rel, '/'));
    }
}

