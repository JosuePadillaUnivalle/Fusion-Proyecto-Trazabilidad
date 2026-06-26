@php

    $items = $items ?? [];

    $compacto = !empty($compacto);

    $titulo = $titulo ?? null;

    $sortable = !empty($sortable);

    $rutaUrl = $rutaUrl ?? '';

    $rutaPasosJson = $rutaPasosJson ?? [];

    $etapasCompletadas = (int) ($etapasCompletadas ?? 0);

    $modoPlan = ! empty($modoPlan);

    $puedeGestionarPlan = ! empty($puedeGestionarPlan);

    $formPlanActivo = ! empty($formPlanActivo);

    $cerrarFaseUrl = $cerrarFaseUrl ?? '';

    $cambiarFaseUrl = $cambiarFaseUrl ?? '';

    $loteTimeline = $lote ?? null;

    $puedeMarcarCompletada = ! empty($puedeMarcarCompletada);

    $usuarioActualId = (int) ($usuarioActualId ?? 0);

    $esOperarioPlanta = ! empty($esOperarioPlanta);

@endphp



@if($titulo)

<div class="small font-weight-bold text-success mb-2"><i class="fas fa-route mr-1"></i>{{ $titulo }}</div>

@endif



@if(empty($items))

<p class="small text-muted mb-0">Sin etapas definidas aún.</p>

@else

@if($sortable)

<p class="small text-muted mb-2 tl-transformacion__hint">

    <i class="fas fa-arrows-alt-v mr-1"></i> Arrastre una carta (icono <i class="fas fa-grip-vertical mx-1"></i> o borde) para <strong>intercambiar</strong> posiciones.
    <i class="fas fa-lock text-muted mx-1"></i> Etapas con operario asignado o Empaquetado quedan fijas.
    @if($modoPlan)
        Asigne operarios y ajuste parámetros en cada carta; use <strong>Cerrar fase</strong> por etapa o <strong>Cerrar todos</strong> cuando todas tengan operario.
    @endif

</p>

@endif

