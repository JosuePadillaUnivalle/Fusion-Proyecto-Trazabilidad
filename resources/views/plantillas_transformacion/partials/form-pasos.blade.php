@php
    $pasosIniciales = $pasosIniciales ?? [['procesoplantaid' => '', 'maquinaplantaid' => '', 'notas' => '', 'variables' => []]];
    $procesoCierreId = $procesoCierreId ?? \App\Support\ProcesoPlantaCatalogo::idProcesoCierreTransformacion();
    $procesoCierreNombre = \App\Support\ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION;
    $mapaMaquinasProceso = $mapaMaquinasProceso ?? \App\Support\MaquinaProcesoCompatibilidad::mapaMaquinasFormulario();
    $variablesCatalogo = $variablesCatalogo ?? \App\Models\VariableEstandar::where('activo', true)->orderBy('nombre')->get();
    $variablesMetaJson = $variablesCatalogo->map(fn ($v) => [
        'id' => (int) $v->variableestandarid,
        'nombre' => $v->nombre,
        'unidad' => $v->unidad,
    ])->values();
    $urlVariablesMaquina = $urlVariablesMaquina ?? route('maquinas-planta.variables-sugeridas', ['maquinas_plantum' => 0]);
    $urlVariablesMaquina = preg_replace('/\/0\/variables-sugeridas$/', '/__ID__/variables-sugeridas', $urlVariablesMaquina);
@endphp

<div class="plantilla-pasos-builder">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:.5rem">
        <div>
            <label class="font-weight-bold mb-0 text-success"><i class="fas fa-stream mr-1"></i> Etapas de la línea</label>
            <p class="small text-muted mb-0">Arrastre para intercambiar pasos · último paso = <strong>{{ $procesoCierreNombre }}</strong> (fijo)</p>
        </div>
        <button type="button" class="btn btn-sm btn-success" id="btnAgregarPasoPlantilla">
            <i class="fas fa-plus mr-1"></i> Agregar paso
        </button>
    </div>
    <div id="alertEmpaquetadoFinal" class="alert alert-warning d-none mb-2" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i><span id="alertEmpaquetadoFinalTexto"></span>
    </div>
    @error('pasos')<div class="alert alert-danger mb-2"><i class="fas fa-times-circle mr-1"></i>{{ $message }}</div>@enderror
    <div id="listaPasosPlantilla" class="lista-pasos-plantilla"></div>
    <div id="zonaDropFinalPasos" class="paso-drop-final-zone" aria-hidden="true">
        <i class="fas fa-arrow-down mr-1"></i> Suelte aquí para colocar antes de <strong>{{ $procesoCierreNombre }}</strong>
    </div>
</div>

@include('plantillas_transformacion.partials.modal-maquina-paso')

