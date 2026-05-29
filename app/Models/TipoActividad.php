<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoActividad extends Model
{
    use HasFactory;

    protected $table = 'tipoactividad';
    protected $primaryKey = 'tipoactividadid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $hidden = [
        'actividades',
    ];

    // Relaciones
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'tipoactividadid', 'tipoactividadid');
    }
}