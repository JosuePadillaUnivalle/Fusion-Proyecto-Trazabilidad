<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistIncidenteEnvio extends Model
{
    protected $table = 'checklist_incidente_envio';
    protected $primaryKey = 'checklistincidenteenvioid';

    protected $fillable = [
        'envioasignacionmultipleid',
        'fecha',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(EnvioAsignacionMultiple::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ChecklistIncidenteEnvioDetalle::class, 'checklistincidenteenvioid', 'checklistincidenteenvioid');
    }
}
