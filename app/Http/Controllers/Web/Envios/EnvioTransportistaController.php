<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class EnvioTransportistaController extends Controller
{
    public function __construct()
    {
        $this->middleware('action.permission:transportistas,read')->only(['index', 'show']);
        $this->middleware('action.permission:transportistas,create')->only(['create', 'store']);
        $this->middleware('action.permission:transportistas,update')->only(['edit', 'update']);
        $this->middleware('action.permission:transportistas,delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $q = Usuario::query()->where('role', 'transportista');

        if ($request->filled('buscar')) {
            $b = '%'.trim((string) $request->buscar).'%';
            $q->where(function ($query) use ($b) {
                $query->where('nombre', 'like', $b)
                    ->orWhere('apellido', 'like', $b)
                    ->orWhere('email', 'like', $b)
                    ->orWhere('telefono', 'like', $b);
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $q->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $q->where('activo', false);
            }
        }

        $transportistas = $q->orderByDesc('usuarioid')->paginate(15)->withQueryString();

        return view('envios.transportistas.index', [
            'transportistas' => $transportistas,
            'stats' => ['total' => Usuario::where('role', 'transportista')->count()],
        ]);
    }

    public function create(): View
    {
        return view('envios.transportistas.create');
    }

    public function show(Usuario $transportista): View
    {
        $this->asegurarTransportista($transportista);

        return view('envios.transportistas.show', compact('transportista'));
    }

    public function edit(Usuario $transportista): View
    {
        $this->asegurarTransportista($transportista);

        return view('envios.transportistas.edit', compact('transportista'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:usuario,email',
            'nombreusuario' => 'nullable|string|max:80|unique:usuario,nombreusuario',
            'telefono' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:4|max:60',
        ]);

        $usuario = Usuario::create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'] ?? '',
            'email' => $data['email'],
            'nombreusuario' => $data['nombreusuario'] ?? strstr($data['email'], '@', true) ?: $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'passwordhash' => Hash::make($data['password'] ?? '12345'),
            'role' => 'transportista',
            'activo' => true,
            'fecharegistro' => now(),
        ]);

        Role::findOrCreate('transportista', 'web');
        $usuario->assignRole('transportista');

        return redirect()
            ->route('envios.transportistas.show', $usuario)
            ->with('success', 'Transportista registrado correctamente.');
    }

    public function update(Request $request, Usuario $transportista): RedirectResponse
    {
        $this->asegurarTransportista($transportista);

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:usuario,email,'.$transportista->usuarioid.',usuarioid',
            'nombreusuario' => 'nullable|string|max:80|unique:usuario,nombreusuario,'.$transportista->usuarioid.',usuarioid',
            'telefono' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
            'password' => 'nullable|string|min:4|max:60',
        ]);

        $transportista->nombre = $data['nombre'];
        $transportista->apellido = $data['apellido'] ?? '';
        $transportista->email = $data['email'];
        $transportista->nombreusuario = $data['nombreusuario'] ?? $transportista->nombreusuario;
        $transportista->telefono = $data['telefono'] ?? null;
        $transportista->activo = $request->boolean('activo');
        $transportista->fechamodificacion = now();

        if (! empty($data['password'])) {
            $transportista->passwordhash = Hash::make($data['password']);
        }

        $transportista->save();

        return redirect()
            ->route('envios.transportistas.show', $transportista)
            ->with('success', 'Transportista actualizado.');
    }

    public function destroy(Usuario $transportista): RedirectResponse
    {
        $this->asegurarTransportista($transportista);
        $transportista->delete();

        return redirect()
            ->route('envios.transportistas.index')
            ->with('success', 'Transportista eliminado.');
    }

    private function asegurarTransportista(Usuario $usuario): void
    {
        abort_unless($usuario->role === 'transportista', 404);
    }
}
