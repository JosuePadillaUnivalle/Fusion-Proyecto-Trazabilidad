<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuerza que route(), url() y redirects usen el mismo host del navegador.
 * Evita perder sesión al alternar 127.0.0.1 ↔ IP LAN (cookies distintas por host).
 */
class SyncAppUrlWithRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() !== '') {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        }

        return $next($request);
    }
}
