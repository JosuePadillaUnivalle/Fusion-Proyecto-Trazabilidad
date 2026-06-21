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
        'ambito',
        'responsable_usuarioid',
        'activo',
    ];

    protected $casts = [
        'almacenid'      => 'integer',
        'capacidad'      => 'float',
        'unidadmedidaid' => 'integer',
        'tipoalmacenid'  => 'integer',
        'responsable_usuarioid' => 'integer',
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

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_usuarioid', 'usuarioid');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'almacenid', 'almacenid');
    }
}