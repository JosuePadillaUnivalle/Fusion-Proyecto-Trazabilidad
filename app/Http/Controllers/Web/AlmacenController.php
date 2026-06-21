<?php



namespace App\Http\Controllers\Web;



use App\Http\Controllers\Controller;

use App\Models\Almacen;

use App\Models\AlmacenajeLoteProduccion;

use App\Models\Insumo;

use App\Models\ProduccionAlmacenamiento;

use App\Models\PuntoVenta;

use App\Models\TipoAlmacen;

use App\Models\UnidadMedida;

use App\Services\AlmacenCapacidadService;

use App\Services\CosechaPresentacionService;

use App\Services\UbicacionesAlmacenService;

use App\Support\AlmacenAmbito;

use App\Support\AlmacenNombreCatalogo;

use App\Support\InsumoCatalogo;

use App\Support\MayoristaAccess;

use App\Support\PuntoVentaAccess;

use App\Support\UbicacionGpsParser;

use App\Support\UsuarioRol;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Schema;



class AlmacenController extends Controller

{

    public function __construct(

        private readonly AlmacenCapacidadService $capacidadService,

        private readonly CosechaPresentacionService $presentacionService,

    ) {}



    public function index(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $ambito = $ctx['ambito'];

        $baseQuery = AlmacenAmbito::scopeParaUsuario(Almacen::query(), $ambito, $request->user());

        $q = clone $baseQuery;

        $almacenesPagina = (clone $baseQuery)
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



        $almacenesMapa = $this->almacenesParaMapaIndex($ambito, $ctx['rutaPrefijo']);

        return view('almacenes.index', array_merge(compact('almacenesPagina', 'stats', 'ocupacionPorId', 'almacenesMapa'), $ctx, [

            'almacenes' => $almacenesPagina,

        ]));

    }



    public function create(Request $request)

