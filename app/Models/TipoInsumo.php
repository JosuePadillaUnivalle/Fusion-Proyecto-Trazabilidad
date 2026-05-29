<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoInsumo extends Model
{
    use HasFactory;

    protected $table = 'tipoinsumo';
    protected $primaryKey = 'tipoinsumoid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = [
        'insumos',
    ];

    // Relaciones
    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'tipoinsumoid', 'tipoinsumoid');
    }
}