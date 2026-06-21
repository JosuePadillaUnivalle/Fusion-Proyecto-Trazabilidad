<?php

use App\Exceptions\EliminacionBloqueadaException;
use App\Support\EliminacionSegura;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'action.permission' => \App\Http\Middleware\ActionPermissionMiddleware::class,
            'almacen.ambito' => \App\Http\Middleware\AlmacenAmbitoMiddleware::class,
            'cuenta.aprobada' => \App\Http\Middleware\EnsureCuentaAprobada::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (EliminacionBloqueadaException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            if (! EliminacionSegura::esViolacionFk($e)) {
                return null;
            }

            $mensaje = EliminacionSegura::mensajeGenerico();

            if ($request->expectsJson()) {
                return response()->json(['message' => $mensaje], 422);
            }

            if ($request->isMethod('DELETE') || $request->isMethod('POST')) {
                return back()->with('error', $mensaje);
            }

            return null;
        });
    })->create();