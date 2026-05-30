<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\Usuario;
use App\Support\EnvioAsignacionEstadoCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AsignacionMultipleController extends Controller
{
    public function index(): View
    {
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
        return view('logistica.asignaciones.create');
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

    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'envio_ids' => ['required', 'array', 'min:1'],
            'envio_ids.*' => ['required', 'string', 'max:64'],
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'almacenid' => ['nullable', 'integer', 'exists:almacen,almacenid'],
            'productos' => ['nullable', 'array'],
            'productos.*.sku' => ['nullable', 'string'],
            'productos.*.cantidad' => ['nullable', 'numeric'],
        ]);

        foreach ($validated['envio_ids'] as $envioId) {
            EnvioAsignacionMultiple::updateOrCreate(
                [
                    'externo_envio_id' => $envioId,
                    'transportista_usuarioid' => $validated['transportista_usuarioid'],
                ],
                EnvioAsignacionEstadoCatalogo::applyToAttributes([
                    'asignadopor_usuarioid' => auth()->id(),
                    'rutamultientregaid' => $validated['rutamultientregaid'] ?? null,
                    'vehiculo_ref' => $validated['vehiculo_ref'] ?? null,
                    'almacenid' => $validated['almacenid'] ?? null,
                    'estado' => 'asignado',
                    'fecha_asignacion' => now(),
                    'detalles_productos' => $validated['productos'] ?? null,
                ])
            );
        }

        return redirect()
            ->route('logistica.asignaciones.index')
            ->with('success', 'Los envíos se asignaron correctamente al chofer seleccionado.');
    }

    public function markDelivered(EnvioAsignacionMultiple $asignacion): RedirectResponse
    {
        $asignacion->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
            'estado' => 'entregado',
            'fecha_asignacion' => now(),
        ]));

        return back()->with('success', 'Recepción registrada correctamente.');
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

        $envios = $this->enviosPendientesDeAsignar(50);

        if ($envios->isEmpty()) {
            return redirect()
                ->route('logistica.asignaciones.index')
                ->with('warning', 'No hay envíos pendientes de asignar. Todos ya tienen chofer o están entregados.');
        }

        $transportistaId = (int) $validated['transportista_usuarioid'];
        $chofer = Usuario::find($transportistaId);
        $rutaIdFinal = isset($validated['rutamultientregaid']) ? (int) $validated['rutamultientregaid'] : null;
        $crearRuta = $request->boolean('crear_ruta', true);

        $asignados = DB::transaction(function () use ($envios, $transportistaId, $validated, $rutaIdFinal, $crearRuta, $chofer) {
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
                    'vehiculo_ref' => $validated['vehiculo_ref'] ?? $envio->vehiculo_ref,
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
            ->route('logistica.asignaciones.index')
            ->with('success', $msg);
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
}

