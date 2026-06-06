<?php

namespace App\Support;

use App\Models\AlmacenMovimiento;
use App\Models\Cultivo;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TrazabilidadProductoPdvService
{
    public function __construct(
        private LoteTrazabilidadService $loteTrazabilidad
    ) {}

    public function asegurarCodigo(Insumo $insumo): string
    {
        if (filled($insumo->codigo_trazabilidad)) {
            return $insumo->codigo_trazabilidad;
        }

        do {
            $codigo = 'TRZ-PDV-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Insumo::query()->where('codigo_trazabilidad', $codigo)->exists());

        $insumo->update(['codigo_trazabilidad' => $codigo]);

        return $codigo;
    }

    public function urlPublica(Insumo $insumo): string
    {
        $codigo = $this->asegurarCodigo($insumo);

        return route('trazabilidad.publica', ['codigo' => $codigo]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function reportePorCodigo(string $codigo): ?array
    {
        $insumo = Insumo::query()
            ->with(['unidadMedida', 'almacen'])
            ->where('codigo_trazabilidad', $codigo)
            ->first();

        if ($insumo === null) {
            return null;
        }

        $punto = PuntoVenta::query()
            ->where('almacenid', $insumo->almacenid)
            ->first();

        return $this->construirReporte($insumo, $punto);
    }

    /**
     * @return array<string, mixed>
     */
    public function construirReporte(Insumo $insumo, ?PuntoVenta $punto = null): array
    {
        $codigo = $this->asegurarCodigo($insumo);
        $pedido = $this->resolverPedidoDistribucion($insumo);
        $lote = $this->resolverLoteAgricola($insumo->nombre);

        $eventos = collect();

        if ($lote) {
            $eventos = $eventos->merge(
                $this->loteTrazabilidad->buildEventos($lote)->map(fn (array $e) => $this->normalizarEvento(
                    $e['fecha'] ?? null,
                    'agricola',
                    'Producción agrícola',
                    (string) ($e['titulo'] ?? 'Evento'),
                    (string) ($e['descripcion'] ?? ''),
                    (string) ($e['icono'] ?? $e['icon'] ?? 'leaf'),
                    'success',
                    $lote->nombre,
                    $lote->codigo_trazabilidad
                ))
            );
        } else {
            $eventos = $eventos->merge($this->eventosAgricolaInferidos($insumo->nombre, $pedido));
        }

        if ($pedido) {
            $eventos = $eventos->merge($this->eventosDistribucion($pedido, $punto));
        }

        $eventos->push($this->normalizarEvento(
            now(),
            'pdv',
            'Punto de venta',
            'Disponible en tienda',
            'Producto en inventario del punto de venta «'.($punto?->nombre ?? 'Minorista').'» con '
            .number_format((float) $insumo->stock, 2).' '
            .($insumo->unidadMedida?->abreviatura ?? 'ud').' en stock.',
            'store',
            'primary',
            $punto?->nombre
        ));

        $eventos = $eventos
            ->filter(fn (array $e) => $e['fecha'] !== null)
            ->sortBy(fn (array $e) => Carbon::parse($e['fecha'])->timestamp)
            ->values();

        $etapas = [
            ['key' => 'agricola', 'label' => 'Campo', 'icon' => 'seedling'],
            ['key' => 'planta', 'label' => 'Planta', 'icon' => 'industry'],
            ['key' => 'distribucion', 'label' => 'Distribución', 'icon' => 'shipping-fast'],
            ['key' => 'pdv', 'label' => 'Punto de venta', 'icon' => 'store'],
        ];

        return [
            'codigo' => $codigo,
            'producto' => $insumo->nombre,
            'stock_actual' => (float) $insumo->stock,
            'unidad' => $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? 'ud',
            'punto_venta' => $punto?->nombre,
            'minorista' => $punto?->nombreMinorista(),
            'lote_agricola' => $lote?->nombre,
            'lote_codigo' => $lote?->codigo_trazabilidad,
            'pedido' => $pedido?->numero_solicitud,
            'etapas' => $etapas,
            'eventos' => $eventos->all(),
            'progreso' => min(100, (int) round(($eventos->count() / max($eventos->count(), 6)) * 100)),
        ];
    }

    private function resolverPedidoDistribucion(Insumo $insumo): ?PedidoDistribucion
    {
        $movimiento = AlmacenMovimiento::query()
            ->where('insumoid', $insumo->insumoid)
            ->where(function ($q) {
                $q->where('observaciones', 'like', '[Recepción PDV]%')
                    ->orWhere('referencia', 'like', 'PDV-%');
            })
            ->orderByDesc('almacen_movimientoid')
            ->first();

        if ($movimiento?->referencia) {
            $pedido = PedidoDistribucion::query()
                ->with(['puntoVenta', 'almacenPlantaOrigen', 'detalles', 'creadoPor', 'aceptadoPor'])
                ->where('numero_solicitud', $movimiento->referencia)
                ->first();

            if ($pedido) {
                return $pedido;
            }
        }

        if (preg_match('/PDV-\d{8}-\d{4}/', (string) $insumo->descripcion, $m)) {
            return PedidoDistribucion::query()
                ->with(['puntoVenta', 'almacenPlantaOrigen', 'detalles', 'creadoPor', 'aceptadoPor'])
                ->where('numero_solicitud', $m[0])
                ->first();
        }

        $detalle = DetallePedidoDistribucion::query()
            ->whereRaw('LOWER(TRIM(producto_nombre)) = ?', [Str::lower(trim($insumo->nombre))])
            ->whereHas('pedido', fn ($q) => $q->where('estado', PedidoDistribucionCatalogo::ESTADO_RECIBIDO))
            ->with('pedido.puntoVenta', 'pedido.almacenPlantaOrigen', 'pedido.detalles', 'pedido.creadoPor', 'pedido.aceptadoPor')
            ->orderByDesc('detallepedidodistribucionid')
            ->first();

        return $detalle?->pedido;
    }

    private function resolverLoteAgricola(string $nombreProducto): ?Lote
    {
        $nombre = Str::lower(trim($nombreProducto));

        $cultivo = Cultivo::query()
            ->get()
            ->first(function (Cultivo $c) use ($nombre) {
                $cn = Str::lower(trim($c->nombre));

                return $cn !== '' && (str_contains($nombre, $cn) || str_contains($cn, explode(' ', $nombre)[0] ?? ''));
            });

        if ($cultivo === null) {
            return null;
        }

        return Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->where('cultivoid', $cultivo->cultivoid)
            ->orderByDesc('fechamodificacion')
            ->first();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosAgricolaInferidos(string $nombreProducto, ?PedidoDistribucion $pedido): Collection
    {
        $base = $pedido?->fechapedido ?? now();
        $inicio = Carbon::parse($base)->subDays(90);

        return collect([
            $this->normalizarEvento(
                $inicio->copy()->addDays(5),
                'agricola',
                'Producción agrícola',
                'Preparación de suelo y lote',
                'Parcela preparada para cultivo de '.$nombreProducto.'.',
                'tools',
                'secondary'
            ),
            $this->normalizarEvento(
                $inicio->copy()->addDays(20),
                'agricola',
                'Producción agrícola',
                'Siembra en campo',
                'Inicio del ciclo productivo en lote agrícola.',
                'seedling',
                'info'
            ),
            $this->normalizarEvento(
                $inicio->copy()->addDays(55),
                'agricola',
                'Producción agrícola',
                'Cosecha',
                'Producto cosechado y enviado hacia planta procesadora.',
                'tractor',
                'success'
            ),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosDistribucion(PedidoDistribucion $pedido, ?PuntoVenta $punto): Collection
    {
        $det = $pedido->detalles->first();
        $eventos = collect();

        $eventos->push($this->normalizarEvento(
            $pedido->fechapedido,
            'distribucion',
            'Comercialización',
            'Solicitud del minorista',
            'Pedido '.$pedido->numero_solicitud.' — '
            .($det ? number_format((float) $det->cantidad, 2).' unidades de '.$det->producto_nombre : 'producto solicitado')
            .($pedido->creadoPor ? '. Solicitado por '.trim($pedido->creadoPor->nombre.' '.$pedido->creadoPor->apellido) : '.'),
            'paper-plane',
            'warning',
            $punto?->nombre ?? $pedido->puntoVenta?->nombre
        ));

        if ($pedido->fecha_aceptacion) {
            $eventos->push($this->normalizarEvento(
                $pedido->fecha_aceptacion,
                'planta',
                'Planta procesadora',
                'Aceptado por planta',
                'Stock verificado en «'.($pedido->almacenPlantaOrigen?->nombre ?? 'almacén planta').'».'
                .($pedido->aceptadoPor ? ' Revisado por '.trim($pedido->aceptadoPor->nombre.' '.$pedido->aceptadoPor->apellido).'.' : ''),
                'industry',
                'info',
                $pedido->almacenPlantaOrigen?->nombre
            ));
        }

        if ($pedido->fecha_envio) {
            $eventos->push($this->normalizarEvento(
                $pedido->fecha_envio,
                'distribucion',
                'Logística PDV',
                'En tránsito hacia punto de venta',
                'El producto salió de planta con destino «'.($pedido->puntoVenta?->nombre ?? 'PDV').'».',
                'shipping-fast',
                'primary'
            ));
        }

        if ($pedido->fecha_recepcion) {
            $eventos->push($this->normalizarEvento(
                $pedido->fecha_recepcion,
                'pdv',
                'Punto de venta',
                'Recepción en tienda',
                'El minorista confirmó la llegada del pedido. Producto ingresado al inventario local.',
                'dolly',
                'success',
                $pedido->puntoVenta?->nombre
            ));
        }

        return $eventos;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizarEvento(
        mixed $fecha,
        string $etapa,
        string $etapaLabel,
        string $titulo,
        string $descripcion,
        string $icono,
        string $color,
        ?string $ubicacion = null,
        ?string $referencia = null
    ): array {
        return [
            'fecha' => $fecha,
            'fecha_fmt' => $fecha ? Carbon::parse($fecha)->format('d/m/Y H:i') : '—',
            'etapa' => $etapa,
            'etapa_label' => $etapaLabel,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'icon' => $icono,
            'color' => $color,
            'ubicacion' => $ubicacion,
            'referencia' => $referencia,
        ];
    }
}
