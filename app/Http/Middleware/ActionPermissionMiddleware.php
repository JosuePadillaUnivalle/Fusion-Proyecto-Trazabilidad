<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActionPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $permission = config("permission_matrix.modules.{$module}.{$action}");
        if (!$permission) {
            abort(403, "Permiso no definido para {$module}.{$action}");
        }

        if (!$user->can($permission)) {
            abort(403);
        }

        return $next($request);
    }
}

