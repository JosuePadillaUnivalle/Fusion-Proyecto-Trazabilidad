<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActorAbastecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActorAbastecimientoController extends Controller
{
    public function index(): View
    {
        $q = ActorAbastecimiento::query();

        $stats = [
            'total' => (clone $q)->count(),
            'activos' => (clone $q)->where('activo', true)->count(),
            'inactivos' => (clone $q)->where('activo', false)->count(),
            'tipos' => (clone $q)->distinct()->count('tipo_actor'),
            'productores' => (clone $q)->where('tipo_actor', 'productor')->count(),
            'proveedores' => (clone $q)->where('tipo_actor', 'proveedor')->count(),
            'mixtos' => (clone $q)->where('tipo_actor', 'mixto')->count(),
        ];

        $tiposFiltro = (clone $q)->distinct()
            ->orderBy('tipo_actor')
            ->pluck('tipo_actor')
            ->filter()
            ->values();

        $actores = ActorAbastecimiento::orderBy('actorid', 'desc')->paginate(15);

        return view('actores_abastecimiento.index', compact('actores', 'stats', 'tiposFiltro'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:120',
            'tipo_actor' => 'required|in:productor,proveedor,mixto',
            'email'      => 'nullable|email|max:120',
            'telefono'   => 'nullable|string|max:30',
            'activo'     => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        ActorAbastecimiento::create($data);

        return redirect()->route('actores-abastecimiento.index')->with('success', 'Actor de abastecimiento creado.');
    }

    public function update(Request $request, ActorAbastecimiento $actores_abastecimiento): RedirectResponse
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:120',
            'tipo_actor' => 'required|in:productor,proveedor,mixto',
            'email'      => 'nullable|email|max:120',
            'telefono'   => 'nullable|string|max:30',
            'activo'     => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', false);
        $actores_abastecimiento->update($data);

        return redirect()->route('actores-abastecimiento.index')->with('success', 'Actor de abastecimiento actualizado.');
    }

    public function destroy(ActorAbastecimiento $actores_abastecimiento): RedirectResponse
    {
        $actores_abastecimiento->delete();
        return redirect()->route('actores-abastecimiento.index')->with('success', 'Actor de abastecimiento eliminado.');
    }
}
