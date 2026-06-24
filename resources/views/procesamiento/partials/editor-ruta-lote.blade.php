@php
    $rutaInicial = $rutaPlantilla ?? [];
    $procesos = $procesosPlanta ?? \App\Support\ProcesoPlantaCatalogo::paraTransformacion();
    $mapaMaquinas = $mapaCompatibilidad ?? \App\Support\MaquinaProcesoCompatibilidad::mapaSelectores();
    $procesoCierreId = \App\Support\ProcesoPlantaCatalogo::idProcesoCierreTransformacion();
    $procesoCierreNombre = \App\Support\ProcesoPlantaCatalogo::PROCESO_CIERRE_TRANSFORMACION;
    $completados = (int) ($etapasCompletadasRuta ?? 0);
    $urlVariablesMaquina = url('/maquinas-planta/__ID__/variables-sugeridas');

    $lpRutaEditorJs = [
        'procesoCierreId' => (int) $procesoCierreId,
        'procesoCierreNombre' => $procesoCierreNombre,
        'completados' => $completados,
        'procesos' => $procesos->map(fn ($p) => [
            'id' => (int) $p->procesoplantaid,
            'nombre' => $p->nombre,
        ])->values()->all(),
        'mapaMaquinas' => $mapaMaquinas,
        'maquinas' => ($maquinasPlanta ?? collect())->map(fn ($m) => [
            'id' => (int) $m->maquinaplantaid,
            'nombre' => $m->nombre,
            'codigo' => $m->codigo,
        ])->values()->all(),
        'urlVariablesMaquina' => $urlVariablesMaquina,
        'pasos' => collect($rutaInicial)->map(function (array $p) {
            return [
                'loteproduccionrutapasoid' => $p['loteproduccionrutapasoid'] ?? null,
                'orden' => (int) ($p['orden'] ?? 0),
                'procesoplantaid' => (int) ($p['procesoplantaid'] ?? 0),
                'maquinaplantaid' => $p['maquinaplantaid'] ?? null,
                'notas' => $p['notas'] ?? '',
                'proceso' => $p['proceso'] ?? '',
                'maquina' => $p['maquina'] ?? null,
                'editable' => ! empty($p['editable']),
                'variables' => collect($p['parametros'] ?? [])->map(fn (array $v) => [
                    'variableestandarid' => (int) ($v['variableestandarid'] ?? 0),
                    'nombre' => $v['nombre'] ?? '',
                    'unidad' => $v['unidad'] ?? null,
                    'valor_minimo' => (float) ($v['valor_minimo'] ?? 0),
                    'valor_maximo' => (float) ($v['valor_maximo'] ?? 0),
                ])->values()->all(),
            ];
        })->values()->all(),
    ];
@endphp

<div class="af-ruta-editor" id="lp-editor-ruta-lote">
    <div class="af-ruta-editor__head">
        <div>
            <strong><i class="fas fa-route mr-1"></i> Ajustar pasos pendientes</strong>
            <span class="d-block small text-white-50 mt-1">El último paso debe ser «{{ $procesoCierreNombre }}». Los completados no se modifican.</span>
        </div>
        <button type="button" class="btn btn-sm btn-light font-weight-bold" id="btnLpRutaAgregarPaso">
            <i class="fas fa-plus mr-1"></i> Paso
        </button>
    </div>
    <div class="af-ruta-editor__body">
        <form method="POST" action="{{ route('procesamiento.actualizar-ruta', $lote) }}" id="formLpRutaLote">
            @csrf
            @method('PUT')
            <div id="lpRutaCompletadosChips" class="af-ruta-chips mb-2"></div>
            <div id="listaLpRutaPasos"></div>
            <p class="small text-muted mb-2" id="lpRutaSinPasos" style="display:none">Sin pasos pendientes. Agregue al menos uno antes de «{{ $procesoCierreNombre }}».</p>
            <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                <i class="fas fa-save mr-1"></i> Guardar cambios
            </button>
        </form>
    </div>
</div>

