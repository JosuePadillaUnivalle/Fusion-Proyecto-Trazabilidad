<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoIncidenteTransporte extends Model
{
    protected $table = 'tipo_incidente_transporte';
    protected $primaryKey = 'tipoincidentetransporteid';

    protected $fillable = ['codigo', 'titulo', 'descripcion'];
}
