<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cultivo extends Model
{
    use HasFactory;

    protected $table = 'cultivo';
    protected $primaryKey = 'cultivoid';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = [
        'lotes',
    ];

    // Relaciones
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'cultivoid', 'cultivoid');
    }
}