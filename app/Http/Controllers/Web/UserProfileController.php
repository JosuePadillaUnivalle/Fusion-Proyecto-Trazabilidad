<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\UsuarioAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $avatarUrl = UsuarioAvatar::resolve($user);

        return view('profile.show', compact('user', 'avatarUrl'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate(
            [
                'nombre' => 'required|string|max:100',
                'apellido' => 'required|string|max:100',
                'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')->ignore($user->usuarioid, 'usuarioid')],
                'telefono' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:6|confirmed',
                'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
            ],
            [
                'password.confirmed' => 'La confirmación de la contraseña no coincide.',
                'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            ],
            [
                'nombre' => 'nombre',
                'apellido' => 'apellido',
                'email' => 'correo electrónico',
                'telefono' => 'teléfono',
                'password' => 'contraseña',
                'password_confirmation' => 'confirmación de la contraseña',
                'imagen' => 'foto de perfil',
            ]
        );

        $user->nombre = $data['nombre'];
        $user->apellido = $data['apellido'];
        $user->email = $data['email'];
        $user->telefono = $data['telefono'];

        if ($request->hasFile('imagen')) {
            $user->imagenurl = UsuarioAvatar::storeUpload($user, $request->file('imagen'));
        } elseif (UsuarioAvatar::storageRelativePath($user->imagenurl) === null
            && str_contains((string) $user->imagenurl, 'supabase.co')) {
            $user->imagenurl = null;
        }

        if ($request->filled('password')) {
            $user->passwordhash = Hash::make($data['password']);
        }

        $user->fechamodificacion = now();
        $user->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
