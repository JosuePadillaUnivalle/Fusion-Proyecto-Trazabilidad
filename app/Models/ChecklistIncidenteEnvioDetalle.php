<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistIncidenteEnvioDetalle extends Model
{
    protected $table = 'checklist_incidente_envio_detalle';
    protected $primaryKey = 'checklistincidentedetalleid';

    protected $fillable = [
        'checklistincidenteenvioid',
        'tipoincidentetransporteid',
        'ocurrio',
        'descripcion',
    ];

    protected $casts = [
        'ocurrio' => 'boolean',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(ChecklistIncidenteEnvio::class, 'checklistincidenteenvioid', 'checklistincidenteenvioid');
    }

    public function tipoIncidente(): BelongsTo
    {
        return $this->belongsTo(TipoIncidenteTransporte::class, 'tipoincidentetransporteid', 'tipoincidentetransporteid');
    }
}
