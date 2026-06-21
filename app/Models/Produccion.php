<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produccion extends Model
{
    use HasFactory;

    protected $table = 'produccion';
    protected $primaryKey = 'produccionid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'cantidad',
        'unidadmedidaid',
        'cantidad_base',
        'fechacosecha',
        'destinoproduccionid',
        'almacendestinoid',
        'procesoplantaid',
        'maquinaplantaid',
        'imagenurl',
        'observaciones',
    ];

    protected $casts = [
        'produccionid'        => 'integer',
        'loteid'              => 'integer',
        'cantidad'            => 'float',
        'unidadmedidaid'      => 'integer',
        'cantidad_base'       => 'float',
        'destinoproduccionid' => 'integer',
        'almacendestinoid'    => 'integer',
        'procesoplantaid'     => 'integer',
        'maquinaplantaid'     => 'integer',
        'fechacosecha'        => 'date',
    ];

    protected $hidden = [
        'lote',
        'destino',
        'ventas',
        'unidadMedida',
        'almacenamientos',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'loteid', 'loteid');
    }

    public function destino()
    {
        return $this->belongsTo(DestinoProduccion::class, 'destinoproduccionid', 'destinoproduccionid');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'produccionid', 'produccionid');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function almacenamientos()
    {
        return $this->hasMany(ProduccionAlmacenamiento::class, 'produccionid', 'produccionid');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacendestinoid', 'almacenid');
    }

    public function procesoPlanta()
    {
        return $this->belongsTo(ProcesoPlanta::class, 'procesoplantaid', 'procesoplantaid');
    }

    public function maquinaPlanta()
    {
        return $this->belongsTo(MaquinaPlanta::class, 'maquinaplantaid', 'maquinaplantaid');
    }

    /**
     * Obtiene la cantidad disponible para venta (cantidad - vendido)
     */
    public function getCantidadDisponibleAttribute()
    {
        $vendido = $this->ventas()->sum('cantidad');
        return $this->cantidad - $vendido;
    }
}