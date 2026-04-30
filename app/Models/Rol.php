<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'rolid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $hidden = [
        'usuarios',
    ];

    // Relación N:N con usuarios mediante la tabla usuariorol
    public function usuarios()
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuariorol',
            'rolid',
            'usuarioid',
            'rolid',
            'usuarioid'
        );
    }
}