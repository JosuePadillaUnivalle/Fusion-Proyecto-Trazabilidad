<?php

namespace App\Models;

use App\Support\PedidoCatalogo;
use App\Support\UbicacionGpsParser;
use App\Support\SuperficieFormato;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lote';
    protected $primaryKey = 'loteid';
    public $timestamps = false;

    protected $fillable = [
        'usuarioid',
        'nombre',
        'ubicacion',
        'superficie',
        'unidadsuperficieid',
        'cultivoid',
        'insumosemillaid',
        'cantidad_semilla_planificada',
        'catalogotamanoconteoid',
        'actorid',
        'codigo_trazabilidad',
        'fechasiembra',
        'estadolotetipoid',
        'latitud',
        'longitud',
        'fechacreacion',
        'fechamodificacion',
        'imagenurl',
    ];

    protected $casts = [
        'loteid'              => 'integer',
        'usuarioid'           => 'integer',
        'superficie'          => 'float',
        'unidadsuperficieid'  => 'integer',
        'cultivoid'           => 'integer',
        'insumosemillaid'     => 'integer',
        'cantidad_semilla_planificada' => 'float',
        'catalogotamanoconteoid' => 'integer',
        'actorid'             => 'integer',
        'estadolotetipoid'    => 'integer',
        'latitud'             => 'float',
        'longitud'            => 'float',
        'fechasiembra'        => 'date',
        'fechacreacion'       => 'datetime',
        'fechamodificacion'   => 'datetime',
    ];

    protected $hidden = [
        'usuario',
        'cultivo',
        'estadoTipo',
        'unidadSuperficie',
        'estados',
        'producciones',
        'loteInsumos',
        'actividades',
        'clima',
        'historialEstados',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivoid', 'cultivoid');
    }

    public function insumoSemilla()
    {
        return $this->belongsTo(Insumo::class, 'insumosemillaid', 'insumoid');
    }

    public function catalogoTamanoConteo()
    {
        return $this->belongsTo(CatalogoTamanoConteo::class, 'catalogotamanoconteoid', 'catalogotamanoconteoid');
    }

    public function actorAbastecimiento()
    {
        return $this->belongsTo(ActorAbastecimiento::class, 'actorid', 'actorid');
    }

    public function estadoTipo()
    {
        return $this->belongsTo(EstadoLoteTipo::class, 'estadolotetipoid', 'estadolotetipoid');
    }

    public function unidadSuperficie()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadsuperficieid', 'unidadmedidaid');
    }

    public function estados()
    {
        return $this->hasMany(EstadoLote::class, 'loteid', 'loteid');
    }

    public function producciones()
    {
        return $this->hasMany(Produccion::class, 'loteid', 'loteid');
    }

    public function loteInsumos()
    {
        return $this->hasMany(LoteInsumo::class, 'loteid', 'loteid');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'loteid', 'loteid');
    }

    public function clima()
    {
        return $this->hasMany(Clima::class, 'loteid', 'loteid');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstadoLote::class, 'loteid', 'loteid');
    }

    public function certificaciones()
    {
        return $this->hasMany(CertificacionLote::class, 'loteid', 'loteid');
    }

    public function getUbicacionVisibleAttribute(): string
    {
        return UbicacionGpsParser::textoLoteVisible(
            $this->ubicacion,
            $this->loteid,
            $this->latitud,
            $this->longitud
        );
    }

    public function getSuperficieEtiquetaAttribute(): string
    {
        return SuperficieFormato::etiqueta($this->superficie);
    }

    public function getCultivoEtiquetaAttribute(): ?string
    {
        $this->loadMissing('insumoSemilla', 'cultivo');

        if ($this->insumoSemilla && filled($this->insumoSemilla->nombre)) {
            return trim($this->insumoSemilla->nombre);
        }

        return $this->cultivo?->nombre;
    }
}