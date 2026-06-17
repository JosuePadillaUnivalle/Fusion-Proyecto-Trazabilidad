<?php

namespace App\Services;

use App\Models\EnvioAsignacionMultiple;
use App\Models\PedidoDistribucion;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Models\Usuario;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\RutaDistribucionCatalogo;
use App\Support\RutaPorCallesService;
use App\Support\SimulacionRutaCatalogo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SimulacionRutaService
{
    public function __construct(
        private readonly RutaPorCallesService $rutasCalles,
        private readonly DistribucionRutaService $distribucion,
        private readonly RecepcionPlantaEnvioService $recepcionPlanta,
        private readonly NotificacionUsuarioService $notificaciones,
    ) {}

    public function empezarAgricola(EnvioAsignacionMultiple $envio): void
    {
        $envio->loadMissing(['pedido', 'ruta.paradas']);

        if (! SimulacionRutaCatalogo::puedeEmpezarAgricola($envio)) {
            throw new InvalidArgumentException('Este envío no está listo para iniciar la ruta.');
        }

        $paradas = EnvioPedidoService::paradasMapaEnvio($envio);
        if (count($paradas) < 2) {
            throw new InvalidArgumentException('El envío no tiene coordenadas suficientes para simular la ruta.');
        }

        $geo = $this->construirGeoJson($paradas);
        $duracion = $this->calcularDuracionSegundos($geo, $paradas);

        $envio->update([
            'estado' => 'en_transporte_planta',
            'fecha_asignacion' => $envio->fecha_asignacion ?? now(),
            'simulacion_inicio_at' => now(),
            'simulacion_duracion_seg' => $duracion,
            'simulacion_geojson' => $geo,
        ]);
    }

    public function empezarDistribucion(RutaDistribucion $ruta): void
    {
        $ruta->loadMissing(['paradas', 'pedidos']);

        if (! SimulacionRutaCatalogo::puedeEmpezarDistribucion($ruta)) {
            throw new InvalidArgumentException('Esta ruta no está lista para iniciar.');
        }

        $paradas = $this->distribucion->paradasMapa($ruta);
        if (count($paradas) < 2) {
            throw new InvalidArgumentException('La ruta no tiene coordenadas suficientes para la simulación.');
        }

        $geo = $this->construirGeoJson($paradas);
        $duracion = $this->calcularDuracionSegundos($geo, $paradas);

        DB::transaction(function () use ($ruta, $geo, $duracion) {
            $ruta->update([
                'estado' => RutaDistribucionCatalogo::ESTADO_EN_RUTA,
                'fecha_salida' => now(),
                'simulacion_inicio_at' => now(),
                'simulacion_duracion_seg' => $duracion,
                'simulacion_geojson' => $geo,
            ]);

            foreach ($ruta->pedidos as $pedido) {
                $pedido->update([
                    'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
                    'fecha_envio' => now(),
                ]);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function estadoAgricola(EnvioAsignacionMultiple $envio, bool $intentarCompletar = true): array
    {
        if ($intentarCompletar && $this->debeCompletarAgricola($envio)) {
            try {
                $this->completarAgricola($envio);
            } catch (\Throwable $e) {
                report($e);
                $this->marcarRecepcionMinimaAgricola($envio);
            }
            $envio->refresh();
        }

        return $this->armarEstado(
            SimulacionRutaCatalogo::TIPO_AGRICOLA,
            $envio->externo_envio_id ?? ('#'.$envio->envioasignacionmultipleid),
            $envio->simulacion_inicio_at,
            (int) ($envio->simulacion_duracion_seg ?? 0),
            $envio->simulacion_geojson,
            EnvioAsignacionEstadoCatalogo::llegoADestino($envio),
            EnvioPedidoService::paradasMapaEnvio($envio),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function estadoDistribucion(RutaDistribucion $ruta, bool $intentarCompletar = true): array
    {
        if ($intentarCompletar && $this->debeCompletarDistribucion($ruta)) {
            try {
                $this->completarDistribucion($ruta);
            } catch (\Throwable $e) {
                report($e);
                $this->marcarCompletadaMinimaDistribucion($ruta);
            }
            $ruta->refresh();
        }

        return $this->armarEstado(
            SimulacionRutaCatalogo::TIPO_DISTRIBUCION,
            $ruta->codigo,
            $ruta->simulacion_inicio_at,
            (int) ($ruta->simulacion_duracion_seg ?? 0),
            $ruta->simulacion_geojson,
            $ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA,
            $this->distribucion->paradasMapa($ruta),
        );
    }

    public function completarManualAgricola(EnvioAsignacionMultiple $envio): void
    {
        if (! SimulacionRutaCatalogo::simulacionActivaAgricola($envio)) {
            throw new InvalidArgumentException('Este envío no tiene un recorrido activo para cerrar.');
        }

        try {
            $this->completarAgricola($envio);
        } catch (\Throwable $e) {
            report($e);
            $this->marcarRecepcionMinimaAgricola($envio);
        }
    }

    public function completarManualDistribucion(RutaDistribucion $ruta): void
    {
        if (! SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta)) {
            throw new InvalidArgumentException('Esta ruta no tiene un recorrido activo para cerrar.');
        }

        try {
            $this->completarDistribucion($ruta);
        } catch (\Throwable $e) {
            report($e);
            $this->marcarCompletadaMinimaDistribucion($ruta);
        }
    }

    /** @return Collection<int, array<string, mixed>> */
    public function listarActivas(): Collection
    {
        $agricolas = EnvioAsignacionMultiple::query()
            ->with(['transportista', 'pedido.detalles'])
            ->whereNotNull('simulacion_inicio_at')
            ->whereNull('fecha_recepcion_planta')
            ->whereNotIn('estado', ['recibido_planta', 'entregado', 'entregada', 'cancelado', 'cancelada'])
            ->get()
            ->filter(fn (EnvioAsignacionMultiple $e) => SimulacionRutaCatalogo::simulacionActivaAgricola($e))
            ->map(fn (EnvioAsignacionMultiple $e) => $this->mapearItemLista($e));

        $distribucion = RutaDistribucion::query()
            ->with(['transportista', 'vehiculo', 'almacenOrigen', 'paradas'])
            ->whereNotNull('simulacion_inicio_at')
            ->where('estado', RutaDistribucionCatalogo::ESTADO_EN_RUTA)
            ->get()
            ->map(fn (RutaDistribucion $r) => $this->mapearItemLista($r));

        return $agricolas
            ->merge($distribucion)
            ->filter()
            ->sortByDesc('progreso')
            ->values();
    }

    public function completarAgricola(EnvioAsignacionMultiple $envio): void
    {
        if (EnvioAsignacionEstadoCatalogo::llegoADestino($envio)) {
            return;
        }

        $envio->loadMissing('pedido');
        $transportista = $envio->transportista ?? Usuario::query()->find($envio->transportista_usuarioid);

        if ($envio->pedido && $transportista) {
            $this->recepcionPlanta->confirmarDesdePedido($envio->pedido, $transportista);
            $envio->refresh();
            $this->notificaciones->simulacionCompletadaAgricola($envio->fresh(['pedido', 'transportista']));

            return;
        }

        $envio->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
            'estado' => 'recibido_planta',
            'fecha_recepcion_planta' => now(),
        ]));
        $this->notificaciones->simulacionCompletadaAgricola($envio->fresh(['pedido', 'transportista']));
    }

    public function completarDistribucion(RutaDistribucion $ruta): void
    {
        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            return;
        }

        DB::transaction(function () use ($ruta) {
            $ruta->update(['estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA]);

            $ruta->paradas()
                ->where('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)
                ->update(['estado' => 'completada']);

            PedidoDistribucion::query()
                ->where('rutadistribucionid', $ruta->rutadistribucionid)
                ->update([
                    'estado' => PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
                    'fecha_recepcion' => now(),
                ]);
        });

        $this->notificaciones->simulacionCompletadaDistribucion($ruta->fresh(['transportista', 'almacenOrigen']));
    }

    public function debeCompletarAgricola(EnvioAsignacionMultiple $envio): bool
    {
        $duracion = SimulacionRutaCatalogo::duracionEfectiva((int) ($envio->simulacion_duracion_seg ?? 0));

        return $envio->simulacion_inicio_at !== null
            && ! EnvioAsignacionEstadoCatalogo::llegoADestino($envio)
            && $duracion > 0
            && $this->segundosTranscurridos($envio->simulacion_inicio_at) >= $duracion;
    }

    public function debeCompletarDistribucion(RutaDistribucion $ruta): bool
    {
        $duracion = SimulacionRutaCatalogo::duracionEfectiva((int) ($ruta->simulacion_duracion_seg ?? 0));

        return $ruta->simulacion_inicio_at !== null
            && $ruta->estado === RutaDistribucionCatalogo::ESTADO_EN_RUTA
            && $duracion > 0
            && $this->segundosTranscurridos($ruta->simulacion_inicio_at) >= $duracion;
    }

    private function marcarRecepcionMinimaAgricola(EnvioAsignacionMultiple $envio): void
    {
        if (EnvioAsignacionEstadoCatalogo::llegoADestino($envio)) {
            return;
        }

        $envio->update(EnvioAsignacionEstadoCatalogo::applyToAttributes([
            'estado' => 'recibido_planta',
            'fecha_recepcion_planta' => now(),
        ]));
        $this->notificaciones->simulacionCompletadaAgricola($envio->fresh(['pedido', 'transportista']));
    }

    private function marcarCompletadaMinimaDistribucion(RutaDistribucion $ruta): void
    {
        if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) {
            return;
        }

        $ruta->update(['estado' => RutaDistribucionCatalogo::ESTADO_COMPLETADA]);
        $this->notificaciones->simulacionCompletadaDistribucion($ruta->fresh(['transportista', 'almacenOrigen']));
    }

    private function segundosTranscurridos(?Carbon $inicio): int
    {
        if ($inicio === null) {
            return 0;
        }

        return (int) $inicio->diffInSeconds(now());
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $paradas
     * @return array<string, mixed>
     */
    private function construirGeoJson(array $paradas): array
    {
        $waypoints = array_map(fn (array $p) => ['lat' => $p['lat'], 'lng' => $p['lng']], $paradas);
        $geo = $this->rutasCalles->rutaPorCalles($waypoints);

        return $geo ?? [
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['provider' => 'straight'],
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => array_map(fn (array $p) => [$p['lng'], $p['lat']], $waypoints),
                ],
            ]],
        ];
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $paradas
     */
    private function calcularDuracionSegundos(array $geo, array $paradas): int
    {
        return SimulacionRutaCatalogo::duracionEfectiva();
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $paradas
     */
    private function distanciaTotalParadas(array $paradas): float
    {
        $total = 0.0;
        for ($i = 1, $n = count($paradas); $i < $n; $i++) {
            $total += $this->haversineMetros(
                $paradas[$i - 1]['lat'],
                $paradas[$i - 1]['lng'],
                $paradas[$i]['lat'],
                $paradas[$i]['lng'],
            );
        }

        return $total;
    }

    private function haversineMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function progreso(?Carbon $inicio, int $duracionSeg): float
    {
        $duracionEfectiva = SimulacionRutaCatalogo::duracionEfectiva($duracionSeg);
        if ($inicio === null || $duracionEfectiva <= 0) {
            return 0.0;
        }

        return min(1.0, $this->segundosTranscurridos($inicio) / $duracionEfectiva);
    }

    /**
     * @param  array<string, mixed>|null  $geo
     * @return array{lat: float, lng: float}|null
     */
    public function posicionEnRuta(?array $geo, float $progreso): ?array
    {
        $coords = $this->extraerCoordenadas($geo);
        if ($coords === []) {
            return null;
        }

        if ($progreso >= 1.0) {
            $ultimo = $coords[array_key_last($coords)];

            return ['lat' => $ultimo[1], 'lng' => $ultimo[0]];
        }

        $distancias = [0.0];
        $total = 0.0;
        for ($i = 1, $n = count($coords); $i < $n; $i++) {
            $total += $this->haversineMetros($coords[$i - 1][1], $coords[$i - 1][0], $coords[$i][1], $coords[$i][0]);
            $distancias[] = $total;
        }

        if ($total <= 0) {
            return ['lat' => $coords[0][1], 'lng' => $coords[0][0]];
        }

        $objetivo = $total * $progreso;
        for ($i = 1, $n = count($coords); $i < $n; $i++) {
            if ($distancias[$i] >= $objetivo) {
                $segmento = $distancias[$i] - $distancias[$i - 1];
                $t = $segmento > 0 ? ($objetivo - $distancias[$i - 1]) / $segmento : 0;
                $lng = $coords[$i - 1][0] + ($coords[$i][0] - $coords[$i - 1][0]) * $t;
                $lat = $coords[$i - 1][1] + ($coords[$i][1] - $coords[$i - 1][1]) * $t;

                return ['lat' => $lat, 'lng' => $lng];
            }
        }

        $ultimo = $coords[array_key_last($coords)];

        return ['lat' => $ultimo[1], 'lng' => $ultimo[0]];
    }

    /**
     * @param  array<string, mixed>|null  $geo
     * @return array<int, array{0: float, 1: float}>
     */
    private function extraerCoordenadas(?array $geo): array
    {
        if (! is_array($geo)) {
            return [];
        }

        $geometry = $geo['features'][0]['geometry'] ?? null;
        if (! is_array($geometry) || ($geometry['type'] ?? '') !== 'LineString') {
            return [];
        }

        $coords = $geometry['coordinates'] ?? [];

        return is_array($coords) ? $coords : [];
    }

    /**
     * @param  array<int, array{lat: float, lng: float, orden?: int, label?: string}>  $paradas
     * @return array<string, mixed>
     */
    private function armarEstado(
        string $tipo,
        string $codigo,
        ?Carbon $inicio,
        int $duracionSeg,
        ?array $geo,
        bool $completada,
        array $paradas,
    ): array {
        $duracionEfectiva = SimulacionRutaCatalogo::duracionEfectiva($duracionSeg);
        $progreso = $completada ? 1.0 : $this->progreso($inicio, $duracionSeg);
        $geoEfectivo = $geo;
        if ($this->extraerCoordenadas($geoEfectivo) === [] && count($paradas) >= 2) {
            $geoEfectivo = $this->construirGeoJson(array_map(
                fn (array $p) => ['lat' => $p['lat'], 'lng' => $p['lng']],
                $paradas
            ));
        }
        $posicion = $this->posicionEnRuta($geoEfectivo, $progreso)
            ?? $this->posicionEntreParadas($paradas, $progreso);
        $transcurrido = $this->segundosTranscurridos($inicio);
        $restante = $completada || $inicio === null || $duracionEfectiva <= 0
            ? 0
            : max(0, $duracionEfectiva - $transcurrido);

        return [
            'tipo' => $tipo,
            'codigo' => $codigo,
            'activa' => $inicio !== null && ! $completada,
            'completada' => $completada,
            'progreso' => round($progreso * 100, 1),
            'progreso_ratio' => $progreso,
            'segundos_restantes' => $restante,
            'eta' => $completada || $restante <= 0
                ? null
                : now()->addSeconds($restante)->toIso8601String(),
            'posicion' => $posicion,
            'geojson' => $geoEfectivo,
            'paradas' => $paradas,
            'inicio_at' => $inicio?->toIso8601String(),
            'inicio_at_unix' => $inicio?->timestamp,
            'duracion_seg' => $duracionEfectiva,
        ];
    }

    /**
     * @param  array<int, array{lat: float, lng: float, orden?: int, label?: string}>  $paradas
     * @return array{lat: float, lng: float}|null
     */
    private function posicionEntreParadas(array $paradas, float $progreso): ?array
    {
        if (count($paradas) < 2) {
            return null;
        }

        $origen = $paradas[0];
        $destino = $paradas[array_key_last($paradas)];
        $ratio = min(1.0, max(0.0, $progreso));

        return [
            'lat' => $origen['lat'] + ($destino['lat'] - $origen['lat']) * $ratio,
            'lng' => $origen['lng'] + ($destino['lng'] - $origen['lng']) * $ratio,
        ];
    }

    /** @return array<string, mixed>|null */
    private function mapearItemLista(EnvioAsignacionMultiple|RutaDistribucion $item): ?array
    {
        if ($item instanceof EnvioAsignacionMultiple) {
            $estado = $this->estadoAgricola($item, true);
            $item->refresh();
            if ($estado['completada'] || ! SimulacionRutaCatalogo::simulacionActivaAgricola($item)) {
                return null;
            }
            $chofer = trim(($item->transportista?->nombre ?? '').' '.($item->transportista?->apellido ?? ''));
            $destino = $item->pedido
                ? (EnvioPedidoService::etiquetaPlantaDestinoLista($item->pedido) ?? 'Planta')
                : 'Planta';

            return [
                'tipo' => SimulacionRutaCatalogo::TIPO_AGRICOLA,
                'tipo_etiqueta' => 'Almacén → Planta',
                'id' => $item->envioasignacionmultipleid,
                'codigo' => $item->externo_envio_id ?? ('#'.$item->envioasignacionmultipleid),
                'chofer' => $chofer,
                'destino' => $destino,
                'progreso' => $estado['progreso'],
                'segundos_restantes' => $estado['segundos_restantes'],
                'ver_url' => route('logistica.rutas-tiempo-real.show', [
                    'tipo' => SimulacionRutaCatalogo::TIPO_AGRICOLA,
                    'id' => $item->envioasignacionmultipleid,
                ]),
            ];
        }

        $estado = $this->estadoDistribucion($item, true);
        $item->refresh();
        if ($estado['completada'] || ! SimulacionRutaCatalogo::simulacionActivaDistribucion($item)) {
            return null;
        }

        $chofer = trim(($item->transportista?->nombre ?? '').' '.($item->transportista?->apellido ?? ''));
        $paradas = $item->paradas?->where('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)->count() ?? 0;

        return [
            'tipo' => SimulacionRutaCatalogo::TIPO_DISTRIBUCION,
            'tipo_etiqueta' => 'Planta → PDV',
            'id' => $item->rutadistribucionid,
            'codigo' => $item->codigo,
            'chofer' => $chofer,
            'destino' => ($item->almacenOrigen?->nombre ?? 'Planta').' · '.$paradas.' entrega(s)',
            'progreso' => $estado['progreso'],
            'segundos_restantes' => $estado['segundos_restantes'],
            'ver_url' => route('logistica.rutas-tiempo-real.show', [
                'tipo' => SimulacionRutaCatalogo::TIPO_DISTRIBUCION,
                'id' => $item->rutadistribucionid,
            ]),
        ];
    }
}
