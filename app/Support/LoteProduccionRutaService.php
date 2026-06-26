<?php

namespace App\Support;

use App\Models\LoteProduccionPedido;
use App\Models\LoteProduccionPasoVariable;
use App\Models\LoteProduccionRutaPaso;
use App\Models\LoteProduccionRutaPasoVariable;
use App\Models\MaquinaVariablePlanta;
use App\Models\PlantillaTransformacion;
use App\Models\PlantillaTransformacionPaso;
use App\Models\ProcesoPlanta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoteProduccionRutaService
{
    public function tieneRuta(LoteProduccionPedido $lote): bool
    {
        if (! Schema::hasTable('lote_produccion_ruta_paso')) {
            return false;
        }

        return LoteProduccionRutaPaso::query()
            ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
            ->exists();
    }

    /** @return Collection<int, LoteProduccionRutaPaso> */
    public function pasosOrdenados(LoteProduccionPedido $lote): Collection
    {
        if (! $this->tieneRuta($lote)) {
            return collect();
        }

        return LoteProduccionRutaPaso::query()
            ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
            ->with(['proceso', 'maquina', 'variables.variableEstandar'])
            ->orderBy('orden')
            ->get();
    }

    public function inicializarDesdePlantilla(LoteProduccionPedido $lote, ?int $plantillatransformacionid): void
    {
        if (! Schema::hasTable('lote_produccion_ruta_paso') || ! $plantillatransformacionid) {
            return;
        }

        LoteProduccionRutaPaso::query()
            ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
            ->each(fn (LoteProduccionRutaPaso $p) => $p->variables()->delete());
        LoteProduccionRutaPaso::query()
            ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
            ->delete();

        $plantilla = PlantillaTransformacion::query()
            ->with(['pasos.proceso', 'pasos.maquina', 'pasos.variables'])
            ->find($plantillatransformacionid);

        if (! $plantilla) {
            return;
        }

        $overrides = LoteProduccionPasoVariable::query()
            ->where('loteproduccionpedidoid', $lote->loteproduccionpedidoid)
            ->get()
            ->groupBy(fn (LoteProduccionPasoVariable $o) => $o->plantillapasoid.'-'.$o->variableestandarid);

        foreach ($plantilla->pasos as $paso) {
            $rutaPaso = LoteProduccionRutaPaso::create([
                'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
                'orden' => (int) $paso->orden,
                'procesoplantaid' => (int) $paso->procesoplantaid,
                'maquinaplantaid' => $paso->maquinaplantaid ? (int) $paso->maquinaplantaid : null,
                'notas' => $paso->notas,
                'plantillapasoid' => (int) $paso->plantillapasoid,
            ]);

            foreach ($paso->variables as $pv) {
                $key = $paso->plantillapasoid.'-'.$pv->variableestandarid;
                $override = $overrides->get($key)?->first();

                LoteProduccionRutaPasoVariable::create([
                    'loteproduccionrutapasoid' => $rutaPaso->loteproduccionrutapasoid,
                    'variableestandarid' => (int) $pv->variableestandarid,
                    'valor_minimo' => $override ? (float) $override->valor_minimo : (float) $pv->valor_minimo,
                    'valor_maximo' => $override ? (float) $override->valor_maximo : (float) $pv->valor_maximo,
                    'obligatorio' => true,
                ]);
            }
        }
    }

    public function asegurarRuta(LoteProduccionPedido $lote): bool
    {
        if ($this->tieneRuta($lote)) {
            return false;
        }

        if ($lote->plantillatransformacionid) {
            $this->inicializarDesdePlantilla($lote, (int) $lote->plantillatransformacionid);

            return $this->tieneRuta($lote);
        }

        $this->inicializarDesdeRegistros($lote);

        return $this->tieneRuta($lote);
    }

    /**
     * Corrige rutas donde «Empaquetado» quedó fuera del último lugar (p. ej. tras un reorden erróneo).
     */
    public function repararEmpaquetadoAlFinal(LoteProduccionPedido $lote): bool
    {
        if (! $this->tieneRuta($lote)) {
            return false;
        }

        $completados = $this->etapasCompletadas($lote);
        $payload = $this->payloadPasosParaSincronizar($lote);
        $normalizado = $this->normalizarPayloadEmpaquetadoAlFinal($payload, $completados);

        if (array_column($payload, 'loteproduccionrutapasoid') === array_column($normalizado, 'loteproduccionrutapasoid')) {
            return false;
        }

        try {
            $this->sincronizarRuta($lote, $normalizado);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * @param  list<array{loteproduccionrutapasoid?: int, procesoplantaid: int, maquinaplantaid?: ?int, notas?: ?string, variables?: list<mixed>}>  $pasos
     * @return list<array{loteproduccionrutapasoid?: int, procesoplantaid: int, maquinaplantaid?: ?int, notas?: ?string, variables?: list<mixed>}>
     */
    private function normalizarPayloadEmpaquetadoAlFinal(array $pasos, int $completados, ?int $cierreId = null): array
    {
        $cierreId ??= ProcesoPlantaCatalogo::idProcesoCierreTransformacion();
        if (! $cierreId || count($pasos) <= $completados + 1) {
            return $pasos;
        }

        $fijos = array_slice($pasos, 0, $completados);
        $pendientes = array_slice($pasos, $completados);
        $cierre = null;
        $resto = [];

        foreach ($pendientes as $paso) {
            if ((int) ($paso['procesoplantaid'] ?? 0) === $cierreId) {
                $cierre = $paso;
            } else {
                $resto[] = $paso;
            }
        }

        if ($cierre === null) {
            return $pasos;
        }

        return array_merge($fijos, $resto, [$cierre]);
    }

    public function inicializarDesdeRegistros(LoteProduccionPedido $lote): void
    {
        if (! Schema::hasTable('lote_produccion_ruta_paso') || $this->tieneRuta($lote)) {
            return;
        }

        $registros = app(LoteProduccionTransformacionService::class)->registrosOrdenados($lote);
        if ($registros->isEmpty()) {
            return;
        }

        $orden = 1;
        foreach ($registros as $registro) {
            $vinculo = $registro->procesoMaquina;
            if (! $vinculo) {
                continue;
            }

            $rutaPaso = LoteProduccionRutaPaso::create([
                'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
                'orden' => $orden,
                'procesoplantaid' => (int) $vinculo->procesoplantaid,
                'maquinaplantaid' => $vinculo->maquinaplantaid ? (int) $vinculo->maquinaplantaid : null,
                'notas' => null,
                'plantillapasoid' => null,
            ]);

            $this->copiarVariablesMaquinaAPaso(
                $rutaPaso,
                $vinculo->maquinaplantaid ? (int) $vinculo->maquinaplantaid : null
            );

            $orden++;
        }
    }

    /**
     * @return array{procesados: int, creados: int, desde_plantilla: int, desde_registros: int}
     */
    public function migrarTodosLosLotes(): array
    {
        $stats = [
            'procesados' => 0,
            'creados' => 0,
            'desde_plantilla' => 0,
            'desde_registros' => 0,
        ];

        if (! Schema::hasTable('lote_produccion_ruta_paso')) {
            return $stats;
        }

        LoteProduccionPedido::query()
            ->orderBy('loteproduccionpedidoid')
            ->chunkById(50, function ($lotes) use (&$stats) {
                foreach ($lotes as $lote) {
                    $stats['procesados']++;
                    if ($this->tieneRuta($lote)) {
                        continue;
                    }

                    $conPlantilla = (bool) $lote->plantillatransformacionid;
                    if ($this->asegurarRuta($lote)) {
                        $stats['creados']++;
                        if ($conPlantilla) {
                            $stats['desde_plantilla']++;
                        } else {
                            $stats['desde_registros']++;
                        }
                    }
                }
            }, 'loteproduccionpedidoid');

        return $stats;
    }

    private function copiarVariablesMaquinaAPaso(LoteProduccionRutaPaso $paso, ?int $maquinaplantaid): void
    {
        if (! $maquinaplantaid || ! Schema::hasTable('maquina_variable_planta')) {
            return;
        }

        $variables = MaquinaVariablePlanta::query()
            ->where('maquinaplantaid', $maquinaplantaid)
            ->get();

        foreach ($variables as $variable) {
            LoteProduccionRutaPasoVariable::create([
                'loteproduccionrutapasoid' => $paso->loteproduccionrutapasoid,
                'variableestandarid' => (int) $variable->variableestandarid,
                'valor_minimo' => (float) $variable->valor_minimo,
                'valor_maximo' => (float) $variable->valor_maximo,
                'obligatorio' => true,
            ]);
        }
    }

    /** @return list<array<string, mixed>> */
    public function payloadPasosParaSincronizar(LoteProduccionPedido $lote): array
    {
        return $this->pasosOrdenados($lote)->map(function (LoteProduccionRutaPaso $paso) {
            return [
                'loteproduccionrutapasoid' => (int) $paso->loteproduccionrutapasoid,
                'procesoplantaid' => (int) $paso->procesoplantaid,
                'maquinaplantaid' => $paso->maquinaplantaid ? (int) $paso->maquinaplantaid : null,
                'notas' => $paso->notas,
                'es_cierre' => ProcesoPlantaCatalogo::esCierreTransformacion($paso->proceso?->nombre),
                'variables' => $paso->variables->map(fn (LoteProduccionRutaPasoVariable $v) => [
                    'variableestandarid' => (int) $v->variableestandarid,
                    'valor_minimo' => (float) $v->valor_minimo,
                    'valor_maximo' => (float) $v->valor_maximo,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    public function pasoEnOrden(LoteProduccionPedido $lote, int $orden): ?LoteProduccionRutaPaso
    {
        return $this->pasosOrdenados($lote)->firstWhere('orden', $orden);
    }

    public function etapasCompletadas(LoteProduccionPedido $lote): int
    {
        return app(LoteProduccionTransformacionService::class)->etapasCompletadasCount($lote);
    }

    /** @return list<array<string, mixed>> */
    public function rutaParaVista(LoteProduccionPedido $lote): array
    {
        $pasos = $this->pasosOrdenados($lote);
        if ($pasos->isEmpty()) {
            return [];
        }

        $completados = $this->etapasCompletadas($lote);
        $ordenActual = $completados + 1;
        $hayPendiente = app(LoteProduccionTransformacionService::class)->tieneAsignacionesPendientes($lote);
        $registrados = collect(app(LoteProduccionTransformacionService::class)->procesosRegistradosIds($lote));
        $pasosBloqueadosReorden = app(LoteProduccionTransformacionService::class)
            ->pasosRutaPasoIdsBloqueadosReorden($lote);
        $items = [];

        foreach ($pasos as $paso) {
            $orden = (int) $paso->orden;
            $esCierre = ProcesoPlantaCatalogo::esCierreTransformacion($paso->proceso?->nombre);
            $procesoHecho = $registrados->contains((int) $paso->procesoplantaid);
            $estado = match (true) {
                $procesoHecho => 'hecho',
                $orden === $ordenActual && $hayPendiente => 'en_curso',
                $orden === $ordenActual => 'actual',
                default => 'bloqueado',
            };

            $vars = $paso->variables->map(fn (LoteProduccionRutaPasoVariable $v) => [
                'variableestandarid' => (int) $v->variableestandarid,
                'nombre' => $v->variableEstandar?->nombre ?? '—',
                'unidad' => $v->variableEstandar?->unidad,
                'valor_minimo' => (float) $v->valor_minimo,
                'valor_maximo' => (float) $v->valor_maximo,
                'maq_minimo' => $paso->maquinaplantaid
                    ? ParametroRangoPlanta::limitesMaquina((int) $paso->maquinaplantaid, (int) $v->variableestandarid)['min'] ?? null
                    : null,
                'maq_maximo' => $paso->maquinaplantaid
                    ? ParametroRangoPlanta::limitesMaquina((int) $paso->maquinaplantaid, (int) $v->variableestandarid)['max'] ?? null
                    : null,
            ])->values()->all();

            $items[] = [
                'loteproduccionrutapasoid' => (int) $paso->loteproduccionrutapasoid,
                'orden' => $orden,
                'proceso' => $paso->proceso?->nombre ?? '—',
                'maquina' => $paso->maquina?->nombre,
                'procesoplantaid' => (int) $paso->procesoplantaid,
                'maquinaplantaid' => $paso->maquinaplantaid ? (int) $paso->maquinaplantaid : null,
                'notas' => $paso->notas,
                'estado' => $estado,
                'es_cierre' => $esCierre,
                'parametros' => $vars,
                'editable' => $orden > $completados
                    && ! $esCierre
                    && ! in_array((int) $paso->loteproduccionrutapasoid, $pasosBloqueadosReorden, true),
            ];
        }

        return $items;
    }

    /**
     * @param  list<array{loteproduccionrutapasoid?: int, procesoplantaid: int, maquinaplantaid?: ?int, notas?: ?string, variables?: list<array{variableestandarid: int, valor_minimo: float|int, valor_maximo: float|int}>}>  $pasos
     */
    public function sincronizarRuta(LoteProduccionPedido $lote, array $pasos): void
    {
        if (! Schema::hasTable('lote_produccion_ruta_paso')) {
            return;
        }

        $completados = $this->etapasCompletadas($lote);
        $cierreId = ProcesoPlantaCatalogo::idProcesoCierreTransformacion();

        $pasos = $this->normalizarPayloadEmpaquetadoAlFinal($pasos, $completados, $cierreId);

        $bloqueados = app(LoteProduccionTransformacionService::class)
            ->pasosRutaPasoIdsBloqueadosReorden($lote);

        if ($cierreId) {
            $ultimo = $pasos[array_key_last($pasos)] ?? null;
            if ($ultimo && (int) ($ultimo['procesoplantaid'] ?? 0) !== $cierreId) {
                throw new \InvalidArgumentException('El último paso de la ruta debe ser «'.ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION.'».');
            }
        }

        DB::transaction(function () use ($lote, $pasos, $completados, $bloqueados) {
            $existentes = $this->pasosOrdenados($lote)->keyBy('loteproduccionrutapasoid');

            // Evitar choque del índice único (lote, orden) al reordenar pasos pendientes.
            $tempBase = 100000;
            foreach ($existentes as $p) {
                if ((int) $p->orden > $completados) {
                    LoteProduccionRutaPaso::query()
                        ->whereKey($p->loteproduccionrutapasoid)
                        ->update(['orden' => $tempBase + (int) $p->loteproduccionrutapasoid]);
                }
            }

            $existentes = $this->pasosOrdenados($lote)->keyBy('loteproduccionrutapasoid');
            $vistos = [];
            $orden = 1;

            foreach ($pasos as $fila) {
                $id = (int) ($fila['loteproduccionrutapasoid'] ?? 0);
                $paso = $id > 0 ? $existentes->get($id) : null;
                if ($paso && (int) $paso->orden <= $completados) {
                    if ((int) $paso->orden !== $orden) {
                        throw new \InvalidArgumentException('No puede reordenar pasos ya completados.');
                    }
                    $vistos[$paso->loteproduccionrutapasoid] = true;
                    $orden++;

                    continue;
                }

                if ($paso && in_array((int) $paso->loteproduccionrutapasoid, $bloqueados, true)
                    && (int) $paso->orden !== $orden) {
                    throw new \InvalidArgumentException(
                        'No puede reordenar etapas con operario asignado. Complete la etapa o cancele el plan.'
                    );
                }

                $procId = (int) ($fila['procesoplantaid'] ?? 0);
                $maqId = ! empty($fila['maquinaplantaid']) ? (int) $fila['maquinaplantaid'] : null;

                if ($maqId && ! MaquinaProcesoCompatibilidad::compatible($procId, $maqId)) {
                    throw new \InvalidArgumentException('La máquina no es compatible con el proceso del paso '.$orden.'.');
                }

                if ($paso) {
                    $cambioDatos = (int) $paso->procesoplantaid !== $procId
                        || (int) ($paso->maquinaplantaid ?? 0) !== (int) ($maqId ?? 0)
                        || (string) ($paso->notas ?? '') !== (string) ($fila['notas'] ?? '');

                    $paso->update([
                        'orden' => $orden,
                        'procesoplantaid' => $procId,
                        'maquinaplantaid' => $maqId,
                        'notas' => $fila['notas'] ?? null,
                    ]);

                    if ($cambioDatos || $this->variablesCambiaron($paso, $fila['variables'] ?? [])) {
                        $this->syncVariablesPaso($paso->fresh(), $fila['variables'] ?? [], $maqId);
                    }

                    $vistos[$paso->loteproduccionrutapasoid] = true;
                } else {
                    $nuevo = LoteProduccionRutaPaso::create([
                        'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
                        'orden' => $orden,
                        'procesoplantaid' => $procId,
                        'maquinaplantaid' => $maqId,
                        'notas' => $fila['notas'] ?? null,
                        'plantillapasoid' => null,
                    ]);
                    $this->syncVariablesPaso($nuevo, $fila['variables'] ?? [], $maqId);
                    $vistos[$nuevo->loteproduccionrutapasoid] = true;
                }

                $orden++;
            }

            foreach ($existentes as $id => $paso) {
                if (! isset($vistos[$id]) && (int) $paso->orden > $completados) {
                    $paso->variables()->delete();
                    $paso->delete();
                }
            }
        });
    }

    /** @param  list<array{variableestandarid: int, valor_minimo: float|int, valor_maximo: float|int}>  $variables */
    private function variablesCambiaron(LoteProduccionRutaPaso $paso, array $variables): bool
    {
        $paso->loadMissing('variables');

        $normalizar = static function (array $lista): array {
            return collect($lista)
                ->map(fn ($v) => [
                    'variableestandarid' => (int) ($v['variableestandarid'] ?? 0),
                    'valor_minimo' => (float) ($v['valor_minimo'] ?? 0),
                    'valor_maximo' => (float) ($v['valor_maximo'] ?? 0),
                ])
                ->filter(fn ($v) => $v['variableestandarid'] > 0)
                ->sortBy('variableestandarid')
                ->values()
                ->all();
        };

        $actuales = $normalizar($paso->variables->map(fn ($v) => [
            'variableestandarid' => $v->variableestandarid,
            'valor_minimo' => $v->valor_minimo,
            'valor_maximo' => $v->valor_maximo,
        ])->all());

        return $actuales !== $normalizar($variables);
    }

    /** @param  list<array{variableestandarid: int, valor_minimo: float|int, valor_maximo: float|int}>  $variables */
    public function actualizarVariablesPaso(LoteProduccionRutaPaso $paso, array $variables): void
    {
        $paso->loadMissing('maquina');
        $this->syncVariablesPaso($paso, $variables, $paso->maquinaplantaid ? (int) $paso->maquinaplantaid : null);
    }

    /** @param  list<array{variableestandarid: int, valor_minimo: float|int, valor_maximo: float|int}>  $variables */
    private function syncVariablesPaso(LoteProduccionRutaPaso $paso, array $variables, ?int $maquinaplantaid): void
    {
        $paso->variables()->delete();
        $nombres = \App\Models\VariableEstandar::query()->pluck('nombre', 'variableestandarid')->all();

        foreach ($variables as $var) {
            $varId = (int) ($var['variableestandarid'] ?? 0);
            if ($varId <= 0) {
                continue;
            }
            $min = (float) ($var['valor_minimo'] ?? 0);
            $max = (float) ($var['valor_maximo'] ?? 0);
            $error = ParametroRangoPlanta::validarRango($maquinaplantaid, $varId, $min, $max, $nombres[$varId] ?? null);
            if ($error !== null) {
                throw new \InvalidArgumentException('Paso '.$paso->orden.': '.$error);
            }

            LoteProduccionRutaPasoVariable::create([
                'loteproduccionrutapasoid' => $paso->loteproduccionrutapasoid,
                'variableestandarid' => $varId,
                'valor_minimo' => $min,
                'valor_maximo' => $max,
                'obligatorio' => true,
            ]);
        }
    }
}
