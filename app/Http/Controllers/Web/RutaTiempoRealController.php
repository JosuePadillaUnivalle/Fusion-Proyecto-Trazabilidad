<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Services\SimulacionRutaService;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\PuntoVentaAccess;
use App\Support\RutaDistribucionCatalogo;
use App\Support\SimulacionRutaCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RutaTiempoRealController extends Controller
{
    public function __construct(
        private readonly SimulacionRutaService $simulacion
    ) {}

    public function index(): View
    {
        $this->autorizarSupervisor();

        $busqueda = request()->string('q')->trim()->toString();
        $variante = request()->string('variante')->trim()->toString();
        $variante = $variante !== '' ? $variante : null;

        $rutas = $this->simulacion->listarActivasFiltradas(
            $busqueda !== '' ? $busqueda : null,
            $variante
        );

        return view('logistica.rutas-tiempo-real.index', [
            'rutas' => $rutas,
            'totalActivas' => $rutas->count(),
            'busqueda' => $busqueda,
            'variante' => $variante,
            'variantes' => SimulacionRutaCatalogo::catalogoVariantes(),
        ]);
    }

    public function mapa(): View
    {
        $this->autorizarSupervisor();

        return view('logistica.rutas-tiempo-real.mapa', [
            'variantes' => SimulacionRutaCatalogo::catalogoVariantes(),
            'estadoUrl' => route('logistica.rutas-tiempo-real.mapa-estado'),
            'volverUrl' => route('logistica.rutas-tiempo-real.index'),
        ]);
    }

    public function estadoMapa(): JsonResponse
    {
        $this->autorizarSupervisor();

        return response()->json([
            'actualizado_at' => now()->toIso8601String(),
            'rutas' => $this->simulacion->estadosMapaGlobal(),
        ]);
    }

    public function show(string $tipo, int $id): View
    {
        $this->autorizarVerIndividual($tipo, $id);

        [$titulo, $estado, $paradas, $trayecto, $volverUrl] = match ($tipo) {
            SimulacionRutaCatalogo::TIPO_AGRICOLA => [...$this->resolverAgricola($id), $this->urlVolverAgricola($id)],
            SimulacionRutaCatalogo::TIPO_DISTRIBUCION => [...$this->resolverDistribucion($id), $this->urlVolverDistribucion($id)],
            SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA => [...$this->resolverPlantaMayorista($id), $this->urlVolverPlantaMayorista($id)],
            default => abort(404),
        };

        return view('logistica.rutas-tiempo-real.show', [
            'tipo' => $tipo,
            'id' => $id,
            'titulo' => $titulo,
            'estado' => $estado,
            'paradas' => $paradas,
            'trayecto' => $trayecto,
            'volverUrl' => $volverUrl,
            'puedeCerrarManual' => $this->puedeCerrarManual() && ! ($estado['completada'] ?? false),
        ]);
    }

    public function completarManual(string $tipo, int $id): JsonResponse|RedirectResponse
    {
        $this->autorizarCierreManual();

        try {
            match ($tipo) {
                SimulacionRutaCatalogo::TIPO_AGRICOLA => $this->cerrarManualAgricola($id),
                SimulacionRutaCatalogo::TIPO_DISTRIBUCION => $this->cerrarManualDistribucion($id),
                SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA => $this->cerrarManualPlantaMayorista($id),
                default => abort(404),
            };
        } catch (\InvalidArgumentException $e) {
            if (request()->expectsJson()) {
                return response()->json(['mensaje' => $e->getMessage()], 422);
            }

            return back()->with('warning', $e->getMessage());
        }

        $mensaje = 'Recorrido marcado como completado correctamente.';

        if (request()->expectsJson()) {
            return response()->json([
                'mensaje' => $mensaje,
                'completada' => true,
            ]);
        }

        return redirect()
            ->route('logistica.rutas-tiempo-real.index')
            ->with('success', $mensaje);
    }

    public function estado(string $tipo, int $id): JsonResponse
    {
        $this->autorizarVerIndividual($tipo, $id);

        if ($tipo === SimulacionRutaCatalogo::TIPO_AGRICOLA) {
            $envio = EnvioAsignacionMultiple::query()->findOrFail($id);

            return response()->json($this->simulacion->estadoAgricola($envio));
        }

        if ($tipo === SimulacionRutaCatalogo::TIPO_DISTRIBUCION
            || $tipo === SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA) {
            $ruta = RutaDistribucion::query()->findOrFail($id);

            return response()->json($this->simulacion->estadoDistribucion($ruta));
        }

        abort(404);
    }

    private function autorizarSupervisor(): void
    {
        abort_unless($this->puedeVerTodasLasRutas(), 403);
    }

    private function autorizarVerIndividual(string $tipo, int $id): void
    {
        if ($this->puedeVerTodasLasRutas()) {
            return;
        }

        $user = auth()->user();
        abort_unless($user !== null, 403);

        if ($tipo === SimulacionRutaCatalogo::TIPO_AGRICOLA) {
            abort_unless($this->puedeVerEnvioAgricola($id, $user), 403);

            return;
        }

        if ($tipo === SimulacionRutaCatalogo::TIPO_DISTRIBUCION) {
            abort_unless($this->puedeVerRutaDistribucion($id, $user), 403);

            return;
        }

        if ($tipo === SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA) {
            abort_unless($this->puedeVerTrasladoPlantaMayorista($id, $user), 403);

            return;
        }

        abort(403);
    }

    private function puedeVerTodasLasRutas(): bool
    {
        $user = auth()->user();

        return $user && (
            UsuarioRol::esAdminGlobal($user)
            || UsuarioRol::esJefePlanta($user)
            || UsuarioRol::esJefeMayorista($user)
            || ($user->can('asignaciones.read') && ! UsuarioRol::esTransportista($user) && ! UsuarioRol::esMinorista($user))
        );
    }

    private function puedeVerEnvioAgricola(int $id, $user): bool
    {
        $envio = EnvioAsignacionMultiple::query()
            ->with('pedido')
            ->find($id);

        if ($envio === null || ! SimulacionRutaCatalogo::simulacionActivaAgricola($envio)) {
            return false;
        }

        if (UsuarioRol::esTransportista($user)) {
            return (int) $envio->transportista_usuarioid === (int) $user->usuarioid;
        }

        if (UsuarioRol::esJefePlanta($user) || UsuarioRol::esJefeAgricultor($user)) {
            return true;
        }

        return false;
    }

    private function puedeVerRutaDistribucion(int $id, $user): bool
    {
        $ruta = RutaDistribucion::query()
            ->with(['pedidos.puntoVenta'])
            ->find($id);

        if ($ruta === null || ! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            return false;
        }

        if (UsuarioRol::esTransportista($user)) {
            return (int) $ruta->transportista_usuarioid === (int) $user->usuarioid;
        }

        if (UsuarioRol::esMinorista($user)) {
            return $ruta->pedidos->contains(
                fn (PedidoDistribucion $p) => (int) $p->puntoVenta?->usuarioid === (int) $user->usuarioid
            );
        }

        return false;
    }

    private function puedeVerTrasladoPlantaMayorista(int $id, $user): bool
    {
        $ruta = RutaDistribucion::query()->find($id);

        if ($ruta === null
            || ! RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)
            || ! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            return false;
        }

        if (UsuarioRol::esTransportista($user)) {
            return (int) $ruta->transportista_usuarioid === (int) $user->usuarioid;
        }

        if (UsuarioRol::esJefePlanta($user) || UsuarioRol::esJefeMayorista($user)) {
            return true;
        }

        return false;
    }

    private function autorizarCierreManual(): void
    {
        abort_unless($this->puedeCerrarManual(), 403);
    }

    private function puedeCerrarManual(): bool
    {
        $user = auth()->user();

        return $user && (
            UsuarioRol::esAdminGlobal($user)
            || UsuarioRol::esJefePlanta($user)
            || UsuarioRol::esJefeAgricultor($user)
            || ($user->can('asignaciones.update') && ! UsuarioRol::esTransportista($user))
        );
    }

    private function urlVolverAgricola(int $id): string
    {
        $envio = EnvioAsignacionMultiple::query()->with('pedido')->find($id);
        if ($envio?->pedido) {
            return route('agricola.pedidos.show', $envio->pedido);
        }

        return route('logistica.asignaciones.show', $id);
    }

    private function urlVolverDistribucion(int $id): string
    {
        $user = auth()->user();
        $ruta = RutaDistribucion::query()->with('pedidos')->find($id);

        if ($user && UsuarioRol::esMinorista($user) && $ruta) {
            $pedido = $ruta->pedidos->first(
                fn (PedidoDistribucion $p) => PuntoVentaAccess::puedeVerPedido($user, $p)
            );
            if ($pedido) {
                return route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => 4]);
            }
        }

        if ($ruta) {
            return route('punto-venta.rutas.show', $ruta);
        }

        return route('logistica.rutas-tiempo-real.index');
    }

    private function cerrarManualAgricola(int $id): void
    {
        $envio = EnvioAsignacionMultiple::query()->findOrFail($id);
        $this->simulacion->completarManualAgricola($envio);
    }

    private function cerrarManualDistribucion(int $id): void
    {
        $ruta = RutaDistribucion::query()->findOrFail($id);
        $this->simulacion->completarManualDistribucion($ruta);
    }

    private function cerrarManualPlantaMayorista(int $id): void
    {
        $ruta = RutaDistribucion::query()->findOrFail($id);
        abort_unless(RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta), 404);
        $this->simulacion->completarManualDistribucion($ruta);
    }

    /** @return array{0: string, 1: array<string, mixed>, 2: array<int, mixed>, 3: ?string} */
    private function resolverAgricola(int $id): array
    {
        $envio = EnvioAsignacionMultiple::query()
            ->with(['pedido.detalles', 'transportista'])
            ->findOrFail($id);

        if (SimulacionRutaCatalogo::simulacionActivaAgricola($envio)) {
            $estado = $this->simulacion->estadoAgricola($envio, true);
            $envio->refresh();
        } elseif (! EnvioAsignacionEstadoCatalogo::llegoADestino($envio)) {
            abort(404, 'La simulación de este envío ya no está activa.');
        } else {
            $estado = $this->simulacion->estadoAgricola($envio, false);
        }

        $trayecto = \App\Support\EnvioPedidoService::trayectoTexto($envio);

        return [
            $envio->externo_envio_id ?? 'Envío #'.$id,
            $estado,
            $estado['paradas'] ?? [],
            $trayecto,
        ];
    }

    /** @return array{0: string, 1: array<string, mixed>, 2: array<int, mixed>, 3: ?string} */
    private function resolverDistribucion(int $id): array
    {
        $ruta = RutaDistribucion::query()
            ->with(['transportista', 'almacenOrigen'])
            ->findOrFail($id);

        if (SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            $estado = $this->simulacion->estadoDistribucion($ruta, true);
            $ruta->refresh();
        } elseif ($ruta->estado !== RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            abort(404, 'La simulación de esta ruta ya no está activa.');
        } else {
            $estado = $this->simulacion->estadoDistribucion($ruta, false);
        }

        $trayecto = app(\App\Services\DistribucionRutaService::class)->trayectoTexto($ruta);

        return [$ruta->codigo, $estado, $estado['paradas'] ?? [], $trayecto];
    }

    /** @return array{0: string, 1: array<string, mixed>, 2: array<int, mixed>, 3: ?string} */
    private function resolverPlantaMayorista(int $id): array
    {
        $ruta = RutaDistribucion::query()
            ->with(['transportista', 'almacenPlantaOrigen', 'almacenMayoristaDestino'])
            ->findOrFail($id);

        abort_unless(RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta), 404);

        if (SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            $estado = $this->simulacion->estadoDistribucion($ruta, true);
            $ruta->refresh();
        } elseif ($ruta->estado !== RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            abort(404, 'La simulación de este traslado ya no está activa.');
        } else {
            $estado = $this->simulacion->estadoDistribucion($ruta, false);
        }

        $trayecto = app(\App\Services\TrasladoPlantaMayoristaService::class)->trayectoTexto($ruta);

        return [$ruta->codigo, $estado, $estado['paradas'] ?? [], $trayecto];
    }

    private function urlVolverPlantaMayorista(int $id): string
    {
        $ruta = RutaDistribucion::query()->find($id);
        if ($ruta) {
            return route('logistica.traslados-planta.show', $ruta);
        }

        return route('logistica.rutas-tiempo-real.index');
    }
}
