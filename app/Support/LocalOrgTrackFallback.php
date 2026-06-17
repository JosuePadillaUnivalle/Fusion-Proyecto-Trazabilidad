<?php

namespace App\Support;

use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\RutaMultiEntrega;
use App\Models\TipoEmpaque;
use App\Models\TipoTransporte;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Schema;

/**
 * Respuestas con forma similar a OrgTrack para el proxy cuando la API externa
 * no está disponible o no devuelve registros, usando tablas locales de logística.
 */
final class LocalOrgTrackFallback
{
    /**
     * Lista compatible con la vista mandar-envio: [{ "id": int, "nombre": string }, ...]
     * (semillas del bloque A en tabla tipo_transporte).
     */
    public static function tiposTransporteList(): array
    {
        if (! Schema::hasTable('tipo_transporte')) {
            return [];
        }

        return TipoTransporte::query()
            ->orderBy('nombre')
            ->get()
            ->map(fn (TipoTransporte $row) => [
                'id' => (int) $row->tipotransporteid,
                'nombre' => (string) $row->nombre,
            ])
            ->values()
            ->all();
    }

    /**
     * Catálogo de tipos de empaque para mandar-envio (medidas opcionales rellenadas en vacío si no existen en BD).
     */
    public static function tiposEmpaqueCatalogList(): array
    {
        if (! Schema::hasTable('tipo_empaque')) {
            return [];
        }

        return TipoEmpaque::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function (TipoEmpaque $e) {
                return [
                    'id' => (int) $e->tipoempaqueid,
                    'nombre' => (string) $e->nombre,
                    'largo' => '',
                    'ancho' => '',
                    'alto' => '',
                    'tara' => '',
                    'capacidad' => '',
                    'unidades_por_pallet' => '',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * KPIs y conteos por estado sin cargar todos los registros (rápido para dashboard/seguimiento).
     *
     * @return array{stats: array<string, int>, porEstado: array<string, int>}
     */
    public static function panelEstadisticasEnvios(): array
    {
        $stats = [
            'total' => 0,
            'pendientes' => 0,
            'asignados' => 0,
            'curso' => 0,
            'parcial' => 0,
            'completados' => 0,
        ];
        $porEstado = [];

        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return compact('stats', 'porEstado');
        }

        $stats['total'] = (int) EnvioAsignacionMultiple::query()->count();

        $porEstado = EnvioAsignacionMultiple::query()
            ->selectRaw('LOWER(TRIM(COALESCE(estado, \'sin estado\'))) as est')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('est')
            ->pluck('total', 'est')
            ->map(fn ($c) => (int) $c)
            ->all();

        foreach ($porEstado as $estado => $cantidad) {
            if (in_array($estado, ['pendiente', 'sin estado', 'sin asignar'], true)) {
                $stats['pendientes'] += $cantidad;
            }
            if ($estado === 'asignado') {
                $stats['asignados'] += $cantidad;
            }
            if (in_array($estado, ['en curso', 'en_ruta', 'en ruta'], true)) {
                $stats['curso'] += $cantidad;
            }
            if (in_array($estado, ['parcialmente entregado', 'parcial'], true)) {
                $stats['parcial'] += $cantidad;
            }
            if (in_array($estado, ['entregado', 'finalizado', 'completado'], true)) {
                $stats['completados'] += $cantidad;
            }
        }

        arsort($porEstado);

        return compact('stats', 'porEstado');
    }

    /**
     * Lista ligera filtrada por estado (carga bajo demanda en dashboard).
     *
     * @return list<array<string, mixed>>
     */
    public static function enviosPorEstado(string $estado, int $limit = 50): array
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return [];
        }

        $estado = strtolower(trim($estado));
        $limit = max(1, min(100, $limit));

        $rows = EnvioAsignacionMultiple::query()
            ->with(['pedido:pedidoid,numero_solicitud,nombre_planta,direccion_texto', 'almacen:almacenid,nombre,ubicacion'])
            ->whereRaw('LOWER(TRIM(COALESCE(estado, \'sin estado\'))) = ?', [$estado])
            ->orderByDesc('envioasignacionmultipleid')
            ->limit($limit)
            ->get();

        return $rows->map(fn (EnvioAsignacionMultiple $a) => self::mapEnvioRow($a))->values()->all();
    }

