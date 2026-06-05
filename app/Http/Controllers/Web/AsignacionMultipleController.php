<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\PerfilTransportista;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\Usuario;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\PedidoCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AsignacionMultipleController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->can('asignaciones.create')) {
            return redirect()->route('logistica.asignaciones.create');
        }

        $q = EnvioAsignacionMultiple::query()
            ->with(['transportista', 'asignadoPor', 'ruta']);
        $user = auth()->user();
        if (auth()->user()->can('asignaciones.create') === false) {
            $q->where('transportista_usuarioid', auth()->id());
        }
        $asignaciones = $q->orderByDesc('created_at')->paginate(20);

        $enviosPendientes = $this->enviosPendientesDeAsignar();
        $transportistas = Usuario::query()
            ->where('role', 'transportista')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $rutasDisponibles = RutaMultiEntrega::query()
            ->whereIn('estado', ['planificada', 'en_ruta'])
            ->with('transportista')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('logistica.asignaciones.index', compact(
            'asignaciones',
            'enviosPendientes',
            'transportistas',
            'rutasDisponibles'
        ));
    }

    public function create(): View
    {
        $enviosPendientes = $this->enviosPendientesDeAsignar(100);
        $transportistas = Usuario::query()
            ->where('role', 'transportista')
            ->where('activo', true)
            ->with('perfilTransportista.vehiculo')
            ->orderBy('nombre')
            ->get();
        $vehiculosPorTransportista = $transportistas->mapWithKeys(function (Usuario $t) {
            $placa = $t->perfilTransportista?->vehiculo?->placa;

            return [(string) $t->usuarioid => $placa ?? ''];
        });

        return view('logistica.asignaciones.create', compact(
            'enviosPendientes',
            'transportistas',
            'vehiculosPorTransportista'
        ));
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
                continue;
            }

            $existente = EnvioAsignacionMultiple::query()
                ->where('externo_envio_id', $envioId)
                ->first();

            EnvioAsignacionMultiple::updateOrCreate(
                [
                    'externo_envio_id' => $envioId,
                    'transportista_usuarioid' => $transportistaId,
                ],
                array_merge($attrs, [
                    'pedidoid' => $existente?->pedidoid,
                ])
            );
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

    /** @deprecated La recepción en planta se confirma desde Gestión de pedidos. */
    public function markDelivered(EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        return redirect()
            ->route('pedidos.index')
            ->with('warning', 'Confirme la llegada a planta desde Gestión de pedidos (botón ✓ en el listado).');
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
            return true;
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
}

