<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\UsuarioRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Mostrar formulario de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Auth::attempt usará getAuthPassword() => passwordhash
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Guardar último login
            $user = Auth::user();
            $user->ultimologin = now();
            $user->save();

            return redirect()->route('dashboard')->with('success', 'Bienvenido.');
        }

        return back()
            ->withErrors(['email' => 'Credenciales inválidas.'])
            ->withInput($request->only('email'));
    }

    // Mostrar formulario de registro
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuario,email',
            'nombreusuario' => 'required|string|max:100|unique:usuario,nombreusuario',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $usuario = Usuario::create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'nombreusuario' => $data['nombreusuario'],
            'telefono' => $data['telefono'] ?? null,
            'passwordhash' => Hash::make($data['password']),
            'imagenurl' => 'https://bsmobatqfjmrfiipkimu.supabase.co/storage/v1/object/public/agronexus-bucket/usuarios/userDefault.png',
            'activo' => true,
            'fecharegistro' => now(),
        ]);

        // Asignar rol por defecto "agricultor"
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'agricultor']);
        $usuario->assignRole($role);

        // Loguear automáticamente al usuario
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Cuenta creada correctamente.');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}