<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionEnvio extends Model
{
    protected $table = 'calificacion_envio';
    protected $primaryKey = 'calificacionenvioid';

    protected $fillable = [
        'pedidoid',
        'usuarioid',
        'perfiltransportistaid',
        'puntuacion',
        'comentario',
        'fecha',
    ];

    protected $casts = [
        'calificacionenvioid'    => 'integer',
        'pedidoid'               => 'integer',
        'usuarioid'              => 'integer',
        'perfiltransportistaid'  => 'integer',
        'puntuacion'             => 'integer',
        'fecha'                  => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function perfilTransportista(): BelongsTo
    {
        return $this->belongsTo(PerfilTransportista::class, 'perfiltransportistaid', 'perfiltransportistaid');
    }
}
