<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoLote extends Model
{
    use HasFactory;

    protected $table = 'estadolote';
    protected $primaryKey = 'estadoid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'estadolotetipoid',
        'fecharegistro',
        'observaciones',
        'imagenurl',
    ];

    protected $casts = [
        'estadoid'        => 'integer',
        'loteid'          => 'integer',
        'estadolotetipoid'=> 'integer',
        'fecharegistro'   => 'datetime',
    ];
    
    protected $hidden = [
        'lote',
        'estadoTipo',
    ];

    public function lote(){ return $this->belongsTo(Lote::class,'loteid','loteid'); }
    public function estadoTipo(){ return $this->belongsTo(EstadoLoteTipo::class,'estadolotetipoid','estadolotetipoid'); }
}