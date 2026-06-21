<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Str;

class UsuarioUsernameService
{
    public function generarTemporalSolicitud(): string
    {
        do {
            $candidate = 'sol_'.Str::lower(Str::random(10));
        } while (Usuario::where('nombreusuario', $candidate)->exists());

        return $candidate;
    }

    public function generarDesdeNombreApellido(string $nombre, string $apellido): string
    {
        $inicial = Str::substr(Str::ascii(Str::lower(trim($nombre))), 0, 1);
        $apellidoSlug = Str::slug(Str::ascii(trim($apellido)), '');
        $base = Str::substr($inicial.$apellidoSlug, 0, 14);

        if ($base === '') {
            $base = 'agro';
        }

        for ($i = 0; $i < 20; $i++) {
            $candidate = $base.random_int(10, 9999);
            if (! Usuario::where('nombreusuario', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base.Str::lower(Str::random(4));
    }
}
