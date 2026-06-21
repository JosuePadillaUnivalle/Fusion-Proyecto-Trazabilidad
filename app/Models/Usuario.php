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

    public function getRouteKeyName(): string
    {
        return 'usuarioid';
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombre.' '.($this->apellido ?? '')) ?: ($this->nombreusuario ?? 'Sin nombre');
    }

    public function avatarUrl(): string
    {
        return \App\Support\UsuarioAvatar::resolve($this);
    }

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'nombreusuario',
        'telefono',
        'ci_nit',
        'tipo_licencia',
        'licencias_json',
        'passwordhash',
        'role',
        'supervisor_usuarioid',
        'imagenurl',
        'informacionadicional',
        'carta_motivacion',
        'rol_solicitado',
        'motivo_rechazo',
        'revisado_por',
        'fecha_revision',
        'estado_cuenta',
        'fecharegistro',
        'fechamodificacion',
        'ultimologin',
        'activo',
        'almacenid',
        'bienvenida_vista',
        'nombreusuario_editado',
    ];

    protected $hidden = [
        'passwordhash',
    ];

    protected $casts = [
        'usuarioid' => 'integer',
        'almacenid' => 'integer',
        'supervisor_usuarioid' => 'integer',
        'activo' => 'boolean',
        'bienvenida_vista' => 'boolean',
        'nombreusuario_editado' => 'boolean',
        'role' => 'string',
        'fecharegistro' => 'datetime',
        'fechamodificacion' => 'datetime',
        'ultimologin' => 'datetime',
        'fecha_revision' => 'datetime',
        'revisado_por' => 'integer',
        'licencias_json' => 'array',
    ];

    public function notificaciones()
    {
        return $this->hasMany(UsuarioNotificacion::class, 'usuarioid', 'usuarioid');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(self::class, 'revisado_por', 'usuarioid');
    }

    public function cuentaAprobada(): bool
    {
        return \App\Support\CuentaEstado::puedeIniciarSesion(
            $this->estado_cuenta ?? \App\Support\CuentaEstado::APROBADO,
            (bool) $this->activo
        );
    }

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

    public function perfilTransportista()
    {
        return $this->hasOne(PerfilTransportista::class, 'usuarioid', 'usuarioid');
    }

    public function supervisor()
    {
        return $this->belongsTo(self::class, 'supervisor_usuarioid', 'usuarioid');
    }

    public function empleados()
    {
        return $this->hasMany(self::class, 'supervisor_usuarioid', 'usuarioid');
    }
}