    {

        $ctx = AlmacenAmbito::contexto($request);



        return view('almacenes.create', array_merge(

            $this->datosFormulario(null, $ctx['ambito']),

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

        $data = $this->completarNombreDesdeUbicacion($data, $ctx['ambito']);

        $data['ambito'] = $ctx['ambito'];

        $data['activo'] = true;

        $data['unidadmedidaid'] = $this->unidadKilogramoId();

        $data['tipoalmacenid'] = $this->tipoAlmacenPorDefecto();

        if ($ctx['ambito'] === AlmacenAmbito::MAYORISTA && $request->user() && ! $request->user()->hasRole('admin')) {
            $data['responsable_usuarioid'] = $request->user()->usuarioid;
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

        $tiposContenidoFiltro = $contenidos->pluck('tipo_label')->unique()->sort()->values();

        $resumenCosechasPorCultivo = ($almacen->ambito ?? '') === AlmacenAmbito::AGRICOLA
            ? $this->resumenCosechasPorCultivo($almacen)
            : collect();



        return view('almacenes.show', array_merge(compact(
            'almacen',
            'resumenCapacidad',
            'contenidos',
            'tiposContenidoFiltro',
            'resumenCosechasPorCultivo'
        ), $ctx));

    }



    public function edit(Request $request, Almacen $almacen)

    {

        $ctx = AlmacenAmbito::contexto($request);

        $this->asegurarAmbitoAlmacen($almacen, $ctx['ambito']);



        return view('almacenes.edit', array_merge(

            $this->datosFormulario($almacen, $ctx['ambito']),

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

        if ($this->capacidadService->tieneRecursos($almacen)) {
            return redirect()
                ->back()
                ->with('error', $this->capacidadService->mensajeEliminacionConRecursos());
        }

        $almacen->delete();



        return redirect()

            ->route($ctx['rutaPrefijo'].'.index')

            ->with('success', 'Almacén eliminado.');

    }



    /**
     * @return array<int, array<string, mixed>>
     */
    private function almacenesParaMapaIndex(string $ambito, string $rutaPrefijo): array
    {
        $almacenes = AlmacenAmbito::scopeParaUsuario(Almacen::query()->orderBy('nombre'), $ambito, auth()->user())->get();

        return $almacenes->map(function (Almacen $almacen) use ($rutaPrefijo) {
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );
            $direccion = $resuelto['direccion'] ?? '';

            return [
                'id' => $almacen->almacenid,
                'nombre' => $almacen->nombre,
                'direccion' => $direccion,
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'estimada' => $resuelto['estimada'] ?? false,
                'url' => route($rutaPrefijo.'.show', $almacen),
                'search' => mb_strtolower(trim(
                    ($almacen->nombre ?? '').' '.($almacen->ubicacion ?? '').' '.$direccion
                )),
            ];
        })->values()->all();
    }

    /**
     * Almacenes con GPS para el mapa del formulario de registro/edición (toggle «Ver mis almacenes»).
     *
     * @return array<int, array<string, mixed>>
     */
    private function almacenesParaMapaRegistro(string $ambito, ?Almacen $excluirAlmacen = null): array
    {
        $user = auth()->user();
        $esAdmin = UsuarioRol::esAdminGlobal($user);

        $query = Almacen::query()->orderBy('nombre');

        if ($esAdmin) {
            // Admin: todos los almacenes del sistema.
        } elseif ($ambito === AlmacenAmbito::PUNTO_VENTA && UsuarioRol::esMinorista($user)) {
            $idsAlmacen = PuntoVentaAccess::scopePuntosDelUsuario(
                PuntoVenta::query()->whereNotNull('almacenid'),
                $user
            )->pluck('almacenid');

            $query->whereIn('almacenid', $idsAlmacen);
        } else {
            $query = AlmacenAmbito::scopeParaUsuario($query, $ambito, $user);
        }

        if ($excluirAlmacen !== null) {
            $query->where('almacenid', '!=', $excluirAlmacen->almacenid);
        }

        return $query->get()->map(function (Almacen $almacen) {
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );

            if ($resuelto['lat'] === null || $resuelto['lng'] === null) {
                return null;
            }

            $direccion = $resuelto['direccion'] ?? '';

            return [
                'id' => $almacen->almacenid,
                'nombre' => $almacen->nombre,
                'direccion' => $direccion,
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'ambito' => AlmacenAmbito::resolverAmbito($almacen),
            ];
        })->filter()->values()->all();
    }

    private function asegurarAmbitoAlmacen(Almacen $almacen, string $ambito): void

    {

        if (Schema::hasColumn('almacen', 'ambito') && $almacen->ambito !== $ambito) {

            abort(404);

        }

        if ($ambito === AlmacenAmbito::MAYORISTA) {
            MayoristaAccess::asegurarPuedeGestionar(auth()->user(), $almacen);
        }

    }



    /**

     * @return array<string, mixed>

     */

    private function datosFormulario(?Almacen $almacen = null, ?string $ambito = null): array

    {

        $config = config('almacenes', []);

        $campos = $config['campos'] ?? [];

        $ambitoKey = $ambito ?? $almacen?->ambito;

        if ($ambitoKey && ! empty($config['campos_por_ambito'][$ambitoKey])) {

            $campos = array_merge($campos, $config['campos_por_ambito'][$ambitoKey]);

        }



        $payload = [

            'almacen' => $almacen,

            'guias' => array_merge($config, ['campos' => $campos]),

        ];

        if ($ambitoKey) {
            $payload['almacenesMapaRegistro'] = $this->almacenesParaMapaRegistro($ambitoKey, $almacen);
        }

        return $payload;

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

    /** @param  array<string, mixed>  $data */
    private function completarNombreDesdeUbicacion(array $data, string $ambito): array
    {
        if (trim((string) ($data['nombre'] ?? '')) !== '') {
            return $data;
        }

        $coords = UbicacionGpsParser::fromTexto($data['ubicacion'] ?? null);
        if (! $coords) {
            return $data;
        }

        $data['nombre'] = AlmacenNombreCatalogo::generar(
            (float) $coords['lat'],
            (float) $coords['lng'],
            $ambito,
            UbicacionGpsParser::direccionLegible($data['ubicacion'] ?? null),
        );

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

        $insumoQuery = Insumo::query()->with(['tipo', 'unidadMedida', 'presentaciones.tipoEmpaque'])
            ->where('almacenid', $almacen->almacenid);

        $insumoQuery = InsumoCatalogo::aplicarFiltroInsumoPorAmbitoAlmacen(
            $insumoQuery,
            (string) ($almacen->ambito ?? '')
        );

        $insumos = $insumoQuery->orderBy('nombre')->get();

        foreach ($insumos as $insumo) {
            if ((float) $insumo->stock <= 0) {
                continue;
            }

            $tipoNombre = $insumo->tipo?->nombre ?? 'Insumo';
            $slug = InsumoCatalogo::slugFromNombreTipo($tipoNombre) ?? 'insumo';
            $kg = $this->capacidadService->convertirAKg((float) $insumo->stock, $insumo->unidadMedida);
            $ambito = (string) ($almacen->ambito ?? '');
            $esMayorista = $ambito === AlmacenAmbito::MAYORISTA;
            $esPlanta = $ambito === AlmacenAmbito::PLANTA;
            $categoria = $esMayorista ? 'producto_mayorista' : ($esPlanta ? 'producto_planta' : 'insumo');
            $tipoLabel = ($esMayorista || $esPlanta) ? 'Producto terminado' : $tipoNombre;
            $presentaciones = $insumo->presentaciones
                ?->where('activo', true)
                ->pluck('nombre')
                ->filter()
                ->values();
            $detallePres = $presentaciones && $presentaciones->isNotEmpty()
                ? 'Presentaciones: '.$presentaciones->join(' · ')
                : null;
            $detalle = $detallePres
                ?? ($insumo->descripcion ? \Illuminate\Support\Str::limit($insumo->descripcion, 80) : '—');

            $items->push((object) [
                'categoria' => $categoria,
                'tipo_label' => $tipoLabel,
                'tipo_filtro' => ($esMayorista || $esPlanta) ? 'producto terminado' : $slug,
                'nombre' => $insumo->nombre,
                'detalle' => $detalle,
                'cantidad' => (float) $insumo->stock,
                'unidad' => $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? '',
                'kg' => $kg,
                'search' => strtolower(trim($insumo->nombre.' '.$tipoNombre)),
                'insumoid' => $insumo->insumoid,
            ]);
        }

        $cosechas = ProduccionAlmacenamiento::query()
            ->with([
                'produccion.lote.cultivo',
                'produccion.lote.catalogoTamanoConteo.tipoEmpaque',
                'catalogoTamanoConteo.tipoEmpaque',
                'unidadMedida',
            ])
            ->where('almacenid', $almacen->almacenid)
            ->whereNull('fechasalida')
            ->orderByDesc('fechaentrada')
            ->get();

        foreach ($cosechas as $c) {
            $lote = $c->produccion?->lote;
            $cultivo = $lote?->cultivo?->nombre ?? 'Cultivo';
            $nombre = $cultivo.' · '.($lote?->nombre ?? 'Producción #'.$c->produccionid);
            $kg = $this->capacidadService->convertirAKg((float) $c->cantidad, $c->unidadMedida);
            $presentacion = $this->presentacionService->paraAlmacenamiento($c);
            $detalle = $c->fechaentrada ? \Carbon\Carbon::parse($c->fechaentrada)->format('d/m/Y') : '—';
            if ($presentacion['ok'] ?? false) {
                $detalle .= ' · '.$presentacion['resumen'];
            }

            $items->push((object) [
                'categoria' => 'cosecha',
                'tipo_label' => 'Cosecha',
                'tipo_filtro' => 'cosecha',
                'nombre' => $nombre,
                'detalle' => $detalle,
                'cantidad' => (float) $c->cantidad,
                'unidad' => $c->unidadMedida?->abreviatura ?? 'kg',
                'kg' => $kg,
                'cajas' => $presentacion['empaques'] ?? null,
                'unidades' => $presentacion['unidades'] ?? null,
                'cultivo' => $cultivo,
                'search' => strtolower(trim($nombre.' cosecha '.$cultivo)),
                'produccionid' => $c->produccionid,
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
            $producto = $lote->producto ?: $lote->nombre;
            $nombre = $producto.' · '.$lote->nombre;
            $resumen = \App\Support\ProductoPlantaCatalogo::resumenProduccion($lote, $this->capacidadService);
            $kg = $resumen['kg'] > 0
                ? $resumen['kg']
                : $this->capacidadService->convertirAKg((float) $ingreso->cantidad, $lote->unidadMedida);
            $cantidad = $resumen['cantidad'] > 0 ? $resumen['cantidad'] : (float) $ingreso->cantidad;
            $unidad = \App\Support\ProductoPlantaCatalogo::unidadEtiqueta($producto, $lote->unidadMedida);
            $detalle = trim(($ingreso->condicion ? $ingreso->condicion.' · ' : '').($ingreso->fecha_almacenaje
                ? \Carbon\Carbon::parse($ingreso->fecha_almacenaje)->format('d/m/Y H:i')
                : ''));

            $items->push((object) [
                'categoria' => 'producto_planta',
                'tipo_label' => 'Producto terminado',
                'tipo_filtro' => 'producto terminado',
                'nombre' => $nombre,
                'detalle' => $detalle !== '' ? \Illuminate\Support\Str::limit($detalle, 80) : ($lote->codigo_lote ?? '—'),
                'cantidad' => $cantidad,
                'unidad' => $unidad,
                'kg' => $kg,
                'search' => strtolower(trim($nombre.' producto planta '.$producto.' '.$lote->codigo_lote)),
                'lote_produccion_pedido_id' => $lote->loteproduccionpedidoid,
            ]);
        }

        return $items->sortBy('nombre')->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function resumenCosechasPorCultivo(Almacen $almacen): \Illuminate\Support\Collection
    {
        $cosechas = ProduccionAlmacenamiento::query()
            ->with([
                'produccion.lote.cultivo',
                'produccion.lote.catalogoTamanoConteo.tipoEmpaque',
                'catalogoTamanoConteo.tipoEmpaque',
                'unidadMedida',
            ])
            ->where('almacenid', $almacen->almacenid)
            ->whereNull('fechasalida')
            ->get();

        $agrupado = [];

        foreach ($cosechas as $c) {
            $cultivo = $c->produccion?->lote?->cultivo?->nombre ?? 'Cultivo';
            $presentacion = $this->presentacionService->paraAlmacenamiento($c);

            if (! isset($agrupado[$cultivo])) {
                $agrupado[$cultivo] = (object) [
                    'cultivo' => $cultivo,
                    'cajas' => 0,
                    'unidades' => 0,
                    'kg' => 0.0,
                    'lotes' => 0,
                ];
            }

            $agrupado[$cultivo]->cajas += (int) ($presentacion['empaques'] ?? 0);
            $agrupado[$cultivo]->unidades += (int) ($presentacion['unidades'] ?? 0);
            $agrupado[$cultivo]->kg += $this->capacidadService->convertirAKg((float) $c->cantidad, $c->unidadMedida);
            $agrupado[$cultivo]->lotes++;
        }

        return collect(array_values($agrupado))->sortBy('cultivo')->values();
    }

}