<template id="tplPasoPlantilla">
    <div class="paso-plantilla-row">
        <div class="paso-card">
            <div class="paso-card__head">
                <span class="paso-drag-handle" title="Arrastrar"><i class="fas fa-grip-vertical"></i></span>
                <span class="badge paso-orden-badge badge-success">Paso 1</span>
                <button type="button" class="btn btn-sm btn-link text-danger btn-quitar-paso ml-auto p-0" title="Quitar"><i class="fas fa-times"></i></button>
            </div>
            <div class="paso-card__body">
                <div class="paso-card__media" role="button" tabindex="0" title="Clic para elegir máquina">
                    <img class="paso-maquina-img d-none" src="" alt="">
                    <div class="paso-maquina-placeholder">
                        <i class="fas fa-industry"></i>
                        <span>Sin máquina</span>
                    </div>
                </div>
                <div class="paso-card__fields">
                    <div class="form-row">
                        <div class="col-md-5 form-group mb-2">
                            <label class="small font-weight-bold mb-0">Proceso</label>
                            <select class="form-control form-control-sm paso-proceso" required>
                                <option value="">Seleccionar…</option>
                                @foreach($procesos as $proc)
                                    <option value="{{ $proc->procesoplantaid }}" @if((int) $proc->procesoplantaid === (int) $procesoCierreId) data-es-cierre="1" @endif>{{ $proc->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="paso-hint-cierre text-muted d-none"></small>
                        </div>
                        <div class="col-md-4 form-group mb-2">
                            <label class="small font-weight-bold mb-0">Máquina sugerida</label>
                            <input type="hidden" class="paso-maquina" value="">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-block btn-elegir-maquina-paso" disabled>
                                <i class="fas fa-search mr-1"></i> Elegir máquina
                            </button>
                            <div class="paso-maquina-label small text-muted mt-1">Cualquiera compatible</div>
                            <button type="button" class="btn btn-link btn-sm p-0 paso-limpiar-maquina d-none">Quitar selección</button>
                        </div>
                        <div class="col-md-3 form-group mb-2">
                            <label class="small font-weight-bold mb-0">Notas</label>
                            <input type="text" class="form-control form-control-sm paso-notas" maxlength="255" placeholder="Ej. Pelado fino">
                        </div>
                    </div>
                    <div class="paso-variables-wrap">
                        <div class="mb-1">
                            <span class="small font-weight-bold text-secondary"><i class="fas fa-sliders-h mr-1"></i>Rangos del paso</span>
                        </div>
                        <div class="paso-variables-lista"></div>
                        <p class="small text-muted mb-0 paso-vars-vacio">Elija una máquina para cargar los parámetros definidos en el equipo.</p>
                        <p class="small text-muted mb-0 mt-1 paso-vars-leyenda">Solo se usan las variables de la máquina. Ajuste el rango del paso dentro del límite estándar del equipo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="tplVarPaso">
    <div class="paso-var-row">
        <select class="form-control form-control-sm paso-var-select paso-var-select--fija" required disabled aria-readonly="true">
            <option value="">Variable…</option>
            @foreach($variablesCatalogo as $var)
                <option value="{{ $var->variableestandarid }}">{{ $var->nombre }}@if($var->unidad) ({{ $var->unidad }})@endif</option>
            @endforeach
        </select>
        <input type="hidden" class="paso-var-id-hidden" value="">
        <div class="paso-var-campo">
            <input type="number" step="0.01" class="form-control form-control-sm paso-var-min" placeholder="Mín" required>
            <span class="paso-var-lim-maq paso-var-lim-min"></span>
        </div>
        <div class="paso-var-campo">
            <input type="number" step="0.01" class="form-control form-control-sm paso-var-max" placeholder="Máx" required>
            <span class="paso-var-lim-maq paso-var-lim-max"></span>
        </div>
        <input type="hidden" class="paso-var-oblig" value="1">
    </div>
</template>

@push('styles')
<style>
.paso-plantilla-row { margin-bottom: .85rem; transition: transform .2s ease; }
.paso-plantilla-row.paso-dragging { opacity: .45; transform: scale(1.02); z-index: 10; position: relative; }
.paso-plantilla-row.paso-dragging .paso-card { box-shadow: 0 16px 40px rgba(30,70,32,.22); border-color: #28a745; }
.paso-plantilla-row.paso-drag-over { transform: translateY(4px); }
.paso-plantilla-row.paso-drag-over .paso-card { border-color: #28a745 !important; border-style: dashed !important; background: #f0fdf4; }
.paso-card {
    border: 1px solid #d4e5d6; border-radius: 12px; background: #fff;
    box-shadow: 0 2px 8px rgba(30,70,32,.06); overflow: hidden;
}
.paso-card__head {
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .85rem; background: linear-gradient(90deg,#f0f7f1,#fff);
    border-bottom: 1px solid #e8f0e9;
}
.paso-drag-handle { cursor: grab; color: #94a3b8; padding: .2rem .35rem; }
.paso-drag-handle:active { cursor: grabbing; }
.paso-card__body { display: flex; gap: 1rem; padding: .85rem; flex-wrap: wrap; }
.paso-card__media {
    width: 110px; flex-shrink: 0; border: 2px solid #e2ebe3; border-radius: 10px;
    background: #f8faf8; min-height: 110px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: border-color .15s;
}
.paso-card__media:hover { border-color: #28a745; }
.paso-card__media img { width: 100%; height: 106px; object-fit: contain; padding: .35rem; }
.paso-maquina-placeholder { text-align: center; color: #94a3b8; font-size: .72rem; padding: .5rem; }
.paso-maquina-placeholder i { font-size: 1.75rem; display: block; margin-bottom: .35rem; opacity: .5; }
.paso-card__fields { flex: 1; min-width: 240px; }
.paso-variables-wrap { border-top: 1px dashed #e2ebe3; padding-top: .65rem; margin-top: .35rem; }
.paso-var-row {
    display: grid; grid-template-columns: 1.5fr .85fr .85fr; gap: .4rem; align-items: start;
    background: #f8fbf8; border-radius: 8px; padding: .4rem .5rem; margin-bottom: .35rem;
}
.paso-var-select--fija:disabled { background: #f1f5f9; color: #334155; cursor: default; opacity: 1; -webkit-text-fill-color: #334155; }
.paso-var-campo { display: flex; flex-direction: column; gap: .12rem; min-width: 0; }
.paso-var-lim-maq { font-size: .67rem; line-height: 1.2; color: #64748b; min-height: .8rem; }
.paso-plantilla-row.paso-fijo-empaque .paso-drag-handle { cursor: not-allowed; opacity: .55; }
.paso-plantilla-row.paso-fijo-empaque .paso-card { border-color: #93c5fd; }
.paso-plantilla-row.paso-drag-over-swap .paso-card { border-color: #28a745 !important; border-width: 2px; background: #f0fdf4; }
.paso-drop-final-zone {
    display: none; margin-top: .5rem; padding: .65rem 1rem; border: 2px dashed #94c9a8;
    border-radius: 10px; background: #f0fdf4; color: #166534; font-size: .82rem;
}
.paso-drop-final-zone.is-active { display: block; }
.paso-drop-final-zone.is-hover { border-color: #28a745; background: #dcfce7; }
.lista-pasos-plantilla.is-dragging-pasos { min-height: 48px; }
.modal-maq-paso-card {
    border: 2px solid #e5e7eb; border-radius: 12px; background: #fff; cursor: pointer;
    transition: all .15s; height: 100%; overflow: hidden;
}
.modal-maq-paso-card:hover, .modal-maq-paso-card.is-selected { border-color: #28a745; box-shadow: 0 4px 14px rgba(40,167,69,.15); }
.modal-maq-paso-card__img {
    height: 140px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden;
}
.modal-maq-paso-card__img img { width: 100%; height: 100%; object-fit: contain; padding: .5rem; }
.modal-maq-paso-card__body { padding: .65rem .75rem; }
.modal-maq-paso-card__nombre { font-weight: 700; font-size: .88rem; color: #1e293b; }
.modal-maq-paso-card__meta { font-size: .72rem; color: #64748b; }
.modal-maq-paso-card--any { border-style: dashed; text-align: center; padding: 1.5rem .75rem; }
@media (max-width: 768px) {
    .paso-var-row { grid-template-columns: 1fr 1fr; }
}
.paso-plantilla-row.paso-error-empaque .paso-card { border-color: #f59e0b; box-shadow: 0 0 0 1px rgba(245,158,11,.35); }
.paso-hint-cierre.is-warning { color: #b45309; font-weight: 600; }
@keyframes pasoFlash {
    0%, 100% { background: #fff; }
    50% { background: #ecfdf5; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const lista = document.getElementById('listaPasosPlantilla');
    const tpl = document.getElementById('tplPasoPlantilla');
    const tplVar = document.getElementById('tplVarPaso');
    const alerta = document.getElementById('alertEmpaquetadoFinal');
    const alertaTexto = document.getElementById('alertEmpaquetadoFinalTexto');
    const procesoCierreId = @json($procesoCierreId);
    const procesoCierreNombre = @json($procesoCierreNombre);
    const mapaMaquinasProceso = @json($mapaMaquinasProceso);
    const urlVariablesMaquina = @json($urlVariablesMaquina);
    const iniciales = @json($pasosIniciales);
    const catalogoVariables = @json($variablesMetaJson);
    const limitesMaquinaCache = {};
    const zonaDropFinal = document.getElementById('zonaDropFinalPasos');
    const modal = document.getElementById('modalMaquinaPasoPlantilla');
    const modalGrid = document.getElementById('modalMaquinaPasoGrid');
    const modalBuscar = document.getElementById('modalMaquinaPasoBuscar');
    const modalVacio = document.getElementById('modalMaquinaPasoVacio');
    const modalContador = document.getElementById('modalMaquinaPasoContador');
    if (!lista || !tpl) return;

    const form = lista.closest('form');
    const btnGuardar = form?.querySelector('button[type="submit"]');
    let dragSrc = null;
    let filaModalActiva = null;

    function idCierre() {
        return procesoCierreId ? String(procesoCierreId) : (tpl.content.querySelector('option[data-es-cierre]')?.value || null);
    }
    function filas() { return lista.querySelectorAll('.paso-plantilla-row'); }
    function ultimaFila() { const r = filas(); return r.length ? r[r.length - 1] : null; }
    function ultimoProcesoId() { return ultimaFila()?.querySelector('.paso-proceso')?.value || null; }
    function todosPasosTienenProceso() {
        const s = lista.querySelectorAll('.paso-proceso');
        return s.length > 0 && [...s].every(x => x.value);
    }
    function filasCierreIndices() {
        const cierreId = idCierre();
        if (!cierreId) return [];
        return [...filas()].map((row, idx) => row.querySelector('.paso-proceso')?.value === cierreId ? idx : -1).filter(i => i >= 0);
    }

    function esPasoEmpaquetado(row) {
        const cierreId = idCierre();
        return !!(cierreId && row?.querySelector('.paso-proceso')?.value === cierreId);
    }

    function filaEmpaquetado() {
        return [...filas()].find(esPasoEmpaquetado) || null;
    }

    function avisoEmpaquetadoFijo() {
        mostrarAvisoGuardar(
            '«' + procesoCierreNombre + '» siempre queda como último paso y no se puede mover.',
            'Paso fijo'
        );
    }

    function actualizarHintsMaquina(vr, row) {
        const sel = vr.querySelector('.paso-var-select');
        const limMin = vr.querySelector('.paso-var-lim-min');
        const limMax = vr.querySelector('.paso-var-lim-max');
        if (!sel || !limMin || !limMax) return;

        const maqId = row?.querySelector('.paso-maquina')?.value;
        const varId = sel.value;
        const maqRaw = maqId && varId ? limitesMaquinaParaVar(maqId, varId) : null;

        if (maqRaw) {
            limMin.textContent = 'Máq. mín. ' + maqRaw.valor_minimo;
            limMax.textContent = 'Máq. máx. ' + maqRaw.valor_maximo;
        } else {
            limMin.textContent = '';
            limMax.textContent = '';
        }
    }

    function mensajeEmpaquetadoError() {
        const cierreId = idCierre();
        const rows = [...filas()];
        const indices = filasCierreIndices();

        if (indices.length > 1) {
            return 'Solo puede incluir «' + procesoCierreNombre + '» una vez en la línea.';
        }
        if (indices.length === 1 && indices[0] !== rows.length - 1) {
            return '«' + procesoCierreNombre + '» debe ser el último paso. Arrástrelo al final de la línea (use la zona «Suelte aquí») o elimine los pasos que van después.';
        }
        if (rows.length > 0 && cierreId) {
            const ultimoVal = rows[rows.length - 1].querySelector('.paso-proceso')?.value;
            if (ultimoVal && ultimoVal !== cierreId) {
                return 'El último paso de la línea debe ser «' + procesoCierreNombre + '».';
            }
        }
        return '';
    }

    function mostrarAvisoGuardar(mensaje, titulo) {
        titulo = titulo || 'No se puede guardar';
        if (window.ModalConfirmar?.aviso) {
            ModalConfirmar.aviso({ titulo: titulo, mensaje: mensaje, tono: 'warning' });
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: titulo, text: mensaje, icon: 'warning', confirmButtonText: 'Entendido' });
            return;
        }
        alert(mensaje);
    }

    function limitesVariable(varId) {
        const meta = catalogoVariables.find(v => String(v.id) === String(varId));
        if (!meta?.unidad) return null;
        const u = String(meta.unidad).toLowerCase();
        const m = u.match(/escala\s*(\d+(?:[.,]\d+)?)\s*[-–]\s*(\d+(?:[.,]\d+)?)/);
        if (m) {
            return {
                min: parseFloat(m[1].replace(',', '.')),
                max: parseFloat(m[2].replace(',', '.')),
            };
        }
        return null;
    }

    function limitesMaquinaParaVar(maquinaId, varId) {
        const list = limitesMaquinaCache[String(maquinaId)] || [];
        return list.find(v => String(v.variableestandarid) === String(varId)) || null;
    }

    function combinarLimites(maq, escala) {
        if (!maq && !escala) return null;
        if (!maq) return escala;
        if (!escala) return maq;
        const min = Math.max(maq.min, escala.min);
        const max = Math.min(maq.max, escala.max);
        if (max < min) return maq;
        return { min, max };
    }

    function limitesPermitidosFila(vr, row) {
        const maqId = row?.querySelector('.paso-maquina')?.value;
        const varId = vr.querySelector('.paso-var-select')?.value;
        const maqRaw = maqId && varId ? limitesMaquinaParaVar(maqId, varId) : null;
        const maq = maqRaw ? { min: parseFloat(maqRaw.valor_minimo), max: parseFloat(maqRaw.valor_maximo) } : null;
        return combinarLimites(maq, limitesVariable(varId));
    }

    function acotarInputRango(input, lim) {
        if (!input || !lim) return;
        let v = parseFloat(input.value);
        if (!Number.isFinite(v)) return;
        if (v < lim.min) input.value = String(lim.min);
        if (v > lim.max) input.value = String(lim.max);
    }

    function aplicarLimitesVarFila(vr, row) {
        const sel = vr.querySelector('.paso-var-select');
        const minIn = vr.querySelector('.paso-var-min');
        const maxIn = vr.querySelector('.paso-var-max');
        if (!sel || !minIn || !maxIn) return;

        const refresh = () => {
            const lim = limitesPermitidosFila(vr, row);
            actualizarHintsMaquina(vr, row);
            if (lim) {
                minIn.min = lim.min;
                minIn.max = lim.max;
                maxIn.min = lim.min;
                maxIn.max = lim.max;
                const maqId = row?.querySelector('.paso-maquina')?.value;
                const maqRaw = maqId ? limitesMaquinaParaVar(maqId, sel.value) : null;
                const hintMaq = maqRaw ? ' (máquina ' + maqRaw.valor_minimo + '–' + maqRaw.valor_maximo + ')' : '';
                minIn.title = 'Rango permitido: ' + lim.min + ' – ' + lim.max + hintMaq;
                maxIn.title = minIn.title;
                if (document.activeElement !== minIn && document.activeElement !== maxIn) {
                    acotarInputRango(minIn, lim);
                    acotarInputRango(maxIn, lim);
                    if (parseFloat(minIn.value) > parseFloat(maxIn.value)) {
                        maxIn.value = minIn.value;
                    }
                }
            } else {
                ['min', 'max', 'title'].forEach(attr => {
                    minIn.removeAttribute(attr);
                    maxIn.removeAttribute(attr);
                });
            }
        };

        const acotarAlSalir = () => {
            const lim = limitesPermitidosFila(vr, row);
            if (!lim) return;
            acotarInputRango(minIn, lim);
            acotarInputRango(maxIn, lim);
            if (parseFloat(minIn.value) > parseFloat(maxIn.value)) {
                maxIn.value = minIn.value;
            }
        };

        const acotarMaxAlSalir = () => {
            const lim = limitesPermitidosFila(vr, row);
            if (!lim) return;
            acotarInputRango(maxIn, lim);
            acotarInputRango(minIn, lim);
            if (parseFloat(minIn.value) > parseFloat(maxIn.value)) {
                minIn.value = maxIn.value;
            }
        };

        sel.addEventListener('change', refresh);
        minIn.addEventListener('blur', acotarAlSalir);
        maxIn.addEventListener('blur', acotarMaxAlSalir);
        refresh();
    }

    function refrescarLimitesPaso(row) {
        row.querySelectorAll('.paso-var-row').forEach(vr => aplicarLimitesVarFila(vr, row));
    }

    function validarReglasEmpaquetado() {
        const rows = [...filas()];
        const indices = filasCierreIndices();

        filas().forEach(r => r.classList.remove('paso-error-empaque'));

        if (indices.length > 1) {
            indices.forEach(i => rows[i]?.classList.add('paso-error-empaque'));
        } else if (indices.length === 1 && indices[0] !== rows.length - 1) {
            rows[indices[0]]?.classList.add('paso-error-empaque');
        }

        if (alerta) alerta.classList.add('d-none');

        return mensajeEmpaquetadoError() === '';
    }

    function actualizarInterfazPaso() {
        const rows = [...filas()];
        rows.forEach((row, idx) => {
            const hint = row.querySelector('.paso-hint-cierre');
            if (!hint) return;
            hint.classList.add('d-none');
            hint.classList.remove('is-warning');
            hint.innerHTML = '';
            const val = row.querySelector('.paso-proceso')?.value;
            const cierreId = idCierre();
            if (cierreId && val === cierreId && idx !== rows.length - 1) {
                hint.classList.remove('d-none');
                hint.classList.add('is-warning');
                hint.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>' + procesoCierreNombre + ' debe quedar al final de la línea.';
            } else if (idx === rows.length - 1 && rows.length > 0 && cierreId && val === cierreId) {
                hint.classList.remove('d-none');
                hint.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Último paso: <strong>' + procesoCierreNombre + '</strong>.';
            }
        });
        if (btnGuardar) btnGuardar.disabled = false;
        actualizarBloqueoEmpaquetado();
    }

    function actualizarBloqueoEmpaquetado() {
        filas().forEach(row => {
            const fijo = esPasoEmpaquetado(row);
            row.classList.toggle('paso-fijo-empaque', fijo);
            const handle = row.querySelector('.paso-drag-handle');
            if (handle) {
                handle.setAttribute('draggable', fijo ? 'false' : 'true');
                handle.title = fijo ? procesoCierreNombre + ' — paso fijo al final' : 'Arrastrar para intercambiar posición';
                handle.innerHTML = fijo
                    ? '<i class="fas fa-lock"></i>'
                    : '<i class="fas fa-grip-vertical"></i>';
            }
        });
    }

    function validarAlGuardar() {
        if (!todosPasosTienenProceso()) {
            mostrarAvisoGuardar('Complete el proceso de todos los pasos antes de guardar.');
            return false;
        }
        const msg = mensajeEmpaquetadoError();
        if (msg) {
            validarReglasEmpaquetado();
            mostrarAvisoGuardar(msg, 'Empaquetado debe ser el último paso');
            const filaEmp = [...filas()].find((r, idx) => {
                const indices = filasCierreIndices();
                return indices.length === 1 && indices[0] === idx;
            });
            filaEmp?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    }

    function maquinaData(procesoId, maquinaId) {
        return (mapaMaquinasProceso[procesoId] || []).find(m => String(m.id) === String(maquinaId)) || null;
    }

    function renumerarNombresPaso(row, idx) {
        row.querySelector('.paso-proceso').name = 'pasos[' + idx + '][procesoplantaid]';
        row.querySelector('.paso-maquina').name = 'pasos[' + idx + '][maquinaplantaid]';
        row.querySelector('.paso-notas').name = 'pasos[' + idx + '][notas]';
        row.querySelectorAll('.paso-var-row').forEach((vr, vi) => {
            const b = 'pasos[' + idx + '][variables][' + vi + ']';
            const sel = vr.querySelector('.paso-var-select');
            const hid = vr.querySelector('.paso-var-id-hidden');
            if (hid) {
                hid.name = b + '[variableestandarid]';
                hid.value = sel?.value || '';
            }
            vr.querySelector('.paso-var-min').name = b + '[valor_minimo]';
            vr.querySelector('.paso-var-max').name = b + '[valor_maximo]';
            const o = vr.querySelector('.paso-var-oblig');
            o.name = b + '[obligatorio]'; o.value = '1';
        });
        const vac = row.querySelector('.paso-vars-vacio');
        if (vac) vac.classList.toggle('d-none', row.querySelectorAll('.paso-var-row').length > 0);
    }

    function renumerar() {
        filas().forEach((row, idx) => {
            const badge = row.querySelector('.paso-orden-badge');
            if (badge) {
                badge.textContent = 'Paso ' + (idx + 1);
                badge.className = 'badge paso-orden-badge ' + (idx === filas().length - 1 && filas().length > 1 ? 'badge-info' : 'badge-success');
            }
            renumerarNombresPaso(row, idx);
        });
        validarReglasEmpaquetado();
        actualizarInterfazPaso();
    }

    function setMaquinaEnPaso(row, maquinaId, cargarVars) {
        const hid = row.querySelector('.paso-maquina');
        const label = row.querySelector('.paso-maquina-label');
        const btnLimpiar = row.querySelector('.paso-limpiar-maquina');
        const procId = row.querySelector('.paso-proceso')?.value;
        const img = row.querySelector('.paso-maquina-img');
        const ph = row.querySelector('.paso-maquina-placeholder');
        hid.value = maquinaId ? String(maquinaId) : '';
        const m = maquinaId ? maquinaData(procId, maquinaId) : null;
        if (label) label.textContent = m ? m.nombre + (m.codigo ? ' (' + m.codigo + ')' : '') : 'Cualquiera compatible';
        btnLimpiar?.classList.toggle('d-none', !maquinaId);
        const src = m?.imagen_src || null;
        if (src && img) { img.src = src; img.classList.remove('d-none'); ph?.classList.add('d-none'); }
        else { img?.classList.add('d-none'); img?.removeAttribute('src'); ph?.classList.remove('d-none'); }
        if (cargarVars && maquinaId) cargarVariablesMaquina(row, maquinaId, true);
    }

    function fijarSelectVariable(vr) {
        const sel = vr.querySelector('.paso-var-select');
        const hid = vr.querySelector('.paso-var-id-hidden');
        if (!sel || !sel.value) return;
        const opt = sel.querySelector('option[value="' + sel.value + '"]');
        if (opt) {
            sel.innerHTML = '';
            sel.appendChild(opt.cloneNode(true));
            sel.value = opt.value;
        }
        sel.disabled = true;
        if (hid) hid.value = sel.value;
    }

    function agregarVarPaso(row, data) {
        if (!tplVar) return;
        const node = tplVar.content.cloneNode(true);
        const vr = node.querySelector('.paso-var-row');
        if (data?.variableestandarid) vr.querySelector('.paso-var-select').value = String(data.variableestandarid);
        if (data?.valor_minimo != null) vr.querySelector('.paso-var-min').value = data.valor_minimo;
        if (data?.valor_maximo != null) vr.querySelector('.paso-var-max').value = data.valor_maximo;
        fijarSelectVariable(vr);
        row.querySelector('.paso-variables-lista').appendChild(node);
        aplicarLimitesVarFila(vr, row);
        renumerar();
    }

    function limpiarVariablesPaso(row) {
        row.querySelectorAll('.paso-var-row').forEach(r => r.remove());
        renumerar();
    }

    async function cargarVariablesMaquina(row, maquinaId, reemplazar) {
        try {
            const res = await fetch(urlVariablesMaquina.replace('__ID__', String(maquinaId)), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            limitesMaquinaCache[String(maquinaId)] = data.variables || [];
            if (data.imagen_src) setMaquinaEnPaso(row, maquinaId, false);
            if (reemplazar) limpiarVariablesPaso(row);
            (data.variables || []).forEach(v => agregarVarPaso(row, v));
            refrescarLimitesPaso(row);
        } catch (e) {}
    }

    function syncPickerPaso(row) {
        const proc = row.querySelector('.paso-proceso')?.value;
        const btn = row.querySelector('.btn-elegir-maquina-paso');
        if (btn) btn.disabled = !proc;
    }

    function renderModalMaquinas(row, q) {
        const procId = row.querySelector('.paso-proceso')?.value;
        const selId = row.querySelector('.paso-maquina')?.value;
        const listaMaq = mapaMaquinasProceso[procId] || [];
        q = (q || '').toLowerCase().trim();
        if (!modalGrid) return;
        modalGrid.innerHTML = '';

        const colAny = document.createElement('div');
        colAny.className = 'col-md-4 col-sm-6 mb-3';
        colAny.innerHTML = '<div class="modal-maq-paso-card modal-maq-paso-card--any' + (!selId ? ' is-selected' : '') + '" data-id=""><i class="fas fa-random fa-2x text-muted mb-2"></i><div class="font-weight-bold">Cualquiera compatible</div><div class="small text-muted">Sin máquina fija</div></div>';
        colAny.querySelector('.modal-maq-paso-card').addEventListener('click', () => seleccionarMaquinaModal(''));
        modalGrid.appendChild(colAny);

        const filtradas = listaMaq.filter(m => {
            if (!q) return true;
            const t = (m.nombre + ' ' + (m.codigo || '') + ' ' + (m.descripcion || '')).toLowerCase();
            return t.includes(q);
        });

        filtradas.forEach(m => {
            const col = document.createElement('div');
            col.className = 'col-md-4 col-sm-6 mb-3';
            const img = m.imagen_src
                ? '<img src="' + m.imagen_src + '" alt="">'
                : '<i class="fas fa-cogs fa-3x text-muted opacity-50"></i>';
            const sel = String(selId) === String(m.id) ? ' is-selected' : '';
            const mant = m.activo ? '' : ' <span class="badge badge-warning">Mantenimiento</span>';
            col.innerHTML =
                '<div class="modal-maq-paso-card' + sel + '" data-id="' + m.id + '">' +
                    '<div class="modal-maq-paso-card__img">' + img + '</div>' +
                    '<div class="modal-maq-paso-card__body">' +
                        '<div class="modal-maq-paso-card__nombre">' + m.nombre + mant + '</div>' +
                        '<div class="modal-maq-paso-card__meta">' + (m.codigo || 'Sin código') + '</div>' +
                    '</div></div>';
            col.querySelector('.modal-maq-paso-card').addEventListener('click', () => seleccionarMaquinaModal(String(m.id)));
            modalGrid.appendChild(col);
        });

        if (modalVacio) modalVacio.classList.toggle('d-none', filtradas.length > 0 || !q);
        if (modalContador) modalContador.textContent = filtradas.length + ' máquina(s) compatible(s)';
    }

    function abrirModalMaquinas(row) {
        filaModalActiva = row;
        if (modalBuscar) modalBuscar.value = '';
        renderModalMaquinas(row, '');
        window.jQuery?.('#modalMaquinaPasoPlantilla').modal('show');
    }

    function seleccionarMaquinaModal(id) {
        if (!filaModalActiva) return;
        if (id) setMaquinaEnPaso(filaModalActiva, id, true);
        else { setMaquinaEnPaso(filaModalActiva, '', false); limpiarVariablesPaso(filaModalActiva); }
        window.jQuery?.('#modalMaquinaPasoPlantilla').modal('hide');
        filaModalActiva = null;
    }

    modalBuscar?.addEventListener('input', () => {
        if (filaModalActiva) renderModalMaquinas(filaModalActiva, modalBuscar.value);
    });

    function setDragActivo(activo) {
        lista?.classList.toggle('is-dragging-pasos', activo);
        zonaDropFinal?.classList.toggle('is-active', activo);
        if (activo) {
            document.addEventListener('dragover', autoScrollDuranteDrag);
        } else {
            document.removeEventListener('dragover', autoScrollDuranteDrag);
        }
    }

    function autoScrollDuranteDrag(e) {
        if (!dragSrc) return;
        e.preventDefault();
        const margen = 90;
        const velocidad = 18;
        const y = e.clientY;
        const alto = window.innerHeight;
        if (y < margen) {
            window.scrollBy(0, -velocidad);
        } else if (y > alto - margen) {
            window.scrollBy(0, velocidad);
        }
    }

    function intercambiarPasos(fromRow, targetRow) {
        if (!fromRow || !targetRow || fromRow === targetRow) return false;
        if (esPasoEmpaquetado(fromRow) || esPasoEmpaquetado(targetRow)) {
            avisoEmpaquetadoFijo();
            return false;
        }

        const rows = [...filas()];
        const fromIndex = rows.indexOf(fromRow);
        const toIndex = rows.indexOf(targetRow);
        if (fromIndex === -1 || toIndex === -1 || fromIndex === toIndex) return false;

        rows[fromIndex] = targetRow;
        rows[toIndex] = fromRow;
        rows.forEach(r => lista.appendChild(r));
        renumerar();
        return true;
    }

    function moverAntesDeEmpaquetado(fromRow) {
        if (!fromRow || esPasoEmpaquetado(fromRow)) {
            avisoEmpaquetadoFijo();
            return false;
        }
        const emp = filaEmpaquetado();
        if (!emp) {
            lista.appendChild(fromRow);
        } else if (fromRow !== emp) {
            lista.insertBefore(fromRow, emp);
        }
        renumerar();
        return true;
    }

    function wireDrag(row) {
        const handle = row.querySelector('.paso-drag-handle');
        if (!handle) return;

        handle.setAttribute('draggable', 'true');
        handle.addEventListener('dragstart', e => {
            if (esPasoEmpaquetado(row)) {
                e.preventDefault();
                avisoEmpaquetadoFijo();
                return;
            }
            dragSrc = row;
            row.classList.add('paso-dragging');
            setDragActivo(true);
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', 'paso');
            e.stopPropagation();
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('paso-dragging');
            filas().forEach(r => r.classList.remove('paso-drag-over', 'paso-drag-over-swap'));
            zonaDropFinal?.classList.remove('is-hover');
            setDragActivo(false);
            dragSrc = null;
        });

        row.addEventListener('dragover', e => {
            if (!dragSrc || dragSrc === row) return;
            if (esPasoEmpaquetado(row)) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            filas().forEach(r => r.classList.remove('paso-drag-over', 'paso-drag-over-swap'));
            row.classList.add('paso-drag-over', 'paso-drag-over-swap');
        });

        row.addEventListener('dragleave', e => {
            if (!row.contains(e.relatedTarget)) {
                row.classList.remove('paso-drag-over', 'paso-drag-over-swap');
            }
        });

        row.addEventListener('drop', e => {
            e.preventDefault();
            e.stopPropagation();
            row.classList.remove('paso-drag-over', 'paso-drag-over-swap');
            if (!dragSrc || dragSrc === row) return;
            intercambiarPasos(dragSrc, row);
        });
    }

    if (zonaDropFinal) {
        zonaDropFinal.addEventListener('dragover', e => {
            if (!dragSrc) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            zonaDropFinal.classList.add('is-hover');
        });
        zonaDropFinal.addEventListener('dragleave', () => zonaDropFinal.classList.remove('is-hover'));
        zonaDropFinal.addEventListener('drop', e => {
            e.preventDefault();
            zonaDropFinal.classList.remove('is-hover');
            if (!dragSrc) return;
            moverAntesDeEmpaquetado(dragSrc);
        });
    }

    if (lista) {
        lista.addEventListener('dragover', e => {
            if (!dragSrc) return;
            const ultima = ultimaFila();
            if (ultima && e.target === lista) {
                e.preventDefault();
            }
        });
    }

    async function precargarCacheMaquina(maquinaId) {
        if (!maquinaId || limitesMaquinaCache[String(maquinaId)]) return;
        try {
            const res = await fetch(urlVariablesMaquina.replace('__ID__', String(maquinaId)), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            limitesMaquinaCache[String(maquinaId)] = data.variables || [];
        } catch (e) {}
    }

    function agregarPaso(data) {
        const node = tpl.content.cloneNode(true);
        const row = node.querySelector('.paso-plantilla-row');
        if (data?.procesoplantaid) row.querySelector('.paso-proceso').value = data.procesoplantaid;
        if (data?.notas) row.querySelector('.paso-notas').value = data.notas;

        const empRow = filaEmpaquetado();
        const esNuevoEmpaquetado = idCierre() && String(data?.procesoplantaid || '') === idCierre();
        if (esNuevoEmpaquetado || !empRow) {
            lista.appendChild(row);
        } else {
            lista.insertBefore(row, empRow);
        }

        wireDrag(row);
        syncPickerPaso(row);
        if (data?.maquinaplantaid) setMaquinaEnPaso(row, data.maquinaplantaid, false);
        (data?.variables || []).forEach(v => agregarVarPaso(row, v));
        if (data?.maquinaplantaid && !(data?.variables || []).length) {
            cargarVariablesMaquina(row, data.maquinaplantaid, false);
        } else if (data?.maquinaplantaid) {
            precargarCacheMaquina(data.maquinaplantaid).then(() => refrescarLimitesPaso(row));
        }
        renumerar();
    }

    document.getElementById('btnAgregarPasoPlantilla')?.addEventListener('click', () => agregarPaso(null));

    lista.addEventListener('click', e => {
        const row = e.target.closest('.paso-plantilla-row');
        if (!row) return;
        if (e.target.closest('.btn-quitar-paso')) {
            if (filas().length <= 1) { alert('Debe haber al menos un paso.'); return; }
            if (esPasoEmpaquetado(row)) {
                mostrarAvisoGuardar('«' + procesoCierreNombre + '» es obligatorio como último paso de la línea.', 'Paso fijo');
                return;
            }
            row.remove(); renumerar(); return;
        }
        if (e.target.closest('.btn-elegir-maquina-paso') || e.target.closest('.paso-card__media')) {
            if (row.querySelector('.paso-proceso')?.value) abrirModalMaquinas(row);
            else alert('Primero seleccione el proceso de este paso.');
            return;
        }
        if (e.target.closest('.paso-limpiar-maquina')) {
            setMaquinaEnPaso(row, '', false); limpiarVariablesPaso(row);
        }
    });

    lista.addEventListener('change', e => {
        const row = e.target.closest('.paso-plantilla-row');
        if (!row || !e.target.classList.contains('paso-proceso')) return;

        const cierreId = idCierre();
        const nuevoVal = e.target.value;
        if (cierreId && nuevoVal === cierreId) {
            const duplicado = [...filas()].find(r => r !== row && r.querySelector('.paso-proceso')?.value === cierreId);
            if (duplicado) {
                e.target.value = '';
                if (window.ModalConfirmar?.aviso) {
                    ModalConfirmar.aviso({
                        titulo: 'Empaquetado duplicado',
                        mensaje: 'Ya hay un paso de «' + procesoCierreNombre + '». No puede empaquetar dos veces.',
                        tono: 'warning',
                    });
                } else {
                    alert('Ya existe un paso de Empaquetado. No puede empaquetar dos veces.');
                }
            } else {
                lista.appendChild(row);
            }
        }

        setMaquinaEnPaso(row, '', false);
        limpiarVariablesPaso(row);
        syncPickerPaso(row);
        renumerar();
    });

    form?.addEventListener('submit', e => { if (!validarAlGuardar()) e.preventDefault(); });
    (iniciales.length ? iniciales : [{}]).forEach(agregarPaso);
})();
</script>
@endpush