@once
@push('styles')
<style>
.af-ruta-editor {
    border-radius: 14px; overflow: hidden;
    border: 1px solid #c5dcc7;
    box-shadow: 0 6px 20px rgba(30,70,32,.08);
}
.af-ruta-editor__head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem;
    padding: .85rem 1rem;
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #4a7c59 100%);
    color: #fff;
}
.af-ruta-editor__body { padding: .85rem 1rem; background: #f8fbf8; }
.af-ruta-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
.af-ruta-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .72rem; font-weight: 600; padding: .28rem .55rem;
    border-radius: 999px; background: #dcfce7; color: #166534;
    border: 1px solid #bbf7d0;
}
.af-ruta-paso-card {
    background: #fff; border: 1px solid #e2ebe3; border-radius: 12px;
    padding: .75rem .85rem; margin-bottom: .55rem;
    border-left: 4px solid #4a7c59;
}
.af-ruta-paso-card__top {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; margin-bottom: .55rem;
}
.af-ruta-paso-card__orden {
    width: 1.65rem; height: 1.65rem; border-radius: 8px;
    background: #1e4620; color: #fff; font-size: .72rem; font-weight: 800;
    display: inline-flex; align-items: center; justify-content: center;
}
.af-ruta-vars { display: flex; flex-wrap: wrap; gap: .35rem .65rem; margin-top: .45rem; }
.af-ruta-var-pill {
    display: inline-flex; align-items: center; gap: .25rem;
    background: #f0fdf4; border: 1px solid #d1e7d4; border-radius: 8px;
    padding: .2rem .4rem; font-size: .72rem;
}
.af-ruta-var-pill input {
    width: 3.6rem; border: 0; background: transparent; padding: 0;
    font-size: .72rem; font-weight: 700; color: #1e4620;
}
</style>
@endpush
@endonce

