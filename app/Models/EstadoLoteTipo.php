<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoLoteTipo extends Model
{
    use HasFactory;

    protected $table = 'estadolote_tipo';
    protected $primaryKey = 'estadolotetipoid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
    
    protected $hidden = [
        'lotes',
        'estadosLote',
        'historial',
    ];

    // Lotes asociados a este tipo de estado
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'estadolotetipoid', 'estadolotetipoid');
    }

    // Historial de estados de lotes
    public function estadosLote()
    {
        return $this->hasMany(EstadoLote::class, 'estadolotetipoid', 'estadolotetipoid');
    }

    public function historial()
    {
        return $this->hasMany(HistorialEstadoLote::class, 'estadolotetipoid', 'estadolotetipoid');
    }
}
