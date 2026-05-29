<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionAlmacenamiento extends Model
{
    use HasFactory;

    protected $table = 'produccionalmacenamiento';
    protected $primaryKey = 'produccionalmacenamientoid';
    public $timestamps = false;

    protected $fillable = [
        'produccionid',
        'almacenid',
        'cantidad',
        'unidadmedidaid',
        'temperatura',
        'humedad',
        'temperatura_min',
        'temperatura_max',
        'humedad_min',
        'humedad_max',
        'fechaentrada',
        'fechasalida',
        'observaciones',
    ];

    protected $casts = [
        'produccionalmacenamientoid' => 'integer',
        'produccionid'               => 'integer',
        'almacenid'                  => 'integer',
        'unidadmedidaid'             => 'integer',
        'cantidad'                   => 'float',
        'temperatura'                => 'float',
        'humedad'                    => 'float',
        'temperatura_min'            => 'float',
        'temperatura_max'            => 'float',
        'humedad_min'                => 'float',
        'humedad_max'                => 'float',
        'fechaentrada'               => 'datetime',
        'fechasalida'                => 'datetime',
    ];

    protected $hidden = [
        'produccion',
        'almacen',
        'unidadMedida',
    ];

    public function produccion()
    {
        return $this->belongsTo(Produccion::class, 'produccionid', 'produccionid');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }
}