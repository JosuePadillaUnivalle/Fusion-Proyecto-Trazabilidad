<?php

namespace App\Services;

use App\Models\AlmacenajeLoteProduccion;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\InventarioPresentacionLote;
use App\Models\Pedido;
use App\Models\PedidoDistribucion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Models\Usuario;
use App\Models\Almacen;
use App\Services\AlmacenCapacidadService;
use App\Support\AlmacenAmbito;
use App\Support\LoteProduccionNombre;
use App\Support\CampoJefeScope;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\EtiquetaDemo;
use App\Support\InsumoCatalogo;
use App\Support\PedidoCatalogo;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\RutaDistribucionCatalogo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReporteCentroService
{
    public function __construct(
        private readonly ReporteDistribucionService $distribucion,
        private readonly AlmacenCapacidadService $capacidad,
        private readonly ProductoPlantaInventarioService $inventarioPlanta,
        private readonly InventarioPresentacionService $inventarioPresentacion,
        private readonly DistribucionRutaService $rutasDistribucion,
    ) {}

    /** @return array{desde: string, hasta: string} */
    public function resolverPeriodo(Request $request, int $diasDefault = 30): array
    {
        $hoy = Carbon::today();
        $desde = $request->filled('fecha_desde')
            ? Carbon::parse($request->string('fecha_desde')->toString())
            : $hoy->copy()->subDays($diasDefault - 1);
        $hasta = $request->filled('fecha_fin')
            ? Carbon::parse($request->string('fecha_fin')->toString())
            : ($request->filled('fecha_hasta')
                ? Carbon::parse($request->string('fecha_hasta')->toString())
                : $hoy->copy());

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
        ];
    }

    public function enviosEstadoPreview(): ?string
    {
        $periodo = $this->resolverPeriodo(request());
        $datos = $this->enviosEstado($periodo['desde'], $periodo['hasta']);

        return ($datos['kpis']['total'] ?? 0) > 0
            ? (string) $datos['kpis']['total'].' en período'
            : 'Sin movimientos';
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function enviosEstado(string $desde, string $hasta, array $filtros = []): array
    {
        $filas = $this->filasEnvioPeriodo($desde, $hasta);
        $filas = $this->filtrarFilasEnvio($filas, $filtros);
        $porEstado = [];

        foreach ($filas as $fila) {
            $clave = $fila['estado_etiqueta'];
            if (! isset($porEstado[$clave])) {
                $porEstado[$clave] = ['estado' => $clave, 'total' => 0, 'cancelados' => 0];
            }
            $porEstado[$clave]['total']++;
            if ($fila['cancelado'] ?? false) {
                $porEstado[$clave]['cancelados']++;
            }
        }

        uasort($porEstado, fn ($a, $b) => $b['total'] <=> $a['total']);
        $total = count($filas);
        $totalCancelados = collect($filas)->where('cancelado', true)->count();

        $dias = max(1, Carbon::parse($desde)->diffInDays(Carbon::parse($hasta)) + 1);
        $anteriorHasta = Carbon::parse($desde)->subDay();
        $anteriorDesde = $anteriorHasta->copy()->subDays($dias - 1);
        $anteriorTotal = count($this->filasEnvioPeriodo($anteriorDesde->toDateString(), $anteriorHasta->toDateString()));
        $variacion = $anteriorTotal > 0
            ? round((($total - $anteriorTotal) / $anteriorTotal) * 100, 1)
            : ($total > 0 ? 100.0 : 0.0);

        $tabla = collect($porEstado)->map(function (array $row) use ($total) {
            $row['porcentaje'] = $total > 0 ? round(($row['total'] / $total) * 100, 1) : 0.0;

            return $row;
        })->values();

        return [
            'periodo' => compact('desde', 'hasta'),
            'kpis' => [
                'total' => $total,
                'variacion' => $variacion,
                'anterior' => $anteriorTotal,
                'estados' => count($porEstado),
                'cancelacion' => $total > 0 ? round(($totalCancelados / $total) * 100, 1) : 0.0,
            ],
            'tabla' => $tabla,
            'detalleMovimientos' => collect($filas)
                ->sortByDesc('fecha_orden')
                ->take(150)
                ->values()
                ->map(fn (array $f) => [
                    'fecha' => $f['fecha'] ?? '—',
                    'canal' => $f['canal'] ?? '—',
                    'referencia' => $f['referencia'] ?? '—',
                    'estado' => $f['estado_etiqueta'] ?? '—',
                    'transportista' => ($f['transportista_nombre'] ?? '') !== '' ? $f['transportista_nombre'] : '—',
                    'origen' => $f['origen'] ?? '—',
                    'destino' => $f['destino'] ?? '—',
                ]),
            'chart' => [
                'labels' => $tabla->pluck('estado')->all(),
                'values' => $tabla->pluck('total')->all(),
            ],
        ];
    }

    public function stockAmbitoPreview(): ?string
    {
        $totalKg = (float) $this->stockOperativoPorAmbito()->sum('stock_kg');

        return $totalKg > 0 ? number_format($totalKg, 0).' kg en red' : 'Sin stock';
    }

    /**
     * Stock operativo real por ámbito (insumos + cosecha + producto planta).
     *
     * @return Collection<int, array{clave: string, etiqueta: string, stock_kg: float, almacenes: int, productos: int, criticos: int}>
     */
    private function stockOperativoPorAmbito(): Collection
    {
        $ambitos = [
            AlmacenAmbito::AGRICOLA => 'Agrícola',
            AlmacenAmbito::PLANTA => 'Planta',
            AlmacenAmbito::MAYORISTA => 'Mayorista',
            AlmacenAmbito::PUNTO_VENTA => 'Punto de venta',
        ];

        return collect($ambitos)->map(function (string $etiqueta, string $clave) {
            $almacenes = Almacen::query()
                ->where('activo', true)
                ->where('ambito', $clave)
                ->orderBy('nombre')
                ->get();

            $filas = $almacenes->map(fn (Almacen $almacen) => $this->filaStockAlmacen($almacen));

            return [
                'clave' => $clave,
                'etiqueta' => $etiqueta,
                'stock_kg' => (float) $filas->sum('stock'),
                'almacenes' => $filas->filter(fn (array $f) => ($f['stock'] ?? 0) > 0)->count(),
                'productos' => (int) $filas->sum('productos'),
                'criticos' => (int) $filas->sum('criticos'),
            ];
        })->values();
    }

    /** @return array{stock: float, productos: int, criticos: int} */
    private function filaStockAlmacen(Almacen $almacen): array
    {
        $stockKg = $this->capacidad->ocupadoKg($almacen);

        $insumosActivos = Insumo::query()
            ->where('almacenid', $almacen->almacenid)
            ->where('stock', '>', 0);

        $productos = (int) (clone $insumosActivos)->count()
            + (int) ProduccionAlmacenamiento::query()
                ->where('almacenid', $almacen->almacenid)
                ->whereNull('fechasalida')
                ->count()
            + (int) AlmacenajeLoteProduccion::query()
                ->where('almacenid', $almacen->almacenid)
                ->whereNull('fecha_retiro')
                ->count();

        $criticos = (int) Insumo::query()
            ->where('almacenid', $almacen->almacenid)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', DB::raw('COALESCE(stockminimo, '.InsumoCatalogo::UMBRAL_ALERTA_STOCK.')'))
            ->count();

        return [
            'stock' => $stockKg,
            'productos' => $productos,
            'criticos' => $criticos,
        ];
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function stockAmbito(?string $ambitoFiltro = null, array $filtros = []): array
    {
        $resumenOperativo = $this->stockOperativoPorAmbito();

        if ($ambitoFiltro) {
            $resumenOperativo = $resumenOperativo->where('clave', $ambitoFiltro)->values();
        }

        $detalleAlmacen = collect();
        $resumenAmbito = [];

        foreach ($resumenOperativo as $bloque) {
            $clave = $bloque['clave'];
            $etiqueta = $bloque['etiqueta'];

            $almacenes = Almacen::query()
                ->where('activo', true)
                ->where('ambito', $clave)
                ->orderBy('nombre')
                ->get();

            foreach ($almacenes as $almacen) {
                $fila = $this->filaStockAlmacen($almacen);
                if ($fila['stock'] <= 0 && $fila['productos'] <= 0) {
                    continue;
                }
                $detalleAlmacen->push([
                    'ambito' => $etiqueta,
                    'almacen' => $almacen->nombre,
                    'stock' => (float) $fila['stock'],
                    'productos' => (int) $fila['productos'],
                    'criticos' => (int) $fila['criticos'],
                ]);
            }

            $filasAmbito = $detalleAlmacen->where('ambito', $etiqueta);
            $resumenAmbito[] = [
                'ambito' => $etiqueta,
                'clave' => $clave,
                'stock' => (float) $filasAmbito->sum('stock'),
                'almacenes' => $filasAmbito->count(),
                'productos' => (int) $filasAmbito->sum('productos'),
                'criticos' => (int) $filasAmbito->sum('criticos'),
            ];
        }

        if (! empty($filtros['solo_criticos'])) {
            $detalleAlmacen = $detalleAlmacen->filter(fn (array $f) => ($f['criticos'] ?? 0) > 0)->values();
            $resumenAmbito = collect($resumenAmbito)
                ->map(function (array $row) use ($detalleAlmacen) {
                    $filasAmbito = $detalleAlmacen->where('ambito', $row['ambito']);
                    $row['stock'] = (float) $filasAmbito->sum('stock');
                    $row['criticos'] = (int) $filasAmbito->sum('criticos');
                    $row['almacenes'] = $filasAmbito->count();
                    $row['productos'] = (int) $filasAmbito->sum('productos');

                    return $row;
                })
                ->filter(fn (array $row) => $detalleAlmacen->where('ambito', $row['ambito'])->isNotEmpty())
                ->values()
                ->all();
        }

        $totalStock = collect($resumenAmbito)->sum('stock');
        $totalCriticos = collect($resumenAmbito)->sum('criticos');

        return [
            'ambitoFiltro' => $ambitoFiltro,
            'kpis' => [
                'stock' => $totalStock,
                'almacenes' => (int) collect($resumenAmbito)->sum('almacenes'),
                'productos' => (int) collect($resumenAmbito)->sum('productos'),
                'criticos' => $totalCriticos,
            ],
            'resumenAmbito' => collect($resumenAmbito),
            'detalleAlmacen' => $detalleAlmacen,
            'chart' => [
                'labels' => collect($resumenAmbito)->pluck('ambito')->all(),
                'values' => collect($resumenAmbito)->pluck('stock')->all(),
            ],
        ];
    }

    public function transportistasPreview(): ?string
    {
        $periodo = $this->resolverPeriodo(request());
        $datos = $this->transportistas($periodo['desde'], $periodo['hasta']);
        $n = (int) ($datos['kpis']['transportistas'] ?? 0);

        return $n > 0 ? $n.' activos' : null;
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function transportistas(string $desde, string $hasta, array $filtros = []): array
    {
        $filas = $this->filasEnvioPeriodo($desde, $hasta);

        if (! empty($filtros['transportista'])) {
            $tid = (int) $filtros['transportista'];
            $filas = array_values(array_filter($filas, fn (array $f) => (int) ($f['transportista_id'] ?? 0) === $tid));
        }

        $ranking = [];

        foreach ($filas as $fila) {
            $tid = (int) ($fila['transportista_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            if (! isset($ranking[$tid])) {
                $ranking[$tid] = [
                    'transportista_id' => $tid,
                    'nombre' => $fila['transportista_nombre'] ?? 'Transportista #'.$tid,
                    'asignaciones' => 0,
                    'completados' => 0,
                    'cancelados' => 0,
                ];
            }
            $ranking[$tid]['asignaciones']++;
            if ($fila['completado'] ?? false) {
                $ranking[$tid]['completados']++;
            }
            if ($fila['cancelado'] ?? false) {
                $ranking[$tid]['cancelados']++;
            }
        }

        $tabla = collect($ranking)->map(function (array $row) {
            $row['eficiencia'] = $row['asignaciones'] > 0
                ? round(($row['completados'] / $row['asignaciones']) * 100, 1)
                : 0.0;

            return $row;
        })->sortByDesc('asignaciones')->values();

        $totalAsignaciones = (int) $tabla->sum('asignaciones');
        $promedio = $tabla->count() > 0 ? round($totalAsignaciones / $tabla->count(), 1) : 0.0;
        $eficienciaGlobal = $totalAsignaciones > 0
            ? round(($tabla->sum('completados') / $totalAsignaciones) * 100, 1)
            : 0.0;

        return [
            'periodo' => compact('desde', 'hasta'),
            'kpis' => [
                'transportistas' => $tabla->count(),
                'asignaciones' => $totalAsignaciones,
                'promedio' => $promedio,
                'eficiencia' => $eficienciaGlobal,
            ],
            'tabla' => $tabla,
            'chart' => [
                'labels' => $tabla->take(8)->pluck('nombre')->map(fn ($n) => Str::limit($n, 18))->all(),
                'values' => $tabla->take(8)->pluck('asignaciones')->all(),
            ],
        ];
    }

    public function trasladosPreview(): ?string
    {
        $n = RutaDistribucion::query()
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->count();

        return $n > 0 ? $n.' traslados' : null;
    }

    /** @param  array<string, mixed>  $filtros
     * @return array<string, mixed>
     */
    public function trasladosPlantaMayorista(string $desde, string $hasta, array $filtros = []): array
    {
        $query = RutaDistribucion::query()
            ->with(['transportista', 'almacenMayoristaDestino', 'almacenPlantaOrigen'])
            ->where('tipo_ruta', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween(DB::raw('DATE(COALESCE(fecha_salida, created_at))'), [$desde, $hasta])
                    ->orWhereBetween(DB::raw('DATE(created_at)'), [$desde, $hasta]);
            });

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (! empty($filtros['almacen_planta'])) {
            $query->where('almacen_planta_origenid', (int) $filtros['almacen_planta']);
        }
        if (! empty($filtros['almacen_destino'])) {
            $query->where('almacen_mayorista_destinoid', (int) $filtros['almacen_destino']);
        }

        $rutas = $query
            ->orderByDesc('rutadistribucionid')
            ->get()
            ->reject(fn (RutaDistribucion $r) => EtiquetaDemo::esDemo($r->codigo ?? '') || EtiquetaDemo::esDemo($r->nombre ?? ''));

        $porEstado = $rutas->groupBy(fn ($r) => RutaDistribucionCatalogo::etiquetaEstado($r->estado))
            ->map(fn (Collection $g, string $estado) => ['estado' => $estado, 'total' => $g->count()])
            ->sortByDesc('total')
            ->values();

        $tabla = $rutas->map(fn (RutaDistribucion $r) => [
            'codigo' => $r->codigo,
            'estado' => RutaDistribucionCatalogo::etiquetaEstado($r->estado),
            'origen' => $this->etiquetaAlmacen($r->almacenPlantaOrigen),
            'destino' => $this->etiquetaAlmacen($r->almacenMayoristaDestino),
            'transportista' => trim(($r->transportista?->nombre ?? '').' '.($r->transportista?->apellido ?? '')) ?: '—',
            'fecha' => $r->fecha_salida?->format('d/m/Y') ?? '—',
            'url' => \App\Support\RutaDistribucionNavegacion::urlVer($r),
        ])->values();

        return [
            'periodo' => compact('desde', 'hasta'),
            'kpis' => [
                'total' => $rutas->count(),
                'completados' => $rutas->where('estado', RutaDistribucionCatalogo::ESTADO_COMPLETADA)->count(),
                'en_ruta' => $rutas->where('estado', RutaDistribucionCatalogo::ESTADO_EN_RUTA)->count(),
                'pendientes' => $rutas->whereIn('estado', [
                    RutaDistribucionCatalogo::ESTADO_PLANIFICADA,
                    RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION,
                ])->count(),
            ],
            'tabla' => $tabla,
            'porEstado' => $porEstado,
            'chart' => [
                'labels' => $porEstado->pluck('estado')->all(),
                'values' => $porEstado->pluck('total')->all(),
            ],
        ];
    }

    public function pedidosPdvPreview(): ?string
    {
        $n = PedidoDistribucion::query()
            ->whereRaw('UPPER(COALESCE(numero_solicitud, "")) NOT LIKE ?', ['%DEMO%'])
            ->whereRaw('UPPER(COALESCE(numero_solicitud, "")) NOT LIKE ?', ['MOD-PED-%'])
            ->count();

        return $n > 0 ? $n.' pedidos' : null;
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function pedidosPdv(string $desde, string $hasta, array $filtros = []): array
    {
        $query = PedidoDistribucion::query()
            ->with('puntoVenta')
            ->whereDate('fechapedido', '>=', $desde)
            ->whereDate('fechapedido', '<=', $hasta);

        if (! empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (! empty($filtros['puntoventaid'])) {
            $query->where('puntoventaid', (int) $filtros['puntoventaid']);
        }

        $pedidos = $query->orderByDesc('fechapedido')->get()
            ->reject(fn (PedidoDistribucion $p) => $this->esPedidoDistribucionDemo($p));

        $porEstado = $pedidos->groupBy(fn ($p) => PedidoDistribucionCatalogo::etiquetaEstado($p->estado))
            ->map(fn (Collection $g, string $estado) => ['estado' => $estado, 'total' => $g->count()])
            ->sortByDesc('total')
            ->values();

        $tabla = $pedidos->map(fn (PedidoDistribucion $p) => [
            'solicitud' => $p->numero_solicitud,
            'punto' => $p->puntoVenta?->nombre ?? '—',
            'estado' => PedidoDistribucionCatalogo::etiquetaEstado($p->estado),
            'fecha' => $p->fechapedido?->format('d/m/Y') ?? '—',
            'url' => route('punto-venta.pedidos.show', $p),
        ])->values();

        return [
            'periodo' => compact('desde', 'hasta'),
            'kpis' => [
                'total' => $pedidos->count(),
                'recibidos' => $pedidos->where('estado', PedidoDistribucionCatalogo::ESTADO_RECIBIDO)->count(),
                'en_transito' => $pedidos->where('estado', PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO)->count(),
                'pendientes' => $pedidos->where('estado', PedidoDistribucionCatalogo::ESTADO_PENDIENTE)->count(),
            ],
            'tabla' => $tabla,
            'porEstado' => $porEstado,
            'chart' => [
                'labels' => $porEstado->pluck('estado')->all(),
                'values' => $porEstado->pluck('total')->all(),
            ],
        ];
    }

    public function productosTerminadosPreview(): ?string
    {
        $datos = $this->productosTerminados();
        $productos = (int) ($datos['kpis']['productos'] ?? 0);

        if ($productos <= 0) {
            return 'Sin producto terminado';
        }

        $stockKg = (float) ($datos['kpis']['stock_kg'] ?? 0);

        return $productos.' referencias · '.number_format($stockKg, 0).' kg';
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function productosTerminados(?string $ambitoFiltro = null, array $filtros = []): array
    {
        $this->inventarioPlanta->sincronizarDesdeAlmacenajes();
        $tipoId = $this->inventarioPlanta->tipoProductoTerminadoId();

        $ambitos = $ambitoFiltro
            ? [$ambitoFiltro]
            : [AlmacenAmbito::PLANTA, AlmacenAmbito::MAYORISTA];

        $query = Insumo::query()
            ->with(['almacen', 'unidadMedida'])
            ->withCount(['presentaciones' => fn ($q) => $q->where('activo', true)])
            ->where('tipoinsumoid', $tipoId)
            ->where('stock', '>', 0)
            ->whereHas('almacen', function ($q) use ($ambitos) {
                $q->where('activo', true)->whereIn('ambito', $ambitos);
            });

        if (! empty($filtros['q'])) {
            $term = '%'.Str::lower($filtros['q']).'%';
            $query->whereRaw('LOWER(nombre) LIKE ?', [$term]);
        }

        $insumos = $query->orderBy('nombre')->get();

        foreach ($insumos as $insumo) {
            if (($insumo->almacen?->ambito ?? '') === AlmacenAmbito::MAYORISTA) {
                $this->inventarioPresentacion->asegurarInventarioDesdeStock(
                    (int) $insumo->almacenid,
                    (int) $insumo->insumoid
                );
            }
        }

        $detallePresentacion = $this->filasInventarioPresentacionProductoTerminado($tipoId, $ambitos, $filtros);
        $detallePlanta = $this->filasAlmacenajePlantaProductoTerminado($ambitos, $filtros);

        if ($insumos->isEmpty() && ($detallePlanta->isNotEmpty() || $detallePresentacion->isNotEmpty())) {
            $this->inventarioPlanta->sincronizarDesdeAlmacenajes();
            $insumos = (clone $query)->orderBy('nombre')->get();
        }

        $tabla = $insumos->map(fn (Insumo $i) => [
            'nombre' => $i->nombre,
            'almacen' => $i->almacen?->nombre ?? '—',
            'ambito' => $this->etiquetaAmbito($i->almacen?->ambito),
            'stock' => (float) $i->stock,
            'stock_kg' => $this->stockInsumoEnKg($i),
            'unidad' => $i->unidadMedida?->abreviatura ?? 'kg',
            'presentaciones' => (int) ($i->presentaciones_count ?? 0),
        ])->values();

        $resumenProducto = $insumos->groupBy(fn (Insumo $i) => Str::lower(trim($i->nombre)))
            ->map(function (Collection $grupo) {
                $primero = $grupo->first();

                return [
                    'nombre' => $primero->nombre,
                    'referencias' => $grupo->count(),
                    'stock_kg' => (float) $grupo->sum(fn (Insumo $i) => $this->stockInsumoEnKg($i)),
                    'planta_kg' => (float) $grupo
                        ->filter(fn (Insumo $i) => ($i->almacen?->ambito ?? '') === AlmacenAmbito::PLANTA)
                        ->sum(fn (Insumo $i) => $this->stockInsumoEnKg($i)),
                    'mayorista_kg' => (float) $grupo
                        ->filter(fn (Insumo $i) => ($i->almacen?->ambito ?? '') === AlmacenAmbito::MAYORISTA)
                        ->sum(fn (Insumo $i) => $this->stockInsumoEnKg($i)),
                ];
            })
            ->sortByDesc('stock_kg')
            ->values();

        $stockKgTotal = (float) $resumenProducto->sum('stock_kg');
        if ($stockKgTotal <= 0) {
            $stockKgTotal = (float) $detallePresentacion->sum('kg') + (float) $detallePlanta->sum('kg');
        }

        $porAmbito = collect($ambitos)->map(function (string $ambito) use ($insumos) {
            $grupo = $insumos->filter(fn (Insumo $i) => ($i->almacen?->ambito ?? '') === $ambito);

            return [
                'ambito' => $this->etiquetaAmbito($ambito),
                'productos' => $grupo->count(),
                'stock_kg' => (float) $grupo->sum(fn (Insumo $i) => $this->stockInsumoEnKg($i)),
            ];
        })->filter(fn (array $r) => $r['productos'] > 0 || $r['stock_kg'] > 0)->values();

        return [
            'ambitoFiltro' => $ambitoFiltro,
            'kpis' => [
                'productos' => $insumos->count(),
                'stock_kg' => $stockKgTotal,
                'stock' => (float) $insumos->sum('stock'),
                'planta' => (int) $insumos->filter(fn (Insumo $i) => ($i->almacen?->ambito ?? '') === AlmacenAmbito::PLANTA)->count(),
                'mayorista' => (int) $insumos->filter(fn (Insumo $i) => ($i->almacen?->ambito ?? '') === AlmacenAmbito::MAYORISTA)->count(),
                'lotes' => $detallePresentacion->count() + $detallePlanta->count(),
                'presentaciones' => $detallePresentacion->pluck('presentacion')->unique()->filter()->count(),
            ],
            'tabla' => $tabla,
            'resumenProducto' => $resumenProducto,
            'detallePresentacion' => $detallePresentacion,
            'detallePlanta' => $detallePlanta,
            'porAmbito' => $porAmbito,
            'chart' => [
                'labels' => $porAmbito->pluck('ambito')->all(),
                'values' => $porAmbito->pluck('stock_kg')->all(),
            ],
        ];
    }

    /** @return array<string, string> */
    public function opcionesEstadoEnvioPeriodo(string $desde, string $hasta): array
    {
        $opciones = ['PDV' => 'Pedidos PDV (todos)'];

        foreach ($this->filasEnvioPeriodo($desde, $hasta) as $fila) {
            $etiqueta = (string) ($fila['estado_etiqueta'] ?? '');
            if ($etiqueta === '' || str_starts_with($etiqueta, 'PDV ·')) {
                continue;
            }
            $opciones[$etiqueta] = $etiqueta;
        }

        ksort($opciones, SORT_NATURAL | SORT_FLAG_CASE);

        return $opciones;
    }

    private function esDemoAsignacion(EnvioAsignacionMultiple $asignacion): bool
    {
        if (EtiquetaDemo::esDemo($asignacion->externo_envio_id ?? '')) {
            return true;
        }

        return $this->esPedidoAgricolaDemo($asignacion->pedido);
    }

    private function esReferenciaOperativaDemo(?string $referencia): bool
    {
        if (EtiquetaDemo::esDemo($referencia)) {
            return true;
        }

        $ref = strtoupper(trim((string) $referencia));

        return str_starts_with($ref, 'MOD-PED-');
    }

    private function esPedidoAgricolaDemo(?Pedido $pedido): bool
    {
        if ($pedido === null) {
            return false;
        }

        if ($this->esReferenciaOperativaDemo($pedido->numero_solicitud)) {
            return true;
        }

        if (str_contains((string) ($pedido->observaciones ?? ''), '[MOD-PEDIDOS]')) {
            return true;
        }

        return EtiquetaDemo::esDemo($pedido->nombre_planta ?? '');
    }

    private function esPedidoDistribucionDemo(PedidoDistribucion $pedido): bool
    {
        return $this->esReferenciaOperativaDemo($pedido->numero_solicitud)
            || str_contains((string) ($pedido->observaciones ?? ''), '[DEMO');
    }

    private function resolverTransportistaPedidoPdv(PedidoDistribucion $pedido): ?Usuario
    {
        if ($pedido->relationLoaded('transportista') === false) {
            $pedido->load('transportista');
        }

        if ($pedido->transportista !== null) {
            return $pedido->transportista;
        }

        $pedido->loadMissing('rutaDistribucion.transportista');
        if ($pedido->rutaDistribucion?->transportista !== null) {
            return $pedido->rutaDistribucion->transportista;
        }

        $ruta = RutaDistribucion::query()
            ->with('transportista')
            ->where(function ($q) use ($pedido) {
                $q->whereHas('pedidos', fn ($p) => $p->where('pedidodistribucionid', $pedido->pedidodistribucionid));
                if (Schema::hasTable('ruta_distribucion_parada')) {
                    $q->orWhereHas('paradas', fn ($p) => $p->where('pedidodistribucionid', $pedido->pedidodistribucionid));
                }
            })
            ->orderByDesc('rutadistribucionid')
            ->first();

        return $ruta?->transportista;
    }

    /** @return array{origen: string, destino: string} */
    private function resolverOrigenDestinoAsignacionAgricola(EnvioAsignacionMultiple $asignacion): array
    {
        $trayecto = EnvioPedidoService::trayectoPartes($asignacion);

        $origen = $trayecto['recogidas'][0] ?? null;
        if ($origen === null || $origen === '' || $origen === '—') {
            $pedido = $asignacion->pedido;
            if ($pedido?->origen_direccion) {
                $origen = trim((string) $pedido->origen_direccion);
            }
        }
        if ($origen === null || $origen === '' || $origen === '—') {
            $origen = $this->etiquetaAlmacen($asignacion->almacen);
        }
        if ($origen === '—') {
            $origen = 'Almacén agrícola';
        }

        $destino = $trayecto['destino'] ?? null;
        if ($destino === null || $destino === '' || $destino === '—') {
            $destino = EnvioPedidoService::etiquetaPlantaDestinoLista($asignacion->pedido);
        }
        if ($destino === null || $destino === '' || $destino === '—') {
            $destino = 'Planta';
        }

        return ['origen' => $origen, 'destino' => $destino];
    }

    /** @param  list<array<string, mixed>>  $filas
     * @return list<array<string, mixed>>
     */
    private function filtrarFilasEnvio(array $filas, array $filtros): array
    {
        $estado = $filtros['estado_envio'] ?? null;
        if (! $estado) {
            return $filas;
        }

        if ($estado === 'PDV') {
            return array_values(array_filter(
                $filas,
                fn (array $f) => str_starts_with((string) ($f['estado_etiqueta'] ?? ''), 'PDV ·')
            ));
        }

        if ($estado === 'Cancelado') {
            return array_values(array_filter(
                $filas,
                fn (array $f) => ($f['cancelado'] ?? false)
                    || str_contains((string) ($f['estado_etiqueta'] ?? ''), 'Cancel')
            ));
        }

        return array_values(array_filter(
            $filas,
            fn (array $f) => ($f['estado_etiqueta'] ?? '') === $estado
        ));
    }

    /** @return list<array<string, mixed>> */
    private function filasEnvioPeriodo(string $desde, string $hasta): array
    {
        $filas = [];
        $user = auth()->user();
        $soloAgricolaJefe = CampoJefeScope::debeAcotar($user);

        $asignacionesQuery = EnvioAsignacionMultiple::query()
            ->with(['pedido', 'transportista', 'almacen', 'ruta.paradas'])
            ->whereHas('pedido', fn ($p) => PedidoCatalogo::aplicarFiltroLogistica($p))
            ->whereDate('fecha_asignacion', '>=', $desde)
            ->whereDate('fecha_asignacion', '<=', $hasta);

        if ($soloAgricolaJefe) {
            CampoJefeScope::aplicarEnEnvioAgricola($asignacionesQuery, $user);
        }

        $asignaciones = $asignacionesQuery->get();

        foreach ($asignaciones as $a) {
            if ($this->esDemoAsignacion($a)) {
                continue;
            }
            $estado = strtolower(trim((string) ($a->estado ?? 'pendiente')));
            $transportista = $a->transportista;
            $trayectoAgricola = $this->resolverOrigenDestinoAsignacionAgricola($a);
            $filas[] = [
                'estado_etiqueta' => EnvioAsignacionEstadoCatalogo::etiqueta($estado),
                'transportista_id' => $a->transportista_usuarioid,
                'transportista_nombre' => $this->nombreTransportista($transportista),
                'cancelado' => $estado === 'cancelado',
                'completado' => in_array($estado, ['recibido_planta', 'entregado', 'completada'], true),
                'fecha' => $a->fecha_asignacion?->format('d/m/Y') ?? '—',
                'fecha_orden' => $a->fecha_asignacion?->timestamp ?? 0,
                'canal' => 'Agrícola → planta',
                'referencia' => $a->pedido?->numero_solicitud ?? ('Asig. #'.$a->envioasignacionmultipleid),
                'origen' => $trayectoAgricola['origen'],
                'destino' => $trayectoAgricola['destino'],
            ];
        }

        if ($soloAgricolaJefe) {
            return $filas;
        }

        $rutas = RutaDistribucion::query()
            ->with([
                'transportista',
                'almacenPlantaOrigen',
                'almacenMayoristaDestino',
                'almacenMayoristaOrigen',
                'almacenOrigen',
                'paradas.puntoVenta',
                'pedidos.puntoVenta',
            ])
            ->where(function ($q) use ($desde, $hasta) {
                $q->whereBetween(DB::raw('DATE(COALESCE(fecha_salida, created_at))'), [$desde, $hasta]);
            })
            ->get();

        foreach ($rutas as $r) {
            if (EtiquetaDemo::esDemo($r->codigo ?? '') || EtiquetaDemo::esDemo($r->nombre ?? '')) {
                continue;
            }
            $estado = strtolower(trim((string) ($r->estado ?? '')));
            $transportista = $r->transportista;
            $canal = ($r->tipo_ruta ?? '') === RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA
                ? 'Planta → mayorista'
                : 'Distribución';
            $trayecto = $this->resolverOrigenDestinoRuta($r);
            $filas[] = [
                'estado_etiqueta' => RutaDistribucionCatalogo::etiquetaEstado($r->estado),
                'transportista_id' => $r->transportista_usuarioid,
                'transportista_nombre' => $this->nombreTransportista($transportista),
                'cancelado' => in_array($estado, [
                    RutaDistribucionCatalogo::ESTADO_CANCELADA,
                    RutaDistribucionCatalogo::ESTADO_RECHAZADA,
                ], true),
                'completado' => $estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA,
                'fecha' => $r->fecha_salida?->format('d/m/Y') ?? $r->created_at?->format('d/m/Y') ?? '—',
                'fecha_orden' => $r->fecha_salida?->timestamp ?? $r->created_at?->timestamp ?? 0,
                'canal' => $canal,
                'referencia' => $r->codigo ?: ($r->nombre ?: 'Ruta #'.$r->rutadistribucionid),
                'origen' => $trayecto['origen'],
                'destino' => $trayecto['destino'],
            ];
        }

        $pedidos = PedidoDistribucion::query()
            ->with(['puntoVenta', 'transportista', 'rutaDistribucion.transportista', 'almacenMayoristaOrigen', 'almacenPlantaOrigen'])
            ->whereDate('fechapedido', '>=', $desde)
            ->whereDate('fechapedido', '<=', $hasta)
            ->get();

        foreach ($pedidos as $p) {
            if ($this->esPedidoDistribucionDemo($p)) {
                continue;
            }
            $estado = strtolower(trim((string) ($p->estado ?? '')));
            $transportista = $this->resolverTransportistaPedidoPdv($p);
            $origen = $this->etiquetaAlmacen($p->almacenMayoristaOrigen);
            if ($origen === '—') {
                $origen = $this->etiquetaAlmacen($p->almacenPlantaOrigen);
            }
            if ($origen === '—') {
                $origen = 'Mayorista';
            }
            $filas[] = [
                'estado_etiqueta' => 'PDV · '.PedidoDistribucionCatalogo::etiquetaEstado($p->estado),
                'transportista_id' => $transportista?->usuarioid ?? $p->transportista_usuarioid ?? $p->rutaDistribucion?->transportista_usuarioid,
                'transportista_nombre' => $this->nombreTransportista($transportista),
                'cancelado' => in_array($estado, [
                    PedidoDistribucionCatalogo::ESTADO_CANCELADO,
                    PedidoDistribucionCatalogo::ESTADO_RECHAZADO,
                ], true),
                'completado' => $estado === PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
                'fecha' => $p->fechapedido?->format('d/m/Y') ?? '—',
                'fecha_orden' => $p->fechapedido?->timestamp ?? 0,
                'canal' => 'Mayorista → PDV',
                'referencia' => $p->numero_solicitud ?? ('Pedido #'.$p->pedidodistribucionid),
                'origen' => $origen,
                'destino' => $p->puntoVenta?->nombre ?? 'Punto de venta',
            ];
        }

        return $filas;
    }

    private function etiquetaAlmacen(?Almacen $almacen): string
    {
        if ($almacen === null) {
            return '—';
        }

        $nombre = trim((string) $almacen->nombre);
        $ubicacion = trim((string) ($almacen->ubicacion ?? ''));

        if ($nombre === '') {
            return $ubicacion !== '' ? $ubicacion : '—';
        }

        return $ubicacion !== '' ? $nombre.', '.$ubicacion : $nombre;
    }

    private function nombreTransportista(?Usuario $usuario): string
    {
        if ($usuario === null) {
            return '';
        }

        return trim($usuario->nombre.' '.($usuario->apellido ?? ''));
    }

    /** @return array{origen: string, destino: string} */
    private function resolverOrigenDestinoRuta(RutaDistribucion $ruta): array
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            return [
                'origen' => $this->etiquetaAlmacen($ruta->almacenPlantaOrigen),
                'destino' => $this->etiquetaAlmacen($ruta->almacenMayoristaDestino),
            ];
        }

        $trayecto = $this->rutasDistribucion->trayectoPartes($ruta);
        $origen = $trayecto['origen'] ?? null;
        if ($origen === null || $origen === '') {
            $origen = $this->etiquetaAlmacen($ruta->almacenMayoristaOrigen ?? $ruta->almacenOrigen);
        }

        $destinos = $trayecto['destinos'] ?? [];
        if ($destinos === []) {
            $paradas = $ruta->paradas
                ?->where('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)
                ->map(function (RutaDistribucionParada $p) {
                    $nombre = trim((string) ($p->destino ?? ''));
                    if ($nombre !== '') {
                        return $nombre;
                    }

                    return $p->puntoVenta?->nombre;
                })
                ->filter()
                ->unique()
                ->values() ?? collect();

            if ($paradas->isEmpty()) {
                $paradas = $ruta->pedidos
                    ?->map(fn (PedidoDistribucion $p) => $p->puntoVenta?->nombre)
                    ->filter()
                    ->unique()
                    ->values() ?? collect();
            }

            if ($paradas->isEmpty()) {
                $destino = $ruta->nombre ?: 'Sin destino definido';
            } elseif ($paradas->count() === 1) {
                $destino = (string) $paradas->first();
            } else {
                $destino = $paradas->first().' (+'.($paradas->count() - 1).' PDV)';
            }
        } elseif (count($destinos) === 1) {
            $destino = $destinos[0];
        } else {
            $destino = $destinos[0].' (+'.(count($destinos) - 1).' PDV)';
        }

        return [
            'origen' => ($origen === '—' || $origen === null || $origen === '') ? 'Mayorista' : $origen,
            'destino' => $destino,
        ];
    }

    private function etiquetaAmbito(?string $ambito): string
    {
        return match ($ambito) {
            AlmacenAmbito::PLANTA => 'Planta',
            AlmacenAmbito::MAYORISTA => 'Mayorista',
            default => $ambito ? ucfirst($ambito) : '—',
        };
    }

    private function stockInsumoEnKg(Insumo $insumo): float
    {
        if (Schema::hasTable('inventario_presentacion_lote')) {
            $kg = (float) InventarioPresentacionLote::query()
                ->where('insumoid', $insumo->insumoid)
                ->where('almacenid', $insumo->almacenid)
                ->sum('cantidad_kg');
            if ($kg > 0) {
                return $kg;
            }
        }

        $abrev = Str::lower(trim((string) ($insumo->unidadMedida?->abreviatura ?? 'kg')));

        return in_array($abrev, ['kg', 'kilo', 'kilogramo', 'kilogramos'], true)
            ? (float) $insumo->stock
            : (float) $insumo->stock;
    }

    /**
     * @param  list<string>  $ambitos
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, array<string, mixed>>
     */
    private function filasInventarioPresentacionProductoTerminado(int $tipoId, array $ambitos, array $filtros): Collection
    {
        if (! Schema::hasTable('inventario_presentacion_lote')) {
            return collect();
        }

        $query = InventarioPresentacionLote::query()
            ->with(['almacen', 'insumo', 'presentacion.tipoEmpaque', 'loteProduccion'])
            ->where(function ($q) {
                $q->where('cantidad_unidades', '>', 0)->orWhere('cantidad_kg', '>', 0);
            })
            ->whereHas('insumo', fn ($q) => $q->where('tipoinsumoid', $tipoId))
            ->whereHas('almacen', fn ($q) => $q->where('activo', true)->whereIn('ambito', $ambitos));

        if (! empty($filtros['q'])) {
            $term = '%'.Str::lower($filtros['q']).'%';
            $query->whereHas('insumo', fn ($q) => $q->whereRaw('LOWER(nombre) LIKE ?', [$term]));
        }

        return $query->orderBy('insumoid')->get()->map(fn (InventarioPresentacionLote $inv) => [
            'producto' => $inv->insumo?->nombre ?? '—',
            'presentacion' => $inv->presentacion?->nombre ?? '—',
            'empaque' => $inv->presentacion?->tipoEmpaque?->nombre ?? ($inv->presentacion?->tipo_envase ?? '—'),
            'lote' => $inv->etiquetaLote(),
            'almacen' => $inv->almacen?->nombre ?? '—',
            'ambito' => $this->etiquetaAmbito($inv->almacen?->ambito),
            'unidades' => (float) $inv->cantidad_unidades,
            'kg' => (float) $inv->cantidad_kg,
        ])->values();
    }

    /**
     * @param  list<string>  $ambitos
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, array<string, mixed>>
     */
    private function filasAlmacenajePlantaProductoTerminado(array $ambitos, array $filtros): Collection
    {
        if (! in_array(AlmacenAmbito::PLANTA, $ambitos, true)) {
            return collect();
        }

        $query = AlmacenajeLoteProduccion::query()
            ->with(['almacen', 'loteProduccionPedido.unidadMedida'])
            ->whereNull('fecha_retiro')
            ->where('cantidad', '>', 0)
            ->whereHas('almacen', fn ($q) => $q->where('activo', true)->where('ambito', AlmacenAmbito::PLANTA));

        $term = ! empty($filtros['q']) ? Str::lower($filtros['q']) : null;

        return $query->orderByDesc('fecha_almacenaje')->get()
            ->map(function (AlmacenajeLoteProduccion $ingreso) {
                $lote = $ingreso->loteProduccionPedido;
                $nombre = $lote ? LoteProduccionNombre::productoDesdeLote($lote) : '—';

                return [
                    'producto' => $nombre,
                    'lote' => $lote?->codigo_lote ?? $lote?->nombre ?? '—',
                    'almacen' => $ingreso->almacen?->nombre ?? '—',
                    'ambito' => 'Planta',
                    'ubicacion' => $ingreso->ubicacion ?: '—',
                    'cantidad' => (float) $ingreso->cantidad,
                    'unidad' => $lote?->unidadMedida?->abreviatura ?? 'kg',
                    'kg' => (float) $ingreso->cantidad,
                    'fecha' => $ingreso->fecha_almacenaje?->format('d/m/Y') ?? '—',
                    '_nombre_busqueda' => Str::lower($nombre),
                ];
            })
            ->when($term, fn (Collection $c) => $c->filter(
                fn (array $f) => str_contains($f['_nombre_busqueda'], $term)
            ))
            ->map(fn (array $f) => collect($f)->except('_nombre_busqueda')->all())
            ->values();
    }
}
