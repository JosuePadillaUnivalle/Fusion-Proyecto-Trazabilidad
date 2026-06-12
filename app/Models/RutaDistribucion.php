<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RutaDistribucion extends Model
{
    protected $table = 'ruta_distribucion';

    protected $primaryKey = 'rutadistribucionid';

    protected $fillable = [
        'codigo',
        'nombre',
        'almacen_planta_origenid',
        'transportista_usuarioid',
        'vehiculoid',
        'creado_por_usuarioid',
        'estado',
        'fecha_salida',
        'rutageojson',
    ];

    protected $casts = [
        'rutadistribucionid' => 'integer',
        'almacen_planta_origenid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'vehiculoid' => 'integer',
        'creado_por_usuarioid' => 'integer',
        'fecha_salida' => 'datetime',
        'rutageojson' => 'array',
    ];

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_planta_origenid', 'almacenid');
    }

    public function transportista(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'transportista_usuarioid', 'usuarioid');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculoid', 'vehiculoid');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por_usuarioid', 'usuarioid');
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(RutaDistribucionParada::class, 'rutadistribucionid', 'rutadistribucionid')->orderBy('orden');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(PedidoDistribucion::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function getRouteKeyName(): string
    {
        return 'rutadistribucionid';
    }
}
