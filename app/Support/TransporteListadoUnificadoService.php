<?php

namespace App\Support;

use App\Models\Pedido;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use App\Services\DistribucionRutaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

final class TransporteListadoUnificadoService
{
    public function __construct(
        private readonly DistribucionRutaService $rutasDistribucion
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function prepararListado(Request $request): array
    {
        $user = $request->user();
        $esTransportista = UsuarioRol::esTransportista($user);
        $filtroTransportista = (int) $request->query('transportista', 0);

        $pedidos = EnvioListadoService::queryBase($user, $esTransportista, $request, $filtroTransportista)
            ->get();
        $rutas = $this->queryRutas($user, $esTransportista, $request, $filtroTransportista)
            ->with([
                'transportista.perfilTransportista.vehiculo',
                'vehiculo',
                'almacenOrigen',
                'almacenPlantaOrigen',
                'almacenMayoristaDestino',
                'paradas',
                'pedidos.detalles',
                'detallesTraslado.insumo',
            ])
            ->get();

        $items = collect();
        foreach ($pedidos as $pedido) {
            $items->push($this->mapearPedido($pedido));
        }
        foreach ($rutas as $ruta) {
            $items->push($this->mapearRuta($ruta));
        }

        $ordenados = $items->sortByDesc(fn (array $item) => $item['fecha_orden'])->values();
        $pagina = max(1, (int) $request->query('page', 1));
        $porPagina = 20;
        $total = $ordenados->count();
        $slice = $ordenados->slice(($pagina - 1) * $porPagina, $porPagina)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $transportistas = $esTransportista
            ? collect()
            : Usuario::query()
                ->where('role', 'transportista')
                ->where('activo', true)
                ->orderBy('nombre')
                ->orderBy('apellido')
                ->get();

        $transportistaFiltroNombre = '';
        if ($filtroTransportista > 0) {
            $tSel = $transportistas->firstWhere('usuarioid', $filtroTransportista)
                ?? Usuario::query()->find($filtroTransportista);
            $transportistaFiltroNombre = trim(($tSel?->nombre ?? '').' '.($tSel?->apellido ?? ''));
        }

        $resumenEnvios = (! $esTransportista && $user?->can('asignaciones.create'))
            ? self::resumenUnificado()
            : null;

        return [
            'items' => $paginator,
            'transportistas' => $transportistas,
            'filtroTransportista' => $filtroTransportista,
            'transportistaFiltroNombre' => $transportistaFiltroNombre,
            'esTransportista' => $esTransportista,
            'estadosPedido' => PedidoCatalogo::opcionesEstadoEnSelector(),
            'estadosLogistica' => EnvioAsignacionEstadoCatalogo::opcionesFiltro(),
            'resumenEnvios' => $resumenEnvios,
            'puedeAsignarLogistica' => (bool) $user?->can('asignaciones.create'),
            'urlListado' => $request->routeIs('pedidos.*')
                ? route('pedidos.index')
                : route('logistica.asignaciones.listado'),
        ];
    }

    /**
     * @return Builder<RutaDistribucion>
     */
    private function queryRutas(
        ?Usuario $user,
        bool $esTransportista,
        Request $request,
        int $filtroTransportista = 0
    ): Builder {
        $query = RutaDistribucion::query()
            ->orderByDesc('fecha_salida')
            ->orderByDesc('rutadistribucionid');

        if ($esTransportista) {
            $query->where('transportista_usuarioid', $user?->usuarioid);
        } elseif ($filtroTransportista > 0) {
            $query->where('transportista_usuarioid', $filtroTransportista);
        }

        if (! $esTransportista && $request->filled('transportista_nombre')) {
            $nombre = $request->string('transportista_nombre')->trim()->toString();
            $query->whereHas('transportista', function ($q) use ($nombre) {
                $q->where('nombre', 'like', "%{$nombre}%")
                    ->orWhere('apellido', 'like', "%{$nombre}%")
                    ->orWhere('nombreusuario', 'like', "%{$nombre}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$nombre}%"]);
            });
        }

        if ($request->filled('vehiculo')) {
            $placa = $request->string('vehiculo')->trim()->toString();
            $query->where(function ($q) use ($placa) {
                $q->whereHas('vehiculo', fn ($v) => $v->where('placa', 'like', "%{$placa}%"))
                    ->orWhereHas('transportista.perfilTransportista.vehiculo', fn ($v) => $v->where('placa', 'like', "%{$placa}%"));
            });
        }

        if ($request->filled('estado_logistica')) {
            $filtro = $request->string('estado_logistica')->toString();
            $estadosRuta = match ($filtro) {
                'en_camino' => [RutaDistribucionCatalogo::ESTADO_EN_RUTA],
                'recibidos' => [RutaDistribucionCatalogo::ESTADO_COMPLETADA],
                'asignados' => [RutaDistribucionCatalogo::ESTADO_PLANIFICADA],
                default => [],
            };
            if ($estadosRuta !== []) {
                $query->whereIn('estado', $estadosRuta);
            } elseif ($filtro !== '') {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('estado')) {
            $query->whereRaw('1 = 0');
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $query->where(function ($w) use ($term) {
                $w->where('codigo', 'like', "%{$term}%")
                    ->orWhere('nombre', 'like', "%{$term}%")
                    ->orWhereHas('almacenOrigen', fn ($a) => $a->where('nombre', 'like', "%{$term}%"))
                    ->orWhereHas('transportista', function ($t) use ($term) {
                        $t->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('nombreusuario', 'like', "%{$term}%");
                    })
                    ->orWhereHas('paradas', fn ($p) => $p->where('destino', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_salida', '>=', $request->string('desde')->toString());
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_salida', '<=', $request->string('hasta')->toString());
        }

        $query->where(function (Builder $w) {
            $w->where('tipo_ruta', '!=', RutaDistribucionCatalogo::TIPO_RUTA_PLANTA_MAYORISTA)
                ->orWhereHas('detallesTraslado');
        });

        return $query;
    }

    /** @return array<string, mixed> */
    private function mapearPedido(Pedido $pedido): array
    {
        $asignacion = $pedido->envioAsignacion;
        $itemsCount = $pedido->detalles?->count() ?? 0;
        $totalKg = $pedido->detalles?->sum('cantidad') ?? 0;
        $transportistaAsignado = $asignacion?->transportista;
        $logisticaEnvio = EnvioPedidoService::datosLogistica($asignacion);
        $estadoVisual = PedidoCatalogo::badgeEstadoLista($logisticaEnvio, $pedido);
        $faseLogistica = PedidoCatalogo::faseLogistica($logisticaEnvio);
        $codigoEnvio = $asignacion?->externo_envio_id ?? $pedido->numero_solicitud;
        $fecha = Carbon::parse($pedido->fechapedido);

        return [
            'tipo' => 'agricola',
            'tipo_etiqueta' => 'Almacén → Planta',
            'codigo' => $codigoEnvio,
            'subcodigo' => $pedido->numero_solicitud !== $codigoEnvio ? $pedido->numero_solicitud : null,
            'producto_label' => $pedido->detalles->first()?->cultivo_personalizado ?? '—',
            'producto_extra' => $itemsCount > 1 ? '+'.($itemsCount - 1).' ítem(s) más' : null,
            'total_kg' => $totalKg,
            'destino_label' => EnvioPedidoService::etiquetaPlantaDestinoLista($pedido),
            'chofer_nombre' => $transportistaAsignado
                ? trim($transportistaAsignado->nombre.' '.($transportistaAsignado->apellido ?? ''))
                : null,
            'vehiculo_placa' => $asignacion?->vehiculo_ref,
            'trayecto_partes' => EnvioPedidoService::trayectoPartesPedido($pedido),
            'estado_badge' => $estadoVisual,
            'fecha' => $fecha,
            'fecha_orden' => $fecha->timestamp,
            'costo_bs' => $asignacion?->costo_bs !== null ? (float) $asignacion->costo_bs : null,
            'ver_url' => $asignacion
                ? route('logistica.asignaciones.show', $asignacion)
                : route('pedidos.show', $pedido),
            'pedido' => $pedido,
            'asignacion' => $asignacion,
            'ruta' => null,
            'puede_asignar' => PedidoCatalogo::puedeAsignarTransportista($pedido) && ! $transportistaAsignado,
            'fase_logistica' => $faseLogistica,
        ];
    }

    /** @return array<string, mixed> */
    private function mapearRuta(RutaDistribucion $ruta): array
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            return $this->mapearTrasladoPlantaMayorista($ruta);
        }

        $transportista = $ruta->transportista;
        $badge = RutaDistribucionCatalogo::badgeEstado($ruta);
        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_PLANIFICADA) {
            $badge = ['clase' => 'warning', 'etiqueta' => 'Pendiente de salida'];
        }
        $trayecto = $this->rutasDistribucion->trayectoPartes($ruta);
        $paradasEntrega = $ruta->paradas?->where('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)->count() ?? 0;
        $totalKg = $ruta->pedidos?->flatMap->detalles->sum('cantidad') ?? 0;
        $primerProducto = $ruta->pedidos?->first()?->detalles?->first()?->insumo?->nombre
            ?? $ruta->pedidos?->first()?->detalles?->first()?->cultivo_personalizado
            ?? 'Distribución';
        $fecha = $ruta->fecha_salida ?? $ruta->created_at;

        $trayectoPartes = null;
        if ($trayecto) {
            $destinos = $trayecto['destinos'] ?? [];
            $destinoResumen = count($destinos) > 1
                ? $destinos[0].' +'.(count($destinos) - 1).' más'
                : ($destinos[0] ?? null);
            $trayectoPartes = [
                'recogidas' => [$trayecto['origen'] ?? 'Planta'],
                'destino' => $destinoResumen,
            ];
        }

        return [
            'tipo' => 'distribucion',
            'tipo_etiqueta' => 'Planta → PDV',
            'codigo' => $ruta->codigo,
            'subcodigo' => $ruta->nombre !== $ruta->codigo ? $ruta->nombre : null,
            'producto_label' => $primerProducto,
            'producto_extra' => $paradasEntrega > 1 ? $paradasEntrega.' punto(s) de venta' : null,
            'total_kg' => $totalKg > 0 ? $totalKg : null,
            'destino_label' => $paradasEntrega.' entrega(s)',
            'chofer_nombre' => $transportista
                ? trim($transportista->nombre.' '.($transportista->apellido ?? ''))
                : null,
            'vehiculo_placa' => $ruta->vehiculo?->placa
                ?? $transportista?->perfilTransportista?->vehiculo?->placa,
            'trayecto_partes' => $trayectoPartes,
            'estado_badge' => [
                'etiqueta' => $badge['etiqueta'],
                'clase' => 'pedido-estado-'.match ($badge['clase']) {
                    'success' => 'recibido',
                    'primary' => 'camino',
                    'info' => 'logistica',
                    default => 'agricola',
                },
                'titulo' => $badge['etiqueta'],
            ],
            'fecha' => $fecha,
            'fecha_orden' => $fecha?->timestamp ?? 0,
            'costo_bs' => $ruta->costo_bs !== null ? (float) $ruta->costo_bs : null,
            'ver_url' => route('punto-venta.rutas.show', $ruta),
            'pedido' => null,
            'asignacion' => null,
            'ruta' => $ruta,
            'puede_asignar' => false,
            'fase_logistica' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function mapearTrasladoPlantaMayorista(RutaDistribucion $ruta): array
    {
        $transportista = $ruta->transportista;
        $badge = RutaDistribucionCatalogo::badgeEstado($ruta);
        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_PLANIFICADA) {
            $badge = ['clase' => 'warning', 'etiqueta' => 'Pendiente de salida'];
        }

        $detalles = $ruta->detallesTraslado ?? collect();
        $totalKg = $detalles->sum('cantidad');
        $primerProducto = $detalles->first()?->producto_nombre ?? 'Traslado';
        $extraProductos = $detalles->count() > 1 ? '+'.($detalles->count() - 1).' producto(s)' : null;
        $origen = $ruta->almacenPlantaOrigen?->nombre ?? 'Planta';
        $destino = $ruta->almacenMayoristaDestino?->nombre ?? 'Mayorista';
        $fecha = $ruta->fecha_salida ?? $ruta->created_at;

        return [
            'tipo' => 'traslado_planta_mayorista',
            'tipo_etiqueta' => 'Planta → Mayorista',
            'codigo' => $ruta->codigo,
            'subcodigo' => $ruta->nombre !== $ruta->codigo ? $ruta->nombre : null,
            'producto_label' => $primerProducto,
            'producto_extra' => $extraProductos,
            'total_kg' => $totalKg > 0 ? $totalKg : null,
            'destino_label' => $destino,
            'chofer_nombre' => $transportista
                ? trim($transportista->nombre.' '.($transportista->apellido ?? ''))
                : null,
            'vehiculo_placa' => $ruta->vehiculo?->placa
                ?? $transportista?->perfilTransportista?->vehiculo?->placa,
            'trayecto_partes' => [
                'recogidas' => [$origen],
                'destino' => $destino,
            ],
            'estado_badge' => [
                'etiqueta' => $badge['etiqueta'],
                'clase' => 'pedido-estado-'.match ($badge['clase']) {
                    'success' => 'recibido',
                    'primary' => 'camino',
                    'info' => 'logistica',
                    default => 'agricola',
                },
                'titulo' => $badge['etiqueta'],
            ],
            'fecha' => $fecha,
            'fecha_orden' => $fecha?->timestamp ?? 0,
            'costo_bs' => $ruta->costo_bs !== null ? (float) $ruta->costo_bs : null,
            'ver_url' => route('logistica.traslados-planta.show', $ruta),
            'pedido' => null,
            'asignacion' => null,
            'ruta' => $ruta,
            'puede_asignar' => false,
            'fase_logistica' => null,
        ];
    }

    /** @return array<string, int> */
    public static function resumenUnificado(): array
    {
        $agricola = EnvioListadoService::resumenDesdePedidos();
        $distribucionBase = RutaDistribucion::query();

        $distribucion = [
            'total' => (clone $distribucionBase)->count(),
            'asignados' => (clone $distribucionBase)->where('estado', RutaDistribucionCatalogo::ESTADO_PLANIFICADA)->count(),
            'en_camino' => (clone $distribucionBase)->where('estado', RutaDistribucionCatalogo::ESTADO_EN_RUTA)->count(),
            'recibidos' => (clone $distribucionBase)->where('estado', RutaDistribucionCatalogo::ESTADO_COMPLETADA)->count(),
            'recibidos_hoy' => (clone $distribucionBase)->where('estado', RutaDistribucionCatalogo::ESTADO_COMPLETADA)
                ->whereDate('fecha_salida', now()->toDateString())->count(),
        ];

        return [
            'total' => $agricola['total'] + $distribucion['total'],
            'asignados' => $agricola['asignados'] + $distribucion['asignados'],
            'en_camino' => $agricola['en_camino'] + $distribucion['en_camino'],
            'recibidos' => $agricola['recibidos'] + $distribucion['recibidos'],
            'recibidos_hoy' => $agricola['recibidos_hoy'] + $distribucion['recibidos_hoy'],
        ];
    }
}
