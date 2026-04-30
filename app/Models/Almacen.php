<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacen';
    protected $primaryKey = 'almacenid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'capacidad',
        'unidadmedidaid',
        'tipoalmacenid',
        'activo',
    ];

    protected $casts = [
        'almacenid'      => 'integer',
        'capacidad'      => 'float',
        'unidadmedidaid' => 'integer',
        'tipoalmacenid'  => 'integer',
        'activo'         => 'boolean',
    ];

    protected $hidden = [
        'tipoAlmacen',
        'unidadMedida',
        'almacenamientos',
    ];

    public function tipoAlmacen()
    {
        return $this->belongsTo(TipoAlmacen::class, 'tipoalmacenid', 'tipoalmacenid');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function almacenamientos()
    {
        return $this->hasMany(ProduccionAlmacenamiento::class, 'almacenid', 'almacenid');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'almacenid', 'almacenid');
    }
}