    public static function estadosDistintos(): array
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return [];
        }

        return EnvioAsignacionMultiple::query()
            ->selectRaw('DISTINCT LOWER(TRIM(COALESCE(estado, \'sin estado\'))) as est')
            ->orderBy('est')
            ->pluck('est')
            ->all();
    }

    public static function enviosPayload(int $limit = 150, ?string $filtroEstado = null): array
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return self::emptyEnvios('Sin tabla envio_asignacion_multiple.');
        }

        $limit = max(1, min(500, $limit));

        $query = EnvioAsignacionMultiple::query()
            ->with(['pedido:pedidoid,numero_solicitud,nombre_planta,direccion_texto', 'almacen:almacenid,nombre,ubicacion'])
            ->orderByDesc('envioasignacionmultipleid');

        if ($filtroEstado !== null && $filtroEstado !== '') {
            $query->whereRaw('LOWER(TRIM(COALESCE(estado, \'sin estado\'))) = ?', [strtolower(trim($filtroEstado))]);
        }

        $rows = $query->limit($limit)->get();

        if ($rows->isEmpty()) {
            return self::emptyEnvios('No hay asignaciones locales. Ejecute los seeders demo de envíos.');
        }

        $data = $rows->map(function (EnvioAsignacionMultiple $a) {
            return self::mapEnvioRow($a);
        })->values()->all();

        return [
            'data' => $data,
            '_meta' => [
                'fuente' => 'fusion_local',
                'mensaje' => 'Datos del sistema: información registrada en la base local.',
                'limit' => $limit,
            ],
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
        $catalogo = self::catalogoVehiculosPorPlaca();
        $items = [];
        $seen = [];

        if (Schema::hasTable('vehiculo')) {
            foreach (Vehiculo::query()->with(['tipoVehiculo', 'estadoVehiculo'])->orderBy('placa')->get() as $vehiculo) {
                $placa = trim((string) $vehiculo->placa);
                if ($placa === '') {
                    continue;
                }
                $seen[$placa] = true;
                $items[] = self::filaVehiculoDesdeModelo($vehiculo, $catalogo[$placa] ?? null);
            }
        }

        if (Schema::hasTable('ruta_multi_entrega')) {
            foreach (RutaMultiEntrega::query()->orderBy('rutamultientregaid')->get() as $r) {
                $sum = is_array($r->resumen) ? $r->resumen : [];
                $placa = trim((string) ($sum['vehiculo_placa'] ?? ''));
                if ($placa === '' || isset($seen[$placa])) {
                    continue;
                }
                $seen[$placa] = true;
                $meta = array_merge($catalogo[$placa] ?? [], [
                    'vehiculo_nombre' => $sum['vehiculo_nombre'] ?? null,
                    'vehiculo_estado' => $sum['vehiculo_estado'] ?? null,
                    'capacidad_kg' => $sum['capacidad_kg'] ?? null,
                ]);
                $items[] = self::filaVehiculoDesdeMeta($placa, $meta);
            }
        }

        if (! count($items) && Schema::hasTable('envio_asignacion_multiple')) {
            foreach (EnvioAsignacionMultiple::query()->whereNotNull('vehiculo_ref')->orderBy('envioasignacionmultipleid')->get() as $a) {
                $key = trim((string) $a->vehiculo_ref);
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $items[] = self::filaVehiculoDesdeMeta($key, $catalogo[$key] ?? ['vehiculo_nombre' => 'Referencia de envío']);
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

    /**
     * @return array<string, array{vehiculo_nombre: string, capacidad_kg: int|float, vehiculo_estado?: string}>
     */
    private static function catalogoVehiculosPorPlaca(): array
    {
        return [
            'SCZ-1020' => ['vehiculo_nombre' => 'Camión Volvo FH', 'capacidad_kg' => 10000, 'vehiculo_estado' => 'Activo'],
            'SCZ-2040' => ['vehiculo_nombre' => 'Camioneta Toyota Hilux', 'capacidad_kg' => 1200, 'vehiculo_estado' => 'Activo'],
            'SCZ-3090' => ['vehiculo_nombre' => 'Camión Mercedes Atego', 'capacidad_kg' => 7000, 'vehiculo_estado' => 'En mantenimiento'],
            'SCZ-MOD-01' => ['vehiculo_nombre' => 'Camión Volvo FH', 'capacidad_kg' => 10000, 'vehiculo_estado' => 'Activo'],
            'SCZ-MOD-02' => ['vehiculo_nombre' => 'Camioneta Toyota Hilux', 'capacidad_kg' => 1200, 'vehiculo_estado' => 'Activo'],
            'SCZ-MOD-03' => ['vehiculo_nombre' => 'Camión Mercedes Atego', 'capacidad_kg' => 7000, 'vehiculo_estado' => 'Activo'],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>
     */
    private static function filaVehiculoDesdeModelo(Vehiculo $vehiculo, ?array $meta = null): array
    {
        $meta = $meta ?? [];
        $tipo = $meta['vehiculo_nombre']
            ?? self::nombreTipoDesdeMarcaModelo($vehiculo->marca, $vehiculo->modelo, $vehiculo->tipoVehiculo?->nombre);

        $capKg = $meta['capacidad_kg'] ?? $vehiculo->tipoVehiculo?->capacidad_kg;
        $estado = $meta['vehiculo_estado']
            ?? $vehiculo->estadoVehiculo?->nombre
            ?? ($vehiculo->activo ? 'Activo' : 'En mantenimiento');

        return self::filaVehiculo($vehiculo->placa, $tipo, $estado, $capKg);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private static function filaVehiculoDesdeMeta(string $placa, array $meta): array
    {
        $tipo = $meta['vehiculo_nombre'] ?? 'Vehículo';
        $estado = $meta['vehiculo_estado'] ?? 'Activo';
        $capKg = $meta['capacidad_kg'] ?? null;

        return self::filaVehiculo($placa, $tipo, $estado, $capKg);
    }

    private static function nombreTipoDesdeMarcaModelo(?string $marca, ?string $modelo, ?string $tipoCatalogo = null): string
    {
        $marca = trim((string) $marca);
        $modelo = trim((string) $modelo);
        if ($marca !== '' && $modelo !== '') {
            $prefijo = preg_match('/hilux|ranger|amarok/i', $modelo) ? 'Camioneta' : 'Camión';

            return trim($prefijo.' '.$marca.' '.$modelo);
        }

        return $tipoCatalogo ?: 'Vehículo';
    }

    /**
     * @return array<string, mixed>
     */
    private static function filaVehiculo(string $placa, string $tipo, string $estado, mixed $capKg): array
    {
        $capFormateada = self::formatearCapacidadKg($capKg);

        return [
            'placa' => $placa,
            'tipo_vehiculo' => ['nombre' => $tipo],
            'tipoVehiculo' => ['nombre' => $tipo],
            'estado_vehiculo' => ['nombre' => $estado],
            'estadoVehiculo' => ['nombre' => $estado],
            'capacidad_carga' => $capFormateada,
            'capacidad' => $capKg,
        ];
    }

    private static function formatearCapacidadKg(mixed $capKg): ?string
    {
        if ($capKg === null || $capKg === '' || $capKg === '—') {
            return null;
        }

        $num = is_numeric($capKg) ? (float) $capKg : null;
        if ($num === null) {
            return (string) $capKg;
        }

        return number_format($num, 0, ',', '.').' kg';
    }

    /**
     * Contadores para KPIs del módulo envíos (dashboard y seguimiento).
     *
     * @param  list<array<string, mixed>>  $envios
     * @return array{total: int, pendientes: int, asignados: int, curso: int, parcial: int, completados: int}
     */
    public static function resumenFiltrosEnvios(array $envios): array
    {
        $counts = [
            'total' => count($envios),
            'pendientes' => 0,
            'asignados' => 0,
            'curso' => 0,
            'parcial' => 0,
            'completados' => 0,
        ];

        foreach ($envios as $envio) {
            $estado = strtolower(trim((string) ($envio['estado'] ?? $envio['estado_actual'] ?? 'sin estado')));
            if (in_array($estado, ['pendiente', 'sin estado', 'sin asignar'], true)) {
                $counts['pendientes']++;
            }
            if ($estado === 'asignado') {
                $counts['asignados']++;
            }
            if (in_array($estado, ['en curso', 'en_ruta', 'en ruta'], true)) {
                $counts['curso']++;
            }
            if (in_array($estado, ['parcialmente entregado', 'parcial'], true)) {
                $counts['parcial']++;
            }
            if (in_array($estado, ['entregado', 'finalizado', 'completado'], true)) {
                $counts['completados']++;
            }
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    public static function operationalMetrics(): array
    {
        return self::dashboardCounts();
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapEnvioRow(EnvioAsignacionMultiple $a): array
    {
        $p = $a->pedido;
        $alm = $a->almacen;
        $origen = $alm
            ? trim(($alm->nombre ?? '').' · '.($alm->ubicacion ?? ''))
            : 'Origen almacén';
        $destino = $p?->direccion_texto ?: ($p?->nombre_planta ?? 'Sin destino');
        $fecha = $a->fecha_asignacion ?? $a->created_at;
        $detalles = is_array($a->detalles_productos) ? $a->detalles_productos : [];
        $remitente = $detalles['remitente'] ?? $alm?->nombre ?? 'Operación logística Fusion';

        return [
            'id' => (int) $a->envioasignacionmultipleid,
            'externo_envio_id' => $a->externo_envio_id,
            'numero_solicitud' => $p?->numero_solicitud,
            'estado' => $a->estado,
            'estado_actual' => $a->estado,
            'estado_etiqueta' => EnvioAsignacionEstadoCatalogo::etiqueta($a->estado),
            'nombre_estado' => EnvioAsignacionEstadoCatalogo::etiqueta($a->estado),
            'fecha_creacion' => $fecha?->toIso8601String(),
            'nombre_remitente' => $remitente,
            'destino' => $destino,
            'direccion_destino' => $destino,
            'destino_direccion' => $destino,
            'direccion_origen' => $origen,
            'origen_direccion' => $origen,
            'origen' => $origen,
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

        $vehiculosActivos = Schema::hasTable('ruta_multi_entrega')
            ? (int) RutaMultiEntrega::query()->where('estado', 'en_ruta')->count()
            : 0;

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

    /**
     * Detalle compatible con envios/detalle.blade.php (particiones, mapa, transportista).
     *
     * @param  int|string  $id  envioasignacionmultipleid o externo_envio_id
     */
    public static function envioDetallePayload(int|string $id): array
    {
        if (! Schema::hasTable('envio_asignacion_multiple')) {
            return ['particiones' => [], '_meta' => ['fuente' => 'fusion_local', 'error' => 'Sin tabla de envíos.']];
        }

        $query = EnvioAsignacionMultiple::query()
            ->with(['pedido.detalles', 'transportista', 'almacen', 'tipoTransporte', 'recogidaEntrega']);

        $asig = is_numeric($id)
            ? $query->where('envioasignacionmultipleid', (int) $id)->first()
            : $query->where('externo_envio_id', (string) $id)->first();

        if (! $asig) {
            return ['particiones' => [], '_meta' => ['fuente' => 'fusion_local', 'error' => 'Envío no encontrado.']];
        }

        $pedido = $asig->pedido;
        $alm = $asig->almacen;
        $detalles = is_array($asig->detalles_productos) ? $asig->detalles_productos : [];

        $origenNombre = $detalles['origen'] ?? ($alm
            ? trim(($alm->nombre ?? '').' · '.($alm->ubicacion ?? ''))
            : 'Origen logístico');
        $destinoNombre = $pedido?->nombre_planta ?? ($detalles['destino'] ?? 'Destino');
        $destinoDir = $pedido?->direccion_texto ?? ($detalles['destino'] ?? '');

        $destLat = $pedido?->latitud;
        $destLng = $pedido?->longitud;
        $origLat = $destLat ? (float) $destLat + 0.04 : -17.7833;
        $origLng = $destLng ? (float) $destLng + 0.03 : -63.1821;

        $transportista = $asig->transportista;
        $placa = $asig->vehiculo_ref;
        if (is_string($placa) && str_contains($placa, '/')) {
            $parts = explode('/', $placa);
            $placa = trim(end($parts));
        }

        $fechaAsig = $asig->fecha_asignacion ?? $asig->created_at;
        $recogida = $asig->recogidaEntrega;

        $particion = [
            'estado' => self::etiquetaEstadoEnvio($asig->estado),
            'transportista' => [
                'nombre' => $transportista?->nombre ?? '—',
                'apellido' => $transportista?->apellido ?? '',
                'telefono' => $transportista?->telefono ?? '—',
                'ci' => '—',
            ],
            'vehiculo' => ['placa' => $placa ?: '—'],
            'tipoTransporte' => [
                'nombre' => $asig->tipoTransporte?->nombre ?? 'Transporte terrestre',
                'descripcion' => $asig->externo_envio_id ?? 'Asignación Fusion',
            ],
            'recogidaEntrega' => [
                'fecha_recogida' => $recogida?->fecha_recogida?->format('d/m/Y')
                    ?? $fechaAsig?->format('d/m/Y')
                    ?? now()->format('d/m/Y'),
                'hora_recogida' => $recogida?->hora_recogida ?? '08:00',
                'hora_entrega' => $recogida?->hora_entrega ?? '14:00',
                'instrucciones_recogida' => $recogida?->instrucciones_recogida
                    ?? 'Recoger en punto de origen confirmado.',
                'instrucciones_entrega' => $recogida?->instrucciones_entrega
                    ?? 'Entregar en destino con acta de recepción.',
            ],
            'cargas' => self::cargasDesdeAsignacion($asig, $detalles),
            'codigo_acceso' => strtoupper(substr(md5((string) $asig->envioasignacionmultipleid), 0, 8)),
            'id_transportista' => $asig->transportista_usuarioid,
            'id_vehiculo' => $placa ? 1 : null,
        ];

        return [
            'id' => $asig->envioasignacionmultipleid,
            'externo_envio_id' => $asig->externo_envio_id,
            'numero_solicitud' => $pedido?->numero_solicitud,
            'estado' => $asig->estado,
            'nombre_origen' => $origenNombre,
            'nombre_destino' => $destinoNombre,
            'direccion_destino' => $destinoDir,
            'coordenadas_origen' => ['lat' => $origLat, 'lng' => $origLng],
            'coordenadas_destino' => ['lat' => $destLat, 'lng' => $destLng],
            'particiones' => [$particion],
            '_meta' => [
                'fuente' => 'fusion_local',
                'mensaje' => 'Detalle generado desde la base local de Fusion-Proyectos.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $detalles
     * @return list<array<string, mixed>>
     */
    private static function cargasDesdeAsignacion(EnvioAsignacionMultiple $asig, array $detalles): array
    {
        $cargas = [];

        if (isset($detalles[0]) && is_array($detalles[0])) {
            foreach ($detalles as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $cargas[] = [
                    'tipo' => (string) ($item['producto'] ?? 'Producto'),
                    'variedad' => (string) ($item['codigo_producto'] ?? ''),
                    'cantidad' => (float) ($item['cantidad'] ?? 0),
                    'peso' => (float) ($item['cantidad'] ?? 0),
                ];
            }
        }

        if ($cargas === [] && $asig->pedido) {
            foreach ($asig->pedido->detalles ?? [] as $det) {
                $cargas[] = [
                    'tipo' => (string) ($det->cultivo_personalizado ?? 'Producto'),
                    'variedad' => '',
                    'cantidad' => (float) ($det->cantidad ?? 0),
                    'peso' => (float) ($det->cantidad ?? 0),
                ];
            }
        }

        if ($cargas === []) {
            $cargas[] = [
                'tipo' => 'Carga general',
                'variedad' => '',
                'cantidad' => 0,
                'peso' => 0,
            ];
        }

        return $cargas;
    }

    private static function etiquetaEstadoEnvio(?string $estado): string
    {
        return match (strtolower((string) $estado)) {
            'en_ruta', 'en ruta' => 'En curso',
            'entregado' => 'Entregado',
            'asignado' => 'Asignado',
            'pendiente' => 'Pendiente',
            default => ucfirst((string) ($estado ?: 'Pendiente')),
        };
    }

    private static function emptyEnvios(string $mensaje): array
    {
        return [
            'data' => [],
            '_meta' => ['fuente' => 'fusion_local', 'mensaje' => $mensaje],
        ];
    }
}
