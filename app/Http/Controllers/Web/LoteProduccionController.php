<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\AlmacenajeLoteProduccion;
use App\Models\EvaluacionFinalLoteProduccion;
use App\Models\LoteProduccionPedido;
use App\Models\MaquinaPlanta;
use App\Models\Pedido;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\UnidadMedida;
use App\Services\AlmacenCapacidadService;
use App\Services\LoteProduccionPlantaService;
use App\Support\AlmacenAmbito;
use App\Support\AlmacenajeLoteCondiciones;
use App\Support\LoteProduccionNombre;
use App\Support\LoteProduccionTransformacionService;
use App\Support\LoteProduccionTrazabilidadService;
use App\Support\MaquinaProcesoCompatibilidad;
use App\Support\ProcesoPlantaCatalogo;
use App\Support\ProductoPlantaCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoteProduccionController extends Controller
{
    public function __construct(
        private readonly LoteProduccionPlantaService $loteService,
        private readonly LoteProduccionTrazabilidadService $trazabilidad,
        private readonly LoteProduccionTransformacionService $transformacion,
        private readonly AlmacenCapacidadService $capacidadService,
    ) {}

    public function index(Request $request): View
    {
        $productoFiltro = LoteProduccionNombre::normalizarProducto((string) $request->query('producto', ''));
        $estadoFiltro = (string) $request->query('estado', '');
        $busqueda = trim((string) $request->query('q', ''));
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $lotesQuery = LoteProduccionPedido::query()
            ->with(['pedido', 'unidadMedida', 'materiasPrimas.insumo', 'procesoPlanta', 'evaluacionesFinales', 'almacenajes'])
            ->orderByDesc('fecha_creacion');

        if ($productoFiltro !== '') {
            $key = mb_strtolower($productoFiltro);
            $lotesQuery->where(function ($q) use ($key) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('lote_produccion_pedido', 'producto')) {
                    $q->whereRaw('LOWER(TRIM(producto)) = ?', [$key]);
                } else {
                    $q->whereRaw('LOWER(nombre) LIKE ?', [$key.' - lote %']);
                }
            });
        }

        if ($busqueda !== '') {
            $term = '%'.mb_strtolower($busqueda).'%';
            $lotesQuery->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(codigo_lote) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', [$term])
                    ->orWhereHas('pedido', fn ($p) => $p->whereRaw('LOWER(numero_solicitud) LIKE ?', [$term]));
            });
        }

        if ($desde) {
            $lotesQuery->whereDate('fecha_creacion', '>=', $desde);
        }
        if ($hasta) {
            $lotesQuery->whereDate('fecha_creacion', '<=', $hasta);
        }

        if ($estadoFiltro === 'pendiente') {
            $lotesQuery->whereNull('hora_fin')
                ->whereDoesntHave('evaluacionesFinales')
                ->whereDoesntHave('registrosProceso');
        } elseif ($estadoFiltro === 'en_proceso') {
            $lotesQuery->whereNull('hora_fin')
                ->whereHas('registrosProceso')
                ->whereDoesntHave('almacenajes');
        } elseif ($estadoFiltro === 'completado') {
            $lotesQuery->where(function ($q) {
                $q->whereNotNull('hora_fin')->orWhereHas('almacenajes');
            });
        }

        $lotes = $lotesQuery->paginate(15)->withQueryString();

        $lotes->getCollection()->transform(function (LoteProduccionPedido $lote) {
            $lote->fase_label = LoteProduccionTrazabilidadService::FASES[$this->trazabilidad->resolverFaseActual($lote)]['label'] ?? '—';
            $lote->estado_operativo = $this->trazabilidad->estadoOperativo($lote);

            return $lote;
        });

        $stats = [
            'total' => LoteProduccionPedido::count(),
            'pendientes' => LoteProduccionPedido::query()->whereNull('hora_fin')->whereDoesntHave('registrosProceso')->count(),
            'en_proceso' => LoteProduccionPedido::query()->whereNull('hora_fin')->whereHas('registrosProceso')->whereDoesntHave('almacenajes')->count(),
            'completados' => LoteProduccionPedido::query()->where(fn ($q) => $q->whereNotNull('hora_fin')->orWhereHas('almacenajes'))->count(),
        ];

        $pedidoLabel = '';
        if (old('pedidoid')) {
            $pedidoLabel = Pedido::find(old('pedidoid'))?->numero_solicitud ?? '';
        }

        $almacenes = AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::PLANTA)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $filtroAlmacenes = array_merge(
            [['value' => '', 'label' => 'Todos los almacenes de planta']],
            $almacenes->map(fn (Almacen $a) => [
                'value' => (string) $a->almacenid,
                'label' => $a->nombre,
            ])->all()
        );

        $estadosPedido = ['pendiente', 'confirmado', 'en produccion', 'aprobado', 'aceptado', 'en_proceso', 'asignado'];
        $existentes = Pedido::query()->distinct()->pluck('estado')->filter()->all();
        $todosEstados = array_values(array_unique(array_merge($estadosPedido, $existentes)));
        $filtroEstadosPedido = array_merge(
            [['value' => '', 'label' => 'Todos los estados']],
            array_map(fn ($e) => [
                'value' => $e,
                'label' => ucfirst(str_replace('_', ' ', (string) $e)),
            ], $todosEstados)
        );

        $unidadesMedida = UnidadMedida::query()->orderBy('nombre')->get();
        $productosLote = LoteProduccionNombre::productosDistintos();
        $procesosPlanta = \App\Support\ProcesoPlantaCatalogo::activosOrdenados();

        return view('procesamiento.index', compact(
            'lotes',
            'stats',
            'pedidoLabel',
            'almacenes',
            'filtroAlmacenes',
            'filtroEstadosPedido',
            'unidadesMedida',
            'productosLote',
            'productoFiltro',
            'estadoFiltro',
            'busqueda',
            'desde',
            'hasta',
            'procesosPlanta'
        ));
    }

    public function show(LoteProduccionPedido $loteProduccion): View
    {
        abort_unless(
            auth()->user()?->can('lote_produccion.view') || auth()->user()?->can('lote_produccion.create'),
            403
        );

        $loteProduccion->load(['pedido', 'unidadMedida', 'materiasPrimas.insumo.unidadMedida', 'almacenajes']);

        if ($loteProduccion->almacenajes->isNotEmpty() && ! $loteProduccion->hora_fin) {
            $loteProduccion->update([
                'hora_fin' => $loteProduccion->almacenajes->max('fecha_almacenaje') ?? now(),
            ]);
            $loteProduccion->refresh();
        }

        $dash = $this->trazabilidad->dashboardLote($loteProduccion);
        $procesosPlanta = ProcesoPlantaCatalogo::paraTransformacion();
        $procesosDisponibles = $procesosPlanta;
        $procesosUsadosIds = $this->transformacion->procesosRegistradosIds($loteProduccion);
        $maquinasPlanta = MaquinaPlanta::query()->where('activo', true)->orderBy('nombre')->get();
        $mapaCompatibilidad = MaquinaProcesoCompatibilidad::mapaSelectores();
        $almacenesPlanta = AlmacenAmbito::scope(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])->where('activo', true),
            AlmacenAmbito::PLANTA
        )->orderBy('nombre')->get();
        $condicionesAlmacenaje = AlmacenajeLoteCondiciones::opciones();

        $cantidadProductoAlmacen = (float) ($loteProduccion->cantidad_objetivo ?? 0);
        if ($cantidadProductoAlmacen <= 0) {
            $cantidadProductoAlmacen = (float) $loteProduccion->materiasPrimas->sum('cantidad_usada');
        }
        $productoLote = LoteProduccionNombre::productoDesdeLote($loteProduccion);
        $produccionEstimada = ProductoPlantaCatalogo::resumenProduccion($loteProduccion, $this->capacidadService);
        $unidadProductoAlmacen = ProductoPlantaCatalogo::unidadEtiqueta(
            $productoLote,
            $loteProduccion->unidadMedida
        );
        $cantidadProductoAlmacen = $produccionEstimada['cantidad'] > 0
            ? $produccionEstimada['cantidad']
            : $cantidadProductoAlmacen;
        $cantidadProductoAlmacenKg = $produccionEstimada['kg'];

        return view('procesamiento.show', array_merge($dash, [
            'lote' => $loteProduccion,
            'procesosPlanta' => $procesosPlanta,
            'procesosDisponibles' => $procesosDisponibles,
            'procesosUsadosIds' => $procesosUsadosIds,
            'maquinasPlanta' => $maquinasPlanta,
            'mapaCompatibilidad' => $mapaCompatibilidad,
            'almacenesPlanta' => $almacenesPlanta,
            'condicionesAlmacenaje' => $condicionesAlmacenaje,
            'cantidadProductoAlmacen' => $cantidadProductoAlmacen,
            'cantidadProductoAlmacenKg' => $cantidadProductoAlmacenKg,
            'unidadProductoAlmacen' => $unidadProductoAlmacen,
            'produccionEstimada' => $produccionEstimada,
            'fases' => LoteProduccionTrazabilidadService::FASES,
            'puedeEliminar' => $this->loteService->puedeEliminar($loteProduccion),
        ]));
    }

    public function registrarEtapa(Request $request, LoteProduccionPedido $loteProduccion): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo($request->user()) || $request->user()?->hasRole('admin'), 403);

        if ($this->trazabilidad->transformacionCompleta($loteProduccion)) {
            return back()->with('error', 'La transformación ya finalizó con «'.ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION.'».');
        }

        $data = $request->validate([
            'procesoplantaid' => ['required', 'integer', 'exists:proceso_planta,procesoplantaid'],
            'maquinaplantaid' => ['required', 'integer', 'exists:maquina_planta,maquinaplantaid'],
            'hora_inicio' => ['required', 'date'],
            'hora_fin' => ['required', 'date', 'after_or_equal:hora_inicio'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $proceso = \App\Models\ProcesoPlanta::query()->findOrFail($data['procesoplantaid']);
        if (in_array($proceso->nombre, ['Control de Calidad'], true)) {
            return back()->with('error', '«Control de Calidad» corresponde a la fase de certificación, no a transformación.');
        }

        if (! MaquinaProcesoCompatibilidad::compatible((int) $data['procesoplantaid'], (int) $data['maquinaplantaid'])) {
            $maquina = MaquinaPlanta::find($data['maquinaplantaid']);

            return back()->with('error', 'La maquinaria «'.($maquina?->nombre ?? '').'» no es compatible con el proceso «'.$proceso->nombre.'».');
        }

        try {
            $paso = $this->transformacion->resolverPasoProcesoMaquina(
                (int) $data['procesoplantaid'],
                (int) $data['maquinaplantaid']
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        RegistroProcesoMaquinaPlanta::create([
            'procesomaquinaplantaid' => $paso->procesomaquinaplantaid,
            'loteproduccionpedidoid' => $loteProduccion->loteproduccionpedidoid,
            'usuarioid' => $request->user()->usuarioid,
            'variables_ingresadas' => json_encode([
                'proceso' => $proceso->nombre,
                'maquina' => MaquinaPlanta::find($data['maquinaplantaid'])?->nombre,
            ]),
            'cumple_estandar' => true,
            'observaciones' => $data['observaciones'] ?? null,
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'fecha_registro' => $data['hora_fin'],
        ]);

        if (! $loteProduccion->hora_inicio) {
            $loteProduccion->update(['hora_inicio' => $data['hora_inicio']]);
        }

        $loteProduccion->update(['procesoplantaid' => $data['procesoplantaid']]);

        $mensaje = 'Etapa «'.$proceso->nombre.'» registrada.';
        if (ProcesoPlantaCatalogo::esCierreTransformacion($proceso->nombre)) {
            $mensaje .= ' Transformación completada: ya puede certificar el lote.';
        }

        return redirect()
            ->route('procesamiento.show', $loteProduccion)
            ->with('success', $mensaje);
    }

    public function certificar(Request $request, LoteProduccionPedido $loteProduccion): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo($request->user()) || $request->user()?->hasRole('admin'), 403);

        if (! $this->trazabilidad->transformacionCompleta($loteProduccion)) {
            return back()->with('error', 'Complete la transformación antes de certificar.');
        }

        $data = $request->validate([
            'razon' => ['required', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        EvaluacionFinalLoteProduccion::updateOrCreate(
            ['loteproduccionpedidoid' => $loteProduccion->loteproduccionpedidoid],
            [
                'inspector_usuarioid' => $request->user()->usuarioid,
                'razon' => $data['razon'],
                'observaciones' => $data['observaciones'] ?? null,
                'fecha_evaluacion' => now(),
            ]
        );

        return redirect()
            ->route('procesamiento.show', $loteProduccion)
            ->with('success', 'Evaluación final registrada.');
    }

    public function almacenar(Request $request, LoteProduccionPedido $loteProduccion): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo($request->user()) || $request->user()?->hasRole('admin'), 403);

        if (! $loteProduccion->evaluacionesFinales()->exists()) {
            return back()->with('error', 'Certifique el lote antes de almacenar.');
        }

        $data = $request->validate([
            'almacenid' => ['required', 'integer', 'exists:almacen,almacenid'],
            'condicion' => ['required', 'string', Rule::in(AlmacenajeLoteCondiciones::opciones())],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $loteProduccion->loadMissing('materiasPrimas.insumo.unidadMedida', 'unidadMedida');

        $cantidad = ProductoPlantaCatalogo::cantidadParaAlmacenaje($loteProduccion, $this->capacidadService);

        if ($cantidad <= 0) {
            return back()->with('error', 'No hay materia prima registrada ni cantidad objetivo para calcular el almacenaje.');
        }

        $cantidadKg = ProductoPlantaCatalogo::kgParaAlmacenaje($loteProduccion, $this->capacidadService);
        if ($cantidadKg <= 0) {
            return back()->with('error', 'No se pudo calcular el peso del producto terminado a partir de la materia prima.');
        }

        $almacen = Almacen::query()->with('unidadMedida')->findOrFail($data['almacenid']);
        if (($almacen->ambito ?? '') !== AlmacenAmbito::PLANTA) {
            return back()->with('error', 'Seleccione un almacén de planta.');
        }

        $resumen = $this->capacidadService->resumen($almacen);
        if ($cantidadKg > $resumen['disponible_kg']) {
            return back()->with('error',
                'La cantidad del lote excede la capacidad disponible del almacén. Disponible: '.
                round($resumen['disponible_kg'], 2).' kg'
            );
        }

        AlmacenajeLoteProduccion::create([
            'loteproduccionpedidoid' => $loteProduccion->loteproduccionpedidoid,
            'almacenid' => $almacen->almacenid,
            'ubicacion' => $almacen->nombre,
            'cantidad' => $cantidad,
            'condicion' => $data['condicion'],
            'observaciones' => ($data['observaciones'] ?? null)
                ?: 'Producto terminado: '.$loteProduccion->nombre,
            'fecha_almacenaje' => now(),
        ]);

        $loteProduccion->update([
            'cantidad_objetivo' => $cantidad,
            'unidadmedidaid' => ProductoPlantaCatalogo::resolverUnidadMedidaId(
                ProductoPlantaCatalogo::nombreProducto($loteProduccion),
                $loteProduccion->unidadmedidaid
            ) ?? $loteProduccion->unidadmedidaid,
        ]);

        if (! $loteProduccion->hora_fin) {
            $loteProduccion->update(['hora_fin' => now()]);
        }

        return redirect()
            ->route('procesamiento.show', $loteProduccion)
            ->with('success', 'Almacenaje registrado. Lote completado al 100 %.');
    }

    public function completar(LoteProduccionPedido $loteProduccion): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo(auth()->user()) || auth()->user()?->hasRole('admin'), 403);

        if (! $loteProduccion->almacenajes()->exists()) {
            return back()->with('error', 'Registre el almacenaje antes de cerrar el lote.');
        }

        if (! $loteProduccion->hora_fin) {
            $loteProduccion->update(['hora_fin' => now()]);
        }

        return redirect()
            ->route('procesamiento.show', $loteProduccion)
            ->with('success', 'Lote marcado como completado.');
    }

    public function siguienteNombre(Request $request): JsonResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo($request->user()) || $request->user()?->hasRole('admin'), 403);

        $producto = LoteProduccionNombre::normalizarProducto((string) $request->query('producto', ''));
        if ($producto === '') {
            return response()->json(['nombre' => '', 'numero' => 1]);
        }

        $numero = LoteProduccionNombre::siguienteNumero($producto);

        return response()->json([
            'nombre' => LoteProduccionNombre::formatear($producto, $numero),
            'numero' => $numero,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo($request->user()) || $request->user()?->hasRole('admin'), 403);

        $data = $request->validate([
            'producto' => ['required', 'string', 'max:100'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'cantidad_objetivo' => ['nullable', 'numeric', 'min:0'],
            'unidadmedidaid' => ['nullable', 'integer', 'exists:unidadmedida,unidadmedidaid'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'materias' => ['required', 'array', 'min:1'],
            'materias.*.insumoid' => ['required', 'integer', 'exists:insumo,insumoid'],
            'materias.*.cantidad' => ['required', 'numeric', 'min:0.001'],
        ]);

        $lineas = collect($data['materias'])
            ->map(fn (array $m) => [
                'insumoid' => (int) $m['insumoid'],
                'cantidad' => (float) $m['cantidad'],
            ])
            ->all();

        try {
            $lote = $this->loteService->crear(
                $request->user(),
                $data['producto'],
                isset($data['pedidoid']) ? (int) $data['pedidoid'] : null,
                isset($data['cantidad_objetivo']) ? (float) $data['cantidad_objetivo'] : null,
                isset($data['unidadmedidaid']) ? (int) $data['unidadmedidaid'] : null,
                $lineas,
                $data['observaciones'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('procesamiento.show', $lote)
            ->with('success', 'Lote «'.$lote->nombre.'» creado ('.$lote->codigo_lote.'). Se descontó el stock del almacén de planta.');
    }

    public function edit(LoteProduccionPedido $loteProduccion): View
    {
        abort_unless(UsuarioRol::esPlantaOperativo(auth()->user()) || auth()->user()?->hasRole('admin'), 403);

        $loteProduccion->load(['pedido', 'unidadMedida', 'materiasPrimas.insumo.unidadMedida']);

        $pedidoLabel = $loteProduccion->pedido?->numero_solicitud ?? '';
        $productoActual = LoteProduccionNombre::productoDesdeLote($loteProduccion);
        $fase = $this->trazabilidad->resolverFaseActual($loteProduccion);
        $puedeEditarMaterias = $this->loteService->puedeEditarMaterias($loteProduccion);

        $almacenes = AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::PLANTA)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $filtroAlmacenes = array_merge(
            [['value' => '', 'label' => 'Todos los almacenes de planta']],
            $almacenes->map(fn (Almacen $a) => [
                'value' => (string) $a->almacenid,
                'label' => $a->nombre,
            ])->all()
        );

        $estadosPedido = ['pendiente', 'confirmado', 'en produccion', 'aprobado', 'aceptado', 'en_proceso', 'asignado'];
        $existentes = Pedido::query()->distinct()->pluck('estado')->filter()->all();
        $todosEstados = array_values(array_unique(array_merge($estadosPedido, $existentes)));
        $filtroEstadosPedido = array_merge(
            [['value' => '', 'label' => 'Todos los estados']],
            array_map(fn ($e) => [
                'value' => $e,
                'label' => ucfirst(str_replace('_', ' ', (string) $e)),
            ], $todosEstados)
        );

        $unidadesMedida = UnidadMedida::query()->orderBy('nombre')->get();
        $productosLote = LoteProduccionNombre::productosDistintos();

        $materiasIniciales = $loteProduccion->materiasPrimas->map(function ($mp) {
            return [
                'id' => $mp->insumoid,
                'label' => $mp->insumo?->nombre ?? 'Insumo',
                'meta' => 'Stock actual en almacén',
                'stock' => (float) ($mp->insumo?->stock ?? 0) + (float) $mp->cantidad_usada,
                'unidad' => $mp->insumo?->unidadMedida?->abreviatura ?? $mp->insumo?->unidadMedida?->nombre ?? 'ud',
                'cantidad' => (float) $mp->cantidad_usada,
            ];
        })->values();

        return view('procesamiento.edit', compact(
            'loteProduccion',
            'pedidoLabel',
            'productoActual',
            'fase',
            'puedeEditarMaterias',
            'filtroAlmacenes',
            'filtroEstadosPedido',
            'unidadesMedida',
            'productosLote',
            'materiasIniciales'
        ));
    }

    public function update(Request $request, LoteProduccionPedido $loteProduccion): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo($request->user()) || $request->user()?->hasRole('admin'), 403);

        $puedeEditarMaterias = $this->loteService->puedeEditarMaterias($loteProduccion);

        $rules = [
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'cantidad_objetivo' => ['nullable', 'numeric', 'min:0'],
            'unidadmedidaid' => ['nullable', 'integer', 'exists:unidadmedida,unidadmedidaid'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];

        if ($puedeEditarMaterias) {
            $rules['producto'] = ['required', 'string', 'max:100'];
            $rules['materias'] = ['required', 'array', 'min:1'];
            $rules['materias.*.insumoid'] = ['required', 'integer', 'exists:insumo,insumoid'];
            $rules['materias.*.cantidad'] = ['required', 'numeric', 'min:0.001'];
        }

        $request->merge(['pedidoid' => $request->input('pedidoid') ?: null]);

        $data = $request->validate($rules);

        $lineas = null;
        if ($puedeEditarMaterias) {
            $lineas = collect($data['materias'])
                ->map(fn (array $m) => [
                    'insumoid' => (int) $m['insumoid'],
                    'cantidad' => (float) $m['cantidad'],
                ])
                ->all();
        }

        try {
            $lote = $this->loteService->actualizar(
                $request->user(),
                $loteProduccion,
                array_key_exists('pedidoid', $data) ? ($data['pedidoid'] !== null ? (int) $data['pedidoid'] : null) : null,
                array_key_exists('cantidad_objetivo', $data) ? ($data['cantidad_objetivo'] !== null ? (float) $data['cantidad_objetivo'] : null) : null,
                array_key_exists('unidadmedidaid', $data) ? ($data['unidadmedidaid'] !== null ? (int) $data['unidadmedidaid'] : null) : null,
                $data['observaciones'] ?? null,
                $puedeEditarMaterias ? $data['producto'] : null,
                $lineas
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('procesamiento.show', $lote)
            ->with('success', 'Lote «'.$lote->nombre.'» actualizado.');
    }

    public function destroy(LoteProduccionPedido $loteProduccion): RedirectResponse
    {
        abort_unless(UsuarioRol::esPlantaOperativo(auth()->user()) || auth()->user()?->hasRole('admin'), 403);

        $nombre = $loteProduccion->nombre;

        try {
            $this->loteService->eliminar(auth()->user(), $loteProduccion);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('procesamiento.index')
            ->with('success', 'Lote «'.$nombre.'» eliminado. Se revirtió el stock de materias primas.');
    }
}
