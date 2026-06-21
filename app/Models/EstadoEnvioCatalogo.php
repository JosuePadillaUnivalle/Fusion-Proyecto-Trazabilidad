<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoEnvioCatalogo extends Model
{
    protected $table = 'estado_envio_catalogo';
    protected $primaryKey = 'estadoenviocatalogoid';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'orden',
    ];

    protected $casts = [
        'estadoenviocatalogoid' => 'integer',
        'orden'                 => 'integer',
    ];

    public function historialesEstado(): HasMany
    {
        return $this->hasMany(HistorialEstadoEnvio::class, 'estadoenviocatalogoid', 'estadoenviocatalogoid');
    }
}
