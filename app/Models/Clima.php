<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clima extends Model
{
    use HasFactory;

    protected $table = 'clima';
    protected $primaryKey = 'climaid';
    public $timestamps = false;

    protected $fillable = [
        'loteid',
        'fecha',
        'temperatura',
        'humedad',
        'lluvia',
        'viento',
        'presion',
        'descripcion',
        'icono',
        'observaciones',
    ];

    protected $casts = [
        'climaid'     => 'integer',
        'loteid'      => 'integer',
        'temperatura' => 'float',
        'humedad'     => 'float',
        'lluvia'      => 'float',
        'viento'      => 'float',
        'presion'     => 'integer',
        'fecha'       => 'datetime',
    ];

    public function lote()
    { 
        return $this->belongsTo(Lote::class, 'loteid', 'loteid'); 
    }
}