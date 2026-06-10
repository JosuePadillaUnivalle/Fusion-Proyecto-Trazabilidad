<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlantillaTransformacion;
use App\Support\ProcesoPlantaCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlantillaTransformacionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()?->hasRole('agricultor') || $request->user()?->hasRole('transportista')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $query = PlantillaTransformacion::query()
            ->withCount('pasos')
            ->with(['pasos.maquina']);

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', $buscar)
                    ->orWhere('producto_ejemplo', 'like', $buscar)
                    ->orWhere('descripcion', 'like', $buscar);
            });
        }

        if ($request->filled('estado')) {
            match ($request->estado) {
                'activo' => $query->operativas(),
                'mantenimiento' => $query->bloqueadasPorMantenimiento(),
                default => null,
            };
        }

        $stats = [
            'total' => PlantillaTransformacion::count(),
            'activas' => PlantillaTransformacion::query()->operativas()->count(),
            'inactivas' => PlantillaTransformacion::count() - PlantillaTransformacion::query()->operativas()->count(),
        ];

        $plantillas = $query->orderBy('nombre')->paginate(15)->withQueryString();

        return view('plantillas_transformacion.index', compact('plantillas', 'stats'));
    }

    public function create(): View
    {
        return view('plantillas_transformacion.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);
        $plantilla = PlantillaTransformacion::create($data);
        $this->syncPasos($plantilla, $request->input('pasos', []));

        return redirect()
            ->route('plantillas-transformacion.show', $plantilla)
            ->with('success', 'Plantilla de transformación creada.');
    }

    public function show(PlantillaTransformacion $plantillas_transformacion): View
    {
        $plantilla = $plantillas_transformacion;
        $plantilla->load(['pasos.proceso', 'pasos.maquina']);

        return view('plantillas_transformacion.show', compact('plantilla'));
    }

    public function edit(PlantillaTransformacion $plantillas_transformacion): View
    {
        $plantilla = $plantillas_transformacion;
        $plantilla->load(['pasos.proceso', 'pasos.maquina']);

        return view('plantillas_transformacion.edit', array_merge($this->formData(), compact('plantilla')));
    }

    public function update(Request $request, PlantillaTransformacion $plantillas_transformacion): RedirectResponse
    {
        $data = $this->validar($request);
        $plantillas_transformacion->update($data);
        $this->syncPasos($plantillas_transformacion, $request->input('pasos', []));

        return redirect()
            ->route('plantillas-transformacion.show', $plantillas_transformacion)
            ->with('success', 'Plantilla actualizada.');
    }

    public function destroy(PlantillaTransformacion $plantillas_transformacion): RedirectResponse
    {
        if ($plantillas_transformacion->lotes()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay lotes que usan esta plantilla.');
        }

        $plantillas_transformacion->delete();

        return redirect()
            ->route('plantillas-transformacion.index')
            ->with('success', 'Plantilla eliminada.');
    }

    /** @return array{procesos: \Illuminate\Support\Collection, mapaMaquinasProceso: array<int, list<array{id: int, nombre: string, codigo: ?string, activo: bool}>>, procesoCierreId: ?int} */
    private function formData(): array
    {
        return [
            'procesos' => ProcesoPlantaCatalogo::paraTransformacion(),
            'mapaMaquinasProceso' => \App\Support\MaquinaProcesoCompatibilidad::mapaMaquinasFormulario(),
            'procesoCierreId' => ProcesoPlantaCatalogo::idProcesoCierreTransformacion(),
        ];
    }

    /** @return array<string, mixed> */
    private function validar(Request $request): array
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'producto_ejemplo' => ['nullable', 'string', 'max:100'],
            'palabras_clave' => ['nullable', 'string', 'max:500'],
            'activo' => ['nullable', 'boolean'],
            'pasos' => ['required', 'array', 'min:1'],
            'pasos.*.procesoplantaid' => ['required', 'integer', 'exists:proceso_planta,procesoplantaid'],
            'pasos.*.maquinaplantaid' => ['nullable', 'integer', 'exists:maquina_planta,maquinaplantaid'],
            'pasos.*.notas' => ['nullable', 'string', 'max:255'],
        ]);

        $errorCierre = ProcesoPlantaCatalogo::errorSiUltimoPasoNoEsEmpaquetado($request->input('pasos', []));
        if ($errorCierre !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'pasos' => [$errorCierre],
            ]);
        }

        $claves = trim((string) $request->input('palabras_clave', ''));
        $clavesJson = null;
        if ($claves !== '') {
            $lista = array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $claves) ?: [])));
            $clavesJson = json_encode($lista, JSON_UNESCAPED_UNICODE);
        }

        return [
            'nombre' => $request->input('nombre'),
            'descripcion' => $request->input('descripcion'),
            'producto_ejemplo' => $request->input('producto_ejemplo'),
            'palabras_clave' => $clavesJson,
            'activo' => true,
        ];
    }

    /** @param  list<array{procesoplantaid?: mixed, maquinaplantaid?: mixed, notas?: mixed}>  $pasos */
    private function syncPasos(PlantillaTransformacion $plantilla, array $pasos): void
    {
        $plantilla->pasos()->delete();
        $orden = 1;

        foreach ($pasos as $paso) {
            if (empty($paso['procesoplantaid'])) {
                continue;
            }

            $plantilla->pasos()->create([
                'orden' => $orden++,
                'procesoplantaid' => (int) $paso['procesoplantaid'],
                'maquinaplantaid' => ! empty($paso['maquinaplantaid']) ? (int) $paso['maquinaplantaid'] : null,
                'notas' => $paso['notas'] ?? null,
            ]);
        }
    }
}
