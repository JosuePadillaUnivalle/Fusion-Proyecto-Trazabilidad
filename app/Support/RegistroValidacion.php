<?php

namespace App\Support;

class RegistroValidacion
{
    /** Solo letras (incluye acentos) y espacios entre palabras. */
    public const NOMBRE_APELLIDO = '/^[\p{L}]+(?:\s[\p{L}]+)*$/u';

    /** Prefijo + dígitos y número local (solo números, un espacio entre ambos). */
    public const TELEFONO = '/^\+[0-9]{1,5} [0-9]{6,15}$/';

    /** Letras, números y espacios (ej. 1234567 LP). */
    public const CI_NIT = '/^[\p{L}\d]+(?:\s+[\p{L}\d]+)*$/u';

    public static function mensajes(): array
    {
        return [
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'apellido.regex' => 'El apellido solo puede contener letras.',
            'telefono.regex' => 'El teléfono solo puede contener números y el prefijo con +.',
            'ci_nit.regex' => 'El CI/NIT solo puede contener letras y números.',
        ];
    }
}
