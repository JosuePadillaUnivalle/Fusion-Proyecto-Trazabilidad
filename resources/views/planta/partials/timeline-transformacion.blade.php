@php

    $items = $items ?? [];

    $compacto = !empty($compacto);

    $titulo = $titulo ?? null;

    $sortable = !empty($sortable);

    $rutaUrl = $rutaUrl ?? '';

    $rutaPasosJson = $rutaPasosJson ?? [];

    $etapasCompletadas = (int) ($etapasCompletadas ?? 0);

@endphp



@if($titulo)

<div class="small font-weight-bold text-success mb-2"><i class="fas fa-route mr-1"></i>{{ $titulo }}</div>

@endif



@if(empty($items))

<p class="small text-muted mb-0">Sin etapas definidas aún.</p>

@else

@if($sortable)

<p class="small text-muted mb-2 tl-transformacion__hint">

    <i class="fas fa-arrows-alt-v mr-1"></i> Arrastre una carta sobre otra para <strong>intercambiar</strong> su posición. <i class="fas fa-lock text-muted mx-1"></i> Empaquetado queda fijo al final.

</p>

@endif

<div class="tl-transformacion {{ $compacto ? 'tl-transformacion--compact' : '' }}"

     id="tlTransformacionRoot"

     @if($sortable) data-sortable="1" data-ruta-url="{{ $rutaUrl }}" data-completados="{{ $etapasCompletadas }}" data-cierre-proceso-id="{{ \App\Support\ProcesoPlantaCatalogo::idProcesoCierreTransformacion() ?? '' }}" @endif>

    @foreach($items as $idx => $paso)

        @php

            $estado = $paso['estado'] ?? 'bloqueado';

            $estadoClass = match ($estado) {

                'hecho' => 'tl-transformacion__item--hecho',

                'actual' => 'tl-transformacion__item--actual',

                'en_curso' => 'tl-transformacion__item--en-curso',

                default => 'tl-transformacion__item--bloqueado',

            };

            $prevEstado = $idx > 0 ? ($items[$idx - 1]['estado'] ?? '') : '';

            $flowActual = in_array($estado, ['actual', 'en_curso'], true);

            $flowHecho = $prevEstado === 'hecho' && $estado === 'hecho';

            $esCierre = ! empty($paso['es_cierre']);

            $reordenable = $sortable && $estado !== 'hecho' && ! empty($paso['editable']) && ! $esCierre;

        @endphp



        @if($idx > 0)

        <div class="tl-transformacion__flow{{ $flowActual ? ' tl-transformacion__flow--actual' : '' }}{{ $flowHecho ? ' tl-transformacion__flow--hecho' : '' }}">

            <div class="tl-transformacion__flow-arrow" title="{{ $flowActual ? 'Usted está aquí' : 'Siguiente paso' }}">

                <i class="fas fa-long-arrow-alt-down"></i>

            </div>

        </div>

        @endif



        <div class="tl-transformacion__item {{ $estadoClass }} {{ $reordenable ? 'tl-transformacion__item--reordenable' : '' }} {{ $esCierre && $estado !== 'hecho' ? 'tl-transformacion__item--cierre-fijo' : '' }}"

             @if(!empty($paso['loteproduccionrutapasoid']))

                 data-ruta-paso-id="{{ (int) $paso['loteproduccionrutapasoid'] }}"

                 @if($esCierre) data-es-cierre="1" @endif

             @endif

             @if($reordenable)

                 draggable="true"

             @endif>

            @if($reordenable)

            <div class="tl-transformacion__grip" title="Arrastrar para intercambiar posición">

                <i class="fas fa-grip-vertical"></i>

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

                        @if($estado === 'actual')<span class="badge badge-success ml-1">Siguiente</span>@endif

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

                @if(!empty($paso['parametros_rango']))

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

    display: flex; gap: .75rem; align-items: flex-start; position: relative;

    padding: .8rem .9rem; margin-bottom: 0;

    background: #fff; border: 1px solid #e2ebe3; border-radius: 14px;

    box-shadow: 0 2px 10px rgba(30,70,32,.04);

    transition: box-shadow .2s ease, transform .15s ease, border-color .2s ease;

}

.tl-transformacion__item--reordenable { cursor: grab; }

.tl-transformacion__item--reordenable:active { cursor: grabbing; }

