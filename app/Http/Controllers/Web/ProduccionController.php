<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Produccion;
use App\Models\Lote;
use App\Models\DestinoProduccion;
use App\Models\UnidadMedida;
use App\Models\HistorialEstadoLote;
use App\Models\Almacen;
use App\Support\AlmacenAmbito;
use App\Models\ProduccionAlmacenamiento;
use App\Support\CertificacionCampoService;
use App\Support\EstadoLoteCatalogo;
use App\Support\EvidenciaFoto;
use App\Support\LoteTrazabilidadService;
use App\Support\UbicacionGpsParser;
use Illuminate\Support\Collection;
use App\Services\AlmacenCapacidadService;
use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    public function __construct(
        private readonly AlmacenCapacidadService $capacidadService,
        private readonly LoteTrazabilidadService $trazabilidadService,
        private readonly CertificacionCampoService $certificacionCampo,
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
        $query = $this->cosechasCompletadasQuery($request);

        $stats = [
            'total' => (clone $query)->count(),
            'kg_total' => (float) (clone $query)->sum('cantidad'),
            'lotes' => (clone $query)->count(),
            'promedio' => 0,
        ];
        if ($stats['total'] > 0) {
            $stats['promedio'] = $stats['kg_total'] / $stats['total'];
        }

        $producciones = $query
            ->with(['lote.usuario', 'lote.insumoSemilla', 'lote.cultivo', 'destino', 'unidadMedida', 'almacenamientos.almacen'])
            ->orderByDesc('fechacosecha')
            ->orderByDesc('produccionid')
            ->paginate(15)
            ->withQueryString();

        $estadoIdsPostCosecha = EstadoLoteCatalogo::idsLotePostCosecha() ?: [-1];
        $loteIdsCosechadosFilter = Lote::query()
            ->whereIn('estadolotetipoid', $estadoIdsPostCosecha)
            ->orderBy('nombre')
            ->pluck('loteid');

        $loteFiltroSeleccionado = $request->filled('loteid')
            ? Lote::find((int) $request->loteid)
            : null;

        return view('producciones.index', compact(
            'producciones',
            'stats',
            'loteIdsCosechadosFilter',
            'loteFiltroSeleccionado',
        ));
    }

    /**
     * Una cosecha por lote: el registro más reciente de lotes ya cosechados
     * (incluye certificado, no conforme y finalizado, no solo «Cosechado»).
     */
    private function cosechasCompletadasQuery(Request $request)
    {
        $estadoIds = EstadoLoteCatalogo::idsLotePostCosecha();

        $ultimasPorLote = Produccion::query()
            ->selectRaw('MAX(produccionid) as produccionid')
            ->whereHas('lote', fn ($q) => $q->whereIn('estadolotetipoid', $estadoIds === [] ? [-1] : $estadoIds))
            ->groupBy('loteid');

        $query = Produccion::query()->whereIn('produccionid', $ultimasPorLote);

        if ($request->filled('loteid')) {
            $query->where('loteid', (int) $request->loteid);
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
                    ->orWhereHas('lote.insumoSemilla', fn ($i) => $i->where('nombre', 'like', $buscar))
                    ->orWhereHas('destino', fn ($d) => $d->where('nombre', 'like', $buscar))
                    ->orWhere('observaciones', 'like', $buscar);
            });
        }

        return $query;
    }

    public function create(Request $request)
    {
        $loteidParam = $request->integer('loteid') ?: null;

        $lotesQuery = Lote::with(['usuario', 'cultivo', 'estadoTipo', 'actividades.tipoActividad']);

        if ($loteidParam) {
            $lotesQuery->where('loteid', $loteidParam);
        }

        if (auth()->user()?->hasRole('agricultor')) {
            $lotesQuery->where('usuarioid', auth()->id());
        }

        $lotes = $lotesQuery->orderBy('nombre')->get();

        if (! $loteidParam) {
            $lotes = $lotes->filter(fn (Lote $lote) => $this->trazabilidadService->puedeRegistrarCosecha($lote))->values();
        }

        $unidades = UnidadMedida::where('categoria', 'peso')->get();
        AlmacenAmbito::asegurarAmbitosEnRegistros();
        $almacenesTodos = AlmacenAmbito::scope(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])
                ->where('activo', true)
                ->where('capacidad', '>', 0),
            AlmacenAmbito::AGRICOLA
        )->get();
        $almacenes = $this->almacenesDestacadosParaCosecha($almacenesTodos);
        $resumenesCapacidad = $almacenesTodos
            ->mapWithKeys(fn (Almacen $almacen) => [
                $almacen->almacenid => $this->capacidadService->resumen($almacen),
            ])
            ->all();
        $almacenesCatalogo = $this->catalogoAlmacenesCosecha($almacenesTodos);
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
            'almacenesTodos',
            'almacenesCatalogo',
            'resumenesCapacidad',
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
            'almacenid' => 'nullable|exists:almacen,almacenid',
            'evidencia_foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'evidencia_foto.required' => 'Debe subir una foto de la cosecha realizada.',
            'evidencia_foto.image' => 'El archivo debe ser una imagen.',
            'evidencia_foto.max' => 'La imagen no puede superar 5 MB.',
        ]);

        DB::beginTransaction();

        try {
            $lote = Lote::with(['estadoTipo', 'cultivo'])->findOrFail($data['loteid']);

            if (! $this->trazabilidadService->puedeRegistrarCosecha($lote)) {
                $pendientes = $this->trazabilidadService->actividadesCrecimientoPendientes($lote);
                $mensaje = $pendientes !== []
                    ? 'Complete y marque como realizadas las actividades pendientes: '.implode(', ', $pendientes).'.'
                    : 'El lote aún no está listo para cosecha. Estado actual: '.($lote->estadoTipo->nombre ?? 'sin estado');

                return back()->withErrors(['loteid' => $mensaje])->withInput();
            }

            $destinoAlmacenamiento = DestinoProduccion::whereRaw('LOWER(TRIM(nombre)) = ?', ['almacenamiento'])->first();
            $unidadProduccion = UnidadMedida::find($data['unidadmedidaid']);
            $cantidadBaseKg = $this->capacidadService->convertirAKg((float) $data['cantidad'], $unidadProduccion);

            $rutaEvidencia = EvidenciaFoto::guardar($request->file('evidencia_foto'), 'producciones_evidencia');

            $produccion = Produccion::create([
                'loteid' => $data['loteid'],
                'cantidad' => $data['cantidad'],
                'unidadmedidaid' => $data['unidadmedidaid'],
                'cantidad_base' => $cantidadBaseKg,
                'fechacosecha' => now()->toDateString(),
                'destinoproduccionid' => $destinoAlmacenamiento->destinoproduccionid ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'imagenurl' => 'storage/'.$rutaEvidencia,
            ]);

            $mensajeAlmacen = '';
            $avisoAlmacenPendiente = null;
            if (! empty($data['almacenid'])) {
                $bloqueo = $this->certificacionCampo->mensajeBloqueoAlmacen($lote);
                $almacenDestino = AlmacenAmbito::scope(Almacen::query(), AlmacenAmbito::AGRICOLA)
                    ->where('almacenid', $data['almacenid'])
                    ->firstOrFail();

                if ($bloqueo !== null) {
                    $produccion->update(['almacendestinoid' => $almacenDestino->almacenid]);
                    $avisoAlmacenPendiente = $bloqueo;
                } else {
                $almacen = $almacenDestino;

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
                }
            }

            // Cambiar estado del lote a "Cosechado"
            $estadoCosechadoId = EstadoLoteCatalogo::idPorSlug('cosechado');

            if ($estadoCosechadoId) {
                $lote->update([
                    'estadolotetipoid' => $estadoCosechadoId,
                    'fechamodificacion' => now(),
                ]);

                // Registrar en historial de estados
                $unidad = UnidadMedida::find($data['unidadmedidaid']);
                HistorialEstadoLote::create([
                    'loteid' => $lote->loteid,
                    'estadolotetipoid' => $estadoCosechadoId,
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
                .$mensajeAlmacen.'.';
            if ($mensajeAlmacen === '') {
                $mensaje .= ' Certifique el lote en Certificaciones y luego envíelo al almacén desde la trazabilidad del lote.';
            } else {
                $mensaje .= ' El ingreso aparece en Movimientos de almacén agrícola.';
            }

            $returnUrl = $this->validReturnUrl($request->input('return'));
            $redirect = $returnUrl
                ? redirect($returnUrl)->with('success', $mensaje)
                : redirect()->route('producciones.index')->with('success', $mensaje);

            if ($avisoAlmacenPendiente !== null) {
                $redirect = $redirect->with('warning', 'La cosecha quedó registrada, pero no se envió al almacén: '.$avisoAlmacenPendiente);
            }

            return $redirect;

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar cosecha: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Produccion $produccion)
    {
        $produccion->load(['lote.usuario', 'lote.insumoSemilla', 'lote.cultivo', 'destino', 'unidadMedida', 'almacenamientos.almacen', 'ventas']);

        return view('producciones.show', compact('produccion'));
    }

    public function edit(Produccion $produccion)
    {
        $produccion->load('lote');

        return redirect()
            ->route('lotes.trazabilidad', $produccion->loteid)
            ->with('info', 'La cosecha se consulta aquí; para gestionar el ciclo use el lote.');
    }

    public function update(Request $request, Produccion $produccion)
    {
        return redirect()
            ->route('lotes.trazabilidad', $produccion->loteid)
            ->with('info', 'La cosecha no se edita desde este módulo. Use el lote correspondiente.');
    }

    public function destroy(Produccion $produccion)
    {
        return redirect()
            ->route('producciones.index')
            ->with('info', 'Las cosechas completadas no se eliminan desde este listado. Gestione el lote si necesita corregir datos.');
    }

    /** @return Collection<int, Almacen> */
    private function almacenesDestacadosParaCosecha(Collection $almacenes): Collection
    {
        if ($almacenes->count() <= 4) {
            return $almacenes->values();
        }

        $usoPorAlmacen = ProduccionAlmacenamiento::query()
            ->selectRaw('almacenid, COUNT(*) as total')
            ->groupBy('almacenid')
            ->pluck('total', 'almacenid');

        return $almacenes
            ->sortByDesc(fn (Almacen $almacen) => (int) ($usoPorAlmacen[$almacen->almacenid] ?? 0))
            ->take(4)
            ->values();
    }

    /** @return array<int, array<string, mixed>> */
    private function catalogoAlmacenesCosecha(Collection $almacenes): array
    {
        return $almacenes->map(function (Almacen $almacen) {
            $resumen = $this->capacidadService->resumen($almacen);
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );

            return [
                'id' => $almacen->almacenid,
                'nombre' => $almacen->nombre,
                'tipo' => $almacen->tipoAlmacen->nombre ?? 'General',
                'ubicacion' => $almacen->ubicacion,
                'disponible' => $resumen['disponible_kg'],
                'capacidad' => $resumen['capacidad_kg'],
                'um' => 'kg',
                'tags' => strtolower($almacen->nombre.' '.($almacen->tipoAlmacen->nombre ?? '').' '.($almacen->ubicacion ?? '')),
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'direccion' => $resuelto['direccion'],
                'ubicacion_estimada' => $resuelto['estimada'],
            ];
        })->values()->all();
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