<div class="tl-transformacion {{ $compacto ? 'tl-transformacion--compact' : '' }}"

     id="tlTransformacionRoot"

     @if($modoPlan) data-modo-plan="1" @endif

     @if($sortable) data-sortable="1" data-ruta-url="{{ $rutaUrl }}" data-completados="{{ $etapasCompletadas }}" data-cierre-proceso-id="{{ \App\Support\ProcesoPlantaCatalogo::idProcesoCierreTransformacion() ?? '' }}" @endif>

    @foreach($items as $idx => $paso)

        @php

            $estado = $paso['estado'] ?? 'bloqueado';

            if ($modoPlan && $estado === 'bloqueado') {
                $estado = 'plan_asignable';
            }

            if ($estado === 'bloqueado' && ($paso['estado_asignacion'] ?? '') === 'programada') {
                $estado = 'en_cola';
            }

            $estadoClass = match ($estado) {

                'hecho' => 'tl-transformacion__item--hecho',

                'actual' => 'tl-transformacion__item--actual',

                'en_curso' => 'tl-transformacion__item--en-curso',

                'plan_asignable' => 'tl-transformacion__item--plan',

                'en_cola' => 'tl-transformacion__item--en-cola',

                default => 'tl-transformacion__item--bloqueado',

            };

            $prevEstado = $idx > 0 ? ($items[$idx - 1]['estado'] ?? '') : '';

            $flowActual = in_array($estado, ['actual', 'en_curso'], true);

            $flowHecho = $prevEstado === 'hecho' && $estado === 'hecho';

            $esCierre = ! empty($paso['es_cierre']);

            $asignacionBloqueada = ! empty($paso['asignacion_bloqueada']);

            $reordenable = $sortable && $estado !== 'hecho' && ! empty($paso['editable']) && ! $esCierre && ! $asignacionBloqueada;

            $pasoId = (int) ($paso['loteproduccionrutapasoid'] ?? 0);

            $mostrarCamposPlan = $modoPlan && $estado !== 'hecho' && empty($paso['operador_asignado']) && $pasoId > 0;

            $mostrarCambiarFase = $puedeGestionarPlan
                && ! empty($paso['operador_asignado'])
                && $estado !== 'hecho'
                && ! empty($paso['asignacion_id']);

            $mostrarMarcarCompletada = $puedeMarcarCompletada
                && ($paso['estado_asignacion'] ?? '') === 'pendiente'
                && ! empty($paso['asignacion_id'])
                && ! empty($loteTimeline);

            $mostrarOperarioCompletar = $esOperarioPlanta
                && $usuarioActualId > 0
                && (int) ($paso['operador_usuarioid'] ?? 0) === $usuarioActualId
                && ($paso['estado_asignacion'] ?? '') === 'pendiente'
                && ! empty($paso['asignacion_id'])
                && ! empty($loteTimeline);

        @endphp



        @if($idx > 0)

        <div class="tl-transformacion__flow{{ $flowActual ? ' tl-transformacion__flow--actual' : '' }}{{ $flowHecho ? ' tl-transformacion__flow--hecho' : '' }}">

            <div class="tl-transformacion__flow-arrow" title="{{ $flowActual ? 'Usted está aquí' : 'Siguiente paso' }}">

                <i class="fas fa-long-arrow-alt-down"></i>

            </div>

        </div>

        @endif



        <div class="tl-transformacion__item {{ $estadoClass }} {{ $reordenable ? 'tl-transformacion__item--reordenable' : '' }} {{ $esCierre && $estado !== 'hecho' ? 'tl-transformacion__item--cierre-fijo' : '' }}"

             @if(!empty($paso['asignacion_id'])) data-asignacion-id="{{ (int) $paso['asignacion_id'] }}" @endif

             @if(!empty($paso['loteproduccionrutapasoid']))

                 data-ruta-paso-id="{{ (int) $paso['loteproduccionrutapasoid'] }}"

                 @if($esCierre) data-es-cierre="1" @endif

             @endif

            @if($reordenable)

                 data-reordenable="1"

             @endif>

            @if($reordenable)

            <div class="tl-transformacion__grip" title="Arrastrar para intercambiar posición">

                <i class="fas fa-grip-vertical"></i>

            </div>

            @elseif($asignacionBloqueada && $estado !== 'hecho')

            <div class="tl-transformacion__grip tl-transformacion__grip--lock" title="Etapa con operario asignada — no se puede mover">

                <i class="fas fa-lock"></i>

            </div>

            @elseif($esCierre && $estado !== 'hecho')

            <div class="tl-transformacion__grip tl-transformacion__grip--lock" title="Empaquetado siempre al final">

                <i class="fas fa-lock"></i>

            </div>

            @endif

            <div class="tl-transformacion__media">

                @if(!empty($paso['imagen_src']))

                    <img src="{{ $paso['imagen_src'] }}" alt="{{ $paso['maquina'] ?? 'Máquina' }}">

                @else

                    <i class="fas fa-industry"></i>

                @endif

            </div>

            <div class="tl-transformacion__body">

                <div class="tl-transformacion__head">

                    <span class="tl-transformacion__orden">

                        @if($estado === 'hecho')<i class="fas fa-check"></i>@else{{ $paso['orden'] ?? ($idx + 1) }}@endif

                    </span>

                    <div>

                        <strong>{{ $paso['proceso'] ?? '—' }}</strong>

                        @if(!empty($paso['es_cierre']))<span class="badge badge-info ml-1">Cierre</span>@endif

                        @if($estado === 'en_curso')<span class="badge badge-warning ml-1">En curso</span>@endif

                        @if($estado === 'actual' && ! $modoPlan)<span class="badge badge-success ml-1">Siguiente</span>@endif

                        @if($modoPlan && in_array($estado, ['actual', 'plan_asignable'], true) && $estado !== 'hecho')
                            @if($estado === 'actual')
                                <span class="badge badge-success ml-1">Empieza aquí</span>
                            @else
                                <span class="badge badge-light border text-muted ml-1">Por asignar</span>
                            @endif
                        @endif

                        @if(!empty($paso['operador_asignado']) && $estado !== 'hecho')
                            <span class="badge badge-light border ml-1"><i class="fas fa-user-cog mr-1"></i>{{ $paso['operador_asignado'] }}</span>
                            @if(($paso['estado_asignacion'] ?? '') === 'programada')
                                <span class="badge badge-secondary ml-1">En cola</span>
                            @endif
                        @endif

                    </div>

                </div>

                @if(!empty($paso['maquina']))

                    <div class="tl-transformacion__meta">

                        <i class="fas fa-cogs mr-1"></i>{{ $paso['maquina'] }}

                        @if(!empty($paso['maquina_codigo']))<span class="text-muted">({{ $paso['maquina_codigo'] }})</span>@endif

                    </div>

                @endif

                @if(!empty($paso['notas']) && !$compacto)

                    <div class="tl-transformacion__notas">{{ $paso['notas'] }}</div>

                @endif

                @if($mostrarCamposPlan)

                    <div class="tl-transformacion__plan-fields" data-plan-etapa="1" data-paso-id="{{ $pasoId }}">

                        <div class="tl-transformacion__operario-row">

                            <input type="hidden" name="etapas[{{ $pasoId }}][loteproduccionrutapasoid]" value="{{ $pasoId }}">

                            @include('partials.selector-catalogo', [
                                'id' => 'plan_operario_'.$pasoId,
                                'name' => 'etapas['.$pasoId.'][operador_usuarioid]',
                                'label' => 'Operario',
                                'icon' => 'fa-user-cog',
                                'endpoint' => route('catalogo-selector.usuarios'),
                                'params' => ['operarios_planta' => '1'],
                                'title' => 'Elegir operario — etapa '.($paso['orden'] ?? ''),
                                'searchPlaceholder' => 'Nombre, correo…',
                                'colNombre' => 'Operario',
                                'colDetalle' => 'Contacto',
                                'rowIcon' => 'fa-user-cog',
                                'theme' => 'planta',
                                'required' => true,
                                'inputGroup' => true,
                                'showLabel' => true,
                                'size' => 'sm',
                                'value' => old('etapas.'.$pasoId.'.operador_usuarioid', ''),
                                'labelSelected' => '',
                            ])

                        </div>

                        @if(!empty($paso['parametros_rango']))

                            <div class="tl-transformacion__params-edit">

                                <span class="tl-transformacion__params-edit-title">Parámetros de máquina</span>

                                <div class="row">

                                    @foreach($paso['parametros_rango'] as $vIdx => $pr)

                                        @php

                                            $varId = (int) ($pr['variableestandarid'] ?? 0);

                                            $maqLim = null;

                                            if (! empty($paso['maquinaplantaid']) && $varId) {

                                                $maqLim = \App\Support\ParametroRangoPlanta::limitesMaquina((int) $paso['maquinaplantaid'], $varId);

                                            }

                                            $escala = \App\Support\ParametroRangoPlanta::limitesEscala($pr['unidad'] ?? null);

                                            $comb = \App\Support\ParametroRangoPlanta::combinarLimites($maqLim, $escala);

                                        @endphp

                                        <div class="col-md-6 col-lg-4 mb-2">

                                            <div class="tl-plan-var-row" data-var-id="{{ $varId }}" data-ruta-paso-id="{{ $pasoId }}">

                                                <div class="tl-transformacion__param-label">

                                                    {{ $pr['nombre'] ?? '—' }}

                                                    @if(!empty($pr['unidad']))<span class="text-muted">({{ $pr['unidad'] }})</span>@endif

                                                </div>

                                                <div class="tl-transformacion__param-inputs">

                                                    <input type="hidden" name="etapas[{{ $pasoId }}][variables][{{ $vIdx }}][variableestandarid]" value="{{ $varId }}">

                                                    <input type="number" step="0.1"

                                                           name="etapas[{{ $pasoId }}][variables][{{ $vIdx }}][valor_minimo]"

                                                           class="form-control form-control-sm tl-plan-var-min"

                                                           value="{{ old('etapas.'.$pasoId.'.variables.'.$vIdx.'.valor_minimo', $pr['valor_minimo'] ?? 0) }}"

                                                           @if($comb) data-rango-min="{{ $comb['min'] }}" data-rango-max="{{ $comb['max'] }}" @endif

                                                           required>

                                                    <span class="tl-transformacion__param-sep">–</span>

                                                    <input type="number" step="0.1"

                                                           name="etapas[{{ $pasoId }}][variables][{{ $vIdx }}][valor_maximo]"

                                                           class="form-control form-control-sm tl-plan-var-max"

                                                           value="{{ old('etapas.'.$pasoId.'.variables.'.$vIdx.'.valor_maximo', $pr['valor_maximo'] ?? 0) }}"

                                                           @if($comb) data-rango-min="{{ $comb['min'] }}" data-rango-max="{{ $comb['max'] }}" @endif

                                                           required>

                                                </div>

                                                @if($maqLim)

                                                    <small class="text-muted d-block tl-transformacion__param-maq">Máq. {{ number_format($maqLim['min'], 1) }}–{{ number_format($maqLim['max'], 1) }}</small>

                                                @endif

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endif

                        @if($formPlanActivo && $cerrarFaseUrl !== '')
                        <div class="tl-transformacion__plan-actions mt-2">
                            <button type="submit"
                                    formaction="{{ $cerrarFaseUrl }}"
                                    name="cerrar_paso"
                                    value="{{ $pasoId }}"
                                    class="btn btn-success btn-sm font-weight-bold js-cerrar-fase-single"
                                    disabled
                                    aria-disabled="true"
                                    title="Seleccione un operario para cerrar la fase"
                                    data-confirm-modal
                                    data-confirm-title="Cerrar fase"
                                    data-confirm-message="¿Cerrar esta fase con el operario y parámetros indicados?"
                                    data-confirm-tone="success"
                                    data-confirm-btn="Cerrar fase">
                                <i class="fas fa-lock mr-1"></i> Cerrar fase
                            </button>
                        </div>
                        @endif

                    </div>

                @elseif(!empty($paso['parametros_rango']))

                    <div class="tl-transformacion__params">

                        @foreach($paso['parametros_rango'] as $pr)

                            <span class="badge badge-light border mr-1 mb-1">

                                {{ $pr['nombre'] }}@if(!empty($pr['unidad'])) ({{ $pr['unidad'] }})@endif:

                                {{ number_format($pr['valor_minimo'], 1) }}–{{ number_format($pr['valor_maximo'], 1) }}

                            </span>

                        @endforeach

                    </div>

                @endif

                @if(!empty($paso['parametros_medidos']))

                    <div class="tl-transformacion__medidos">

                        @foreach($paso['parametros_medidos'] as $pm)

                            <span class="badge badge-success mr-1 mb-1">

                                {{ $pm['nombre'] }}: {{ number_format($pm['valor'], 1) }}@if(!empty($pm['unidad'])) {{ $pm['unidad'] }}@endif

                            </span>

                        @endforeach

                    </div>

                @endif

                @if($mostrarMarcarCompletada || $mostrarOperarioCompletar || $mostrarCambiarFase)
                    <div class="tl-transformacion__acciones-etapa mt-2">
                        @if($mostrarOperarioCompletar)
                            <button type="button"
                                    class="btn btn-success btn-sm font-weight-bold js-tl-marcar-completada"
                                    data-completar-url="{{ route('procesamiento.completar-etapa-asignada', [$loteTimeline, $paso['asignacion_id']]) }}"
                                    data-csrf="{{ csrf_token() }}">
                                <i class="fas fa-check-double mr-1"></i> Completar tarea
                            </button>
                        @elseif($mostrarMarcarCompletada)
                            <button type="button"
                                    class="btn btn-success btn-sm font-weight-bold js-tl-marcar-completada"
                                    data-completar-url="{{ route('procesamiento.completar-etapa-asignada', [$loteTimeline, $paso['asignacion_id']]) }}"
                                    data-csrf="{{ csrf_token() }}">
                                <i class="fas fa-check mr-1"></i> Marcar completada
                            </button>
                        @endif
                        @if($mostrarCambiarFase)
                            @if($formPlanActivo && $cambiarFaseUrl !== '')
                                <button type="submit"
                                        formaction="{{ $cambiarFaseUrl }}"
                                        name="loteproduccionrutapasoid"
                                        value="{{ $pasoId }}"
                                        class="btn btn-sm btn-outline-warning font-weight-bold"
                                        data-confirm-modal
                                        data-confirm-title="Cambiar fase"
                                        data-confirm-message="Se liberará el operario. Los rangos de la ruta se conservan y podrá reasignar."
                                        data-confirm-tone="warning"
                                        data-confirm-btn="Cambiar fase">
                                    <i class="fas fa-exchange-alt mr-1"></i> Cambiar fase
                                </button>
                            @elseif($cambiarFaseUrl !== '')
                                <form method="POST" action="{{ $cambiarFaseUrl }}" class="d-inline mb-0 js-lp-guardar-scroll">
                                    @csrf
                                    <input type="hidden" name="loteproduccionrutapasoid" value="{{ $pasoId }}">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-warning font-weight-bold"
                                            data-confirm-modal
                                            data-confirm-title="Cambiar fase"
                                            data-confirm-message="Se liberará el operario. Los rangos de la ruta se conservan y podrá reasignar."
                                            data-confirm-tone="warning"
                                            data-confirm-btn="Cambiar fase">
                                        <i class="fas fa-exchange-alt mr-1"></i> Cambiar fase
                                    </button>
                                </form>
                            @endif
                        @endif
                        @if($mostrarMarcarCompletada && !$mostrarOperarioCompletar)
                            <p class="small text-muted mb-0 mt-2 w-100">
                                <i class="fas fa-info-circle mr-1"></i>
                                Los parámetros quedaron fijados en la línea de procesos.
                            </p>
                        @endif
                    </div>
                @endif

                @if($estado === 'hecho' && (!empty($paso['inicio']) || !empty($paso['operador'])))

                    <div class="tl-transformacion__times text-muted">

                        @if(!empty($paso['inicio']))

                            <i class="far fa-clock mr-1"></i>{{ optional($paso['inicio'])->format('d/m/Y H:i') }}

                            @if(!empty($paso['fin'])) → {{ optional($paso['fin'])->format('d/m/Y H:i') }}@endif

                        @endif

                        @if(!empty($paso['operador'])) · {{ $paso['operador'] }}@endif

                    </div>

                @endif

            </div>

        </div>

    @endforeach

