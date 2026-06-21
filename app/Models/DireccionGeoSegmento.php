<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionGeoSegmento extends Model
{
    protected $table = 'direccion_geo_segmento';
    protected $primaryKey = 'direcciongeosegmentoid';

    protected $fillable = [
        'direcciongeoenvioid',
        'segmentogeojson',
    ];

    public function direccionGeoEnvio(): BelongsTo
    {
        return $this->belongsTo(DireccionGeoEnvio::class, 'direcciongeoenvioid', 'direcciongeoenvioid');
    }
}
