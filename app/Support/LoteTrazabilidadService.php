<?php

namespace App\Support;

use App\Models\CertificacionLote;
use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Support\EstadoLoteCatalogo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LoteTrazabilidadService
{
    /** @var array<string, array{label: string, orden: int, color: string, icon: string}> */
    public const FASES = [
        'preparacion' => ['label' => 'Preparación', 'orden' => 1, 'color' => '#6c757d', 'icon' => 'tools'],
        'siembra' => ['label' => 'Siembra', 'orden' => 2, 'color' => '#17a2b8', 'icon' => 'seedling'],
        'en_crecimiento' => ['label' => 'En crecimiento', 'orden' => 3, 'color' => '#28a745', 'icon' => 'leaf'],
        'cosecha' => ['label' => 'Cosecha', 'orden' => 4, 'color' => '#e83e8c', 'icon' => 'tractor'],
        'envio_almacen' => ['label' => 'Envío al almacén', 'orden' => 5, 'color' => '#6f42c1', 'icon' => 'warehouse'],
    ];

    /** Fases internas de eventos (historial) que se agrupan en «En crecimiento» en el pipeline. */
    private const FASES_EVENTO_EXTRA = [
        'regado' => ['label' => 'Regado', 'color' => '#28a745', 'icon' => 'tint'],
        'fumigacion' => ['label' => 'Fumigación', 'color' => '#fd7e14', 'icon' => 'spray-can'],
    ];

    /** @var array<string, string> */
    private const ESTADO_A_FASE = [
        'planificado' => 'preparacion',
        'disponible' => 'preparacion',
        'en preparación' => 'preparacion',
        'en preparacion' => 'preparacion',
        'sembrado' => 'siembra',
        'en crecimiento' => 'en_crecimiento',
        'en producción' => 'en_crecimiento',
        'en produccion' => 'en_crecimiento',
        'listo para cosecha' => 'cosecha',
        'cosechado' => 'cosecha',
        'finalizado' => 'envio_almacen',
        'en descanso' => 'preparacion',
    ];

    public function fasesMeta(): array
    {
        return self::FASES;
    }

    /** @return array<string, array{label: string, color: string, icon?: string}> */
    public function fasesMetaEvento(): array
    {
        $base = collect(self::FASES)->map(fn ($m) => [
            'label' => $m['label'],
            'color' => $m['color'],
            'icon' => $m['icon'],
        ])->all();

        foreach (self::FASES_EVENTO_EXTRA as $key => $meta) {
            $base[$key] = $meta;
        }

        return $base;
    }

    public function faseFromEstado(?string $estadoNombre): string
    {
        $key = strtolower(trim($estadoNombre ?? 'disponible'));

        return self::ESTADO_A_FASE[$key] ?? 'preparacion';
    }

    public function resolverFaseActual(Lote $lote): string
    {
        $lote->loadMissing([
            'estadoTipo',
            'actividades.tipoActividad',
            'producciones.almacenamientos',
        ]);

        if ($this->milestoneEnvioAlmacen($lote)) {
            return 'envio_almacen';
        }
        if ($this->milestoneCosecha($lote)) {
            return 'envio_almacen';
        }
        if ($this->milestoneFumigacion($lote) || $this->milestoneRegado($lote)) {
            return 'en_crecimiento';
        }
        if ($this->milestoneSiembra($lote)) {
            return 'en_crecimiento';
        }

        return 'preparacion';
    }

    public function trazabilidadCompleta(Lote $lote): bool
    {
        $lote->loadMissing(['producciones.almacenamientos']);

        return $this->milestoneEnvioAlmacen($lote);
    }

    public function siguienteFase(string $faseActual): ?string
    {
        $ordenActual = self::FASES[$faseActual]['orden'] ?? 1;
        foreach (self::FASES as $key => $meta) {
            if ($meta['orden'] === $ordenActual + 1) {
                return $key;
            }
        }

        return null;
    }

    /**
     * URL directa para registrar la fase indicada (usada en el botón «siguiente» del pipeline).
     */
    public function urlAccionFase(Lote $lote, string $faseKey): ?string
    {
        $return = route('lotes.trazabilidad', $lote);

        return match ($faseKey) {
            'siembra' => route('actividades.create', [
                'loteid' => $lote->loteid,
                'tipo' => 'Siembra',
                'return' => $return,
                'completar' => 1,
            ]),
            'en_crecimiento' => match (true) {
                ! $this->milestoneRegado($lote) => route('actividades.create', [
                    'loteid' => $lote->loteid,
                    'tipo' => 'Riego',
                    'return' => $return,
                    'completar' => 1,
                ]),
                ! $this->milestoneFumigacion($lote) => route('actividades.create', [
                    'loteid' => $lote->loteid,
                    'tipo' => 'Control de plagas',
                    'return' => $return,
                    'completar' => 1,
                ]),
                default => route('producciones.create', [
                    'loteid' => $lote->loteid,
                    'return' => $return,
                ]),
            },
            'cosecha' => route('producciones.create', [
                'loteid' => $lote->loteid,
                'return' => $return,
            ]),
            'envio_almacen' => route('producciones_almacenamiento.create', [
                'loteid' => $lote->loteid,
                'return' => $return,
            ]),
            default => null,
        };
    }

    private function milestoneSiembra(Lote $lote): bool
    {
        if ($lote->fechasiembra) {
            return true;
        }

        $estado = mb_strtolower(trim($lote->estadoTipo->nombre ?? ''));

        return $estado === 'sembrado'
            || $this->actividadCompletadaConKeywords($lote, ['siembra']);
    }

    private function milestoneRegado(Lote $lote): bool
    {
        return $this->actividadCompletadaConKeywords($lote, ['riego', 'regad']);
    }

    private function milestoneFumigacion(Lote $lote): bool
    {
        return $this->actividadCompletadaConKeywords($lote, ['fumig', 'plaga', 'fitosanit']);
    }

    private function milestoneCosecha(Lote $lote): bool
    {
        return $lote->producciones->isNotEmpty();
    }

    private function milestoneEnvioAlmacen(Lote $lote): bool
    {
        return $lote->producciones->flatMap->almacenamientos->isNotEmpty();
    }

    /**
     * @param  array<int, string>  $keywords
     */
    private function actividadCompletadaConKeywords(Lote $lote, array $keywords): bool
    {
        return $lote->actividades
            ->whereNotNull('fechafin')
            ->contains(function ($actividad) use ($keywords) {
                $nombre = mb_strtolower(trim($actividad->tipoActividad->nombre ?? ''));

                foreach ($keywords as $keyword) {
                    if (str_contains($nombre, mb_strtolower($keyword))) {
                        return true;
                    }
                }

                return false;
            });
    }

    public function progresoFase(string $faseKey): int
    {
        $orden = self::FASES[$faseKey]['orden'] ?? 1;
        $total = count(self::FASES);

        return (int) round(($orden / $total) * 100);
    }

    /**
     * Pasos y fases que faltan para alcanzar comercialización (100 %).
     *
     * @return array{
     *     completo: bool,
     *     siguiente_fase: ?string,
     *     siguiente_label: ?string,
     *     progreso_siguiente: ?int,
     *     fases_restantes: array<int, string>,
     *     acciones: array<int, string>,
     *     resumen: string
     * }
     */
    public function pasosHaciaCompleto(Lote $lote, string $faseActual): array
    {
        $lote->loadMissing([
            'estadoTipo',
            'actividades.tipoActividad',
            'loteInsumos',
            'producciones.almacenamientos',
        ]);

        $ordenActual = self::FASES[$faseActual]['orden'] ?? 1;

        if ($this->trazabilidadCompleta($lote)) {
            return [
                'completo' => true,
                'siguiente_fase' => null,
                'siguiente_label' => null,
                'progreso_siguiente' => 100,
                'fases_restantes' => [],
                'acciones' => [],
                'resumen' => 'Trazabilidad completa (100 %)',
            ];
        }

        if ($faseActual === 'envio_almacen') {
            $acciones = [
                'Registrar el ingreso del producto cosechado al almacén',
                'Indicar almacén, cantidad y condiciones de conservación',
            ];

            return [
                'completo' => false,
                'siguiente_fase' => null,
                'siguiente_label' => null,
                'progreso_siguiente' => 100,
                'fases_restantes' => [],
                'acciones' => $acciones,
                'resumen' => 'Último paso: enviar la cosecha al almacén (100 %)',
            ];
        }

        $fasesRestantes = [];
        $siguienteKey = null;
        foreach (self::FASES as $key => $meta) {
            if ($meta['orden'] > $ordenActual) {
                $fasesRestantes[] = $meta['label'];
                $siguienteKey ??= $key;
            }
        }

        $acciones = $this->accionesSugeridas($lote, $faseActual, $siguienteKey);
        $siguienteLabel = $siguienteKey ? (self::FASES[$siguienteKey]['label'] ?? $siguienteKey) : null;
        $progresoSiguiente = $siguienteKey ? $this->progresoFase($siguienteKey) : 100;

        $resumen = $siguienteLabel
            ? sprintf(
                'Siguiente meta: %s (%d %%)',
                $siguienteLabel,
                $progresoSiguiente
            )
            : 'En avance hacia envío al almacén';

        if (count($fasesRestantes) > 1) {
            $resumen .= ' · Después: '.implode(' → ', array_slice($fasesRestantes, 1));
        }

        return [
            'completo' => false,
            'siguiente_fase' => $siguienteKey,
            'siguiente_label' => $siguienteLabel,
            'progreso_siguiente' => $progresoSiguiente,
            'fases_restantes' => $fasesRestantes,
            'acciones' => $acciones,
            'resumen' => $resumen,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function accionesSugeridas(Lote $lote, string $faseActual, ?string $siguienteFase): array
    {
        $lote->loadMissing([
            'actividades',
            'loteInsumos',
            'producciones.almacenamientos',
            'producciones.ventas',
            'certificaciones',
        ]);

        $pasos = [];

        switch ($faseActual) {
            case 'preparacion':
                $pasos[] = 'Registrar la siembra del lote';
                $pasos[] = 'Programar labranza o preparación del suelo si aplica';
                break;

            case 'siembra':
                if (! $lote->fechasiembra) {
                    $pasos[] = 'Confirmar fecha de siembra en el lote';
                }
                $pasos[] = 'Registrar actividades de siembra completadas';
                break;

            case 'en_crecimiento':
                if (! $this->milestoneRegado($lote)) {
                    $pasos[] = 'Registrar riego / regado del lote';
                }
                if (! $this->milestoneFumigacion($lote)) {
                    $pasos[] = 'Registrar fumigación o control de plagas';
                }
                $pasos[] = 'Documentar fechas y observaciones de las actividades de crecimiento';
                break;

            case 'cosecha':
                if ($lote->producciones->isEmpty()) {
                    $pasos[] = 'Registrar la cosecha (kg, fecha y destino)';
                }
                $pasos[] = 'Verificar cantidad cosechada antes del envío a almacén';
                break;

            case 'envio_almacen':
                if ($lote->producciones->flatMap->almacenamientos->isEmpty()) {
                    $pasos[] = 'Registrar el ingreso del producto al almacén';
                }
                $pasos[] = 'Verificar stock y condiciones de conservación';
                break;
        }

        if ($siguienteFase && isset(self::FASES[$siguienteFase])) {
            array_unshift(
                $pasos,
                'Alcanzar la fase «'.self::FASES[$siguienteFase]['label'].'» ('.$this->progresoFase($siguienteFase).' %)'
            );
        }

        return array_values(array_unique(array_slice($pasos, 0, 5)));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function buildEventos(Lote $lote): Collection
    {
        $lote->loadMissing([
            'cultivo',
            'historialEstados.estadoTipo',
            'historialEstados.usuario',
            'loteInsumos.insumo',
            'loteInsumos.usuario',
            'actividades.tipoActividad',
            'actividades.usuario',
            'producciones.unidadMedida',
            'producciones.destino',
            'producciones.almacenamientos.almacen',
            'producciones.almacenamientos.unidadMedida',
            'producciones.ventas',
            'certificaciones.usuario',
        ]);

        $eventos = collect();

        if ($lote->fechasiembra) {
            $tieneActividadSiembra = $lote->actividades->contains(function ($actividad) {
                $nombre = mb_strtolower(trim($actividad->tipoActividad->nombre ?? ''));

                return str_contains($nombre, 'siembra');
            });

            if (! $tieneActividadSiembra) {
                $eventos->push($this->evento(
                    $lote->fechasiembra,
                    'siembra',
                    'siembra',
                    'Siembra iniciada',
                    'Cultivo: '.($lote->cultivo->nombre ?? 'No especificado'),
                    null,
                    'seedling',
                    'success'
                ));
            }
        }

        foreach ($lote->historialEstados as $historial) {
            $estadoNombre = $historial->estadoTipo->nombre ?? '';
            if (EstadoLoteCatalogo::slugFromNombre($estadoNombre) === 'sembrado') {
                continue;
            }

            $usuarioHist = $historial->usuario;
            $nombreHist = trim(($usuarioHist->nombre ?? '').' '.($usuarioHist->apellido ?? ''));
            $rolHist = ucfirst($usuarioHist->role ?? '');
            $descripcion = $historial->observaciones ?? 'Sin observaciones';
            if ($nombreHist && ! str_contains($descripcion, 'Realizado por:')) {
                $descripcion .= ' · Realizado por: '.$nombreHist
                    .($rolHist ? " ({$rolHist})" : '')
                    .' — '.Carbon::parse($historial->fecha_cambio)->format('d/m/Y H:i');
            }

            $eventos->push($this->evento(
                $historial->fecha_cambio,
                'estado',
                $this->faseFromEstado($estadoNombre),
                'Estado cambiado a: '.ucfirst($estadoNombre),
                $descripcion,
                $nombreHist ?: null,
                'exchange-alt',
                'info'
            ));
        }

        foreach ($lote->loteInsumos as $insumo) {
            $nombreInsumo = mb_strtolower(trim($insumo->insumo->nombre ?? ''));
            $faseInsumo = str_contains($nombreInsumo, 'fumig') || str_contains($nombreInsumo, 'fitosanit')
                ? 'fumigacion'
                : 'regado';
            $eventos->push($this->evento(
                $insumo->fechauo,
                'insumo',
                $faseInsumo,
                'Aplicación: '.($insumo->insumo->nombre ?? 'Insumo'),
                'Cantidad: '.$insumo->cantidadusada.' — '.($insumo->observaciones ?? ''),
                $insumo->usuario->nombre ?? null,
                'flask',
                'warning'
            ));
        }

        foreach ($lote->actividades as $actividad) {
            $tipoNombre = strtolower(trim($actividad->tipoActividad->nombre ?? ''));
            $faseAct = match (true) {
                str_contains($tipoNombre, 'siembra') => 'siembra',
                str_contains($tipoNombre, 'cosecha') => 'cosecha',
                str_contains($tipoNombre, 'riego') || str_contains($tipoNombre, 'regad') => 'regado',
                str_contains($tipoNombre, 'fumig') || str_contains($tipoNombre, 'plaga') => 'fumigacion',
                str_contains($tipoNombre, 'labranza') => 'preparacion',
                default => 'regado',
            };
            $eventos->push($this->evento(
                $actividad->fechainicio,
                'actividad',
                $faseAct,
                $actividad->tipoActividad->nombre ?? 'Actividad',
                $actividad->descripcion ?? 'Sin descripción',
                $actividad->usuario->nombre ?? null,
                'tasks',
                'primary',
                $actividad->fechafin !== null
            ));
        }

        foreach ($lote->producciones as $produccion) {
            $eventos->push($this->evento(
                $produccion->fechacosecha,
                'cosecha',
                'cosecha',
                'Cosecha registrada',
                'Cantidad: '.number_format((float) $produccion->cantidad, 2).' '
                    .($produccion->unidadMedida->abreviatura ?? 'kg')
                    .' — Destino: '.($produccion->destino->nombre ?? 'N/D'),
                null,
                'tractor',
                'success'
            ));

            foreach ($produccion->almacenamientos as $alm) {
                $eventos->push($this->evento(
                    $alm->fechaentrada ?? $produccion->fechacosecha,
                    'almacenamiento',
                    'envio_almacen',
                    'Ingreso a almacén',
                    ($alm->almacen->nombre ?? 'Almacén').' — '
                        .number_format((float) $alm->cantidad, 2).' '
                        .($alm->unidadMedida->abreviatura ?? 'kg'),
                    null,
                    'warehouse',
                    'secondary'
                ));
            }

            foreach ($produccion->ventas as $venta) {
                $eventos->push($this->evento(
                    $venta->fechaventa ?? $produccion->fechacosecha,
                    'venta',
                    'envio_almacen',
                    'Venta registrada',
                    ($venta->cliente ?? 'Cliente').' — '
                        .number_format((float) ($venta->cantidad ?? 0), 2).' u. × Bs. '
                        .number_format((float) ($venta->preciounitario ?? 0), 2),
                    null,
                    'shopping-cart',
                    'info'
                ));
            }
        }

        foreach ($lote->certificaciones as $cert) {
            $eventos->push($this->evento(
                $cert->fecha_certificacion,
                'certificacion',
                'envio_almacen',
                'Certificación de lote',
                $cert->codigo_certificado
                    ? 'Código: '.$cert->codigo_certificado
                    : ($cert->observaciones ?? 'Lote certificado'),
                $cert->usuario->nombre ?? null,
                'certificate',
                'success'
            ));
        }

        return $eventos
            ->filter(fn ($e) => $e['fecha'] !== null)
            ->sortByDesc(fn ($e) => Carbon::parse($e['fecha'])->timestamp)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardGlobal(Request $request): array
    {
        $filtros = $this->parseFiltros($request);

        $query = Lote::query()
            ->with([
                'cultivo',
                'estadoTipo',
                'usuario',
                'producciones.ventas',
                'producciones.almacenamientos',
                'certificaciones',
            ]);

        if ($filtros['cultivoid']) {
            $query->where('cultivoid', $filtros['cultivoid']);
        }
        if ($filtros['estadolotetipoid']) {
            $query->where('estadolotetipoid', $filtros['estadolotetipoid']);
        }
        if ($filtros['usuarioid']) {
            $query->where('usuarioid', $filtros['usuarioid']);
        }
        if ($filtros['q']) {
            $q = '%'.$filtros['q'].'%';
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', $q)
                    ->orWhere('codigo_trazabilidad', 'like', $q)
                    ->orWhere('ubicacion', 'like', $q);
            });
        }

        $lotes = $query->orderByDesc('fechamodificacion')->orderBy('nombre')->get();

        $filas = collect();
        $todosEventos = collect();
        $porFase = array_fill_keys(array_keys(self::FASES), 0);

        foreach ($lotes as $lote) {
            $faseActual = $this->resolverFaseActual($lote);
            if ($filtros['fase'] && $filtros['fase'] !== $faseActual) {
                continue;
            }

            $eventos = $this->buildEventos($lote);
            $eventosFiltrados = $this->filtrarEventos($eventos, $filtros);
            $ultimo = $eventosFiltrados->first();

            $porFase[$faseActual] = ($porFase[$faseActual] ?? 0) + 1;
            $todosEventos = $todosEventos->merge($eventosFiltrados);

            $progreso = $this->progresoFase($faseActual);
            $pendiente = $this->pasosHaciaCompleto($lote, $faseActual);

            $filas->push([
                'lote' => $lote,
                'fase_actual' => $faseActual,
                'fase_label' => self::FASES[$faseActual]['label'],
                'fase_color' => self::FASES[$faseActual]['color'],
                'progreso' => $progreso,
                'pendiente' => $pendiente,
                'total_eventos' => $eventos->count(),
                'kg_producidos' => $lote->producciones->sum('cantidad'),
                'ultimo_evento' => $ultimo,
            ]);
        }

        $porTipo = $todosEventos->groupBy('tipo')->map->count();

        return [
            'filtros' => $filtros,
            'fases' => self::FASES,
            'stats' => [
                'total_lotes' => $filas->count(),
                'total_eventos' => $todosEventos->count(),
                'kg_total' => round($filas->sum('kg_producidos'), 2),
                'lotes_en_cultivo' => $filas->whereIn('fase_actual', ['siembra', 'en_crecimiento'])->count(),
                'lotes_cosechados' => $filas->whereIn('fase_actual', ['cosecha', 'envio_almacen'])->count(),
            ],
            'chart_por_fase' => [
                'labels' => collect($porFase)->map(fn ($c, $k) => self::FASES[$k]['label'])->values()->all(),
                'data' => array_values($porFase),
                'colors' => collect($porFase)->keys()->map(fn ($k) => self::FASES[$k]['color'])->values()->all(),
            ],
            'chart_por_tipo' => [
                'labels' => $porTipo->keys()->map(fn ($t) => ucfirst($t))->values()->all(),
                'data' => $porTipo->values()->all(),
            ],
            'chart_linea' => $this->chartLineaMensual($todosEventos),
            'filas' => $filas,
            'cultivos' => Cultivo::orderBy('nombre')->get(['cultivoid', 'nombre']),
            'estados' => EstadoLoteTipo::orderBy('nombre')->get(['estadolotetipoid', 'nombre']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardLote(Lote $lote, Request $request): array
    {
        $base = $this->buildLoteDetalleBase($lote);
        $filtros = $this->parseFiltros($request);
        $eventos = $this->buildEventos($lote);
        $eventosFiltrados = $this->filtrarEventos($eventos, $filtros);
        $faseActual = $this->resolverFaseActual($lote);

        $porFase = $eventos->groupBy('fase')->map->count();
        $porTipo = $eventosFiltrados->groupBy('tipo')->map->count();

        $fasesPipeline = collect(self::FASES)->map(function ($meta, $key) use ($faseActual, $porFase, $lote) {
            $ordenActual = self::FASES[$faseActual]['orden'] ?? 1;
            $ordenSiguiente = $ordenActual + 1;
            $completo = $this->trazabilidadCompleta($lote);

            $estado = match (true) {
                $completo => 'done',
                $meta['orden'] < $ordenActual => 'done',
                $key === $faseActual => 'active',
                $meta['orden'] === $ordenSiguiente => 'next',
                default => 'pending',
            };

            $url = null;
            if ($estado === 'next' || ($estado === 'active' && ! $completo)) {
                $url = $this->urlAccionFase($lote, $key);
            }

            $eventosCount = match ($key) {
                'en_crecimiento' => ($porFase->get('regado', 0) + $porFase->get('fumigacion', 0)),
                default => $porFase->get($key, 0),
            };

            return [
                'key' => $key,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'icon' => $meta['icon'],
                'eventos' => $eventosCount,
                'estado' => $estado,
                'url' => $url,
            ];
        })->values();

        $siguienteFase = $this->siguienteFase($faseActual);
        $urlSiguienteFase = $siguienteFase ? $this->urlAccionFase($lote, $siguienteFase) : null;

        return array_merge($base, [
            'filtros' => $filtros,
            'fases' => self::FASES,
            'fases_evento' => $this->fasesMetaEvento(),
            'fase_actual' => $faseActual,
            'fase_actual_label' => self::FASES[$faseActual]['label'],
            'progreso' => $this->progresoFase($faseActual),
            'pendiente' => $this->pasosHaciaCompleto($lote, $faseActual),
            'trazabilidad' => $eventosFiltrados,
            'trazabilidad_json' => $eventosFiltrados->values()->all(),
            'fases_pipeline' => $fasesPipeline,
            'siguiente_fase' => $siguienteFase,
            'siguiente_fase_label' => $siguienteFase ? (self::FASES[$siguienteFase]['label'] ?? $siguienteFase) : null,
            'url_siguiente_fase' => $urlSiguienteFase,
            'chart_por_fase' => [
                'labels' => $porFase->keys()->map(fn ($k) => self::FASES[$k]['label'] ?? $k)->values()->all(),
                'data' => $porFase->values()->all(),
                'colors' => $porFase->keys()->map(fn ($k) => self::FASES[$k]['color'] ?? '#999')->values()->all(),
            ],
            'chart_por_tipo' => [
                'labels' => $porTipo->keys()->map(fn ($t) => ucfirst($t))->values()->all(),
                'data' => $porTipo->values()->all(),
            ],
            'chart_linea' => $this->chartLineaMensual($eventosFiltrados),
        ]);
    }

    /**
     * @return array{lote: Lote, estadisticas: array<string, mixed>, estadoClass: string}
     */
    public function buildLoteDetalleBase(Lote $lote): array
    {
        $lote->load(['usuario', 'cultivo', 'estadoTipo']);

        $estadoColors = [
            'disponible' => 'bg-secondary',
            'en preparación' => 'bg-info',
            'sembrado' => 'bg-primary',
            'en producción' => 'bg-success',
            'cosechado' => 'bg-warning text-dark',
            'en descanso' => 'bg-dark',
        ];
        $estadoNombre = strtolower($lote->estadoTipo->nombre ?? 'disponible');
        $estadoClass = $estadoColors[$estadoNombre] ?? 'bg-secondary';

        $diasDesdeSiembra = null;
        if ($lote->fechasiembra) {
            $fechaSiembra = Carbon::parse($lote->fechasiembra);
            $diasDesdeSiembra = $fechaSiembra->isFuture() ? 0 : (int) $fechaSiembra->diffInDays(now());
        }

        $lote->loadCount(['loteInsumos', 'actividades', 'producciones']);
        $lote->load(['actividades', 'producciones']);

        $estadisticas = [
            'total_insumos' => $lote->lote_insumos_count,
            'total_actividades' => $lote->actividades_count,
            'actividades_completadas' => $lote->actividades->whereNotNull('fechafin')->count(),
            'actividades_pendientes' => $lote->actividades->whereNull('fechafin')->count(),
            'total_aplicaciones' => $lote->lote_insumos_count,
            'total_cosechas' => $lote->producciones_count,
            'produccion_total' => $lote->producciones->sum('cantidad'),
            'dias_desde_siembra' => $diasDesdeSiembra,
        ];

        return compact('lote', 'estadisticas', 'estadoClass');
    }

    /**
     * @return array<string, mixed>
     */
    private function parseFiltros(Request $request): array
    {
        return [
            'fase' => $request->input('fase'),
            'tipo' => $request->input('tipo'),
            'cultivoid' => $request->integer('cultivoid') ?: null,
            'estadolotetipoid' => $request->integer('estadolotetipoid') ?: null,
            'usuarioid' => $request->integer('usuarioid') ?: null,
            'q' => trim((string) $request->input('q', '')),
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventos
     * @return Collection<int, array<string, mixed>>
     */
    private function filtrarEventos(Collection $eventos, array $filtros): Collection
    {
        return $eventos->filter(function ($e) use ($filtros) {
            if ($filtros['fase']) {
                $faseEvento = $e['fase'] ?? '';
                $match = $faseEvento === $filtros['fase']
                    || ($filtros['fase'] === 'en_crecimiento' && in_array($faseEvento, ['regado', 'fumigacion'], true));
                if (! $match) {
                    return false;
                }
            }
            if ($filtros['tipo'] && ($e['tipo'] ?? '') !== $filtros['tipo']) {
                return false;
            }
            $fecha = $e['fecha'] ? Carbon::parse($e['fecha']) : null;
            if ($filtros['desde'] && $fecha && $fecha->lt(Carbon::parse($filtros['desde'])->startOfDay())) {
                return false;
            }
            if ($filtros['hasta'] && $fecha && $fecha->gt(Carbon::parse($filtros['hasta'])->endOfDay())) {
                return false;
            }

            return true;
        })->values();
    }

    private function evento(
        mixed $fecha,
        string $tipo,
        string $fase,
        string $titulo,
        string $descripcion,
        ?string $usuario,
        string $icono,
        string $color,
        ?bool $completada = null
    ): array {
        $row = [
            'fecha' => $fecha,
            'fecha_iso' => $fecha ? Carbon::parse($fecha)->toIso8601String() : null,
            'fecha_fmt' => $fecha ? Carbon::parse($fecha)->format('d/m/Y H:i') : '—',
            'tipo' => $tipo,
            'fase' => $fase,
            'fase_label' => self::FASES_EVENTO_EXTRA[$fase]['label']
                ?? self::FASES[$fase]['label']
                ?? ucfirst($fase),
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'usuario' => $usuario,
            'icono' => $icono,
            'color' => $color,
        ];
        if ($completada !== null) {
            $row['completada'] = $completada;
        }

        return $row;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eventos
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private function chartLineaMensual(Collection $eventos): array
    {
        $porMes = $eventos
            ->filter(fn ($e) => $e['fecha'])
            ->groupBy(fn ($e) => Carbon::parse($e['fecha'])->format('Y-m'))
            ->map->count()
            ->sortKeys();

        if ($porMes->isEmpty()) {
            $mes = now()->format('Y-m');

            return [
                'labels' => [Carbon::parse($mes.'-01')->translatedFormat('M Y')],
                'data' => [0],
            ];
        }

        return [
            'labels' => $porMes->keys()->map(fn ($ym) => Carbon::parse($ym.'-01')->translatedFormat('M Y'))->values()->all(),
            'data' => $porMes->values()->all(),
        ];
    }
}
