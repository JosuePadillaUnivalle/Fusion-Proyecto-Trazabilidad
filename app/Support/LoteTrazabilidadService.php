<?php

namespace App\Support;

use App\Models\Actividad;
use App\Models\Almacen;
use App\Models\CertificacionLote;
use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\Usuario;
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
        'certificacion' => ['label' => 'Certificación', 'orden' => 5, 'color' => '#6f42c1', 'icon' => 'certificate'],
        'envio_almacen' => ['label' => 'Envío al almacén', 'orden' => 6, 'color' => '#6f42c1', 'icon' => 'warehouse'],
    ];

    /** Fases que ocurren una sola vez por lote (muestran check, no contador). */
    private const FASES_UNICAS = ['preparacion', 'siembra', 'cosecha', 'certificacion', 'envio_almacen'];

    /** Fases internas de eventos (historial) que se agrupan en «En crecimiento» en el pipeline. */
    private const FASES_EVENTO_EXTRA = [
        'regado' => ['label' => 'Regado', 'color' => '#28a745', 'icon' => 'tint'],
        'fumigacion' => ['label' => 'Fumigación', 'color' => '#fd7e14', 'icon' => 'spray-can'],
        'fertilizacion' => ['label' => 'Fertilización', 'color' => '#20c997', 'icon' => 'flask'],
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
        'certificado' => 'envio_almacen',
        'no conforme' => 'certificacion',
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
            $cert = app(CertificacionCampoService::class);
            if ($cert->estaCertificado($lote)) {
                return 'envio_almacen';
            }

            return 'certificacion';
        }
        if ($this->milestoneFumigacion($lote) || $this->milestoneRegado($lote)) {
            return 'en_crecimiento';
        }
        if ($this->milestoneSiembra($lote)) {
            return 'en_crecimiento';
        }
        if ($this->milestonePreparacion($lote)) {
            return 'siembra';
        }

        return 'preparacion';
    }

    /**
     * Valida si el tipo de actividad puede registrarse en el lote (fase actual + duplicados).
     */
    public function mensajeActividadNoPermitida(Lote $lote, ?string $tipoNombre): ?string
    {
        if ($tipoNombre === null || trim($tipoNombre) === '') {
            return null;
        }

        $lote->loadMissing(['actividades.tipoActividad', 'estadoTipo']);
        $nombre = mb_strtolower(trim($tipoNombre));
        $faseActual = $this->resolverFaseActual($lote);
        $faseTipo = $this->faseDeTipoActividad($nombre);

        if ($faseTipo !== null && ! $this->tipoActividadPermitidoEnFase($tipoNombre, $faseActual)) {
            $labelActual = self::FASES[$faseActual]['label'] ?? ucfirst($faseActual);

            return "El lote está en fase «{$labelActual}». No puede registrar «{$tipoNombre}» porque pertenece a una fase anterior o distinta.";
        }

        // Riego, fertilización y control de plagas pueden repetirse en «en crecimiento».
        if (str_contains($nombre, 'siembra')) {
            if ($this->actividadExistenteConKeywords($lote, ['siembra'], true)) {
                return 'Este lote ya tiene una actividad de siembra (pendiente o completada). Solo puede realizarse una vez.';
            }
            if ($this->milestoneSiembra($lote)) {
                return 'Este lote ya superó la fase de siembra.';
            }
        }

        if ((str_contains($nombre, 'labranza') || str_contains($nombre, 'prepar'))
            && $this->milestonePreparacion($lote)) {
            return 'La preparación del lote ya fue registrada. Solo puede realizarse una vez.';
        }

        return null;
    }

    /** @deprecated Use mensajeActividadNoPermitida() */
    public function mensajeActividadDuplicada(Lote $lote, ?string $tipoNombre): ?string
    {
        return $this->mensajeActividadNoPermitida($lote, $tipoNombre);
    }

    public function tipoActividadPermitidoEnFase(?string $tipoNombre, string $faseActual): bool
    {
        $faseTipo = $this->faseDeTipoActividad(mb_strtolower(trim($tipoNombre ?? '')));

        if ($faseTipo === null) {
            return true;
        }

        if ($faseTipo === $faseActual) {
            return true;
        }

        return $this->siguienteFase($faseActual) === $faseTipo;
    }

    private function faseDeTipoActividad(string $nombre): ?string
    {
        if ($nombre === '') {
            return null;
        }

        return match (true) {
            str_contains($nombre, 'labranza') || str_contains($nombre, 'prepar') => 'preparacion',
            str_contains($nombre, 'siembra') => 'siembra',
            str_contains($nombre, 'riego') || str_contains($nombre, 'regad'),
            str_contains($nombre, 'fumig') || str_contains($nombre, 'plaga') || str_contains($nombre, 'fitosanit'),
            str_contains($nombre, 'fertiliz') => 'en_crecimiento',
            str_contains($nombre, 'cosecha') => 'cosecha',
            default => null,
        };
    }

    private function milestonePreparacion(Lote $lote): bool
    {
        return $this->actividadCompletadaConKeywords($lote, ['labranza', 'prepar']);
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

    private function returnUrlTrazabilidad(Lote $lote): string
    {
        return route('lotes.trazabilidad', $lote, absolute: false);
    }

    /**
     * URL para registrar actividades de la fase «En crecimiento» (sin saltar a cosecha).
     */
    public function urlAsignarActividadEnCrecimiento(Lote $lote): string
    {
        $return = $this->returnUrlTrazabilidad($lote);
        $params = [
            'loteid' => $lote->loteid,
            'return' => $return,
        ];

        $tipoSugerido = $this->siguienteTipoActividadCrecimiento($lote);
        if ($tipoSugerido !== null) {
            $params['tipo'] = $tipoSugerido;
        }

        return route('actividades.create', $params);
    }

    /**
     * URL directa para registrar la fase indicada (usada en el botón «siguiente» del pipeline).
     */
    public function urlAccionFase(Lote $lote, string $faseKey): ?string
    {
        $return = $this->returnUrlTrazabilidad($lote);

        return match ($faseKey) {
            'siembra' => null,
            'en_crecimiento' => $this->urlAsignarActividadEnCrecimiento($lote),
            'cosecha' => route('producciones.create', [
                'loteid' => $lote->loteid,
                'return' => $return,
            ]),
            'certificacion' => null,
            'envio_almacen' => null,
            default => null,
        };
    }

    public function produccionPendienteAlmacen(Lote $lote): ?Produccion
    {
        $lote->loadMissing(['producciones.almacenamientos', 'producciones.unidadMedida', 'producciones.almacenDestino.tipoAlmacen']);

        return $lote->producciones
            ->sortByDesc(fn (Produccion $p) => $p->fechacosecha ?? $p->produccionid)
            ->first(fn (Produccion $p) => $p->almacenamientos->isEmpty());
    }

    public function puedeCertificarCampo(Lote $lote): bool
    {
        $lote->loadMissing('producciones');
        $cert = app(CertificacionCampoService::class);

        return $this->resolverFaseActual($lote) === 'certificacion'
            && ! $cert->fueEvaluado($lote)
            && $lote->producciones->isNotEmpty();
    }

    public function puedeEnviarAlmacenCampo(Lote $lote): bool
    {
        $cert = app(CertificacionCampoService::class);

        return $cert->estaCertificado($lote)
            && $this->produccionPendienteAlmacen($lote) !== null;
    }

    public function siguienteTipoActividadCrecimiento(Lote $lote): ?string
    {
        $lote->loadMissing('actividades.tipoActividad');

        if (! $this->milestoneRegado($lote)) {
            return 'Riego';
        }
        if (! $this->milestoneFumigacion($lote)) {
            return 'Control de plagas';
        }
        if (! $this->milestoneFertilizacion($lote)) {
            return 'Fertilización';
        }

        return null;
    }

    public function actividadesCrecimientoCompletas(Lote $lote): bool
    {
        $lote->loadMissing('actividades.tipoActividad');

        return $this->milestoneRegado($lote)
            && $this->milestoneFumigacion($lote)
            && $this->milestoneFertilizacion($lote);
    }

    /** @return Collection<int, Actividad> */
    public function actividadesPendientes(Lote $lote, bool $soloFaseActual = true): Collection
    {
        $lote->loadMissing('actividades.tipoActividad');
        $faseActual = $soloFaseActual ? $this->resolverFaseActual($lote) : null;

        return $lote->actividades
            ->whereNull('fechafin')
            ->when($faseActual !== null, fn (Collection $items) => $items->filter(
                fn (Actividad $actividad) => $this->tipoActividadPermitidoEnFase(
                    $actividad->tipoActividad->nombre ?? null,
                    $faseActual
                )
            ))
            ->sortByDesc('fechainicio')
            ->values();
    }

    public function esActividadCrecimientoEsencial(Actividad $actividad): bool
    {
        $nombre = mb_strtolower(trim($actividad->tipoActividad->nombre ?? ''));

        return str_contains($nombre, 'riego')
            || str_contains($nombre, 'regad')
            || str_contains($nombre, 'fumig')
            || str_contains($nombre, 'plaga')
            || str_contains($nombre, 'fitosanit')
            || str_contains($nombre, 'fertiliz');
    }

    /** @return Collection<int, Actividad> */
    public function actividadesPendientesEsencialesCrecimiento(Lote $lote): Collection
    {
        $lote->loadMissing('actividades.tipoActividad');

        return $lote->actividades
            ->whereNull('fechafin')
            ->filter(fn (Actividad $actividad) => $this->esActividadCrecimientoEsencial($actividad))
            ->sortByDesc('fechainicio')
            ->values();
    }

    public function puedeIrACosecha(Lote $lote): bool
    {
        return $this->puedeRegistrarCosecha($lote)
            && $this->actividadesPendientesEsencialesCrecimiento($lote)->isEmpty();
    }

    /**
     * @return array{
     *     meta_label: ?string,
     *     meta_progreso: ?int,
     *     hitos: array<int, array{label: string, ok: bool}>,
     *     actividades_abiertas: array<int, array{titulo: string, responsable: ?string}>
     * }
     */
    public function panelPendientesLegible(Lote $lote, string $faseActual): array
    {
        $lote->loadMissing(['actividades.tipoActividad', 'actividades.usuario']);
        $pend = $this->pasosHaciaCompleto($lote, $faseActual);
        $siguienteKey = $pend['siguiente_fase'] ?? null;

        $hitos = [];
        if ($faseActual === 'en_crecimiento') {
            $hitos = [
                ['label' => 'Riego completado', 'ok' => $this->milestoneRegado($lote)],
                ['label' => 'Control de plagas completado', 'ok' => $this->milestoneFumigacion($lote)],
                ['label' => 'Fertilización completada', 'ok' => $this->milestoneFertilizacion($lote)],
            ];
        }

        $actividadesAbiertas = $this->actividadesPendientes($lote)
            ->map(fn (Actividad $a) => [
                'actividadid' => (int) $a->actividadid,
                'titulo' => $a->descripcion ?: ($a->tipoActividad->nombre ?? 'Actividad'),
                'responsable' => trim(($a->usuario->nombre ?? '').' '.($a->usuario->apellido ?? '')) ?: null,
                'es_siembra' => str_contains(mb_strtolower(trim($a->tipoActividad->nombre ?? '')), 'siembra'),
            ])
            ->values()
            ->all();

        return [
            'meta_label' => $pend['siguiente_label'] ?? null,
            'meta_progreso' => $pend['progreso_siguiente'] ?? null,
            'fases_despues' => array_slice($pend['fases_restantes'] ?? [], 1),
            'acciones' => $pend['acciones'] ?? [],
            'hitos' => $hitos,
            'actividades_abiertas' => $actividadesAbiertas,
            'completo' => (bool) ($pend['completo'] ?? false),
            'resumen_corto' => $pend['resumen'] ?? '',
        ];
    }

    /** @return list<string> */
    public function actividadesCrecimientoPendientes(Lote $lote): array
    {
        $lote->loadMissing('actividades.tipoActividad');
        $pendientes = [];

        if (! $this->milestoneRegado($lote)) {
            $pendientes[] = 'riego';
        }
        if (! $this->milestoneFumigacion($lote)) {
            $pendientes[] = 'control de plagas';
        }
        if (! $this->milestoneFertilizacion($lote)) {
            $pendientes[] = 'fertilización';
        }

        return $pendientes;
    }

    public function puedeRegistrarCosecha(Lote $lote): bool
    {
        $lote->loadMissing(['estadoTipo', 'actividades.tipoActividad']);
        $slug = EstadoLoteCatalogo::slugFromNombre($lote->estadoTipo->nombre ?? '');

        if ($slug === 'listo_para_cosecha') {
            return true;
        }

        return $slug === 'en_crecimiento' && $this->actividadesCrecimientoCompletas($lote);
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

    public function siembraCompletada(Lote $lote): bool
    {
        $lote->loadMissing(['estadoTipo', 'actividades.tipoActividad']);

        return $this->milestoneSiembra($lote);
    }

    public function actividadSiembraPendiente(Lote $lote): ?Actividad
    {
        $lote->loadMissing('actividades.tipoActividad');

        return $lote->actividades
            ->whereNull('fechafin')
            ->first(function (Actividad $actividad) {
                $nombre = mb_strtolower(trim($actividad->tipoActividad->nombre ?? ''));

                return str_contains($nombre, 'siembra');
            });
    }

    private function milestoneRegado(Lote $lote): bool
    {
        return $this->actividadCompletadaConKeywords($lote, ['riego', 'regad']);
    }

    private function milestoneFumigacion(Lote $lote): bool
    {
        return $this->actividadCompletadaConKeywords($lote, ['fumig', 'plaga', 'fitosanit']);
    }

    private function milestoneFertilizacion(Lote $lote): bool
    {
        return $this->actividadCompletadaConKeywords($lote, ['fertiliz']);
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
        return $this->actividadExistenteConKeywords($lote, $keywords, false);
    }

    private function actividadExistenteConKeywords(Lote $lote, array $keywords, bool $incluirPendientes): bool
    {
        return $lote->actividades
            ->when(! $incluirPendientes, fn (Collection $items) => $items->whereNotNull('fechafin'))
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
            $prod = $this->produccionPendienteAlmacen($lote);
            $prod?->loadMissing('almacenDestino.tipoAlmacen');
            $acciones = $this->accionesSugeridas($lote, $faseActual, null);

            if ($prod?->almacenDestino) {
                array_unshift(
                    $acciones,
                    'Destino previsto: '.$prod->almacenDestino->nombre.' (elegido en la cosecha)'
                );
            }

            return [
                'completo' => false,
                'siguiente_fase' => 'envio_almacen',
                'siguiente_label' => self::FASES['envio_almacen']['label'],
                'progreso_siguiente' => 100,
                'fases_restantes' => [],
                'acciones' => $acciones,
                'resumen' => 'Confirme el envío de la cosecha al almacén agrícola',
            ];
        }

        $certService = app(CertificacionCampoService::class);
        if ($faseActual === 'certificacion' && ! $certService->fueEvaluado($lote)) {
            $acciones = $this->accionesSugeridas($lote, $faseActual, null);

            return [
                'completo' => false,
                'siguiente_fase' => 'certificacion',
                'siguiente_label' => self::FASES['certificacion']['label'],
                'progreso_siguiente' => $this->progresoFase('certificacion'),
                'fases_restantes' => [self::FASES['envio_almacen']['label']],
                'acciones' => $acciones,
                'resumen' => 'Evalúe el lote como Certificado o No conforme',
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
                $pasos[] = 'Asignar quién va a sembrar el lote';
                $pasos[] = 'Programar labranza o preparación del suelo si aplica';
                break;

            case 'siembra':
                $siembraPendiente = $this->actividadSiembraPendiente($lote);
                if ($siembraPendiente) {
                    $pasos[] = 'Completar la siembra asignada (con foto de evidencia)';
                } else {
                    $pasos[] = 'Asignar quién va a sembrar el lote';
                }
                break;

            case 'en_crecimiento':
                if (! $this->milestoneRegado($lote)) {
                    $pasos[] = 'Asignar y completar el riego del lote';
                }
                if (! $this->milestoneFumigacion($lote)) {
                    $pasos[] = 'Asignar y completar el control de plagas';
                }
                if (! $this->milestoneFertilizacion($lote)) {
                    $pasos[] = 'Asignar y completar la fertilización';
                }
                foreach ($this->actividadesPendientes($lote) as $actividad) {
                    $titulo = $actividad->descripcion ?: ($actividad->tipoActividad->nombre ?? 'Actividad');
                    $pasos[] = 'Completar actividad pendiente: «'.$titulo.'»';
                }
                break;

            case 'cosecha':
                if ($lote->producciones->isEmpty()) {
                    $pasos[] = 'Registrar la cosecha (kg, fecha y evidencia)';
                } else {
                    $pasos[] = 'Cosecha registrada — continúe con la certificación del lote';
                }
                break;

            case 'certificacion':
                $cert = app(CertificacionCampoService::class);
                if ($cert->esNoConforme($lote)) {
                    $pasos[] = 'Lote No conforme: no puede enviarse al almacén';
                    if ($cert->ultima($lote)?->observaciones) {
                        $pasos[] = 'Motivo: '.$cert->ultima($lote)->observaciones;
                    }
                } else {
                    $pasos[] = 'Evaluar el lote como Certificado o No conforme (formulario de abajo)';
                    $pasos[] = 'Si hay daños, plagas o calidad deficiente, marque No conforme';
                }
                break;

            case 'envio_almacen':
                $prod = $this->produccionPendienteAlmacen($lote);
                if ($prod?->almacenDestino) {
                    $pasos[] = 'Confirmar envío a «'.$prod->almacenDestino->nombre.'» (elegido en cosecha)';
                } else {
                    $pasos[] = 'Seleccionar el almacén agrícola de destino';
                }
                if ($lote->producciones->flatMap->almacenamientos->isEmpty()) {
                    $pasos[] = 'Registrar el ingreso con el botón «Enviar al almacén»';
                }
                break;
        }

        if ($siguienteFase && isset(self::FASES[$siguienteFase]) && $faseActual !== 'en_crecimiento') {
            array_unshift(
                $pasos,
                'Alcanzar la fase «'.self::FASES[$siguienteFase]['label'].'» ('.$this->progresoFase($siguienteFase).' %)'
            );
        }

        return array_values(array_unique(array_slice($pasos, 0, 6)));
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
            $descripcion = $this->formatearObservacionHistorial(
                $historial->observaciones,
                $nombreHist ?: null,
                $rolHist ?: null,
            );

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

            // La siembra tiene pantalla y flujo propios; no aparece como «actividad» en el historial.
            if (str_contains($tipoNombre, 'siembra')) {
                continue;
            }

            $fasePipeline = $this->resolverFasePipelineActividad($actividad, $lote);
            $faseAct = match (true) {
                str_contains($tipoNombre, 'siembra') => 'siembra',
                str_contains($tipoNombre, 'cosecha') => 'cosecha',
                str_contains($tipoNombre, 'riego') || str_contains($tipoNombre, 'regad') => 'regado',
                str_contains($tipoNombre, 'fumig') || str_contains($tipoNombre, 'plaga') => 'fumigacion',
                str_contains($tipoNombre, 'fertiliz') => 'fertilizacion',
                str_contains($tipoNombre, 'labranza') => 'preparacion',
                default => 'regado',
            };
            $etapaLabel = self::FASES[$fasePipeline]['label'] ?? ucfirst($fasePipeline);
            $nombreTipo = $actividad->tipoActividad->nombre ?? 'Actividad';
            $descripcionAct = trim((string) ($actividad->descripcion ?? ''));
            $evidenciaAct = $actividad->fechafin !== null
                ? EvidenciaFoto::urlDesdePath($actividad->evidencia_foto_path ?? null)
                : null;
            $eventos->push($this->evento(
                $actividad->fechainicio,
                'actividad',
                $faseAct,
                $nombreTipo,
                $descripcionAct,
                $actividad->usuario->nombre ?? null,
                'tasks',
                'primary',
                $actividad->fechafin !== null,
                (int) $actividad->actividadid,
                $etapaLabel,
                $evidenciaAct,
            ));
        }

        foreach ($lote->actividades as $actividad) {
            $tipoNombre = strtolower(trim($actividad->tipoActividad->nombre ?? ''));
            if (! str_contains($tipoNombre, 'siembra') || $actividad->fechafin === null) {
                continue;
            }

            $descripcionSiembra = trim((string) ($actividad->descripcion ?? ''));
            $evidenciaSiembra = EvidenciaFoto::urlDesdePath($actividad->evidencia_foto_path ?? null);
            $eventos->push($this->evento(
                $actividad->fechafin ?? $actividad->fechainicio,
                'siembra',
                'siembra',
                'Siembra realizada',
                $descripcionSiembra,
                $actividad->usuario->nombre ?? null,
                'seedling',
                'info',
                true,
                null,
                self::FASES['siembra']['label'],
                $evidenciaSiembra,
            ));
        }

        foreach ($lote->producciones as $produccion) {
            $evidenciaCosecha = EvidenciaFoto::urlDesdeImagenUrl($produccion->imagenurl ?? null);
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
                'success',
                null,
                null,
                null,
                $evidenciaCosecha,
                (int) $produccion->produccionid,
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
            $titulo = $cert->esNoConforme() ? 'No conforme — lote de campo' : 'Certificación de lote';
            $detalle = $cert->codigo_certificado
                ? 'Código: '.$cert->codigo_certificado
                : ($cert->observaciones ?? 'Evaluación registrada');
            if ($cert->observaciones && $cert->esNoConforme()) {
                $detalle .= ' — '.$cert->observaciones;
            }

            $eventos->push($this->evento(
                $cert->fecha_certificacion,
                'certificacion',
                'certificacion',
                $titulo,
                $detalle,
                $cert->usuario->nombre ?? null,
                'certificate',
                $cert->esNoConforme() ? 'danger' : 'success'
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
        $porTipo = $eventos->groupBy('tipo')->map->count();
        $porTipoFiltrado = $eventosFiltrados->groupBy('tipo')->map->count();

        $fasesPipeline = collect(self::FASES)->map(function ($meta, $key) use ($faseActual, $porFase, $porTipo, $lote) {
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
                $candidata = $this->urlAccionFase($lote, $key);
                if ($key === 'cosecha' && $faseActual === 'en_crecimiento' && ! $this->puedeIrACosecha($lote)) {
                    $url = null;
                } elseif ($key === 'envio_almacen' && ! app(CertificacionCampoService::class)->estaCertificado($lote)) {
                    $url = null;
                } else {
                    $url = $candidata;
                }
            }

            $esFaseUnica = in_array($key, self::FASES_UNICAS, true);
            $eventosCount = match ($key) {
                'en_crecimiento' => ($porFase->get('regado', 0) + $porFase->get('fumigacion', 0) + $porFase->get('fertilizacion', 0)),
                'cosecha' => (int) $porTipo->get('cosecha', 0),
                'certificacion' => (int) $porTipo->get('certificacion', 0),
                'envio_almacen' => (int) $porTipo->get('almacenamiento', 0),
                default => (int) $porFase->get($key, 0),
            };

            if ($esFaseUnica && $key !== 'en_crecimiento') {
                $eventosCount = 0;
            }

            $certService = app(CertificacionCampoService::class);
            $completada = match ($key) {
                'preparacion', 'siembra' => $estado === 'done',
                'cosecha' => (int) $porTipo->get('cosecha', 0) > 0,
                'certificacion' => $certService->fueEvaluado($lote),
                'envio_almacen' => (int) $porTipo->get('almacenamiento', 0) > 0,
                default => false,
            };

            return [
                'key' => $key,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'icon' => $meta['icon'],
                'eventos' => $eventosCount,
                'fase_unica' => $esFaseUnica,
                'completada' => $completada,
                'mostrar_contador' => $key === 'en_crecimiento' && $eventosCount > 0,
                'estado' => $estado,
                'url' => $url,
            ];
        })->values();

        $siguienteFase = $this->siguienteFase($faseActual);
        $urlSiguienteFase = $siguienteFase ? $this->urlAccionFase($lote, $siguienteFase) : null;
        $puedeCertificarCampo = $this->puedeCertificarCampo($lote);
        $puedeEnviarAlmacen = $this->puedeEnviarAlmacenCampo($lote);
        $produccionPendienteAlmacen = $this->produccionPendienteAlmacen($lote);
        if ($puedeCertificarCampo || $puedeEnviarAlmacen) {
            $urlSiguienteFase = null;
        }
        $certCampo = app(CertificacionCampoService::class);
        $siembraPendiente = $this->actividadSiembraPendiente($lote);
        if ($siembraPendiente) {
            $siembraPendiente->loadMissing('usuario');
        }
        $user = $request->user();
        $puedeCompletarSiembraDirecta = $siguienteFase === 'siembra'
            && ! $this->siembraCompletada($lote)
            && $siembraPendiente === null
            && $user
            && (int) $lote->usuarioid === (int) $user->usuarioid;
        $urlAsignarActividad = $faseActual === 'en_crecimiento'
            ? $this->urlAsignarActividadEnCrecimiento($lote)
            : null;
        $siguienteActividadCrecimiento = $faseActual === 'en_crecimiento'
            ? $this->siguienteTipoActividadCrecimiento($lote)
            : null;

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
            'puede_certificar_campo' => $puedeCertificarCampo,
            'puede_enviar_almacen' => $puedeEnviarAlmacen,
            'produccion_pendiente_almacen' => $produccionPendienteAlmacen,
            'certificacion_campo' => $certCampo->ultima($lote),
            'puede_asignar_siembra' => $siguienteFase === 'siembra'
                && ! $this->siembraCompletada($lote)
                && $siembraPendiente === null
                && ! $puedeCompletarSiembraDirecta,
            'puede_completar_siembra_directa' => $puedeCompletarSiembraDirecta,
            'url_completar_siembra' => $puedeCompletarSiembraDirecta
                ? route('lotes.siembra.create', [
                    'lote' => $lote,
                    'completar' => 1,
                    'return' => $this->returnUrlTrazabilidad($lote),
                ])
                : null,
            'actividad_siembra_pendiente' => $siembraPendiente ? [
                'actividadid' => (int) $siembraPendiente->actividadid,
                'titulo' => $siembraPendiente->descripcion ?: ($siembraPendiente->tipoActividad->nombre ?? 'Siembra'),
                'responsable' => trim(($siembraPendiente->usuario->nombre ?? '').' '.($siembraPendiente->usuario->apellido ?? '')) ?: null,
            ] : null,
            'url_asignar_actividad' => $urlAsignarActividad,
            'siguiente_actividad_crecimiento' => $siguienteActividadCrecimiento,
            'puede_ir_a_cosecha' => $faseActual === 'en_crecimiento' && $siguienteFase === 'cosecha'
                ? $this->puedeIrACosecha($lote)
                : true,
            'actividades_pendientes_count' => $faseActual === 'en_crecimiento'
                ? $this->actividadesPendientesEsencialesCrecimiento($lote)->count()
                : $this->actividadesPendientes($lote)->count(),
            'panel_pendientes' => $this->panelPendientesLegible($lote, $faseActual),
            'chart_por_fase' => [
                'labels' => $porFase->keys()->map(fn ($k) => self::FASES[$k]['label'] ?? $k)->values()->all(),
                'data' => $porFase->values()->all(),
                'colors' => $porFase->keys()->map(fn ($k) => self::FASES[$k]['color'] ?? '#999')->values()->all(),
            ],
            'chart_por_tipo' => [
                'labels' => $porTipoFiltrado->keys()->map(fn ($t) => ucfirst($t))->values()->all(),
                'data' => $porTipoFiltrado->values()->all(),
            ],
            'chart_linea' => $this->chartLineaMensual($eventosFiltrados),
            'actividades_marcables_ids' => $this->idsActividadesMarcables($lote, $request->user()),
        ]);
    }

    /** @return list<int> */
    private function idsActividadesMarcables(Lote $lote, ?Usuario $user): array
    {
        if (! $user) {
            return [];
        }

        return Actividad::query()
            ->where('loteid', $lote->loteid)
            ->whereNull('fechafin')
            ->with('tipoActividad')
            ->get()
            ->filter(fn (Actividad $actividad) => ActividadPermisos::puedeMarcarCompletada($user, $actividad))
            ->map(fn (Actividad $actividad) => (int) $actividad->actividadid)
            ->values()
            ->all();
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
            $fecha = $e['fecha'] ? $this->parseFechaApp($e['fecha']) : null;
            if ($filtros['desde'] && $fecha && $fecha->lt($this->parseFechaApp($filtros['desde'])->startOfDay())) {
                return false;
            }
            if ($filtros['hasta'] && $fecha && $fecha->gt($this->parseFechaApp($filtros['hasta'])->endOfDay())) {
                return false;
            }

            return true;
        })->values();
    }

    private function formatearObservacionHistorial(?string $observaciones, ?string $usuario, ?string $rol): string
    {
        $lineas = [];

        if ($observaciones !== null && trim($observaciones) !== '' && trim($observaciones) !== 'Sin observaciones') {
            $texto = preg_replace('/^\[[^\]]+\]\s*/', '', trim($observaciones)) ?? trim($observaciones);
            $partes = preg_split('/\s*·\s*/', $texto) ?: [];

            foreach ($partes as $parte) {
                $parte = trim($parte);
                if ($parte === '' || strcasecmp($parte, 'historial') === 0) {
                    continue;
                }
                if (preg_match('/^Realizado por:/i', $parte)) {
                    continue;
                }
                if (preg_match('/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/', $parte)) {
                    continue;
                }
                if (preg_match('/\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}/', $parte)) {
                    continue;
                }
                $lineas[] = $parte;
            }
        }

        if ($usuario) {
            $lineas[] = 'Registrado por '.$usuario.($rol ? " ({$rol})" : '');
        }

        if ($lineas === []) {
            return 'Cambio de estado registrado en el lote.';
        }

        return implode("\n", array_values(array_unique($lineas)));
    }

    private function resolverFasePipelineActividad(Actividad $actividad, Lote $lote): string
    {
        $tipoNombre = strtolower(trim($actividad->tipoActividad->nombre ?? ''));

        if (str_contains($tipoNombre, 'labranza')) {
            return 'preparacion';
        }
        if (str_contains($tipoNombre, 'siembra')) {
            return 'siembra';
        }
        if (str_contains($tipoNombre, 'cosecha')) {
            return 'cosecha';
        }

        $siembraCompletada = $lote->actividades->first(function (Actividad $a) {
            $nombre = strtolower(trim($a->tipoActividad->nombre ?? ''));

            return str_contains($nombre, 'siembra') && $a->fechafin !== null;
        });

        if ($siembraCompletada === null) {
            return 'siembra';
        }

        $fechaAct = $actividad->fechainicio ? $this->parseFechaApp($actividad->fechainicio) : null;
        $fechaSiembraFin = $this->parseFechaApp($siembraCompletada->fechafin);

        if ($fechaAct && $fechaAct->lte($fechaSiembraFin)) {
            return 'siembra';
        }

        return 'en_crecimiento';
    }

    private function parseFechaApp(mixed $fecha): Carbon
    {
        return Carbon::parse($fecha)->timezone(config('app.timezone'));
    }

    private function formatearFechaApp(mixed $fecha): string
    {
        if ($fecha === null || $fecha === '') {
            return '—';
        }

        return $this->parseFechaApp($fecha)->format('d/m/Y H:i');
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
        ?bool $completada = null,
        ?int $actividadid = null,
        ?string $faseLabelOverride = null,
        ?string $evidenciaUrl = null,
        ?int $produccionid = null,
    ): array {
        $row = [
            'fecha' => $fecha,
            'fecha_iso' => $fecha ? $this->parseFechaApp($fecha)->toIso8601String() : null,
            'fecha_fmt' => $this->formatearFechaApp($fecha),
            'tipo' => $tipo,
            'fase' => $fase,
            'fase_label' => $faseLabelOverride
                ?? self::FASES_EVENTO_EXTRA[$fase]['label']
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
        if ($actividadid !== null) {
            $row['actividadid'] = $actividadid;
        }
        if ($evidenciaUrl !== null) {
            $row['evidencia_url'] = $evidenciaUrl;
        }
        if ($produccionid !== null) {
            $row['produccionid'] = $produccionid;
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

    /** @return array<string, mixed> */
    public function datosFormularioEnvioAlmacen(Lote $lote): array
    {
        AlmacenAmbito::asegurarAmbitosEnRegistros();
        $capacidadService = app(\App\Services\AlmacenCapacidadService::class);

        $almacenesTodos = AlmacenAmbito::scope(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])
                ->where('activo', true)
                ->where('capacidad', '>', 0),
            AlmacenAmbito::AGRICOLA
        )->get();

        $usoPorAlmacen = ProduccionAlmacenamiento::query()
            ->selectRaw('almacenid, COUNT(*) as total')
            ->groupBy('almacenid')
            ->pluck('total', 'almacenid');

        $ordenados = $almacenesTodos->count() <= 4
            ? $almacenesTodos->values()
            : $almacenesTodos->sortByDesc(fn (Almacen $a) => (int) ($usoPorAlmacen[$a->almacenid] ?? 0));

        $almacenesMasUsados = $almacenesTodos->count() <= 4
            ? $almacenesTodos->values()
            : $ordenados->take(4)->values();

        $almacenesMenosUsados = $almacenesTodos->count() <= 4
            ? $almacenesTodos->values()
            : $ordenados->reverse()->take(4)->values();

        $almacenes = $almacenesMasUsados;

        $resumenesCapacidad = $almacenesTodos
            ->mapWithKeys(fn (Almacen $a) => [$a->almacenid => $capacidadService->resumen($a)])
            ->all();

        $almacenesCatalogo = $almacenesTodos->map(function (Almacen $almacen) use ($capacidadService) {
            $resumen = $capacidadService->resumen($almacen);
            $resuelto = UbicacionGpsParser::resolverAlmacen(
                (int) $almacen->almacenid,
                $almacen->nombre,
                $almacen->ubicacion
            );

            return [
                'id' => $almacen->almacenid,
                'nombre' => $almacen->nombre,
                'tipo' => $almacen->tipoAlmacen->nombre ?? 'General',
                'ubicacion' => $almacen->ubicacion,
                'disponible' => $resumen['disponible_kg'],
                'capacidad' => $resumen['capacidad_kg'],
                'um' => 'kg',
                'tags' => strtolower($almacen->nombre.' '.($almacen->tipoAlmacen->nombre ?? '').' '.($almacen->ubicacion ?? '')),
                'lat' => $resuelto['lat'],
                'lng' => $resuelto['lng'],
                'direccion' => $resuelto['direccion'],
                'ubicacion_estimada' => $resuelto['estimada'],
            ];
        })->values()->all();

        $produccion = $this->produccionPendienteAlmacen($lote);
        $almacenDestinoId = old('almacenid') ?: $produccion?->almacendestinoid;
        $almacenDestino = $almacenDestinoId
            ? $almacenesTodos->firstWhere('almacenid', (int) $almacenDestinoId)
            : null;

        return [
            'almacenes' => $almacenes,
            'almacenesMasUsados' => $almacenesMasUsados,
            'almacenesMenosUsados' => $almacenesMenosUsados,
            'almacenesTodos' => $almacenesTodos,
            'almacenesCatalogo' => $almacenesCatalogo,
            'resumenesCapacidad' => $resumenesCapacidad,
            'produccion_pendiente_almacen' => $produccion,
            'almacen_destino_id' => $almacenDestinoId ? (int) $almacenDestinoId : null,
            'almacen_destino_preseleccionado' => $almacenDestino,
            'almacen_destino_resumen' => $almacenDestino
                ? ($resumenesCapacidad[$almacenDestino->almacenid] ?? null)
                : null,
        ];
    }
}
