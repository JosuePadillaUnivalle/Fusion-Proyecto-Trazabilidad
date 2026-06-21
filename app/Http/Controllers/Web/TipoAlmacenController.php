<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\TipoAlmacen;
use Illuminate\Http\Request;

class TipoAlmacenController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        $query = TipoAlmacen::query();
        $this->aplicarFiltroBuscar($query, $request, ['nombre', 'descripcion']);
        $tipos = $query->orderBy('tipoalmacenid')->paginate(15)->withQueryString();

        return view('tipoalmacenes.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipoalmacenes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:tipoalmacen,nombre',
            'descripcion' => 'nullable|string|max:200',
        ]);

        TipoAlmacen::create($data);

        return redirect()->route('tipoalmacenes.index')->with('success', 'Tipo de almacén creado.');
    }

    public function show(TipoAlmacen $tipoalmacen)
    {
        return view('tipoalmacenes.show', ['item' => $tipoalmacen]);
    }

    public function edit(TipoAlmacen $tipoalmacen)
    {
        return view('tipoalmacenes.edit', ['item' => $tipoalmacen]);
    }

    public function update(Request $request, TipoAlmacen $tipoalmacen)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:tipoalmacen,nombre,'.$tipoalmacen->tipoalmacenid.',tipoalmacenid',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipoalmacen->update($data);

        return redirect()->route('tipoalmacenes.index')->with('success', 'Tipo de almacén actualizado.');
    }

    public function destroy(TipoAlmacen $tipoalmacen)
    {
        $tipoalmacen->delete();

        return redirect()->route('tipoalmacenes.index')->with('success', 'Tipo de almacén eliminado.');
    }
}
