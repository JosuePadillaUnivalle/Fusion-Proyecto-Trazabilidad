<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribucionSalida extends Model
{
    protected $table = 'distribucion_salida';

    protected $primaryKey = 'distribucionsalidaid';

    protected $fillable = [
        'codigo_comprobante', 'fecha', 'estado', 'almacenid', 'operador_usuarioid',
        'transportista_usuarioid', 'distribuciontiposalidaid', 'vehiculoid', 'administrador_usuarioid',
    ];

    protected $casts = [
        'fecha' => 'date',
        'estado' => 'integer',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DistribucionDetalleSalida::class, 'distribucionsalidaid', 'distribucionsalidaid');
    }
}
