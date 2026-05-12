<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\RutaMultiEntrega;
use App\Models\Usuario;
use Illuminate\Support\Facades\Schema;

/**
 * Respuestas con forma similar a OrgTrack para el proxy cuando la API externa
 * no está disponible o no devuelve registros, usando tablas locales de logística.
 */
final class LocalOrgTrackFallback
{
    public static function enviosPayload(): array
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return self::emptyEnvios('Sin tabla envio_asignacion_multiple.');
        }

        $rows = EnvioAsignacionMultiple::query()
            ->with(['pedido', 'almacen'])
            ->orderByDesc('envioasignacionmultipleid')
            ->get();

        if ($rows->isEmpty()) {
            return self::emptyEnvios('No hay asignaciones locales. Ejecute los seeders demo de envíos.');
        }

        $data = $rows->map(function (EnvioAsignacionMultiple $a) {
            $p = $a->pedido;
            $alm = $a->almacen;
            $origen = $alm ? trim(($alm->nombre ?? '').' · '.($alm->ubicacion ?? '')) : 'Origen almacén';
            $cantidad = null;
            if ($p) {
                $detalles = $p->detalles()->get();
                if ($detalles->isNotEmpty()) {
                    $cantidad = $detalles->sum('cantidad');
                }
            }

            return [
                'id' => $a->externo_envio_id,
                'externo_envio_id' => $a->externo_envio_id,
                'estado' => $a->estado,
                'estado_actual' => $a->estado,
                'nombre_estado' => $a->estado,
                'destino' => $p->nombre_planta ?? '',
                'direccion_destino' => $p->direccion_texto ?? '',
                'destino_direccion' => $p->direccion_texto ?? '',
                'direccion_origen' => $origen,
                'origen_direccion' => $origen,
                'origen' => $origen,
                'cantidad' => $cantidad,
            ];
        })->values()->all();

        return [
            'data' => $data,
            '_meta' => [
                'fuente' => 'fusion_local',
                'mensaje' => 'Datos del sistema: información registrada en la base local.',
            ],
            'local_dashboard' => self::dashboardCounts(),
        ];
    }

    public static function transportistasPayload(): array
    {
        if (! Schema::hasTable('usuario')) {
            return ['data' => [], '_meta' => ['fuente' => 'fusion_local', 'vacío' => true]];
        }

        $users = Usuario::query()
            ->where('role', 'transportista')
            ->where('activo', true)
            ->orderBy('usuarioid')
            ->get();

        $hasInfoCol = Schema::hasColumn('usuario', 'informacionadicional');

        $data = $users->map(function (Usuario $u) use ($hasInfoCol) {
            $demo = [];
            if ($hasInfoCol && $u->informacionadicional) {
                $decoded = json_decode($u->informacionadicional, true);
                $demo = is_array($decoded) ? ($decoded['demo_xtra2'] ?? []) : [];
            }
            $estadoNombre = $demo['estado_logistico'] ?? 'Disponible';

            return [
                'persona' => [
                    'nombre' => $u->nombre,
                    'apellido' => $u->apellido,
                ],
                'usuario' => ['correo' => $u->email],
                'correo' => $u->email,
                'nombre' => trim($u->nombre.' '.$u->apellido),
                'estado' => ['nombre' => $estadoNombre],
                'estadotransportista' => ['nombre' => $estadoNombre],
            ];
        })->values()->all();

        return [
            'data' => $data,
            '_meta' => [
                'fuente' => 'fusion_local',
                'mensaje' => 'Datos del sistema: transportistas registrados.',
            ],
        ];
    }

    public static function vehiculosPayload(): array
    {
        $items = [];
        $seen = [];

        if (Schema::hasTable('ruta_multi_entrega')) {
            foreach (RutaMultiEntrega::query()->orderBy('rutamultientregaid')->get() as $r) {
                $sum = $r->resumen;
                if (! is_array($sum)) {
                    continue;
                }
                $placa = $sum['vehiculo_placa'] ?? null;
                if (! $placa || isset($seen[$placa])) {
                    continue;
                }
                $seen[$placa] = true;
                $nombre = $sum['vehiculo_nombre'] ?? 'Vehículo';
                $estado = $sum['vehiculo_estado'] ?? 'Activo';
                $capKg = $sum['capacidad_kg'] ?? null;
                $items[] = [
                    'placa' => $placa,
                    'tipo_vehiculo' => ['nombre' => $nombre],
                    'tipoVehiculo' => ['nombre' => $nombre],
                    'estado_vehiculo' => ['nombre' => $estado],
                    'estadoVehiculo' => ['nombre' => $estado],
                    'capacidad_carga' => $capKg !== null ? $capKg.' kg' : null,
                    'capacidad' => $capKg,
                ];
            }
        }

        if (! count($items) && Schema::hasTable('envio_asignacion_multiple')) {
            foreach (EnvioAsignacionMultiple::query()->whereNotNull('vehiculo_ref')->orderBy('envioasignacionmultipleid')->get() as $a) {
                $key = trim((string) $a->vehiculo_ref);
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $items[] = [
                    'placa' => $key,
                    'tipo_vehiculo' => ['nombre' => 'Referencia envío'],
                    'tipoVehiculo' => ['nombre' => 'Referencia envío'],
                    'estado_vehiculo' => ['nombre' => '—'],
                    'estadoVehiculo' => ['nombre' => '—'],
                    'capacidad_carga' => null,
                    'capacidad' => null,
                ];
            }
        }

        return [
            'data' => array_values($items),
            '_meta' => [
                'fuente' => 'fusion_local',
                'mensaje' => count($items)
                    ? 'Datos del sistema: vehículos registrados en la operación logística.'
                    : 'No hay datos disponibles de vehículos registrados.',
            ],
        ];
    }

    private static function dashboardCounts(): array
    {
        $pendientes = 0;
        $enRuta = 0;
        $asignados = 0;
        $entregados = 0;

        if (Schema::hasTable('envio_asignacion_multiple')) {
            $pendientes = (int) EnvioAsignacionMultiple::query()->where('estado', 'pendiente')->count();
            $enRuta = (int) EnvioAsignacionMultiple::query()->where('estado', 'en_ruta')->count();
            $asignados = (int) EnvioAsignacionMultiple::query()->where('estado', 'asignado')->count();
            $entregados = (int) EnvioAsignacionMultiple::query()->where('estado', 'entregado')->count();
        }

        $transportistas = Schema::hasTable('usuario')
            ? (int) Usuario::query()->where('role', 'transportista')->where('activo', true)->count()
            : 0;

        $vehiculosActivos = 0;
        if (Schema::hasTable('ruta_multi_entrega')) {
            foreach (RutaMultiEntrega::query()->get() as $r) {
                $sum = $r->resumen;
                if (is_array($sum) && ($sum['vehiculo_estado'] ?? '') === 'Activo') {
                    $vehiculosActivos++;
                }
            }
        }

        $rutasActivas = Schema::hasTable('ruta_multi_entrega')
            ? (int) RutaMultiEntrega::query()->whereIn('estado', ['planificada', 'en_ruta'])->count()
            : 0;

        $incidentesAbiertos = Schema::hasTable('incidente_envio')
            ? (int) IncidenteEnvio::query()->where('estado', 'abierto')->count()
            : 0;

        return [
            'envios_pendientes' => $pendientes,
            'envios_asignados' => $asignados,
            'envios_en_transito' => $enRuta,
            'envios_entregados' => $entregados,
            'transportistas' => $transportistas,
            'vehiculos_activos' => $vehiculosActivos,
            'rutas_activas' => $rutasActivas,
            'incidentes_abiertos' => $incidentesAbiertos,
        ];
    }

    private static function emptyEnvios(string $mensaje): array
    {
        return [
            'data' => [],
            '_meta' => ['fuente' => 'fusion_local', 'mensaje' => $mensaje],
            'local_dashboard' => self::dashboardCounts(),
        ];
    }
}
