<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteInsumo extends Model
{
    use HasFactory;

    protected $table = 'loteinsumo';
    protected $primaryKey = 'loteinsumoid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'actividadid',
        'insumoid',
        'usuarioid',
        'cantidadusada',
        'fechauo',
        'costototal',
        'estadoloteinsumoid',
        'observaciones',
    ];

    protected $casts = [
        'loteinsumoid'      => 'integer',
        'loteid'            => 'integer',
        'insumoid'          => 'integer',
        'usuarioid'         => 'integer',
        'cantidadusada'     => 'float',
        'costototal'        => 'float',
        'estadoloteinsumoid'=> 'integer',
        'fechauo'           => 'datetime',
    ];

    protected $hidden = [
        'lote',
        'insumo',
        'usuario',
        'estado',
    ];

    public function lote(){ return $this->belongsTo(Lote::class,'loteid','loteid'); }
    public function insumo(){ return $this->belongsTo(Insumo::class,'insumoid','insumoid'); }
    public function usuario(){ return $this->belongsTo(Usuario::class,'usuarioid','usuarioid'); }
    public function estado(){ return $this->belongsTo(EstadoLoteInsumo::class,'estadoloteinsumoid','estadoloteinsumoid'); }
}