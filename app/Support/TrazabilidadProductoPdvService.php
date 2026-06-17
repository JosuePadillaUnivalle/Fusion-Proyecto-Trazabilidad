<?php

namespace App\Support;

use App\Models\AlmacenMovimiento;
use App\Models\Cultivo;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteProduccionPedido;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
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

        return PublicUrlHelper::absolute($path);
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
        $lote = $this->resolverLoteAgricola($insumo->nombre, (string) ($insumo->descripcion ?? ''));

        $eventos = collect();

        if ($lote) {
            $eventos = $eventos->merge(
                $this->loteTrazabilidad->buildEventos($lote)->map(fn (array $e) => $this->normalizarEvento(
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
                    (string) ($e['tipo'] ?? '')
                ))
            );
        } else {
            $eventos = $eventos->merge($this->eventosAgricolaInferidos($insumo->nombre, $pedido));
        }

        if ($pedido) {
            $eventos = $eventos->merge($this->eventosPlanta($insumo, $pedido));
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

        if ($pedido) {
            $limitePlanta = $this->limiteCronologicoPedido($pedido);
            if ($limitePlanta !== null) {
                $eventos = $eventos->filter(
                    fn (array $e) => $e['etapa'] !== 'planta' || Carbon::parse($e['fecha'])->lte($limitePlanta)
                );
            }
        }

        $ordenEtapas = ['agricola' => 1, 'planta' => 2, 'distribucion' => 3, 'pdv' => 4];

        $eventos = $eventos
            ->filter(fn (array $e) => $e['fecha'] !== null)
            ->sortBy(fn (array $e) => [
                Carbon::parse($e['fecha'])->timestamp,
                $ordenEtapas[$e['etapa']] ?? 9,
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
            ['key' => 'distribucion', 'label' => 'Distribución', 'icon' => 'shipping-fast'],
            ['key' => 'planta', 'label' => 'Planta / almacén', 'icon' => 'industry'],
            ['key' => 'pdv', 'label' => 'Punto de venta', 'icon' => 'store'],
        ];

        $eventosAgrupados = collect($categorias)->map(function (array $cat) use ($eventos) {
            $items = $eventos->where('etapa', $cat['key'])->values()->all();

            return array_merge($cat, [
                'eventos' => $items,
                'total' => count($items),
                'orden_cronologico' => collect($items)->min(
                    fn (array $e) => Carbon::parse($e['fecha'])->timestamp
                ) ?? PHP_INT_MAX,
            ]);
        })
            ->filter(fn (array $cat) => $cat['total'] > 0)
            ->sortBy('orden_cronologico')
            ->values()
            ->map(fn (array $cat) => array_diff_key($cat, ['orden_cronologico' => true]))
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

    private function resolverLoteAgricola(string $nombreProducto, string $descripcion = ''): ?Lote
    {
        if (preg_match('/(TRAZ-[A-Z0-9\-]+)/', $descripcion, $match)) {
            $lote = Lote::query()
                ->with(['cultivo', 'estadoTipo', 'usuario'])
                ->where('codigo_trazabilidad', $match[1])
                ->first();

            if ($lote !== null) {
                return $lote;
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
    private function eventosPlanta(Insumo $insumoPdv, PedidoDistribucion $pedido): Collection
    {
        $eventos = collect();
        $insumoPlanta = $this->resolverInsumoPlanta($insumoPdv, $pedido);

        if ($insumoPlanta) {
            $eventos = $eventos->merge($this->eventosMovimientosPlanta($insumoPlanta, $pedido->numero_solicitud));
        }

        $eventos = $eventos->merge($this->eventosLoteProduccionPlanta($insumoPdv->nombre, $pedido->almacenPlantaOrigen?->nombre));

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
            ->where(function ($q) use ($refPedido) {
                $q->where('observaciones', 'like', '[Recepción planta%')
                    ->orWhere('observaciones', 'like', '[Consumo lote%')
                    ->orWhere('observaciones', 'like', '[Ingreso%');
                if ($refPedido) {
                    $q->orWhere(function ($w) use ($refPedido) {
                        $w->where('referencia', $refPedido)
                            ->where('observaciones', 'like', '[Distribución PDV%');
                    });
                }
            })
            ->orderBy('fecha')
            ->get();

        return $movimientos->map(function (AlmacenMovimiento $mov) {
            $obs = (string) $mov->observaciones;
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

            return $this->normalizarEvento(
                $mov->fecha,
                'planta',
                'Almacén de planta',
                'Movimiento de inventario',
                trim(preg_replace('/^\[[^\]]+\]\s*/', '', $obs) ?: 'Ingreso registrado en planta.')
                ."\n".'Cantidad: '.number_format((float) $mov->cantidad, 2).'.'
                .($usuario ? "\n".'Registrado por '.$usuario : ''),
                'warehouse',
                'secondary',
                $mov->almacen?->nombre,
                $mov->referencia
            );
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosLoteProduccionPlanta(string $nombreProducto, ?string $almacenNombre): Collection
    {
        $clave = Str::lower(trim($nombreProducto));
        if ($clave === '') {
            return collect();
        }

        $query = LoteProduccionPedido::query()
            ->with([
                'evaluacionesFinales.inspector',
                'almacenajes.almacen',
                'registrosProceso.procesoMaquina.proceso',
                'registrosProceso.procesoMaquina.maquina',
                'registrosProceso.usuario',
                'unidadMedida',
            ]);

        if (Schema::hasColumn('lote_produccion_pedido', 'producto')) {
            $query->where(function ($q) use ($clave) {
                $q->whereRaw('LOWER(TRIM(producto)) = ?', [$clave])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', [$clave.'%']);
            });
        } else {
            $query->whereRaw('LOWER(nombre) LIKE ?', [$clave.'%']);
        }

        $lote = $query->orderByDesc('loteproduccionpedidoid')->first();
        if ($lote === null) {
            return collect();
        }

        $eventos = collect();
        $ubicacion = $almacenNombre ?? $lote->almacenajes->first()?->almacen?->nombre;

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

        foreach ($lote->registrosProceso->sortBy('hora_inicio') as $registro) {
            $proceso = $registro->procesoMaquina?->nombre
                ?? $registro->procesoMaquina?->proceso?->nombre
                ?? 'Etapa de transformación';
            $maquina = $registro->procesoMaquina?->maquina?->nombre;
            $operador = $this->nombreUsuario($registro->usuario);

            $lineas = [$proceso];
            if ($maquina) {
                $lineas[] = 'Maquinaria: '.$maquina;
            }
            if ($operador) {
                $lineas[] = 'Operador: '.$operador;
            }
            if ($registro->cumple_estandar === false) {
                $lineas[] = 'Observación: fuera de estándar';
            }

            $eventos->push($this->normalizarEvento(
                $registro->hora_inicio ?? $registro->fecha_registro ?? $lote->hora_inicio,
                'planta',
                'Transformación',
                $proceso,
                implode("\n", $lineas),
                'cogs',
                $registro->cumple_estandar === false ? 'warning' : 'info',
                $ubicacion,
                $lote->codigo_lote
            ));
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
        if ($pedido->almacenPlantaOrigen?->nombre) {
            $lineasSolicitud[] = 'Planta origen: '.$pedido->almacenPlantaOrigen->nombre;
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
                'Pedido confirmado con stock disponible.',
            ];
            if ($det) {
                $lineasAceptacion[] = 'Cantidad: '.number_format((float) $det->cantidad, 2).' '.$unidad;
            }
            $lineasAceptacion[] = 'Almacén: '.($pedido->almacenPlantaOrigen?->nombre ?? 'Planta procesadora');
            if ($pedido->aceptadoPor) {
                $lineasAceptacion[] = 'Revisado por '.$this->nombreUsuario($pedido->aceptadoPor);
            }

            $eventos->push($this->normalizarEvento(
                $pedido->fecha_aceptacion,
                'planta',
                'Planta procesadora',
                'Aceptado por planta',
                implode("\n", $lineasAceptacion),
                'industry',
                'info',
                $pedido->almacenPlantaOrigen?->nombre
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
                'El producto salió de planta con destino «'.($pedido->puntoVenta?->nombre ?? 'punto de venta').'».',
            ];
            if ($det) {
                $lineasTransito[] = 'Cantidad enviada: '.number_format((float) $det->cantidad, 2).' '.$unidad;
            }
            if ($pedido->almacenPlantaOrigen?->nombre) {
                $lineasTransito[] = 'Origen: '.$pedido->almacenPlantaOrigen->nombre;
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
                $pedido->almacenPlantaOrigen?->nombre
            ));
        }

        $movSalida = AlmacenMovimiento::query()
            ->with('usuario')
            ->where('referencia', $pedido->numero_solicitud)
            ->where('observaciones', 'like', '[Distribución PDV — salida planta]%')
            ->orderByDesc('almacen_movimientoid')
            ->first();

        if ($movSalida) {
            $eventos->push($this->normalizarEvento(
                $pedido->fecha_recepcion ?? $pedido->fecha_envio ?? $movSalida->fecha,
                'planta',
                'Salida de almacén',
                'Despacho desde planta',
                'Salida registrada en inventario de planta.'
                ."\n".'Cantidad: '.number_format((float) $movSalida->cantidad, 2).' '.$unidad
                .($movSalida->destino_motivo ? "\n".'Destino: '.$movSalida->destino_motivo : '')
                .($movSalida->usuario ? "\n".'Registrado por '.$this->nombreUsuario($movSalida->usuario) : ''),
                'truck-loading',
                'warning',
                $pedido->almacenPlantaOrigen?->nombre,
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
        string $tipoEvento = ''
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

        return $evento;
    }
}
