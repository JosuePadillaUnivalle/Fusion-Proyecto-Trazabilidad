@php
    $modoSiembra = $modoSiembra ?? false;
    $insumosEndpoint = route('catalogo-selector.insumos-actividad');
@endphp

@push('styles')
<style>
.act-det-resumen {
    background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 100%);
    border: 1px solid #bbf7d0;
    border-left: 4px solid #16a34a;
    border-radius: 14px;
    padding: 1rem 1.1rem;
    margin-top: .65rem;
    margin-bottom: 1.35rem;
    box-shadow: 0 4px 14px rgba(22, 163, 74, .08);
}
.act-det-resumen.is-empty { display: none; margin-bottom: 0; }
.act-det-resumen:not(.is-empty) + .form-group {
    margin-top: .25rem;
}
.act-det-resumen__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .85rem;
}
.act-det-resumen__title {
    font-size: .8rem;
    font-weight: 700;
    color: #166534;
    margin: 0;
}
.act-det-resumen__card {
    display: flex;
    align-items: flex-start;
    gap: .9rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .85rem;
    margin-bottom: .65rem;
}
.act-det-resumen__card:last-child { margin-bottom: 0; }
.act-det-resumen__thumb {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.act-det-resumen__thumb--riego {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    color: #2563eb;
    font-size: 1.6rem;
}
.act-det-resumen__body { flex: 1; min-width: 0; }
.act-det-resumen__nombre {
    font-weight: 700;
    color: #0f172a;
    font-size: .92rem;
    line-height: 1.35;
    margin-bottom: .45rem;
}
.act-det-resumen__cant-row {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: .35rem;
}
.act-det-resumen__cant-label {
    font-size: .75rem;
    font-weight: 600;
    color: #64748b;
}
.act-det-resumen__cant-input {
    max-width: 110px;
    text-align: right;
    font-weight: 700;
    color: #14532d;
}
.act-det-resumen__unidad {
    font-size: .82rem;
    font-weight: 600;
    color: #475569;
}
.act-det-resumen__hint {
    font-size: .76rem;
    color: #15803d;
    line-height: 1.45;
    margin: 0;
}
.act-det-resumen__riego-nombre {
    font-size: 1rem;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0;
}
.act-det-resumen__riego-desc {
    font-size: .78rem;
    color: #64748b;
    margin: .2rem 0 0;
}
.act-det-calc {
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.act-det-calc__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .45rem 0;
    border-bottom: 1px dashed #d1fae5;
    font-size: .88rem;
}
.act-det-calc__row:last-child { border-bottom: 0; }
.act-det-calc__label { color: #475569; }
.act-det-calc__value { font-weight: 700; color: #14532d; }
.act-det-calc__total {
    background: #fff;
    border-radius: 10px;
    padding: .75rem;
    margin-top: .65rem;
    text-align: center;
}
.act-det-calc__total-num {
    font-size: 1.45rem;
    font-weight: 800;
    color: #15803d;
    line-height: 1.2;
}
.act-det-calc__total-hint {
    font-size: .78rem;
    color: #64748b;
    margin-top: .25rem;
}
.act-det-insumo-card {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: .75rem;
    margin-bottom: .5rem;
    cursor: pointer;
    transition: border-color .15s;
    display: flex;
    align-items: center;
    gap: .75rem;
}
.act-det-insumo-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.act-det-insumo-card:hover { border-color: #86efac; }
.act-det-insumo-card.is-selected {
    border-color: #16a34a;
    background: #f0fdf4;
}
.act-det-riego-card {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: .85rem;
    margin-bottom: .5rem;
    cursor: pointer;
}
.act-det-riego-card:hover { border-color: #93c5fd; }
.act-det-riego-card.is-selected {
    border-color: #2563eb;
    background: #eff6ff;
}
.act-det-riego-card__title { font-weight: 700; color: #1e3a8a; font-size: .9rem; }
.act-det-riego-card__desc { font-size: .8rem; color: #64748b; margin: .25rem 0 0; }
.act-det-linea {
    display: flex;
    gap: .5rem;
    align-items: end;
    margin-bottom: .5rem;
    flex-wrap: wrap;
}
.act-det-modal-loading {
    text-align: center;
    padding: 2rem 1rem;
    color: #64748b;
}
</style>
@endpush

<input type="hidden" name="detalle_actividad_json" id="detalle_actividad_json" value="{{ old('detalle_actividad_json', '') }}">

<div id="actDetResumenPanel" class="act-det-resumen is-empty">
    <div class="act-det-resumen__head">
        <p class="act-det-resumen__title mb-0"><i class="fas fa-check-circle mr-1"></i> Detalle de la actividad</p>
        <button type="button" class="btn btn-link btn-sm p-0" id="btnActDetEditar">Cambiar</button>
    </div>
    <div id="actDetResumenContent"></div>
</div>

<div class="modal fade" id="modalActDetInsumos" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalActDetInsumosTitulo"><i class="fas fa-flask mr-2"></i>Seleccionar insumos</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="actDetSiembraCalc" class="act-det-calc d-none"></div>
                <p class="small text-muted" id="actDetInsumosAyuda"></p>
                <div id="actDetInsumosLista"></div>
                <div id="actDetInsumosCantidades" class="mt-3"></div>
                <div id="actDetInsumosVacio" class="alert alert-warning d-none small mb-0">
                    No hay insumos con stock en la bodega agrícola para esta actividad.
                    <a href="{{ route('insumos.create') }}">Registrar insumo</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnActDetInsumosConfirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalActDetRiego" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-tint mr-2"></i>Tipo de riego</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="actDetRiegoLista"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnActDetRiegoConfirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>

@if($modoSiembra)
<div class="form-group" id="siembraMaterialPanel">
    <label class="font-weight-bold"><i class="fas fa-seedling text-success mr-1"></i> Material de siembra <span class="text-danger">*</span></label>
    <div class="act-det-calc" id="siembraCalcInline"></div>
    <div id="siembraInsumosInline"></div>
    <div id="siembraCantidadWrap" class="d-none mt-2">
        <label class="small font-weight-bold">¿Cuánto va a usar?</label>
        <div class="input-group" style="max-width:220px;">
            <input type="text" inputmode="decimal" autocomplete="off" class="form-control act-det-cant-numeric" id="siembraCantidadInput">
            <div class="input-group-append"><span class="input-group-text" id="siembraCantidadUnidad">kg</span></div>
        </div>
        <small class="text-muted d-block mt-1">Puede usar más o menos que la sugerencia.</small>
    </div>
</div>
@endif

@push('scripts')
<script>
(function () {
    const endpoint = @json($insumosEndpoint);
    const modoSiembra = @json($modoSiembra);
    const tiposRiego = @json(\App\Support\ActividadDetalleCatalogo::TIPOS_RIEGO);
    const sugerenciaSiembraInicial = @json($sugerenciaSiembra ?? null);
    const insumosSiembraInicial = @json($insumosSiembra ?? []);

    const hiddenJson = document.getElementById('detalle_actividad_json');
    const resumenPanel = document.getElementById('actDetResumenPanel');
    const resumenContent = document.getElementById('actDetResumenContent');
    const selTipo = document.querySelector('[name="tipoactividadid"]');

    function obtenerLoteId() {
        const el = document.querySelector('[name="loteid"]');
        return el && el.value ? String(el.value) : '';
    }

    let estado = { modo: null, insumos: [], riego: null };
    let catalogoActual = [];
    let cacheCatalogo = {};
    let maxInsumos = 10;
    let sugerenciaActual = null;
    let tipoSlugActual = '';
    let tipoNombreActual = '';
    let modalDetConfirmado = false;

    const mapaSlug = {
        'fertiliz': 'fertilizantes',
        'plaga': 'pesticidas',
        'siembra': 'material_siembra',
        'riego': 'riego',
        'regad': 'riego',
    };

    function slugDesdeTipoNombre(nombre) {
        const n = (nombre || '').toLowerCase();
        for (const [frag, slug] of Object.entries(mapaSlug)) {
            if (n.includes(frag)) return slug;
        }
        return '';
    }

    function fmtNum(n) {
        const x = Number(n);
        if (!Number.isFinite(x)) return '—';
        return x.toLocaleString('es-BO', { maximumFractionDigits: 2 });
    }

    function normalizarCantidadTexto(raw) {
        let v = String(raw || '').replace(',', '.');
        v = v.replace(/[^\d.]/g, '');
        const parts = v.split('.');
        if (parts.length > 2) {
            v = parts[0] + '.' + parts.slice(1).join('');
        }
        return v;
    }

    function aplicarLimiteCantidad(input, maxStock) {
        const v = normalizarCantidadTexto(input.value);
        input.value = v;
        if (v === '' || v === '.') return null;
        let num = parseFloat(v);
        if (!Number.isFinite(num) || num < 0) {
            input.value = '';
            return null;
        }
        if (Number.isFinite(maxStock) && num > maxStock) {
            num = maxStock;
            input.value = String(maxStock);
        }
        return num;
    }

    function bindCantidadNumerica(input, maxStock, onChange) {
        input.addEventListener('input', function () {
            aplicarLimiteCantidad(input, maxStock);
            if (typeof onChange === 'function') onChange();
        });
        input.addEventListener('blur', function () {
            const num = aplicarLimiteCantidad(input, maxStock);
            if (num !== null) {
                input.value = String(Math.round(num * 100) / 100);
            }
            if (typeof onChange === 'function') onChange();
        });
    }

    function hintDesdeItem(item, cantidad, divisor) {
        const sugDet = item.sugerencia_detalle;
        const baseSug = item.sugerencia ?? (sugDet && sugDet.tiene_dosis ? sugDet.sugerido : null);
        if (!sugDet || !sugDet.tiene_dosis || baseSug == null) {
            return 'Cantidad máxima disponible: ' + fmtNum(item.stock) + ' ' + item.unidad + '.';
        }
        if (divisor > 1) {
            return 'Repartido entre ' + divisor + ' insumos: '
                + fmtNum(baseSug / divisor) + ' ' + item.unidad
                + ' (dosis total ' + fmtNum(baseSug) + ' ' + item.unidad
                + ' · ' + fmtNum(sugDet.por_ha) + ' ' + item.unidad + '/ha × ' + fmtNum(sugDet.superficie_ha) + ' ha).';
        }
        return 'Sugerido: ' + fmtNum(cantidad ?? baseSug) + ' ' + item.unidad
            + ' (' + fmtNum(sugDet.por_ha) + ' ' + item.unidad + '/ha × ' + fmtNum(sugDet.superficie_ha) + ' ha).';
    }

    function renderCalcSiembra(container, sug) {
        if (!container || !sug || !sug.tiene_dosis) {
            if (container) container.classList.add('d-none');
            return;
        }
        container.classList.remove('d-none');
        const ha = fmtNum(sug.superficie_ha);
        const porHa = fmtNum(sug.por_ha);
        const total = fmtNum(sug.sugerido);
        const unidad = sug.unidad || 'kg';
        const etiqueta = sug.etiqueta_unidad || unidad;
        container.innerHTML =
            '<div class="act-det-calc__row"><span class="act-det-calc__label">Su lote ocupa</span><span class="act-det-calc__value">' + ha + ' hectáreas</span></div>' +
            '<div class="act-det-calc__row"><span class="act-det-calc__label">Por cada hectárea se usan</span><span class="act-det-calc__value">' + porHa + ' ' + unidad + '</span></div>' +
            '<div class="act-det-calc__total">' +
                '<div class="small text-muted mb-1">Total que le sugerimos usar</div>' +
                '<div class="act-det-calc__total-num">' + total + ' ' + unidad + '</div>' +
                '<div class="act-det-calc__total-hint">(' + ha + ' hectáreas × ' + porHa + ' ' + unidad + ' por hectárea)</div>' +
                '<div class="act-det-calc__total-hint mt-1">Unidad: ' + etiqueta + '. Puede ajustar la cantidad abajo.</div>' +
            '</div>';
        sugerenciaActual = sug;
    }

    function renderResumen() {
        if (!resumenPanel || !resumenContent) return;

        if (estado.modo === 'riego' && estado.riego) {
            const tipo = tiposRiego.find(function (t) { return t.key === estado.riego.key; });
            resumenPanel.classList.remove('is-empty');
            resumenContent.innerHTML =
                '<div class="act-det-resumen__card">' +
                    '<div class="act-det-resumen__thumb act-det-resumen__thumb--riego"><i class="fas fa-tint"></i></div>' +
                    '<div class="act-det-resumen__body">' +
                        '<p class="act-det-resumen__riego-nombre mb-0">' + estado.riego.label + '</p>' +
                        '<p class="act-det-resumen__riego-desc">' + (tipo?.descripcion || 'Tipo de riego seleccionado para esta actividad.') + '</p>' +
                    '</div>' +
                '</div>';
            return;
        }

        if (estado.modo === 'insumos' && estado.insumos.length) {
            resumenPanel.classList.remove('is-empty');
            resumenContent.innerHTML = estado.insumos.map(function (ins, idx) {
                const img = ins.imagen
                    ? '<img src="' + ins.imagen + '" alt="" class="act-det-resumen__thumb" loading="lazy">'
                    : '<div class="act-det-resumen__thumb act-det-resumen__thumb--riego"><i class="fas fa-flask"></i></div>';
                return '<div class="act-det-resumen__card">' +
                    img +
                    '<div class="act-det-resumen__body">' +
                        '<div class="act-det-resumen__nombre">' + ins.nombre + '</div>' +
                        '<div class="act-det-resumen__cant-row">' +
                            '<span class="act-det-resumen__cant-label">Cantidad a usar</span>' +
                            '<input type="text" inputmode="decimal" autocomplete="off" class="form-control form-control-sm act-det-resumen__cant-input act-det-resumen-cant-inline" data-idx="' + idx + '" value="' + fmtNum(ins.cantidad) + '">' +
                            '<span class="act-det-resumen__unidad">' + ins.unidad + '</span>' +
                        '</div>' +
                        '<p class="act-det-resumen__hint">' + (ins.sugerencia_hint || ('Sugerido según la superficie del lote.')) + '</p>' +
                        '<p class="act-det-resumen__stock text-success small mb-0 mt-1">' +
                            '<i class="fas fa-boxes mr-1"></i>Stock en bodega: <strong>' + fmtNum(ins.stock) + ' ' + ins.unidad + '</strong> disponibles' +
                        '</p>' +
                    '</div>' +
                '</div>';
            }).join('');

            resumenContent.querySelectorAll('.act-det-resumen-cant-inline').forEach(function (input) {
                const idx = Number(input.dataset.idx);
                const ins = estado.insumos[idx];
                if (!ins) return;
                bindCantidadNumerica(input, Number(ins.stock), function () {
                    const num = aplicarLimiteCantidad(input, Number(ins.stock));
                    if (num !== null && num > 0) {
                        ins.cantidad = num;
                        persistir(false);
                    }
                });
            });
            return;
        }

        resumenPanel.classList.add('is-empty');
        resumenContent.innerHTML = '';
    }

    function persistir(redraw) {
        if (hiddenJson) hiddenJson.value = JSON.stringify(estado);
        if (redraw !== false) renderResumen();
    }

    function cargarCatalogo(tipoSlug, loteId) {
        const key = tipoSlug + ':' + (loteId || '');
        if (cacheCatalogo[key]) {
            return Promise.resolve(cacheCatalogo[key]);
        }
        const url = endpoint + '?tipo_slug=' + encodeURIComponent(tipoSlug) + (loteId ? '&loteid=' + loteId : '');
        return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                cacheCatalogo[key] = json;
                return json;
            });
    }

    function mostrarCargandoModal() {
        const lista = document.getElementById('actDetInsumosLista');
        const cantWrap = document.getElementById('actDetInsumosCantidades');
        const vacio = document.getElementById('actDetInsumosVacio');
        const calc = document.getElementById('actDetSiembraCalc');
        if (lista) {
            lista.innerHTML = '<div class="act-det-modal-loading"><i class="fas fa-spinner fa-spin fa-lg mr-2"></i>Cargando insumos…</div>';
        }
        if (cantWrap) cantWrap.innerHTML = '';
        vacio?.classList.add('d-none');
        calc?.classList.add('d-none');
    }

    function pintarInsumosModal(items, meta) {
        const lista = document.getElementById('actDetInsumosLista');
        const cantWrap = document.getElementById('actDetInsumosCantidades');
        const vacio = document.getElementById('actDetInsumosVacio');
        const calc = document.getElementById('actDetSiembraCalc');
        if (!lista) return;

        maxInsumos = meta.max_insumos || 10;
        sugerenciaActual = meta.sugerencia_siembra || null;
        renderCalcSiembra(calc, sugerenciaActual);

        lista.innerHTML = '';
        cantWrap.innerHTML = '';
        if (!items.length) {
            vacio?.classList.remove('d-none');
            return;
        }
        vacio?.classList.add('d-none');

        const seleccionados = new Set(estado.insumos.map(function (i) { return Number(i.insumoid); }));

        items.forEach(function (item) {
            const card = document.createElement('div');
            card.className = 'act-det-insumo-card' + (seleccionados.has(item.id) ? ' is-selected' : '');
            card.dataset.id = item.id;
            card.innerHTML =
                '<img src="' + (item.imagen || '') + '" alt="" class="act-det-insumo-thumb" loading="lazy">' +
                '<div><strong>' + item.nombre + '</strong><br><small class="text-muted">Disponible: ' + fmtNum(item.stock) + ' ' + item.unidad + '</small></div>';
            card.addEventListener('click', function () {
                if (maxInsumos === 1) {
                    lista.querySelectorAll('.act-det-insumo-card').forEach(function (c) { c.classList.remove('is-selected'); });
                    card.classList.add('is-selected');
                    sugerenciaActual = item.sugerencia_detalle || sugerenciaActual;
                    renderCalcSiembra(calc, sugerenciaActual);
                    pintarCantidades([item]);
                } else {
                    card.classList.toggle('is-selected');
                    const ids = Array.from(lista.querySelectorAll('.act-det-insumo-card.is-selected')).map(function (c) {
                        return items.find(function (x) { return String(x.id) === c.dataset.id; });
                    }).filter(Boolean);
                    pintarCantidades(ids);
                }
            });
            lista.appendChild(card);
        });

        if (estado.insumos.length) {
            const pre = items.filter(function (it) { return seleccionados.has(it.id); });
            if (pre.length) pintarCantidades(pre);
        }
    }

    function pintarCantidades(items) {
        const wrap = document.getElementById('actDetInsumosCantidades');
        if (!wrap) return;
        wrap.innerHTML = '<label class="small font-weight-bold d-block mb-2">Cantidad a usar</label>';
        const divisor = Math.max(1, items.length);

        items.forEach(function (item) {
            const prev = estado.insumos.find(function (i) { return Number(i.insumoid) === Number(item.id); });
            const sugDet = item.sugerencia_detalle;
            const baseSug = item.sugerencia ?? (sugDet && sugDet.tiene_dosis ? sugDet.sugerido : null);
            const val = prev ? prev.cantidad : (baseSug != null ? Math.round((baseSug / divisor) * 100) / 100 : '');
            const hint = hintDesdeItem(item, val, divisor);

            const linea = document.createElement('div');
            linea.className = 'act-det-linea';
            linea.innerHTML =
                '<div class="flex-grow-1"><small class="text-muted d-block">' + item.nombre + '</small>' +
                '<div class="input-group input-group-sm" style="max-width:200px;">' +
                '<input type="text" inputmode="decimal" autocomplete="off" class="form-control act-det-cant-input act-det-cant-numeric" data-id="' + item.id + '" data-nombre="' + item.nombre + '" data-unidad="' + item.unidad + '" data-stock="' + item.stock + '" value="' + (val || '') + '">' +
                '<div class="input-group-append"><span class="input-group-text">' + item.unidad + '</span></div></div>' +
                '<small class="text-success d-block mt-1 act-det-cant-hint">' + hint + '</small></div>';
            wrap.appendChild(linea);

            const input = linea.querySelector('.act-det-cant-input');
            bindCantidadNumerica(input, Number(item.stock));
        });
    }

    function confirmarInsumos() {
        const lista = document.getElementById('actDetInsumosLista');
        const inputs = document.querySelectorAll('.act-det-cant-input');
        const filas = [];
        const cardsSel = lista ? lista.querySelectorAll('.act-det-insumo-card.is-selected') : [];
        if (!cardsSel.length) {
            alert('Seleccione al menos un insumo.');
            return false;
        }
        if (maxInsumos > 1 && cardsSel.length > maxInsumos) {
            alert('Demasiados insumos seleccionados.');
            return false;
        }
        const divisor = Math.max(1, cardsSel.length);

        inputs.forEach(function (inp) {
            const stock = parseFloat(inp.dataset.stock);
            const cant = aplicarLimiteCantidad(inp, stock);
            if (cant === null || cant <= 0) return;
            const item = catalogoActual.find(function (x) { return String(x.id) === inp.dataset.id; });
            filas.push({
                insumoid: Number(inp.dataset.id),
                nombre: inp.dataset.nombre,
                cantidad: cant,
                unidad: inp.dataset.unidad,
                stock: stock,
                imagen: item?.imagen || '',
                sugerencia_hint: item ? hintDesdeItem(item, cant, divisor) : '',
            });
        });
        if (!filas.length) {
            alert('Indique la cantidad a usar.');
            return false;
        }
        estado = { modo: 'insumos', insumos: filas, stock_aplicado: false };
        persistir();
        modalDetConfirmado = true;
        $('#modalActDetInsumos').modal('hide');
        return true;
    }

    function pintarRiegoModal() {
        const cont = document.getElementById('actDetRiegoLista');
        if (!cont) return;
        cont.innerHTML = '';
        tiposRiego.forEach(function (t) {
            const card = document.createElement('div');
            card.className = 'act-det-riego-card' + (estado.riego && estado.riego.key === t.key ? ' is-selected' : '');
            card.dataset.key = t.key;
            card.dataset.label = t.label;
            card.innerHTML = '<div class="act-det-riego-card__title">' + t.label + '</div><div class="act-det-riego-card__desc">' + t.descripcion + '</div>';
            card.addEventListener('click', function () {
                cont.querySelectorAll('.act-det-riego-card').forEach(function (c) { c.classList.remove('is-selected'); });
                card.classList.add('is-selected');
            });
            cont.appendChild(card);
        });
    }

    function confirmarRiego() {
        const sel = document.querySelector('#actDetRiegoLista .act-det-riego-card.is-selected');
        if (!sel) { alert('Seleccione un tipo de riego.'); return false; }
        const tipo = tiposRiego.find(function (t) { return t.key === sel.dataset.key; });
        estado = {
            modo: 'riego',
            riego: { key: sel.dataset.key, label: sel.dataset.label },
            stock_aplicado: false,
        };
        persistir();
        modalDetConfirmado = true;
        $('#modalActDetRiego').modal('hide');
        return true;
    }

    function abrirModalParaTipo() {
        if (!selTipo || !selTipo.value) return;
        const opt = selTipo.options[selTipo.selectedIndex];
        tipoNombreActual = opt ? opt.textContent.trim() : '';
        tipoSlugActual = slugDesdeTipoNombre(tipoNombreActual);
        const loteId = obtenerLoteId();

        if (tipoSlugActual === 'riego') {
            modalDetConfirmado = false;
            pintarRiegoModal();
            $('#modalActDetRiego').modal('show');
            return;
        }
        if (!tipoSlugActual || tipoSlugActual === 'riego') return;

        document.getElementById('modalActDetInsumosTitulo').textContent = 'Seleccionar — ' + tipoNombreActual;
        document.getElementById('actDetInsumosAyuda').textContent = (tipoSlugActual === 'material_siembra' ? 1 : 10) === 1
            ? 'Elija un insumo y la cantidad. Todo se descuenta de la bodega agrícola al completar la actividad.'
            : 'Puede elegir varios insumos. La dosis sugerida por hectárea se reparte entre los que seleccione.';

        modalDetConfirmado = false;
        mostrarCargandoModal();
        $('#modalActDetInsumos').modal('show');

        cargarCatalogo(tipoSlugActual, loteId).then(function (json) {
            catalogoActual = json.data || [];
            pintarInsumosModal(catalogoActual, json.meta || {});
        }).catch(function () {
            const lista = document.getElementById('actDetInsumosLista');
            if (lista) {
                lista.innerHTML = '<div class="alert alert-danger small mb-0">No se pudo cargar el catálogo. Intente de nuevo.</div>';
            }
        });
    }

    function revertirTipoSinDetalle() {
        if (!selTipo || modalDetConfirmado) return;
        const slug = slugDesdeTipoNombre(selTipo.options[selTipo.selectedIndex]?.textContent || '');
        const requiereDetalle = slug === 'fertilizantes' || slug === 'pesticidas' || slug === 'material_siembra' || slug === 'riego';
        if (!requiereDetalle) return;
        const ok = (slug === 'riego' && estado.riego) || ((slug === 'fertilizantes' || slug === 'pesticidas' || slug === 'material_siembra') && estado.insumos.length);
        if (!ok) {
            selTipo.value = '';
            estado = { modo: null, insumos: [], riego: null };
            persistir();
        }
    }

    $('#modalActDetInsumos').on('hidden.bs.modal', revertirTipoSinDetalle);
    $('#modalActDetRiego').on('hidden.bs.modal', revertirTipoSinDetalle);

    document.getElementById('selector_wrap_actividad_lote')?.addEventListener('selector-catalogo:change', function () {
        cacheCatalogo = {};
    });

    if (selTipo && !modoSiembra) {
        selTipo.addEventListener('change', function () {
            estado = { modo: null, insumos: [], riego: null };
            persistir();
            if (selTipo.value) abrirModalParaTipo();
        });
    }

    document.getElementById('btnActDetInsumosConfirmar')?.addEventListener('click', confirmarInsumos);
    document.getElementById('btnActDetRiegoConfirmar')?.addEventListener('click', confirmarRiego);
    document.getElementById('btnActDetEditar')?.addEventListener('click', abrirModalParaTipo);

    document.getElementById('formActividad')?.addEventListener('submit', function (e) {
        if (!selTipo || !selTipo.value) return;
        const slug = slugDesdeTipoNombre(selTipo.options[selTipo.selectedIndex]?.textContent || '');
        if ((slug === 'fertilizantes' || slug === 'pesticidas' || slug === 'material_siembra') && (!estado.insumos || !estado.insumos.length)) {
            e.preventDefault();
            alert('Debe completar el detalle de insumos en el modal.');
            abrirModalParaTipo();
            return;
        }
        if ((slug === 'fertilizantes' || slug === 'pesticidas' || slug === 'material_siembra') && estado.insumos.length) {
            for (const ins of estado.insumos) {
                if (!ins.cantidad || ins.cantidad <= 0 || ins.cantidad > Number(ins.stock || 0)) {
                    e.preventDefault();
                    alert('Revise la cantidad de «' + ins.nombre + '». Máximo: ' + fmtNum(ins.stock) + ' ' + ins.unidad + '.');
                    return;
                }
            }
        }
        if (slug === 'riego' && (!estado.riego || !estado.riego.key)) {
            e.preventDefault();
            alert('Debe elegir el tipo de riego.');
            abrirModalParaTipo();
        }
    });

    if (modoSiembra) {
        renderCalcSiembra(document.getElementById('siembraCalcInline'), sugerenciaSiembraInicial);
        const cont = document.getElementById('siembraInsumosInline');
        const cantWrap = document.getElementById('siembraCantidadWrap');
        const cantInput = document.getElementById('siembraCantidadInput');
        const cantUnidad = document.getElementById('siembraCantidadUnidad');

        if (cont && insumosSiembraInicial.length) {
            insumosSiembraInicial.forEach(function (item) {
                const card = document.createElement('div');
                card.className = 'act-det-insumo-card';
                card.dataset.id = item.id;
                card.innerHTML =
                    '<img src="' + (item.imagen || '') + '" alt="" class="act-det-insumo-thumb" loading="lazy">' +
                    '<div><strong>' + item.nombre + '</strong><br><small class="text-muted">En bodega: ' + fmtNum(item.stock) + ' ' + item.unidad + '</small></div>';
                card.addEventListener('click', function () {
                    cont.querySelectorAll('.act-det-insumo-card').forEach(function (c) { c.classList.remove('is-selected'); });
                    card.classList.add('is-selected');
                    cantWrap?.classList.remove('d-none');
                    if (cantUnidad) cantUnidad.textContent = item.unidad;
                    if (cantInput) {
                        cantInput.dataset.id = item.id;
                        cantInput.dataset.nombre = item.nombre;
                        cantInput.dataset.unidad = item.unidad;
                        cantInput.dataset.stock = item.stock;
                        cantInput.value = (sugerenciaSiembraInicial && sugerenciaSiembraInicial.tiene_dosis) ? sugerenciaSiembraInicial.sugerido : '';
                        bindCantidadNumerica(cantInput, Number(item.stock));
                    }
                });
                cont.appendChild(card);
            });
        } else if (cont) {
            cont.innerHTML = '<div class="alert alert-warning small">No hay material de siembra con stock. <a href="{{ route('insumos.create') }}">Registrar insumo</a></div>';
        }

        document.getElementById('formSiembra')?.addEventListener('submit', function (e) {
            const card = cont?.querySelector('.act-det-insumo-card.is-selected');
            const stock = parseFloat(cantInput?.dataset.stock || '0');
            const cant = aplicarLimiteCantidad(cantInput, stock);
            if (!card || cant === null || cant <= 0) {
                e.preventDefault();
                alert('Seleccione el material de siembra y la cantidad a usar.');
                return;
            }
            estado = {
                modo: 'insumos',
                insumos: [{
                    insumoid: Number(cantInput.dataset.id),
                    nombre: cantInput.dataset.nombre,
                    cantidad: cant,
                    unidad: cantInput.dataset.unidad,
                    stock: stock,
                }],
                stock_aplicado: false,
            };
            persistir(false);
        });
    }

    try {
        if (hiddenJson && hiddenJson.value) {
            const parsed = JSON.parse(hiddenJson.value);
            if (parsed && typeof parsed === 'object') estado = parsed;
            persistir();
        }
    } catch (err) { /* ignore */ }
})();
</script>
@endpush
