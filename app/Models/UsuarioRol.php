<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioRol extends Model
{
    use HasFactory;

    protected $table = 'usuariorol';
    protected $primaryKey = 'usuariorolid';
    public $timestamps = false;

    protected $fillable = [
        'usuarioid',
        'rolid',
    ];

    protected $casts = [
        'usuariorolid' => 'integer',
        'usuarioid'    => 'integer',
        'rolid'        => 'integer',
    ];

    protected $hidden = [
        'usuario',
        'rol',
    ];

    public function usuario(){ return $this->belongsTo(Usuario::class,'usuarioid','usuarioid'); }
    public function rol(){ return $this->belongsTo(Rol::class,'rolid','rolid'); }
}