</div>

@if($sortable)

<script type="application/json" id="tlRutaPasosJson">@json($rutaPasosJson)</script>

@endif

@endif



@once

@push('styles')

<style>

.tl-transformacion { position: relative; padding-left: .15rem; }

.tl-transformacion__hint { font-size: .75rem; }

.tl-transformacion__flow {

    position: relative; height: 2rem; margin-left: 2.35rem;

    display: flex; align-items: center; justify-content: center;

}

.tl-transformacion__flow-arrow {

    position: relative; z-index: 1;

    width: 1.65rem; height: 1.65rem; border-radius: 50%;

    background: #fff; border: 2px solid #94a3b8;

    display: flex; align-items: center; justify-content: center;

    color: #64748b; font-size: .85rem;

    box-shadow: 0 2px 6px rgba(15,23,42,.08);

}

.tl-transformacion__flow--hecho .tl-transformacion__flow-arrow {

    border-color: #86efac; color: #4ade80; background: #f7fef9;

}

.tl-transformacion__flow--actual .tl-transformacion__flow-arrow {

    border-color: #22c55e; color: #16a34a; background: #f0fdf4;

    box-shadow: 0 0 0 3px rgba(34, 197, 94, .22);

}

.tl-transformacion__item {

    display: flex; gap: .85rem; align-items: center; position: relative;

    padding: .95rem 1rem; margin-bottom: 0;

    background: #fff; border: 1px solid #e2ebe3; border-radius: 14px;

    box-shadow: 0 2px 10px rgba(30,70,32,.04);

    transition: box-shadow .2s ease, transform .15s ease, border-color .2s ease;

}

