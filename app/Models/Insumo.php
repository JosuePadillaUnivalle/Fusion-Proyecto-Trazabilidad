<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumo';
    protected $primaryKey = 'insumoid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipoinsumoid',
        'unidadmedidaid',
        'stock',
        'stockminimo',
        'proveedor',
        'actorid',
        'preciounitario',
        'descripcion',
        'almacenid',
        'codigo_trazabilidad',
    ];

    protected $casts = [
        'insumoid'       => 'integer',
        'tipoinsumoid'   => 'integer',
        'unidadmedidaid' => 'integer',
        'actorid'        => 'integer',
        'stock'          => 'float',
        'stockminimo'    => 'float',
        'preciounitario' => 'float',
        'almacenid' => 'integer',
    ];

    protected $hidden = [
        'tipo',
        'unidadMedida',
        'loteInsumos',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoInsumo::class, 'tipoinsumoid', 'tipoinsumoid');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }

    public function loteInsumos()
    {
        return $this->hasMany(LoteInsumo::class, 'insumoid', 'insumoid');
    }

    public function actorAbastecimiento()
    {
        return $this->belongsTo(ActorAbastecimiento::class, 'actorid', 'actorid');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    /**
     * Decrementa el stock del insumo
     * @param float $cantidad
     * @return bool
     * @throws \Exception
     */
    public function decrementarStock(float $cantidad): bool
    {
        if ($cantidad <= 0) {
            throw new \Exception("La cantidad a decrementar debe ser mayor a 0");
        }

        if ($this->stock < $cantidad) {
            throw new \Exception("Stock insuficiente. Disponible: {$this->stock} {$this->unidadMedida->abreviatura}");
        }

        $this->stock -= $cantidad;
        return $this->save();
    }

    /**
     * Incrementa el stock del insumo
     * @param float $cantidad
     * @return bool
     */
    public function incrementarStock(float $cantidad): bool
    {
        if ($cantidad <= 0) {
            throw new \Exception("La cantidad a incrementar debe ser mayor a 0");
        }

        $this->stock += $cantidad;
        return $this->save();
    }

    /**
     * Verifica si hay stock suficiente
     * @param float $cantidad
     * @return bool
     */
    public function tieneStockSuficiente(float $cantidad): bool
    {
        return $this->stock >= $cantidad;
    }

    /**
     * Verifica si el stock está por debajo del mínimo
     * @return bool
     */
    public function stockBajo(): bool
    {
        return \App\Support\InsumoCatalogo::stockCritico((float) $this->stock);
    }
}