@push('scripts')
<script>
(function () {
    const cfg = @json($lpRutaEditorJs);
    let pasos = cfg.pasos;
    const lista = document.getElementById('listaLpRutaPasos');
    const chips = document.getElementById('lpRutaCompletadosChips');
    const vacio = document.getElementById('lpRutaSinPasos');
    const form = document.getElementById('formLpRutaLote');
    const limitesCache = {};

    function maquinasCompatibles(procId) {
        const ids = cfg.mapaMaquinas[String(procId)] || cfg.mapaMaquinas[procId] || [];
        return cfg.maquinas.filter(function (m) { return ids.indexOf(m.id) !== -1; });
    }

    function fetchLimites(maquinaId) {
        if (!maquinaId) return Promise.resolve([]);
        const key = String(maquinaId);
        if (limitesCache[key]) return Promise.resolve(limitesCache[key]);
        return fetch(cfg.urlVariablesMaquina.replace('__ID__', key), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                limitesCache[key] = data.variables || [];
                return limitesCache[key];
            })
            .catch(function () { return []; });
    }

    function renderChips() {
        if (!chips) return;
        chips.innerHTML = '';
        pasos.forEach(function (paso, idx) {
            if (idx >= cfg.completados) return;
            const el = document.createElement('span');
            el.className = 'af-ruta-chip';
            el.innerHTML = '<i class="fas fa-check"></i> ' + (idx + 1) + '. ' + (paso.proceso || 'Paso');
            chips.appendChild(el);
        });
        chips.style.display = cfg.completados > 0 ? 'flex' : 'none';
    }

    function render() {
        renderChips();
        if (!lista) return;
        lista.innerHTML = '';
        const pendientes = pasos.filter(function (_, idx) { return idx >= cfg.completados; });
        if (vacio) vacio.style.display = pendientes.length ? 'none' : 'block';

        pasos.forEach(function (paso, idx) {
            if (idx < cfg.completados) return;

            const row = document.createElement('div');
            row.className = 'af-ruta-paso-card';
            row.dataset.idx = String(idx);

            let procOpts = '<option value="">Proceso…</option>';
            cfg.procesos.forEach(function (p) {
                procOpts += '<option value="' + p.id + '"' + (p.id === paso.procesoplantaid ? ' selected' : '') + '>' + p.nombre + '</option>';
            });
            let maqOpts = '<option value="">Máquina…</option>';
            maquinasCompatibles(paso.procesoplantaid).forEach(function (m) {
                maqOpts += '<option value="' + m.id + '"' + (m.id === paso.maquinaplantaid ? ' selected' : '') + '>' + m.nombre + '</option>';
            });

            let varsHtml = '<div class="af-ruta-vars">';
            (paso.variables || []).forEach(function (v, vIdx) {
                varsHtml += '<span class="af-ruta-var-pill">' +
                    '<span>' + (v.nombre || 'Var') + '</span>' +
                    '<input type="number" step="0.1" class="lp-ruta-var-min" value="' + v.valor_minimo + '">' +
                    '<span>—</span>' +
                    '<input type="number" step="0.1" class="lp-ruta-var-max" value="' + v.valor_maximo + '">' +
                    '<button type="button" class="btn btn-link btn-sm p-0 text-danger lp-ruta-quitar-var" data-vidx="' + vIdx + '"><i class="fas fa-times"></i></button>' +
                    '</span>';
            });
            varsHtml += '</div>';

            row.innerHTML =
                '<div class="af-ruta-paso-card__top">' +
                    '<span class="af-ruta-paso-card__orden">' + (idx + 1) + '</span>' +
                    '<div class="btn-group btn-group-sm">' +
                        '<button type="button" class="btn btn-outline-secondary lp-ruta-subir" title="Subir"' + (idx <= cfg.completados ? ' disabled' : '') + '><i class="fas fa-arrow-up"></i></button>' +
                        '<button type="button" class="btn btn-outline-secondary lp-ruta-bajar" title="Bajar"' + (idx >= pasos.length - 1 ? ' disabled' : '') + '><i class="fas fa-arrow-down"></i></button>' +
                        '<button type="button" class="btn btn-outline-danger lp-ruta-quitar" title="Quitar"><i class="fas fa-trash"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="form-row">' +
                    '<div class="col-md-4 form-group mb-1"><select class="form-control form-control-sm lp-ruta-proc">' + procOpts + '</select></div>' +
                    '<div class="col-md-4 form-group mb-1"><select class="form-control form-control-sm lp-ruta-maq">' + maqOpts + '</select></div>' +
                    '<div class="col-md-4 form-group mb-1"><input type="text" class="form-control form-control-sm lp-ruta-notas" maxlength="255" placeholder="Notas" value="' + (paso.notas || '').replace(/"/g, '&quot;') + '"></div>' +
                '</div>' +
                varsHtml +
                '<button type="button" class="btn btn-link btn-sm p-0 mt-1 lp-ruta-cargar-vars"><i class="fas fa-sliders-h mr-1"></i>Cargar variables de máquina</button>';

            lista.appendChild(row);
        });
        bindEvents();
    }

    function bindEvents() {
        lista.querySelectorAll('.lp-ruta-proc').forEach(function (sel) {
            sel.onchange = function () {
                const idx = parseInt(sel.closest('[data-idx]').dataset.idx, 10);
                pasos[idx].procesoplantaid = parseInt(sel.value, 10) || 0;
                pasos[idx].maquinaplantaid = null;
                pasos[idx].variables = [];
                render();
            };
        });
        lista.querySelectorAll('.lp-ruta-maq').forEach(function (sel) {
            sel.onchange = function () {
                const idx = parseInt(sel.closest('[data-idx]').dataset.idx, 10);
                pasos[idx].maquinaplantaid = sel.value ? parseInt(sel.value, 10) : null;
            };
        });
        lista.querySelectorAll('.lp-ruta-notas').forEach(function (inp) {
            inp.oninput = function () {
                const idx = parseInt(inp.closest('[data-idx]').dataset.idx, 10);
                pasos[idx].notas = inp.value;
            };
        });
        lista.querySelectorAll('.lp-ruta-subir').forEach(function (btn) {
            btn.onclick = function () {
                const idx = parseInt(btn.closest('[data-idx]').dataset.idx, 10);
                if (idx <= cfg.completados) return;
                const tmp = pasos[idx - 1]; pasos[idx - 1] = pasos[idx]; pasos[idx] = tmp;
                render();
            };
        });
        lista.querySelectorAll('.lp-ruta-bajar').forEach(function (btn) {
            btn.onclick = function () {
                const idx = parseInt(btn.closest('[data-idx]').dataset.idx, 10);
                if (idx >= pasos.length - 1) return;
                const tmp = pasos[idx + 1]; pasos[idx + 1] = pasos[idx]; pasos[idx] = tmp;
                render();
            };
        });
        lista.querySelectorAll('.lp-ruta-quitar').forEach(function (btn) {
            btn.onclick = function () {
                const idx = parseInt(btn.closest('[data-idx]').dataset.idx, 10);
                if (idx < cfg.completados) return;
                pasos.splice(idx, 1);
                render();
            };
        });
        lista.querySelectorAll('.lp-ruta-cargar-vars').forEach(function (btn) {
            btn.onclick = function () {
                const idx = parseInt(btn.closest('[data-idx]').dataset.idx, 10);
                fetchLimites(pasos[idx].maquinaplantaid).then(function (vars) {
                    pasos[idx].variables = vars.map(function (v) {
                        return {
                            variableestandarid: v.variableestandarid,
                            nombre: v.nombre,
                            unidad: v.unidad,
                            valor_minimo: parseFloat(v.valor_minimo || 0),
                            valor_maximo: parseFloat(v.valor_maximo || 0),
                        };
                    });
                    render();
                });
            };
        });
        lista.querySelectorAll('.af-ruta-var-pill').forEach(function (pill) {
            const wrap = pill.closest('[data-idx]');
            const idx = parseInt(wrap.dataset.idx, 10);
            const vIdx = parseInt(pill.querySelector('.lp-ruta-quitar-var')?.dataset.vidx || '0', 10);
            pill.querySelector('.lp-ruta-var-min')?.addEventListener('input', function (e) {
                pasos[idx].variables[vIdx].valor_minimo = parseFloat(e.target.value) || 0;
            });
            pill.querySelector('.lp-ruta-var-max')?.addEventListener('input', function (e) {
                pasos[idx].variables[vIdx].valor_maximo = parseFloat(e.target.value) || 0;
            });
            pill.querySelector('.lp-ruta-quitar-var')?.addEventListener('click', function () {
                pasos[idx].variables.splice(vIdx, 1);
                render();
            });
        });
    }

    document.getElementById('btnLpRutaAgregarPaso')?.addEventListener('click', function () {
        const ultimoEsCierre = pasos.length && pasos[pasos.length - 1].procesoplantaid === cfg.procesoCierreId;
        const insertAt = ultimoEsCierre ? pasos.length - 1 : pasos.length;
        pasos.splice(insertAt, 0, {
            loteproduccionrutapasoid: null,
            procesoplantaid: 0,
            maquinaplantaid: null,
            notas: '',
            variables: [],
        });
        render();
    });

    form?.addEventListener('submit', function (e) {
        if (!pasos.length) {
            e.preventDefault();
            alert('Agregue al menos un paso a la ruta.');
            return;
        }
        const ultimo = pasos[pasos.length - 1];
        if (cfg.procesoCierreId && ultimo.procesoplantaid !== cfg.procesoCierreId) {
            e.preventDefault();
            alert('El último paso debe ser «' + cfg.procesoCierreNombre + '».');
            return;
        }
        form.querySelectorAll('input[name^="pasos"]').forEach(function (el) { el.remove(); });
        pasos.forEach(function (paso, i) {
            function add(name, val) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'pasos[' + i + '][' + name + ']';
                inp.value = val;
                form.appendChild(inp);
            }
            if (paso.loteproduccionrutapasoid) add('loteproduccionrutapasoid', paso.loteproduccionrutapasoid);
            add('procesoplantaid', paso.procesoplantaid);
            if (paso.maquinaplantaid) add('maquinaplantaid', paso.maquinaplantaid);
            if (paso.notas) add('notas', paso.notas);
            (paso.variables || []).forEach(function (v, j) {
                add('variables[' + j + '][variableestandarid]', v.variableestandarid);
                add('variables[' + j + '][valor_minimo]', v.valor_minimo);
                add('variables[' + j + '][valor_maximo]', v.valor_maximo);
            });
        });
    });

    render();
})();
</script>
@endpush
