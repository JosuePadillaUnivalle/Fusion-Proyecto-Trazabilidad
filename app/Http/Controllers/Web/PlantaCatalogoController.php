<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\EliminacionBloqueadaException;
use App\Http\Controllers\Controller;
use App\Support\EliminacionSegura;
use App\Support\PlantaCatalogoRegistry;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PlantaCatalogoController extends Controller
{
    private const ROUTE_PREFIX = 'produccion-planta.catalogos';

    public function __construct()
    {
        $this->middleware(fn (Request $request, Closure $next) => $this->autorizar($request, $next, [
            'lote_produccion.view',
            'envios.view',
        ]))->only(['index']);

        $this->middleware(fn (Request $request, Closure $next) => $this->autorizar($request, $next, [
            'lote_produccion.create',
            'envios.create',
        ]))->only(['create', 'store']);

        $this->middleware(fn (Request $request, Closure $next) => $this->autorizar($request, $next, [
            'lote_produccion.update',
            'envios.update',
        ]))->only(['edit', 'update']);

        $this->middleware(fn (Request $request, Closure $next) => $this->autorizar($request, $next, [
            'lote_produccion.delete',
            'envios.delete',
        ]))->only(['destroy']);
    }

    public function index(string $tipo): View
    {
        $config = $this->config($tipo);
        $modelClass = $config['modelo'];
        $query = $modelClass::query();
        if (! empty($config['with'])) {
            $query->with($config['with']);
        }

        $registros = $query->orderBy($config['orden'])->paginate(20);

        return view('produccion-planta.catalogos.index', compact('tipo', 'config', 'registros'));
    }

    public function create(string $tipo): View
    {
        $config = $this->config($tipo);

        return view('produccion-planta.catalogos.form', [
            'tipo' => $tipo,
            'config' => $config,
            'registro' => null,
        ]);
    }

    public function store(Request $request, string $tipo): RedirectResponse
    {
        $config = $this->config($tipo);
        $data = $this->validar($request, $config);
        $config['modelo']::query()->create($data);

        return redirect()
            ->route(self::ROUTE_PREFIX.'.index', $tipo)
            ->with('success', $config['titulo'].' registrado correctamente.');
    }

    public function edit(string $tipo, int $id): View
    {
        $config = $this->config($tipo);
        $registro = $config['modelo']::query()->findOrFail($id);

        return view('produccion-planta.catalogos.form', compact('tipo', 'config', 'registro'));
    }

    public function update(Request $request, string $tipo, int $id): RedirectResponse
    {
        $config = $this->config($tipo);
        $registro = $config['modelo']::query()->findOrFail($id);
        $registro->update($this->validar($request, $config));

        return redirect()
            ->route(self::ROUTE_PREFIX.'.index', $tipo)
            ->with('success', 'Registro actualizado.');
    }

    public function destroy(string $tipo, int $id): RedirectResponse
    {
        $config = $this->config($tipo);
        $registro = $config['modelo']::query()->findOrFail($id);

        try {
            EliminacionSegura::ejecutar(fn () => $registro->delete());
        } catch (EliminacionBloqueadaException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route(self::ROUTE_PREFIX.'.index', $tipo)
            ->with('success', 'Registro eliminado.');
    }

    /** @param  list<string>  $permisos */
    private function autorizar(Request $request, Closure $next, array $permisos): Response
    {
        abort_unless($request->user()?->canany($permisos), 403);

        return $next($request);
    }

    /** @return array<string, mixed> */
    private function config(string $tipo): array
    {
        $config = PlantaCatalogoRegistry::get($tipo);
        abort_if($config === null, 404);

        return $config;
    }

    /** @param  array<string, mixed>  $config */
    private function validar(Request $request, array $config): array
    {
        $rules = [];
        foreach ($config['campos'] as $campo => $meta) {
            if (! empty($meta['no_persistir'])) {
                continue;
            }
            $rules[$campo] = $meta['rules'];
        }

        $data = $request->validate($rules);

        foreach ($config['campos'] as $campo => $meta) {
            if (($meta['tipo'] ?? '') === 'checkbox') {
                $data[$campo] = $request->boolean($campo);
            }
        }

        return $data;
    }
}
