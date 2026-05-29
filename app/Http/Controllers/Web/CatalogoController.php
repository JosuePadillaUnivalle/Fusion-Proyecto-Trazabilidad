<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use App\Models\TipoActividad;
use App\Models\TipoInsumo;
use App\Models\TipoAlmacen;
use App\Models\UnidadMedida;
use App\Models\EstadoLoteTipo;
use App\Models\EstadoLoteInsumo;
use App\Models\Prioridad;

class CatalogoController extends Controller
{
    public function index()
    {
        $counts = [
            'cultivos' => Cultivo::count(),
            'tiposActividad' => TipoActividad::count(),
            'tiposInsumo' => TipoInsumo::count(),
            'tiposAlmacen' => TipoAlmacen::count(),
            'unidadesMedida' => UnidadMedida::count(),
            'estadosLote' => EstadoLoteTipo::count(),
            'estadosInsumo' => EstadoLoteInsumo::count(),
            'prioridades' => Prioridad::count(),
        ];

        return view('catalogos.index', compact('counts'));
    }
}