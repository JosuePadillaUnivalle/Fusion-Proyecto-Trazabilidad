<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenteEnvio extends Model
{
    protected $table = 'incidente_envio';
    protected $primaryKey = 'incidenteenvioid';

    protected $fillable = [
        'externo_envio_id',
        'pedidoid',
        'reportadopor_usuarioid',
        'tipo',
        'descripcion',
        'estado',
        'resueltopor_usuarioid',
        'fecha_resolucion',
        'nota_resolucion',
        'almacenid',
    ];

    protected $casts = [
        'incidenteenvioid' => 'integer',
        'pedidoid' => 'integer',
        'reportadopor_usuarioid' => 'integer',
        'resueltopor_usuarioid' => 'integer',
        'almacenid' => 'integer',
        'fecha_resolucion' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reportadopor_usuarioid', 'usuarioid');
    }

    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'resueltopor_usuarioid', 'usuarioid');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }
}