.tl-transformacion__item--dragging { opacity: .55; transform: scale(.985); }

.tl-transformacion--guardando { opacity: .92; }

.tl-transformacion__item--drag-over { border-color: #2c5530; box-shadow: 0 0 0 3px rgba(44,85,48,.18); }

.tl-transformacion__item--hecho { background: linear-gradient(180deg, #f0fdf4, #fff); border-color: #bbf7d0; }

.tl-transformacion__item--actual { border-color: #2c5530; box-shadow: 0 0 0 2px rgba(44,85,48,.12); }

.tl-transformacion__item--en-curso { border-color: #f59e0b; }

.tl-transformacion__item--bloqueado { opacity: .72; }

.tl-transformacion__item--cierre-fijo { border-style: dashed; border-color: #94a3b8; background: #f8fafc; }

.tl-transformacion__grip {

    color: #94a3b8; padding: .15rem .1rem; margin-top: .15rem; flex-shrink: 0;

}

.tl-transformacion__grip--lock { color: #64748b; cursor: not-allowed; }

.tl-transformacion__media {

    width: 68px; height: 68px; flex-shrink: 0; border-radius: 12px;

    border: 2px solid #e2ebe3; background: #f8faf8;

    display: flex; align-items: center; justify-content: center; overflow: hidden;

}

.tl-transformacion--compact .tl-transformacion__media { width: 52px; height: 52px; border-radius: 10px; }

.tl-transformacion__media img { width: 100%; height: 100%; object-fit: contain; padding: .25rem; }

.tl-transformacion__media i { font-size: 1.5rem; color: #94a3b8; }

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

</style>

@endpush

@endonce



@if($sortable)

@push('scripts')

<script>
(function () {
    const shell = document.getElementById('lp-timeline-visual');
    const jsonEl = document.getElementById('tlRutaPasosJson');
    if (!shell || !jsonEl) return;

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
            el.classList.remove('tl-transformacion__item--actual', 'tl-transformacion__item--bloqueado');
            const esSiguiente = orden === completados + 1;
            el.classList.add(esSiguiente ? 'tl-transformacion__item--actual' : 'tl-transformacion__item--bloqueado');
            if (head) {
                let badge = head.querySelector('.badge-success');
                if (esSiguiente) {
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

    function guardarRuta() {
        syncMetaRoot();
        pasosPayload = asegurarCierreAlFinal(pasosPayload);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('#formLpRutaLote input[name="_token"]')?.value
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

    shell.addEventListener('dragstart', function (e) {
        const item = e.target.closest('.tl-transformacion__item--reordenable');
        if (!item || guardando) { e.preventDefault(); return; }
        dragId = item.dataset.rutaPasoId;
        item.classList.add('tl-transformacion__item--dragging');
        if (e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', dragId);
        }
    });

    shell.addEventListener('dragend', function (e) {
        const item = e.target.closest('.tl-transformacion__item--reordenable');
        if (item) item.classList.remove('tl-transformacion__item--dragging');
        shell.querySelectorAll('.tl-transformacion__item--drag-over').forEach(function (el) {
            el.classList.remove('tl-transformacion__item--drag-over');
        });
        dragId = null;
    });

    shell.addEventListener('dragover', function (e) {
        const item = e.target.closest('.tl-transformacion__item--reordenable');
        if (!item || item.classList.contains('tl-transformacion__item--cierre-fijo') || guardando) return;
        e.preventDefault();
        if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
        item.classList.add('tl-transformacion__item--drag-over');
    });

    shell.addEventListener('dragleave', function (e) {
        const item = e.target.closest('.tl-transformacion__item--reordenable');
        if (item) item.classList.remove('tl-transformacion__item--drag-over');
    });

    shell.addEventListener('drop', function (e) {
        const item = e.target.closest('.tl-transformacion__item--reordenable');
        if (!item || item.classList.contains('tl-transformacion__item--cierre-fijo')) return;
        e.preventDefault();
        item.classList.remove('tl-transformacion__item--drag-over');
        const fromId = dragId || (e.dataTransfer ? e.dataTransfer.getData('text/plain') : '');
        const toId = item.dataset.rutaPasoId;
        if (!fromId || !toId || fromId === toId) return;
        aplicarReorden(fromId, toId);
    });
})();
</script>

@endpush

@endif


