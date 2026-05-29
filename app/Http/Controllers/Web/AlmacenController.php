<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\TipoAlmacen;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        $almacenes = Almacen::with(['tipoAlmacen', 'unidadMedida'])
            ->orderBy('almacenid', 'desc')
            ->paginate(15);

        return view('almacenes.index', compact('almacenes'));
    }

    public function create()
    {
        $tipos    = TipoAlmacen::all();
        $unidades = UnidadMedida::all();

        return view('almacenes.create', compact('tipos', 'unidades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:100|unique:almacen,nombre',
            'descripcion'    => 'nullable|string|max:250',
            'ubicacion'      => 'nullable|string|max:200',
            'capacidad'      => 'nullable|numeric|min:0',
            'unidadmedidaid' => 'nullable|exists:unidadmedida,unidadmedidaid',
            'tipoalmacenid'  => 'nullable|exists:tipoalmacen,tipoalmacenid',
            'activo'         => 'boolean',
        ]);

        // si no viene activo, lo dejamos por defecto de BD (true)
        if (!isset($data['activo'])) {
            unset($data['activo']);
        }

        Almacen::create($data);

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén creado.');
    }

    public function show(Almacen $almacen)
    {
        $almacen->load(['tipoAlmacen', 'unidadMedida', 'almacenamientos']);

        return view('almacenes.show', compact('almacen'));
    }

    public function edit(Almacen $almacen)
    {
        $tipos    = TipoAlmacen::all();
        $unidades = UnidadMedida::all();

        return view('almacenes.edit', compact('almacen', 'tipos', 'unidades'));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:100|unique:almacen,nombre,' . $almacen->almacenid . ',almacenid',
            'descripcion'    => 'nullable|string|max:250',
            'ubicacion'      => 'nullable|string|max:200',
            'capacidad'      => 'nullable|numeric|min:0',
            'unidadmedidaid' => 'nullable|exists:unidadmedida,unidadmedidaid',
            'tipoalmacenid'  => 'nullable|exists:tipoalmacen,tipoalmacenid',
            'activo'         => 'boolean',
        ]);

        $almacen->update($data);

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén actualizado.');
    }

    public function destroy(Almacen $almacen)
    {
        $almacen->delete();

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén eliminado.');
    }
}