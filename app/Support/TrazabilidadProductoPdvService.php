<?php

namespace App\Support;

use App\Models\AlmacenMovimiento;
use App\Models\Cultivo;
use App\Models\DetallePedido;
use App\Models\DetallePedidoDistribucion;
use App\Models\DetalleTrasladoPlantaMayorista;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteProduccionPedido;
use App\Models\PedidoDistribucion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\PlantillaTransformacion;
use App\Models\PuntoVenta;
use App\Models\RegistroProcesoMaquinaPlanta;
use App\Models\RutaDistribucion;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\RutaDistribucionCatalogo;
use App\Services\DistribucionRutaService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TrazabilidadProductoPdvService
{
    public function __construct(
        private LoteTrazabilidadService $loteTrazabilidad,
        private DistribucionRutaService $rutaService,
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
        $path = route('trazabilidad.publica', ['codigo' => $codigo], false);

        return PublicUrlHelper::absoluteForQr($path);
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
        $lote = $this->resolverLoteAgricola(
            $insumo->nombre,
            (string) ($insumo->descripcion ?? ''),
            $insumo,
            $pedido
        );
        $this->vincularLoteAgricolaEnInsumoPdv($insumo, $lote);

        $eventos = collect();

        if ($lote) {
            $eventos = $eventos->merge(
                $this->loteTrazabilidad->buildEventos($lote)->values()->map(function (array $e, int $idx) use ($lote) {
                    $evento = $this->normalizarEvento(
                        $e['fecha'] ?? null,
                        'agricola',
                        (string) ($e['fase_label'] ?? 'Producción agrícola'),
                        (string) ($e['titulo'] ?? 'Evento'),
                        (string) ($e['descripcion'] ?? ''),
                        (string) ($e['icono'] ?? $e['icon'] ?? 'leaf'),
                        'success',
                        $lote->nombre,
                        $lote->codigo_trazabilidad,
                        $e['evidencia_url'] ?? null,
                        (string) ($e['tipo'] ?? ''),
                        $e['evidencia_tipo'] ?? null,
                        $e['evidencia_foto_url'] ?? null,
                    );
                    $evento['secuencia_agricola'] = $idx;

                    return $evento;
                })
            );
        } else {
            $eventos = $eventos->merge($this->eventosAgricolaInferidos($insumo->nombre, $pedido));
        }

        $eventos = $eventos->merge($this->eventosEnvioAgricolaPlanta($lote, $insumo));

        if ($pedido) {
            $eventos = $eventos->merge($this->eventosPlanta($insumo, $pedido, $lote, $eventos));
            $eventos = $eventos->merge($this->eventosTrasladoPlantaMayorista($insumo, $pedido));
            $eventos = $eventos->merge($this->eventosMayoristaCentro($insumo, $pedido));
            $eventos = $eventos->merge($this->eventosDistribucion($pedido, $punto, $insumo));
        }

        $fechaDisponible = $pedido?->fecha_recepcion ?? $this->ultimaFechaRecepcionPdv($insumo);
        if ($fechaDisponible !== null) {
            $eventos->push($this->normalizarEvento(
                $fechaDisponible,
                'pdv',
                'Punto de venta',
                'Disponible en tienda',
                'Producto en inventario del punto de venta «'.($punto?->nombre ?? 'Minorista').'».'
                ."\n".'Stock actual: '.number_format((float) $insumo->stock, 2).' '
                .($insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? 'ud').'.',
                'store',
                'primary',
                $punto?->nombre
            ));
        }

        $ordenEtapas = ['agricola' => 1, 'planta' => 2, 'mayorista' => 3, 'distribucion' => 4, 'pdv' => 5];

        $eventos = $eventos
            ->filter(fn (array $e) => $e['fecha'] !== null)
            ->sortBy(fn (array $e) => [
                $ordenEtapas[$e['etapa']] ?? 9,
                isset($e['secuencia_agricola']) ? (int) $e['secuencia_agricola'] : 9999,
                Carbon::parse($e['fecha'])->timestamp,
            ])
            ->values()
            ->map(function (array $e, int $idx) {
                $e['paso'] = $idx + 1;
                $titulo = mb_strtolower(trim($e['titulo'] ?? ''));
                $lineas = $this->descripcionALineas($e['descripcion'] ?? '');
                $e['descripcion_lineas'] = array_values(array_filter(
                    $lineas,
                    fn (string $linea) => mb_strtolower(trim($linea)) !== $titulo
                ));

                return $e;
            });

        $categorias = [
            ['key' => 'agricola', 'label' => 'Producción agrícola', 'icon' => 'seedling'],
            ['key' => 'planta', 'label' => 'Planta procesadora', 'icon' => 'industry'],
            ['key' => 'mayorista', 'label' => 'Centro mayorista', 'icon' => 'warehouse'],
            ['key' => 'distribucion', 'label' => 'Distribución al PDV', 'icon' => 'shipping-fast'],
            ['key' => 'pdv', 'label' => 'Punto de venta', 'icon' => 'store'],
        ];

        $eventosAgrupados = collect($categorias)
            ->map(function (array $cat) use ($eventos) {
                $items = $eventos->where('etapa', $cat['key'])->values()->all();

                return array_merge($cat, [
                    'eventos' => $items,
                    'total' => count($items),
                ]);
            })
            ->filter(fn (array $cat) => $cat['total'] > 0)
            ->values()
            ->all();

        $totalEventos = $eventos->count();

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
            'categorias' => $categorias,
            'eventos_agrupados' => $eventosAgrupados,
            'eventos' => $eventos->all(),
            'total_eventos' => $totalEventos,
            'progreso' => min(100, (int) round(($eventos->count() / max($eventos->count(), 6)) * 100)),
        ];
    }

    /**
     * @return list<string>
     */
    private function descripcionALineas(string $descripcion): array
    {
        $descripcion = trim($descripcion);
        if ($descripcion === '') {
            return [];
        }

        $texto = preg_replace('/^\[[^\]]+\]\s*/', '', $descripcion) ?? $descripcion;
        $lineas = preg_split('/\r\n|\n|\s*·\s*/', $texto) ?: [];

        $resultado = [];
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '' || strcasecmp($linea, 'historial') === 0) {
                continue;
            }
            if (preg_match('/^Realizado por:/i', $linea)) {
                $linea = preg_replace('/^Realizado por:\s*/i', 'Registrado por ', $linea);
            }
            if (preg_match('/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/', $linea) && preg_match('/\d{2}\/\d{2}\/\d{4}/', $linea)) {
                continue;
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $linea)) {
                continue;
            }
            $resultado[] = $linea;
        }

        return array_values(array_unique($resultado));
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
                ->with($this->relacionesPedidoDistribucion())
                ->where('numero_solicitud', $movimiento->referencia)
                ->first();

            if ($pedido) {
                return $pedido;
            }
        }

        if (preg_match('/PDV-\d{8}-\d{4}/', (string) $insumo->descripcion, $m)) {
            return PedidoDistribucion::query()
                ->with($this->relacionesPedidoDistribucion())
                ->where('numero_solicitud', $m[0])
                ->first();
        }

        $detalle = DetallePedidoDistribucion::query()
            ->whereRaw('LOWER(TRIM(producto_nombre)) = ?', [Str::lower(trim($insumo->nombre))])
            ->whereHas('pedido', fn ($q) => $q->where('estado', PedidoDistribucionCatalogo::ESTADO_RECIBIDO))
            ->with(array_map(fn (string $r) => 'pedido.'.$r, $this->relacionesPedidoDistribucion()))
            ->orderByDesc('detallepedidodistribucionid')
            ->first();

        return $detalle?->pedido;
    }

    /** @return list<string> */
    private function relacionesPedidoDistribucion(): array
    {
        return [
            'puntoVenta',
            'almacenPlantaOrigen',
            'almacenMayoristaOrigen',
            'detalles.insumo.unidadMedida',
            'creadoPor',
            'aceptadoPor',
            'transportista',
            'vehiculo',
            'rutaDistribucion.transportista',
            'rutaDistribucion.vehiculo',
            'rutaDistribucion.paradas',
            'rutaDistribucion.almacenOrigen',
        ];
    }

    private function resolverLoteAgricola(
        string $nombreProducto,
        string $descripcion = '',
        ?Insumo $insumoPdv = null,
        ?PedidoDistribucion $pedidoPdv = null,
    ): ?Lote {
        if (preg_match('/(TRAZ-[A-Z0-9\-]+)/', $descripcion, $match)) {
            $lote = $this->loteAgricolaConRelaciones($match[1]);
            if ($lote !== null) {
                return $lote;
            }
        }

        if ($insumoPdv !== null) {
            $desdeCadena = $this->resolverLoteAgricolaDesdeCadenaSuministro($insumoPdv, $pedidoPdv);
            if ($desdeCadena !== null) {
                return $desdeCadena;
            }
        }

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
            ->withCount([
                'actividades as actividades_completadas_count' => fn ($q) => $q->whereNotNull('fechafin'),
                'producciones as producciones_count',
            ])
            ->orderByDesc('producciones_count')
            ->orderByDesc('actividades_completadas_count')
            ->orderByDesc('fechamodificacion')
            ->first();
    }

    private function loteAgricolaConRelaciones(string $codigoTrazabilidad): ?Lote
    {
        return Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->where('codigo_trazabilidad', $codigoTrazabilidad)
            ->first();
    }

    private function resolverLoteAgricolaDesdeCadenaSuministro(
        Insumo $insumoPdv,
        ?PedidoDistribucion $pedidoPdv,
    ): ?Lote {
        $lotesVistos = [];

        foreach ($this->resolverEnviosAgricolaPlantaCandidatos(null, $insumoPdv) as $envio) {
            $detalle = $this->resolverDetallePedidoEnvio($envio, null);
            $lote = $this->loteAgricolaDesdeDetallePedido($detalle);
            if ($lote !== null && ! isset($lotesVistos[(int) $lote->loteid])) {
                $lotesVistos[(int) $lote->loteid] = $lote;

                return $lote->loadMissing(['cultivo', 'estadoTipo', 'usuario']);
            }
        }

        $cultivoNombre = $this->nombreCultivoDesdeProducto($insumoPdv->nombre);
        if ($cultivoNombre !== '') {
            $detalles = DetallePedido::query()
                ->with(['pedido.envioAsignacion'])
                ->whereHas('pedido.envioAsignacion')
                ->where(function ($q) use ($cultivoNombre) {
                    $q->where('cultivo_personalizado', 'like', '%'.$cultivoNombre.'%')
                        ->orWhereHas('insumo', fn ($sub) => $sub->where('nombre', 'like', '%'.$cultivoNombre.'%'));
                })
                ->where(function ($q) {
                    $q->whereNotNull('produccionalmacenamientoid')
                        ->orWhere('producto_ref', 'like', 'cosecha:%');
                })
                ->orderByDesc('detallepedidoid')
                ->limit(30)
                ->get();

            foreach ($detalles as $detalle) {
                $lote = $this->loteAgricolaDesdeDetallePedido($detalle);
                if ($lote !== null && ! isset($lotesVistos[(int) $lote->loteid])) {
                    $lotesVistos[(int) $lote->loteid] = $lote;

                    return $lote->loadMissing(['cultivo', 'estadoTipo', 'usuario']);
                }
            }
        }

        if ($pedidoPdv !== null) {
            $lotePlanta = $this->resolverLoteProduccionPedido($insumoPdv, $pedidoPdv, null);
            if ($lotePlanta !== null) {
                $lote = $this->resolverLoteAgricolaDesdeLoteProduccionPlanta($lotePlanta, $insumoPdv);
                if ($lote !== null) {
                    return $lote->loadMissing(['cultivo', 'estadoTipo', 'usuario']);
                }
            }
        }

        if (preg_match('/(LOTE-\d+-\d+)/i', $insumoPdv->nombre, $codigoPlanta)) {
            $lotePlanta = LoteProduccionPedido::query()
                ->where('codigo_lote', $codigoPlanta[1])
                ->first();
            if ($lotePlanta !== null) {
                $lote = $this->resolverLoteAgricolaDesdeLoteProduccionPlanta($lotePlanta, $insumoPdv);
                if ($lote !== null) {
                    return $lote->loadMissing(['cultivo', 'estadoTipo', 'usuario']);
                }
            }
        }

        $cultivoNombre = $this->nombreCultivoDesdeProducto($insumoPdv->nombre);
        if ($cultivoNombre !== '') {
            $lote = $this->resolverLoteAgricolaConCosechaEnviada($cultivoNombre);
            if ($lote !== null) {
                return $lote->loadMissing(['cultivo', 'estadoTipo', 'usuario']);
            }
        }

        return null;
    }

    private function resolverLoteAgricolaConCosechaEnviada(string $cultivoNombre): ?Lote
    {
        return Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->whereHas('cultivo', fn ($q) => $q->where('nombre', 'like', '%'.$cultivoNombre.'%'))
            ->whereHas('producciones.almacenamientos', function ($q) {
                $q->whereIn('produccionalmacenamientoid', function ($sub) {
                    $sub->select('produccionalmacenamientoid')
                        ->from('detallepedido')
                        ->whereNotNull('produccionalmacenamientoid');
                });
            })
            ->withCount([
                'actividades as actividades_completadas_count' => fn ($q) => $q->whereNotNull('fechafin'),
            ])
            ->orderByDesc('actividades_completadas_count')
            ->orderByDesc('fechamodificacion')
            ->first();
    }

    private function resolverLoteAgricolaDesdeLoteProduccionPlanta(
        LoteProduccionPedido $lotePlanta,
        Insumo $insumoPdv,
    ): ?Lote {
        $lotePlanta->loadMissing(['materiasPrimas.insumo']);

        foreach ($lotePlanta->materiasPrimas as $materia) {
            $nombreMp = Str::lower(trim((string) ($materia->insumo?->nombre ?? '')));
            if ($nombreMp === '') {
                continue;
            }

            $cultivo = Cultivo::query()
                ->get()
                ->first(function (Cultivo $c) use ($nombreMp) {
                    $cn = Str::lower(trim($c->nombre));

                    return $cn !== '' && (str_contains($nombreMp, $cn) || str_contains($cn, $nombreMp));
                });

            if ($cultivo === null) {
                continue;
            }

            $lote = Lote::query()
                ->with(['cultivo', 'estadoTipo', 'usuario'])
                ->where('cultivoid', $cultivo->cultivoid)
                ->whereHas('producciones')
                ->withCount([
                    'actividades as actividades_completadas_count' => fn ($q) => $q->whereNotNull('fechafin'),
                ])
                ->orderByDesc('actividades_completadas_count')
                ->orderByDesc('fechamodificacion')
                ->first();

            if ($lote !== null) {
                return $lote;
            }
        }

        return null;
    }

    private function loteAgricolaDesdeDetallePedido(?DetallePedido $detalle): ?Lote
    {
        if ($detalle === null) {
            return null;
        }

        $almId = (int) ($detalle->produccionalmacenamientoid ?? 0);
        if ($almId <= 0 && filled($detalle->producto_ref) && str_starts_with((string) $detalle->producto_ref, 'cosecha:')) {
            $almId = (int) str_replace('cosecha:', '', (string) $detalle->producto_ref);
        }

        if ($almId <= 0) {
            return null;
        }

        $almacenamiento = ProduccionAlmacenamiento::query()
            ->with('produccion.lote.cultivo')
            ->find($almId);

        return $almacenamiento?->produccion?->lote;
    }

    private function nombreCultivoDesdeProducto(string $nombreProducto): string
    {
        $nombre = Str::lower(trim($nombreProducto));
        if ($nombre === '') {
            return '';
        }

        $cultivo = Cultivo::query()
            ->get()
            ->sortByDesc(fn (Cultivo $c) => mb_strlen(trim($c->nombre ?? '')))
            ->first(function (Cultivo $c) use ($nombre) {
                $cn = Str::lower(trim($c->nombre));

                return $cn !== '' && (str_contains($nombre, $cn) || str_contains($cn, explode(' ', $nombre)[0] ?? ''));
            });

        return trim((string) ($cultivo?->nombre ?? ''));
    }

    private function vincularLoteAgricolaEnInsumoPdv(Insumo $insumo, ?Lote $lote): void
    {
        if ($lote === null || blank($lote->codigo_trazabilidad)) {
            return;
        }

        $codigo = (string) $lote->codigo_trazabilidad;
        $descripcion = (string) ($insumo->descripcion ?? '');
        if (str_contains($descripcion, $codigo)) {
            return;
        }

        $insumo->update([
            'descripcion' => trim($descripcion.' · Lote agrícola: '.$codigo),
        ]);
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
    private function eventosEnvioAgricolaPlanta(?Lote $loteAgricola, Insumo $insumoPdv): Collection
    {
        $envio = $this->resolverEnvioAgricolaPlanta($loteAgricola, $insumoPdv);
        if ($envio === null) {
            return collect();
        }

        $envio->loadMissing(['transportista', 'pedido.detalles', 'almacen', 'recepcionConfirmadaPor']);
        $pedido = $envio->pedido;
        $detalle = $this->resolverDetallePedidoEnvio($envio, $loteAgricola);
        $trayecto = $pedido ? EnvioPedidoService::trayectoPartesPedido($pedido) : null;
        $origen = $trayecto['recogidas'][0] ?? $envio->almacen?->nombre ?? 'Almacén agrícola';
        $destino = $trayecto['destino'] ?? $pedido?->nombre_planta ?? 'Planta procesadora';
        $transportista = $this->nombreUsuario($envio->transportista);
        $vehiculo = trim((string) ($envio->vehiculo_ref ?? ''));
        $codigo = $envio->externo_envio_id ?? $pedido?->numero_solicitud ?? '—';
        $cantidad = $detalle ? number_format((float) $detalle->cantidad, 2).' kg' : null;
        $producto = $detalle?->cultivo_personalizado ?? $insumoPdv->nombre;

        $eventos = collect();

        if ($envio->fecha_asignacion) {
            $lineas = [
                'Código de envío: '.$codigo,
                'Trayecto: '.$origen.' → '.$destino,
            ];
            if ($pedido?->numero_solicitud) {
                $lineas[] = 'Pedido: '.$pedido->numero_solicitud;
            }
            if ($cantidad) {
                $lineas[] = 'Carga: '.$cantidad.' de '.$producto;
            }
            if ($transportista) {
                $lineas[] = 'Transportista: '.$transportista;
            }
            if ($vehiculo !== '') {
                $lineas[] = 'Vehículo: '.$vehiculo;
            }

            $eventos->push($this->normalizarEvento(
                $envio->fecha_asignacion,
                'agricola',
                'Envío hacia planta',
                'Envío agrícola programado',
                implode("\n", $lineas),
                'clipboard-list',
                'info',
                $origen,
                $codigo,
                null,
                'envio_agricola_planta'
            ));
        }

        $salida = $envio->simulacion_inicio_at ?? $envio->fecha_asignacion;
        if ($salida) {
            $lineas = [
                'Salida del almacén agrícola con destino a planta procesadora.',
                'Origen: '.$origen,
                'Destino: '.$destino,
            ];
            if ($transportista) {
                $lineas[] = 'Transportista: '.$transportista;
            }
            if ($vehiculo !== '') {
                $lineas[] = 'Vehículo: '.$vehiculo;
            }
            if ($cantidad) {
                $lineas[] = 'Cantidad en tránsito: '.$cantidad;
            }

            $eventos->push($this->normalizarEvento(
                $salida,
                'agricola',
                'Envío hacia planta',
                'En tránsito hacia planta',
                implode("\n", $lineas),
                'truck',
                'primary',
                $origen.' → '.$destino,
                $codigo,
                null,
                'envio_agricola_planta'
            ));
        }

        $llegada = $envio->llegada_confirmada_at
            ?? ($envio->fecha_recepcion_planta
                ? Carbon::parse($envio->fecha_recepcion_planta)->subMinutes(20)
                : null);

        if ($llegada) {
            $eventos->push($this->normalizarEvento(
                $llegada,
                'planta',
                'Logística de recepción',
                'Llegada a planta procesadora',
                'El transporte llegó al punto de entrega en planta.'
                ."\n".'Destino: '.$destino
                .($transportista ? "\n".'Transportista: '.$transportista : ''),
                'map-marker-alt',
                'info',
                $destino,
                $codigo,
                null,
                'envio_agricola_planta'
            ));
        }

        if ($envio->fecha_recepcion_planta) {
            $receptor = $this->nombreUsuario($envio->recepcionConfirmadaPor);
            $lineas = [
                'Mercadería recibida y registrada en planta.',
                'Estado del envío: '.EnvioAsignacionEstadoCatalogo::etiqueta($envio->estado),
                'Destino: '.$destino,
            ];
            if ($cantidad) {
                $lineas[] = 'Cantidad recibida: '.$cantidad;
            }
            if ($receptor) {
                $lineas[] = 'Recepción confirmada por '.$receptor;
            }

            $eventos->push($this->normalizarEvento(
                $envio->fecha_recepcion_planta,
                'planta',
                'Recepción en planta',
                'Recepción confirmada en planta',
                implode("\n", $lineas),
                'dolly',
                'success',
                $destino,
                $codigo,
                null,
                'envio_agricola_planta'
            ));
        }

        return $eventos;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosTrasladoPlantaMayorista(Insumo $insumoPdv, PedidoDistribucion $pedido): Collection
    {
        $eventos = collect();
        $nombreNorm = Str::lower(trim($insumoPdv->nombre));
        $origenPlanta = $pedido->almacenPlantaOrigen?->nombre ?? 'Planta procesadora';
        $destinoMay = $pedido->almacenMayoristaOrigen?->nombre ?? 'Centro mayorista';

        $rutas = RutaDistribucion::query()
            ->with([
                'transportista',
                'vehiculo',
                'almacenPlantaOrigen',
                'almacenMayoristaDestino',
                'detallesTraslado',
            ])
            ->whereNotNull('almacen_planta_origenid')
            ->where(function ($q) use ($nombreNorm, $pedido) {
                $q->whereHas('detallesTraslado', function ($sub) use ($nombreNorm) {
                    $sub->whereRaw('LOWER(TRIM(producto_nombre)) = ?', [$nombreNorm])
                        ->orWhereRaw('LOWER(producto_nombre) LIKE ?', ['%'.Str::before($nombreNorm, ' ').'%']);
                });
                if ($pedido->almacen_mayorista_origenid) {
                    $q->where('almacen_mayorista_destinoid', $pedido->almacen_mayorista_origenid);
                }
            })
            ->orderByDesc('rutadistribucionid')
            ->get();

        foreach ($rutas as $ruta) {
            $detalle = $ruta->detallesTraslado->first(
                fn (DetalleTrasladoPlantaMayorista $d) => str_contains(Str::lower((string) $d->producto_nombre), Str::before($nombreNorm, ' '))
                    || Str::lower(trim((string) $d->producto_nombre)) === $nombreNorm
            ) ?? $ruta->detallesTraslado->first();

            $transportista = $this->nombreUsuario($ruta->transportista);
            $vehiculo = trim(implode(' ', array_filter([
                $ruta->vehiculo?->placa,
                $ruta->vehiculo?->marca,
                $ruta->vehiculo?->modelo,
            ])));
            $origen = $ruta->almacenPlantaOrigen?->nombre ?? $origenPlanta;
            $destino = $ruta->almacenMayoristaDestino?->nombre ?? $destinoMay;
            $codigo = $ruta->codigo;
            $cantidad = $detalle
                ? number_format((float) $detalle->cantidad, 2).' kg'
                : null;

            if ($ruta->fecha_salida || $ruta->simulacion_inicio_at) {
                $lineas = [
                    'Código de traslado: '.$codigo,
                    'Trayecto: '.$origen.' → '.$destino,
                ];
                if ($cantidad) {
                    $lineas[] = 'Producto: '.($detalle->producto_nombre ?? $insumoPdv->nombre);
                    $lineas[] = 'Cantidad: '.$cantidad;
                }
                if ($transportista) {
                    $lineas[] = 'Transportista: '.$transportista;
                }
                if ($vehiculo !== '') {
                    $lineas[] = 'Vehículo: '.$vehiculo;
                }

                $eventos->push($this->normalizarEvento(
                    $ruta->fecha_salida ?? $ruta->simulacion_inicio_at,
                    'planta',
                    'Envío hacia mayorista',
                    'Salida de planta hacia centro mayorista',
                    implode("\n", $lineas),
                    'truck-loading',
                    'warning',
                    $origen,
                    $codigo,
                    null,
                    'envio_planta_mayorista'
                ));
            }

            if ($ruta->simulacion_inicio_at && $ruta->estado === RutaDistribucionCatalogo::ESTADO_EN_RUTA) {
                $eventos->push($this->normalizarEvento(
                    $ruta->simulacion_inicio_at,
                    'planta',
                    'Envío hacia mayorista',
                    'En tránsito hacia centro mayorista',
                    'Producto terminado en camino al almacén mayorista.'
                    ."\n".'Destino: '.$destino
                    .($transportista ? "\n".'Transportista: '.$transportista : ''),
                    'shipping-fast',
                    'primary',
                    $origen.' → '.$destino,
                    $codigo,
                    null,
                    'envio_planta_mayorista'
                ));
            }

            if ($ruta->llegada_confirmada_at) {
                $eventos->push($this->normalizarEvento(
                    $ruta->llegada_confirmada_at,
                    'mayorista',
                    'Logística de recepción',
                    'Llegada al centro mayorista',
                    'El transporte llegó al almacén mayorista.'
                    ."\n".'Destino: '.$destino
                    .($transportista ? "\n".'Transportista: '.$transportista : ''),
                    'map-marker-alt',
                    'info',
                    $destino,
                    $codigo,
                    null,
                    'envio_planta_mayorista'
                ));
            }

            if ($ruta->estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA && $ruta->fecha_aprobacion_mayorista) {
                $eventos->push($this->normalizarEvento(
                    $ruta->fecha_aprobacion_mayorista,
                    'mayorista',
                    'Recepción en mayorista',
                    'Traslado recibido en almacén mayorista',
                    'Ingreso confirmado en «'.$destino.'».'
                    .($cantidad ? "\n".'Cantidad: '.$cantidad : '')
                    ."\n".'Estado: '.RutaDistribucionCatalogo::etiquetaEstado($ruta->estado),
                    'warehouse',
                    'success',
                    $destino,
                    $codigo,
                    null,
                    'envio_planta_mayorista'
                ));
            }
        }

        if ($eventos->isNotEmpty()) {
            return $eventos;
        }

        $almacenMayId = (int) ($pedido->almacen_mayorista_origenid ?? 0);

        $movimientos = AlmacenMovimiento::query()
            ->with(['usuario', 'almacen'])
            ->where('observaciones', 'like', '[Traslado planta → mayorista%')
            ->when($almacenMayId > 0, fn ($q) => $q->where('almacenid', $almacenMayId))
            ->orderBy('fecha')
            ->get();

        foreach ($movimientos as $mov) {
            $obs = Str::lower((string) $mov->observaciones);
            $ref = Str::lower((string) ($mov->referencia ?? ''));
            if (! str_contains($obs, $nombreNorm)
                && ! str_contains($ref, $nombreNorm)
                && ! str_contains($ref, Str::lower($pedido->numero_solicitud ?? ''))) {
                continue;
            }

            $esIngreso = str_contains($obs, 'ingreso');
            $esSalida = str_contains($obs, 'salida');
            $usuario = $this->nombreUsuario($mov->usuario);

            if ($esSalida) {
                $eventos->push($this->normalizarEvento(
                    $mov->fecha,
                    'planta',
                    'Envío hacia mayorista',
                    'Salida de planta hacia centro mayorista',
                    'Producto terminado despachado desde planta.'
                    ."\n".'Cantidad: '.number_format((float) $mov->cantidad, 2).'.'
                    ."\n".'Destino: '.($mov->destino_motivo ?? $destinoMay)
                    .($mov->referencia ? "\n".'Referencia: '.$mov->referencia : '')
                    .($usuario ? "\n".'Registrado por '.$usuario : ''),
                    'truck-loading',
                    'warning',
                    $origenPlanta,
                    $mov->referencia,
                    null,
                    'envio_planta_mayorista'
                ));
            }

            if ($esIngreso) {
                $eventos->push($this->normalizarEvento(
                    $mov->fecha,
                    'mayorista',
                    'Recepción en mayorista',
                    'Ingreso al almacén mayorista',
                    'El centro mayorista recibió el producto desde planta.'
                    ."\n".'Cantidad: '.number_format((float) $mov->cantidad, 2).'.'
                    ."\n".'Almacén: '.($mov->almacen?->nombre ?? $destinoMay)
                    .($mov->referencia ? "\n".'Referencia: '.$mov->referencia : '')
                    .($usuario ? "\n".'Registrado por '.$usuario : ''),
                    'warehouse',
                    'success',
                    $mov->almacen?->nombre,
                    $mov->referencia,
                    null,
                    'envio_planta_mayorista'
                ));
            }
        }

        if ($eventos->isNotEmpty()) {
            return $eventos;
        }

        return $this->eventosEnvioPlantaMayoristaInferido($insumoPdv, $pedido, $origenPlanta, $destinoMay);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosEnvioPlantaMayoristaInferido(
        Insumo $insumoPdv,
        PedidoDistribucion $pedido,
        string $origenPlanta,
        string $destinoMay
    ): Collection {
        $ingreso = Carbon::parse($pedido->fecha_aceptacion ?? $pedido->fechapedido);
        $salida = $ingreso->copy()->subHours(7);
        $transito = $ingreso->copy()->subHours(3);

        $det = $pedido->detalles->first();
        $cantidad = $det
            ? number_format((float) $det->cantidad, 2).' '.($det->insumo?->unidadMedida?->abreviatura ?? 'kg')
            : number_format((float) $insumoPdv->stock, 2).' '.($insumoPdv->unidadMedida?->abreviatura ?? 'kg');

        $llegada = $ingreso->copy()->subMinutes(30);

        return collect([
            $this->normalizarEvento(
                $salida,
                'planta',
                'Envío hacia mayorista',
                'Salida de planta hacia centro mayorista',
                'Despacho de «'.$insumoPdv->nombre.'» desde planta.'
                ."\n".'Trayecto: '.$origenPlanta.' → '.$destinoMay
                ."\n".'Cantidad: '.$cantidad,
                'truck-loading',
                'warning',
                $origenPlanta,
                $pedido->numero_solicitud,
                null,
                'envio_planta_mayorista'
            ),
            $this->normalizarEvento(
                $transito,
                'planta',
                'Envío hacia mayorista',
                'En tránsito hacia centro mayorista',
                'Producto terminado en camino al almacén mayorista.'
                ."\n".'Destino: '.$destinoMay,
                'shipping-fast',
                'primary',
                $origenPlanta.' → '.$destinoMay,
                $pedido->numero_solicitud,
                null,
                'envio_planta_mayorista'
            ),
            $this->normalizarEvento(
                $llegada,
                'mayorista',
                'Logística de recepción',
                'Llegada al centro mayorista',
                'El transporte llegó al almacén mayorista.'
                ."\n".'Destino: '.$destinoMay,
                'map-marker-alt',
                'info',
                $destinoMay,
                $pedido->numero_solicitud,
                null,
                'envio_planta_mayorista'
            ),
            $this->normalizarEvento(
                $ingreso,
                'mayorista',
                'Recepción en mayorista',
                'Ingreso al almacén mayorista',
                'Producto disponible en «'.$destinoMay.'» para distribución a puntos de venta.'
                ."\n".'Cantidad: '.$cantidad,
                'warehouse',
                'success',
                $destinoMay,
                $pedido->numero_solicitud,
                null,
                'envio_planta_mayorista'
            ),
        ]);
    }

    private function resolverEnvioAgricolaPlanta(?Lote $loteAgricola, Insumo $insumoPdv): ?EnvioAsignacionMultiple
    {
        $candidatos = $this->resolverEnviosAgricolaPlantaCandidatos($loteAgricola, $insumoPdv);
        if ($candidatos->isEmpty()) {
            return null;
        }

        $fechaReferencia = null;
        if ($loteAgricola !== null) {
            $loteAgricola->loadMissing(['producciones']);
            $fechaReferencia = $loteAgricola->producciones->max('fechacosecha');
        }

        if ($fechaReferencia !== null) {
            $ref = Carbon::parse($fechaReferencia)->startOfDay();
            $candidatos = $candidatos->filter(function (EnvioAsignacionMultiple $envio) use ($ref) {
                $fecha = $envio->fecha_recepcion_planta ?? $envio->simulacion_inicio_at ?? $envio->fecha_asignacion;

                return $fecha && Carbon::parse($fecha)->gte($ref);
            });
        }

        if ($candidatos->isEmpty()) {
            return null;
        }

        return $candidatos
            ->sortBy(function (EnvioAsignacionMultiple $envio) use ($fechaReferencia) {
                $prioridad = in_array($envio->estado, ['recibido_planta', 'entregado', 'entregada'], true) ? 0 : 1;
                $fecha = $envio->fecha_recepcion_planta ?? $envio->simulacion_inicio_at ?? $envio->fecha_asignacion;
                $distancia = $fechaReferencia && $fecha
                    ? abs(Carbon::parse($fecha)->diffInSeconds(Carbon::parse($fechaReferencia)))
                    : PHP_INT_MAX;

                return [$prioridad, $distancia];
            })
            ->first();
    }

    /**
     * @return Collection<int, EnvioAsignacionMultiple>
     */
    private function resolverEnviosAgricolaPlantaCandidatos(?Lote $loteAgricola, Insumo $insumoPdv): Collection
    {
        $almIds = collect();
        $cultivoNombre = '';

        if ($loteAgricola !== null) {
            $loteAgricola->loadMissing(['cultivo', 'producciones.almacenamientos']);
            $cultivoNombre = trim((string) ($loteAgricola->cultivo?->nombre ?? ''));
            $almIds = $loteAgricola->producciones
                ->flatMap(fn ($p) => $p->almacenamientos->pluck('produccionalmacenamientoid'))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        if ($cultivoNombre === '') {
            $cultivoNombre = Str::before(trim($insumoPdv->nombre), ' ');
        }

        $refs = $almIds->map(fn (int $id) => 'cosecha:'.$id)->all();

        $detalles = DetallePedido::query()
            ->with(['pedido.envioAsignacion.transportista', 'pedido'])
            ->whereHas('pedido.envioAsignacion')
            ->where(function ($q) use ($almIds, $refs, $cultivoNombre) {
                if ($almIds->isNotEmpty()) {
                    $q->whereIn('produccionalmacenamientoid', $almIds->all())
                        ->orWhereIn('producto_ref', $refs);
                }
                if ($cultivoNombre !== '') {
                    $method = $almIds->isNotEmpty() ? 'orWhere' : 'where';
                    $q->{$method}(function ($sub) use ($cultivoNombre) {
                        $sub->where('cultivo_personalizado', 'like', '%'.$cultivoNombre.'%');
                    });
                }
            })
            ->get();

        return $detalles
            ->map(fn (DetallePedido $d) => $d->pedido?->envioAsignacion)
            ->filter()
            ->unique('envioasignacionmultipleid')
            ->values();
    }

    private function resolverDetallePedidoEnvio(EnvioAsignacionMultiple $envio, ?Lote $loteAgricola): ?DetallePedido
    {
        $detalles = $envio->pedido?->detalles ?? collect();
        if ($detalles->isEmpty()) {
            return null;
        }

        if ($loteAgricola !== null) {
            $loteAgricola->loadMissing(['producciones.almacenamientos']);
            $almIds = $loteAgricola->producciones
                ->flatMap(fn ($p) => $p->almacenamientos->pluck('produccionalmacenamientoid'))
                ->filter()
                ->map(fn ($id) => (int) $id);

            $porAlm = $detalles->first(
                fn (DetallePedido $d) => $almIds->contains((int) $d->produccionalmacenamientoid)
                    || $almIds->contains((int) str_replace('cosecha:', '', (string) $d->producto_ref))
            );
            if ($porAlm !== null) {
                return $porAlm;
            }
        }

        $conCosecha = $detalles->first(function (DetallePedido $d) {
            if ((int) ($d->produccionalmacenamientoid ?? 0) > 0) {
                return true;
            }

            return str_starts_with((string) ($d->producto_ref ?? ''), 'cosecha:');
        });
        if ($conCosecha !== null) {
            return $conCosecha;
        }

        return $detalles->first();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosMayoristaCentro(Insumo $insumoPdv, PedidoDistribucion $pedido): Collection
    {
        $eventos = collect();
        $almacen = $pedido->almacenMayoristaOrigen;
        $nombreAlmacen = $almacen?->nombre ?? 'Centro mayorista';

        if ($pedido->fecha_aceptacion && $almacen) {
            $aceptador = $this->nombreUsuario($pedido->aceptadoPor);
            $eventos->push($this->normalizarEvento(
                $pedido->fecha_aceptacion,
                'mayorista',
                'Centro mayorista',
                'Stock confirmado para despacho',
                'Producto disponible en «'.$nombreAlmacen.'» para atender pedidos de minoristas.'
                .($aceptador ? "\n".'Revisado por '.$aceptador : ''),
                'boxes',
                'info',
                $nombreAlmacen,
                $pedido->numero_solicitud
            ));
        }

        return $eventos;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventosPrevios
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosPlanta(
        Insumo $insumoPdv,
        PedidoDistribucion $pedido,
        ?Lote $loteAgricola,
        Collection $eventosPrevios
    ): Collection {
        $ubicacion = $pedido->almacenPlantaOrigen?->nombre ?? 'Planta procesadora';
        $eventos = collect();

        $loteProd = $this->resolverLoteProduccionPedido($insumoPdv, $pedido, $loteAgricola);
        if ($loteProd !== null) {
            $eventos = $eventos->merge($this->eventosLoteProduccionPlanta($loteProd, $ubicacion, $eventosPrevios, $pedido));
        } else {
            $plantilla = $this->resolverPlantillaTransformacion($insumoPdv->nombre);
            if ($plantilla !== null) {
                $eventos = $eventos->merge(
                    $this->eventosDesdePlantillaTransformacion($plantilla, $insumoPdv, $pedido, $loteAgricola, $eventosPrevios, $ubicacion)
                );
            }
        }

        if ($loteAgricola !== null) {
            $eventos = $eventos->merge(
                $this->eventosRegistrosProcesoLoteAgricola($loteAgricola, $ubicacion, $eventos)
            );
        }

        $insumoPlanta = $this->resolverInsumoPlantaAlmacen($insumoPdv, $pedido);
        if ($insumoPlanta !== null) {
            $eventos = $eventos->merge($this->eventosMovimientosPlanta($insumoPlanta, $pedido->numero_solicitud));
        }

        return $eventos;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosMovimientosPlanta(Insumo $insumoPlanta, ?string $refPedido): Collection
    {
        $movimientos = AlmacenMovimiento::query()
            ->with(['usuario', 'almacen'])
            ->where('insumoid', $insumoPlanta->insumoid)
            ->where(function ($q) {
                $q->where('observaciones', 'like', '[Recepción planta%')
                    ->orWhere('observaciones', 'like', '[Consumo lote%')
                    ->orWhere('observaciones', 'like', '[Ingreso%')
                    ->orWhere('observaciones', 'like', '[Traslado planta%');
            })
            ->orderBy('fecha')
            ->get();

        return $movimientos->map(function (AlmacenMovimiento $mov) {
            $obs = (string) $mov->observaciones;
            if (str_contains($obs, '[Distribución PDV')) {
                return null;
            }

            $usuario = $this->nombreUsuario($mov->usuario);

            if (str_contains($obs, '[Recepción planta')) {
                return $this->normalizarEvento(
                    $mov->fecha,
                    'planta',
                    'Recepción en planta',
                    'Materia prima ingresada al almacén',
                    'Cantidad: '.number_format((float) $mov->cantidad, 2).' unidades.'
                    ."\n".'Almacén: '.($mov->almacen?->nombre ?? 'Planta procesadora')
                    .($mov->referencia ? "\n".'Referencia envío: '.$mov->referencia : '')
                    .($usuario ? "\n".'Registrado por '.$usuario : ''),
                    'dolly',
                    'success',
                    $mov->almacen?->nombre,
                    $mov->referencia
                );
            }

            if (str_contains($obs, '[Consumo lote')) {
                preg_match('/\[Consumo lote ([^\]]+)\]/', $obs, $m);
                $codigoLote = $m[1] ?? null;

                return $this->normalizarEvento(
                    $mov->fecha,
                    'planta',
                    'Producción en planta',
                    'Consumo de materia prima',
                    'Utilizado en lote de producción «'.($codigoLote ?? 'procesamiento').'».'
                    ."\n".'Cantidad: '.number_format((float) $mov->cantidad, 2).' unidades.'
                    .($usuario ? "\n".'Registrado por '.$usuario : ''),
                    'cogs',
                    'info',
                    $mov->almacen?->nombre,
                    $codigoLote
                );
            }

            if (str_contains($obs, '[Traslado planta')) {
                return null;
            }

            return $this->normalizarEvento(
                $mov->fecha,
                'planta',
                'Almacén de planta',
                'Ingreso de producto terminado',
                trim(preg_replace('/^\[[^\]]+\]\s*/', '', $obs) ?: 'Producto terminado registrado en almacén de planta.')
                ."\n".'Cantidad: '.number_format((float) $mov->cantidad, 2).'.'
                .($usuario ? "\n".'Registrado por '.$usuario : ''),
                'warehouse',
                'success',
                $mov->almacen?->nombre,
                $mov->referencia
            );
        })->filter()->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventosPrevios
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosLoteProduccionPlanta(
        LoteProduccionPedido $lote,
        ?string $almacenNombre,
        Collection $eventosPrevios,
        PedidoDistribucion $pedido
    ): Collection {
        $lote->loadMissing([
            'evaluacionesFinales.inspector',
            'almacenajes.almacen',
            'registrosProceso.procesoMaquina.proceso',
            'registrosProceso.procesoMaquina.maquina',
            'registrosProceso.usuario',
            'unidadMedida',
            'plantillaTransformacion.pasos.proceso',
            'plantillaTransformacion.pasos.maquina',
        ]);

        $eventos = collect();
        $ubicacion = $almacenNombre ?? $lote->almacenajes->first()?->almacen?->nombre ?? 'Planta procesadora';

        if ($lote->hora_inicio || $lote->fecha_creacion) {
            $eventos->push($this->normalizarEvento(
                $lote->hora_inicio ?? $lote->fecha_creacion,
                'planta',
                'Producción en planta',
                'Lote de procesamiento iniciado',
                'Lote «'.$lote->codigo_lote.'» — '.$lote->nombre
                .($lote->cantidad_objetivo ? "\n".'Cantidad objetivo: '.number_format((float) $lote->cantidad_objetivo, 2).' '.($lote->unidadMedida?->abreviatura ?? 'ud') : ''),
                'industry',
                'info',
                $ubicacion,
                $lote->codigo_lote
            ));
        }

        $registros = $lote->registrosProceso->sortBy('hora_inicio');
        if ($registros->isNotEmpty()) {
            foreach ($registros as $registro) {
                $eventos->push($this->eventoDesdeRegistroProceso($registro, $ubicacion, $lote->codigo_lote));
            }
        } elseif ($lote->plantillaTransformacion !== null) {
            $eventos = $eventos->merge(
                $this->eventosDesdePlantillaTransformacion(
                    $lote->plantillaTransformacion,
                    null,
                    $pedido,
                    null,
                    $eventosPrevios,
                    $ubicacion,
                    $lote->hora_inicio ?? $lote->fecha_creacion
                )
            );
        }

        $evaluacion = $lote->evaluacionesFinales->sortByDesc('fecha_evaluacion')->first();
        if ($evaluacion) {
            $inspector = $this->nombreUsuario($evaluacion->inspector);
            $eventos->push($this->normalizarEvento(
                $evaluacion->fecha_evaluacion,
                'planta',
                'Control de calidad',
                'Evaluación: '.$evaluacion->razon,
                ($evaluacion->observaciones ? $evaluacion->observaciones."\n" : '')
                .($inspector ? 'Inspector: '.$inspector : 'Resultado registrado en planta'),
                'certificate',
                $evaluacion->esCertificado() ? 'success' : 'danger',
                $ubicacion,
                $lote->codigo_lote
            ));
        }

        foreach ($lote->almacenajes->sortBy('fecha_almacenaje') as $almacenaje) {
            $eventos->push($this->normalizarEvento(
                $almacenaje->fecha_almacenaje,
                'planta',
                'Almacenaje en planta',
                'Producto terminado ingresado',
                'Cantidad: '.number_format((float) $almacenaje->cantidad, 2).' '.($lote->unidadMedida?->abreviatura ?? 'ud')
                ."\n".'Almacén: '.($almacenaje->almacen?->nombre ?? $almacenaje->ubicacion)
                .($almacenaje->condicion ? "\n".'Condición: '.$almacenaje->condicion : '')
                .($almacenaje->observaciones ? "\n".$almacenaje->observaciones : ''),
                'warehouse',
                'success',
                $almacenaje->almacen?->nombre ?? $almacenaje->ubicacion,
                $lote->codigo_lote
            ));
        }

        return $eventos;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventosPrevios
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosDesdePlantillaTransformacion(
        PlantillaTransformacion $plantilla,
        ?Insumo $insumoPdv,
        PedidoDistribucion $pedido,
        ?Lote $loteAgricola,
        Collection $eventosPrevios,
        string $ubicacion,
        mixed $fechaInicioOverride = null
    ): Collection {
        $plantilla->loadMissing(['pasos.proceso', 'pasos.maquina']);
        $pasos = $plantilla->pasos->sortBy('orden')->values();
        if ($pasos->isEmpty()) {
            return collect();
        }

        $eventos = collect();
        $fechaPedido = Carbon::parse($pedido->fechapedido);
        $fechaCosecha = $this->fechaFinEtapaAgricola($eventosPrevios);
        $recepcionEnvio = $this->resolverEnvioAgricolaPlanta($loteAgricola, $insumoPdv ?? new Insumo)
            ?->fecha_recepcion_planta;

        if ($fechaInicioOverride !== null) {
            $fechaInicio = Carbon::parse($fechaInicioOverride);
        } elseif ($recepcionEnvio) {
            $fechaInicio = Carbon::parse($recepcionEnvio)->copy()->addHour();
        } elseif ($fechaCosecha !== null) {
            $fechaInicio = $fechaCosecha->copy()->addHours(8);
        } else {
            $fechaInicio = $fechaPedido->copy()->subDays(7);
        }

        $fechaFin = $fechaPedido->copy()->subHours(12);
        if ($fechaFin->lte($fechaInicio)) {
            $fechaInicio = $fechaPedido->copy()->subDays(4);
            $fechaFin = $fechaPedido->copy()->subHours(6);
        }

        $horasTotales = max(1, $fechaInicio->diffInHours($fechaFin));
        $producto = $insumoPdv?->nombre ?? $plantilla->producto_ejemplo ?? $plantilla->nombre;
        $tieneEnvioAgricola = $this->resolverEnvioAgricolaPlanta($loteAgricola, $insumoPdv ?? new Insumo) !== null;

        if ($loteAgricola !== null && ! $tieneEnvioAgricola) {
            $eventos->push($this->normalizarEvento(
                $fechaInicio->copy()->subHours(2),
                'planta',
                'Recepción en planta',
                'Ingreso de materia prima desde campo',
                'Lote agrícola «'.$loteAgricola->nombre.'» ('.$loteAgricola->codigo_trazabilidad.')'
                ."\n".'Producto de origen: '.$producto
                ."\n".'Pesaje, inspección visual y registro en almacén de materia prima.',
                'dolly',
                'success',
                $ubicacion,
                $loteAgricola->codigo_trazabilidad
            ));
        }

        $eventos->push($this->normalizarEvento(
            $fechaInicio,
            'planta',
            'Producción en planta',
            'Inicio de línea de transformación',
            'Plantilla: «'.$plantilla->nombre.'»'
            .($plantilla->descripcion ? "\n".$plantilla->descripcion : '')
            ."\n".'Producto objetivo: '.$producto,
            'industry',
            'info',
            $ubicacion,
            $plantilla->nombre
        ));

        $totalPasos = $pasos->count();
        foreach ($pasos as $idx => $paso) {
            $proceso = $paso->proceso?->nombre ?? 'Etapa de transformación';
            $maquina = $paso->maquina?->nombre ?? $paso->maquina?->codigo ?? null;
            $orden = (int) ($paso->orden ?: ($idx + 1));
            $fecha = $fechaInicio->copy()->addHours((int) round($horasTotales * ($orden - 0.5) / max($totalPasos, 1)));

            $lineas = [];
            if (filled($paso->notas)) {
                $lineas[] = $paso->notas;
            }
            if ($maquina) {
                $lineas[] = 'Equipo: '.$maquina;
            }
            $lineas[] = 'Paso '.$orden.' de '.$totalPasos.' — '.$proceso;

            $titulo = $this->tituloPasoPlanta($proceso, $paso->notas, $orden);

            $eventos->push($this->normalizarEvento(
                $fecha,
                'planta',
                'Transformación',
                $titulo,
                implode("\n", $lineas),
                $this->iconoProcesoPlanta($proceso),
                'info',
                $ubicacion,
                $plantilla->nombre
            ));
        }

        $eventos->push($this->normalizarEvento(
            $fechaFin->copy()->subHours(4),
            'planta',
            'Control de calidad',
            'Liberación de lote procesado',
            'Verificación organoléptica, humedad y granulometría conforme.'
            ."\n".'Producto: '.$producto
            ."\n".'Apto para despacho a centro mayorista.',
            'certificate',
            'success',
            $ubicacion,
            $plantilla->nombre
        ));

        $eventos->push($this->normalizarEvento(
            $fechaFin->copy()->subHours(1),
            'planta',
            'Almacenaje en planta',
            'Producto terminado en almacén de planta',
            'Stock listo para traslado al centro mayorista.'
            ."\n".'Producto: '.$producto,
            'warehouse',
            'success',
            $ubicacion,
            $plantilla->nombre
        ));

        return $eventos;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventosExistentes
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosRegistrosProcesoLoteAgricola(
        Lote $loteAgricola,
        string $ubicacion,
        Collection $eventosExistentes
    ): Collection {
        if ($eventosExistentes->where('etapa', 'planta')->where('tipo_evento', 'registro_proceso')->isNotEmpty()) {
            return collect();
        }

        return RegistroProcesoMaquinaPlanta::query()
            ->with(['procesoMaquina.proceso', 'procesoMaquina.maquina', 'usuario'])
            ->where('loteid', $loteAgricola->loteid)
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn (RegistroProcesoMaquinaPlanta $registro) => $this->eventoDesdeRegistroProceso(
                $registro,
                $ubicacion,
                $loteAgricola->codigo_trazabilidad,
                true
            ));
    }

    private function eventoDesdeRegistroProceso(
        RegistroProcesoMaquinaPlanta $registro,
        string $ubicacion,
        ?string $referencia,
        bool $marcarTipo = false
    ): array {
        $proceso = $registro->procesoMaquina?->nombre
            ?? $registro->procesoMaquina?->proceso?->nombre
            ?? 'Etapa de transformación';
        $maquina = $registro->procesoMaquina?->maquina?->nombre;
        $operador = $this->nombreUsuario($registro->usuario);

        $lineas = [$proceso];
        if ($maquina) {
            $lineas[] = 'Equipo: '.$maquina;
        }
        if ($operador) {
            $lineas[] = 'Operador: '.$operador;
        }
        if ($registro->hora_inicio && $registro->hora_fin) {
            $segundos = (int) $registro->hora_inicio->diffInSeconds($registro->hora_fin);
            if ($segundos > 0) {
                $minutos = max(1, (int) round($segundos / 60));
                $lineas[] = 'Duración: '.$minutos.' min';
            }
        }
        if ($registro->cumple_estandar === false) {
            $lineas[] = 'Observación: fuera de estándar';
        } elseif ($registro->cumple_estandar === true) {
            $lineas[] = 'Cumple estándar de proceso';
        }

        $imagenMaquina = $registro->procesoMaquina?->maquina?->imagenSrc();

        return $this->normalizarEvento(
            $registro->hora_inicio ?? $registro->fecha_registro,
            'planta',
            'Transformación',
            $proceso,
            implode("\n", $lineas),
            $this->iconoProcesoPlanta($proceso),
            $registro->cumple_estandar === false ? 'warning' : 'info',
            $ubicacion,
            $referencia,
            $imagenMaquina,
            $marcarTipo ? 'registro_proceso' : '',
            $imagenMaquina ? 'maquina' : null,
        );
    }

    private function resolverLoteProduccionPedido(
        Insumo $insumoPdv,
        PedidoDistribucion $pedido,
        ?Lote $loteAgricola
    ): ?LoteProduccionPedido {
        $clave = Str::lower(trim($insumoPdv->nombre));

        $query = LoteProduccionPedido::query()
            ->with([
                'evaluacionesFinales.inspector',
                'almacenajes.almacen',
                'registrosProceso.procesoMaquina.proceso',
                'registrosProceso.procesoMaquina.maquina',
                'registrosProceso.usuario',
                'unidadMedida',
                'plantillaTransformacion.pasos.proceso',
                'plantillaTransformacion.pasos.maquina',
            ]);

        if (Schema::hasColumn('lote_produccion_pedido', 'producto')) {
            $query->where(function ($q) use ($clave) {
                $q->whereRaw('LOWER(TRIM(producto)) = ?', [$clave])
                    ->orWhereRaw('LOWER(TRIM(nombre)) = ?', [$clave])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', [$clave.'%']);
            });
        } else {
            $query->whereRaw('LOWER(nombre) LIKE ?', [$clave.'%']);
        }

        $lote = $query->orderByDesc('loteproduccionpedidoid')->first();
        if ($lote !== null) {
            return $lote;
        }

        $plantilla = $this->resolverPlantillaTransformacion($insumoPdv->nombre);
        if ($plantilla !== null) {
            $lote = LoteProduccionPedido::query()
                ->with([
                    'evaluacionesFinales.inspector',
                    'almacenajes.almacen',
                    'registrosProceso.procesoMaquina.proceso',
                    'registrosProceso.procesoMaquina.maquina',
                    'registrosProceso.usuario',
                    'unidadMedida',
                    'plantillaTransformacion.pasos.proceso',
                    'plantillaTransformacion.pasos.maquina',
                ])
                ->where('plantillatransformacionid', $plantilla->plantillatransformacionid)
                ->orderByDesc('loteproduccionpedidoid')
                ->first();
        }

        return $lote;
    }

    private function resolverPlantillaTransformacion(string $nombreProducto): ?PlantillaTransformacion
    {
        $nombre = Str::lower(trim($nombreProducto));
        if ($nombre === '') {
            return null;
        }

        $plantillas = PlantillaTransformacion::query()
            ->with(['pasos.proceso', 'pasos.maquina'])
            ->where('activo', true)
            ->get();

        $mejor = null;
        $mejorPuntaje = 0;

        foreach ($plantillas as $plantilla) {
            $puntaje = 0;
            $ejemplo = Str::lower(trim((string) $plantilla->producto_ejemplo));
            $nombrePlantilla = Str::lower(trim($plantilla->nombre));

            if ($ejemplo !== '' && $ejemplo === $nombre) {
                $puntaje += 100;
            }
            if ($nombrePlantilla === $nombre) {
                $puntaje += 90;
            }
            if ($ejemplo !== '' && str_contains($nombre, $ejemplo)) {
                $puntaje += 40;
            }
            foreach ($plantilla->palabrasClaveLista() as $palabra) {
                $palabra = Str::lower(trim($palabra));
                if ($palabra !== '' && str_contains($nombre, $palabra)) {
                    $puntaje += 12;
                }
            }

            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $mejor = $plantilla;
            }
        }

        return $mejorPuntaje >= 12 ? $mejor : null;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventos
     */
    private function fechaFinEtapaAgricola(Collection $eventos): ?Carbon
    {
        $agricolas = $eventos->where('etapa', 'agricola');
        if ($agricolas->isEmpty()) {
            return null;
        }

        $cosecha = $agricolas->filter(function (array $e) {
            $texto = mb_strtolower(($e['titulo'] ?? '').' '.($e['descripcion'] ?? ''));

            return str_contains($texto, 'cosecha')
                || str_contains($texto, 'enviado hacia planta')
                || str_contains($texto, 'planta procesadora');
        })->map(fn (array $e) => Carbon::parse($e['fecha']))->filter();

        if ($cosecha->isNotEmpty()) {
            return $cosecha->max();
        }

        return $agricolas->map(fn (array $e) => Carbon::parse($e['fecha']))->max();
    }

    private function tituloPasoPlanta(string $proceso, ?string $notas, int $orden): string
    {
        if (filled($notas)) {
            return trim((string) $notas);
        }

        return $proceso.' (paso '.$orden.')';
    }

    private function iconoProcesoPlanta(string $proceso): string
    {
        $p = mb_strtolower($proceso);

        return match (true) {
            str_contains($p, 'secado') => 'wind',
            str_contains($p, 'mezcl') || str_contains($p, 'moli') => 'blender',
            str_contains($p, 'térm') || str_contains($p, 'term') || str_contains($p, 'cocc') => 'fire',
            str_contains($p, 'envas') || str_contains($p, 'empaq') => 'box',
            str_contains($p, 'etiquet') => 'tag',
            str_contains($p, 'prepar') || str_contains($p, 'lavado') => 'shower',
            default => 'cogs',
        };
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosDistribucion(PedidoDistribucion $pedido, ?PuntoVenta $punto, Insumo $insumoPdv): Collection
    {
        $det = $pedido->detalles->first();
        $unidad = $det?->insumo?->unidadMedida?->abreviatura
            ?? $det?->insumo?->unidadMedida?->nombre
            ?? $insumoPdv->unidadMedida?->abreviatura
            ?? 'ud';
        $eventos = collect();

        $lineasSolicitud = [
            'Pedido '.$pedido->numero_solicitud,
        ];
        if ($det) {
            $lineasSolicitud[] = 'Cantidad: '.number_format((float) $det->cantidad, 2).' '.$unidad.' de '.$det->producto_nombre;
        }
        if ($pedido->almacenMayoristaOrigen?->nombre) {
            $lineasSolicitud[] = 'Mayorista origen: '.$pedido->almacenMayoristaOrigen->nombre;
        }
        if ($pedido->fecha_entrega_deseada) {
            $lineasSolicitud[] = 'Entrega deseada: '.Carbon::parse($pedido->fecha_entrega_deseada)->format('d/m/Y');
        }
        if ($pedido->creadoPor) {
            $lineasSolicitud[] = 'Solicitado por '.$this->nombreUsuario($pedido->creadoPor);
        }
        if (filled($pedido->observaciones) && ! str_contains((string) $pedido->observaciones, '[Rechazado planta]')) {
            $lineasSolicitud[] = 'Notas: '.Str::limit((string) $pedido->observaciones, 120);
        }

        $eventos->push($this->normalizarEvento(
            $pedido->fechapedido,
            'distribucion',
            'Comercialización',
            'Solicitud del minorista',
            implode("\n", $lineasSolicitud),
            'paper-plane',
            'warning',
            $punto?->nombre ?? $pedido->puntoVenta?->nombre
        ));

        if ($pedido->estado === PedidoDistribucionCatalogo::ESTADO_RECHAZADO) {
            $eventos->push($this->normalizarEvento(
                $pedido->fechapedido,
                'planta',
                'Planta procesadora',
                'Pedido rechazado',
                'La planta no pudo atender esta solicitud.'
                .($pedido->observaciones ? "\n".Str::limit((string) $pedido->observaciones, 200) : ''),
                'times-circle',
                'danger',
                $pedido->almacenPlantaOrigen?->nombre
            ));

            return $eventos;
        }

        if ($pedido->fecha_aceptacion) {
            $lineasAceptacion = [
                'Pedido confirmado por el centro mayorista.',
            ];
            if ($det) {
                $lineasAceptacion[] = 'Cantidad: '.number_format((float) $det->cantidad, 2).' '.$unidad;
            }
            $lineasAceptacion[] = 'Almacén: '.($pedido->almacenMayoristaOrigen?->nombre ?? 'Centro mayorista');
            if ($pedido->aceptadoPor) {
                $lineasAceptacion[] = 'Revisado por '.$this->nombreUsuario($pedido->aceptadoPor);
            }

            $eventos->push($this->normalizarEvento(
                $pedido->fecha_aceptacion,
                'mayorista',
                'Centro mayorista',
                'Pedido aceptado',
                implode("\n", $lineasAceptacion),
                'check-circle',
                'info',
                $pedido->almacenMayoristaOrigen?->nombre
            ));
        }

        $ruta = $pedido->rutaDistribucion;
        if ($ruta) {
            $trayecto = $this->rutaService->trayectoTexto($ruta);
            $transportista = $this->nombreUsuario($ruta->transportista);
            $vehiculo = $ruta->vehiculo
                ? trim($ruta->vehiculo->placa.' '.($ruta->vehiculo->marca ?? '').' '.($ruta->vehiculo->modelo ?? ''))
                : null;

            $lineasRuta = ['Ruta '.$ruta->codigo.' asignada al pedido.'];
            if ($trayecto) {
                $lineasRuta[] = 'Trayecto: '.$trayecto;
            }
            if ($transportista) {
                $lineasRuta[] = 'Transportista: '.$transportista;
            }
            if ($vehiculo) {
                $lineasRuta[] = 'Vehículo: '.trim($vehiculo);
            }

            $eventos->push($this->normalizarEvento(
                $ruta->fecha_salida ?? $pedido->fecha_envio,
                'distribucion',
                'Logística PDV',
                'Ruta de distribución',
                implode("\n", $lineasRuta),
                'route',
                'primary',
                $ruta->almacenOrigen?->nombre ?? $pedido->almacenPlantaOrigen?->nombre
            ));
        }

        if ($pedido->fecha_envio) {
            $lineasTransito = [
                'El producto salió del almacén mayorista con destino «'.($pedido->puntoVenta?->nombre ?? 'punto de venta').'».',
            ];
            if ($det) {
                $lineasTransito[] = 'Cantidad enviada: '.number_format((float) $det->cantidad, 2).' '.$unidad;
            }
            if ($pedido->almacenMayoristaOrigen?->nombre) {
                $lineasTransito[] = 'Origen: '.$pedido->almacenMayoristaOrigen->nombre;
            }
            $transportistaDirecto = $this->nombreUsuario($pedido->transportista);
            if ($transportistaDirecto && ! $ruta) {
                $lineasTransito[] = 'Transportista: '.$transportistaDirecto;
            }
            if ($pedido->vehiculo && ! $ruta) {
                $vehiculoTxt = trim($pedido->vehiculo->placa.' '.($pedido->vehiculo->marca ?? '').' '.($pedido->vehiculo->modelo ?? ''));
                if ($vehiculoTxt !== '') {
                    $lineasTransito[] = 'Vehículo: '.$vehiculoTxt;
                }
            }

            $eventos->push($this->normalizarEvento(
                $pedido->fecha_envio,
                'distribucion',
                'Logística PDV',
                'En tránsito hacia punto de venta',
                implode("\n", $lineasTransito),
                'shipping-fast',
                'primary',
                $pedido->almacenMayoristaOrigen?->nombre
                    ?? $ruta?->almacenOrigen?->nombre
                    ?? $pedido->almacenPlantaOrigen?->nombre
            ));
        }

        $movSalida = AlmacenMovimiento::query()
            ->with('usuario')
            ->where('referencia', $pedido->numero_solicitud)
            ->where(function ($q) {
                $q->where('observaciones', 'like', '[Distribución PDV — salida%')
                    ->orWhere('observaciones', 'like', '[Salida mayorista%');
            })
            ->orderByDesc('almacen_movimientoid')
            ->first();

        if ($movSalida) {
            $eventos->push($this->normalizarEvento(
                $pedido->fecha_recepcion ?? $pedido->fecha_envio ?? $movSalida->fecha,
                'mayorista',
                'Salida de almacén mayorista',
                'Despacho hacia punto de venta',
                'Salida registrada en inventario del centro mayorista.'
                ."\n".'Cantidad: '.number_format((float) $movSalida->cantidad, 2).' '.$unidad
                .($movSalida->destino_motivo ? "\n".'Destino: '.$movSalida->destino_motivo : '')
                .($movSalida->usuario ? "\n".'Registrado por '.$this->nombreUsuario($movSalida->usuario) : ''),
                'truck-loading',
                'warning',
                $pedido->almacenMayoristaOrigen?->nombre,
                $pedido->numero_solicitud
            ));
        }

        $movRecepcion = AlmacenMovimiento::query()
            ->with('usuario')
            ->where('insumoid', $insumoPdv->insumoid)
            ->where('referencia', $pedido->numero_solicitud)
            ->where('observaciones', 'like', '[Recepción PDV]%')
            ->orderByDesc('almacen_movimientoid')
            ->first();

        $fechaRecepcion = $pedido->fecha_recepcion ?? $movRecepcion?->fecha;
        if ($fechaRecepcion) {
            $lineasRecepcion = [
                'El minorista confirmó la llegada del pedido.',
                'Producto ingresado al inventario local.',
            ];
            if ($det) {
                $lineasRecepcion[] = 'Cantidad: '.number_format((float) $det->cantidad, 2).' '.$unidad;
            }
            if ($movRecepcion?->usuario) {
                $lineasRecepcion[] = 'Confirmado por '.$this->nombreUsuario($movRecepcion->usuario);
            }

            $eventos->push($this->normalizarEvento(
                $fechaRecepcion,
                'pdv',
                'Punto de venta',
                'Recepción en tienda',
                implode("\n", $lineasRecepcion),
                'dolly',
                'success',
                $pedido->puntoVenta?->nombre
            ));
        }

        return $eventos;
    }

    private function resolverInsumoPlantaAlmacen(Insumo $insumoPdv, PedidoDistribucion $pedido): ?Insumo
    {
        if ($pedido->almacen_planta_origenid) {
            $enPlanta = Insumo::query()
                ->where('almacenid', $pedido->almacen_planta_origenid)
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($insumoPdv->nombre))])
                ->first();

            if ($enPlanta !== null) {
                return $enPlanta;
            }
        }

        return null;
    }

    private function resolverInsumoPlanta(Insumo $insumoPdv, PedidoDistribucion $pedido): ?Insumo
    {
        $det = $pedido->detalles->first();
        if ($det?->insumo) {
            return $det->insumo;
        }

        if ($pedido->almacen_planta_origenid) {
            return Insumo::query()
                ->where('almacenid', $pedido->almacen_planta_origenid)
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($insumoPdv->nombre))])
                ->first();
        }

        return null;
    }

    private function ultimaFechaRecepcionPdv(Insumo $insumo): mixed
    {
        $mov = AlmacenMovimiento::query()
            ->where('insumoid', $insumo->insumoid)
            ->where('observaciones', 'like', '[Recepción PDV]%')
            ->orderByDesc('almacen_movimientoid')
            ->first();

        return $mov?->fecha;
    }

    private function limiteCronologicoPedido(PedidoDistribucion $pedido): ?Carbon
    {
        $fecha = $pedido->fecha_recepcion ?? $pedido->fecha_envio ?? $pedido->fecha_aceptacion ?? $pedido->fechapedido;

        return $fecha ? Carbon::parse($fecha) : null;
    }

    private function nombreUsuario(?object $usuario): ?string
    {
        if ($usuario === null) {
            return null;
        }

        $nombre = trim(($usuario->nombre ?? '').' '.($usuario->apellido ?? ''));

        return $nombre !== '' ? $nombre : null;
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
        ?string $referencia = null,
        ?string $evidenciaUrl = null,
        string $tipoEvento = '',
        ?string $evidenciaTipo = null,
        ?string $evidenciaFotoUrl = null,
    ): array {
        $evento = [
            'fecha' => $fecha,
            'fecha_fmt' => $fecha ? Carbon::parse($fecha)->timezone(config('app.timezone'))->format('d/m/Y H:i') : '—',
            'etapa' => $etapa,
            'etapa_label' => $etapaLabel,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'icon' => $icono,
            'color' => $color,
            'ubicacion' => $ubicacion,
            'referencia' => $referencia,
        ];

        if (filled($evidenciaUrl)) {
            $evento['evidencia_url'] = $evidenciaUrl;
        }
        if ($tipoEvento !== '') {
            $evento['tipo_evento'] = $tipoEvento;
        }
        if (filled($evidenciaTipo)) {
            $evento['evidencia_tipo'] = $evidenciaTipo;
        }
        if (filled($evidenciaFotoUrl)) {
            $evento['evidencia_foto_url'] = $evidenciaFotoUrl;
        }

        return $evento;
    }
}
