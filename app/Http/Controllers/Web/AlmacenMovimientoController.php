<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoMovimientoAlmacen;
use App\Services\DestinosMotivoAlmacenService;
use App\Services\ReferenciasAlmacenDisponiblesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenMovimientoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = AlmacenMovimiento::query()
            ->with(['almacen', 'insumo.unidadMedida', 'tipo', 'usuario'])
            ->orderByDesc('fecha')
            ->orderByDesc('almacen_movimientoid');

        $statsBase = clone $q;

        $filtroNaturaleza = $request->string('naturaleza')->toString();
        if (in_array($filtroNaturaleza, ['ingreso', 'salida'], true)) {
            $q->whereHas('tipo', fn ($t) => $t->where('naturaleza', $filtroNaturaleza));
        } else {
            $filtroNaturaleza = '';
        }

        $totalIngresos = (clone $statsBase)->whereHas('tipo', fn ($t) => $t->where('naturaleza', 'ingreso'))->count();
        $totalSalidas = (clone $statsBase)->whereHas('tipo', fn ($t) => $t->where('naturaleza', 'salida'))->count();
        $totalMovimientos = (clone $statsBase)->count();

        $almacenesFiltro = (clone $statsBase)
            ->join('almacen', 'almacen_movimiento.almacenid', '=', 'almacen.almacenid')
            ->distinct()
            ->orderBy('almacen.nombre')
            ->pluck('almacen.nombre');

        $tiposFiltro = (clone $statsBase)
            ->join('tipo_movimiento_almacen', 'almacen_movimiento.tipo_movimiento_almacenid', '=', 'tipo_movimiento_almacen.tipo_movimiento_almacenid')
            ->distinct()
            ->orderBy('tipo_movimiento_almacen.nombre')
            ->pluck('tipo_movimiento_almacen.nombre');

        $movimientos = $q->paginate(20)->withQueryString();

        return view('almacen_movimientos.index', compact(
            'movimientos',
            'filtroNaturaleza',
            'totalIngresos',
            'totalSalidas',
            'totalMovimientos',
            'almacenesFiltro',
            'tiposFiltro'
        ));
    }

    public function create(Request $request, string $naturaleza)
    {
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 404);
        $permisoCrear = $naturaleza === 'ingreso' ? 'almacen.ingresos.create' : 'almacen.salidas.create';
        abort_unless($request->user()?->can($permisoCrear), 403);

        $user = $request->user();
        $almacenes = Almacen::query()->orderBy('nombre');
        $insumos = Insumo::query()->with('unidadMedida')->orderBy('nombre');

        $insumosList = $insumos->get();
        $guias = config('almacen_movimientos', []);
        $tiposAyuda = $guias['tipos'][$naturaleza] ?? [];
        $tipos = TipoMovimientoAlmacen::activosPorNaturaleza($naturaleza);
        $tiposAyudaPorId = $tipos->mapWithKeys(function (TipoMovimientoAlmacen $tipo) use ($tiposAyuda) {
            $texto = $tiposAyuda[$tipo->nombre] ?? collect($tiposAyuda)->first(
                fn ($_, string $nombre) => TipoMovimientoAlmacen::normalizeNombre($nombre) === TipoMovimientoAlmacen::normalizeNombre($tipo->nombre)
            );

            return [$tipo->tipo_movimiento_almacenid => $texto ?? 'Motivo del movimiento según su operación interna.'];
        });

        $almacenesList = $almacenes->get();
        $almacenIdInicial = (int) old('almacenid', $almacenesList->first()?->almacenid ?? 0) ?: null;
        $insumoIdInicial = (int) old('insumoid') ?: null;
        $tipoIdInicial = (int) old('tipo_movimiento_almacenid') ?: null;

        $referenciasService = app(ReferenciasAlmacenDisponiblesService::class);
        $destinosService = app(DestinosMotivoAlmacenService::class);

        $sugerenciasIniciales = [
            'almacenid' => $almacenIdInicial,
            'grupos' => [],
            'destinos' => [],
            'destino_sugerido' => null,
        ];

        if ($almacenIdInicial) {
            $sugerenciasIniciales['grupos'] = $referenciasService->listar($naturaleza, $almacenIdInicial, $insumoIdInicial);
            $sugerenciasIniciales['destinos'] = $destinosService->listar(
                $naturaleza,
                $almacenIdInicial,
                $insumoIdInicial,
                $tipoIdInicial,
                old('referencia')
            );
            $sugerenciasIniciales['destino_sugerido'] = $sugerenciasIniciales['destinos'][0]['items'][0]['valor'] ?? null;
        }

        return view('almacen_movimientos.create', [
            'naturaleza' => $naturaleza,
            'almacenes' => $almacenesList,
            'insumos' => $insumosList,
            'insumosPorAlmacen' => $insumosList->groupBy('almacenid')->map(fn ($items) => $items->values()),
            'tipos' => $tipos,
            'guias' => $guias,
            'tiposAyudaPorId' => $tiposAyudaPorId,
            'sugerenciasIniciales' => $sugerenciasIniciales,
        ]);
    }

    public function referenciasDisponibles(
        Request $request,
        ReferenciasAlmacenDisponiblesService $referenciasService,
        DestinosMotivoAlmacenService $destinosService,
    ) {
        $user = $request->user();
        abort_unless(
            $user?->can('almacen.movimientos.view')
            || $user?->can('almacen.ingresos.view')
            || $user?->can('almacen.ingresos.create')
            || $user?->can('almacen.salidas.view')
            || $user?->can('almacen.salidas.create'),
            403
        );

        $naturaleza = $request->string('naturaleza')->toString();
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 422);

        $almacenId = $request->integer('almacenid') ?: null;
        $insumoId = $request->integer('insumoid') ?: null;
        $tipoId = $request->integer('tipo_movimiento_almacenid') ?: null;
        $referencia = $request->string('referencia')->toString();

        $destinos = $destinosService->listar($naturaleza, $almacenId, $insumoId, $tipoId, $referencia ?: null);
        $destinoSugerido = $destinos[0]['items'][0]['valor'] ?? null;

        if ($referencia) {
            $desdeRef = $referenciasService->resolverDestinoPorReferencia($naturaleza, $almacenId, $referencia);
            if ($desdeRef) {
                $destinoSugerido = $desdeRef;
            }
        }

        return response()->json([
            'grupos' => $referenciasService->listar($naturaleza, $almacenId, $insumoId),
            'destinos' => $destinos,
            'destino_sugerido' => $destinoSugerido,
        ]);
    }

    public function show(Request $request, AlmacenMovimiento $almacenMovimiento)
    {
        $almacenMovimiento->load(['almacen', 'insumo.unidadMedida', 'tipo', 'usuario']);

        return view('almacen_movimientos.show', [
            'movimiento' => $almacenMovimiento,
            'filtroNaturaleza' => $request->string('naturaleza')->toString(),
        ]);
    }

    public function store(Request $request, string $naturaleza)
    {
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 404);
        $permisoCrear = $naturaleza === 'ingreso' ? 'almacen.ingresos.create' : 'almacen.salidas.create';
        abort_unless($request->user()?->can($permisoCrear), 403);

        $data = $request->validate([
            'almacenid' => 'required|exists:almacen,almacenid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'tipo_movimiento_almacenid' => 'required|exists:tipo_movimiento_almacen,tipo_movimiento_almacenid',
            'fecha' => 'required|date',
            'cantidad' => 'required|numeric|min:0.001',
            'referencia' => 'nullable|string|max:100',
            'destino_motivo' => 'nullable|string|max:150',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $tipo = TipoMovimientoAlmacen::query()
            ->whereKey($data['tipo_movimiento_almacenid'])
            ->where('naturaleza', $naturaleza)
            ->where('activo', true)
            ->firstOrFail();

        $insumo = Insumo::query()->findOrFail($data['insumoid']);
        if ((int) $insumo->almacenid !== (int) $data['almacenid']) {
            return back()->withInput()->withErrors([
                'insumoid' => 'El insumo no pertenece al almacén seleccionado. Elija un insumo de ese mismo almacén.',
            ]);
        }

        if ($tipo->naturaleza === 'salida' && ! $insumo->tieneStockSuficiente((float) $data['cantidad'])) {
            return back()->withInput()->withErrors([
                'cantidad' => 'Stock insuficiente. Disponible: '.number_format((float) $insumo->stock, 3),
            ]);
        }

        try {
            DB::transaction(function () use ($data, $insumo, $tipo, $user) {
                AlmacenMovimiento::create($data + [
                    'usuarioid' => $user->usuarioid,
                    'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                ]);

                if ($tipo->naturaleza === 'ingreso') {
                    $insumo->incrementarStock((float) $data['cantidad']);
                } else {
                    $insumo->decrementarStock((float) $data['cantidad']);
                }
            });
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'cantidad' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('almacen-movimientos.index', ['naturaleza' => $naturaleza])
            ->with('success', 'Movimiento de almacén registrado correctamente.');
    }

    public function reportes(Request $request)
    {
        $user = $request->user();
        $almacenId = $request->integer('almacenid') ?: null;
        [$fechaDesde, $fechaHasta, $periodoActivo] = $this->resolverPeriodoReportes($request);

        $base = AlmacenMovimiento::query()
            ->with(['almacen', 'insumo', 'tipo'])
            ->when($almacenId, fn($q) => $q->where('almacenid', $almacenId))
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $movimientos = (clone $base)->orderByDesc('fecha')->limit(200)->get();

        $resumenProducto = (clone $base)
            ->join('tipo_movimiento_almacen as tma', 'almacen_movimiento.tipo_movimiento_almacenid', '=', 'tma.tipo_movimiento_almacenid')
            ->join('insumo as i', 'almacen_movimiento.insumoid', '=', 'i.insumoid')
            ->select('i.nombre as producto')
            ->selectRaw("SUM(CASE WHEN tma.naturaleza = 'ingreso' THEN almacen_movimiento.cantidad ELSE 0 END) as ingresos")
            ->selectRaw("SUM(CASE WHEN tma.naturaleza = 'salida' THEN almacen_movimiento.cantidad ELSE 0 END) as salidas")
            ->groupBy('i.nombre')
            ->orderBy('i.nombre')
            ->get();

        $stockPorAlmacen = Insumo::query()
            ->select('almacen.nombre as almacen')
            ->selectRaw('SUM(insumo.stock) as stock')
            ->join('almacen', 'insumo.almacenid', '=', 'almacen.almacenid')
            ->when($almacenId, fn($q) => $q->where('insumo.almacenid', $almacenId))
            ->groupBy('almacen.nombre')
            ->orderBy('almacen.nombre')
            ->get();

        $almacenes = Almacen::query()
            ->orderBy('nombre')
            ->get();

        return view('almacen_movimientos.reportes', compact(
            'movimientos',
            'resumenProducto',
            'stockPorAlmacen',
            'almacenes',
            'almacenId',
            'fechaDesde',
            'fechaHasta',
            'periodoActivo'
        ));
    }

    /**
     * @return array{0:string,1:string,2:string}
     */
    private function resolverPeriodoReportes(Request $request): array
    {
        $periodo = $request->string('periodo')->toString();
        $desde = $request->string('fecha_desde')->toString();
        $hasta = $request->string('fecha_hasta')->toString();
        $hoy = Carbon::today();

        $usarFechasManual = $periodo === 'personalizado' || ($periodo === '' && ($desde !== '' || $hasta !== ''));

        if ($usarFechasManual) {
            $fechaDesde = $desde !== '' ? Carbon::parse($desde) : $hoy->copy()->subDays(29);
            $fechaHasta = $hasta !== '' ? Carbon::parse($hasta) : $hoy->copy();
            if ($fechaDesde->gt($fechaHasta)) {
                [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
            }

            return [$fechaDesde->toDateString(), $fechaHasta->toDateString(), 'personalizado'];
        }

        switch ($periodo) {
            case 'hoy':
                $fechaDesde = $hoy->copy();
                $fechaHasta = $hoy->copy();
                $periodoActivo = 'hoy';
                break;
            case '7d':
                $fechaDesde = $hoy->copy()->subDays(6);
                $fechaHasta = $hoy->copy();
                $periodoActivo = '7d';
                break;
            case 'mes_actual':
                $fechaDesde = $hoy->copy()->startOfMonth();
                $fechaHasta = $hoy->copy()->endOfMonth();
                $periodoActivo = 'mes_actual';
                break;
            case 'mes_pasado':
                $fechaDesde = $hoy->copy()->subMonthNoOverflow()->startOfMonth();
                $fechaHasta = $hoy->copy()->subMonthNoOverflow()->endOfMonth();
                $periodoActivo = 'mes_pasado';
                break;
            case '90d':
                $fechaDesde = $hoy->copy()->subDays(89);
                $fechaHasta = $hoy->copy();
                $periodoActivo = '90d';
                break;
            case '30d':
            default:
                $fechaDesde = $hoy->copy()->subDays(29);
                $fechaHasta = $hoy->copy();
                $periodoActivo = '30d';
                break;
        }

        return [$fechaDesde->toDateString(), $fechaHasta->toDateString(), $periodoActivo];
    }
}
