<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RutaMultiEntrega extends Model
{
    protected $table = 'ruta_multi_entrega';
    protected $primaryKey = 'rutamultientregaid';

    protected $fillable = [
        'nombre',
        'creadopor_usuarioid',
        'transportista_usuarioid',
        'estado',
        'fecha_salida',
        'fecha_cierre',
        'resumen',
        'rutageojson',
    ];

    protected $casts = [
        'rutamultientregaid' => 'integer',
        'creadopor_usuarioid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'fecha_salida' => 'datetime',
        'fecha_cierre' => 'datetime',
        'resumen' => 'array',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creadopor_usuarioid', 'usuarioid');
    }

    public function transportista(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'transportista_usuarioid', 'usuarioid');
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(RutaParada::class, 'rutamultientregaid', 'rutamultientregaid')->orderBy('orden');
    }
}

