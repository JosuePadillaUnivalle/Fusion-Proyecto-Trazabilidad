<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAlmacen extends Model
{
    use HasFactory;

    protected $table = 'tipoalmacen';
    protected $primaryKey = 'tipoalmacenid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $hidden = [
        'almacenes',
    ];

    public function almacenes()
    {
        return $this->hasMany(Almacen::class, 'tipoalmacenid', 'tipoalmacenid');
    }
}