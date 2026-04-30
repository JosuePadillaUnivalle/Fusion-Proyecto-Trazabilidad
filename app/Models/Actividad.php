<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    protected $table = 'actividad';
    protected $primaryKey = 'actividadid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'usuarioid',
        'descripcion',
        'fechainicio',
        'fechafin',
        'tipoactividadid',
        'prioridadid',
        'observaciones',
    ];

    protected $casts = [
        'actividadid'     => 'integer',
        'loteid'          => 'integer',
        'usuarioid'       => 'integer',
        'tipoactividadid' => 'integer',
        'prioridadid'     => 'integer',
        'fechainicio'     => 'datetime',
        'fechafin'        => 'datetime',
    ];

    protected $hidden = [
        'lote',
        'usuario',
        'tipoActividad',
        'prioridad',
    ];

    public function lote()       { return $this->belongsTo(Lote::class,'loteid','loteid'); }
    public function usuario()    { return $this->belongsTo(Usuario::class,'usuarioid','usuarioid'); }
    public function tipoActividad(){return $this->belongsTo(TipoActividad::class,'tipoactividadid','tipoactividadid');}
    public function prioridad()  { return $this->belongsTo(Prioridad::class,'prioridadid','prioridadid'); }
}