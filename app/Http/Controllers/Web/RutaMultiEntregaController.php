<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Support\RutaPorCallesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RutaMultiEntregaController extends Controller
{
    public function __construct(
        private readonly RutaPorCallesService $rutasCalles
    ) {}

    public function index(): View
    {
        $q = RutaMultiEntrega::query()
            ->with(['transportista'])
            ->withCount('paradas');
        if (auth()->user()->can('rutas_multi.create') === false && auth()->user()->hasRole('transportista')) {
            $q->where('transportista_usuarioid', auth()->id());
        }
        $rutas = $q->orderByDesc('created_at')->paginate(15);

        return view('logistica.rutas.index', compact('rutas'));
    }

    public function create(): View
    {
        $enviosParaRuta = EnvioAsignacionMultiple::query()
            ->with(['pedido:pedidoid,nombre_planta,direccion_texto,latitud,longitud', 'transportista:usuarioid,nombre,apellido,nombreusuario'])
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->where(function ($q) {
                $q->whereNull('rutamultientregaid')
                    ->orWhere('rutamultientregaid', 0);
            })
            ->orderByDesc('envioasignacionmultipleid')
            ->limit(80)
            ->get();

        return view('logistica.rutas.create', compact('enviosParaRuta'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'paradas' => $this->normalizarParadas($request->input('paradas', [])),
        ]);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'transportista_usuarioid' => ['nullable', 'integer', 'exists:usuario,usuarioid'],
            'fecha_salida' => ['nullable', 'date'],
            'paradas' => ['nullable', 'array'],
            'paradas.*.destino' => ['nullable', 'string', 'max:255'],
            'paradas.*.externo_envio_id' => ['nullable', 'string', 'max:64'],
            'paradas.*.pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
        ]);

        $ruta = DB::transaction(function () use ($validated) {
            $ruta = RutaMultiEntrega::create([
                'nombre' => $validated['nombre'],
                'creadopor_usuarioid' => auth()->id(),
                'transportista_usuarioid' => $validated['transportista_usuarioid'] ?? null,
                'fecha_salida' => $validated['fecha_salida'] ?? null,
                'estado' => 'planificada',
            ]);

            $this->crearParadasDesdeFormulario($ruta, $validated['paradas'] ?? []);
            $this->guardarTrazadoRuta($ruta);

            return $ruta;
        });

        return redirect()->route('logistica.rutas.show', $ruta)
            ->with('success', 'Ruta de entrega creada correctamente.');
    }

    public function mapa(): View
    {
        $enviosMapa = EnvioAsignacionMultiple::query()
            ->with(['pedido:pedidoid,nombre_planta,direccion_texto,latitud,longitud', 'transportista:usuarioid,nombreusuario'])
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->where(function ($q) {
                $q->whereNull('rutamultientregaid')->orWhere('rutamultientregaid', 0);
            })
            ->orderByDesc('envioasignacionmultipleid')
            ->limit(200)
            ->get()
            ->map(function (EnvioAsignacionMultiple $envio) {
                $coords = $this->rutasCalles->coordsDesdePedido($envio->pedido);

                return [
                    'id' => $envio->envioasignacionmultipleid,
                    'codigo' => $envio->externo_envio_id,
                    'destino' => $envio->pedido?->nombre_planta ?: $envio->pedido?->direccion_texto,
                    'estado' => $envio->estado,
                    'chofer' => $envio->transportista?->nombreusuario,
                    'lat' => $coords['lat'] ?? null,
                    'lng' => $coords['lng'] ?? null,
                    'pedidoid' => $envio->pedidoid,
                ];
            });

        return view('logistica.rutas.mapa', compact('enviosMapa'));
    }

    /**
     * Vista previa antes de confirmar la generación automática.
     */
    public function previewGenerarAutomatica(Request $request): View|RedirectResponse
    {
        $transportistaId = $request->integer('transportista_usuarioid') ?: null;
        $envios = $this->enviosParaRutaAutomatica($transportistaId);

        if ($envios->isEmpty()) {
            return redirect()
                ->route('logistica.rutas.index')
                ->with('warning', 'No hay envíos pendientes sin ruta para generar automáticamente.');
        }

        $puntos = $envios->map(function (EnvioAsignacionMultiple $envio, $index) {
            $coords = $this->rutasCalles->coordsDesdePedido($envio->pedido);
            $destino = $envio->pedido?->nombre_planta
                ?: $envio->pedido?->direccion_texto
                ?: 'Entrega '.$envio->externo_envio_id;

            return [
                'orden' => $index + 1,
                'codigo' => $envio->externo_envio_id,
                'destino' => $destino,
                'lat' => $coords['lat'] ?? null,
                'lng' => $coords['lng'] ?? null,
            ];
        });

        $geo = $this->rutasCalles->rutaPorCalles(
            $puntos->filter(fn ($p) => $p['lat'] && $p['lng'])->map(fn ($p) => ['lat' => $p['lat'], 'lng' => $p['lng']])->values()->all()
        );

        $chofer = $transportistaId ? Usuario::find($transportistaId) : null;

        return view('logistica.rutas.preview-generar', [
            'envios' => $envios,
            'puntos' => $puntos,
            'geo' => $geo,
            'transportistaId' => $transportistaId,
            'choferNombre' => $chofer ? trim($chofer->nombre.' '.($chofer->apellido ?? '')) : 'Todos los choferes',
        ]);
    }

    /**
     * Genera una ruta con todos los envíos pendientes sin ruta (máx. 30), solo tras confirmación.
     */
    public function generarAutomatica(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transportista_usuarioid' => ['nullable', 'integer', 'exists:usuario,usuarioid'],
            'confirmar' => ['required', 'accepted'],
        ]);

        $transportistaId = $validated['transportista_usuarioid'] ?? null;
        $envios = $this->enviosParaRutaAutomatica($transportistaId);

        if ($envios->isEmpty()) {
            return redirect()
                ->route('logistica.rutas.index')
                ->with('warning', 'No hay envíos pendientes sin ruta para generar automáticamente.');
        }

        $transportistaId = $transportistaId ?? $envios->first()->transportista_usuarioid;
        $nombreChofer = $transportistaId
            ? (Usuario::find($transportistaId)?->nombreusuario ?? 'chofer')
            : 'general';

        $ruta = DB::transaction(function () use ($envios, $transportistaId, $nombreChofer) {
            $ruta = RutaMultiEntrega::create([
                'nombre' => 'Ruta automática '.now()->format('d/m/Y H:i').' — '.$nombreChofer,
                'creadopor_usuarioid' => auth()->id(),
                'transportista_usuarioid' => $transportistaId,
                'fecha_salida' => now(),
                'estado' => 'planificada',
            ]);

            foreach ($envios as $index => $envio) {
                $destino = $envio->pedido?->nombre_planta
                    ?: $envio->pedido?->direccion_texto
                    ?: 'Entrega '.$envio->externo_envio_id;
                $coords = $this->rutasCalles->coordsDesdePedido($envio->pedido);

                RutaParada::create([
                    'rutamultientregaid' => $ruta->rutamultientregaid,
                    'orden' => $index + 1,
                    'destino' => $destino,
                    'externo_envio_id' => $envio->externo_envio_id,
                    'pedidoid' => $envio->pedidoid,
                    'latitud' => $coords['lat'] ?? null,
                    'longitud' => $coords['lng'] ?? null,
                    'estado' => 'pendiente',
                ]);

                $envio->update(['rutamultientregaid' => $ruta->rutamultientregaid]);
            }

            $this->guardarTrazadoRuta($ruta);

            return $ruta;
        });

        return redirect()
            ->route('logistica.rutas.show', $ruta)
            ->with('success', 'Se creó la ruta con '.$envios->count().' paradas automáticas.');
    }

    public function trazado(RutaMultiEntrega $ruta): JsonResponse
    {
        $ruta->load(['paradas.pedido']);

        if ($ruta->rutageojson) {
            $decoded = json_decode($ruta->rutageojson, true);
            if (is_array($decoded)) {
                return response()->json([
                    'geo' => $decoded,
                    'paradas' => $this->rutasCalles->paradasConCoordenadas($ruta->paradas),
                ]);
            }
        }

        $geo = $this->rutasCalles->rutaDesdeParadas($ruta->paradas);

        return response()->json([
            'geo' => $geo,
            'paradas' => $this->rutasCalles->paradasConCoordenadas($ruta->paradas),
        ]);
    }

    /**
     * @param  array<int, mixed>  $paradas
     * @return array<int, array<string, mixed>>
     */
    private function normalizarParadas(array $paradas): array
    {
        return collect($paradas)
            ->map(function ($parada) {
                if (! is_array($parada)) {
                    return [];
                }

                $pedidoId = $parada['pedidoid'] ?? null;
                if ($pedidoId === '' || $pedidoId === null) {
                    $parada['pedidoid'] = null;
                } else {
                    $parada['pedidoid'] = (int) $pedidoId;
                }

                $parada['destino'] = isset($parada['destino']) ? trim((string) $parada['destino']) : null;
                $parada['externo_envio_id'] = isset($parada['externo_envio_id'])
                    ? trim((string) $parada['externo_envio_id']) : null;

                if ($parada['destino'] === '') {
                    $parada['destino'] = null;
                }
                if ($parada['externo_envio_id'] === '') {
                    $parada['externo_envio_id'] = null;
                }

                return $parada;
            })
            ->filter(function (array $parada) {
                return filled($parada['destino'] ?? null)
                    || filled($parada['externo_envio_id'] ?? null)
                    || filled($parada['pedidoid'] ?? null);
            })
            ->values()
            ->all();
    }

    public function show(RutaMultiEntrega $ruta): View
    {
        $ruta->load(['transportista', 'paradas.pedido']);
        $paradasMapa = $this->rutasCalles->paradasConCoordenadas($ruta->paradas);

        return view('logistica.rutas.show', compact('ruta', 'paradasMapa'));
    }

    public function update(Request $request, RutaMultiEntrega $ruta): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:planificada,en_ruta,completada,cancelada'],
            'fecha_cierre' => ['nullable', 'date'],
        ]);

        $ruta->update($validated);

        return back()->with('success', 'Ruta actualizada correctamente.');
    }

    public function reorder(Request $request, RutaMultiEntrega $ruta): RedirectResponse
    {
        $validated = $request->validate([
            'orden' => ['required', 'array', 'min:1'],
            'orden.*' => ['required', 'integer'],
        ]);

        $paradas = $ruta->paradas()->pluck('rutaparadaid')->all();
        DB::transaction(function () use ($validated, $paradas, $ruta) {
            foreach ($validated['orden'] as $index => $paradaId) {
                if (in_array($paradaId, $paradas, true)) {
                    RutaParada::where('rutaparadaid', $paradaId)->update(['orden' => $index + 1]);
                }
            }
            $this->guardarTrazadoRuta($ruta->fresh(['paradas.pedido']));
        });

        return back()->with('success', 'Orden de paradas actualizado.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, EnvioAsignacionMultiple>
     */
    private function enviosParaRutaAutomatica(?int $transportistaId)
    {
        return EnvioAsignacionMultiple::query()
            ->with('pedido')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->where(function ($q) {
                $q->whereNull('rutamultientregaid')->orWhere('rutamultientregaid', 0);
            })
            ->when($transportistaId, fn ($q) => $q->where('transportista_usuarioid', $transportistaId))
            ->orderBy('transportista_usuarioid')
            ->orderByDesc('envioasignacionmultipleid')
            ->limit(30)
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $paradas
     */
    private function crearParadasDesdeFormulario(RutaMultiEntrega $ruta, array $paradas): void
    {
        foreach ($paradas as $index => $parada) {
            $pedido = ! empty($parada['pedidoid']) ? Pedido::find($parada['pedidoid']) : null;
            $coords = $this->rutasCalles->coordsDesdePedido($pedido);

            RutaParada::create([
                'rutamultientregaid' => $ruta->rutamultientregaid,
                'orden' => $index + 1,
                'destino' => $parada['destino'] ?? null,
                'externo_envio_id' => $parada['externo_envio_id'] ?? null,
                'pedidoid' => $parada['pedidoid'] ?? null,
                'latitud' => $coords['lat'] ?? null,
                'longitud' => $coords['lng'] ?? null,
                'estado' => 'pendiente',
            ]);

            if (! empty($parada['externo_envio_id'])) {
                EnvioAsignacionMultiple::query()
                    ->where('externo_envio_id', $parada['externo_envio_id'])
                    ->update(['rutamultientregaid' => $ruta->rutamultientregaid]);
            }
        }
    }

    private function guardarTrazadoRuta(RutaMultiEntrega $ruta): void
    {
        $ruta->load(['paradas.pedido']);
        $geo = $this->rutasCalles->rutaDesdeParadas($ruta->paradas);
        if ($geo) {
            $ruta->update(['rutageojson' => json_encode($geo)]);
        }
    }
}

