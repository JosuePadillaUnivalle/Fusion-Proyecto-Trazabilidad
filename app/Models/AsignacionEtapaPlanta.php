<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionEtapaPlanta extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PROGRAMADA = 'programada';
    public const ESTADO_COMPLETADA = 'completada';
    public const ESTADO_CANCELADA = 'cancelada';

    protected $table = 'asignacion_etapa_planta';
    protected $primaryKey = 'asignacionetapaplantaid';
    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'asignacionetapaplantaid';
    }

    protected $fillable = [
        'loteproduccionpedidoid',
        'loteproduccionrutapasoid',
        'orden',
        'procesoplantaid',
        'maquinaplantaid',
        'operador_usuarioid',
        'asignado_por_usuarioid',
        'estado',
        'observaciones',
        'registroprocesomaquinaplantaid',
        'creado_en',
        'completada_en',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'completada_en' => 'datetime',
    ];

    public function loteProduccion(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionPedido::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function rutaPaso(): BelongsTo
    {
        return $this->belongsTo(LoteProduccionRutaPaso::class, 'loteproduccionrutapasoid', 'loteproduccionrutapasoid');
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(ProcesoPlanta::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(MaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'operador_usuarioid', 'usuarioid');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignado_por_usuarioid', 'usuarioid');
    }

    public function registro(): BelongsTo
    {
        return $this->belongsTo(RegistroProcesoMaquinaPlanta::class, 'registroprocesomaquinaplantaid', 'registroprocesomaquinaplantaid');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function estaProgramada(): bool
    {
        return $this->estado === self::ESTADO_PROGRAMADA;
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeProgramadas($query)
    {
        return $query->where('estado', self::ESTADO_PROGRAMADA);
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_PROGRAMADA]);
    }
}
