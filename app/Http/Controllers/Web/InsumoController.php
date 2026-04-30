<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\ActorAbastecimiento;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index()
    {
        $q = Insumo::with(['tipo', 'unidadMedida'])->orderBy('insumoid', 'desc');
        $user = auth()->user();
        if ($user?->hasRole('almacen')) {
            if ($user->almacenid) {
                $q->where('almacenid', $user->almacenid);
            } else {
                $q->whereRaw('0 = 1');
            }
        }
        $insumos = $q->paginate(15);

        return view('insumos.index', compact('insumos'));
    }

    public function create()
    {
        $tipos = TipoInsumo::all();
        $unidades = UnidadMedida::all();
        $actores = ActorAbastecimiento::where('activo', true)->orderBy('nombre')->get();

        return view('insumos.create', compact('tipos', 'unidades', 'actores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipoinsumoid' => 'required|exists:tipoinsumo,tipoinsumoid',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'stock' => 'required|numeric|min:0',
            'stockminimo' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:100',
            'actorid' => 'nullable|exists:actor_abastecimiento,actorid',
            'preciounitario' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
        ]);

        if ($request->user()?->hasRole('almacen') && $request->user()->almacenid) {
            $data['almacenid'] = $request->user()->almacenid;
        }

        Insumo::create($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo creado.');
    }

    public function show(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

        return view('insumos.show', compact('insumo'));
    }

    public function edit(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

        $tipos = TipoInsumo::all();
        $unidades = UnidadMedida::all();
        $actores = ActorAbastecimiento::where('activo', true)->orderBy('nombre')->get();

        return view('insumos.edit', compact('insumo', 'tipos', 'unidades', 'actores'));
    }

    public function update(Request $request, Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipoinsumoid' => 'required|exists:tipoinsumo,tipoinsumoid',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'stock' => 'required|numeric|min:0',
            'stockminimo' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:100',
            'actorid' => 'nullable|exists:actor_abastecimiento,actorid',
            'preciounitario' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
        ]);

        $insumo->update($data);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado.');
    }

    public function destroy(Insumo $insumo)
    {
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

        $insumo->delete();

        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado.');
    }

    private function asegurarInsumoDelAlmacenUsuario(Insumo $insumo): void
    {
        $u = auth()->user();
        if (! $u?->hasRole('almacen')) {
            return;
        }
        if (! $u->almacenid || (int) $insumo->almacenid !== (int) $u->almacenid) {
            abort(403);
        }
    }
}