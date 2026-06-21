<?php

namespace App\Services;

use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\EtiquetaDemo;
use App\Support\PedidoCatalogo;
use App\Support\RutaDistribucionCatalogo;
use Illuminate\Support\Collection;

class ReporteDistribucionService
{
    /** @return array<string, mixed> */
    public function datosReporte(): array
    {
        $filas = $this->filasOperativas();

        $porEstado = [];
        $porDestino = [];
        $enviosPorEstado = [];
        $enviosPorDestino = [];
        $enviosPorTransportistaId = [];

        foreach ($filas as $fila) {
            $estadoEtiqueta = $fila['estado_etiqueta'];
            $destino = $fila['destino'];

            $porEstado[$estadoEtiqueta] = ($porEstado[$estadoEtiqueta] ?? 0) + 1;
            $porDestino[$destino] = ($porDestino[$destino] ?? 0) + 1;
            $enviosPorEstado[$estadoEtiqueta][] = $fila;
            $enviosPorDestino[$destino][] = $fila;

            $tid = (int) ($fila['transportista_id'] ?? 0);
            if ($tid > 0) {
                $enviosPorTransportistaId[$tid][] = $fila;
            }
        }

        arsort($porEstado);
        arsort($porDestino);

        return [
            'counts' => $this->conteoResumen($filas),
            'topTransportistas' => $this->topTransportistas($filas),
            'porEstado' => $porEstado,
            'porDestino' => $porDestino,
            'enviosPorEstado' => $enviosPorEstado,
            'enviosPorDestino' => $enviosPorDestino,
            'enviosPorTransportistaId' => $enviosPorTransportistaId,
            'estadosLista' => array_keys($porEstado),
            'destinosLista' => array_keys($porDestino),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function filasOperativas(): array
    {
        $filas = [];

        $asignaciones = EnvioAsignacionMultiple::query()
            ->with(['pedido', 'transportista', 'almacen'])
            ->whereHas('pedido', fn ($p) => PedidoCatalogo::aplicarFiltroLogistica($p))
            ->orderByDesc('envioasignacionmultipleid')
            ->get();

        foreach ($asignaciones as $asignacion) {
            if ($this->esRegistroDemo($asignacion)) {
                continue;
            }

            $filas[] = $this->mapearAsignacion($asignacion);
        }

        $rutas = RutaDistribucion::query()
            ->with(['transportista', 'almacenOrigen', 'paradas'])
            ->orderByDesc('rutadistribucionid')
            ->get();

        foreach ($rutas as $ruta) {
            if ($this->esRutaDemo($ruta)) {
                continue;
            }

            $filas[] = $this->mapearRuta($ruta);
        }

        return $filas;
    }

    /** @return array<string, int> */
    private function conteoResumen(array $filas): array
    {
        $conteo = [
            'total' => count($filas),
            'pendientes' => 0,
            'asignados' => 0,
            'en_ruta' => 0,
            'entregados' => 0,
            'stock_productos_todas_bodegas' => 0,
            'lineas_inventario_envio' => 0,
        ];

        foreach ($filas as $fila) {
            match ($fila['estado_clave'] ?? '') {
                'pendiente', 'creada' => $conteo['pendientes']++,
                'asignado', 'asignada' => $conteo['asignados']++,
                'en_ruta', 'en_transito', 'en_transporte_planta' => $conteo['en_ruta']++,
                'recibido_planta', 'entregado', 'entregada', 'completada' => $conteo['entregados']++,
                default => null,
            };
        }

        return $conteo;
    }

    /**
     * @param  list<array<string, mixed>>  $filas
     * @return Collection<int, object{transportista_usuarioid: int, c: int}>
     */
    private function topTransportistas(array $filas): Collection
    {
        $conteo = [];
        foreach ($filas as $fila) {
            $tid = (int) ($fila['transportista_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            $conteo[$tid] = ($conteo[$tid] ?? 0) + 1;
        }

        arsort($conteo);

        return collect($conteo)
            ->take(10)
            ->map(fn (int $c, int $tid) => (object) [
                'transportista_usuarioid' => $tid,
                'c' => $c,
            ])
            ->values();
    }

    /** @return array<string, mixed> */
    private function mapearAsignacion(EnvioAsignacionMultiple $asignacion): array
    {
        $estadoRaw = strtolower(trim((string) ($asignacion->estado ?? 'pendiente')));
        $detalles = is_array($asignacion->detalles_productos) ? $asignacion->detalles_productos : [];
        $transportista = $asignacion->transportista;

        return [
            'id' => (int) $asignacion->envioasignacionmultipleid,
            'tipo' => 'agricola',
            'externo_envio_id' => $asignacion->externo_envio_id,
            'nombre_remitente' => $detalles['remitente'] ?? ($asignacion->almacen?->nombre ?? 'Almacén agrícola'),
            'estado' => $estadoRaw,
            'estado_clave' => $estadoRaw,
            'estado_etiqueta' => EnvioAsignacionEstadoCatalogo::etiqueta($estadoRaw),
            'destino' => $this->resolverDestinoAsignacion($asignacion),
            'transportista_id' => $asignacion->transportista_usuarioid,
            'ver_url' => route('logistica.asignaciones.show', $asignacion),
        ];
    }

    /** @return array<string, mixed> */
    private function mapearRuta(RutaDistribucion $ruta): array
    {
        $estadoRaw = strtolower(trim((string) ($ruta->estado ?? RutaDistribucionCatalogo::ESTADO_PLANIFICADA)));

        return [
            'id' => (int) $ruta->rutadistribucionid,
            'tipo' => 'distribucion',
            'externo_envio_id' => $ruta->codigo,
            'nombre_remitente' => $ruta->almacenOrigen?->nombre ?? 'Planta',
            'estado' => $estadoRaw,
            'estado_clave' => $estadoRaw === RutaDistribucionCatalogo::ESTADO_EN_RUTA ? 'en_ruta' : $estadoRaw,
            'estado_etiqueta' => RutaDistribucionCatalogo::etiquetaEstado($ruta->estado),
            'destino' => $this->resolverDestinoRuta($ruta),
            'transportista_id' => $ruta->transportista_usuarioid,
            'ver_url' => route('punto-venta.rutas.show', $ruta),
        ];
    }

    private function resolverDestinoAsignacion(EnvioAsignacionMultiple $asignacion): string
    {
        $pedido = $asignacion->pedido;
        $destino = trim((string) ($pedido?->nombre_planta ?? ''));

        if ($destino === '') {
            $destino = trim((string) ($pedido?->direccion_texto ?? ''));
        }

        if ($destino === '') {
            $destino = EnvioPedidoService::etiquetaPlantaDestinoLista($pedido) ?? '';
        }

        if ($destino === '' || $destino === '—') {
            $detalles = is_array($asignacion->detalles_productos) ? $asignacion->detalles_productos : [];
            $destino = trim((string) ($detalles['destino'] ?? ''));
        }

        if ($destino === '') {
            $destino = 'Destino no registrado';
        }

        return $destino;
    }

    private function resolverDestinoRuta(RutaDistribucion $ruta): string
    {
        $paradas = $ruta->paradas
            ?->where('tipo', RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)
            ->pluck('destino')
            ->map(fn ($d) => trim((string) $d))
            ->filter()
            ->unique()
            ->values() ?? collect();

        if ($paradas->isEmpty()) {
            return $ruta->nombre ?: 'Ruta sin destinos definidos';
        }

        if ($paradas->count() === 1) {
            return (string) $paradas->first();
        }

        return $paradas->first().' (+'.($paradas->count() - 1).' más)';
    }

    private function esRegistroDemo(EnvioAsignacionMultiple $asignacion): bool
    {
        if ($this->textoEsDemoOperativo($asignacion->externo_envio_id)) {
            return true;
        }

        $pedido = $asignacion->pedido;
        if ($pedido !== null) {
            if ($this->textoEsDemoOperativo($pedido->numero_solicitud)) {
                return true;
            }
            if ($this->textoEsDemoOperativo($pedido->nombre_planta)) {
                return true;
            }
        }

        $detalles = is_array($asignacion->detalles_productos) ? $asignacion->detalles_productos : [];
        foreach (['remitente', 'destino', 'nota', 'referencia'] as $campo) {
            if ($this->textoEsDemoOperativo($detalles[$campo] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function esRutaDemo(RutaDistribucion $ruta): bool
    {
        if ($this->textoEsDemoOperativo($ruta->codigo) || $this->textoEsDemoOperativo($ruta->nombre)) {
            return true;
        }

        foreach ($ruta->paradas ?? [] as $parada) {
            if ($this->textoEsDemoOperativo($parada->destino)) {
                return true;
            }
        }

        return false;
    }

    private function textoEsDemoOperativo(?string $texto): bool
    {
        $t = trim((string) $texto);
        if ($t === '') {
            return false;
        }

        if (EtiquetaDemo::esDemo($t)) {
            return true;
        }

        return (bool) preg_match('/\[(MOD-PANEL|DEMO|demo)/i', $t)
            || (bool) preg_match('/^(MOD-PANEL-|ENV-MOD-)/i', $t);
    }
}
