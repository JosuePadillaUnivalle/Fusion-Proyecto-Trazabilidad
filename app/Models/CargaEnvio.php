<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CargaEnvio extends Model
{
    protected $table = 'carga_envio';
    protected $primaryKey = 'cargaenvioid';

    protected $fillable = [
        'catalogocargaid',
        'tipoempaqueid',
        'cantidad',
        'peso',
        'unidadmedidaid',
    ];

    protected $casts = [
        'cantidad'       => 'integer',
        'peso'           => 'float',
        'tipoempaqueid'  => 'integer',
        'unidadmedidaid' => 'integer',
    ];

    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(CatalogoCarga::class, 'catalogocargaid', 'catalogocargaid');
    }

    public function tipoEmpaque(): BelongsTo
    {
        return $this->belongsTo(TipoEmpaque::class, 'tipoempaqueid', 'tipoempaqueid');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function enviosAsignacionMultiple(): BelongsToMany
    {
        return $this->belongsToMany(
            EnvioAsignacionMultiple::class,
            'asignacion_carga',
            'cargaenvioid',
            'envioasignacionmultipleid'
        )->using(AsignacionCarga::class);
    }
}
