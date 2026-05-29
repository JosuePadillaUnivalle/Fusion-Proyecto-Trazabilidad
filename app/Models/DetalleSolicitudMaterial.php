<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleSolicitudMaterial extends Model
{
    protected $table = 'detalle_solicitud_material';
    protected $primaryKey = 'detallesolicitudmaterialid';

    protected $fillable = [
        'solicitudmaterialid',
        'materiaprimabaseid',
        'cantidad_solicitada',
        'cantidad_aprobada',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'float',
        'cantidad_aprobada'   => 'float',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudMaterialPedido::class, 'solicitudmaterialid', 'solicitudmaterialid');
    }

    public function materiaPrimaBase(): BelongsTo
    {
        return $this->belongsTo(MateriaPrimaBase::class, 'materiaprimabaseid', 'materiaprimabaseid');
    }
}
