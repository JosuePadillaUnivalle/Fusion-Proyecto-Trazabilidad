@extends('layouts.app')

@section('title', ucfirst($naturaleza) . ' de almacén')
@section('page_title', ucfirst($naturaleza) . ' de almacén')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.field-hint{font-size:.8rem;color:#6c757d;margin-top:.25rem}
#tipo-ayuda-box{border-left:3px solid #17a2b8}
.campo-seleccion-readonly{background:#f8f9fa;cursor:pointer}
#buscar-ref-tbody tr,#buscar-dest-tbody tr{cursor:pointer}
#buscar-ref-tbody tr:hover,#buscar-dest-tbody tr:hover{background:#e8f4fd}
</style>
@endpush

@section('content')
    @php
        $campos = $guias['campos'] ?? [];
        $almacenPreseleccion = old('almacenid', $sugerenciasIniciales['almacenid'] ?? $almacenes->first()?->almacenid);
    @endphp
    <div class="card x-card">
        <div class="card-header">
            <h3 class="card-title mb-0">Registrar {{ $naturaleza === 'ingreso' ? 'ingreso' : 'salida' }}</h3>
        </div>
        <form method="POST" action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.store', ['naturaleza' => $naturaleza]) }}" id="form-movimiento">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>No se pudo guardar:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Primero elija el <strong>almacén</strong>; el listado de <strong>insumos</strong> mostrará solo los de ese almacén.
                </div>

                <div class="card card-outline card-secondary collapsed-card mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title small mb-0">
                            <i class="fas fa-question-circle text-info mr-1"></i> Guía rápida de campos
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2 small" style="display:none;">
                        <div class="row">
                            @foreach(['almacen', 'insumo', 'tipo', 'fecha', 'cantidad', 'referencia', 'destino_motivo', 'observaciones'] as $clave)
                                @if(!empty($campos[$clave]))
                                    <div class="col-md-6 mb-2">
                                        <strong class="text-capitalize">{{ str_replace('_', ' ', $clave) }}:</strong>
                                        {{ $campos[$clave] }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @if(!empty($guias['tipos'][$naturaleza]))
                            <hr class="my-2">
                            <p class="mb-1 font-weight-bold">Tipos de {{ $naturaleza }}:</p>
                            <ul class="mb-0 pl-3">
                                @foreach($guias['tipos'][$naturaleza] as $nombreTipo => $desc)
                                    <li><strong>{{ $nombreTipo }}:</strong> {{ $desc }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Almacén <span class="text-danger">*</span></label>
                        <select name="almacenid" id="almacenid" class="form-control @error('almacenid') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->almacenid }}" @selected((int) $almacenPreseleccion === (int) $almacen->almacenid)>{{ $almacen->nombre }}</option>
                            @endforeach
                        </select>
                        @if(!empty($campos['almacen']))<p class="field-hint mb-0">{{ $campos['almacen'] }}</p>@endif
                        @error('almacenid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Insumo <span class="text-danger">*</span></label>
                        <select name="insumoid" id="insumoid" class="form-control @error('insumoid') is-invalid @enderror" required disabled>
                            <option value="">Primero seleccione almacén...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->insumoid }}"
                                        data-almacenid="{{ $insumo->almacenid }}"
                                        @selected(old('insumoid') == $insumo->insumoid)>
                                    {{ $insumo->nombre }} (Stock: {{ number_format((float) $insumo->stock, 3) }} {{ $insumo->unidadMedida?->abreviatura }})
                                </option>
                            @endforeach
                        </select>
                        @if(!empty($campos['insumo']))<p class="field-hint mb-0">{{ $campos['insumo'] }}</p>@endif
                        @error('insumoid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <select name="tipo_movimiento_almacenid" id="tipo_movimiento_almacenid" class="form-control @error('tipo_movimiento_almacenid') is-invalid @enderror" required>
                            <option value="">Seleccione...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->tipo_movimiento_almacenid }}"
                                        data-ayuda="{{ $tiposAyudaPorId[$tipo->tipo_movimiento_almacenid] ?? '' }}"
                                        @selected(old('tipo_movimiento_almacenid') == $tipo->tipo_movimiento_almacenid)>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if(!empty($campos['tipo']))<p class="field-hint mb-1">{{ $campos['tipo'] }}</p>@endif
                        <div id="tipo-ayuda-box" class="alert alert-light py-2 px-3 mb-0 small" style="display:none;"></div>
                        @error('tipo_movimiento_almacenid')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', now()->toDateString()) }}" required>
                        @if(!empty($campos['fecha']))<p class="field-hint mb-0">{{ $campos['fecha'] }}</p>@endif
                        @error('fecha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Cantidad <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0.001" name="cantidad" class="form-control @error('cantidad') is-invalid @enderror" value="{{ old('cantidad') }}" required>
                        @if(!empty($campos['cantidad']))<p class="field-hint mb-0">{{ $campos['cantidad'] }}</p>@endif
                        @error('cantidad')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Referencia</label>
                        <div class="input-group mb-1">
                            <input type="text" name="referencia" id="referencia"
                                   class="form-control campo-seleccion-readonly"
                                   value="{{ old('referencia') }}" maxlength="100"
                                   placeholder="Sin documento seleccionado" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btn-abrir-buscador-ref" title="Buscar documento" disabled>
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-link btn-sm p-0" id="btn-referencia-manual">Escribir referencia manualmente</button>
                        <p id="referencia_estado" class="field-hint mb-0 text-muted">Use el buscador para elegir un documento del sistema.</p>
                        @if(!empty($campos['referencia']))<p class="field-hint mb-0">{{ $campos['referencia'] }}</p>@endif
                    </div>
                    <div class="form-group col-md-3">
                        <label>Destino / motivo</label>
                        <div class="input-group mb-1">
                            <input type="text" name="destino_motivo" id="destino_motivo"
                                   class="form-control campo-seleccion-readonly"
                                   value="{{ old('destino_motivo') }}" maxlength="150"
                                   placeholder="Sin destino seleccionado" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btn-abrir-buscador-dest" title="Buscar destino" disabled>
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-link btn-sm p-0" id="btn-destino-manual">Escribir destino manualmente</button>
                        <p id="destino_estado" class="field-hint mb-0 text-muted">Use el buscador en la pestaña Destino / motivo.</p>
                        @if(!empty($campos['destino_motivo']))<p class="field-hint mb-0">{{ $campos['destino_motivo'] }}</p>@endif
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3" class="form-control" placeholder="Notas adicionales (opcional)">{{ old('observaciones') }}</textarea>
                    @if(!empty($campos['observaciones']))<p class="field-hint mb-0">{{ $campos['observaciones'] }}</p>@endif
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => $naturaleza]) }}" class="btn btn-secondary">Volver</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>

    @include('almacen_movimientos.partials.buscador_documentos_modal')
