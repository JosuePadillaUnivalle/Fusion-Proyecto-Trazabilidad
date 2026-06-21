<?php

namespace App\Models;

use App\Support\RutaDistribucionCatalogo;
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
        'tipo_ruta',
        'almacen_planta_origenid',
        'almacen_mayorista_origenid',
        'almacen_mayorista_destinoid',
        'transportista_usuarioid',
        'vehiculoid',
        'costo_bs',
        'simulacion_inicio_at',
        'simulacion_duracion_seg',
        'simulacion_geojson',
        'creado_por_usuarioid',
        'estado',
        'fecha_salida',
        'fecha_aprobacion_mayorista',
        'aprobado_por_usuarioid',
        'motivo_rechazo_mayorista',
        'rutageojson',
    ];

    protected $casts = [
        'rutadistribucionid' => 'integer',
        'almacen_planta_origenid' => 'integer',
        'almacen_mayorista_origenid' => 'integer',
        'almacen_mayorista_destinoid' => 'integer',
        'transportista_usuarioid' => 'integer',
        'vehiculoid' => 'integer',
        'creado_por_usuarioid' => 'integer',
        'aprobado_por_usuarioid' => 'integer',
        'costo_bs' => 'decimal:2',
        'simulacion_inicio_at' => 'datetime',
        'simulacion_geojson' => 'array',
        'fecha_salida' => 'datetime',
        'fecha_aprobacion_mayorista' => 'datetime',
        'rutageojson' => 'array',
    ];

    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_mayorista_origenid', 'almacenid');
    }

    public function almacenMayoristaOrigen(): BelongsTo
    {
        return $this->almacenOrigen();
    }

    public function almacenPlantaOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_planta_origenid', 'almacenid');
    }

    public function almacenMayoristaDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_mayorista_destinoid', 'almacenid');
    }

    public function esTrasladoPlantaMayorista(): bool
    {
        return RutaDistribucionCatalogo::esTrasladoPlantaMayorista($this);
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

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'aprobado_por_usuarioid', 'usuarioid');
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(RutaDistribucionParada::class, 'rutadistribucionid', 'rutadistribucionid')->orderBy('orden');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(PedidoDistribucion::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function detallesTraslado(): HasMany
    {
        return $this->hasMany(DetalleTrasladoPlantaMayorista::class, 'rutadistribucionid', 'rutadistribucionid');
    }

    public function getRouteKeyName(): string
    {
        return 'rutadistribucionid';
    }
}
