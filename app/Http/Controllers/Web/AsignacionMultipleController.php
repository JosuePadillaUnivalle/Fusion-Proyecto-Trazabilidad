<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Usuario;
use App\Services\NotificacionUsuarioService;
use App\Services\RecepcionPlantaEnvioService;
use App\Models\PerfilTransportista;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioListadoService;
use App\Support\EnvioPedidoService;
use App\Support\PedidoCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AsignacionMultipleController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        return $this->listado($request);
    }

    public function show(EnvioAsignacionMultiple $asignacion): View
    {
        $user = auth()->user();
        if (! $user?->can('asignaciones.create') && (int) $asignacion->transportista_usuarioid !== (int) $user?->usuarioid) {
            abort(403);
        }

        $asignacion->load([
            'pedido.detalles.insumo',
            'transportista.perfilTransportista.vehiculo',
            'asignadoPor',
            'ruta.paradas',
            'almacen',
            'recepcionConfirmadaPor',
        ]);

        $paradasMapa = EnvioPedidoService::paradasMapaEnvio($asignacion);

        return view('logistica.asignaciones.show', [
            'asignacion' => $asignacion,
            'trayectoTexto' => EnvioPedidoService::trayectoTexto($asignacion),
            'trayectoPartes' => EnvioPedidoService::trayectoPartes($asignacion),
            'paradasMapa' => $paradasMapa,
            'urlTrazadoRuta' => $asignacion->ruta
                ? route('logistica.rutas.trazado', $asignacion->ruta)
                : null,
            'llegoDestino' => EnvioAsignacionEstadoCatalogo::llegoADestino($asignacion),
            'puedeGestionar' => EnvioAsignacionEstadoCatalogo::puedeGestionarAdmin($asignacion),
        ]);
    }

    public function edit(EnvioAsignacionMultiple $asignacion): View|RedirectResponse
    {
        if (! EnvioAsignacionEstadoCatalogo::puedeGestionarAdmin($asignacion)) {
            return redirect()
                ->route('logistica.asignaciones.show', $asignacion)
                ->with('warning', 'Este envío ya llegó a destino. Solo puede consultar el detalle.');
        }

        $asignacion->load(['transportista', 'ruta']);

        return view('logistica.asignaciones.edit', compact('asignacion'));
    }

    public function update(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        if (! EnvioAsignacionEstadoCatalogo::puedeGestionarAdmin($asignacion)) {
            return redirect()
                ->route('logistica.asignaciones.show', $asignacion)
                ->with('error', 'No puede editar un envío que ya llegó a destino.');
        }

        $validated = $request->validate([
            'transportista_usuarioid' => ['nullable', 'integer', 'exists:usuario,usuarioid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
        ]);

        $asignacion->update([
            'transportista_usuarioid' => $validated['transportista_usuarioid'] ?? null,
            'vehiculo_ref' => $validated['vehiculo_ref'] ?? null,
            'rutamultientregaid' => $validated['rutamultientregaid'] ?? null,
        ]);

        return redirect()
            ->route('logistica.asignaciones.show', $asignacion)
            ->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy(EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        if (! EnvioAsignacionEstadoCatalogo::puedeGestionarAdmin($asignacion)) {
            return redirect()
                ->route('logistica.asignaciones.listado')
                ->with('error', 'No puede eliminar un envío que ya llegó a destino.');
        }

        $codigo = $asignacion->externo_envio_id;
        $asignacion->delete();

        return redirect()
            ->route('logistica.asignaciones.listado')
            ->with('success', "El envío {$codigo} fue eliminado.");
    }

    public function listado(Request $request): View
    {
        abort_unless(
            $request->user()?->can('asignaciones.view') || $request->user()?->can('pedidos.view'),
            403
        );

        return view('logistica.envios.index', EnvioListadoService::prepararListado($request));
    }

    public function create(Request $request): View
    {
        $enviosPendientes = $this->enviosPendientesDeAsignar(100);

        $transportistaSeleccionado = null;
        $vehiculoPlaca = '';

        if ($request->filled('transportista')) {
            $transportistaSeleccionado = Usuario::query()
                ->where('role', 'transportista')
                ->where('activo', true)
                ->with('perfilTransportista.vehiculo')
                ->find((int) $request->transportista);

            $vehiculoPlaca = $transportistaSeleccionado?->perfilTransportista?->vehiculo?->placa ?? '';
        }

        return view('logistica.asignaciones.create', compact(
            'enviosPendientes',
            'transportistaSeleccionado',
            'vehiculoPlaca'
        ));
    }

    public function seleccionarTransportista(Request $request): View
    {
        $query = Usuario::query()
            ->where('role', 'transportista')
            ->with('perfilTransportista.vehiculo')
            ->orderBy('nombre')
            ->orderBy('apellido');

        if ($request->filled('buscar')) {
            $term = '%'.$request->string('buscar')->trim().'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('apellido', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('telefono', 'like', $term);
            });
        }

        if ($request->filled('placa')) {
            $placa = '%'.$request->string('placa')->trim().'%';
            $query->whereHas('perfilTransportista.vehiculo', fn ($v) => $v->where('placa', 'like', $placa));
        }

        $estado = $request->string('estado')->toString();
        if ($estado === 'inactivo') {
            $query->where('activo', false);
        } elseif ($estado === 'todos') {
            // sin filtro adicional
        } else {
            $query->where('activo', true);
        }

        $transportistas = $query->paginate(12)->withQueryString();

        return view('logistica.asignaciones.seleccionar-transportista', compact('transportistas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'externo_envio_id' => ['required', 'string', 'max:64'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'almacenid' => ['nullable', 'integer', 'exists:almacen,almacenid'],
        ]);

        $envioPendiente = EnvioAsignacionMultiple::query()
            ->with('pedido')
            ->where('externo_envio_id', $validated['externo_envio_id'])
            ->first();

        if ($envioPendiente && ! $this->envioListoParaLogistica($envioPendiente)) {
            return back()->with('error', 'Producción agrícola debe aceptar el pedido y reservar stock antes de asignar transportista.');
        }

        EnvioAsignacionMultiple::updateOrCreate(
            [
                'externo_envio_id' => $validated['externo_envio_id'],
                'transportista_usuarioid' => $validated['transportista_usuarioid'],
            ],
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'pedidoid' => $validated['pedidoid'] ?? null,
                'asignadopor_usuarioid' => auth()->id(),
                'rutamultientregaid' => $validated['rutamultientregaid'] ?? null,
                'vehiculo_ref' => $validated['vehiculo_ref'] ?? null,
                'almacenid' => $validated['almacenid'] ?? null,
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
            ])
        );

        return back()->with('success', 'Envío asignado correctamente.');
    }

    public function storeBatch(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'envio_ids' => ['required', 'array', 'min:1'],
            'envio_ids.*' => ['required', 'string', 'max:64'],
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'almacenid' => ['nullable', 'integer', 'exists:almacen,almacenid'],
        ]);

        $transportistaId = (int) $validated['transportista_usuarioid'];
        $vehiculoDefault = $validated['vehiculo_ref'] ?? $this->vehiculoRefForTransportista($transportistaId);

        $bloqueo = $this->validarEnviosListosParaLogistica($validated['envio_ids']);
        if ($bloqueo !== null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $bloqueo], 422);
            }

            return back()->with('error', $bloqueo);
        }

        $enviosNotificar = [];

        foreach ($validated['envio_ids'] as $envioId) {
            $pendiente = EnvioAsignacionMultiple::query()
                ->where('externo_envio_id', $envioId)
                ->whereNull('transportista_usuarioid')
                ->first();

            $attrs = EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'transportista_usuarioid' => $transportistaId,
                'pedidoid' => $pendiente?->pedidoid,
                'asignadopor_usuarioid' => auth()->id(),
                'rutamultientregaid' => $validated['rutamultientregaid'] ?? null,
                'vehiculo_ref' => $vehiculoDefault ?? $pendiente?->vehiculo_ref,
                'almacenid' => $validated['almacenid'] ?? null,
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
            ]);

            if ($pendiente) {
                $pendiente->update($attrs);
                $enviosNotificar[] = $pendiente->fresh(['pedido.detalles']);
                continue;
            }

            $existente = EnvioAsignacionMultiple::query()
                ->where('externo_envio_id', $envioId)
                ->first();

            $asignacion = EnvioAsignacionMultiple::updateOrCreate(
                [
                    'externo_envio_id' => $envioId,
                    'transportista_usuarioid' => $transportistaId,
                ],
                array_merge($attrs, [
                    'pedidoid' => $existente?->pedidoid,
                ])
            );
            $enviosNotificar[] = $asignacion->fresh(['pedido.detalles']);
        }

        $notificaciones = app(NotificacionUsuarioService::class);
        foreach ($enviosNotificar as $envioAsignado) {
            if ($envioAsignado->pedido && PedidoCatalogo::listoParaLogistica($envioAsignado->pedido)) {
                $notificaciones->envioListoParaRecoger($envioAsignado);
            }
        }

        $transportista = Usuario::query()->find($transportistaId);
        $nombreTransportista = trim(($transportista?->nombre ?? '').' '.($transportista?->apellido ?? '')) ?: ($transportista?->nombreusuario ?? 'Transportista');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Los envíos se asignaron correctamente al chofer seleccionado.',
                'transportista' => $nombreTransportista,
                'transportista_id' => $transportistaId,
                'vehiculo' => $vehiculoDefault ?? '—',
                'envios' => array_values($validated['envio_ids']),
                'cantidad' => count($validated['envio_ids']),
                'urls' => [
                    'listado' => route('logistica.asignaciones.listado', [
                        'transportista' => $transportistaId,
                    ]),
                    'nueva' => route('logistica.asignaciones.create'),
                    'documentos' => route('logistica.documentos.index'),
                ],
            ]);
        }

        return redirect()
            ->route('logistica.asignaciones.create')
            ->with('success', 'Los envíos se asignaron correctamente al chofer seleccionado.');
    }

    public function markEnTransportePlanta(EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        $user = auth()->user();
        if (! $user?->can('asignaciones.update') && (int) $asignacion->transportista_usuarioid !== (int) $user?->usuarioid) {
            abort(403);
        }

        if (! in_array($asignacion->estado, ['asignado', 'pendiente', 'asignada', 'creada'], true)) {
            return back()->with('error', 'Solo puede iniciar el transporte cuando el envío está asignado.');
        }

        if (! $this->envioListoParaLogistica($asignacion)) {
            return back()->with('error', 'No se puede avanzar el envío hasta que producción agrícola acepte el pedido y reserve stock.');
        }

        try {
            EnvioPedidoService::confirmarCargaHaciaPlanta($asignacion);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'El envío quedó en transporte hacia planta.');
    }

    public function markLlegadaDestino(
        EnvioAsignacionMultiple $asignacion,
        RecepcionPlantaEnvioService $recepcionService,
        NotificacionUsuarioService $notificaciones,
    ): RedirectResponse {
        $user = auth()->user();
        if (! $user?->can('asignaciones.update') && (int) $asignacion->transportista_usuarioid !== (int) $user?->usuarioid) {
            abort(403);
        }

        if (! in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)) {
            return back()->with('error', 'Solo puede confirmar llegada cuando el envío está en transporte hacia planta.');
        }

        if ($asignacion->fecha_recepcion_planta) {
            return back()->with('error', 'Este envío ya fue marcado como recibido en planta.');
        }

        $asignacion->load('pedido');

        try {
            if ($asignacion->pedido) {
                $recepcionService->confirmarDesdePedido($asignacion->pedido, $user);
            } else {
                $this->marcarRecibidoPlantaSimple($asignacion, $user);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $asignacion->refresh();
        $notificaciones->llegadaDestinoReportada($asignacion, $user);

        return back()->with('success', 'Llegada a destino confirmada. El envío quedó como recibido en planta.');
    }

    /** @deprecated La recepción en planta se confirma desde Gestión de pedidos. */
    public function markDelivered(EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        return redirect()
            ->route('logistica.asignaciones.listado')
            ->with('warning', 'Confirme la llegada a planta desde el listado unificado de envíos.');
    }

    /**
     * Asigna en bloque los envíos pendientes a un chofer y, si se pide, crea la ruta de entrega.
     */
    public function asignarAutomatica(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
            'crear_ruta' => ['nullable', 'boolean'],
        ]);

        $envios = $this->enviosPendientesDeAsignar(50)
            ->filter(fn (EnvioAsignacionMultiple $envio) => $this->envioListoParaLogistica($envio));

        if ($envios->isEmpty()) {
            return redirect()
                ->route('logistica.asignaciones.index')
                ->with('warning', 'No hay envíos listos para asignar. Producción agrícola debe aceptar los pedidos y reservar stock primero.');
        }

        $transportistaId = (int) $validated['transportista_usuarioid'];
        $chofer = Usuario::find($transportistaId);
        $vehiculoRef = $validated['vehiculo_ref'] ?? $this->vehiculoRefForTransportista($transportistaId);
        $rutaIdFinal = isset($validated['rutamultientregaid']) ? (int) $validated['rutamultientregaid'] : null;
        $crearRuta = $request->boolean('crear_ruta', true);

        $asignados = DB::transaction(function () use ($envios, $transportistaId, $validated, $rutaIdFinal, $crearRuta, $chofer, $vehiculoRef) {
            $rutaId = $rutaIdFinal;

            if ($crearRuta && ! $rutaId) {
                $ruta = RutaMultiEntrega::create([
                    'nombre' => 'Ruta '.now()->format('d/m/Y H:i').' — '.($chofer?->nombreusuario ?? 'chofer'),
                    'creadopor_usuarioid' => auth()->id(),
                    'transportista_usuarioid' => $transportistaId,
                    'fecha_salida' => now(),
                    'estado' => 'planificada',
                ]);
                $rutaId = $ruta->rutamultientregaid;

                foreach ($envios as $index => $envio) {
                    RutaParada::create([
                        'rutamultientregaid' => $rutaId,
                        'orden' => $index + 1,
                        'destino' => $envio->pedido?->nombre_planta
                            ?: $envio->pedido?->direccion_texto
                            ?: 'Entrega '.$envio->externo_envio_id,
                        'externo_envio_id' => $envio->externo_envio_id,
                        'pedidoid' => $envio->pedidoid,
                        'estado' => 'pendiente',
                    ]);
                }
            }

            $count = 0;
            foreach ($envios as $envio) {
                $envio->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
                    'transportista_usuarioid' => $transportistaId,
                    'asignadopor_usuarioid' => auth()->id(),
                    'rutamultientregaid' => $rutaId,
                    'vehiculo_ref' => $vehiculoRef ?? $envio->vehiculo_ref,
                    'estado' => 'asignado',
                    'fecha_asignacion' => now(),
                ]));
                $count++;
            }

            return ['count' => $count, 'ruta_id' => $rutaId];
        });

        $msg = "Se asignaron {$asignados['count']} envíos al chofer ".($chofer?->nombreusuario ?? '').'.';
        if (! empty($asignados['ruta_id'])) {
            $msg .= ' También se creó o vinculó una ruta de entrega.';
        }

        return redirect()
            ->route('logistica.asignaciones.create')
            ->with('success', $msg);
    }

    private function vehiculoRefForTransportista(int $transportistaId): ?string
    {
        $perfil = PerfilTransportista::query()
            ->with('vehiculo')
            ->where('usuarioid', $transportistaId)
            ->first();

        return $perfil?->vehiculo?->placa;
    }

    /**
     * Envíos que aún no tienen chofer o siguen en situación pendiente.
     *
     * @return \Illuminate\Support\Collection<int, EnvioAsignacionMultiple>
     */
    private function enviosPendientesDeAsignar(int $limit = 30)
    {
        return EnvioAsignacionMultiple::query()
            ->with('pedido')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->where(function ($q) {
                $q->whereNull('transportista_usuarioid')
                    ->orWhereRaw('LOWER(TRIM(COALESCE(estado, \'\'))) = ?', ['pendiente']);
            })
            ->orderByDesc('envioasignacionmultipleid')
            ->limit($limit)
            ->get();
    }

    private function envioListoParaLogistica(EnvioAsignacionMultiple $envio): bool
    {
        if (! $envio->relationLoaded('pedido')) {
            $envio->load('pedido');
        }

        if (! $envio->pedido) {
            return false;
        }

        return PedidoCatalogo::listoParaLogistica($envio->pedido);
    }

    /**
     * @param  array<int, string>  $envioIds
     */
    private function validarEnviosListosParaLogistica(array $envioIds): ?string
    {
        $envios = EnvioAsignacionMultiple::query()
            ->with('pedido')
            ->whereIn('externo_envio_id', $envioIds)
            ->get();

        foreach ($envios as $envio) {
            if (! $this->envioListoParaLogistica($envio)) {
                return "El envío {$envio->externo_envio_id} requiere aceptación de producción agrícola antes de asignar transportista.";
            }
        }

        return null;
    }

    private function marcarRecibidoPlantaSimple(EnvioAsignacionMultiple $asignacion, Usuario $user): void
    {
        $asignacion->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
            'estado' => 'recibido_planta',
            'fecha_recepcion_planta' => now(),
            'recepcion_usuarioid' => $user->usuarioid,
        ]));
    }

    /**
     * @return array<string, int>
     */
    private function resumenEnviosAsignados(): array
    {
        $base = EnvioAsignacionMultiple::query();

        return [
            'total' => (clone $base)->count(),
            'asignados' => (clone $base)->whereIn('estado', ['asignado', 'asignada', 'pendiente', 'creada'])->count(),
            'en_camino' => (clone $base)->whereIn('estado', ['en_transporte_planta', 'en_ruta', 'en_transito'])->count(),
            'recibidos' => (clone $base)->where(function ($q) {
                $q->whereIn('estado', ['recibido_planta', 'entregado', 'entregada'])
                    ->orWhereNotNull('fecha_recepcion_planta');
            })->count(),
            'recibidos_hoy' => (clone $base)->whereDate('fecha_recepcion_planta', now()->toDateString())->count(),
        ];
    }
}

