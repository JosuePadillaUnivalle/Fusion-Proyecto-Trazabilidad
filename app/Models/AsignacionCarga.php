<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AsignacionCarga extends Pivot
{
    protected $table = 'asignacion_carga';

    public $incrementing = false;

    public $timestamps = false;
}
