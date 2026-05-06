<?php

namespace App\Http\Controllers\Web\OrgTrack;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;

class TransportistaController extends Controller
{
    public function index()
    {
        $transportistas = Usuario::query()
            ->where('role', 'transportista')
            ->orderBy('usuarioid', 'desc')
            ->paginate(20);

        return view('orgtrack.transportistas.index', compact('transportistas'));
    }

    public function create()
    {
        return view('orgtrack.transportistas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:50',
        ]);

        $data['role'] = 'transportista';
        $data['activo'] = true;
        $data['fecharegistro'] = now();

        Usuario::create($data);

        return redirect()->route('orgtrack.transportistas.index')
            ->with('success', 'Transportista creado');
    }

    public function edit(Usuario $transportista)
    {
        return view('orgtrack.transportistas.edit', compact('transportista'));
    }

    public function update(Request $request, Usuario $transportista)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ]);

        $transportista->update($data);

        return redirect()->route('orgtrack.transportistas.index')
            ->with('success', 'Transportista actualizado');
    }

    public function destroy(Usuario $transportista)
    {
        $transportista->delete();

        return redirect()->route('orgtrack.transportistas.index')
            ->with('success', 'Transportista eliminado');
    }
}
