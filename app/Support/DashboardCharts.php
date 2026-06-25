<?php

namespace App\Support;

use App\Models\Actividad;
use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\PedidoDistribucion;
use App\Models\Produccion;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class DashboardCharts
{
    /**
     * Gráfico de producción por cultivo (dashboard admin).
     *
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>, modo: string}
     */
    public static function produccionAdmin(DashboardFiltros $filtros): array
    {
        $coloresChart = ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14'];
        $meses = $filtros->mesesParaGrafico();
        $chartDesde = $meses[0] ?? null;

        $cultivosChart = Produccion::select('cultivo.cultivoid', 'cultivo.nombre')
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->when($chartDesde, fn ($q) => $q->where('produccion.fechacosecha', '>=', Carbon::create($chartDesde['año'], $chartDesde['mes'], 1)))
            ->when($filtros->cultivoId, fn ($q) => $q->where('lote.cultivoid', $filtros->cultivoId))
            ->when($filtros->loteId, fn ($q) => $q->where('lote.loteid', $filtros->loteId))
            ->when($filtros->estadoLoteId, fn ($q) => $q->where('lote.estadolotetipoid', $filtros->estadoLoteId))
            ->groupBy('cultivo.cultivoid', 'cultivo.nombre')
            ->get();

        if (count($meses) === 1) {
            $dias = $filtros->diasParaGrafico();
            if ($dias !== []) {
                return [
                    'labels' => array_column($dias, 'label'),
                    'datasets' => self::datasetsProduccionPorDias($cultivosChart, $dias, $filtros, $coloresChart),
                    'modo' => 'diario',
                ];
            }
        }

        $datasets = [];
        foreach ($cultivosChart as $index => $cultivo) {
            $data = [];
            foreach ($meses as $mes) {
                $data[] = self::sumProduccionCultivoEnMes($cultivo->cultivoid, $mes, $filtros);
            }
            $color = $coloresChart[$index % count($coloresChart)];
            $datasets[] = self::datasetLineaProduccion($cultivo->nombre, $data, $color);
        }

        return [
            'labels' => array_column($meses, 'nombre'),
            'datasets' => $datasets,
            'modo' => 'mensual',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object{cultivoid: int, nombre: string}>  $cultivosChart
     * @param  array<int, array{fecha: string, label: string}>  $dias
     * @param  array<int, string>  $coloresChart
     * @return array<int, array<string, mixed>>
     */
    private static function datasetsProduccionPorDias($cultivosChart, array $dias, DashboardFiltros $filtros, array $coloresChart): array
    {
        $datasets = [];
        foreach ($cultivosChart as $index => $cultivo) {
            $data = [];
            foreach ($dias as $dia) {
                $qProd = Produccion::join('lote', 'produccion.loteid', '=', 'lote.loteid')
                    ->where('lote.cultivoid', $cultivo->cultivoid)
                    ->whereDate('produccion.fechacosecha', $dia['fecha']);
                if ($filtros->loteId) {
                    $qProd->where('lote.loteid', $filtros->loteId);
                }
                if ($filtros->estadoLoteId) {
                    $qProd->where('lote.estadolotetipoid', $filtros->estadoLoteId);
                }
                $data[] = (float) $qProd->sum('produccion.cantidad');
            }
            $color = $coloresChart[$index % count($coloresChart)];
            $datasets[] = self::datasetLineaProduccion($cultivo->nombre, $data, $color);
        }

        return $datasets;
    }

    /**
     * @param  array{mes: int, año: int, nombre: string}  $mes
     */
    private static function sumProduccionCultivoEnMes(int $cultivoId, array $mes, DashboardFiltros $filtros): float
    {
        $qProd = Produccion::join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->where('lote.cultivoid', $cultivoId)
            ->whereMonth('produccion.fechacosecha', $mes['mes'])
            ->whereYear('produccion.fechacosecha', $mes['año']);
        if ($filtros->loteId) {
            $qProd->where('lote.loteid', $filtros->loteId);
        }
        if ($filtros->estadoLoteId) {
            $qProd->where('lote.estadolotetipoid', $filtros->estadoLoteId);
        }

        return (float) $qProd->sum('produccion.cantidad');
    }

    /**
     * @param  array<int, float>  $data
     * @return array<string, mixed>
     */
    private static function datasetLineaProduccion(string $nombreCultivo, array $data, string $color): array
    {
        return [
            'label' => $nombreCultivo.' (kg)',
            'data' => $data,
            'borderColor' => $color,
            'backgroundColor' => $color.'20',
            'borderWidth' => 3,
            'fill' => true,
            'tension' => 0.4,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraPlanta(DashboardFiltros $filtros): array
    {
        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');

        $pedidosMes = self::conteoPorMes(Pedido::query(), 'fechapedido', $meses, $filtros);
        $asignacionesMes = self::conteoPorMes(EnvioAsignacionMultiple::query(), 'fecha_asignacion', $meses, $filtros);

        $estadosAsig = EnvioAsignacionMultiple::query()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->when($filtros->tieneRango(), fn ($q) => $filtros->aplicarFecha($q, 'fecha_asignacion'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $distribucion = PedidoDistribucion::query()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->when($filtros->tieneRango(), fn ($q) => $filtros->aplicarFecha($q, 'fechapedido'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        return [
            'pedidosMes' => [
                'labels' => $labels,
                'data' => $pedidosMes,
                'label' => 'Pedidos recibidos',
                'color' => '#22c55e',
            ],
            'asignacionesMes' => [
                'labels' => $labels,
                'data' => $asignacionesMes,
                'label' => 'Asignaciones logísticas',
                'color' => '#3b82f6',
            ],
            'estadosAsignacion' => self::doughnutDesdeFilas($estadosAsig, 'estado', [
                'asignado' => 'Asignado',
                'en_ruta' => 'En ruta',
                'entregado' => 'Entregado',
                'cancelado' => 'Cancelado',
            ], ['#f59e0b', '#3b82f6', '#22c55e', '#94a3b8']),
            'distribucionEstados' => self::doughnutDesdeFilas(
                $distribucion,
                'estado',
                PedidoDistribucionCatalogo::etiquetasEstado(),
                ['#f59e0b', '#22c55e', '#0ea5e9', '#10b981', '#ef4444', '#94a3b8'],
            ),
            'incidentesAbiertos' => IncidenteEnvio::query()->where('estado', 'abierto')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraTransportista(DashboardFiltros $filtros, ?int $transportistaId = null, bool $todos = false): array
    {
        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');
        $base = EnvioAsignacionMultiple::query();
        if ($todos) {
            $base->whereNotNull('transportista_usuarioid');
        } elseif ($transportistaId) {
            $base->where('transportista_usuarioid', $transportistaId);
        }

        $asignadosMes = self::conteoPorMes((clone $base), 'fecha_asignacion', $meses, $filtros);
        $entregadosMes = [];
        foreach ($meses as $mes) {
            $q = (clone $base)
                ->where('estado', 'entregado')
                ->whereMonth('fecha_asignacion', $mes['mes'])
                ->whereYear('fecha_asignacion', $mes['año']);
            if ($filtros->tieneRango()) {
                $filtros->aplicarFecha($q, 'fecha_asignacion');
            }
            $entregadosMes[] = $q->count();
        }

        $estados = (clone $base)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->when($filtros->tieneRango(), fn ($q) => $filtros->aplicarFecha($q, 'fecha_asignacion'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $productividadMes = [];
        foreach ($meses as $i => $mes) {
            $total = $asignadosMes[$i] ?? 0;
            $ent = $entregadosMes[$i] ?? 0;
            $productividadMes[] = $total > 0 ? round(($ent / $total) * 100, 1) : 0;
        }

        return [
            'asignacionesMes' => [
                'labels' => $labels,
                'data' => $asignadosMes,
                'label' => 'Envíos asignados',
                'color' => '#ea580c',
            ],
            'entregasMes' => [
                'labels' => $labels,
                'data' => $entregadosMes,
                'label' => 'Entregas completadas',
                'color' => '#22c55e',
            ],
            'productividadMes' => [
                'labels' => $labels,
                'data' => $productividadMes,
                'label' => 'Tasa de entrega (%)',
                'color' => '#7c3aed',
            ],
            'estadosAsignacion' => self::doughnutDesdeFilas($estados, 'estado', [
                'asignado' => 'Por recoger',
                'en_ruta' => 'En camino',
                'entregado' => 'Entregado',
                'cancelado' => 'Cancelado',
            ], ['#f59e0b', '#3b82f6', '#22c55e', '#94a3b8']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraJefeAgricola(DashboardFiltros $filtros, ?int $usuarioId = null, bool $todos = false, ?Usuario $viewer = null): array
    {
        $viewer ??= auth()->user();
        $acotarEquipo = CampoJefeScope::debeAcotar($viewer) && ! $todos && ! $usuarioId;

        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');

        $cosechasMes = [];
        foreach ($meses as $mes) {
            $q = Produccion::query()
                ->whereMonth('fechacosecha', $mes['mes'])
                ->whereYear('fechacosecha', $mes['año']);
            $filtros->aplicarCultivoEnLote($q);
            if ($usuarioId && ! $todos) {
                $q->whereHas('lote', fn ($l) => $l->where('usuarioid', $usuarioId));
            } elseif ($acotarEquipo) {
                CampoJefeScope::aplicarEnRelacionLote($q, $viewer);
            }
            if ($filtros->tieneRango()) {
                $filtros->aplicarFecha($q, 'fechacosecha');
            }
            $cosechasMes[] = (float) $q->sum('cantidad');
        }

        $actividadesMes = self::conteoPorMes(Actividad::query(), 'fechainicio', $meses, $filtros, function ($q) use ($filtros, $usuarioId, $todos, $acotarEquipo, $viewer) {
            $filtros->aplicarCultivoEnLote($q);
            if ($usuarioId && ! $todos) {
                $q->whereHas('lote', fn ($l) => $l->where('usuarioid', $usuarioId));
            } elseif ($acotarEquipo) {
                CampoJefeScope::aplicarEnRelacionLote($q, $viewer);
            }
        });

        $lotesEstado = Lote::query()
            ->select('estadolote_tipo.nombre', DB::raw('COUNT(*) as total'))
            ->join('estadolote_tipo', 'lote.estadolotetipoid', '=', 'estadolote_tipo.estadolotetipoid')
            ->when($filtros->cultivoId, fn ($q) => $q->where('lote.cultivoid', $filtros->cultivoId))
            ->when($filtros->loteId, fn ($q) => $q->where('lote.loteid', $filtros->loteId))
            ->when($filtros->estadoLoteId, fn ($q) => $q->where('lote.estadolotetipoid', $filtros->estadoLoteId))
            ->when($usuarioId && ! $todos, fn ($q) => $q->where('lote.usuarioid', $usuarioId))
            ->when($acotarEquipo, fn ($q) => CampoJefeScope::aplicarEnLote($q, $viewer))
            ->groupBy('estadolote_tipo.nombre')
            ->orderByDesc('total')
            ->get();

        $topCultivos = Produccion::select('cultivo.nombre', DB::raw('SUM(produccion.cantidad) as total'))
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid');
        $filtros->aplicarFecha($topCultivos, 'produccion.fechacosecha');
        if ($filtros->cultivoId) {
            $topCultivos->where('lote.cultivoid', $filtros->cultivoId);
        }
        if ($usuarioId && ! $todos) {
            $topCultivos->where('lote.usuarioid', $usuarioId);
        } elseif ($acotarEquipo) {
            $topCultivos->whereIn('lote.usuarioid', CampoJefeScope::idsEquipo($viewer));
        }
        $topCultivos = $topCultivos->groupBy('cultivo.nombre')->orderByDesc('total')->limit(6)->get();

        return [
            'cosechasMes' => [
                'labels' => $labels,
                'data' => $cosechasMes,
                'label' => 'Cosecha (kg)',
                'color' => '#22c55e',
            ],
            'actividadesMes' => [
                'labels' => $labels,
                'data' => $actividadesMes,
                'label' => 'Actividades registradas',
                'color' => '#0ea5e9',
            ],
            'lotesEstado' => self::doughnutDesdeFilas(
                $lotesEstado,
                'nombre',
                [],
                ['#22c55e', '#f59e0b', '#3b82f6', '#8b5cf6', '#94a3b8', '#ef4444'],
            ),
            'topCultivos' => [
                'labels' => $topCultivos->pluck('nombre')->all(),
                'data' => $topCultivos->pluck('total')->map(fn ($v) => (float) $v)->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraAgricultor(Usuario $user, DashboardFiltros $filtros): array
    {
        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');
        $uid = (int) $user->usuarioid;

        $completadasMes = [];
        $pendientesMes = [];
        foreach ($meses as $mes) {
            $base = Actividad::query()->whereHas('lote', fn ($q) => $q->where('usuarioid', $uid));
            if ($filtros->loteId) {
                $base->where('loteid', $filtros->loteId);
            }
            $completadasMes[] = (clone $base)
                ->whereNotNull('fechafin')
                ->whereMonth('fechafin', $mes['mes'])
                ->whereYear('fechafin', $mes['año'])
                ->count();
            $pendientesMes[] = (clone $base)
                ->whereNull('fechafin')
                ->whereMonth('fechainicio', $mes['mes'])
                ->whereYear('fechainicio', $mes['año'])
                ->count();
        }

        $lotesEstado = Lote::query()
            ->where('usuarioid', $uid)
            ->when($filtros->loteId, fn ($q) => $q->where('loteid', $filtros->loteId))
            ->select('estadolote_tipo.nombre', DB::raw('COUNT(*) as total'))
            ->join('estadolote_tipo', 'lote.estadolotetipoid', '=', 'estadolote_tipo.estadolotetipoid')
            ->groupBy('estadolote_tipo.nombre')
            ->orderByDesc('total')
            ->get();

        $tiposActividad = Actividad::query()
            ->whereHas('lote', fn ($q) => $q->where('usuarioid', $uid))
            ->when($filtros->loteId, fn ($q) => $q->where('loteid', $filtros->loteId))
            ->join('tipoactividad', 'actividad.tipoactividadid', '=', 'tipoactividad.tipoactividadid')
            ->select('tipoactividad.nombre', DB::raw('COUNT(*) as total'));
        if ($filtros->tieneRango()) {
            $filtros->aplicarFecha($tiposActividad, 'actividad.fechainicio');
        }
        $tiposActividad = $tiposActividad->groupBy('tipoactividad.nombre')->orderByDesc('total')->limit(6)->get();

        return [
            'actividadesMes' => [
                'labels' => $labels,
                'completadas' => $completadasMes,
                'pendientes' => $pendientesMes,
            ],
            'lotesEstado' => self::doughnutDesdeFilas(
                $lotesEstado,
                'nombre',
                [],
                ['#22c55e', '#f59e0b', '#3b82f6', '#8b5cf6', '#94a3b8'],
            ),
            'tiposActividad' => [
                'labels' => $tiposActividad->pluck('nombre')->all(),
                'data' => $tiposActividad->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraMayorista(DashboardFiltros $filtros, ?Usuario $usuario = null, bool $todos = false): array
    {
        $base = PedidoDistribucion::query();
        if ($usuario && ! $todos) {
            $almacenIds = MayoristaAccess::idsAlmacenesMayorista($usuario);
            if ($almacenIds !== []) {
                $base->whereIn('almacen_mayorista_origenid', $almacenIds);
            } else {
                $base->whereRaw('1 = 0');
            }
        }
        $seriePedidos = self::serieTemporalConteo($base, 'fechapedido', $filtros);
        $serieTransito = self::serieTemporalConteo(
            (clone $base)->where('estado', PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO),
            'fechapedido',
            $filtros,
        );

        $estados = (clone $base)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->when($filtros->tieneRango(), fn ($q) => $filtros->aplicarFecha($q, 'fechapedido'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $porPdv = (clone $base)
            ->join('punto_venta', 'pedido_distribucion.puntoventaid', '=', 'punto_venta.puntoventaid')
            ->select('punto_venta.nombre', DB::raw('COUNT(*) as total'));
        if ($filtros->tieneRango()) {
            $filtros->aplicarFecha($porPdv, 'pedido_distribucion.fechapedido');
        }
        $porPdv = $porPdv->groupBy('punto_venta.nombre')->orderByDesc('total')->limit(6)->get();

        return [
            'pedidosMes' => [
                'labels' => $seriePedidos['labels'],
                'data' => $seriePedidos['data'],
                'label' => 'Pedidos recibidos',
                'color' => '#0d9488',
                'modo' => $seriePedidos['modo'],
            ],
            'enTransitoMes' => [
                'labels' => $serieTransito['labels'],
                'data' => $serieTransito['data'],
                'label' => 'En tránsito',
                'color' => '#22c55e',
                'modo' => $serieTransito['modo'],
            ],
            'estadosPedido' => self::doughnutDesdeFilas(
                $estados,
                'estado',
                PedidoDistribucionCatalogo::etiquetasEstado(),
                ['#f59e0b', '#22c55e', '#0ea5e9', '#10b981', '#ef4444', '#94a3b8'],
            ),
            'porPdv' => [
                'labels' => $porPdv->pluck('nombre')->all(),
                'data' => $porPdv->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paraMinorista(DashboardFiltros $filtros, ?int $minoristaId = null, bool $todos = false): array
    {
        $puntosQuery = PuntoVenta::query();
        if ($todos) {
            $puntosQuery->whereNotNull('usuarioid');
        } elseif ($minoristaId) {
            $puntosQuery->where('usuarioid', $minoristaId);
        }
        $puntosIds = $puntosQuery->pluck('puntoventaid');
        $base = PedidoDistribucion::query()->whereIn('pedido_distribucion.puntoventaid', $puntosIds);
        $seriePedidos = self::serieTemporalConteo($base, 'fechapedido', $filtros);

        $estados = (clone $base)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->when($filtros->tieneRango(), fn ($q) => $filtros->aplicarFecha($q, 'fechapedido'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $porPunto = (clone $base)
            ->join('punto_venta', 'pedido_distribucion.puntoventaid', '=', 'punto_venta.puntoventaid')
            ->select('punto_venta.nombre', DB::raw('COUNT(*) as total'));
        if ($filtros->tieneRango()) {
            $filtros->aplicarFecha($porPunto, 'pedido_distribucion.fechapedido');
        }
        $porPunto = $porPunto->groupBy('punto_venta.nombre')->orderByDesc('total')->limit(6)->get();

        return [
            'pedidosMes' => [
                'labels' => $seriePedidos['labels'],
                'data' => $seriePedidos['data'],
                'label' => 'Pedidos solicitados',
                'color' => '#8b5cf6',
                'modo' => $seriePedidos['modo'],
            ],
            'estadosPedido' => self::doughnutDesdeFilas(
                $estados,
                'estado',
                PedidoDistribucionCatalogo::etiquetasEstado(),
                ['#f59e0b', '#22c55e', '#0ea5e9', '#10b981', '#ef4444', '#94a3b8'],
            ),
            'porPunto' => [
                'labels' => $porPunto->pluck('nombre')->all(),
                'data' => $porPunto->pluck('total')->map(fn ($v) => (int) $v)->all(),
            ],
        ];
    }

    /**
     * Serie diaria (un solo mes) o mensual según el filtro activo.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return array{labels: array<int, string>, data: array<int, int>, modo: string}
     */
    private static function serieTemporalConteo($query, string $column, DashboardFiltros $filtros): array
    {
        $meses = $filtros->mesesParaGrafico();
        $dias = $filtros->diasParaGrafico();

        if (count($meses) === 1 && $dias !== []) {
            return [
                'labels' => array_column($dias, 'label'),
                'data' => self::conteoPorDias($query, $column, $dias, $filtros),
                'modo' => 'diario',
            ];
        }

        return [
            'labels' => array_column($meses, 'nombre'),
            'data' => self::conteoPorMes($query, $column, $meses, $filtros),
            'modo' => 'mensual',
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, array{fecha: string, label: string}>  $dias
     * @return array<int, int>
     */
    private static function conteoPorDias($query, string $column, array $dias, DashboardFiltros $filtros): array
    {
        $data = [];
        foreach ($dias as $dia) {
            $q = (clone $query)->whereDate($column, $dia['fecha']);
            if ($filtros->tieneRango()) {
                $filtros->aplicarFecha($q, $column);
            }
            $data[] = $q->count();
        }

        return $data;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, array{mes: int, año: int, nombre: string}>  $meses
     * @param  callable|null  $extra
     * @return array<int, int>
     */
    private static function conteoPorMes($query, string $column, array $meses, DashboardFiltros $filtros, ?callable $extra = null): array
    {
        $data = [];
        foreach ($meses as $mes) {
            $q = (clone $query)
                ->whereMonth($column, $mes['mes'])
                ->whereYear($column, $mes['año']);
            if ($extra) {
                $extra($q);
            }
            if ($filtros->tieneRango()) {
                $filtros->aplicarFecha($q, $column);
            }
            $data[] = $q->count();
        }

        return $data;
    }

    /**
     * @param  iterable<int, object>  $filas
     * @param  array<string, string>  $etiquetas
     * @param  array<int, string>  $colores
     * @return array{labels: array<int, string>, data: array<int, int>, colors: array<int, string>}
     */
    private static function doughnutDesdeFilas(iterable $filas, string $campo, array $etiquetas, array $colores): array
    {
        $labels = [];
        $data = [];
        $colors = [];
        $i = 0;
        foreach ($filas as $fila) {
            $raw = (string) ($fila->{$campo} ?? '');
            $labels[] = $etiquetas[$raw] ?? ucfirst(str_replace('_', ' ', $raw));
            $data[] = (int) ($fila->total ?? 0);
            $colors[] = $colores[$i % count($colores)];
            $i++;
        }

        return compact('labels', 'data', 'colors');
    }
}
