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
        'costo_bs',
        'simulacion_inicio_at',
        'simulacion_duracion_seg',
        'simulacion_geojson',
        'estado',
        'fecha_asignacion',
        'fecha_recepcion_planta',
        'recepcion_usuarioid',
        'almacenid',
        'tipotransporteid',
        'recogidaentregaid',
    ];

    protected $casts = [
        'envioasignacionmultipleid' => 'integer',
        'pedidoid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'asignadopor_usuarioid' => 'integer',
        'rutamultientregaid' => 'integer',
        'almacenid' => 'integer',
        'tipotransporteid' => 'integer',
        'recogidaentregaid' => 'integer',
        'costo_bs' => 'decimal:2',
        'simulacion_inicio_at' => 'datetime',
        'simulacion_geojson' => 'array',
        'fecha_asignacion' => 'datetime',
        'fecha_recepcion_planta' => 'datetime',
        'recepcion_usuarioid' => 'integer',
        'detalles_productos' => 'array',
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

    public function recepcionConfirmadaPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'recepcion_usuarioid', 'usuarioid');
    }

    public function tipoTransporte(): BelongsTo
    {
        return $this->belongsTo(TipoTransporte::class, 'tipotransporteid', 'tipotransporteid');
    }

    public function recogidaEntrega(): BelongsTo
    {
        return $this->belongsTo(RecogidaEntrega::class, 'recogidaentregaid', 'recogidaentregaid');
    }
}

