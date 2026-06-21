<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prioridad extends Model
{
    use HasFactory;

    protected $table = 'prioridad';
    protected $primaryKey = 'prioridadid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = [
        'actividades',
    ];

    // Relaciones
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'prioridadid', 'prioridadid');
    }
}