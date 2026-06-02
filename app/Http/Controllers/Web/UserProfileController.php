<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\UsuarioAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $rules = [
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ];

        if (! $user->nombreusuario_editado) {
            $rules['nombreusuario'] = [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('usuario', 'nombreusuario')->ignore($user->usuarioid, 'usuarioid'),
            ];
        }

        $data = $request->validate(
            $rules,
            [
                'nombreusuario.regex' => 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
                'nombreusuario.unique' => 'Ese nombre de usuario ya está en uso.',
            ],
            [
                'nombreusuario' => 'nombre de usuario',
                'imagen' => 'foto de perfil',
            ]
        );

        if (! $user->nombreusuario_editado && isset($data['nombreusuario'])) {
            $nuevo = trim($data['nombreusuario']);
            if ($nuevo !== $user->nombreusuario) {
                $user->nombreusuario = $nuevo;
                $user->nombreusuario_editado = true;
            }
        }

        if ($request->hasFile('imagen')) {
            $user->imagenurl = UsuarioAvatar::storeUpload($user, $request->file('imagen'));
        } elseif (UsuarioAvatar::storageRelativePath($user->imagenurl) === null
            && str_contains((string) $user->imagenurl, 'supabase.co')) {
            $user->imagenurl = null;
        }

        $user->fechamodificacion = now();
        $user->save();

        return redirect()
            ->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function marcarBienvenidaVista(): RedirectResponse
    {
        $user = auth()->user();
        $user->bienvenida_vista = true;
        $user->save();

        return redirect()->back()->with('success', '¡Bienvenido a AgroFusion!');
    }
}
