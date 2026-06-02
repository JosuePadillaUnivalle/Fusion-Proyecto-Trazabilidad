<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\PerfilTransportista;
use App\Models\Usuario;
use App\Services\UsuarioEliminacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\UsuarioUsernameService;
use App\Support\CuentaEstado;
use App\Support\UsuarioRol;
use App\Support\UsuarioSolicitud;
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
            'activos' => Usuario::query()->where(function ($q) {
                $q->whereNull('estado_cuenta')->orWhere('estado_cuenta', CuentaEstado::APROBADO);
            })->count(),
            'pendientes' => Usuario::query()->where('estado_cuenta', CuentaEstado::PENDIENTE)->count(),
            'roles' => count($this->rolesCanonicos()),
        ];

        $usuarios = $query->orderByDesc('usuarioid')->paginate(15)->withQueryString();
        $roles = $this->rolesCanonicos();
        $lotes = Lote::query()->orderBy('nombre')->get(['loteid', 'nombre']);
        $loteSeleccionado = $request->filled('lote')
            ? $lotes->firstWhere('loteid', (int) $request->lote)
            : null;

        return view('usuarios.index', compact('usuarios', 'roles', 'stats', 'lotes', 'loteSeleccionado'));
    }

    public function create()
    {
        $roles = $this->rolesCanonicos();

        return view('usuarios.create', compact('roles'));
    }

    public function show(Usuario $usuario)
    {
        $usuario->load(['roles', 'almacen', 'perfilTransportista']);

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
        if (UsuarioSolicitud::adminSoloPuedeRevisar($usuario)) {
            return redirect()
                ->route('gestion.show', $usuario)
                ->with('error', 'Las solicitudes pendientes solo pueden revisarse (aprobar o rechazar), no editarse.');
        }

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
        $data['estado_cuenta'] = CuentaEstado::APROBADO;

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
        if (UsuarioSolicitud::adminSoloPuedeRevisar($usuario)) {
            abort(403, 'Las solicitudes pendientes no pueden editarse.');
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuario,email,'.$usuario->usuarioid.',usuarioid',
            'nombreusuario' => 'required|string|max:100|unique:usuario,nombreusuario,'.$usuario->usuarioid.',usuarioid',
            'telefono' => 'nullable|string|max:20',
            'imagenurl' => 'nullable|string|max:250',
            'informacionadicional' => 'nullable|string',
            'rolid' => 'nullable|exists:roles,id',
        ]);

        unset($data['activo']);

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
        if (UsuarioSolicitud::adminSoloPuedeRevisar($usuario)) {
            abort(403, 'Las solicitudes pendientes no pueden eliminarse. Usa Rechazar en el detalle.');
        }

        $eliminacion = app(UsuarioEliminacionService::class);

        if (! $eliminacion->puedeEliminar($usuario)) {
            return redirect()
                ->route('gestion.index')
                ->with('error', 'Este usuario es esencial del sistema y no puede eliminarse.');
        }

        try {
            $eliminacion->eliminar($usuario);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'No se pudo eliminar el usuario porque tiene datos vinculados. Contacta al administrador.');
        }

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
        $query = Usuario::query()->with(['roles'])->withCount('lotes');

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
                $query->where(function ($q) {
                    $q->whereNull('estado_cuenta')->orWhere('estado_cuenta', CuentaEstado::APROBADO);
                });
            } elseif ($request->estado === 'pendiente') {
                $query->where('estado_cuenta', CuentaEstado::PENDIENTE);
            }
        }

        if ($request->filled('lote')) {
            $loteId = (int) $request->lote;
            $query->whereHas('lotes', fn ($q) => $q->where('loteid', $loteId));
        }

        return $query;
    }

    public function aprobarSolicitud(Request $request, Usuario $usuario)
    {
        $this->authorizeAprobar($usuario);

        $rolNombre = $usuario->rol_solicitado;
        if (! $rolNombre || ! in_array($rolNombre, CuentaEstado::rolesRegistroPublico(), true)) {
            return back()->withErrors(['rol' => 'La solicitud no tiene un rol válido.']);
        }

        Role::firstOrCreate(['name' => $rolNombre, 'guard_name' => 'web']);

        $usernameService = app(UsuarioUsernameService::class);
        $nombreusuario = $usernameService->generarDesdeNombreApellido(
            (string) $usuario->nombre,
            (string) $usuario->apellido
        );

        $usuario->update([
            'estado_cuenta' => CuentaEstado::APROBADO,
            'activo' => true,
            'role' => $rolNombre,
            'nombreusuario' => $nombreusuario,
            'nombreusuario_editado' => false,
            'bienvenida_vista' => false,
            'motivo_rechazo' => null,
            'revisado_por' => auth()->id(),
            'fecha_revision' => now(),
            'fechamodificacion' => now(),
        ]);
        $usuario->syncRoles([$rolNombre]);

        if ($rolNombre === 'transportista') {
            $this->crearPerfilTransportistaDesdeSolicitud($usuario);
        }

        return redirect()->route('gestion.show', $usuario)->with(
            'success',
            'Solicitud aprobada. Se asignó el usuario «'.$nombreusuario.'»; el usuario ya puede iniciar sesión.'
        );
    }

    private function crearPerfilTransportistaDesdeSolicitud(Usuario $usuario): void
    {
        if (! Schema::hasTable('perfil_transportista')) {
            return;
        }

        $estadoId = null;
        if (Schema::hasTable('estado_transportista')) {
            $estadoId = DB::table('estado_transportista')
                ->where('nombre', 'disponible')
                ->value('estadotransportistaid');
        }

        PerfilTransportista::updateOrCreate(
            ['usuarioid' => $usuario->usuarioid],
            [
                'tipo_licencia' => $usuario->tipo_licencia,
                'licencia' => $usuario->ci_nit,
                'estadotransportistaid' => $estadoId,
                'disponible' => true,
            ]
        );
    }

    public function rechazarSolicitud(Request $request, Usuario $usuario)
    {
        $this->authorizeAprobar($usuario);

        $nombre = trim($usuario->nombre.' '.$usuario->apellido);
        app(UsuarioEliminacionService::class)->eliminar($usuario);

        return redirect()->route('gestion.index')->with(
            'success',
            'Solicitud de '.$nombre.' rechazada y eliminada del sistema.'
        );
    }

    private function authorizeAprobar(Usuario $usuario): void
    {
        if (($usuario->estado_cuenta ?? CuentaEstado::APROBADO) !== CuentaEstado::PENDIENTE) {
            abort(403, 'Esta solicitud ya fue procesada.');
        }

        if (! UsuarioRol::puedeAprobarSolicitud(auth()->user(), $usuario->rol_solicitado)) {
            abort(403, 'No tienes permiso para aprobar esta solicitud.');
        }
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
