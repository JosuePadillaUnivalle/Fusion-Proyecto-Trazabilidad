<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondicionTransporte extends Model
{
    protected $table = 'condicion_transporte';
    protected $primaryKey = 'condiciontransporteid';

    protected $fillable = ['codigo', 'titulo', 'descripcion'];
}
