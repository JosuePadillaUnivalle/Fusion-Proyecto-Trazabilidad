<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistCondicionLogisticaDetalle extends Model
{
    protected $table = 'checklist_condicion_logistica_detalle';
    protected $primaryKey = 'checklistcondiciondetalleid';

    protected $fillable = [
        'checklistcondicionid',
        'condiciontransporteid',
        'valor',
        'comentario',
    ];

    protected $casts = [
        'valor' => 'boolean',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(ChecklistCondicionLogistica::class, 'checklistcondicionid', 'checklistcondicionid');
    }

    public function condicion(): BelongsTo
    {
        return $this->belongsTo(CondicionTransporte::class, 'condiciontransporteid', 'condiciontransporteid');
    }
}
