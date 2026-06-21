@php
    $pasosIniciales = $pasosIniciales ?? [['procesoplantaid' => '', 'maquinaplantaid' => '', 'notas' => '']];
    $procesoCierreId = $procesoCierreId ?? \App\Support\ProcesoPlantaCatalogo::idProcesoCierreTransformacion();
    $procesoCierreNombre = \App\Support\ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION;
    $mapaMaquinasProceso = $mapaMaquinasProceso ?? \App\Support\MaquinaProcesoCompatibilidad::mapaMaquinasFormulario();
@endphp

<div class="plantilla-pasos-builder">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="font-weight-bold mb-0"><i class="fas fa-list-ol mr-1 text-success"></i> Pasos de la línea <span class="text-danger">*</span></label>
        <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarPasoPlantilla">
            <i class="fas fa-plus mr-1"></i> Agregar paso
        </button>
    </div>
    <p class="small text-muted mb-2">
        Ordene las etapas de transformación. Al crear un lote, se sugiere cada paso en secuencia.
        El <strong>último paso debe ser {{ $procesoCierreNombre }}</strong>.
    </p>
    <div id="alertEmpaquetadoFinal" class="alert alert-warning d-none mb-2" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <span id="alertEmpaquetadoFinalTexto">
            El <strong>último paso</strong> debe ser <strong>{{ $procesoCierreNombre }}</strong>.
            Agregue un paso final con ese proceso o cambie el proceso del último paso.
        </span>
    </div>
    @error('pasos')
        <div class="alert alert-danger mb-2" role="alert">
            <i class="fas fa-times-circle mr-1"></i>{{ $message }}
        </div>
    @enderror
    <div id="listaPasosPlantilla" class="lista-pasos-plantilla"></div>
</div>

