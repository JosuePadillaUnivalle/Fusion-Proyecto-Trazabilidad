<?php

namespace App\Support;

use App\Models\DetallePedido;
use App\Models\Produccion;
use App\Services\CosechaPresentacionService;
use Illuminate\Support\Collection;

/**
 * Historial de envíos agrícola → planta vinculados a una cosecha (producción).
 */
final class CosechaEnvioHistorialService
{
    /** @var list<string> */
    private const ESTADOS_ENVIO_REGISTRABLE = [
        'asignado', 'asignada', 'pendiente', 'creada',
        'en_transporte_planta', 'en_ruta', 'en_transito',
        'recibido_planta', 'entregado', 'entregada',
    ];

    public function __construct(
        private readonly CosechaPresentacionService $presentacion,
    ) {}

    /**
     * @return array{
     *     filas: Collection<int, array<string, mixed>>,
     *     total_kg: float,
     *     total_envios: int
     * }
     */
    public function historialParaProduccion(Produccion $produccion): array
    {
        $produccion->loadMissing([
            'lote.cultivo',
            'almacenamientos.almacen',
            'unidadMedida',
        ]);

        $almacenamientoIds = $produccion->almacenamientos
            ->pluck('produccionalmacenamientoid')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $refsCosecha = array_map(fn (int $id) => 'cosecha:'.$id, $almacenamientoIds);
        $cultivoNombre = trim((string) ($produccion->lote?->cultivo?->nombre ?? ''));
        $fechaCosecha = $produccion->fechacosecha;

        $detalles = DetallePedido::query()
            ->with([
                'pedido.envioAsignacion.transportista',
                'cosechaAlmacen.catalogoTamanoConteo.tipoEmpaque',
            ])
            ->whereHas('pedido.envioAsignacion', function ($q) {
                $q->whereIn('estado', self::ESTADOS_ENVIO_REGISTRABLE);
            })
            ->where(function ($q) use ($almacenamientoIds, $refsCosecha, $cultivoNombre, $fechaCosecha) {
                if ($almacenamientoIds !== []) {
                    $q->where(function ($sub) use ($almacenamientoIds, $refsCosecha) {
                        $sub->whereIn('produccionalmacenamientoid', $almacenamientoIds)
                            ->orWhereIn('producto_ref', $refsCosecha);
                    });
                }

                if ($cultivoNombre !== '') {
                    $method = $almacenamientoIds !== [] ? 'orWhere' : 'where';
                    $q->{$method}(function ($sub) use ($cultivoNombre, $fechaCosecha) {
                        $sub->where('cultivo_personalizado', 'like', '%'.$cultivoNombre.'%');
                        if ($fechaCosecha) {
                            $sub->whereHas('pedido', fn ($p) => $p->whereDate('fechapedido', '>=', $fechaCosecha));
                        }
                    });
                }
            })
            ->get()
            ->unique('detallepedidoid');

        $filas = $detalles
            ->map(fn (DetallePedido $detalle) => $this->mapearFila($detalle, $almacenamientoIds))
            ->filter()
            ->sortByDesc(fn (array $f) => $f['fecha_orden'] ?? 0)
            ->values();

        return [
            'filas' => $filas,
            'total_kg' => round((float) $filas->sum('kg'), 2),
            'total_envios' => $filas->pluck('codigo_envio')->unique()->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function presentacionProduccion(Produccion $produccion): array
    {
        return $this->presentacion->paraProduccion($produccion, $produccion->lote);
    }

    /**
     * @param  list<int>  $almacenamientoIds
     * @return array<string, mixed>|null
     */
    private function mapearFila(DetallePedido $detalle, array $almacenamientoIds): ?array
    {
        $envio = $detalle->pedido?->envioAsignacion;
        if ($envio === null) {
            return null;
        }

        $presentacion = PedidoCatalogo::presentacionDetalle($detalle);
        $vinculoDirecto = ($detalle->produccionalmacenamientoid && in_array((int) $detalle->produccionalmacenamientoid, $almacenamientoIds, true))
            || ($detalle->producto_ref && in_array($detalle->producto_ref, array_map(fn (int $id) => 'cosecha:'.$id, $almacenamientoIds), true));

        $fecha = $envio->fecha_recepcion_planta
            ?? $envio->simulacion_inicio_at
            ?? $envio->fecha_asignacion
            ?? $detalle->pedido?->fechapedido;

        $transportista = trim(($envio->transportista?->nombre ?? '').' '.($envio->transportista?->apellido ?? ''));

        return [
            'detalle_id' => (int) $detalle->detallepedidoid,
            'codigo_envio' => $envio->externo_envio_id ?? $detalle->pedido?->numero_solicitud ?? '—',
            'pedido_codigo' => $detalle->pedido?->numero_solicitud,
            'fecha' => $fecha,
            'fecha_orden' => $fecha?->timestamp ?? 0,
            'fecha_fmt' => $fecha?->format('d/m/Y H:i') ?? '—',
            'kg' => (float) $detalle->cantidad,
            'kg_fmt' => number_format((float) $detalle->cantidad, 2, ',', '.'),
            'empaque' => $presentacion['empaque'] ?? null,
            'unidades' => $presentacion['unidades'],
            'unidades_fmt' => $presentacion['unidades_fmt'],
            'presentacion_linea' => $presentacion['linea'],
            'transportista' => $transportista !== '' ? $transportista : '—',
            'vehiculo' => $envio->vehiculo_ref ?? '—',
            'estado' => EnvioAsignacionEstadoCatalogo::etiqueta($envio->estado),
            'estado_clase' => $this->claseEstado($envio->estado),
            'vinculo' => $vinculoDirecto ? 'lote' : 'cultivo',
            'url_envio' => route('logistica.asignaciones.show', $envio),
            'url_pedido' => $detalle->pedido
                ? route('pedidos.show', $detalle->pedido)
                : null,
        ];
    }

    private function claseEstado(?string $estado): string
    {
        $estado = strtolower(trim((string) $estado));

        return match (true) {
            in_array($estado, ['recibido_planta', 'entregado', 'entregada'], true) => 'success',
            in_array($estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true) => 'info',
            default => 'secondary',
        };
    }
}