@endsection

@push('scripts')
<script>
(function () {
    const almacenSelect = document.getElementById('almacenid');
    const insumoSelect = document.getElementById('insumoid');
    const tipoSelect = document.getElementById('tipo_movimiento_almacenid');
    const tipoAyudaBox = document.getElementById('tipo-ayuda-box');
    const referenciaInput = document.getElementById('referencia');
    const destinoInput = document.getElementById('destino_motivo');
    const referenciaEstado = document.getElementById('referencia_estado');
    const destinoEstado = document.getElementById('destino_estado');
    const btnBuscarRef = document.getElementById('btn-abrir-buscador-ref');
    const btnBuscarDest = document.getElementById('btn-abrir-buscador-dest');
    const urlReferencias = @json(route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.referencias'));
    const sugerenciasIniciales = @json($sugerenciasIniciales ?? ['grupos' => [], 'destinos' => []]);
    const LIMITE_FILAS = 80;

    let catalogoReferencias = [];
    let catalogoDestinos = [];
    let mapaReferencias = {};
    let referenciaManual = false;
    let destinoManual = false;

    function flattenGrupos(grupos) {
        const out = [];
        (grupos || []).forEach(function (g) {
            (g.items || []).forEach(function (item) {
                out.push({
                    valor: item.valor,
                    detalle: item.detalle || '',
                    grupo: g.grupo || 'General',
                    destino: item.destino || null,
                    ya_registrada: !!item.ya_registrada
                });
            });
        });
        return out;
    }

    function normalizar(s) {
        return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function llenarFiltroCategorias(selectEl, items) {
        if (!selectEl) return;
        const actual = selectEl.value;
        const cats = [...new Set(items.map(function (i) { return i.grupo; }))].sort();
        selectEl.innerHTML = '<option value="">Todas las categorías</option>';
        cats.forEach(function (c) {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            selectEl.appendChild(opt);
        });
        if (actual && cats.indexOf(actual) >= 0) selectEl.value = actual;
    }

    function filtrarCatalogo(items, texto, categoria) {
        const q = normalizar(texto.trim());
        return items.filter(function (item) {
            if (categoria && item.grupo !== categoria) return false;
            if (!q) return true;
            const blob = normalizar(item.valor + ' ' + item.detalle + ' ' + item.grupo);
            return blob.indexOf(q) >= 0;
        });
    }

    function renderTabla(tipo) {
        const esRef = tipo === 'ref';
        const items = esRef ? catalogoReferencias : catalogoDestinos;
        const texto = document.getElementById(esRef ? 'buscar-ref-texto' : 'buscar-dest-texto');
        const categoria = document.getElementById(esRef ? 'buscar-ref-categoria' : 'buscar-dest-categoria');
        const tbody = document.getElementById(esRef ? 'buscar-ref-tbody' : 'buscar-dest-tbody');
        const resumen = document.getElementById(esRef ? 'buscar-ref-resumen' : 'buscar-dest-resumen');
        if (!tbody) return;

        const filtrados = filtrarCatalogo(items, texto ? texto.value : '', categoria ? categoria.value : '');
        const mostrar = filtrados.slice(0, LIMITE_FILAS);
        tbody.innerHTML = '';

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay datos para este almacén.</td></tr>';
            if (resumen) resumen.textContent = 'Seleccione un almacén y espere la carga.';
            return;
        }

        if (!filtrados.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin coincidencias. Pruebe otro texto o categoría.</td></tr>';
        } else {
            mostrar.forEach(function (item) {
                const tr = document.createElement('tr');
                if (esRef) {
                    tr.innerHTML =
                        '<td><code>' + escapeHtml(item.valor) + '</code>' +
                        (item.ya_registrada ? ' <span class="badge badge-warning">ya registrado</span>' : '') + '</td>' +
                        '<td class="small">' + escapeHtml(item.grupo) + '</td>' +
                        '<td class="small">' + escapeHtml(item.detalle) + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-sm btn-primary btn-elegir-ref">Elegir</button></td>';
                    tr.querySelector('.btn-elegir-ref').addEventListener('click', function () { seleccionarReferencia(item); });
                } else {
                    tr.innerHTML =
                        '<td>' + escapeHtml(item.valor) + '</td>' +
                        '<td class="small">' + escapeHtml(item.grupo) + '</td>' +
                        '<td class="small">' + escapeHtml(item.detalle) + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-sm btn-primary btn-elegir-dest">Elegir</button></td>';
                    tr.querySelector('.btn-elegir-dest').addEventListener('click', function () { seleccionarDestino(item); });
                }
                tr.addEventListener('dblclick', function () {
                    esRef ? seleccionarReferencia(item) : seleccionarDestino(item);
                });
                tbody.appendChild(tr);
            });
        }

        if (resumen) {
            let msg = 'Mostrando ' + mostrar.length + ' de ' + filtrados.length + ' resultado(s)';
            if (filtrados.length > LIMITE_FILAS) msg += ' — refine la búsqueda para ver todos';
            if (!texto || !texto.value.trim()) msg += ' · Escriba para filtrar';
            resumen.textContent = msg;
        }
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function aplicarModoManual(input, manual) {
        if (!input) return;
        if (manual) {
            input.removeAttribute('readonly');
            input.classList.remove('campo-seleccion-readonly');
            input.focus();
        } else {
            input.setAttribute('readonly', 'readonly');
            input.classList.add('campo-seleccion-readonly');
        }
    }

    function seleccionarReferencia(item) {
        referenciaManual = false;
        aplicarModoManual(referenciaInput, false);
        referenciaInput.value = item.valor;
        if (item.destino) {
            seleccionarDestino({ valor: item.destino, detalle: 'Desde documento', grupo: '' }, true);
        }
        if (referenciaEstado) {
            referenciaEstado.textContent = item.grupo + ' · ' + item.detalle;
        }
        if (window.jQuery) jQuery('#modalBuscadorDocs').modal('hide');
    }

    function seleccionarDestino(item, silencioso) {
        destinoManual = false;
        aplicarModoManual(destinoInput, false);
        destinoInput.value = item.valor;
        if (destinoEstado && !silencioso) {
            destinoEstado.textContent = item.grupo + ' · ' + item.detalle;
        }
        if (window.jQuery && !silencioso) jQuery('#modalBuscadorDocs').modal('hide');
    }

    function abrirBuscador(tab) {
        if (!almacenSelect || !almacenSelect.value) {
            alert('Seleccione primero un almacén.');
            return;
        }
        renderTabla('ref');
        renderTabla('dest');
        if (window.jQuery) {
            jQuery('#modalBuscadorDocs').modal('show');
            if (tab === 'dest') jQuery('#tab-dest-link').tab('show');
            else jQuery('#tab-ref-link').tab('show');
        }
    }

    function aplicarCatalogos(data) {
        catalogoReferencias = flattenGrupos(data.grupos || []);
        catalogoDestinos = flattenGrupos(data.destinos || []);
        mapaReferencias = {};
        catalogoReferencias.forEach(function (i) {
            if (i.destino) mapaReferencias[i.valor] = i.destino;
        });

        document.getElementById('badge-count-ref').textContent = catalogoReferencias.length;
        document.getElementById('badge-count-dest').textContent = catalogoDestinos.length;

        llenarFiltroCategorias(document.getElementById('buscar-ref-categoria'), catalogoReferencias);
        llenarFiltroCategorias(document.getElementById('buscar-dest-categoria'), catalogoDestinos);

        if (btnBuscarRef) btnBuscarRef.disabled = false;
        if (btnBuscarDest) btnBuscarDest.disabled = false;

        if (referenciaEstado) {
            referenciaEstado.textContent = catalogoReferencias.length
                ? catalogoReferencias.length + ' documento(s) — pulse la lupa para buscar y filtrar.'
                : 'Sin documentos en este almacén; puede escribir la referencia manualmente.';
        }
        if (destinoEstado) {
            destinoEstado.textContent = catalogoDestinos.length
                ? catalogoDestinos.length + ' destino(s) — pulse la lupa para buscar.'
                : 'Sin sugerencias; escriba el destino manualmente.';
        }

        renderTabla('ref');
        renderTabla('dest');
    }

    function cargarCatalogos() {
        const almacenId = almacenSelect ? almacenSelect.value : '';
        if (!almacenId) {
            catalogoReferencias = [];
            catalogoDestinos = [];
            if (btnBuscarRef) btnBuscarRef.disabled = true;
            if (btnBuscarDest) btnBuscarDest.disabled = true;
            if (referenciaEstado) referenciaEstado.textContent = 'Elija un almacén primero.';
            if (destinoEstado) destinoEstado.textContent = 'Elija un almacén primero.';
            return;
        }

        if (referenciaEstado) referenciaEstado.textContent = 'Cargando catálogo del sistema…';

        const params = new URLSearchParams({ naturaleza: @json($naturaleza), almacenid: almacenId });
        if (insumoSelect && insumoSelect.value) params.set('insumoid', insumoSelect.value);
        if (tipoSelect && tipoSelect.value) params.set('tipo_movimiento_almacenid', tipoSelect.value);
        if (referenciaInput && referenciaInput.value.trim()) params.set('referencia', referenciaInput.value.trim());

        fetch(urlReferencias + '?' + params.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (data) { aplicarCatalogos(data); })
            .catch(function () {
                if (sugerenciasIniciales.grupos && sugerenciasIniciales.grupos.length) {
                    aplicarCatalogos(sugerenciasIniciales);
                } else if (referenciaEstado) {
                    referenciaEstado.textContent = 'Error al cargar; recargue la página.';
                }
            });
    }

    function actualizarAyudaTipo() {
        if (!tipoSelect || !tipoAyudaBox) return;
        const opt = tipoSelect.options[tipoSelect.selectedIndex];
        const ayuda = opt && opt.dataset.ayuda ? opt.dataset.ayuda : '';
        if (ayuda) {
            tipoAyudaBox.innerHTML = '<i class="fas fa-lightbulb text-warning mr-1"></i><strong>' + (opt.textContent || '') + ':</strong> ' + ayuda;
            tipoAyudaBox.style.display = 'block';
        } else {
            tipoAyudaBox.style.display = 'none';
        }
    }

    if (!almacenSelect || !insumoSelect) return;

    const allOptions = Array.from(insumoSelect.querySelectorAll('option[data-almacenid]'));
    const oldInsumo = @json(old('insumoid'));

    function filtrarInsumos() {
        const almacenId = almacenSelect.value;
        insumoSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = almacenId ? 'Seleccione insumo...' : 'Primero seleccione almacén...';
        insumoSelect.appendChild(placeholder);
        if (!almacenId) { insumoSelect.disabled = true; return; }
        let count = 0;
        allOptions.forEach(function (opt) {
            if (opt.dataset.almacenid === almacenId) {
                insumoSelect.appendChild(opt.cloneNode(true));
                count++;
            }
        });
        insumoSelect.disabled = count === 0;
        const restore = oldInsumo || insumoSelect.value;
        if (restore) insumoSelect.value = String(restore);
    }

    ['buscar-ref-texto', 'buscar-dest-texto'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', function () { renderTabla(id.indexOf('ref') >= 0 ? 'ref' : 'dest'); });
    });
    ['buscar-ref-categoria', 'buscar-dest-categoria'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', function () { renderTabla(id.indexOf('ref') >= 0 ? 'ref' : 'dest'); });
    });
    document.getElementById('buscar-ref-limpiar')?.addEventListener('click', function () {
        const t = document.getElementById('buscar-ref-texto');
        const c = document.getElementById('buscar-ref-categoria');
        if (t) t.value = '';
        if (c) c.value = '';
        renderTabla('ref');
    });
    document.getElementById('buscar-dest-limpiar')?.addEventListener('click', function () {
        const t = document.getElementById('buscar-dest-texto');
        const c = document.getElementById('buscar-dest-categoria');
        if (t) t.value = '';
        if (c) c.value = '';
        renderTabla('dest');
    });

    if (tipoSelect) {
        tipoSelect.addEventListener('change', function () {
            actualizarAyudaTipo();
            cargarCatalogos();
        });
        actualizarAyudaTipo();
    }

    if (btnBuscarRef) btnBuscarRef.addEventListener('click', function () { abrirBuscador('ref'); });
    if (btnBuscarDest) btnBuscarDest.addEventListener('click', function () { abrirBuscador('dest'); });
    if (referenciaInput) {
        referenciaInput.addEventListener('click', function () {
            if (!referenciaManual && catalogoReferencias.length) abrirBuscador('ref');
        });
    }
    if (destinoInput) {
        destinoInput.addEventListener('click', function () {
            if (!destinoManual && catalogoDestinos.length) abrirBuscador('dest');
        });
    }

    document.getElementById('btn-referencia-manual')?.addEventListener('click', function (e) {
        e.preventDefault();
        referenciaManual = true;
        aplicarModoManual(referenciaInput, true);
        referenciaInput.value = '';
        if (referenciaEstado) referenciaEstado.textContent = 'Modo manual: escriba el código de referencia.';
    });
    document.getElementById('btn-destino-manual')?.addEventListener('click', function (e) {
        e.preventDefault();
        destinoManual = true;
        aplicarModoManual(destinoInput, true);
        destinoInput.value = '';
        if (destinoEstado) destinoEstado.textContent = 'Modo manual: escriba el destino o motivo.';
    });

    almacenSelect.addEventListener('change', function () {
        filtrarInsumos();
        const ok = !!almacenSelect.value;
        if (btnBuscarRef) btnBuscarRef.disabled = !ok;
        if (btnBuscarDest) btnBuscarDest.disabled = !ok;
        cargarCatalogos();
    });
    insumoSelect.addEventListener('change', cargarCatalogos);

    filtrarInsumos();
    if (almacenSelect.value) {
        if (btnBuscarRef) btnBuscarRef.disabled = false;
        if (btnBuscarDest) btnBuscarDest.disabled = false;
        aplicarCatalogos(sugerenciasIniciales);
        cargarCatalogos();
    }

    if (referenciaInput && referenciaInput.value.trim()) {
        referenciaManual = true;
        aplicarModoManual(referenciaInput, true);
    }
    if (destinoInput && destinoInput.value.trim()) {
        destinoManual = true;
        aplicarModoManual(destinoInput, true);
    }
})();
</script>
@endpush