<template id="tplPasoPlantilla">
    <div class="paso-plantilla-row card border mb-2">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge badge-success paso-orden-badge">Paso 1</span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-paso" title="Quitar paso"><i class="fas fa-times"></i></button>
            </div>
            <div class="form-row">
                <div class="col-md-5 form-group mb-2">
                    <label class="small mb-0">Proceso</label>
                    <select class="form-control form-control-sm paso-proceso" required>
                        <option value="">Seleccionar…</option>
                        @foreach($procesos as $proc)
                            <option value="{{ $proc->procesoplantaid }}" @if((int) $proc->procesoplantaid === (int) $procesoCierreId) data-es-cierre="1" @endif>{{ $proc->nombre }}</option>
                        @endforeach
                    </select>
                    <small class="paso-hint-cierre text-muted d-none"></small>
                </div>
                <div class="col-md-4 form-group mb-2">
                    <label class="small mb-0">Máquina sugerida</label>
                    <select class="form-control form-control-sm paso-maquina" disabled>
                        <option value="">Primero elija un proceso…</option>
                    </select>
                    <small class="paso-hint-maquina text-muted d-none"></small>
                </div>
                <div class="col-md-3 form-group mb-2">
                    <label class="small mb-0">Notas</label>
                    <input type="text" class="form-control form-control-sm paso-notas" maxlength="255" placeholder="Ej. Pelado">
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const lista = document.getElementById('listaPasosPlantilla');
    const tpl = document.getElementById('tplPasoPlantilla');
    const alerta = document.getElementById('alertEmpaquetadoFinal');
    const alertaTexto = document.getElementById('alertEmpaquetadoFinalTexto');
    const procesoCierreId = @json($procesoCierreId);
    const procesoCierreNombre = @json($procesoCierreNombre);
    const mapaMaquinasProceso = @json($mapaMaquinasProceso);
    const iniciales = @json($pasosIniciales);
    if (!lista || !tpl) return;

    const form = lista.closest('form');
    const btnGuardar = form?.querySelector('button[type="submit"]');

    function idCierre() {
        if (procesoCierreId) return String(procesoCierreId);
        const opt = tpl.content.querySelector('option[data-es-cierre]');
        return opt?.value ? String(opt.value) : null;
    }

    function filas() {
        return lista.querySelectorAll('.paso-plantilla-row');
    }

    function ultimaFila() {
        const rows = filas();
        return rows.length ? rows[rows.length - 1] : null;
    }

    function ultimoProcesoId() {
        const row = ultimaFila();
        if (!row) return null;
        const select = row.querySelector('.paso-proceso');
        return select?.value ? String(select.value) : null;
    }

    function nombreProceso(select) {
        if (!select?.value) return null;
        return select.options[select.selectedIndex]?.text?.trim() || null;
    }

    function todosPasosTienenProceso() {
        const selects = lista.querySelectorAll('.paso-proceso');
        return selects.length > 0 && [...selects].every(s => s.value);
    }

    function puedeGuardar() {
        const cierreId = idCierre();
        const ultimoId = ultimoProcesoId();
        return !!cierreId && todosPasosTienenProceso() && ultimoId === cierreId;
    }

    function mensajeAlertaAlGuardar() {
        const rows = filas();
        const ultimoRow = ultimaFila();
        const ultimoSelect = ultimoRow?.querySelector('.paso-proceso');
        const ultimoId = ultimoProcesoId();
        const cierreId = idCierre();

        if (!todosPasosTienenProceso()) {
            return 'Complete el proceso de <strong>todos los pasos</strong> antes de guardar.';
        }

        if (!ultimoId || !cierreId || ultimoId !== cierreId) {
            const nombre = nombreProceso(ultimoSelect);
            if (nombre && ultimoId !== cierreId) {
                return 'El último paso es <strong>' + nombre + '</strong>, pero debe ser <strong>' + procesoCierreNombre + '</strong>. Agregue un paso final con ' + procesoCierreNombre + ' o cambie el último paso.';
            }

            return 'Agregue todos los pasos de la línea y termine con <strong>' + procesoCierreNombre + '</strong> como último paso.';
        }

        return '';
    }

    function actualizarInterfazPaso() {
        const ultimoRow = ultimaFila();
        const ultimoSelect = ultimoRow?.querySelector('.paso-proceso');
        const ultimoHint = ultimoRow?.querySelector('.paso-hint-cierre');

        if (alerta) {
            alerta.classList.add('d-none');
        }

        if (ultimoSelect) {
            ultimoSelect.classList.remove('is-invalid', 'is-valid');
        }

        if (ultimoHint) {
            ultimoHint.classList.remove('d-none');
            ultimoHint.className = 'paso-hint-cierre text-muted';
            ultimoHint.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Puede agregar más pasos. Al guardar, el <strong>último</strong> debe ser ' + procesoCierreNombre + '.';
        }

        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.title = '';
        }
    }

    function validarAlGuardar() {
        const guardarOk = puedeGuardar();
        const mensaje = mensajeAlertaAlGuardar();
        const ultimoRow = ultimaFila();
        const ultimoSelect = ultimoRow?.querySelector('.paso-proceso');

        if (!guardarOk) {
            if (alerta) {
                alerta.classList.remove('d-none');
                if (alertaTexto) alertaTexto.innerHTML = mensaje;
            }
            if (ultimoSelect) {
                ultimoSelect.classList.add('is-invalid');
            }
            alerta?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        return guardarOk;
    }

    function renumerar() {
        filas().forEach((row, idx) => {
            const badge = row.querySelector('.paso-orden-badge');
            const esUltimo = idx === filas().length - 1;
            const hint = row.querySelector('.paso-hint-cierre');

            if (badge) {
                badge.textContent = 'Paso ' + (idx + 1);
                const esUltimoConVarios = esUltimo && filas().length > 1;
                badge.className = 'badge paso-orden-badge ' + (esUltimoConVarios ? 'badge-info' : 'badge-success');
            }

            if (hint) {
                hint.classList.toggle('d-none', !esUltimo);
            }

            row.querySelectorAll('select, input').forEach(el => {
                const cls = el.classList.contains('paso-proceso') ? 'procesoplantaid'
                    : el.classList.contains('paso-maquina') ? 'maquinaplantaid' : 'notas';
                el.name = 'pasos[' + idx + '][' + cls + ']';
            });
        });

        actualizarInterfazPaso();
    }

    function actualizarMaquinasPaso(row, maquinaPreseleccionada) {
        const procSelect = row.querySelector('.paso-proceso');
        const maqSelect = row.querySelector('.paso-maquina');
        const hint = row.querySelector('.paso-hint-maquina');
        const procesoId = procSelect?.value;
        const prev = maquinaPreseleccionada ?? maqSelect?.value ?? '';

        if (!maqSelect) return;

        maqSelect.innerHTML = '';
        if (!procesoId) {
            maqSelect.disabled = true;
            maqSelect.appendChild(new Option('Primero elija un proceso…', ''));
            if (hint) hint.classList.add('d-none');
            return;
        }

        const compatibles = mapaMaquinasProceso[procesoId] || [];
        maqSelect.disabled = false;
        maqSelect.appendChild(new Option('Cualquiera compatible', ''));

        compatibles.forEach(m => {
            const label = m.nombre + (m.codigo ? ' (' + m.codigo + ')' : '') + (m.activo ? '' : ' — en mantenimiento');
            maqSelect.appendChild(new Option(label, String(m.id)));
        });

        if (prev && [...maqSelect.options].some(o => o.value === String(prev))) {
            maqSelect.value = String(prev);
        } else {
            maqSelect.value = '';
        }

        if (hint) {
            if (compatibles.length === 0) {
                hint.classList.remove('d-none');
                hint.textContent = 'Sin máquinas compatibles registradas para este proceso.';
            } else if (!maqSelect.value) {
                hint.classList.remove('d-none');
                hint.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Cualquiera compatible: el proceso sigue activo si queda al menos una máquina compatible operativa.';
            } else {
                hint.classList.add('d-none');
            }
        }
    }

    function agregarPaso(data) {
        const node = tpl.content.cloneNode(true);
        const row = node.querySelector('.paso-plantilla-row');
        if (data?.procesoplantaid) {
            row.querySelector('.paso-proceso').value = data.procesoplantaid;
        }
        if (data?.notas) {
            row.querySelector('.paso-notas').value = data.notas;
        }
        lista.appendChild(node);
        actualizarMaquinasPaso(row, data?.maquinaplantaid || '');
        renumerar();
    }

    document.getElementById('btnAgregarPasoPlantilla')?.addEventListener('click', () => agregarPaso(null));

    lista.addEventListener('click', e => {
        const btn = e.target.closest('.btn-quitar-paso');
        if (!btn) return;
        if (filas().length <= 1) {
            alert('Debe haber al menos un paso.');
            return;
        }
        btn.closest('.paso-plantilla-row')?.remove();
        renumerar();
    });

    lista.addEventListener('change', e => {
        const row = e.target.closest('.paso-plantilla-row');
        if (e.target.classList.contains('paso-proceso') && row) {
            actualizarMaquinasPaso(row, '');
            actualizarInterfazPaso();
        }
        if (e.target.classList.contains('paso-maquina') && row) {
            actualizarMaquinasPaso(row, e.target.value);
        }
    });

    form?.addEventListener('submit', e => {
        if (!validarAlGuardar()) {
            e.preventDefault();
        }
    });

    (iniciales.length ? iniciales : [{}]).forEach(agregarPaso);
})();
</script>
@endpush
