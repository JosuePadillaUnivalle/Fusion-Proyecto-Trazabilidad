<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'usuario';
    protected $primaryKey = 'usuarioid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'nombreusuario',
        'telefono',
        'passwordhash',
        'role',
        'imagenurl',
        'informacionadicional',
        'fecharegistro',
        'fechamodificacion',
        'ultimologin',
        'activo',
        'almacenid',
    ];

    protected $hidden = [
        'passwordhash',
    ];

    protected $casts = [
        'usuarioid' => 'integer',
        'almacenid' => 'integer',
        'activo' => 'boolean',
        'role' => 'string',
        'fecharegistro' => 'datetime',
        'fechamodificacion' => 'datetime',
        'ultimologin' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->passwordhash;
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'usuarioid', 'usuarioid');
    }
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'usuarioid', 'usuarioid');
    }
    public function loteInsumos()
    {
        return $this->hasMany(LoteInsumo::class, 'usuarioid', 'usuarioid');
    }
    public function historialEstadosLote()
    {
        return $this->hasMany(HistorialEstadoLote::class, 'usuarioid', 'usuarioid');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function operadorPlanta()
    {
        return $this->hasOne(OperadorPlanta::class, 'usuarioid', 'usuarioid');
    }

    public function direccionesGeoEnvio()
    {
        return $this->hasMany(DireccionGeoEnvio::class, 'usuarioid', 'usuarioid');
    }

    public function almacenUsuarios()
    {
        return $this->hasMany(AlmacenUsuario::class, 'usuarioid', 'usuarioid');
    }
}