<?php

namespace App\Models;

use App\Support\CultivoCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cultivo extends Model
{
    use HasFactory;

    protected $table = 'cultivo';
    protected $primaryKey = 'cultivoid';
    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'cultivoid';
    }

    protected $fillable = [
        'nombre',
        'detalle',
        'dosis_siembra_por_ha',
        'dosis_siembra_unidad',
    ];

    protected $casts = [
        'dosis_siembra_por_ha' => 'float',
    ];

    public function detalleVisible(): ?string
    {
        if (filled($this->detalle)) {
            return $this->detalle;
        }

        return CultivoCatalogo::detallePorNombre($this->nombre);
    }

    protected $hidden = [
        'lotes',
    ];

    // Relaciones
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'cultivoid', 'cultivoid');
    }
}