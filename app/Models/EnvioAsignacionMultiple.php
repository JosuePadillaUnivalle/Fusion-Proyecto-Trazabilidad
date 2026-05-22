<?php

namespace App\Models;

use App\Support\EnvioAsignacionEstadoCatalogo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'estadoasignacioncatalogoid',
        'motivocancelacionid',
        'tipotransporteid',
        'recogidaentregaid',
        'fecha_asignacion',
        'almacenid',
        'detalles_productos',
    ];

    protected $casts = [
        'envioasignacionmultipleid' => 'integer',
        'pedidoid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'asignadopor_usuarioid' => 'integer',
        'rutamultientregaid' => 'integer',
        'estadoasignacioncatalogoid' => 'integer',
        'motivocancelacionid' => 'integer',
        'tipotransporteid' => 'integer',
        'recogidaentregaid' => 'integer',
        'almacenid' => 'integer',
        'fecha_asignacion' => 'datetime',
        'detalles_productos' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if ($model->isDirty('estado') && $model->estado !== null) {
                $attrs = EnvioAsignacionEstadoCatalogo::applyToAttributes(['estado' => $model->estado]);
                if (isset($attrs['estadoasignacioncatalogoid'])) {
                    $model->estadoasignacioncatalogoid = $attrs['estadoasignacioncatalogoid'];
                }
                if (array_key_exists('motivocancelacionid', $attrs)) {
                    $model->motivocancelacionid = $attrs['motivocancelacionid'];
                }
            }
        });
    }

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

    public function estadoCatalogo(): BelongsTo
    {
        return $this->belongsTo(EstadoAsignacionMultipleCatalogo::class, 'estadoasignacioncatalogoid', 'estadoasignacioncatalogoid');
    }

    public function motivoCancelacion(): BelongsTo
    {
        return $this->belongsTo(MotivoCancelacionEnvio::class, 'motivocancelacionid', 'motivocancelacionid');
    }

    public function tipoTransporte(): BelongsTo
    {
        return $this->belongsTo(TipoTransporte::class, 'tipotransporteid', 'tipotransporteid');
    }

    public function recogidaEntrega(): BelongsTo
    {
        return $this->belongsTo(RecogidaEntrega::class, 'recogidaentregaid', 'recogidaentregaid');
    }

    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstadoEnvio::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function seguimientosGps(): HasMany
    {
        return $this->hasMany(SeguimientoEnvioGps::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function checklistsCondicionLogistica(): HasMany
    {
        return $this->hasMany(ChecklistCondicionLogistica::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function qrTokenAsignacion(): HasOne
    {
        return $this->hasOne(QrTokenAsignacion::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function checklistIncidenteEnvio(): HasOne
    {
        return $this->hasOne(ChecklistIncidenteEnvio::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function firmaRecepcionEnvio(): HasOne
    {
        return $this->hasOne(FirmaRecepcionEnvio::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function firmaTransportistaEnvio(): HasOne
    {
        return $this->hasOne(FirmaTransportistaEnvio::class, 'envioasignacionmultipleid', 'envioasignacionmultipleid');
    }

    public function cargasEnvio(): BelongsToMany
    {
        return $this->belongsToMany(
            CargaEnvio::class,
            'asignacion_carga',
            'envioasignacionmultipleid',
            'cargaenvioid'
        )->using(AsignacionCarga::class);
    }
}

