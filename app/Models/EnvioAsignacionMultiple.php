<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioAsignacionMultiple extends Model
{
    protected $table = 'envio_asignacion_multiple';
    protected $primaryKey = 'envioasignacionmultipleid';

    protected $fillable = [
        'externo_envio_id',
        'pedidoid',
        'transportista_usuarioid',
        'asignadopor_usuarioid',
        'rutamultientregaid',
        'vehiculo_ref',
        'estado',
        'fecha_asignacion',
        'almacenid',
    ];

    protected $casts = [
        'envioasignacionmultipleid' => 'integer',
        'pedidoid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'asignadopor_usuarioid' => 'integer',
        'rutamultientregaid' => 'integer',
        'almacenid' => 'integer',
        'fecha_asignacion' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function transportista(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'transportista_usuarioid', 'usuarioid');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'asignadopor_usuarioid', 'usuarioid');
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(RutaMultiEntrega::class, 'rutamultientregaid', 'rutamultientregaid');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }
}

