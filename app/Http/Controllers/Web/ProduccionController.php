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
use App\Models\ProduccionAlmacenamiento;
use App\Models\ProcesoPlanta;
use App\Models\MaquinaPlanta;
use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()?->hasRole('transportista') || $request->user()?->hasRole('almacen')) {
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
        $procesosFiltro = ProcesoPlanta::query()->where('activo', true)->orderBy('nombre')->get(['procesoplantaid', 'nombre']);
        $maquinasFiltro = MaquinaPlanta::query()->where('activo', true)->orderBy('nombre')->get(['maquinaplantaid', 'nombre', 'codigo']);

        return view('producciones.index', compact(
            'producciones',
            'stats',
            'lotesFiltro',
            'destinosFiltro',
            'procesosFiltro',
            'maquinasFiltro'
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

        if ($request->filled('procesoid')) {
            $query->where('procesoplantaid', (int) $request->procesoid);
        }

        if ($request->filled('maquinaid')) {
            $query->where('maquinaplantaid', (int) $request->maquinaid);
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

    public function create()
    {
        $lotesQuery = Lote::with(['usuario', 'cultivo', 'estadoTipo'])
            ->whereHas('estadoTipo', function ($q) {
                $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['en producción']);
            });

        if (auth()->user()?->hasRole('agricultor')) {
            $lotesQuery->where('usuarioid', auth()->id());
        }

        $lotes = $lotesQuery->orderBy('nombre')->get();

        $unidades = UnidadMedida::where('categoria', 'peso')->get();
        $almacenes = Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])
            ->where('activo', true)
            ->get();
        $procesos = ProcesoPlanta::where('activo', true)->orderBy('nombre')->get();
        $maquinas = MaquinaPlanta::where('activo', true)->orderBy('nombre')->get();

        $lotePreseleccionado = $lotes->count() === 1 ? $lotes->first()->loteid : old('loteid');

        return view('producciones.create', compact(
            'lotes',
            'unidades',
            'almacenes',
            'procesos',
            'maquinas',
            'lotePreseleccionado'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'cantidad' => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'observaciones' => 'nullable|string',
            'procesoplantaid' => 'nullable|exists:proceso_planta,procesoplantaid',
            'maquinaplantaid' => 'nullable|exists:maquina_planta,maquinaplantaid',
            'enviar_almacen' => 'nullable|boolean',
            'almacenid' => 'nullable|exists:almacen,almacenid',
        ]);

        DB::beginTransaction();

        try {
            $lote = Lote::with(['estadoTipo', 'cultivo'])->findOrFail($data['loteid']);

            // Validar que el lote esté en producción
            if (strtolower(trim($lote->estadoTipo->nombre ?? '')) !== 'en producción') {
                return back()->withErrors([
                    'loteid' => "El lote debe estar 'en producción' para registrar cosecha. Estado actual: {$lote->estadoTipo->nombre}",
                ])->withInput();
            }

            $destinoAlmacenamiento = DestinoProduccion::where('nombre', 'almacenamiento')->first();
            $unidadProduccion = UnidadMedida::find($data['unidadmedidaid']);
            $cantidadBaseKg = $this->convertirAKg((float) $data['cantidad'], $unidadProduccion);

            $produccion = Produccion::create([
                'loteid' => $data['loteid'],
                'cantidad' => $data['cantidad'],
                'unidadmedidaid' => $data['unidadmedidaid'],
                'cantidad_base' => $cantidadBaseKg,
                'fechacosecha' => now()->toDateString(),
                'destinoproduccionid' => $destinoAlmacenamiento->destinoproduccionid ?? null,
                'procesoplantaid' => $data['procesoplantaid'] ?? null,
                'maquinaplantaid' => $data['maquinaplantaid'] ?? null,
                'observaciones' => $data['observaciones'],
            ]);

            // Si se seleccionó enviar a almacén
            $mensajeAlmacen = '';
            $almacen = null;

            if ($request->filled('enviar_almacen') && $request->filled('almacenid')) {
                // Usar almacén seleccionado manualmente
                $almacen = Almacen::find($request->almacenid);
            }

            // Si hay almacén, crear el registro de almacenamiento
            if ($almacen) {
                // ================================
                // 1) Capacidad del almacén en KG
                // ================================
                $unidadAlmacen = $almacen->unidadMedida; // relación unidadMedida en modelo Almacen
                $capacidadKg = $this->convertirAKg((float) ($almacen->capacidad ?? 0), $unidadAlmacen);

                // ======================================
                // 2) Ocupación actual del almacén en KG
                // ======================================
                $almacenamientos = ProduccionAlmacenamiento::with('unidadMedida')
                    ->where('almacenid', $almacen->almacenid)
                    ->whereNull('fechasalida')
                    ->get();

                $ocupadoKg = 0;
                foreach ($almacenamientos as $alm) {
                    $ocupadoKg += $this->convertirAKg((float) $alm->cantidad, $alm->unidadMedida);
                }

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
            }

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
            return redirect()
                ->route('producciones.index')
                ->with('success', "¡Cosecha registrada! {$data['cantidad']} {$unidad->abreviatura} de {$lote->cultivo->nombre}"
                    . $mensajeAlmacen.' · Actividad de cosecha generada automáticamente.');

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
        $procesos = ProcesoPlanta::where('activo', true)->orderBy('nombre')->get();
        $maquinas = MaquinaPlanta::where('activo', true)->orderBy('nombre')->get();

        return view('producciones.edit', compact('produccion', 'lotes', 'destinos', 'unidades', 'procesos', 'maquinas'));
    }

    public function update(Request $request, Produccion $produccion)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'cantidad' => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'fechacosecha' => 'required|date',
            'destinoproduccionid' => 'nullable|exists:destinoproduccion,destinoproduccionid',
            'procesoplantaid' => 'nullable|exists:proceso_planta,procesoplantaid',
            'maquinaplantaid' => 'nullable|exists:maquina_planta,maquinaplantaid',
            'observaciones' => 'nullable|string',
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
}