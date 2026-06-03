<?php

namespace App\Support;

use App\Models\AlmacenajeLoteProduccion;
use App\Models\EvaluacionFinalLoteProduccion;
use App\Models\LoteProduccionPedido;
use App\Models\RegistroProcesoMaquinaPlanta;
use Illuminate\Support\Collection;

class LoteProduccionTrazabilidadService
{
    public function __construct(
        private readonly LoteProduccionTransformacionService $transformacion
    ) {}

    /** @var array<string, array{label: string, orden: int, color: string, icon: string}> */
    public const FASES = [
        'creacion' => ['label' => 'Lote creado', 'orden' => 1, 'color' => '#6c757d', 'icon' => 'clipboard-check'],
        'transformacion' => ['label' => 'Transformación', 'orden' => 2, 'color' => '#17a2b8', 'icon' => 'cogs'],
        'certificacion' => ['label' => 'Certificación', 'orden' => 3, 'color' => '#6f42c1', 'icon' => 'certificate'],
        'almacenaje' => ['label' => 'Almacenaje', 'orden' => 4, 'color' => '#fd7e14', 'icon' => 'warehouse'],
        'completado' => ['label' => 'Completado', 'orden' => 5, 'color' => '#28a745', 'icon' => 'flag-checkered'],
    ];

    /** Fases que ocurren una sola vez en el pipeline. */
    private const FASES_UNICAS = ['creacion', 'certificacion', 'almacenaje', 'completado'];

    public function resolverFaseActual(LoteProduccionPedido $lote): string
    {
        $lote->loadMissing(['evaluacionesFinales', 'almacenajes', 'materiasPrimas']);

        if ($lote->hora_fin || $lote->almacenajes->isNotEmpty()) {
            return 'completado';
        }

        if ($lote->evaluacionesFinales->isNotEmpty()) {
            return 'almacenaje';
        }

        if ($this->transformacionCompleta($lote)) {
            return 'certificacion';
        }

        if ($this->transformacion->transformacionIniciada($lote)) {
            return 'transformacion';
        }

        return 'creacion';
    }

    public function transformacionCompleta(LoteProduccionPedido $lote): bool
    {
        return $this->transformacion->transformacionCompleta($lote);
    }

    public function registrosDeLote(LoteProduccionPedido $lote): Collection
    {
        return $this->transformacion->registrosOrdenados($lote);
    }

    public function progresoFase(string $faseKey): int
    {
        $orden = self::FASES[$faseKey]['orden'] ?? 1;
        $total = count(self::FASES);

        return (int) round(($orden / $total) * 100);
    }

    /** Fase cuyo panel de trabajo debe mostrarse en la vista detalle. */
    public function panelFaseActivo(string $faseActual): string
    {
        return match ($faseActual) {
            'creacion', 'transformacion' => 'transformacion',
            'certificacion' => 'certificacion',
            'almacenaje' => 'almacenaje',
            'completado' => 'completado',
            default => 'transformacion',
        };
    }

