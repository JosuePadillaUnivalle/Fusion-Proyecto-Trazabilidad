<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use Illuminate\Http\Request;

class CultivoController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        return redirect()
            ->route('insumos.index')
            ->with('info', 'El cultivo se define por la semilla del inventario de insumos al crear o editar un lote. El catálogo de cultivos ya no se usa.');
    }

    public function create(Request $request)
    {
        return view('cultivos.create', [
            'retornoLote' => $request->query('retorno') === 'lote',
            'selectorLoteId' => $request->query('selector', 'lote_cultivo'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:cultivo,nombre'],
            'detalle' => ['nullable', 'string', 'max:500'],
            'retorno' => ['nullable', 'string', 'in:lote'],
            'selector' => ['nullable', 'string', 'max:80'],
        ]);

        $cultivo = Cultivo::create([
            'nombre' => $data['nombre'],
            'detalle' => $data['detalle'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cultivoid' => $cultivo->cultivoid,
                'nombre' => $cultivo->nombre,
                'message' => 'Cultivo creado exitosamente',
            ]);
        }

        if (($data['retorno'] ?? null) === 'lote') {
            return view('cultivos.asignar-opener', [
                'cultivo' => $cultivo,
                'selectorId' => $data['selector'] ?? 'lote_cultivo',
            ]);
        }

        return redirect()->route('cultivos.index')->with('success', 'Cultivo creado correctamente.');
    }

    public function show(Cultivo $cultivo)
    {
        return view('cultivos.show', ['item' => $cultivo]);
    }

    public function edit(Cultivo $cultivo)
    {
        return view('cultivos.edit', ['item' => $cultivo]);
    }

    public function update(Request $request, Cultivo $cultivo)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:cultivo,nombre,'.$cultivo->cultivoid.',cultivoid'],
            'detalle' => ['nullable', 'string', 'max:500'],
        ]);

        $cultivo->update($data);

        return redirect()->route('cultivos.index')->with('success', 'Cultivo actualizado correctamente.');
    }

    public function destroy(Cultivo $cultivo)
    {
        if ($cultivo->lotes()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay lotes asociados a este cultivo.');
        }

        $cultivo->delete();

        return redirect()->route('cultivos.index')->with('success', 'Cultivo eliminado correctamente.');
    }
}
