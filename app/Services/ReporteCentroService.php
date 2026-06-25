<?php

namespace App\Services;

use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use App\Models\Almacen;
use App\Support\AlmacenAmbito;
use App\Support\CampoJefeScope;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EtiquetaDemo;
use App\Support\InsumoCatalogo;
use App\Support\PedidoCatalogo;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\RutaDistribucionCatalogo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReporteCentroService
{
    public function __construct(
        private readonly ReporteDistribucionService $distribucion
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
            'chart' => [
                'labels' => $tabla->pluck('estado')->all(),
                'values' => $tabla->pluck('total')->all(),
            ],
        ];
    }

    public function stockAmbitoPreview(): ?string
    {
        $kg = (float) Insumo::query()
            ->join('almacen', 'insumo.almacenid', '=', 'almacen.almacenid')
            ->where('almacen.activo', true)
            ->sum('insumo.stock');

        return $kg > 0 ? number_format($kg, 0).' kg total' : 'Sin stock';
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function stockAmbito(?string $ambitoFiltro = null, array $filtros = []): array
    {
        $ambitos = [
            AlmacenAmbito::AGRICOLA => 'Agrícola',
            AlmacenAmbito::PLANTA => 'Planta',
            AlmacenAmbito::MAYORISTA => 'Mayorista',
            AlmacenAmbito::PUNTO_VENTA => 'Punto de venta',
        ];

        if ($ambitoFiltro && isset($ambitos[$ambitoFiltro])) {
            $ambitos = [$ambitoFiltro => $ambitos[$ambitoFiltro]];
        }

        $resumenAmbito = [];
        $detalleAlmacen = collect();

        foreach ($ambitos as $clave => $etiqueta) {
            $filas = Insumo::query()
                ->select('almacen.nombre as almacen')
                ->selectRaw('SUM(insumo.stock) as stock')
                ->selectRaw('COUNT(insumo.insumoid) as productos')
                ->selectRaw('SUM(CASE WHEN insumo.stock <= COALESCE(insumo.stockminimo, ?) THEN 1 ELSE 0 END) as criticos', [InsumoCatalogo::UMBRAL_ALERTA_STOCK])
                ->join('almacen', 'insumo.almacenid', '=', 'almacen.almacenid')
                ->where('almacen.activo', true)
                ->where('almacen.ambito', $clave)
                ->groupBy('almacen.nombre')
                ->orderBy('almacen.nombre')
                ->get();

            $stockAmbito = (float) $filas->sum('stock');
            $resumenAmbito[] = [
                'ambito' => $etiqueta,
                'clave' => $clave,
                'stock' => $stockAmbito,
                'almacenes' => $filas->count(),
                'productos' => (int) $filas->sum('productos'),
                'criticos' => (int) $filas->sum('criticos'),
            ];

            foreach ($filas as $fila) {
                $detalleAlmacen->push([
                    'ambito' => $etiqueta,
                    'almacen' => $fila->almacen,
                    'stock' => (float) $fila->stock,
                    'productos' => (int) $fila->productos,
                    'criticos' => (int) $fila->criticos,
                ]);
            }
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
        $datos = $this->distribucion->datosReporte();
        $top = $datos['topTransportistas'] ?? collect();

        return $top->isNotEmpty() ? $top->count().' activos' : null;
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
        $n = PedidoDistribucion::query()->count();

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

        $pedidos = $query->orderByDesc('fechapedido')->get();

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
        $tipoId = InsumoCatalogo::tipoProductoTerminadoId();
        if ($tipoId === null) {
            return null;
        }

        $n = Insumo::query()->where('tipoinsumoid', $tipoId)->where('stock', '>', 0)->count();

        return $n > 0 ? $n.' con stock' : null;
    }

    /** @return array<string, mixed> */
    /** @param  array<string, mixed>  $filtros */
    public function productosTerminados(?string $ambitoFiltro = null, array $filtros = []): array
    {
        $tipoId = InsumoCatalogo::tipoProductoTerminadoId();
        $query = Insumo::query()
            ->with(['almacen', 'unidadMedida'])
            ->where('tipoinsumoid', $tipoId)
            ->where('stock', '>', 0)
            ->whereHas('almacen', function ($q) use ($ambitoFiltro) {
                $q->where('activo', true);
                if ($ambitoFiltro) {
                    $q->where('ambito', $ambitoFiltro);
                } else {
                    $q->whereIn('ambito', [AlmacenAmbito::PLANTA, AlmacenAmbito::MAYORISTA]);
                }
            });

        if (! empty($filtros['q'])) {
            $term = '%'.Str::lower($filtros['q']).'%';
            $query->whereRaw('LOWER(nombre) LIKE ?', [$term]);
        }

        $productos = $query->orderBy('nombre')->get();

        $porAmbito = $productos->groupBy(fn ($i) => $i->almacen?->ambito ?? 'otro')
            ->map(function (Collection $g, string $ambito) {
                return [
                    'ambito' => match ($ambito) {
                        AlmacenAmbito::PLANTA => 'Planta',
                        AlmacenAmbito::MAYORISTA => 'Mayorista',
                        default => ucfirst($ambito),
                    },
                    'productos' => $g->count(),
                    'stock' => (float) $g->sum('stock'),
                ];
            })->values();

        $tabla = $productos->map(fn (Insumo $i) => [
            'nombre' => $i->nombre,
            'almacen' => $i->almacen?->nombre ?? '—',
            'ambito' => match ($i->almacen?->ambito ?? '') {
                AlmacenAmbito::PLANTA => 'Planta',
                AlmacenAmbito::MAYORISTA => 'Mayorista',
                default => '—',
            },
            'stock' => (float) $i->stock,
            'unidad' => $i->unidadMedida?->abreviatura ?? 'kg',
        ])->values();

        return [
            'ambitoFiltro' => $ambitoFiltro,
            'kpis' => [
                'productos' => $productos->count(),
                'stock' => (float) $productos->sum('stock'),
                'planta' => (int) $productos->filter(fn ($i) => ($i->almacen?->ambito ?? '') === AlmacenAmbito::PLANTA)->count(),
                'mayorista' => (int) $productos->filter(fn ($i) => ($i->almacen?->ambito ?? '') === AlmacenAmbito::MAYORISTA)->count(),
            ],
            'tabla' => $tabla,
            'porAmbito' => $porAmbito,
            'chart' => [
                'labels' => $porAmbito->pluck('ambito')->all(),
                'values' => $porAmbito->pluck('stock')->all(),
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

        $pedido = $asignacion->pedido;

        return $pedido && EtiquetaDemo::esDemo($pedido->numero_solicitud ?? '');
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
            ->with(['pedido', 'transportista', 'almacen'])
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
            $filas[] = [
                'estado_etiqueta' => EnvioAsignacionEstadoCatalogo::etiqueta($estado),
                'transportista_id' => $a->transportista_usuarioid,
                'transportista_nombre' => trim(($transportista?->nombre ?? '').' '.($transportista?->apellido ?? '')),
                'cancelado' => $estado === 'cancelado',
                'completado' => in_array($estado, ['recibido_planta', 'entregado', 'completada'], true),
            ];
        }

        if ($soloAgricolaJefe) {
            return $filas;
        }

        $rutas = RutaDistribucion::query()
            ->with('transportista')
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
            $filas[] = [
                'estado_etiqueta' => RutaDistribucionCatalogo::etiquetaEstado($r->estado),
                'transportista_id' => $r->transportista_usuarioid,
                'transportista_nombre' => trim(($transportista?->nombre ?? '').' '.($transportista?->apellido ?? '')),
                'cancelado' => in_array($estado, [
                    RutaDistribucionCatalogo::ESTADO_CANCELADA,
                    RutaDistribucionCatalogo::ESTADO_RECHAZADA,
                ], true),
                'completado' => $estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA,
            ];
        }

        $pedidos = PedidoDistribucion::query()
            ->whereDate('fechapedido', '>=', $desde)
            ->whereDate('fechapedido', '<=', $hasta)
            ->get();

        foreach ($pedidos as $p) {
            $estado = strtolower(trim((string) ($p->estado ?? '')));
            $filas[] = [
                'estado_etiqueta' => 'PDV · '.PedidoDistribucionCatalogo::etiquetaEstado($p->estado),
                'transportista_id' => $p->transportista_usuarioid,
                'transportista_nombre' => null,
                'cancelado' => in_array($estado, [
                    PedidoDistribucionCatalogo::ESTADO_CANCELADO,
                    PedidoDistribucionCatalogo::ESTADO_RECHAZADO,
                ], true),
                'completado' => $estado === PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
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
}
