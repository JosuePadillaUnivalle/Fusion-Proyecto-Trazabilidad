<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlmacenMovimiento extends Model
{
    use HasFactory;

    protected $table = 'almacen_movimiento';
    protected $primaryKey = 'almacen_movimientoid';

    protected $fillable = [
        'almacenid',
        'insumoid',
        'insumo_presentacionid',
        'loteproduccionpedidoid',
        'tipo_movimiento_almacenid',
        'usuarioid',
        'fecha',
        'cantidad',
        'cantidad_unidades',
        'referencia',
        'destino_motivo',
        'observaciones',
    ];

    protected $casts = [
        'almacenid' => 'integer',
        'insumoid' => 'integer',
        'insumo_presentacionid' => 'integer',
        'loteproduccionpedidoid' => 'integer',
        'tipo_movimiento_almacenid' => 'integer',
        'usuarioid' => 'integer',
        'fecha' => 'date',
        'cantidad' => 'float',
        'cantidad_unidades' => 'float',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumoid', 'insumoid');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoMovimientoAlmacen::class, 'tipo_movimiento_almacenid', 'tipo_movimiento_almacenid');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }
}
