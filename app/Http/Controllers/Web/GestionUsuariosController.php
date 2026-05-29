<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class GestionUsuariosController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->usuariosFilteredQuery($request);

        $stats = [
            'total' => Usuario::query()->count(),
            'activos' => Usuario::query()->where('activo', true)->count(),
            'inactivos' => Usuario::query()->where('activo', false)->count(),
            'roles' => count($this->rolesCanonicos()),
        ];

        $usuarios = $query->orderByDesc('usuarioid')->paginate(15)->withQueryString();
        $roles = $this->rolesCanonicos();

        return view('usuarios.index', compact('usuarios', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = $this->rolesCanonicos();

        return view('usuarios.create', compact('roles'));
    }

    public function show(Usuario $usuario)
    {
        $usuario->load(['roles', 'almacen']);

        $stats = [
            'lotes' => $usuario->lotes()->count(),
            'actividades' => $usuario->actividades()->count(),
        ];

        $lotesRecientes = $usuario->lotes()
            ->with(['cultivo', 'estadoTipo'])
            ->orderByDesc('loteid')
            ->limit(10)
            ->get();

        return view('usuarios.show', compact('usuario', 'stats', 'lotesRecientes'));
    }

    public function edit(Usuario $usuario)
    {
        $usuario->load('roles');
        $roles = $this->rolesCanonicos();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function storeUsuario(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuario,email',
            'nombreusuario' => 'required|string|max:100|unique:usuario,nombreusuario',
            'telefono' => 'nullable|string|max:20',
            'passwordhash' => 'required|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
            'informacionadicional' => 'nullable|string',
            'rolid' => 'nullable|exists:roles,id',
        ]);

        $data['passwordhash'] = Hash::make($data['passwordhash']);
        $data['activo'] = true;

        $usuario = Usuario::create($data);

        if ($request->filled('rolid')) {
            $rol = Role::findById($request->rolid);
            if ($rol) {
                $usuario->assignRole($rol);
            }
        }

        return redirect()->route('gestion.show', $usuario)->with('success', 'Usuario creado correctamente.');
    }

    public function updateUsuario(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuario,email,'.$usuario->usuarioid.',usuarioid',
            'nombreusuario' => 'required|string|max:100|unique:usuario,nombreusuario,'.$usuario->usuarioid.',usuarioid',
            'telefono' => 'nullable|string|max:20',
            'passwordhash' => 'nullable|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
            'informacionadicional' => 'nullable|string',
            'rolid' => 'nullable|exists:roles,id',
        ]);

        unset($data['activo']);

        if ($request->filled('passwordhash')) {
            $data['passwordhash'] = Hash::make($data['passwordhash']);
        } else {
            unset($data['passwordhash']);
        }

        $usuario->update($data);

        if ($request->filled('rolid')) {
            $rol = Role::findById($request->rolid);
            if ($rol) {
                $usuario->syncRoles([$rol]);
            }
        } else {
            $usuario->syncRoles([]);
        }

        return redirect()->route('gestion.show', $usuario)->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroyUsuario(Usuario $usuario)
    {
        $usuario->delete();

        return redirect()->route('gestion.index')->with('success', 'Usuario eliminado correctamente.');
    }

    public function storeRol(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,name',
        ]);

        Role::create(['name' => $data['nombre']]);

        return redirect()->route('gestion.index')->with('success', 'Rol creado correctamente.');
    }

    public function updateRol(Request $request, Role $role)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->update(['name' => $data['nombre']]);

        return redirect()->route('gestion.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroyRol(Role $role)
    {
        $role->delete();

        return redirect()->route('gestion.index')->with('success', 'Rol eliminado correctamente.');
    }

    private function usuariosFilteredQuery(Request $request)
    {
        $query = Usuario::query()->with(['roles']);

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', $buscar)
                    ->orWhere('apellido', 'like', $buscar)
                    ->orWhere('email', 'like', $buscar)
                    ->orWhere('nombreusuario', 'like', $buscar)
                    ->orWhere('telefono', 'like', $buscar);
            });
        }

        if ($request->filled('rol')) {
            $query->whereHas('roles', fn ($q) => $q->where('id', (int) $request->rol));
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'activo') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $query->where('activo', false);
            }
        }

        return $query;
    }

    /** @return \Illuminate\Support\Collection<int, Role> */
    private function rolesCanonicos()
    {
        $nombres = array_keys(config('permission_matrix.role_permissions', []));

        return Role::query()
            ->whereIn('name', $nombres)
            ->orderBy('name')
            ->get();
    }
}
