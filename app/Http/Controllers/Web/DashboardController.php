<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clima;
use App\Models\Lote;
use App\Models\Produccion;
use App\Support\DashboardPresentacion;
use App\Models\Venta;
use App\Models\Actividad;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\RutaMultiEntrega;
use App\Models\Usuario;
use App\Models\Cultivo;
use App\Models\AlmacenMovimiento;
use App\Models\AlmacenProducto;
use App\Models\InventarioAlmacenEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private function isAdmin($user): bool
    {
        return (bool) ($user && ($user->hasRole('admin') || $user->hasRole('Admin')));
    }

    public function index()
    {
        $user = auth()->user();
        if ($this->isAdmin($user)) {
            // El admin siempre debe ver el dashboard principal ejecutivo.
            // Evita que caiga en paneles operativos por permisos comodín (*).
        } elseif ($user && $user->can('panel_planta.view')) {
            return view('dashboard.roles.planta', $this->buildPlantaData());
        } elseif ($user && $user->can('panel_transportista.view')) {
            return view('dashboard.roles.transportista', $this->buildTransportistaData($user));
        } elseif ($user && $user->can('panel_almacen.view')) {
            return view('dashboard.roles.almacen', $this->buildAlmacenData($user));
        }

        // ========================================
        // ESTADÍSTICAS PRINCIPALES (4 small-boxes)
        // ========================================
        $stats = [
            // Lotes activos (todos excepto cosechado y en descanso)
            'lotes_activos' => Lote::whereHas('estadoTipo', function($q) {
                $q->whereNotIn('nombre', ['cosechado', 'en descanso']);
            })->count(),
            
            // Si no hay relación, contar todos los lotes
            'total_lotes' => Lote::count(),
            
            // Producción del mes en KG
            'produccion_mes_kg' => Produccion::whereMonth('fechacosecha', now()->month)
                ->whereYear('fechacosecha', now()->year)
                ->sum('cantidad'),
            
            // Insumos con stock bajo (manejar NULL en stockminimo)
            'insumos_stock_bajo' => Insumo::whereRaw('stock <= COALESCE(stockminimo, 10)')->count(),
            
            // Ventas del mes
            'ventas_mes' => Venta::whereMonth('fechaventa', now()->month)
                ->whereYear('fechaventa', now()->year)
                ->selectRaw('COALESCE(SUM(cantidad * preciounitario), 0) as total')
                ->value('total') ?? 0,
            
            // Para resumen estadístico
            'hectareas_totales' => Lote::sum('superficie') ?? 0,
            'total_actividades' => Actividad::count(),
            'usuarios' => Usuario::count(),
            'total_insumos' => Insumo::count(),
        ];
        
        // Si lotes_activos es 0 pero hay lotes, mostrar total de lotes
        if ($stats['lotes_activos'] == 0 && $stats['total_lotes'] > 0) {
            $stats['lotes_activos'] = $stats['total_lotes'];
        }

        // ========================================
        // GRÁFICO: Producción últimos 6 meses por cultivo
        // ========================================
        $meses = [];
        $mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        // Generar últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $meses[] = [
                'mes' => $fecha->month,
                'año' => $fecha->year,
                'nombre' => $mesesNombres[$fecha->month - 1]
            ];
        }

        // Obtener cultivos con producción
        $cultivosConProduccion = Produccion::select('cultivo.cultivoid', 'cultivo.nombre')
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->where('produccion.fechacosecha', '>=', now()->subMonths(6))
            ->groupBy('cultivo.cultivoid', 'cultivo.nombre')
            ->get();

        $coloresChart = ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14'];
        $datasets = [];

        foreach ($cultivosConProduccion as $index => $cultivo) {
            $data = [];
            foreach ($meses as $mes) {
                $cantidad = Produccion::join('lote', 'produccion.loteid', '=', 'lote.loteid')
                    ->where('lote.cultivoid', $cultivo->cultivoid)
                    ->whereMonth('produccion.fechacosecha', $mes['mes'])
                    ->whereYear('produccion.fechacosecha', $mes['año'])
                    ->sum('produccion.cantidad');
                $data[] = (float) $cantidad;
            }
            
            $color = $coloresChart[$index % count($coloresChart)];
            $datasets[] = [
                'label' => $cultivo->nombre . ' (kg)',
                'data' => $data,
                'borderColor' => $color,
                'backgroundColor' => $color . '20',
                'borderWidth' => 3,
                'fill' => true,
                'tension' => 0.4
            ];
        }

        $chartData = [
            'labels' => array_column($meses, 'nombre'),
            'datasets' => $datasets
        ];

        // ========================================
        // ACTIVIDADES RECIENTES
        // ========================================
        $actividadesRecientes = Actividad::with(['lote', 'usuario', 'tipoActividad'])
            ->orderBy('fechainicio', 'desc')
            ->limit(5)
            ->get();

        // ========================================
        // INSUMOS CON STOCK BAJO (para alertas)
        // ========================================
        $insumosStockBajo = Insumo::with('tipo')
            ->whereRaw('stock <= COALESCE(stockminimo, 10)')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // ========================================
        // LOTES POR ESTADO (info-boxes)
        // ========================================
        $lotesPorEstado = Lote::select('estadolote_tipo.nombre', DB::raw('COUNT(*) as total'))
            ->join('estadolote_tipo', 'lote.estadolotetipoid', '=', 'estadolote_tipo.estadolotetipoid')
            ->groupBy('estadolote_tipo.nombre')
            ->get();

        // ========================================
        // TOP CULTIVOS POR PRODUCCIÓN (barras de progreso)
        // ========================================
        $topCultivos = Produccion::select(
                'cultivo.nombre',
                DB::raw('SUM(produccion.cantidad) as total')
            )
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->groupBy('cultivo.nombre')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        $resumenEstadistico = DashboardPresentacion::resumenEstadistico($stats);

        return view('home', compact(
            'stats',
            'chartData',
            'actividadesRecientes',
            'insumosStockBajo',
            'lotesPorEstado',
            'topCultivos',
            'resumenEstadistico'
        ));
    }

    public function panelPlanta()
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('panel_planta.view') || $this->isAdmin($user)), 403);

        return view('dashboard.roles.planta', $this->buildPlantaData());
    }

    public function panelTransportista()
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('panel_transportista.view') || $this->isAdmin($user)), 403);

        return view('dashboard.roles.transportista', $this->buildTransportistaData($user));
    }

    public function panelAlmacen()
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('panel_almacen.view') || $this->isAdmin($user)), 403);

        return view('dashboard.roles.almacen', $this->buildAlmacenData($user));
    }

    // ========================================
    // API: Obtener clima actual (OpenWeather)
    // ========================================
    public function getClima(Request $request)
    {
        $ciudad = (string) $request->get('ciudad', 'Santa Cruz de la Sierra');
        $apiKey = config('services.weather.key', env('OPENWEATHER_API_KEY', ''));

        if (is_string($apiKey) && trim($apiKey) !== '') {
            try {
                $response = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => "{$ciudad},BO",
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang' => 'es',
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return response()->json([
                        'success' => true,
                        'temperatura' => round($data['main']['temp']),
                        'sensacion' => round($data['main']['feels_like']),
                        'humedad' => $data['main']['humidity'],
                        'descripcion' => ucfirst($data['weather'][0]['description']),
                        'icono' => $data['weather'][0]['icon'],
                        'viento' => round($data['wind']['speed'] * 3.6),
                        'ciudad' => $data['name'],
                        'fuente' => 'openweather',
                    ]);
                }
            } catch (\Exception $e) {
                // Continúa con fallback local
            }
        }

        return response()->json($this->climaFallbackLocal());
    }

    /**
     * @return array<string, mixed>
     */
    private function climaFallbackLocal(): array
    {
        if (Schema::hasTable('clima')) {
            $row = Clima::query()->orderByDesc('fecha')->first();
            if ($row) {
                return [
                    'success' => true,
                    'temperatura' => round((float) ($row->temperatura ?? 26)),
                    'humedad' => (int) round((float) ($row->humedad ?? 60)),
                    'descripcion' => $row->descripcion ?: 'Condición registrada en campo',
                    'icono' => $row->icono ?: '02d',
                    'viento' => (int) round((float) ($row->viento ?? 10)),
                    'ciudad' => 'Santa Cruz de la Sierra',
                    'fuente' => 'registro_local',
                ];
            }
        }

        return [
            'success' => true,
            'temperatura' => 28,
            'humedad' => 62,
            'descripcion' => 'Parcialmente nublado',
            'icono' => '02d',
            'viento' => 14,
            'ciudad' => 'Santa Cruz de la Sierra',
            'fuente' => 'referencia',
        ];
    }

    // ========================================
    // API: Obtener pronóstico 5 días
    // ========================================
    public function getPronostico(Request $request)
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json(['success' => false, 'error' => 'Servicio de clima no configurado'], 503);
        }
        $ciudad = $request->get('ciudad', 'Santa Cruz de la Sierra');
        $pais = 'BO';

        try {
            $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/forecast", [
                'q' => "{$ciudad},{$pais}",
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'es',
                'cnt' => 40
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $pronostico = [];
                
                foreach ($data['list'] as $item) {
                    $fecha = date('Y-m-d', $item['dt']);
                    if (!isset($pronostico[$fecha])) {
                        $pronostico[$fecha] = [
                            'fecha' => $fecha,
                            'dia' => $this->getNombreDia($fecha),
                            'temp_max' => $item['main']['temp_max'],
                            'temp_min' => $item['main']['temp_min'],
                            'descripcion' => ucfirst($item['weather'][0]['description']),
                            'icono' => $item['weather'][0]['icon'],
                            'humedad' => $item['main']['humidity'],
                        ];
                    } else {
                        $pronostico[$fecha]['temp_max'] = max($pronostico[$fecha]['temp_max'], $item['main']['temp_max']);
                        $pronostico[$fecha]['temp_min'] = min($pronostico[$fecha]['temp_min'], $item['main']['temp_min']);
                    }
                }

                return response()->json([
                    'success' => true,
                    'pronostico' => array_values(array_slice($pronostico, 0, 5))
                ]);
            }

            return response()->json(['success' => false, 'error' => 'No se pudo obtener el pronóstico']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function getNombreDia($fecha)
    {
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        return $dias[date('w', strtotime($fecha))];
    }

    private function buildPlantaData(): array
    {
        // Panel operativo de planta: métricas de toda la organización (no solo registros creados por un usuario).
        $rutasActivas = RutaMultiEntrega::query()
            ->whereIn('estado', ['planificada', 'en_ruta']);

        return [
            'stats' => [
                'pedidos_totales' => Pedido::count(),
                'asignaciones' => EnvioAsignacionMultiple::count(),
                'rutas_activas' => (clone $rutasActivas)->count(),
                'incidentes_abiertos' => IncidenteEnvio::query()->where('estado', 'abierto')->count(),
                'documentos' => DocumentoEntrega::count(),
            ],
            'ultimas_asignaciones' => EnvioAsignacionMultiple::query()->latest()->take(8)->get(),
            'rutas_recientes' => RutaMultiEntrega::query()->latest()->take(6)->get(),
        ];
    }

    private function buildTransportistaData($user): array
    {
        $asignaciones = EnvioAsignacionMultiple::query()->where('transportista_usuarioid', $user->usuarioid);
        $rutas = RutaMultiEntrega::query()->where('transportista_usuarioid', $user->usuarioid);
        $incidentes = IncidenteEnvio::query()->where('reportadopor_usuarioid', $user->usuarioid);
        $documentos = DocumentoEntrega::query()->where('usuarioid', $user->usuarioid);

        $total = $asignaciones->count();
        $enRuta = (clone $asignaciones)->where('estado', 'en_ruta')->count();
        $entregados = (clone $asignaciones)->where('estado', 'entregado')->count();

        return [
            'stats' => [
                'asignados' => $total,
                'por_recoger' => (clone $asignaciones)->where('estado', 'asignado')->count(),
                'en_camino' => $enRuta,
                'entregados_hoy' => (clone $asignaciones)->whereDate('updated_at', now()->toDateString())->where('estado', 'entregado')->count(),
                'productividad' => $total > 0 ? round(($entregados / $total) * 100, 2) : 0,
                'incidentes_abiertos' => (clone $incidentes)->where('estado', 'abierto')->count(),
                'documentos' => $documentos->count(),
            ],
            'mis_asignaciones' => $asignaciones->latest()->take(10)->get(),
            'mis_rutas' => $rutas->latest()->take(6)->get(),
        ];
    }

    private function buildAlmacenData($user): array
    {
        $almacenId = $user->almacenid ? (int) $user->almacenid : null;

        if (! $almacenId) {
            return [
                'stats' => [
                    'envios_recibidos' => 0,
                    'por_recibir' => 0,
                    'inventario_total' => 0.0,
                    'stock_productos_distribucion' => 0.0,
                    'lineas_inventario_envio' => 0,
                    'recibidos_hoy' => 0,
                    'incidentes_abiertos' => 0,
                    'notas_entrega' => 0,
                    'ingresos_mes' => 0,
                    'salidas_mes' => 0,
                ],
                'ultimas_recepciones' => collect(),
            ];
        }

        $asignaciones = EnvioAsignacionMultiple::query()->where('almacenid', $almacenId);
        $incidentes = IncidenteEnvio::query()->where('almacenid', $almacenId);
        $documentos = DocumentoEntrega::query()
            ->where('tipo_documento', 'nota_entrega')
            ->where('almacenid', $almacenId);

        $movimientos = AlmacenMovimiento::query()->where('almacenid', $almacenId);

        $stockProductos = Schema::hasTable('almacen_producto')
            ? (float) AlmacenProducto::query()->where('almacenid', $almacenId)->sum('stock')
            : 0.0;
        $lineasInvEnvio = Schema::hasTable('inventario_almacen_envio')
            ? (int) InventarioAlmacenEnvio::query()->where('almacenid', $almacenId)->count()
            : 0;

        return [
            'stats' => [
                'envios_recibidos' => (clone $asignaciones)->where('estado', 'entregado')->count(),
                'por_recibir' => (clone $asignaciones)->whereIn('estado', ['asignado', 'en_ruta'])->count(),
                'inventario_total' => (float) Insumo::query()->where('almacenid', $almacenId)->sum('stock'),
                'stock_productos_distribucion' => $stockProductos,
                'lineas_inventario_envio' => $lineasInvEnvio,
                'recibidos_hoy' => (clone $asignaciones)->whereDate('updated_at', now()->toDateString())->where('estado', 'entregado')->count(),
                'incidentes_abiertos' => (clone $incidentes)->where('estado', 'abierto')->count(),
                'notas_entrega' => $documentos->count(),
                'ingresos_mes' => (clone $movimientos)->whereMonth('fecha', now()->month)->whereHas('tipo', fn($q) => $q->where('naturaleza', 'ingreso'))->count(),
                'salidas_mes' => (clone $movimientos)->whereMonth('fecha', now()->month)->whereHas('tipo', fn($q) => $q->where('naturaleza', 'salida'))->count(),
            ],
            'ultimas_recepciones' => (clone $asignaciones)->where('estado', 'entregado')->latest()->take(10)->get(),
        ];
    }
}