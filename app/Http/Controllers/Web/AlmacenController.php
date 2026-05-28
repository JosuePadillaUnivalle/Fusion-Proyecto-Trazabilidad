<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\TipoAlmacen;
use App\Models\UnidadMedida;
use App\Services\UbicacionesAlmacenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AlmacenController extends Controller
{
    public function index()
    {
        $q = Almacen::query();

        $stats = [
            'total' => (clone $q)->count(),
            'capacidad_total' => (float) (clone $q)->sum('capacidad'),
            'activos' => (clone $q)->where('activo', true)->count(),
            'inactivos' => (clone $q)->where('activo', false)->count(),
            'tipos' => (clone $q)->distinct()->count('tipoalmacenid'),
        ];

        $tiposFiltro = (clone $q)
            ->join('tipoalmacen', 'almacen.tipoalmacenid', '=', 'tipoalmacen.tipoalmacenid')
            ->whereNotNull('almacen.tipoalmacenid')
            ->distinct()
            ->orderBy('tipoalmacen.nombre')
            ->pluck('tipoalmacen.nombre');

        $almacenes = Almacen::with(['tipoAlmacen', 'unidadMedida'])
            ->orderBy('almacenid', 'desc')
            ->paginate(15);

        return view('almacenes.index', compact('almacenes', 'stats', 'tiposFiltro'));
    }

    public function create()
    {
        $tipos    = TipoAlmacen::all();
        $unidades = UnidadMedida::all();

        return view('almacenes.create', $this->datosFormulario($tipos, $unidades));
    }

    public function selectorUbicacion(Request $request)
    {
        $excluirAlmacenId = $request->integer('excluir_almacen_id') ?: null;
        $ubicacionesGrupos = app(UbicacionesAlmacenService::class)
            ->listarParaFormulario($excluirAlmacenId);

        return view('almacenes.selector-ubicacion', [
            'ubicacionesGrupos' => $ubicacionesGrupos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarAlmacen($request);

        Almacen::create($data);

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén creado.');
    }

    public function show(Almacen $almacen)
    {
        $almacen->load(['tipoAlmacen', 'unidadMedida', 'almacenamientos']);

        return view('almacenes.show', compact('almacen'));
    }

    public function edit(Almacen $almacen)
    {
        $tipos    = TipoAlmacen::all();
        $unidades = UnidadMedida::all();

        return view('almacenes.edit', $this->datosFormulario($tipos, $unidades, $almacen));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $almacen->update($this->validarAlmacen($request, $almacen));

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén actualizado.');
    }

    public function destroy(Almacen $almacen)
    {
        $almacen->delete();

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function datosFormulario($tipos, $unidades, ?Almacen $almacen = null): array
    {
        $ubicacionesGrupos = app(UbicacionesAlmacenService::class)
            ->listarParaFormulario($almacen?->almacenid);

        $ubicacionValor = trim((string) old('ubicacion', $almacen->ubicacion ?? ''));
        $ubicacionEnCatalogo = false;
        foreach ($ubicacionesGrupos as $grupo) {
            foreach ($grupo['items'] as $item) {
                if (strcasecmp($ubicacionValor, $item['valor']) === 0) {
                    $ubicacionEnCatalogo = true;
                    break 2;
                }
            }
        }

        $tiposConfig = config('almacenes.tipos', []);
        $tiposAyuda = [];
        foreach ($tipos as $tipo) {
            if (isset($tiposConfig[$tipo->nombre])) {
                $tiposAyuda[$tipo->nombre] = $tiposConfig[$tipo->nombre];
            }
        }

        return [
            'tipos' => $tipos,
            'unidades' => $unidades,
            'almacen' => $almacen,
            'guias' => config('almacenes', []),
            'ubicacionesGrupos' => $ubicacionesGrupos,
            'ubicacionEnCatalogo' => $ubicacionEnCatalogo,
            'tiposAyuda' => $tiposAyuda,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validarAlmacen(Request $request, ?Almacen $almacen = null): array
    {
        $reglas = [
            'nombre'         => 'required|string|max:100|unique:almacen,nombre'.($almacen ? ','.$almacen->almacenid.',almacenid' : ''),
            'descripcion'    => 'nullable|string|max:250',
            'ubicacion'      => 'nullable|string|max:200',
            'capacidad'      => 'nullable|numeric|min:0',
            'unidadmedidaid' => 'nullable|exists:unidadmedida,unidadmedidaid',
            'tipoalmacenid'  => 'nullable|exists:tipoalmacen,tipoalmacenid',
            'activo'         => 'boolean',
        ];

        if (Schema::hasColumn('almacen', 'direccionlogisticaid')) {
            $reglas['direccionlogisticaid'] = 'nullable|exists:direccion_logistica,direccionlogisticaid';
        }

        $data = $request->validate($reglas);

        if (! isset($data['activo'])) {
            unset($data['activo']);
        }

        if (! Schema::hasColumn('almacen', 'direccionlogisticaid')) {
            unset($data['direccionlogisticaid']);
        } elseif (empty($data['direccionlogisticaid'])) {
            $data['direccionlogisticaid'] = null;
        }

        return $data;
    }
}