<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\UsuarioUsernameService;
use App\Support\CuentaEstado;
use App\Support\RegistroValidacion;
use App\Support\TiposLicenciaBolivia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            return back()
                ->withErrors(['email' => 'Credenciales inválidas.'])
                ->withInput($request->only('email'));
        }

        $user = Auth::user();
        $estado = $user->estado_cuenta ?? CuentaEstado::APROBADO;

        if (! CuentaEstado::puedeIniciarSesion($estado, (bool) $user->activo)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $mensaje = match ($estado) {
                CuentaEstado::PENDIENTE => 'Tu cuenta está pendiente de aprobación por un administrador.',
                default => 'Tu cuenta no está activa.',
            };

            return back()->withErrors(['email' => $mensaje])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $user->ultimologin = now();
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Bienvenido.');
    }

    public function showRegisterForm()
    {
        return view('auth.register', [
            'rolesRegistro' => CuentaEstado::rolesRegistroPublico(),
            'prefijosTelefono' => config('telefono_prefijos', []),
            'tiposLicencia' => TiposLicenciaBolivia::todos(),
        ]);
    }

    public function register(Request $request)
    {
        $esTransportista = $request->input('rol_solicitado') === 'transportista';

        $request->merge([
            'nombre' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('nombre', ''))),
            'apellido' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('apellido', ''))),
            'telefono' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('telefono', ''))),
            'ci_nit' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('ci_nit', ''))),
        ]);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:'.RegistroValidacion::NOMBRE_APELLIDO],
            'apellido' => ['required', 'string', 'max:100', 'regex:'.RegistroValidacion::NOMBRE_APELLIDO],
            'email' => 'required|email|max:100|unique:usuario,email',
            'telefono' => ['required', 'string', 'max:20', 'regex:'.RegistroValidacion::TELEFONO],
            'ci_nit' => ['required', 'string', 'max:30', 'regex:'.RegistroValidacion::CI_NIT, 'unique:usuario,ci_nit'],
            'rol_solicitado' => ['required', Rule::in(CuentaEstado::rolesRegistroPublico())],
            'tipo_licencia' => [
                Rule::requiredIf($esTransportista),
                'nullable',
                'string',
                'max:20',
                Rule::in(TiposLicenciaBolivia::codigos()),
            ],
            'carta_motivacion' => 'required|string|min:30|max:2000',
            'password' => 'required|string|min:6|confirmed',
        ], array_merge(RegistroValidacion::mensajes(), [
            'tipo_licencia.required' => 'Indica el tipo de licencia de conducir.',
            'tipo_licencia.in' => 'Selecciona un tipo de licencia válido en Bolivia (M, P, A, B, C o T).',
        ]));

        $nombreusuario = app(UsuarioUsernameService::class)->generarTemporalSolicitud();

        Usuario::create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'nombreusuario' => $nombreusuario,
            'telefono' => $data['telefono'],
            'ci_nit' => $data['ci_nit'],
            'tipo_licencia' => $esTransportista ? $data['tipo_licencia'] : null,
            'carta_motivacion' => $data['carta_motivacion'],
            'rol_solicitado' => $data['rol_solicitado'],
            'passwordhash' => Hash::make($data['password']),
            'imagenurl' => 'https://bsmobatqfjmrfiipkimu.supabase.co/storage/v1/object/public/agronexus-bucket/usuarios/userDefault.png',
            'activo' => true,
            'estado_cuenta' => CuentaEstado::PENDIENTE,
            'fecharegistro' => now(),
        ]);

        return redirect()->route('register.enviado');
    }

    public function registroEnviado()
    {
        return view('auth.registro-enviado');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
