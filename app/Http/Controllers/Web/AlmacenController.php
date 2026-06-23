<?php



namespace App\Http\Controllers\Web;



use App\Http\Controllers\Controller;

use App\Models\Almacen;

use App\Models\AlmacenajeLoteProduccion;

use App\Models\Insumo;

use App\Models\InsumoPresentacion;

use App\Models\ProduccionAlmacenamiento;

use App\Models\TipoAlmacen;

use App\Models\UnidadMedida;

use App\Services\AlmacenCapacidadService;

use App\Services\InventarioPresentacionService;

use App\Services\UbicacionesAlmacenService;

use App\Support\AlmacenAmbito;

use App\Support\AlmacenPlantaCosechaCatalogo;

use App\Support\InsumoCatalogo;

use App\Support\MayoristaAccess;

use App\Support\UbicacionGpsParser;

use App\Support\UsuarioRol;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Schema;



class AlmacenController extends Controller

{

    public function __construct(

        private readonly AlmacenCapacidadService $capacidadService,

        private readonly InventarioPresentacionService $inventarioPresentacion,

    ) {}



    public function index(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $ambito = $ctx['ambito'];

        $q = $this->queryAlmacenesVisibles($request, $ambito);



        $almacenesPagina = (clone $q)

            ->with(['unidadMedida'])

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

            'almacenesMapa' => $this->almacenesParaMapaIndex($request, $ambito, $ctx['rutaPrefijo'] ?? 'almacen-agricola'),

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

        if (Schema::hasColumn('almacen', 'responsable_usuarioid') && $request->user()) {
            $data['responsable_usuarioid'] = (int) $request->user()->usuarioid;
        }



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

        if (in_array($almacen->ambito ?? '', [AlmacenAmbito::PLANTA, AlmacenAmbito::AGRICOLA], true)) {
            $contenidos = AlmacenPlantaCosechaCatalogo::consolidarItemsPlanta(
                $contenidos,
                $almacen,
                $ctx['rutaPrefijo']
            );
        }

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

        $eval = \App\Support\AlmacenEliminacionCatalogo::evaluar($almacen);
        if (! $eval['ok']) {
            return back()->with([
                'error' => $eval['mensaje'],
                'error_modal' => true,
                'error_modal_titulo' => $eval['titulo'],
            ]);
        }

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

            'capacidad' => 'required|numeric|min:0.01',

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
        $esPlanta = ($almacen->ambito ?? '') === AlmacenAmbito::PLANTA;
        $esMayorista = ($almacen->ambito ?? '') === AlmacenAmbito::MAYORISTA;

        $insumosQuery = Insumo::query()->with(['tipo', 'unidadMedida'])
            ->where('almacenid', $almacen->almacenid);

        if ($esPlanta) {
            $insumosQuery = InsumoCatalogo::aplicarFiltroExcluirProductoTerminado($insumosQuery);
            $tipoSiembraId = InsumoCatalogo::tiposOrdenados()
                ->filter(fn ($t) => InsumoCatalogo::slugFromNombreTipo($t->nombre) === 'material_siembra')
                ->pluck('tipoinsumoid')
                ->first();
            if ($tipoSiembraId) {
                $insumosQuery->where(function ($q) use ($tipoSiembraId) {
                    $q->where('tipoinsumoid', '!=', (int) $tipoSiembraId)
                        ->orWhere('descripcion', 'like', 'Recepción pedido%');
                });
            }
        } elseif ($esMayorista) {
            $insumosQuery = InsumoCatalogo::aplicarFiltroProductoTerminado($insumosQuery)
                ->with(['presentaciones.tipoEmpaque']);
        } else {
            $insumosQuery = InsumoCatalogo::aplicarFiltroOperativo($insumosQuery);
        }

        $insumos = $insumosQuery->orderBy('nombre')->get();

        foreach ($insumos as $insumo) {
            if ((float) $insumo->stock <= 0) {
                continue;
            }

            if ($esMayorista) {
                $fila = $this->filaMayoristaConsolidada($almacen, $insumo);
                if ($fila !== null) {
                    $items->push($fila);
                }

                continue;
            }

            $tipoNombre = $insumo->tipo?->nombre ?? 'Insumo';
            $slug = InsumoCatalogo::slugFromNombreTipo($tipoNombre) ?? 'insumo';
            $esRecepcionPedido = ! $esMayorista && AlmacenPlantaCosechaCatalogo::esRecepcionPedidoInsumo($insumo);
            $kg = $this->capacidadService->convertirAKg((float) $insumo->stock, $insumo->unidadMedida);
            $claveCultivo = AlmacenPlantaCosechaCatalogo::claveCultivo($insumo->nombre);

            $items->push((object) [
                'categoria' => $esMayorista ? 'producto_terminado' : ($esRecepcionPedido ? 'cosecha' : 'insumo'),
                'tipo_label' => $esMayorista ? 'Producto terminado' : ($esRecepcionPedido ? 'Cosecha' : $tipoNombre),
                'tipo_filtro' => $esMayorista ? 'producto_terminado' : ($esRecepcionPedido ? 'cosecha' : $slug),
                'nombre' => $esRecepcionPedido
                    ? AlmacenPlantaCosechaCatalogo::etiquetaCultivo($insumo->nombre)
                    : $insumo->nombre,
                'detalle' => $insumo->descripcion ? \Illuminate\Support\Str::limit($insumo->descripcion, 60) : '—',
                'cantidad' => (float) $insumo->stock,
                'unidad' => $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? '',
                'kg' => $kg,
                'empaque' => null,
                'fecha_orden' => AlmacenPlantaCosechaCatalogo::fechaDesdeDescripcionRecepcion($insumo->descripcion)?->timestamp ?? 0,
                'search' => strtolower(trim($insumo->nombre.' '.$tipoNombre)),
                'insumoid' => $insumo->insumoid,
                'origen_tipo' => $esRecepcionPedido ? 'recepcion_pedido' : 'insumo',
                'clave_cultivo' => $claveCultivo,
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

            $fechaEntrada = $c->fechaentrada ? \Carbon\Carbon::parse($c->fechaentrada) : null;
            $claveCultivo = AlmacenPlantaCosechaCatalogo::claveCultivo($cultivo !== '' ? $cultivo : $nombre, $cultivo !== '' ? $cultivo : null);

            $items->push((object) [
                'categoria' => 'cosecha',
                'tipo_label' => 'Cosecha',
                'tipo_filtro' => 'cosecha',
                'nombre' => AlmacenPlantaCosechaCatalogo::etiquetaCultivo($cultivo !== '' ? $cultivo : $nombre, $claveCultivo),
                'detalle' => $fechaEntrada ? $fechaEntrada->format('d/m/Y') : '—',
                'cantidad' => (float) $c->cantidad,
                'unidad' => $c->unidadMedida?->abreviatura ?? 'kg',
                'kg' => $kg,
                'empaque' => null,
                'fecha_orden' => $fechaEntrada?->timestamp ?? 0,
                'search' => strtolower(trim($nombre.' cosecha '.$cultivo)),
                'produccionid' => $c->produccionid,
                'produccionalmacenamientoid' => $c->produccionalmacenamientoid,
                'origen_tipo' => 'produccion',
                'clave_cultivo' => $claveCultivo,
                'lote_nombre' => $lote?->nombre,
            ]);
        }

        $productosPlanta = AlmacenajeLoteProduccion::query()
            ->with(['loteProduccionPedido.unidadMedida', 'loteProduccionPedido.materiasPrimas.insumo.unidadMedida'])
            ->whereNull('fecha_retiro')
            ->where('almacenid', $almacen->almacenid)
            ->orderByDesc('fecha_almacenaje')
            ->get();

        foreach ($productosPlanta as $ingreso) {
            $lote = $ingreso->loteProduccionPedido;
            if (! $lote) {
                continue;
            }

            $lote->loadMissing('materiasPrimas.insumo.unidadMedida', 'unidadMedida');
            $producto = \App\Support\LoteProduccionNombre::productoDesdeLote($lote);
            $nombre = \App\Support\ProductoPlantaCatalogo::etiquetaLoteAlmacen($lote);
            $resumen = \App\Support\ProductoPlantaCatalogo::resumenProduccion($lote, $this->capacidadService);
            $kg = (float) ($resumen['kg'] ?? 0);
            if ($kg <= 0) {
                $kg = $this->capacidadService->convertirAKg((float) $ingreso->cantidad, $lote->unidadMedida);
            }
            $cantidad = \App\Support\ProductoPlantaCatalogo::esProduccionPorUnidades($lote)
                ? (float) \App\Support\ProductoPlantaCatalogo::unidadesProducidas($lote, $this->capacidadService)
                : ((float) ($resumen['cantidad'] ?? 0) > 0 ? (float) $resumen['cantidad'] : (float) $ingreso->cantidad);
            $unidad = \App\Support\ProductoPlantaCatalogo::unidadEtiqueta($producto, $lote->unidadMedida, $lote);
            $empaqueLabel = \App\Support\ProductoPlantaCatalogo::loteTieneEmpaquePlanificado($lote)
                ? \App\Support\EmpaquePlantaCatalogo::etiquetaEmpaquePlanificado(
                    $lote->empaque_catalogo_slug,
                    $lote->empaque_nombre_personalizado,
                    $lote->empaque_peso_neto_kg
                )
                : null;
            $detalleEmpaque = \App\Support\ProductoPlantaCatalogo::detalleEmpaqueAlmacen($lote, $resumen);
            $fechaAlm = $ingreso->fecha_almacenaje ? \Carbon\Carbon::parse($ingreso->fecha_almacenaje) : null;
            $detalle = $detalleEmpaque !== ''
                ? $detalleEmpaque
                : trim(($ingreso->condicion ? $ingreso->condicion.' · ' : '').($fechaAlm ? $fechaAlm->format('d/m/Y H:i') : ''));

            $items->push((object) [
                'categoria' => 'producto_planta',
                'tipo_label' => 'Producto terminado',
                'tipo_filtro' => 'producto terminado',
                'nombre' => $nombre,
                'detalle' => $detalle !== '' ? \Illuminate\Support\Str::limit($detalle, 120) : ($lote->codigo_lote ?? '—'),
                'cantidad' => $cantidad,
                'unidad' => $unidad,
                'kg' => $kg,
                'empaque' => $empaqueLabel,
                'fecha_orden' => $fechaAlm?->timestamp ?? 0,
                'search' => strtolower(trim($nombre.' producto planta '.$producto.' '.$lote->codigo_lote.' '.($empaqueLabel ?? ''))),
                'lote_produccion_pedido_id' => $lote->loteproduccionpedidoid,
            ]);
        }

        return $items->sortByDesc('fecha_orden')->values();
    }

    /**
     * Una fila por producto; el desglose por empaque queda en inventario.show.
     */
    private function filaMayoristaConsolidada(Almacen $almacen, Insumo $insumo): ?object
    {
        $this->inventarioPresentacion->asegurarInventarioDesdeStock(
            (int) $almacen->almacenid,
            (int) $insumo->insumoid
        );

        $presentaciones = $insumo->relationLoaded('presentaciones')
            ? $insumo->presentaciones->where('activo', true)->sortBy('orden')->values()
            : InsumoPresentacion::query()
                ->with('tipoEmpaque')
                ->where('insumoid', $insumo->insumoid)
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get();

        $totalUnidades = 0.0;
        $totalKg = 0.0;
        $empaquesConStock = 0;
        $nombresEmpaque = [];
        $etiquetasUnidad = [];

        foreach ($presentaciones as $presentacion) {
            $unidades = $this->inventarioPresentacion->stockTotalUnidades(
                (int) $almacen->almacenid,
                (int) $presentacion->insumo_presentacionid
            );
            $kg = $this->inventarioPresentacion->stockTotalKg(
                (int) $almacen->almacenid,
                (int) $presentacion->insumo_presentacionid
            );

            if ($unidades <= 0 && $kg <= 0) {
                continue;
            }

            if ($kg <= 0 && $unidades > 0) {
                $kg = $unidades * $presentacion->pesoNetoKg();
            }

            $totalUnidades += $unidades;
            $totalKg += $kg;
            $empaquesConStock++;
            $nombresEmpaque[] = trim($presentacion->nombre);
            $etiquetasUnidad[] = $presentacion->etiquetaUnidad();
        }

        if ($totalKg <= 0 && $totalUnidades <= 0) {
            return null;
        }

        if ($totalKg <= 0) {
            $totalKg = $this->capacidadService->convertirAKg((float) $insumo->stock, $insumo->unidadMedida);
        }

        $empaque = match (true) {
            $empaquesConStock === 0 => '—',
            $empaquesConStock === 1 => $nombresEmpaque[0],
            default => $empaquesConStock.' presentaciones',
        };

        $unidadEtiqueta = count(array_unique($etiquetasUnidad)) === 1
            ? ($etiquetasUnidad[0] ?? 'empaques')
            : 'empaques';

        return (object) [
            'categoria' => 'producto_terminado',
            'tipo_label' => 'Producto terminado',
            'tipo_filtro' => 'producto_terminado',
            'nombre' => $insumo->nombre,
            'detalle' => $insumo->descripcion ? \Illuminate\Support\Str::limit($insumo->descripcion, 60) : '—',
            'cantidad' => $totalUnidades > 0 ? $totalUnidades : (float) $insumo->stock,
            'unidad' => $totalUnidades > 0 ? $unidadEtiqueta : ($insumo->unidadMedida?->abreviatura ?? 'kg'),
            'kg' => $totalKg,
            'empaque' => $empaque,
            'fecha_orden' => 0,
            'search' => strtolower(trim($insumo->nombre.' '.implode(' ', $nombresEmpaque).' producto terminado')),
            'insumoid' => $insumo->insumoid,
            'origen_tipo' => 'insumo',
        ];
    }

    private function queryAlmacenesVisibles(Request $request, string $ambito)
    {
        $query = AlmacenAmbito::scope(Almacen::query(), $ambito);
        $user = $request->user();

        if (! $user || UsuarioRol::esAdminGlobal($user)) {
            return $query;
        }

        if ($ambito === AlmacenAmbito::MAYORISTA) {
            return MayoristaAccess::scopeAlmacenesMayorista($query, $user);
        }

        if (
            $ambito === AlmacenAmbito::AGRICOLA
            && UsuarioRol::esJefeAgricultor($user)
            && Schema::hasColumn('almacen', 'responsable_usuarioid')
        ) {
            return $query->where('responsable_usuarioid', (int) $user->usuarioid);
        }

        return $query;
    }

    /** @return list<array<string, mixed>> */
    private function almacenesParaMapaIndex(Request $request, string $ambito, string $rutaPrefijo): array
    {
        $almacenes = $this->queryAlmacenesVisibles($request, $ambito)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $items = [];

        foreach ($almacenes as $almacen) {
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );

            $nombre = (string) $almacen->nombre;
            $items[] = [
                'id' => (int) $almacen->almacenid,
                'nombre' => $nombre,
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'direccion' => $resuelto['direccion'],
                'search' => mb_strtolower($nombre.' '.($resuelto['direccion'] ?? '')),
                'url' => route($rutaPrefijo.'.show', $almacen),
            ];
        }

        return $items;
    }

}

