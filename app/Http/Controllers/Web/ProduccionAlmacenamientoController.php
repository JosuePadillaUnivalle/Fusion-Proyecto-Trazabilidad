<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProduccionAlmacenamiento;
use App\Models\Produccion;
use App\Models\Almacen;
use App\Models\UnidadMedida;
use Carbon\Carbon;
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

    public function create()
    {
        $producciones = Produccion::with(['lote', 'unidadMedida'])->orderBy('produccionid', 'desc')->get();
        $almacenes    = Almacen::with('tipoAlmacen')->orderBy('nombre')->get();
        $unidades     = UnidadMedida::all();
        $sugerenciasPorProduccion = $this->sugerenciasPorProduccion($producciones);
        $sugerenciasPorAlmacen = $this->sugerenciasPorAlmacen($almacenes);

        return view('producciones_almacenamiento.create', compact(
            'producciones',
            'almacenes',
            'unidades',
            'sugerenciasPorProduccion',
            'sugerenciasPorAlmacen'
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

        $produccion = Produccion::query()->findOrFail((int) $data['produccionid']);
        if (empty($data['unidadmedidaid']) && $produccion->unidadmedidaid) {
            $data['unidadmedidaid'] = $produccion->unidadmedidaid;
        }
        if (empty($data['fechaentrada'])) {
            $data['fechaentrada'] = Carbon::now()->toDateTimeString();
        }

        ProduccionAlmacenamiento::create($data);

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
        $producciones = Produccion::with(['lote', 'unidadMedida'])->orderBy('produccionid', 'desc')->get();
        $almacenes    = Almacen::with('tipoAlmacen')->orderBy('nombre')->get();
        $unidades     = UnidadMedida::all();
        $sugerenciasPorProduccion = $this->sugerenciasPorProduccion($producciones);
        $sugerenciasPorAlmacen = $this->sugerenciasPorAlmacen($almacenes);

        return view('producciones_almacenamiento.edit', compact(
            'registro',
            'producciones',
            'almacenes',
            'unidades',
            'sugerenciasPorProduccion',
            'sugerenciasPorAlmacen'
        ));
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

        $produccion = Produccion::query()->findOrFail((int) $data['produccionid']);
        if (empty($data['unidadmedidaid']) && $produccion->unidadmedidaid) {
            $data['unidadmedidaid'] = $produccion->unidadmedidaid;
        }

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

    private function sugerenciasPorProduccion($producciones): array
    {
        $out = [];
        foreach ($producciones as $p) {
            $loteNombre = strtolower((string) optional($p->lote)->nombre);
            $ubicacion = strtolower((string) optional($p->lote)->ubicacion);
            $cultivo = strtolower((string) optional(optional($p->lote)->cultivo)->nombre);
            $texto = trim($loteNombre.' '.$ubicacion.' '.$cultivo);

            // Base templada
            $s = [
                'temperatura' => 18.0,
                'humedad' => 60.0,
                'temperatura_min' => 12.0,
                'temperatura_max' => 24.0,
                'humedad_min' => 50.0,
                'humedad_max' => 70.0,
                'origen' => 'Sugerencia por lote/cultivo',
            ];

            if (str_contains($texto, 'hoja') || str_contains($texto, 'lechuga') || str_contains($texto, 'verdura')) {
                $s = [
                    'temperatura' => 5.0,
                    'humedad' => 92.0,
                    'temperatura_min' => 2.0,
                    'temperatura_max' => 8.0,
                    'humedad_min' => 85.0,
                    'humedad_max' => 98.0,
                    'origen' => 'Sugerencia por lote fresco (hojas/verduras)',
                ];
            } elseif (str_contains($texto, 'tomate')) {
                $s = [
                    'temperatura' => 12.0,
                    'humedad' => 88.0,
                    'temperatura_min' => 10.0,
                    'temperatura_max' => 15.0,
                    'humedad_min' => 85.0,
                    'humedad_max' => 95.0,
                    'origen' => 'Sugerencia por lote de tomate',
                ];
            } elseif (str_contains($texto, 'papa') || str_contains($texto, 'cebolla') || str_contains($texto, 'maiz')) {
                $s = [
                    'temperatura' => 10.0,
                    'humedad' => 70.0,
                    'temperatura_min' => 7.0,
                    'temperatura_max' => 13.0,
                    'humedad_min' => 60.0,
                    'humedad_max' => 80.0,
                    'origen' => 'Sugerencia por lote de tubérculo/grano',
                ];
            }

            $out[(int) $p->produccionid] = $s;
        }

        return $out;
    }

    private function sugerenciasPorAlmacen($almacenes): array
    {
        $out = [];
        foreach ($almacenes as $a) {
            $tipo = strtolower((string) optional($a->tipoAlmacen)->nombre);
            $nombre = strtolower((string) $a->nombre);
            $texto = trim($tipo.' '.$nombre);

            $s = [
                'temperatura' => 20.0,
                'humedad' => 60.0,
                'temperatura_min' => 15.0,
                'temperatura_max' => 25.0,
                'humedad_min' => 50.0,
                'humedad_max' => 70.0,
                'origen' => 'Sugerencia por almacén',
            ];

            if (str_contains($texto, 'planta') || str_contains($texto, 'frio') || str_contains($texto, 'frío')) {
                $s = [
                    'temperatura' => 8.0,
                    'humedad' => 85.0,
                    'temperatura_min' => 4.0,
                    'temperatura_max' => 12.0,
                    'humedad_min' => 75.0,
                    'humedad_max' => 95.0,
                    'origen' => 'Sugerencia por almacén tipo planta/frío',
                ];
            } elseif (str_contains($texto, 'central')) {
                $s = [
                    'temperatura' => 18.0,
                    'humedad' => 60.0,
                    'temperatura_min' => 14.0,
                    'temperatura_max' => 24.0,
                    'humedad_min' => 50.0,
                    'humedad_max' => 70.0,
                    'origen' => 'Sugerencia por almacén central',
                ];
            }

            $out[(int) $a->almacenid] = $s;
        }

        return $out;
    }
}