<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroProcesoMaquinaPlanta extends Model
{
    protected $table = 'registro_proceso_maquina_planta';
    protected $primaryKey = 'registroprocesomaquinaplantaid';

    protected $fillable = [
        'procesomaquinaplantaid', 'loteid', 'usuarioid', 'variables_ingresadas', 'cumple_estandar',
        'observaciones', 'hora_inicio', 'hora_fin', 'fecha_registro',
    ];

    protected $casts = [
        'cumple_estandar' => 'boolean',
        'hora_inicio'     => 'datetime',
        'hora_fin'        => 'datetime',
        'fecha_registro'  => 'datetime',
    ];

    public function procesoMaquina(): BelongsTo
    {
        return $this->belongsTo(ProcesoMaquinaPlanta::class, 'procesomaquinaplantaid', 'procesomaquinaplantaid');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'loteid', 'loteid');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }
}
