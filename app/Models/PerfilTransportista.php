<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerfilTransportista extends Model
{
    protected $table = 'perfil_transportista';
    protected $primaryKey = 'perfiltransportistaid';

    protected $fillable = [
        'usuarioid',
        'ambito_flota',
        'estadotransportistaid',
        'vehiculoid',
        'licencia',
        'tipo_licencia',
        'licencias_json',
        'fecha_vencimiento_licencia',
        'disponible',
    ];

    protected $casts = [
        'perfiltransportistaid'      => 'integer',
        'usuarioid'                  => 'integer',
        'estadotransportistaid'      => 'integer',
        'vehiculoid'                 => 'integer',
        'fecha_vencimiento_licencia' => 'date',
        'licencias_json' => 'array',
        'disponible'                 => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function calificacionesEnvio(): HasMany
    {
        return $this->hasMany(CalificacionEnvio::class, 'perfiltransportistaid', 'perfiltransportistaid');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculoid', 'vehiculoid');
    }

    public function estadoTransportista(): BelongsTo
    {
        return $this->belongsTo(EstadoTransportista::class, 'estadotransportistaid', 'estadotransportistaid');
    }
}
