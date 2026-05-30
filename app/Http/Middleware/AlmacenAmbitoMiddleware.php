<?php

namespace App\Http\Middleware;

use App\Support\AlmacenAmbito;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlmacenAmbitoMiddleware
{
    public function handle(Request $request, Closure $next, string $ambito): Response
    {
        if (! AlmacenAmbito::esValido($ambito)) {
            abort(404);
        }

        if (! AlmacenAmbito::usuarioPuedeVer($request->user(), $ambito)) {
            abort(403, 'No tienes acceso a este módulo de almacén.');
        }

        $request->route()?->setParameter('ambito', $ambito);

        return $next($request);
    }
}
