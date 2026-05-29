<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Produccion;
use App\Models\Insumo;
use App\Models\LoteInsumo;
use App\Models\Lote;
use App\Models\Cultivo;
use App\Models\Usuario;
use App\Models\Clima;
use App\Models\Almacen;
use App\Models\Actividad;
use App\Models\ProduccionAlmacenamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    private function dateExpr(string $column, string $granularity): string
    {
        $driver = DB::connection()->getDriverName();
        if ($granularity === 'month') {
            return match ($driver) {
                'sqlite' => "strftime('%m', {$column})",
                'pgsql' => "TO_CHAR({$column}, 'MM')",
                default => "DATE_FORMAT({$column}, '%m')",
            };
        }

        if ($granularity === 'year') {
            return match ($driver) {
                'sqlite' => "strftime('%Y', {$column})",
                'pgsql' => "TO_CHAR({$column}, 'YYYY')",
                default => "DATE_FORMAT({$column}, '%Y')",
            };
        }

        // day label dd/mm
        return match ($driver) {
            'sqlite' => "strftime('%d/%m', {$column})",
            'pgsql' => "TO_CHAR({$column}, 'DD/MM')",
            default => "DATE_FORMAT({$column}, '%d/%m')",
        };
    }

    private function dateOrderExpr(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        return match ($driver) {
            'sqlite' => "date({$column})",
            'pgsql' => "DATE({$column})",
            default => "DATE({$column})",
        };
    }
    /**
     * Dashboard principal de reportes
     */
    public function index()
    {
        $stats = [
            'ventas_mes' => Venta::whereMonth('fechaventa', now()->month)
                ->whereYear('fechaventa', now()->year)
                ->selectRaw('SUM(cantidad * preciounitario) as total')
                ->value('total') ?? 0,
            'produccion_mes' => Produccion::whereMonth('fechacosecha', now()->month)
                ->whereYear('fechacosecha', now()->year)
                ->sum('cantidad'),
            'insumos_criticos' => Insumo::whereRaw('stock <= COALESCE(stockminimo, 10)')->count(),
            'actividades_pendientes' => Actividad::whereNull('fechafin')->count(),
        ];

        return view('reportes.index', compact('stats'));
    }

    /**
     * Reporte de Ventas
     */
    public function ventas(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfYear()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());
        $cultivoId = $request->get('cultivo_id');
        $usuarioId = $request->get('usuario_id');

        $query = Venta::with(['produccion.lote.cultivo', 'produccion.lote.usuario', 'unidadMedida'])
            ->whereBetween('fechaventa', [$fechaDesde, $fechaHasta]);

        if ($cultivoId) {
            $query->whereHas('produccion.lote', fn($q) => $q->where('cultivoid', $cultivoId));
        }
        if ($usuarioId) {
            $query->whereHas('produccion.lote', fn($q) => $q->where('usuarioid', $usuarioId));
        }

        $ventas = $query->orderBy('fechaventa', 'desc')->get();

        $ventas->each(function ($venta) {
            $venta->total = $venta->cantidad * $venta->preciounitario;
        });

        $stats = [
            'total_ventas' => $ventas->sum('total'),
            'cantidad_kg' => $ventas->sum('cantidad'),
            'num_transacciones' => $ventas->count(),
            'precio_promedio' => $ventas->count() > 0 ? $ventas->avg('preciounitario') : 0,
        ];

        $exprMes = $this->dateExpr('fechaventa', 'month');
        $exprAnio = $this->dateExpr('fechaventa', 'year');
        $ventasPorMes = Venta::selectRaw("{$exprMes} as mes, {$exprAnio} as anio, SUM(cantidad * preciounitario) as total, SUM(cantidad) as cantidad")
            ->where('fechaventa', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('mes', 'anio')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $ventasPorCultivo = DB::table('venta')
            ->join('produccion', 'venta.produccionid', '=', 'produccion.produccionid')
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->select('cultivo.nombre', DB::raw('SUM(venta.cantidad * venta.preciounitario) as total'), DB::raw('SUM(venta.cantidad) as cantidad'))
            ->whereBetween('venta.fechaventa', [$fechaDesde, $fechaHasta])
            ->groupBy('cultivo.nombre')
            ->orderByDesc('total')
            ->get();

        $topClientes = Venta::select('cliente', DB::raw('SUM(cantidad * preciounitario) as total'), DB::raw('COUNT(*) as transacciones'))
            ->whereBetween('fechaventa', [$fechaDesde, $fechaHasta])
            ->whereNotNull('cliente')
            ->where('cliente', '!=', '')
            ->groupBy('cliente')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $cultivos = Cultivo::orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get();

        return view('reportes.ventas', compact(
            'ventas',
            'stats',
            'ventasPorMes',
            'ventasPorCultivo',
            'topClientes',
            'cultivos',
            'usuarios',
            'fechaDesde',
            'fechaHasta',
            'cultivoId',
            'usuarioId'
        ));
    }

    /**
     * Reporte de Inventario
     */
    public function inventario(Request $request)
    {
        $insumos = Insumo::with(['tipo', 'unidadMedida'])->orderBy('nombre')->get();

        $stats = [
            'total_insumos' => $insumos->count(),
            'stock_critico' => $insumos->filter(fn($i) => $i->stock <= ($i->stockminimo ?? 10))->count(),
            'stock_disponible' => $insumos->filter(fn($i) => $i->stock > ($i->stockminimo ?? 10))->count(),
            'valor_total' => $insumos->sum(fn($i) => $i->stock * ($i->preciounitario ?? 0)),
        ];

        $insumosPorTipo = DB::table('insumo')
            ->leftJoin('tipoinsumo', 'insumo.tipoinsumoid', '=', 'tipoinsumo.tipoinsumoid')
            ->select('tipoinsumo.tipoinsumoid', 'tipoinsumo.nombre as tipo', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(insumo.stock) as stock'))
            ->groupBy('tipoinsumo.tipoinsumoid', 'tipoinsumo.nombre')
            ->get();

        $alertasStock = Insumo::with(['tipo', 'unidadMedida'])
            ->whereRaw('stock <= COALESCE(stockminimo, 10)')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        $consumoReciente = LoteInsumo::with(['insumo.unidadMedida', 'lote'])
            ->where('fechauo', '>=', now()->subDays(30))
            ->orderBy('fechauo', 'desc')
            ->limit(20)
            ->get();

        $stockAlmacenes = Almacen::with('unidadMedida')
            ->get()
            ->map(function ($almacen) {
                $stockActual = ProduccionAlmacenamiento::where('almacenid', $almacen->almacenid)->sum('cantidad');
                return (object) [
                    'nombre' => $almacen->nombre,
                    'stockactual' => $stockActual ?? 0,
                    'capacidadmaxima' => $almacen->capacidad ?? 0,
                ];
            });

        return view('reportes.inventario', compact(
            'insumos',
            'stats',
            'insumosPorTipo',
            'alertasStock',
            'consumoReciente',
            'stockAlmacenes'
        ));
    }

    /**
     * Reporte Climático
     */
    public function climatico(Request $request)
    {
        $dias = $request->get('dias', 7);

        // Historial de clima (últimos registros)
        $historialClima = Clima::where('fecha', '>=', now()->subDays($dias))
            ->orderBy('fecha', 'desc')
            ->get();

        // Promedios del período
        $promedios = [
            'temperatura' => $historialClima->avg('temperatura') ?? 0,
            'humedad' => $historialClima->avg('humedad') ?? 0,
            // 'viento' => $historialClima->avg('viento') ?? 0, // Columna no existe
        ];

        // Datos para el gráfico
        $exprDia = $this->dateExpr('fecha', 'day');
        $exprOrdenDia = $this->dateOrderExpr('fecha');
        $datosGrafico = Clima::selectRaw("{$exprDia} as dia, AVG(temperatura) as temp, AVG(humedad) as hum")
            ->where('fecha', '>=', now()->subDays($dias))
            ->groupByRaw("{$exprDia}, {$exprOrdenDia}")
            ->orderByRaw($exprOrdenDia)
            ->get();

        return view('reportes.climatico', compact(
            'historialClima',
            'promedios',
            'datosGrafico',
            'dias'
        ));
    }

    /**
     * Reporte de Producción
     */
    public function produccion(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfYear()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());
        $cultivoId = $request->get('cultivo_id');
        $loteId = $request->get('lote_id');

        $query = Produccion::with(['lote.cultivo', 'lote.usuario', 'unidadMedida', 'destino'])
            ->whereBetween('fechacosecha', [$fechaDesde, $fechaHasta]);

        if ($cultivoId) {
            $query->whereHas('lote', fn($q) => $q->where('cultivoid', $cultivoId));
        }
        if ($loteId) {
            $query->where('loteid', $loteId);
        }

        $producciones = $query->orderBy('fechacosecha', 'desc')->get();

        $stats = [
            'total_kg' => $producciones->sum('cantidad'),
            'num_cosechas' => $producciones->count(),
            'lotes_productivos' => $producciones->pluck('loteid')->unique()->count(),
            'promedio_cosecha' => $producciones->count() > 0 ? $producciones->avg('cantidad') : 0,
        ];

        $produccionPorCultivo = DB::table('produccion')
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->join('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->select('cultivo.nombre', DB::raw('SUM(produccion.cantidad) as total'))
            ->whereBetween('produccion.fechacosecha', [$fechaDesde, $fechaHasta])
            ->groupBy('cultivo.nombre')
            ->orderByDesc('total')
            ->get();

        $exprMes = $this->dateExpr('fechacosecha', 'month');
        $produccionPorMes = Produccion::selectRaw("{$exprMes} as mes, SUM(cantidad) as total")
            ->whereBetween('fechacosecha', [$fechaDesde, $fechaHasta])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $topLotes = DB::table('produccion')
            ->join('lote', 'produccion.loteid', '=', 'lote.loteid')
            ->leftJoin('cultivo', 'lote.cultivoid', '=', 'cultivo.cultivoid')
            ->select('lote.nombre', 'cultivo.nombre as cultivo', DB::raw('SUM(produccion.cantidad) as total'), DB::raw('COUNT(*) as cosechas'))
            ->whereBetween('produccion.fechacosecha', [$fechaDesde, $fechaHasta])
            ->groupBy('lote.nombre', 'cultivo.nombre')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $cultivos = Cultivo::orderBy('nombre')->get();
        $lotes = Lote::orderBy('nombre')->get();

        return view('reportes.produccion', compact(
            'producciones',
            'stats',
            'produccionPorCultivo',
            'produccionPorMes',
            'topLotes',
            'cultivos',
            'lotes',
            'fechaDesde',
            'fechaHasta',
            'cultivoId',
            'loteId'
        ));
    }

    /**
     * Reporte de Actividades
     */
    public function actividades(Request $request)
    {
        // Por defecto últimos 30 días para mostrar más datos
        $fechaDesde = $request->get('fecha_desde', now()->subDays(30)->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());
        $tipoId = $request->get('tipo_id');
        $loteId = $request->get('lote_id');

        $query = Actividad::with(['lote', 'usuario', 'tipoActividad', 'prioridad']);

        // Solo aplicar filtro de fechas si hay fechainicio
        $query->where(function ($q) use ($fechaDesde, $fechaHasta) {
            $q->whereBetween('fechainicio', [$fechaDesde, $fechaHasta])
                ->orWhereNull('fechainicio');
        });

        if ($tipoId) {
            $query->where('tipoactividadid', $tipoId);
        }
        if ($loteId) {
            $query->where('loteid', $loteId);
        }

        $actividades = $query->orderBy('fechainicio', 'desc')->get();

        // Si no hay actividades con el filtro, traer todas
        if ($actividades->isEmpty()) {
            $actividades = Actividad::with(['lote', 'usuario', 'tipoActividad', 'prioridad'])
                ->when($tipoId, fn($q) => $q->where('tipoactividadid', $tipoId))
                ->when($loteId, fn($q) => $q->where('loteid', $loteId))
                ->orderBy('fechainicio', 'desc')
                ->get();
        }

        $stats = [
            'total' => $actividades->count(),
            'completadas' => $actividades->whereNotNull('fechafin')->count(),
            'pendientes' => $actividades->whereNull('fechafin')->count(),
            'lotes_activos' => $actividades->pluck('loteid')->unique()->count(),
        ];

        $actividadesPorTipo = DB::table('actividad')
            ->join('tipoactividad', 'actividad.tipoactividadid', '=', 'tipoactividad.tipoactividadid')
            ->select('tipoactividad.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('tipoactividad.nombre')
            ->orderByDesc('total')
            ->get();

        $exprDia = $this->dateExpr('fechainicio', 'day');
        $exprOrdenDia = $this->dateOrderExpr('fechainicio');
        $actividadesPorDia = Actividad::selectRaw("{$exprDia} as dia, COUNT(*) as total")
            ->whereNotNull('fechainicio')
            ->whereBetween('fechainicio', [$fechaDesde, $fechaHasta])
            ->groupByRaw("{$exprDia}, {$exprOrdenDia}")
            ->orderByRaw($exprOrdenDia)
            ->get();

        $tipos = DB::table('tipoactividad')->orderBy('nombre')->get();
        $lotes = Lote::orderBy('nombre')->get();

        return view('reportes.actividades', compact(
            'actividades',
            'stats',
            'actividadesPorTipo',
            'actividadesPorDia',
            'tipos',
            'lotes',
            'fechaDesde',
            'fechaHasta',
            'tipoId',
            'loteId'
        ));
    }

    /**
     * Exportar a CSV
     */
    public function exportar(Request $request, $tipo)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfYear()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());
        $formato = $request->get('formato', 'csv');

        // Obtener datos según el tipo
        $datos = [];
        $viewName = "reportes.pdf.{$tipo}";
        $filename = "reporte_{$tipo}_" . now()->format('Y-m-d');

        switch ($tipo) {
            case 'ventas':
                $datos = Venta::with(['produccion.lote.cultivo', 'unidadMedida'])
                    ->whereBetween('fechaventa', [$fechaDesde, $fechaHasta])
                    ->orderBy('fechaventa', 'desc')
                    ->get();
                break;

            case 'produccion':
                $datos = Produccion::with(['lote.cultivo', 'unidadMedida'])
                    ->whereBetween('fechacosecha', [$fechaDesde, $fechaHasta])
                    ->orderBy('fechacosecha', 'desc')
                    ->get();
                break;

            case 'inventario':
                $datos = Insumo::with(['tipo', 'unidadMedida'])
                    ->orderBy('nombre')
                    ->get();
                break;

            case 'actividades':
                $datos = Actividad::with(['lote', 'tipoActividad', 'usuario'])
                    ->whereBetween('fechainicio', [$fechaDesde, $fechaHasta])
                    ->orderBy('fechainicio', 'desc')
                    ->get();
                break;

            default:
                return back()->with('error', 'Tipo de reporte no válido');
        }

        // Exportar a PDF
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView($viewName, [
                'datos' => $datos,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
                'tipo' => $tipo
            ]);
            return $pdf->download("{$filename}.pdf");
        }

        // Exportar a CSV (Lógica original refactorizada)
        $headers = [];
        $rows = [];

        switch ($tipo) {
            case 'ventas':
                $headers = ['ID', 'Fecha', 'Cliente', 'Cultivo', 'Cantidad', 'Unidad', 'Precio Unit.', 'Total'];
                $rows = $datos->map(fn($v) => [
                    $v->ventaid,
                    $v->fechaventa instanceof \Carbon\Carbon ? $v->fechaventa->format('Y-m-d') : $v->fechaventa,
                    $v->cliente ?? '-',
                    $v->produccion->lote->cultivo->nombre ?? '-',
                    $v->cantidad,
                    $v->unidadMedida->abreviatura ?? '-',
                    $v->preciounitario,
                    $v->cantidad * $v->preciounitario
                ]);
                break;

            case 'produccion':
                $headers = ['ID', 'Fecha Cosecha', 'Lote', 'Cultivo', 'Cantidad', 'Unidad', 'Observaciones'];
                $rows = $datos->map(fn($p) => [
                    $p->produccionid,
                    $p->fechacosecha instanceof \Carbon\Carbon ? $p->fechacosecha->format('Y-m-d') : $p->fechacosecha,
                    $p->lote->nombre ?? '-',
                    $p->lote->cultivo->nombre ?? '-',
                    $p->cantidad,
                    $p->unidadMedida->abreviatura ?? '-',
                    $p->observaciones ?? ''
                ]);
                break;

            case 'inventario':
                $headers = ['ID', 'Nombre', 'Tipo', 'Unidad', 'Stock Actual', 'Stock Mínimo', 'Precio Unit.'];
                $rows = $datos->map(fn($i) => [
                    $i->insumoid,
                    $i->nombre,
                    $i->tipo->nombre ?? '-',
                    $i->unidadMedida->abreviatura ?? '-',
                    $i->stock,
                    $i->stockminimo ?? '-',
                    $i->preciounitario ?? '-'
                ]);
                break;

            case 'actividades':
                $headers = ['ID', 'Fecha Inicio', 'Fecha Fin', 'Lote', 'Tipo', 'Responsable', 'Descripción'];
                $rows = $datos->map(fn($a) => [
                    $a->actividadid,
                    $a->fechainicio instanceof \Carbon\Carbon ? $a->fechainicio->format('Y-m-d') : $a->fechainicio,
                    $a->fechafin instanceof \Carbon\Carbon ? $a->fechafin->format('Y-m-d') : ($a->fechafin ?? 'Pendiente'),
                    $a->lote->nombre ?? '-',
                    $a->tipoActividad->nombre ?? '-',
                    $a->usuario->nombre ?? '-',
                    $a->descripcion ?? ''
                ]);
                break;
        }

        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, is_array($row) ? $row : $row->toArray());
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    private function obtenerClimaActual()
    {
        try {
            $apiKey = config('services.openweather.key', env('OPENWEATHER_API_KEY'));
            if ($apiKey) {
                $response = Http::timeout(5)->get("https://api.openweathermap.org/data/2.5/weather", [
                    'q' => 'Santa Cruz,BO',
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang' => 'es'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'temperatura' => round($data['main']['temp'] ?? 0),
                        'sensacion' => round($data['main']['feels_like'] ?? 0),
                        'humedad' => $data['main']['humidity'] ?? 0,
                        'presion' => $data['main']['pressure'] ?? 0,
                        'viento' => round(($data['wind']['speed'] ?? 0) * 3.6, 1),
                        'descripcion' => ucfirst($data['weather'][0]['description'] ?? ''),
                        'icono' => $data['weather'][0]['icon'] ?? '01d',
                        'ubicacion' => 'Santa Cruz, Bolivia'
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        return [
            'temperatura' => 28,
            'sensacion' => 30,
            'humedad' => 65,
            'presion' => 1013,
            'viento' => 12,
            'descripcion' => 'Parcialmente nublado',
            'icono' => '02d',
            'ubicacion' => 'Santa Cruz, Bolivia'
        ];
    }

    private function obtenerPronostico()
    {
        try {
            $apiKey = config('services.openweather.key', env('OPENWEATHER_API_KEY'));
            if ($apiKey) {
                $response = Http::timeout(5)->get("https://api.openweathermap.org/data/2.5/forecast", [
                    'q' => 'Santa Cruz,BO',
                    'appid' => $apiKey,
                    'units' => 'metric',
                    'lang' => 'es',
                    'cnt' => 40
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $pronostico = [];
                    $diasProcesados = [];

                    foreach ($data['list'] as $item) {
                        $fecha = date('Y-m-d', $item['dt']);
                        if (!in_array($fecha, $diasProcesados) && count($pronostico) < 5) {
                            $diasProcesados[] = $fecha;
                            $pronostico[] = [
                                'fecha' => $fecha,
                                'dia' => $this->nombreDia($fecha),
                                'temp_max' => round($item['main']['temp_max']),
                                'temp_min' => round($item['main']['temp_min']),
                                'descripcion' => ucfirst($item['weather'][0]['description'] ?? ''),
                                'icono' => $item['weather'][0]['icon'] ?? '01d',
                            ];
                        }
                    }
                    return $pronostico;
                }
            }
        } catch (\Exception $e) {
        }

        return [
            ['dia' => 'Hoy', 'temp_max' => 28, 'temp_min' => 19, 'descripcion' => 'Parcialmente nublado', 'icono' => '02d'],
            ['dia' => 'Mañana', 'temp_max' => 25, 'temp_min' => 17, 'descripcion' => 'Lluvia ligera', 'icono' => '10d'],
            ['dia' => 'Miércoles', 'temp_max' => 30, 'temp_min' => 21, 'descripcion' => 'Soleado', 'icono' => '01d'],
            ['dia' => 'Jueves', 'temp_max' => 29, 'temp_min' => 20, 'descripcion' => 'Parcialmente nublado', 'icono' => '02d'],
            ['dia' => 'Viernes', 'temp_max' => 27, 'temp_min' => 18, 'descripcion' => 'Nublado', 'icono' => '03d'],
        ];
    }

    private function nombreDia($fecha)
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $hoy = now()->toDateString();
        $manana = now()->addDay()->toDateString();

        if ($fecha == $hoy)
            return 'Hoy';
        if ($fecha == $manana)
            return 'Mañana';

        return $dias[date('w', strtotime($fecha))];
    }
}