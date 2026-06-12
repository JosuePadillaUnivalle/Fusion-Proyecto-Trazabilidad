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
use Illuminate\Support\Facades\DB;

final class DashboardCharts
{
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
    public static function paraTransportista(Usuario $user, DashboardFiltros $filtros): array
    {
        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');
        $base = EnvioAsignacionMultiple::query()->where('transportista_usuarioid', $user->usuarioid);

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
    public static function paraJefeAgricola(DashboardFiltros $filtros): array
    {
        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');

        $cosechasMes = [];
        foreach ($meses as $mes) {
            $q = Produccion::query()
                ->whereMonth('fechacosecha', $mes['mes'])
                ->whereYear('fechacosecha', $mes['año']);
            $filtros->aplicarCultivoEnLote($q);
            if ($filtros->tieneRango()) {
                $filtros->aplicarFecha($q, 'fechacosecha');
            }
            $cosechasMes[] = (float) $q->sum('cantidad');
        }

        $actividadesMes = self::conteoPorMes(Actividad::query(), 'fechainicio', $meses, $filtros, function ($q) use ($filtros) {
            $filtros->aplicarCultivoEnLote($q);
        });

        $lotesEstado = Lote::query()
            ->select('estadolote_tipo.nombre', DB::raw('COUNT(*) as total'))
            ->join('estadolote_tipo', 'lote.estadolotetipoid', '=', 'estadolote_tipo.estadolotetipoid')
            ->when($filtros->cultivoId, fn ($q) => $q->where('lote.cultivoid', $filtros->cultivoId))
            ->when($filtros->loteId, fn ($q) => $q->where('lote.loteid', $filtros->loteId))
            ->when($filtros->estadoLoteId, fn ($q) => $q->where('lote.estadolotetipoid', $filtros->estadoLoteId))
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
            $base = Actividad::query()->where('usuarioid', $uid);
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
            ->where('usuarioid', $uid)
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
    public static function paraMinorista(Usuario $user, DashboardFiltros $filtros): array
    {
        $meses = $filtros->mesesParaGrafico();
        $labels = array_column($meses, 'nombre');
        $puntosIds = PuntoVenta::query()->where('usuarioid', $user->usuarioid)->pluck('puntoventaid');

        $pedidosMes = self::conteoPorMes(
            PedidoDistribucion::query()->whereIn('puntoventaid', $puntosIds),
            'fechapedido',
            $meses,
            $filtros,
        );

        $estados = PedidoDistribucion::query()
            ->whereIn('puntoventaid', $puntosIds)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->when($filtros->tieneRango(), fn ($q) => $filtros->aplicarFecha($q, 'fechapedido'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $porPunto = PedidoDistribucion::query()
            ->whereIn('pedido_distribucion.puntoventaid', $puntosIds)
            ->join('punto_venta', 'pedido_distribucion.puntoventaid', '=', 'punto_venta.puntoventaid')
            ->select('punto_venta.nombre', DB::raw('COUNT(*) as total'));
        if ($filtros->tieneRango()) {
            $filtros->aplicarFecha($porPunto, 'pedido_distribucion.fechapedido');
        }
        $porPunto = $porPunto->groupBy('punto_venta.nombre')->orderByDesc('total')->limit(6)->get();

        return [
            'pedidosMes' => [
                'labels' => $labels,
                'data' => $pedidosMes,
                'label' => 'Pedidos solicitados',
                'color' => '#8b5cf6',
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
