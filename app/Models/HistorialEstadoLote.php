<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoLote extends Model
{
    use HasFactory;

    protected $table = 'historial_estados_lote';
    protected $primaryKey = 'historial_estado_id';

    // Tiene created_at y updated_at
    public $timestamps = true;

    protected $fillable = [
        'loteid',
        'estadolotetipoid',
        'fecha_cambio',
        'observaciones',
        'imagenurl',
        'usuarioid',
    ];

    protected $casts = [
        'historial_estado_id' => 'integer',
        'loteid'              => 'integer',
        'estadolotetipoid'    => 'integer',
        'usuarioid'           => 'integer',
        'fecha_cambio'        => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    protected $hidden = [
        'lote',
        'estadoTipo',
        'usuario',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'loteid', 'loteid');
    }

    public function estadoTipo()
    {
        return $this->belongsTo(EstadoLoteTipo::class, 'estadolotetipoid', 'estadolotetipoid');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }
}