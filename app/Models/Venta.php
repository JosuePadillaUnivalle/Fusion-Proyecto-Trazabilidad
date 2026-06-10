<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'venta';
    protected $primaryKey = 'ventaid';
    public $timestamps = false;

    protected $fillable = [
        'produccionid',
        'cliente',
        'cantidad',
        'unidadmedidaid',
        'preciounitario',
        'total',
        'fechaventa',
        'observaciones',
    ];

    protected static function booted(): void
    {
        static::saving(function (Venta $venta) {
            if ($venta->cantidad !== null && $venta->preciounitario !== null) {
                $venta->total = round((float) $venta->cantidad * (float) $venta->preciounitario, 2);
            }
        });
    }

    protected $casts = [
        'ventaid'        => 'integer',
        'produccionid'   => 'integer',
        'cantidad'       => 'float',
        'unidadmedidaid' => 'integer',
        'preciounitario' => 'float',
        'total'          => 'float',
        'fechaventa'     => 'date',
    ];

    protected $hidden = [
        'produccion',
        'unidadMedida',
    ];

    public function produccion()
    {
        return $this->belongsTo(Produccion::class, 'produccionid', 'produccionid');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidadmedidaid', 'unidadmedidaid');
    }
}