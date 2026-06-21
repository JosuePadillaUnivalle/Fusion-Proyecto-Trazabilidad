<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DireccionGeoEnvio extends Model
{
    protected $table = 'direccion_geo_envio';
    protected $primaryKey = 'direcciongeoenvioid';

    protected $fillable = [
        'usuarioid',
        'nombreorigen',
        'origen_lng',
        'origen_lat',
        'nombredestino',
        'destino_lng',
        'destino_lat',
        'rutageojson',
    ];

    protected $casts = [
        'origen_lng'   => 'float',
        'origen_lat'   => 'float',
        'destino_lng'  => 'float',
        'destino_lat'  => 'float',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function segmentos(): HasMany
    {
        return $this->hasMany(DireccionGeoSegmento::class, 'direcciongeoenvioid', 'direcciongeoenvioid');
    }
}
