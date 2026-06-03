<?php



namespace App\Http\Controllers\Web;



use App\Http\Controllers\Controller;

use App\Models\Almacen;

use App\Models\AlmacenajeLoteProduccion;

use App\Models\Insumo;

use App\Models\ProduccionAlmacenamiento;

use App\Models\TipoAlmacen;

use App\Models\UnidadMedida;

use App\Services\AlmacenCapacidadService;

use App\Services\UbicacionesAlmacenService;

use App\Support\AlmacenAmbito;

use App\Support\InsumoCatalogo;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Schema;



class AlmacenController extends Controller

{

    public function __construct(

        private readonly AlmacenCapacidadService $capacidadService,

    ) {}



    public function index(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $ambito = $ctx['ambito'];

        $q = AlmacenAmbito::scope(Almacen::query(), $ambito);



        $almacenesPagina = AlmacenAmbito::scope(

            Almacen::with(['unidadMedida']),

            $ambito

        )

            ->orderBy('almacenid', 'desc')

            ->paginate(15);



        $ocupacionPorId = [];

        $capacidadTotalKg = 0;

        $ocupadoTotalKg = 0;



        foreach ($almacenesPagina as $almacen) {

            $resumen = $this->capacidadService->resumen($almacen);

            $ocupacionPorId[$almacen->almacenid] = $resumen;

            $capacidadTotalKg += $resumen['capacidad_kg'];

            $ocupadoTotalKg += $resumen['ocupado_kg'];

        }



        $stats = [

            'total' => (clone $q)->count(),

            'capacidad_total' => $capacidadTotalKg,

            'ocupado_total' => $ocupadoTotalKg,

            'ocupacion_promedio' => $capacidadTotalKg > 0

                ? round(($ocupadoTotalKg / $capacidadTotalKg) * 100, 1)

                : 0,

        ];



        return view('almacenes.index', array_merge(compact('almacenesPagina', 'stats', 'ocupacionPorId'), $ctx, [

            'almacenes' => $almacenesPagina,

        ]));

    }



    public function create(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);



