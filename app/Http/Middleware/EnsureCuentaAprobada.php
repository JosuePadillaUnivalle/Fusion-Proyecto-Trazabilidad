<?php

namespace App\Http\Middleware;

use App\Support\CuentaEstado;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCuentaAprobada
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $estado = $user->estado_cuenta ?? CuentaEstado::APROBADO;

        if (! CuentaEstado::puedeIniciarSesion($estado, (bool) $user->activo)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $mensaje = match ($estado) {
                CuentaEstado::PENDIENTE => 'Tu cuenta está pendiente de aprobación por un administrador.',
                default => 'Tu cuenta no está activa.',
            };

            return redirect()->route('login')->withErrors(['email' => $mensaje]);
        }

        return $next($request);
    }
}
