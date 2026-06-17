@php
    $modoSiembra = $modoSiembra ?? false;
    $insumosEndpoint = route('catalogo-selector.insumos-actividad');
@endphp

@push('styles')
<style>
.act-det-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .85rem 1rem;
    margin-top: .5rem;
}
.act-det-panel.is-empty { display: none; }
.act-det-panel__title {
    font-size: .78rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: .35rem;
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
</style>
@endpush

<input type="hidden" name="detalle_actividad_json" id="detalle_actividad_json" value="{{ old('detalle_actividad_json', '') }}">

<div id="actDetResumenPanel" class="act-det-panel is-empty">
    <div class="act-det-panel__title"><i class="fas fa-check-circle text-success mr-1"></i> Detalle registrado</div>
    <div id="actDetResumenTexto" class="small text-muted mb-0"></div>
    <button type="button" class="btn btn-link btn-sm p-0 mt-1" id="btnActDetEditar">Cambiar detalle</button>
</div>

<div class="modal fade" id="modalActDetInsumos" tabindex="-1" role="dialog" aria-hidden="true">
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

<div class="modal fade" id="modalActDetRiego" tabindex="-1" role="dialog" aria-hidden="true">
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
            <input type="number" step="0.01" min="0.01" class="form-control" id="siembraCantidadInput">
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
    const resumenTexto = document.getElementById('actDetResumenTexto');
    const selTipo = document.querySelector('[name="tipoactividadid"]');
    const selLote = document.querySelector('[name="loteid"]');

    let estado = { modo: null, insumos: [], riego: null };
    let catalogoActual = [];
    let maxInsumos = 10;
    let sugerenciaActual = null;
    let tipoSlugActual = '';
    let tipoNombreActual = '';

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

    function persistir() {
        if (hiddenJson) hiddenJson.value = JSON.stringify(estado);
        if (resumenPanel && resumenTexto) {
            const txt = textoResumen();
            if (txt) {
                resumenPanel.classList.remove('is-empty');
                resumenTexto.textContent = txt;
            } else {
                resumenPanel.classList.add('is-empty');
            }
        }
    }

    function textoResumen() {
        if (estado.modo === 'riego' && estado.riego) {
            return 'Riego: ' + estado.riego.label;
        }
        if (estado.modo === 'insumos' && estado.insumos.length) {
            return estado.insumos.map(function (i) {
                return i.nombre + ' (' + fmtNum(i.cantidad) + ' ' + i.unidad + ')';
            }).join(', ');
        }
        return '';
    }

    function cargarCatalogo(tipoSlug, loteId) {
        const url = endpoint + '?tipo_slug=' + encodeURIComponent(tipoSlug) + (loteId ? '&loteid=' + loteId : '');
        return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); });
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
        items.forEach(function (item) {
            const sug = (maxInsumos === 1 && sugerenciaActual && sugerenciaActual.tiene_dosis) ? sugerenciaActual.sugerido : '';
            const prev = estado.insumos.find(function (i) { return Number(i.insumoid) === Number(item.id); });
            const val = prev ? prev.cantidad : sug;
            const linea = document.createElement('div');
            linea.className = 'act-det-linea';
            linea.innerHTML =
                '<div class="flex-grow-1"><small class="text-muted d-block">' + item.nombre + '</small>' +
                '<div class="input-group input-group-sm" style="max-width:200px;">' +
                '<input type="number" step="0.01" min="0.01" max="' + item.stock + '" class="form-control act-det-cant-input" data-id="' + item.id + '" data-nombre="' + item.nombre + '" data-unidad="' + item.unidad + '" data-stock="' + item.stock + '" value="' + (val || '') + '">' +
                '<div class="input-group-append"><span class="input-group-text">' + item.unidad + '</span></div></div></div>';
            wrap.appendChild(linea);
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
        inputs.forEach(function (inp) {
            const cant = parseFloat(inp.value);
            const stock = parseFloat(inp.dataset.stock);
            if (!cant || cant <= 0) return;
            if (cant > stock) {
                alert('La cantidad de «' + inp.dataset.nombre + '» supera el stock (' + fmtNum(stock) + ').');
                throw new Error('stock');
            }
            filas.push({
                insumoid: Number(inp.dataset.id),
                nombre: inp.dataset.nombre,
                cantidad: cant,
                unidad: inp.dataset.unidad,
            });
        });
        if (!filas.length) {
            alert('Indique la cantidad a usar.');
            return false;
        }
        estado = { modo: 'insumos', insumos: filas, stock_aplicado: false };
        persistir();
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
        estado = { modo: 'riego', riego: { key: sel.dataset.key, label: sel.dataset.label }, stock_aplicado: false };
        persistir();
        $('#modalActDetRiego').modal('hide');
        return true;
    }

    function abrirModalParaTipo() {
        if (!selTipo || !selTipo.value) return;
        const opt = selTipo.options[selTipo.selectedIndex];
        tipoNombreActual = opt ? opt.textContent.trim() : '';
        tipoSlugActual = slugDesdeTipoNombre(tipoNombreActual);
        const loteId = selLote ? selLote.value : '';

        if (tipoSlugActual === 'riego') {
            pintarRiegoModal();
            $('#modalActDetRiego').modal('show');
            return;
        }
        if (!tipoSlugActual || tipoSlugActual === 'riego') return;

        document.getElementById('modalActDetInsumosTitulo').textContent = 'Seleccionar — ' + tipoNombreActual;
        document.getElementById('actDetInsumosAyuda').textContent = (tipoSlugActual === 'material_siembra' ? 1 : 10) === 1
            ? 'Elija un insumo y la cantidad. Todo se descuenta de la bodega agrícola al completar la actividad.'
            : 'Puede elegir varios insumos. Indique cuánto usará de cada uno.';

        cargarCatalogo(tipoSlugActual, loteId).then(function (json) {
            catalogoActual = json.data || [];
            pintarInsumosModal(catalogoActual, json.meta || {});
            $('#modalActDetInsumos').modal('show');
        });
    }

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
        }
        if (slug === 'riego' && (!estado.riego || !estado.riego.key)) {
            e.preventDefault();
            alert('Debe elegir el tipo de riego.');
            abrirModalParaTipo();
        }
    });

    // Modo siembra inline
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
                        cantInput.max = item.stock;
                        cantInput.value = (sugerenciaSiembraInicial && sugerenciaSiembraInicial.tiene_dosis) ? sugerenciaSiembraInicial.sugerido : '';
                        cantInput.dataset.id = item.id;
                        cantInput.dataset.nombre = item.nombre;
                        cantInput.dataset.unidad = item.unidad;
                        cantInput.dataset.stock = item.stock;
                    }
                });
                cont.appendChild(card);
            });
        } else if (cont) {
            cont.innerHTML = '<div class="alert alert-warning small">No hay material de siembra con stock. <a href="{{ route('insumos.create') }}">Registrar insumo</a></div>';
        }

        document.getElementById('formSiembra')?.addEventListener('submit', function (e) {
            const card = cont?.querySelector('.act-det-insumo-card.is-selected');
            const cant = parseFloat(cantInput?.value || '');
            if (!card || !cant || cant <= 0) {
                e.preventDefault();
                alert('Seleccione el material de siembra y la cantidad a usar.');
                return;
            }
            const stock = parseFloat(cantInput.dataset.stock);
            if (cant > stock) {
                e.preventDefault();
                alert('La cantidad supera el stock disponible (' + fmtNum(stock) + ').');
                return;
            }
            estado = {
                modo: 'insumos',
                insumos: [{
                    insumoid: Number(cantInput.dataset.id),
                    nombre: cantInput.dataset.nombre,
                    cantidad: cant,
                    unidad: cantInput.dataset.unidad,
                }],
                stock_aplicado: false,
            };
            persistir();
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
