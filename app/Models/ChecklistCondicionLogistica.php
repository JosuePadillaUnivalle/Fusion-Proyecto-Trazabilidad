<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistCondicionLogistica extends Model
{
    public $timestamps = false;

    protected $table = 'checklist_condicion_logistica';
    protected $primaryKey = 'checklistcondicionid';

    protected $fillable = [
        'envioasignacionmultipleid', 'almacenid', 'revisado_por_usuarioid', 'estado_general',
        'productos_completos', 'empaque_intacto', 'temperatura_adecuada', 'sin_danos_visibles',
        'documentacion_completa', 'observaciones', 'fecha_revision', 'created_at',
    ];

    protected $casts = [
        'productos_completos'       => 'boolean',
        'empaque_intacto'           => 'boolean',
        'temperatura_adecuada'      => 'boolean',
        'sin_danos_visibles'        => 'boolean',
        'documentacion_completa'    => 'boolean',
        'fecha_revision'            => 'datetime',
        'created_at'                => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ChecklistCondicionLogisticaDetalle::class, 'checklistcondicionid', 'checklistcondicionid');
    }
}