    /**
     * @return list<string> Claves de fases ya cerradas (para historial).
     */
    public function fasesCompletadas(LoteProduccionPedido $lote, bool $transformacionCompleta): array
    {
        $lote->loadMissing(['evaluacionesFinales', 'almacenajes']);
        $hechas = ['creacion'];

        if ($transformacionCompleta) {
            $hechas[] = 'transformacion';
        }
        if ($lote->evaluacionesFinales->isNotEmpty()) {
            $hechas[] = 'certificacion';
        }
        if ($lote->almacenajes->isNotEmpty()) {
            $hechas[] = 'almacenaje';
            $hechas[] = 'completado';
        } elseif ($lote->hora_fin) {
            $hechas[] = 'completado';
        }

        return $hechas;
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

    public function trazabilidadCompleta(LoteProduccionPedido $lote): bool
    {
        return $lote->hora_fin !== null || $lote->almacenajes()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardLote(LoteProduccionPedido $lote): array
    {
        $lote->loadMissing([
            'pedido',
            'unidadMedida',
            'materiasPrimas.insumo',
            'evaluacionesFinales.inspector',
            'almacenajes',
        ]);

        $faseActual = $this->resolverFaseActual($lote);
        $registros = $this->registrosDeLote($lote);
        $etapasTransformacion = $this->transformacion->timeline($lote);
        $transformacionCompleta = $this->transformacionCompleta($lote);
        $totalEtapas = count($etapasTransformacion);

        $fasesPipeline = collect(self::FASES)->map(function ($meta, $key) use ($faseActual, $lote, $transformacionCompleta, $totalEtapas) {
            $ordenActual = self::FASES[$faseActual]['orden'] ?? 1;
            $ordenSiguiente = $ordenActual + 1;
            $completo = $this->trazabilidadCompleta($lote);

            $estado = match (true) {
                $completo => 'done',
                $meta['orden'] < $ordenActual => 'done',
                $key === 'transformacion' && $transformacionCompleta && $ordenActual >= self::FASES['transformacion']['orden'] => 'done',
                $key === $faseActual => 'active',
                $meta['orden'] === $ordenSiguiente => 'next',
                default => 'pending',
            };

            $esFaseUnica = in_array($key, self::FASES_UNICAS, true);

            return [
                'key' => $key,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'icon' => $meta['icon'],
                'eventos' => $key === 'transformacion' ? $totalEtapas : 0,
                'fase_unica' => $esFaseUnica,
                'completada' => $esFaseUnica && $estado === 'done',
                'estado' => $estado,
                'url' => null,
            ];
        })->values();

        $siguienteFase = $this->siguienteFase($faseActual);
        $pendiente = $this->pasosHaciaCompleto($lote, $faseActual, $etapasTransformacion, $transformacionCompleta);
        $panelFaseActivo = $this->panelFaseActivo($faseActual);
        $fasesCompletadas = $this->fasesCompletadas($lote, $transformacionCompleta);

        return [
            'fase_actual' => $faseActual,
            'fase_actual_label' => self::FASES[$faseActual]['label'] ?? $faseActual,
            'progreso' => $this->progresoFase($faseActual),
            'panel_fase_activo' => $panelFaseActivo,
            'fases_completadas' => $fasesCompletadas,
            'fases_pipeline' => $fasesPipeline,
            'siguiente_fase_label' => $siguienteFase ? (self::FASES[$siguienteFase]['label'] ?? null) : null,
            'pendiente' => $pendiente,
            'etapas_transformacion' => $etapasTransformacion,
            'transformacion_completa' => $transformacionCompleta,
            'registros' => $registros,
            'evaluacion' => $lote->evaluacionesFinales->sortByDesc('fecha_evaluacion')->first(),
            'almacenaje' => $lote->almacenajes->sortByDesc('fecha_almacenaje')->first(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $etapasTransformacion
     * @return array{completo: bool, resumen: string, acciones: array<int, string>}
     */
    public function pasosHaciaCompleto(
        LoteProduccionPedido $lote,
        string $faseActual,
        array $etapasTransformacion,
        bool $transformacionCompleta
    ): array {
        if ($this->trazabilidadCompleta($lote)) {
            return [
                'completo' => true,
                'resumen' => 'Lote completado y trazabilidad cerrada',
                'acciones' => [],
            ];
        }

        $acciones = match ($faseActual) {
            'creacion' => [
                'Registrar la primera etapa de transformación: elija proceso, maquinaria, fecha y hora.',
                'Ejemplo: Preparación de materias primas (pelar/cortar) → Tratamiento térmico (freír) → Empaquetado.',
            ],
            'transformacion' => array_filter([
                'Registre cada etapa con su maquinaria y horario.',
                ! $transformacionCompleta
                    ? 'Debe cerrar la transformación registrando la etapa «'.ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION.'».'
                    : null,
            ]),
            'certificacion' => [
                'Registrar evaluación final (certificado o no conforme)',
            ],
            'almacenaje' => [
                'Registrar el ingreso del producto terminado en un almacén de planta',
                'Seleccione almacén y condiciones de conservación — al confirmar, el lote se cierra al 100 %',
            ],
            default => ['Finalizar el lote de producción'],
        };

        $siguiente = $this->siguienteFase($faseActual);
        $resumen = $siguiente
            ? 'Siguiente meta: '.(self::FASES[$siguiente]['label'] ?? $siguiente).' ('.$this->progresoFase($siguiente).' %)'
            : 'En avance hacia completado';

        return [
            'completo' => false,
            'resumen' => $resumen,
            'acciones' => array_values(array_filter($acciones)),
        ];
    }

    public function estadoOperativo(LoteProduccionPedido $lote): string
    {
        if ($lote->hora_fin || $lote->almacenajes()->exists()) {
            return 'completado';
        }
        if ($lote->evaluacionesFinales()->exists()) {
            return 'certificado';
        }
        if ($this->transformacion->transformacionIniciada($lote)) {
            return 'en_proceso';
        }

        return 'pendiente';
    }
}