.tl-transformacion__item--reordenable { cursor: grab; }

.tl-transformacion__item--reordenable:active { cursor: grabbing; }

.tl-transformacion__item--reordenable .tl-transformacion__plan-fields,
.tl-transformacion__item--reordenable .selector-catalogo-wrapper,
.tl-transformacion__item--reordenable .selector-filtros-field { cursor: default; }

.tl-transformacion__item--reordenable .tl-transformacion__grip { cursor: grab; }

.tl-transformacion__item--dragging { opacity: .55; transform: scale(.985); }

.tl-transformacion--guardando { opacity: .92; }

.tl-transformacion__item--drag-over { border-color: #2c5530; box-shadow: 0 0 0 3px rgba(44,85,48,.18); }

.tl-transformacion__item--hecho { background: linear-gradient(180deg, #f0fdf4, #fff); border-color: #bbf7d0; }

.tl-transformacion__item--actual { border-color: #2c5530; box-shadow: 0 0 0 2px rgba(44,85,48,.12); }

.tl-transformacion__item--en-curso { border-color: #f59e0b; }

.tl-transformacion__item--bloqueado { opacity: .72; }

.tl-transformacion__item--plan {

    opacity: 1; background: #fff; border-color: #e2ebe3;

}

.tl-transformacion__item--plan .tl-transformacion__orden { background: #e2e8f0; color: #475569; }

.tl-transformacion__item--en-cola { opacity: 1; background: #fff; border-color: #e2e8f0; }

.tl-transformacion__item--bloqueado .tl-transformacion__plan-fields { opacity: 1; }

.tl-transformacion__item--cierre-fijo { border-style: dashed; border-color: #94a3b8; background: #f8fafc; }

.tl-transformacion__grip {

    color: #94a3b8; padding: .25rem .15rem; flex-shrink: 0; align-self: center;

}

.tl-transformacion__grip--lock { color: #64748b; cursor: not-allowed; }

.tl-transformacion__media {

    width: 96px; height: 96px; flex-shrink: 0; border-radius: 14px;

    border: 2px solid #e2ebe3; background: #f8faf8;

    display: flex; align-items: center; justify-content: center; overflow: hidden;

}

.tl-transformacion--compact .tl-transformacion__media { width: 72px; height: 72px; border-radius: 12px; }

.tl-transformacion__media img { width: 100%; height: 100%; object-fit: contain; padding: .35rem; }

.tl-transformacion__media i { font-size: 2.25rem; color: #94a3b8; }

.tl-transformacion__body { flex: 1; min-width: 0; }

.tl-transformacion__head { display: flex; align-items: flex-start; gap: .5rem; margin-bottom: .2rem; }

.tl-transformacion__orden {

    width: 1.65rem; height: 1.65rem; border-radius: 50%; flex-shrink: 0;

    display: inline-flex; align-items: center; justify-content: center;

    font-size: .72rem; font-weight: 700; background: #e2e8f0; color: #475569;

}

.tl-transformacion__item--hecho .tl-transformacion__orden { background: #22c55e; color: #fff; }

.tl-transformacion__item--actual .tl-transformacion__orden { background: #2c5530; color: #fff; }

.tl-transformacion__item--en-curso .tl-transformacion__orden { background: #f59e0b; color: #fff; }

.tl-transformacion__meta, .tl-transformacion__notas, .tl-transformacion__times { font-size: .78rem; }

.tl-transformacion__params, .tl-transformacion__medidos { margin-top: .35rem; }

.tl-transformacion__medidos .badge { font-weight: 600; }

.tl-transformacion__plan-fields {

    margin-top: .65rem; padding-top: .65rem; border-top: 1px dashed #e2ebe3;

}

.tl-transformacion__item--actual .tl-transformacion__plan-fields { border-top-color: #bbf7d0; }

.tl-transformacion__item--en-curso .tl-transformacion__plan-fields { border-top-color: #fde68a; }

.tl-transformacion__acciones-etapa {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
    padding-top: .55rem;
    border-top: 1px dashed #e2ebe3;
}

.tl-transformacion__item--en-curso .tl-transformacion__acciones-etapa { border-top-color: #fde68a; }

.tl-transformacion__operario-row {

    margin-bottom: .65rem;

}

.tl-transformacion__operario-row .selector-catalogo-wrapper { width: 100%; max-width: 340px; }

.tl-transformacion__operario-row .small.font-weight-bold { font-size: .78rem; color: #475569; }

.tl-transformacion__params-edit-title {

    display: block; font-size: .72rem; font-weight: 700; text-transform: uppercase;

    letter-spacing: .03em; color: #64748b; margin-bottom: .45rem;

}

.tl-transformacion__param-label { font-size: .75rem; font-weight: 600; margin-bottom: .25rem; color: #334155; }

.tl-transformacion__param-inputs {

    display: flex; align-items: center; gap: .35rem;

}

.tl-transformacion__param-inputs .form-control { max-width: 5.5rem; background: #fff; }

.tl-transformacion__param-sep { color: #94a3b8; font-weight: 600; }

.tl-transformacion__param-maq { font-size: .68rem; margin-top: .15rem; }

.tl-transformacion__item--actual .tl-transformacion__param-inputs .form-control:focus {

    border-color: #2c5530; box-shadow: 0 0 0 2px rgba(44,85,48,.15);

}

.tl-transformacion__grip[draggable="true"] { cursor: grab; }

.tl-transformacion__grip[draggable="true"]:active { cursor: grabbing; }

</style>

@endpush

@endonce



@if($sortable)

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    const shell = document.getElementById('lp-timeline-visual');
    const jsonEl = document.getElementById('tlRutaPasosJson');
    if (!shell || !jsonEl) return;

    const NO_DRAG_SEL = 'input, textarea, button, select, a, .selector-catalogo-wrapper, .selector-filtros-field, .selector-filtros-field__open, .selector-filtros-field__clear';

    let pasosPayload = [];
    try { pasosPayload = JSON.parse(jsonEl.textContent || '[]'); } catch (e) { return; }

    let completados = parseInt((document.getElementById('tlTransformacionRoot') || {}).dataset?.completados || '0', 10);
    let rutaUrl = (document.getElementById('tlTransformacionRoot') || {}).dataset?.rutaUrl || '';
    let dragId = null;
    let guardando = false;
    let ultimoOrdenOk = JSON.stringify(pasosPayload);

    pasosPayload = asegurarCierreAlFinal(pasosPayload);
    if (JSON.stringify(pasosPayload) !== ultimoOrdenOk) {
        ultimoOrdenOk = JSON.stringify(pasosPayload);
        jsonEl.textContent = ultimoOrdenOk;
        reflejarOrdenEnDom();
        guardarRuta();
    }

    function getRoot() {
        return document.getElementById('tlTransformacionRoot');
    }

    function syncMetaRoot() {
        const root = getRoot();
        if (!root) return;
        completados = parseInt(root.dataset.completados || '0', 10);
        rutaUrl = root.dataset.rutaUrl || rutaUrl;
    }

    function nodosPorId() {
        const map = {};
        const root = getRoot();
        if (!root) return map;
        root.querySelectorAll('.tl-transformacion__item[data-ruta-paso-id]').forEach(function (el) {
            const prev = el.previousElementSibling;
            map[el.dataset.rutaPasoId] = {
                item: el,
                flow: prev && prev.classList.contains('tl-transformacion__flow') ? prev : null,
            };
        });
        return map;
    }

    function reflejarOrdenEnDom() {
        const root = getRoot();
        if (!root) return;
        const map = nodosPorId();
        pasosPayload.forEach(function (paso, idx) {
            const id = String(paso.loteproduccionrutapasoid || '');
            const pack = map[id];
            if (!pack) return;
            if (idx > 0 && pack.flow) root.appendChild(pack.flow);
            root.appendChild(pack.item);
        });
        actualizarNumerosYEstados();
        bindDragItems();
    }

    function actualizarFlujos() {
        const root = getRoot();
        if (!root) return;
        let prevItem = null;
        Array.from(root.children).forEach(function (node) {
            if (node.classList.contains('tl-transformacion__flow')) {
                node.classList.remove('tl-transformacion__flow--hecho', 'tl-transformacion__flow--actual');
                return;
            }
            if (!node.classList.contains('tl-transformacion__item')) {
                return;
            }

            const flow = node.previousElementSibling;
            if (flow && flow.classList.contains('tl-transformacion__flow')) {
                const esActual = node.classList.contains('tl-transformacion__item--actual')
                    || node.classList.contains('tl-transformacion__item--en-curso');
                const prevHecho = prevItem && prevItem.classList.contains('tl-transformacion__item--hecho');
                const curHecho = node.classList.contains('tl-transformacion__item--hecho');

                if (esActual) {
                    flow.classList.add('tl-transformacion__flow--actual');
                    flow.querySelector('.tl-transformacion__flow-arrow')?.setAttribute('title', 'Usted está aquí');
                } else if (prevHecho && curHecho) {
                    flow.classList.add('tl-transformacion__flow--hecho');
                    flow.querySelector('.tl-transformacion__flow-arrow')?.setAttribute('title', 'Completado');
                } else {
                    flow.querySelector('.tl-transformacion__flow-arrow')?.setAttribute('title', 'Siguiente paso');
                }
            }

            prevItem = node;
        });
    }

    function actualizarNumerosYEstados() {
        const root = getRoot();
        if (!root) return;
        let orden = 1;
        root.querySelectorAll('.tl-transformacion__item').forEach(function (el) {
            const hecho = el.classList.contains('tl-transformacion__item--hecho');
            const ordEl = el.querySelector('.tl-transformacion__orden');
            const head = el.querySelector('.tl-transformacion__head > div');
            if (hecho) {
                orden++;
                return;
            }
            if (ordEl && !ordEl.querySelector('.fa-check')) {
                ordEl.textContent = String(orden);
            }
            el.classList.remove('tl-transformacion__item--actual', 'tl-transformacion__item--bloqueado', 'tl-transformacion__item--plan');
            const esSiguiente = orden === completados + 1;
            const modoPlan = root.dataset.modoPlan === '1';
            if (modoPlan) {
                el.classList.add(esSiguiente ? 'tl-transformacion__item--actual' : 'tl-transformacion__item--plan');
            } else {
                el.classList.add(esSiguiente ? 'tl-transformacion__item--actual' : 'tl-transformacion__item--bloqueado');
            }
            if (head) {
                let badge = head.querySelector('.badge-success');
                let badgePlan = head.querySelector('.badge-light.border.text-muted');
                if (modoPlan) {
                    if (esSiguiente) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'badge badge-success ml-1';
                            badge.textContent = 'Empieza aquí';
                            head.appendChild(badge);
                        } else {
                            badge.textContent = 'Empieza aquí';
                        }
                        if (badgePlan) badgePlan.remove();
                    } else {
                        if (badge) badge.remove();
                        if (!badgePlan) {
                            badgePlan = document.createElement('span');
                            badgePlan.className = 'badge badge-light border text-muted ml-1';
                            badgePlan.textContent = 'Por asignar';
                            head.appendChild(badgePlan);
                        }
                    }
                } else if (esSiguiente) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'badge badge-success ml-1';
                        badge.textContent = 'Siguiente';
                        head.appendChild(badge);
                    }
                } else if (badge) {
                    badge.remove();
                }
            }
            orden++;
        });
        actualizarFlujos();
    }

    function idCierreProceso() {
        const root = getRoot();
        return root ? parseInt(root.dataset.cierreProcesoId || '0', 10) : 0;
    }

    function esCierrePaso(paso) {
        if (!paso) return false;
        if (paso.es_cierre) return true;
        const cierreId = idCierreProceso();
        return cierreId > 0 && parseInt(paso.procesoplantaid, 10) === cierreId;
    }

    function asegurarCierreAlFinal(pasos) {
        const fijos = pasos.slice(0, completados);
        const pendientes = pasos.slice(completados);
        const reordenables = [];
        let cierre = null;
        pendientes.forEach(function (p) {
            if (esCierrePaso(p)) {
                cierre = p;
            } else {
                reordenables.push(p);
            }
        });
        if (!cierre) {
            return pasos;
        }
        return fijos.concat(reordenables, [cierre]);
    }

    function partesOrdenables() {
        pasosPayload = asegurarCierreAlFinal(pasosPayload);
        const fijos = pasosPayload.slice(0, completados);
        const pendientes = pasosPayload.slice(completados);
        const reordenables = [];
        let anclaFinal = null;
        pendientes.forEach(function (p) {
            if (esCierrePaso(p)) {
                anclaFinal = p;
            } else {
                reordenables.push(p);
            }
        });
        return { fijos: fijos, reordenables: reordenables, anclaFinal: anclaFinal };
    }

    function aplicarReorden(fromId, toId) {
        if (guardando) return;
        const partes = partesOrdenables();
        const fromIdx = partes.reordenables.findIndex(function (p) { return String(p.loteproduccionrutapasoid) === String(fromId); });
        const toIdx = partes.reordenables.findIndex(function (p) { return String(p.loteproduccionrutapasoid) === String(toId); });
        if (fromIdx < 0 || toIdx < 0 || fromIdx === toIdx) return;

        const tmp = partes.reordenables[fromIdx];
        partes.reordenables[fromIdx] = partes.reordenables[toIdx];
        partes.reordenables[toIdx] = tmp;

        const pendientes = partes.anclaFinal
            ? partes.reordenables.concat([partes.anclaFinal])
            : partes.reordenables;
        pasosPayload = partes.fijos.concat(pendientes);
        pasosPayload = asegurarCierreAlFinal(pasosPayload);

        reflejarOrdenEnDom();
        guardarRuta();
    }

    function sincronizarVariablesDesdeDom() {
        const root = getRoot();
        if (!root || !root.querySelector('.tl-transformacion__plan-fields')) return;

        pasosPayload.forEach(function (paso) {
            const id = String(paso.loteproduccionrutapasoid || '');
            const item = root.querySelector('.tl-transformacion__item[data-ruta-paso-id="' + id + '"]');
            if (!item) return;

            const vars = [];
            item.querySelectorAll('.tl-plan-var-row').forEach(function (row) {
                const varId = parseInt(row.dataset.varId || '0', 10);
                const minInp = row.querySelector('.tl-plan-var-min');
                const maxInp = row.querySelector('.tl-plan-var-max');
                if (!varId || !minInp || !maxInp) return;
                vars.push({
                    variableestandarid: varId,
                    valor_minimo: parseFloat(minInp.value) || 0,
                    valor_maximo: parseFloat(maxInp.value) || 0,
                });
            });
            if (vars.length) {
                paso.variables = vars;
            }
        });
    }

    function guardarRuta() {
        syncMetaRoot();
        sincronizarVariablesDesdeDom();
        pasosPayload = asegurarCierreAlFinal(pasosPayload);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('#formLpRutaLote input[name="_token"]')?.value
            || document.querySelector('#formAsignarPlanEtapas input[name="_token"]')?.value
            || document.querySelector('#formAsignarEtapa input[name="_token"]')?.value;

        const body = new FormData();
        body.append('_token', token || '');
        body.append('_method', 'PUT');

        pasosPayload.forEach(function (paso, i) {
            if (paso.loteproduccionrutapasoid) {
                body.append('pasos[' + i + '][loteproduccionrutapasoid]', paso.loteproduccionrutapasoid);
            }
            body.append('pasos[' + i + '][procesoplantaid]', paso.procesoplantaid);
            if (paso.maquinaplantaid) body.append('pasos[' + i + '][maquinaplantaid]', paso.maquinaplantaid);
            if (paso.notas) body.append('pasos[' + i + '][notas]', paso.notas);
            (paso.variables || []).forEach(function (v, j) {
                body.append('pasos[' + i + '][variables][' + j + '][variableestandarid]', v.variableestandarid);
                body.append('pasos[' + i + '][variables][' + j + '][valor_minimo]', v.valor_minimo);
                body.append('pasos[' + i + '][variables][' + j + '][valor_maximo]', v.valor_maximo);
            });
        });

        guardando = true;
        const root = getRoot();
        if (root) root.classList.add('tl-transformacion--guardando');

        fetch(rutaUrl, {
            method: 'POST',
            body: body,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.ok) throw new Error((res.data && res.data.message) || 'No se pudo reordenar.');
                ultimoOrdenOk = JSON.stringify(pasosPayload);
                jsonEl.textContent = ultimoOrdenOk;
                if (res.data.etapa_asignar && window.LpActualizarEtapaAsignar) {
                    window.LpActualizarEtapaAsignar(res.data.etapa_asignar);
                }
            })
            .catch(function (err) {
                try { pasosPayload = JSON.parse(ultimoOrdenOk); } catch (e) {}
                reflejarOrdenEnDom();
                jsonEl.textContent = ultimoOrdenOk;
                alert((err && err.message) || 'No se pudo guardar el nuevo orden.');
            })
            .finally(function () {
                guardando = false;
                const r = getRoot();
                if (r) r.classList.remove('tl-transformacion--guardando');
            });
    }

    function esZonaInteractiva(el) {
        return !!(el && el.closest(NO_DRAG_SEL));
    }

    function setDragActivo(activo) {
        if (activo) {
            document.addEventListener('dragover', autoScrollDuranteDrag);
        } else {
            document.removeEventListener('dragover', autoScrollDuranteDrag);
        }
    }

    function autoScrollDuranteDrag(e) {
        if (!dragId) return;
        e.preventDefault();
        const margen = 100;
        const velocidad = 22;
        const y = e.clientY;
        const alto = window.innerHeight;
        if (y < margen) {
            window.scrollBy(0, -velocidad);
        } else if (y > alto - margen) {
            window.scrollBy(0, velocidad);
        }
        const scrollParent = shell.closest('.lp-fase-panel, .card-body, .lp-timeline-shell');
        if (scrollParent && scrollParent.scrollHeight > scrollParent.clientHeight) {
            const rect = scrollParent.getBoundingClientRect();
            if (y < rect.top + margen) {
                scrollParent.scrollTop -= velocidad;
            } else if (y > rect.bottom - margen) {
                scrollParent.scrollTop += velocidad;
            }
        }
    }

    function bindDragItems() {
        const root = getRoot();
        if (!root) return;
        root.querySelectorAll('.tl-transformacion__item[data-reordenable="1"]').forEach(function (item) {
            if (item.dataset.dndBound === '1') return;
            item.dataset.dndBound = '1';
            item.setAttribute('draggable', 'true');

            item.addEventListener('dragstart', function (e) {
                if (esZonaInteractiva(e.target)) {
                    e.preventDefault();
                    return;
                }
                if (guardando) {
                    e.preventDefault();
                    return;
                }
                dragId = item.dataset.rutaPasoId;
                item.classList.add('tl-transformacion__item--dragging');
                setDragActivo(true);
                if (e.dataTransfer) {
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', dragId);
                }
            });

            item.addEventListener('dragend', function () {
                item.classList.remove('tl-transformacion__item--dragging');
                root.querySelectorAll('.tl-transformacion__item--drag-over').forEach(function (el) {
                    el.classList.remove('tl-transformacion__item--drag-over');
                });
                dragId = null;
                setDragActivo(false);
            });

            item.addEventListener('dragover', function (e) {
                if (item.classList.contains('tl-transformacion__item--cierre-fijo') || guardando) return;
                if (!dragId || item.dataset.rutaPasoId === dragId) return;
                e.preventDefault();
                if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
                item.classList.add('tl-transformacion__item--drag-over');
            });

            item.addEventListener('dragleave', function (e) {
                if (!item.contains(e.relatedTarget)) {
                    item.classList.remove('tl-transformacion__item--drag-over');
                }
            });

            item.addEventListener('drop', function (e) {
                if (item.classList.contains('tl-transformacion__item--cierre-fijo')) return;
                e.preventDefault();
                item.classList.remove('tl-transformacion__item--drag-over');
                const toId = item.dataset.rutaPasoId;
                const fromId = dragId || (e.dataTransfer ? e.dataTransfer.getData('text/plain') : '');
                if (!fromId || !toId || fromId === toId) return;
                aplicarReorden(fromId, toId);
            });
        });
    }

    bindDragItems();
})();
});
</script>

@endpush

@endif

@if(!empty($modoPlan))

@push('scripts')

<script>
(function () {
    const form = document.getElementById('formAsignarPlanEtapas');
    if (!form) return;

    function acotarEnBlur(input) {
        const minL = parseFloat(input.dataset.rangoMin);
        const maxL = parseFloat(input.dataset.rangoMax);
        if (isNaN(minL) || isNaN(maxL)) return;
        let v = parseFloat(input.value);
        if (isNaN(v)) return;
        if (v < minL) input.value = minL;
        if (v > maxL) input.value = maxL;
    }

    form.querySelectorAll('.tl-plan-var-min, .tl-plan-var-max').forEach(function (inp) {
        inp.addEventListener('blur', function () { acotarEnBlur(inp); });
    });

    form.addEventListener('submit', function () {
        form.querySelectorAll('.tl-plan-var-min, .tl-plan-var-max').forEach(function (inp) {
            acotarEnBlur(inp);
        });
    });

    const wrapCerrarTodos = document.getElementById('lp-cerrar-todos-wrap');

    function operarioSeleccionadoEnCard(card) {
        const hidden = card.querySelector('.selector-catalogo-value, input[name*="operador_usuarioid"]');
        return hidden && String(hidden.value || '').trim() !== '';
    }

    function actualizarBotonesCerrarFase() {
        form.querySelectorAll('[data-plan-etapa="1"]').forEach(function (card) {
            const btn = card.querySelector('.js-cerrar-fase-single');
            if (!btn) return;
            const ok = operarioSeleccionadoEnCard(card);
            btn.disabled = !ok;
            btn.setAttribute('aria-disabled', ok ? 'false' : 'true');
            btn.title = ok ? '' : 'Seleccione un operario para cerrar la fase';
        });
    }

    function actualizarCerrarTodos() {
        if (!wrapCerrarTodos) return;
        const cards = form.querySelectorAll('[data-plan-etapa="1"]');
        if (!cards.length) {
            wrapCerrarTodos.classList.add('d-none');
            actualizarBotonesCerrarFase();
            return;
        }
        let todas = true;
        cards.forEach(function (card) {
            if (!operarioSeleccionadoEnCard(card)) todas = false;
        });
        wrapCerrarTodos.classList.toggle('d-none', !todas);
        actualizarBotonesCerrarFase();
    }

    document.addEventListener('selector-catalogo:change', actualizarCerrarTodos);
    form.addEventListener('change', actualizarCerrarTodos);
    actualizarCerrarTodos();
})();
</script>

@endpush

@endif