        return view('almacenes.create', array_merge(

            $this->datosFormulario(null),

            $ctx

        ));

    }



    public function selectorUbicacion(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $excluirAlmacenId = $request->integer('excluir_almacen_id') ?: null;

        $ubicacionesGrupos = app(UbicacionesAlmacenService::class)

            ->listarParaFormulario($excluirAlmacenId);



        return view('almacenes.selector-ubicacion', array_merge([

            'ubicacionesGrupos' => $ubicacionesGrupos,

        ], $ctx));

    }



    public function store(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $data = $this->validarAlmacen($request);

        $data['ambito'] = $ctx['ambito'];

        $data['activo'] = true;

        $data['unidadmedidaid'] = $this->unidadKilogramoId();

        $data['tipoalmacenid'] = $this->tipoAlmacenPorDefecto();



        Almacen::create($data);



        return redirect()

            ->route($ctx['rutaPrefijo'].'.index')

            ->with('success', 'Almacén creado.');

    }



    public function show(Request $request, Almacen $almacen)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $this->asegurarAmbitoAlmacen($almacen, $ctx['ambito']);

        $almacen->load(['unidadMedida', 'almacenamientos']);

        $resumenCapacidad = $this->capacidadService->resumen($almacen);

        $contenidos = $this->contenidoAlmacen($almacen);

        $tiposContenidoFiltro = $contenidos->pluck('tipo_label')->unique()->sort()->values();



        return view('almacenes.show', array_merge(compact(
            'almacen',
            'resumenCapacidad',
            'contenidos',
            'tiposContenidoFiltro'
        ), $ctx));

    }



    public function edit(Request $request, Almacen $almacen)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $this->asegurarAmbitoAlmacen($almacen, $ctx['ambito']);



        return view('almacenes.edit', array_merge(

            $this->datosFormulario($almacen),

            $ctx,

            ['almacen' => $almacen]

        ));

    }



    public function update(Request $request, Almacen $almacen)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $this->asegurarAmbitoAlmacen($almacen, $ctx['ambito']);



        $data = $this->validarAlmacen($request, $almacen);

        $data['ambito'] = $ctx['ambito'];

        $data['activo'] = true;

        $data['unidadmedidaid'] = $this->unidadKilogramoId();

        if (! $almacen->tipoalmacenid) {
            $data['tipoalmacenid'] = $this->tipoAlmacenPorDefecto();
        } else {
            unset($data['tipoalmacenid']);
        }



        $almacen->update($data);



        return redirect()

            ->route($ctx['rutaPrefijo'].'.index')

            ->with('success', 'Almacén actualizado.');

    }



    public function destroy(Request $request, Almacen $almacen)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $this->asegurarAmbitoAlmacen($almacen, $ctx['ambito']);

        $almacen->delete();



        return redirect()

            ->route($ctx['rutaPrefijo'].'.index')

            ->with('success', 'Almacén eliminado.');

    }



    private function asegurarAmbitoAlmacen(Almacen $almacen, string $ambito): void

    {

        if (Schema::hasColumn('almacen', 'ambito') && $almacen->ambito !== $ambito) {

            abort(404);

        }

    }



    /**

     * @return array<string, mixed>

     */

    private function datosFormulario(?Almacen $almacen = null): array

    {

        return [

            'almacen' => $almacen,

            'guias' => config('almacenes', []),

        ];

    }



    /**

     * @return array<string, mixed>

     */

    private function validarAlmacen(Request $request, ?Almacen $almacen = null): array

    {

        $reglas = [

            'nombre' => 'required|string|max:100|unique:almacen,nombre'.($almacen ? ','.$almacen->almacenid.',almacenid' : ''),

            'descripcion' => 'nullable|string|max:250',

            'ubicacion' => 'nullable|string|max:200',

            'capacidad' => 'nullable|numeric|min:0',

        ];



        if (Schema::hasColumn('almacen', 'direccionlogisticaid')) {

            $reglas['direccionlogisticaid'] = 'nullable|exists:direccion_logistica,direccionlogisticaid';

        }



        $data = $request->validate($reglas);



        if (! Schema::hasColumn('almacen', 'direccionlogisticaid')) {

            unset($data['direccionlogisticaid']);

        } elseif (empty($data['direccionlogisticaid'])) {

            $data['direccionlogisticaid'] = null;

        }



        return $data;

    }



    private function unidadKilogramoId(): ?int

    {

        $id = UnidadMedida::query()

            ->where(function ($q) {

                $q->whereRaw('LOWER(abreviatura) = ?', ['kg'])

                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%']);

            })

            ->value('unidadmedidaid');



        return $id ? (int) $id : null;

    }



    private function tipoAlmacenPorDefecto(): ?int

    {

        $id = TipoAlmacen::query()

            ->whereIn('nombre', ['Central', 'Secundario', 'Planta'])

            ->orderByRaw("CASE nombre WHEN 'Central' THEN 1 WHEN 'Secundario' THEN 2 ELSE 3 END")

            ->value('tipoalmacenid');



        if ($id) {

            return (int) $id;

        }



        $fallback = TipoAlmacen::query()->orderBy('tipoalmacenid')->value('tipoalmacenid');



        return $fallback ? (int) $fallback : null;

    }



    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function contenidoAlmacen(Almacen $almacen): \Illuminate\Support\Collection
    {
        $items = collect();

        $insumos = Insumo::query()
            ->with(['tipo', 'unidadMedida'])
            ->where('almacenid', $almacen->almacenid)
            ->orderBy('nombre')
            ->get();

        foreach ($insumos as $insumo) {
            if ((float) $insumo->stock <= 0) {
                continue;
            }

            $tipoNombre = $insumo->tipo?->nombre ?? 'Insumo';
            $slug = InsumoCatalogo::slugFromNombreTipo($tipoNombre) ?? 'insumo';
            $kg = $this->capacidadService->convertirAKg((float) $insumo->stock, $insumo->unidadMedida);

            $items->push((object) [
                'categoria' => 'insumo',
                'tipo_label' => $tipoNombre,
                'tipo_filtro' => $slug,
                'nombre' => $insumo->nombre,
                'detalle' => $insumo->descripcion ? \Illuminate\Support\Str::limit($insumo->descripcion, 60) : '—',
                'cantidad' => (float) $insumo->stock,
                'unidad' => $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? '',
                'kg' => $kg,
                'search' => strtolower(trim($insumo->nombre.' '.$tipoNombre)),
            ]);
        }

        $cosechas = ProduccionAlmacenamiento::query()
            ->with(['produccion.lote.cultivo', 'unidadMedida'])
            ->where('almacenid', $almacen->almacenid)
            ->whereNull('fechasalida')
            ->orderByDesc('fechaentrada')
            ->get();

        foreach ($cosechas as $c) {
            $lote = $c->produccion?->lote;
            $cultivo = $lote?->cultivo?->nombre ?? 'Cultivo';
            $nombre = $cultivo.' · '.($lote?->nombre ?? 'Producción #'.$c->produccionid);
            $kg = $this->capacidadService->convertirAKg((float) $c->cantidad, $c->unidadMedida);

            $items->push((object) [
                'categoria' => 'cosecha',
                'tipo_label' => 'Cosecha',
                'tipo_filtro' => 'cosecha',
                'nombre' => $nombre,
                'detalle' => $c->fechaentrada ? \Carbon\Carbon::parse($c->fechaentrada)->format('d/m/Y') : '—',
                'cantidad' => (float) $c->cantidad,
                'unidad' => $c->unidadMedida?->abreviatura ?? 'kg',
                'kg' => $kg,
                'search' => strtolower(trim($nombre.' cosecha '.$cultivo)),
            ]);
        }

        $productosPlanta = AlmacenajeLoteProduccion::query()
            ->with(['loteProduccionPedido.unidadMedida'])
            ->whereNull('fecha_retiro')
            ->where(function ($q) use ($almacen) {
                $q->where('almacenid', $almacen->almacenid)
                    ->orWhere(function ($q2) use ($almacen) {
                        $q2->whereNull('almacenid')->where('ubicacion', $almacen->nombre);
                    });
            })
            ->orderByDesc('fecha_almacenaje')
            ->get();

        foreach ($productosPlanta as $ingreso) {
            $lote = $ingreso->loteProduccionPedido;
            if (! $lote) {
                continue;
            }

            $producto = $lote->producto ?: $lote->nombre;
            $nombre = $producto.' · '.$lote->nombre;
            $kg = $this->capacidadService->convertirAKg((float) $ingreso->cantidad, $lote->unidadMedida);
            $unidad = $lote->unidadMedida?->abreviatura ?? $lote->unidadMedida?->nombre ?? 'kg';
            $detalle = trim(($ingreso->condicion ? $ingreso->condicion.' · ' : '').($ingreso->fecha_almacenaje
                ? \Carbon\Carbon::parse($ingreso->fecha_almacenaje)->format('d/m/Y H:i')
                : ''));

            $items->push((object) [
                'categoria' => 'producto_planta',
                'tipo_label' => 'Producto terminado',
                'tipo_filtro' => 'producto terminado',
                'nombre' => $nombre,
                'detalle' => $detalle !== '' ? \Illuminate\Support\Str::limit($detalle, 80) : ($lote->codigo_lote ?? '—'),
                'cantidad' => (float) $ingreso->cantidad,
                'unidad' => $unidad,
                'kg' => $kg,
                'search' => strtolower(trim($nombre.' producto planta '.$producto.' '.$lote->codigo_lote)),
            ]);
        }

        return $items->sortBy('nombre')->values();
    }

}

