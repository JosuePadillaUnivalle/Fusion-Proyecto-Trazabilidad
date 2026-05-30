<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Produccion;
use App\Models\Lote;
use App\Models\DestinoProduccion;
use App\Models\UnidadMedida;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Almacen;
use App\Support\AlmacenAmbito;
use App\Models\ProduccionAlmacenamiento;
use App\Support\EstadoLoteCatalogo;
use App\Services\AlmacenCapacidadService;
use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    public function __construct(
        private readonly AlmacenCapacidadService $capacidadService,
    ) {
        $this->middleware(function ($request, $next) {
            if ($request->user()?->hasRole('transportista')) {
                abort(403, 'No tienes permiso para acceder al registro de producción.');
            }

            return $next($request);
        });
    }

    private function convertirAKg(float $cantidad, ?UnidadMedida $unidad): float
    {
        if (!$unidad) {
            // Si no hay unidad, asumimos que ya está en kg
            return $cantidad;
        }

        $abbr = strtolower(trim($unidad->abreviatura ?? $unidad->nombre ?? ''));

        // Puedes ajustar este mapa según tus unidades reales
        $factores = [
            'kg' => 1,
            'kilogramo' => 1,
            'kilogramos' => 1,

            'g' => 0.001,
            'gr' => 0.001,
            'gramo' => 0.001,
            'gramos' => 0.001,

            't' => 1000,
            'tn' => 1000,
            'ton' => 1000,
            'tonelada' => 1000,
            'toneladas' => 1000,

            'qq' => 46,
            'quintal' => 46,
            'quintales' => 46,
        ];

        $factor = $factores[$abbr] ?? 1; // si no lo conoce, lo toma como kg

        return $cantidad * $factor;
    }

    public function index(Request $request)
    {
        $query = $this->produccionesFilteredQuery($request);

        $stats = [
            'total' => (clone $query)->count(),
            'kg_total' => (float) (clone $query)->sum('cantidad'),
            'lotes' => (clone $query)->distinct('loteid')->count('loteid'),
            'promedio' => 0,
        ];
        if ($stats['total'] > 0) {
            $stats['promedio'] = $stats['kg_total'] / $stats['total'];
        }

        $producciones = $query
            ->with(['lote.usuario', 'lote.cultivo', 'destino', 'unidadMedida', 'procesoPlanta', 'maquinaPlanta', 'almacenamientos.almacen'])
            ->orderBy('produccionid', 'desc')
            ->paginate(15)
            ->withQueryString();

        $lotesFiltro = Lote::query()->orderBy('nombre')->get(['loteid', 'nombre']);
        $destinosFiltro = DestinoProduccion::query()->orderBy('nombre')->get(['destinoproduccionid', 'nombre']);
        return view('producciones.index', compact(
            'producciones',
            'stats',
            'lotesFiltro',
            'destinosFiltro',
        ));
    }

    private function produccionesFilteredQuery(Request $request)
    {
        $query = Produccion::query();

        if ($request->filled('loteid')) {
            $query->where('loteid', (int) $request->loteid);
        }

        if ($request->filled('destinoid')) {
            $query->where('destinoproduccionid', (int) $request->destinoid);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fechacosecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fechacosecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('lote', fn ($l) => $l->where('nombre', 'like', $buscar))
                    ->orWhereHas('lote.cultivo', fn ($c) => $c->where('nombre', 'like', $buscar))
                    ->orWhereHas('destino', fn ($d) => $d->where('nombre', 'like', $buscar))
                    ->orWhere('observaciones', 'like', $buscar);
            });
        }

        return $query;
    }

    public function create(Request $request)
    {
        $loteidParam = $request->integer('loteid') ?: null;

        $lotesQuery = Lote::with(['usuario', 'cultivo', 'estadoTipo']);

        if ($loteidParam) {
            $lotesQuery->where('loteid', $loteidParam);
        } else {
            $ids = EstadoLoteCatalogo::idsPorSlugs(['listo_para_cosecha']);
            if ($ids !== []) {
                $lotesQuery->whereIn('estadolotetipoid', $ids);
            } else {
                $lotesQuery->whereHas('estadoTipo', function ($q) {
                    $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['listo para cosecha']);
                });
            }
        }

        if (auth()->user()?->hasRole('agricultor')) {
            $lotesQuery->where('usuarioid', auth()->id());
        }

        $lotes = $lotesQuery->orderBy('nombre')->get();

        $unidades = UnidadMedida::where('categoria', 'peso')->get();
        $almacenes = AlmacenAmbito::scope(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])->where('activo', true),
            AlmacenAmbito::AGRICOLA
        )->get();
        $lotePreseleccionado = $loteidParam
            ?? (old('loteid') ?: ($lotes->count() === 1 ? $lotes->first()->loteid : null));
        $lotePreseleccionadoLabel = null;
        if ($lotePreseleccionado) {
            $loteSel = $lotes->firstWhere('loteid', $lotePreseleccionado) ?? Lote::find($lotePreseleccionado);
            $lotePreseleccionadoLabel = $loteSel?->nombre;
        }

        $returnUrl = $this->validReturnUrl($request->input('return'));

        return view('producciones.create', compact(
            'lotes',
            'unidades',
            'almacenes',
            'lotePreseleccionado',
            'lotePreseleccionadoLabel',
            'returnUrl'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'cantidad' => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'observaciones' => 'nullable|string',
            'almacenid' => 'required|exists:almacen,almacenid',
        ]);

        DB::beginTransaction();

        try {
            $lote = Lote::with(['estadoTipo', 'cultivo'])->findOrFail($data['loteid']);

            if (! EstadoLoteCatalogo::loteEnSlug($lote->estadoTipo->nombre ?? '', 'listo_para_cosecha')) {
                return back()->withErrors([
                    'loteid' => 'El lote debe estar en estado «Listo para cosecha». Estado actual: '.($lote->estadoTipo->nombre ?? 'sin estado'),
                ])->withInput();
            }

            $destinoAlmacenamiento = DestinoProduccion::where('nombre', 'almacenamiento')->first();
            $unidadProduccion = UnidadMedida::find($data['unidadmedidaid']);
            $cantidadBaseKg = $this->capacidadService->convertirAKg((float) $data['cantidad'], $unidadProduccion);

            $produccion = Produccion::create([
                'loteid' => $data['loteid'],
                'cantidad' => $data['cantidad'],
                'unidadmedidaid' => $data['unidadmedidaid'],
                'cantidad_base' => $cantidadBaseKg,
                'fechacosecha' => now()->toDateString(),
                'destinoproduccionid' => $destinoAlmacenamiento->destinoproduccionid ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $mensajeAlmacen = '';
            $almacen = AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::AGRICOLA)
                ->where('almacenid', $data['almacenid'])
                ->firstOrFail();

            // ================================
            // 1) Capacidad del almacén en KG
            // ================================
                $unidadAlmacen = $almacen->unidadMedida; // relación unidadMedida en modelo Almacen
                $resumenAlmacen = $this->capacidadService->resumen($almacen);
                $capacidadKg = $resumenAlmacen['capacidad_kg'];
                $ocupadoKg = $resumenAlmacen['ocupado_kg'];

                // =====================================
                // 3) Nueva cantidad a ingresar en KG
                // =====================================
                $unidadProduccion = UnidadMedida::find($data['unidadmedidaid']);
                $nuevaCantidadKg = $cantidadBaseKg;

                $disponibleKg = $capacidadKg - $ocupadoKg;

                if ($nuevaCantidadKg > $disponibleKg) {
                    throw new \Exception(
                        "La cantidad a almacenar ({$data['cantidad']} {$unidadProduccion->abreviatura}) " .
                        "excede la capacidad disponible del almacén. Disponible: " .
                        round($disponibleKg, 2) . " kg"
                    );
                }

                // Si pasa la validación, guardamos en la unidad que vino del formulario
                ProduccionAlmacenamiento::create([
                    'produccionid' => $produccion->produccionid,
                    'almacenid' => $almacen->almacenid,
                    'cantidad' => $data['cantidad'],
                    'unidadmedidaid' => $data['unidadmedidaid'],
                    'fechaentrada' => now(),
                    'observaciones' => "Cosecha del lote {$lote->nombre}",
                ]);

            $mensajeAlmacen = " y almacenado en {$almacen->nombre}";

            // Cambiar estado del lote a "cosechado"
            $estadoCosechado = EstadoLoteTipo::where('nombre', 'cosechado')->first();

            if ($estadoCosechado) {
                $lote->update([
                    'estadolotetipoid' => $estadoCosechado->estadolotetipoid,
                    'fechamodificacion' => now(),
                ]);

                // Registrar en historial de estados
                $unidad = UnidadMedida::find($data['unidadmedidaid']);
                HistorialEstadoLote::create([
                    'loteid' => $lote->loteid,
                    'estadolotetipoid' => $estadoCosechado->estadolotetipoid,
                    'fecha_cambio' => now(),
                    'observaciones' => "Cosecha: {$data['cantidad']} {$unidad->abreviatura}" . $mensajeAlmacen,
                    'usuarioid' => $lote->usuarioid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            app(OperacionAgricolaAutomaticaService::class)->desdeProduccion($produccion);

            DB::commit();

            $unidad = UnidadMedida::find($data['unidadmedidaid']);
            $mensaje = "¡Cosecha registrada! {$data['cantidad']} {$unidad->abreviatura} de {$lote->cultivo->nombre}"
                . $mensajeAlmacen.'. El ingreso aparece en Movimientos de almacén agrícola.';

            $returnUrl = $this->validReturnUrl($request->input('return'));
            if ($returnUrl) {
                return redirect($returnUrl)->with('success', $mensaje);
            }

            return redirect()
                ->route('producciones.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar cosecha: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Produccion $produccion)
    {
        $produccion->load(['lote.usuario', 'lote.cultivo', 'destino', 'unidadMedida', 'procesoPlanta', 'maquinaPlanta', 'almacenamientos.almacen', 'ventas']);

        return view('producciones.show', compact('produccion'));
    }

    public function edit(Produccion $produccion)
    {
        $lotes = Lote::with(['usuario', 'cultivo'])->get();
        $destinos = DestinoProduccion::all();
        $unidades = UnidadMedida::where('categoria', 'peso')->get();
        return view('producciones.edit', compact('produccion', 'lotes', 'destinos', 'unidades'));
    }

    public function update(Request $request, Produccion $produccion)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'cantidad' => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'fechacosecha' => 'required|date',
            'destinoproduccionid' => 'nullable|exists:destinoproduccion,destinoproduccionid',
        ]);

        $produccion->update($data);

        return redirect()
            ->route('producciones.index')
            ->with('success', 'Producción actualizada.');
    }

    public function destroy(Produccion $produccion)
    {
        DB::beginTransaction();

        try {
            // Si tiene ventas asociadas, no permitir eliminar
            if ($produccion->ventas()->count() > 0) {
                return back()->withErrors([
                    'error' => 'No se puede eliminar esta producción porque tiene ventas asociadas.'
                ]);
            }

            // Eliminar almacenamientos asociados
            $produccion->almacenamientos()->delete();

            $lote = $produccion->lote;
            $produccion->delete();

            // Opcional: volver el lote a "en producción" si no tiene otras producciones
            $otrasProducciones = Produccion::where('loteid', $lote->loteid)->count();
            if ($otrasProducciones == 0) {
                $estadoProduccion = EstadoLoteTipo::where('nombre', 'en producción')->first();
                if ($estadoProduccion) {
                    $lote->update(['estadolotetipoid' => $estadoProduccion->estadolotetipoid]);
                }
            }

            DB::commit();

            return redirect()
                ->route('producciones.index')
                ->with('success', 'Producción y almacenamiento eliminados.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    private function validReturnUrl(mixed $return): ?string
    {
        if (! is_string($return) || trim($return) === '') {
            return null;
        }

        $return = trim($return);
        $appUrl = rtrim((string) config('app.url'), '/');
        if (! str_starts_with($return, '/') && ! str_starts_with($return, $appUrl)) {
            return null;
        }

        return $return;
    }
}