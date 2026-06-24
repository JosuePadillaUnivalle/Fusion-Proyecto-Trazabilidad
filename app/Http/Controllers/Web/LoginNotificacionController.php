<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LoginNotificacionAlcance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LoginNotificacionController extends Controller
{
    public function descartar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alcance' => ['required', 'string', Rule::in(LoginNotificacionAlcance::todos())],
            'claves' => ['required', 'array', 'min:1'],
            'claves.*' => ['required', 'string', 'max:120'],
        ]);

        LoginNotificacionAlcance::marcarVistas(
            $data['alcance'],
            (int) $request->user()->usuarioid,
            $data['claves']
        );

        return response()->json(['ok' => true]);
    }
}
