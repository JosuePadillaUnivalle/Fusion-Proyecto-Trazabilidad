<?php

namespace App\Http\Controllers\Web\OrgTrack;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;

class TransportistaController extends Controller
{
    private function resolveTransportista(Usuario $transportista): Usuario
    {
        abort_unless($transportista->role === 'transportista', 404);
        return $transportista;
    }

    public function index(Request $request)
    {
        $query = Usuario::query()->where('role', 'transportista');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('apellido', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('nombreusuario', 'ilike', "%{$search}%")
                  ->orWhere('telefono', 'ilike', "%{$search}%");
            });
        }

        if ($request->get('activo') !== null && $request->get('activo') !== '') {
            $query->where('activo', (bool) $request->get('activo'));
        }

        $transportistas = $query->orderBy('usuarioid', 'desc')->paginate(20)->withQueryString();
        $total = Usuario::where('role', 'transportista')->count();
        $activos = Usuario::where('role', 'transportista')->where('activo', true)->count();

        return view('orgtrack.transportistas.index', compact('transportistas', 'total', 'activos'));
    }

    public function create()
    {
        return view('orgtrack.transportistas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'        => 'required|string|max:100',
            'apellido'      => 'nullable|string|max:100',
            'nombreusuario' => 'nullable|string|max:100|unique:usuario,nombreusuario',
            'email'         => 'nullable|email|max:150|unique:usuario,email',
            'telefono'      => 'nullable|string|max:50',
            'informacionadicional' => 'nullable|string|max:500',
        ]);

        $data['role'] = 'transportista';
        $data['activo'] = true;
        $data['fecharegistro'] = now();

        if (empty($data['nombreusuario']) && !empty($data['nombre'])) {
            $base = strtolower(str_replace(' ', '.', trim($data['nombre'])));
            $data['nombreusuario'] = $base . rand(10, 99);
        }

        Usuario::create($data);

        return redirect()->route('orgtrack.transportistas.index')
            ->with('success', 'Transportista creado exitosamente.');
    }

    public function edit(Usuario $transportista)
    {
        $this->resolveTransportista($transportista);
        return view('orgtrack.transportistas.edit', compact('transportista'));
    }

    public function update(Request $request, Usuario $transportista)
    {
        $this->resolveTransportista($transportista);

        $data = $request->validate([
            'nombre'        => 'required|string|max:100',
            'apellido'      => 'nullable|string|max:100',
            'nombreusuario' => 'nullable|string|max:100|unique:usuario,nombreusuario,' . $transportista->usuarioid . ',usuarioid',
            'email'         => 'nullable|email|max:150|unique:usuario,email,' . $transportista->usuarioid . ',usuarioid',
            'telefono'      => 'nullable|string|max:50',
            'informacionadicional' => 'nullable|string|max:500',
            'activo'        => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo');

        $transportista->update($data);

        return redirect()->route('orgtrack.transportistas.index')
            ->with('success', 'Transportista actualizado correctamente.');
    }

    public function destroy(Usuario $transportista)
    {
        $this->resolveTransportista($transportista);
        $transportista->delete();

        return redirect()->route('orgtrack.transportistas.index')
            ->with('success', 'Transportista eliminado.');
    }
}
