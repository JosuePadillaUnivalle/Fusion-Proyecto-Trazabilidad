<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProduccionAlmacenamiento;
use App\Models\Produccion;
use App\Models\Almacen;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class ProduccionAlmacenamientoController extends Controller
{
    public function index()
    {
        $q = ProduccionAlmacenamiento::query();

        $stats = [
            'total' => (clone $q)->count(),
            'almacenes' => (clone $q)->distinct()->count('almacenid'),
            'producciones' => (clone $q)->distinct()->count('produccionid'),
            'cantidad_total' => (float) (clone $q)->sum('cantidad'),
            'temp_alta' => (clone $q)->where('temperatura', '>', 25)->count(),
        ];

        $almacenesFiltro = (clone $q)
            ->join('almacen', 'produccionalmacenamiento.almacenid', '=', 'almacen.almacenid')
            ->distinct()
            ->orderBy('almacen.nombre')
            ->pluck('almacen.nombre');

        $unidadesFiltro = (clone $q)
            ->join('unidadmedida', 'produccionalmacenamiento.unidadmedidaid', '=', 'unidadmedida.unidadmedidaid')
            ->whereNotNull('produccionalmacenamiento.unidadmedidaid')
            ->distinct()
            ->orderBy('unidadmedida.nombre')
            ->pluck('unidadmedida.nombre');

        $registros = ProduccionAlmacenamiento::with(['produccion.lote', 'almacen', 'unidadMedida'])
            ->orderByDesc('fechaentrada')
            ->orderByDesc('produccionalmacenamientoid')
            ->paginate(15);

        return view('producciones_almacenamiento.index', compact(
            'registros',
            'stats',
            'almacenesFiltro',
            'unidadesFiltro'
        ));
    }

    public function create(Request $request)
    {
        $loteidParam = $request->integer('loteid') ?: null;

        $produccionesQuery = Produccion::with('lote')->orderBy('produccionid', 'desc');
        if ($loteidParam) {
            $produccionesQuery->where('loteid', $loteidParam);
        }
        $producciones = $produccionesQuery->get();

        $almacenes    = Almacen::orderBy('nombre')->get();
        $unidades     = UnidadMedida::all();

        $produccionPreseleccionada = $producciones->first()?->produccionid;
        $returnUrl = $this->validReturnUrl($request->input('return'));

        return view('producciones_almacenamiento.create', compact(
            'producciones',
            'almacenes',
            'unidades',
            'produccionPreseleccionada',
            'returnUrl'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'produccionid'    => 'required|exists:produccion,produccionid',
            'almacenid'       => 'required|exists:almacen,almacenid',
            'cantidad'        => 'required|numeric|min:0.01',
            'unidadmedidaid'  => 'nullable|exists:unidadmedida,unidadmedidaid',

            'temperatura'     => 'nullable|numeric|between:-50,80',
            'humedad'         => 'nullable|numeric|between:0,100',
            'temperatura_min' => 'nullable|numeric|between:-50,80',
            'temperatura_max' => 'nullable|numeric|between:-50,80',
            'humedad_min'     => 'nullable|numeric|between:0,100',
            'humedad_max'     => 'nullable|numeric|between:0,100',

            'fechaentrada'    => 'nullable|date',
            'fechasalida'     => 'nullable|date|after_or_equal:fechaentrada',
            'observaciones'   => 'nullable|string|max:250',
        ]);

        ProduccionAlmacenamiento::create($data);

        $returnUrl = $this->validReturnUrl($request->input('return'));
        if ($returnUrl) {
            return redirect($returnUrl)->with('success', 'Registro de almacenamiento creado.');
        }

        return redirect()
            ->route('producciones_almacenamiento.index')
            ->with('success', 'Registro de almacenamiento creado.');
    }

    public function show(ProduccionAlmacenamiento $producciones_almacenamiento)
    {
        $producciones_almacenamiento->load(['produccion', 'almacen', 'unidadMedida']);

        // Ojo: nombre de variable para no chocar con el nombre de ruta
        $registro = $producciones_almacenamiento;

        return view('producciones_almacenamiento.show', compact('registro'));
    }

    public function edit(ProduccionAlmacenamiento $producciones_almacenamiento)
    {
        $registro     = $producciones_almacenamiento;
        $producciones = Produccion::with('lote')->orderBy('produccionid', 'desc')->get();
        $almacenes    = Almacen::orderBy('nombre')->get();
        $unidades     = UnidadMedida::all();

        return view('producciones_almacenamiento.edit', compact('registro', 'producciones', 'almacenes', 'unidades'));
    }

    public function update(Request $request, ProduccionAlmacenamiento $producciones_almacenamiento)
    {
        $data = $request->validate([
            'produccionid'    => 'required|exists:produccion,produccionid',
            'almacenid'       => 'required|exists:almacen,almacenid',
            'cantidad'        => 'required|numeric|min:0.01',
            'unidadmedidaid'  => 'nullable|exists:unidadmedida,unidadmedidaid',

            'temperatura'     => 'nullable|numeric|between:-50,80',
            'humedad'         => 'nullable|numeric|between:0,100',
            'temperatura_min' => 'nullable|numeric|between:-50,80',
            'temperatura_max' => 'nullable|numeric|between:-50,80',
            'humedad_min'     => 'nullable|numeric|between:0,100',
            'humedad_max'     => 'nullable|numeric|between:0,100',

            'fechaentrada'    => 'nullable|date',
            'fechasalida'     => 'nullable|date|after_or_equal:fechaentrada',
            'observaciones'   => 'nullable|string|max:250',
        ]);

        $producciones_almacenamiento->update($data);

        return redirect()
            ->route('producciones_almacenamiento.index')
            ->with('success', 'Registro de almacenamiento actualizado.');
    }

    public function destroy(ProduccionAlmacenamiento $producciones_almacenamiento)
    {
        $producciones_almacenamiento->delete();

        return redirect()
            ->route('producciones_almacenamiento.index')
            ->with('success', 'Registro de almacenamiento eliminado.');
    }

    private function validReturnUrl(mixed $return): ?string
    {
        if (! is_string($return) || trim($return) === '') {
            return null;
        }

        $return = trim($return);
        $appUrl = rtrim((string) config('app.url'), '/');
        if (! str_starts_with($return, '/') && ! str_starts_with($return, $appUrl)) {
            return null;
        }

        return $return;
    }
}