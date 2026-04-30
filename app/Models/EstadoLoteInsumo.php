<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoLoteInsumo extends Model
{
    use HasFactory;

    protected $table = 'estadoloteinsumo';
    protected $primaryKey = 'estadoloteinsumoid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = [
        'loteInsumos',
    ];

    // Registros de insumos aplicados al lote
    public function loteInsumos()
    {
        return $this->hasMany(LoteInsumo::class, 'estadoloteinsumoid', 'estadoloteinsumoid');
    }
}