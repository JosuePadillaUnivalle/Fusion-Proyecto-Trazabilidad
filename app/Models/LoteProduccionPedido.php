<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteProduccionPedido extends Model
{
    protected $table = 'lote_produccion_pedido';
    protected $primaryKey = 'loteproduccionpedidoid';

    protected $fillable = [
        'pedidoid',
        'procesoplantaid',
        'plantillatransformacionid',
        'codigo_lote',
        'nombre',
        'producto',
        'fecha_creacion',
        'hora_inicio',
        'hora_fin',
        'cantidad_objetivo',
        'unidadmedidaid',
        'cantidad_producida',
        'empaque_catalogo_slug',
        'empaque_nombre_personalizado',
        'empaque_peso_neto_kg',
        'empaque_tipo_envase',
        'modo_planificacion',
        'cantidad_empaques_objetivo',
        'insumo_presentacionid',
        'observaciones',
    ];

    protected $casts = [
        'fecha_creacion'      => 'date',
        'hora_inicio'         => 'datetime',
        'hora_fin'            => 'datetime',
        'cantidad_objetivo'   => 'float',
        'cantidad_producida'  => 'float',
        'empaque_peso_neto_kg' => 'float',
        'cantidad_empaques_objetivo' => 'float',
        'unidadmedidaid'      => 'integer',
        'insumo_presentacionid' => 'integer',
    ];

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function procesoPlanta(): BelongsTo
    {
        return $this->belongsTo(ProcesoPlanta::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function plantillaTransformacion(): BelongsTo
    {
        return $this->belongsTo(PlantillaTransformacion::class, 'plantillatransformacionid', 'plantillatransformacionid');
    }

    public function registrosProceso(): HasMany
    {
        return $this->hasMany(RegistroProcesoMaquinaPlanta::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function parametrosPaso(): HasMany
    {
        return $this->hasMany(LoteProduccionPasoVariable::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function rutaPasos(): HasMany
    {
        return $this->hasMany(LoteProduccionRutaPaso::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid')
            ->orderBy('orden');
    }

    public function materiasPrimas(): HasMany
    {
        return $this->hasMany(LoteProduccionMateriaPrima::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function evaluacionesFinales(): HasMany
    {
        return $this->hasMany(EvaluacionFinalLoteProduccion::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function almacenajes(): HasMany
    {
        return $this->hasMany(AlmacenajeLoteProduccion::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function asignacionesEtapa(): HasMany
    {
        return $this->hasMany(AsignacionEtapaPlanta::class, 'loteproduccionpedidoid', 'loteproduccionpedidoid');
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(InsumoPresentacion::class, 'insumo_presentacionid', 'insumo_presentacionid');
    }
}
