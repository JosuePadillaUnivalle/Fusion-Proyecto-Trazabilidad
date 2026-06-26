<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Support\EliminacionSegura;
use App\Support\LogisticaCatalogoAcceso;
use App\Support\LogisticaCatalogoRegistry;
use App\Exceptions\EliminacionBloqueadaException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnvioCatalogoController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $tipo = $request->route('tipo');
            $user = $request->user();
            $action = $request->route()?->getActionMethod();

            if ($action === 'index') {
                abort_unless(LogisticaCatalogoAcceso::puedeVer($user), 403);
            } elseif (in_array($action, ['create', 'store'], true)) {
                abort_unless(is_string($tipo) && LogisticaCatalogoAcceso::puedeCrear($user, $tipo), 403);
            } elseif (in_array($action, ['edit', 'update'], true)) {
                abort_unless(is_string($tipo) && LogisticaCatalogoAcceso::puedeEditar($user, $tipo), 403);
            } elseif ($action === 'destroy') {
                abort_unless(is_string($tipo) && LogisticaCatalogoAcceso::puedeEliminar($user, $tipo), 403);
            }

            return $next($request);
        });
    }

    public function index(string $tipo): View
    {
        $config = $this->config($tipo);
        $modelClass = $config['modelo'];
        $query = $modelClass::query();
        if (! empty($config['with'])) {
            $query->with($config['with']);
        }
        if (! empty($config['scope']) && is_callable($config['scope'])) {
            ($config['scope'])($query);
        }

        $registros = $query->orderBy($config['orden'])->paginate(20);

        return view('envios.catalogos.index', compact('tipo', 'config', 'registros'));
    }

    public function create(string $tipo): View
    {
        $config = $this->config($tipo);
        abort_if(! empty($config['solo_edicion']), 404);

        return view('envios.catalogos.form', [
            'tipo' => $tipo,
            'config' => $config,
            'registro' => null,
        ]);
    }

    public function store(Request $request, string $tipo): RedirectResponse
    {
        $config = $this->config($tipo);
        abort_if(! empty($config['solo_edicion']), 404);
        $data = $this->validar($request, $config);
        $data = array_merge($config['defaults'] ?? [], $data);
        $registro = $config['modelo']::query()->create($data);
        $this->sincronizarRelaciones($request, $config, $registro);

        return redirect()
            ->route('envios.catalogos.index', $tipo)
            ->with('success', $config['titulo'].' registrado correctamente.');
    }

    public function edit(string $tipo, int $id): View
    {
        $config = $this->config($tipo);
        $query = $config['modelo']::query();
        if (! empty($config['with'])) {
            $query->with($config['with']);
        }
        if (! empty($config['scope']) && is_callable($config['scope'])) {
            ($config['scope'])($query);
        }
        $registro = $query->findOrFail($id);

        return view('envios.catalogos.form', compact('tipo', 'config', 'registro'));
    }

    public function update(Request $request, string $tipo, int $id): RedirectResponse
    {
        $config = $this->config($tipo);
        $registro = $config['modelo']::query()->findOrFail($id);
        $data = $this->validar($request, $config);
        $registro->update($data);
        $this->sincronizarRelaciones($request, $config, $registro);

        return redirect()
            ->route('envios.catalogos.index', $tipo)
            ->with('success', 'Registro actualizado.');
    }

    public function destroy(string $tipo, int $id): RedirectResponse
    {
        $config = $this->config($tipo);
        abort_if(! empty($config['solo_edicion']), 404);
        $registro = $config['modelo']::query()->findOrFail($id);

        try {
            EliminacionSegura::ejecutar(fn () => $registro->delete());
        } catch (EliminacionBloqueadaException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('envios.catalogos.index', $tipo)
            ->with('success', 'Registro eliminado.');
    }

    /** @return array<string, mixed> */
    private function config(string $tipo): array
    {
        $config = LogisticaCatalogoRegistry::get($tipo);
        abort_if($config === null, 404);

        return $config;
    }

    /** @param  array<string, mixed>  $config */
    private function validar(Request $request, array $config): array
    {
        $rules = [];
        foreach ($config['campos'] as $campo => $meta) {
            if (! empty($meta['no_persistir'])) {
                $rules[$campo] = $meta['rules'];
                if (! empty($meta['rules_item'])) {
                    $rules[$campo.'.*'] = $meta['rules_item'];
                }

                continue;
            }
            $rules[$campo] = $meta['rules'];
        }

        $data = $request->validate($rules);

        foreach ($config['campos'] as $campo => $meta) {
            if (($meta['tipo'] ?? '') === 'checkbox') {
                $data[$campo] = $request->boolean($campo);
            }
            if (! empty($meta['no_persistir'])) {
                unset($data[$campo]);
            }
        }

        return $data;
    }

    /** @param  array<string, mixed>  $config */
    private function sincronizarRelaciones(Request $request, array $config, object $registro): void
    {
        if (empty($config['sync'])) {
            return;
        }

        foreach ($config['sync'] as $relacion => $campoRequest) {
            if (! method_exists($registro, $relacion)) {
                continue;
            }

            $valor = $request->input($campoRequest);
            $ids = [];
            if (is_array($valor)) {
                $ids = collect($valor)->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->values()->all();
            } elseif (filled($valor)) {
                $ids = [(int) $valor];
            }

            $registro->{$relacion}()->sync($ids);
        }
    }
}
