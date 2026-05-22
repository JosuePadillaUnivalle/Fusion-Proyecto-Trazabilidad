<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoTransportista extends Model
{
    public $timestamps = false;

    protected $table = 'estado_transportista';
    protected $primaryKey = 'estadotransportistaid';

    protected $fillable = ['nombre'];

    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilTransportista::class, 'estadotransportistaid', 'estadotransportistaid');
    }
}
