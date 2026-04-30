<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GestionUsuariosController extends Controller
{
    // =========================================================
    // INDEX GLOBAL
    // =========================================================
    public function index()
    {
        $usuarios = Usuario::with(['roles'])->orderBy('usuarioid', 'desc')->paginate(15);
        $roles = Role::orderBy('name', 'asc')->get();
        $editarUsuario = null;
        $editarRol = null;
        if (request()->has('editarUsuario')) {
            $editarUsuario = Usuario::with('roles')->find(request('editarUsuario'));
        }

        if (request()->has('editarRol')) {
            $editarRol = Role::find(request('editarRol'));
        }

        return view('usuarios.index', compact('usuarios', 'roles', 'editarUsuario', 'editarRol'));
    }

    // =========================================================
    // USUARIOS CRUD
    // =========================================================

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
            'activo' => 'required|boolean',
            'rolid' => 'nullable|exists:roles,id'
        ]);

        // Hashear password
        $data['passwordhash'] = Hash::make($data['passwordhash']);

        $usuario = Usuario::create($data);

        // Asignar rol con Spatie
        if ($request->filled('rolid')) {
            $rol = Role::findById($request->rolid);
            if ($rol) {
                $usuario->assignRole($rol);
            }
        }

        return redirect()->route('gestion.index')->with('success', 'Usuario creado.');
    }

    public function updateUsuario(Request $request, Usuario $usuario)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuario,email,' . $usuario->usuarioid . ',usuarioid',
            'nombreusuario' => 'required|string|max:100|unique:usuario,nombreusuario,' . $usuario->usuarioid . ',usuarioid',
            'telefono' => 'nullable|string|max:20',
            'passwordhash' => 'nullable|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
            'informacionadicional' => 'nullable|string',
            'activo' => 'required|boolean',
            'rolid' => 'nullable|exists:roles,id'
        ]);

        // Si viene nueva contraseña, la hasheamos; si no, la quitamos del array
        if ($request->filled('passwordhash')) {
            $data['passwordhash'] = Hash::make($data['passwordhash']);
        } else {
            unset($data['passwordhash']);
        }

        $usuario->update($data);

        // Sincronizar rol con Spatie
        if ($request->filled('rolid')) {
            $rol = Role::findById($request->rolid);
            if ($rol) {
                $usuario->syncRoles([$rol]);
            }
        } else {
            $usuario->syncRoles([]);
        }

        return redirect()->route('gestion.index')->with('success', 'Usuario actualizado.');
    }

    public function destroyUsuario(Usuario $usuario)
    {
        $usuario->delete();

        return redirect()->route('gestion.index')->with('success', 'Usuario eliminado.');
    }

    // =========================================================
    // ROLES CRUD (Spatie)
    // =========================================================

    public function storeRol(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,name',
        ]);

        // Crear Rol Spatie
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
}