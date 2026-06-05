@extends('layouts.app')

@section('title', 'Reportes de distribución | AgroFusion')
@section('page_title', 'Reportes de distribución')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Envíos</a></li>
    <li class="breadcrumb-item active">Reportes de distribución</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
@php $c = $counts ?? []; @endphp
<div class="modulo-env page-env-reportes">

    <div class="env-page-intro mb-3">
        <strong><i class="fas fa-chart-pie text-success mr-1"></i> Reportes de distribución</strong>
        <span class="d-block small text-muted mt-1">Use los filtros por sección o el panel global. Expanda filas para ver el detalle de envíos.</span>
    </div>

    {{-- Filtros globales --}}
    <div class="card card-modulo-main mb-3">
        <div class="card-header py-3">
            <h3 class="card-title mb-0"><i class="fas fa-sliders-h text-success mr-2"></i>Filtros globales</h3>
        </div>
        <div class="filtros-panel">
            <div class="row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="text-muted small font-weight-bold">Estado de envío</label>
                    <select id="filtroGlobalEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estadosLista ?? [] as $est)
                        <option value="{{ $est }}">{{ ucfirst(str_replace('_', ' ', $est)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="text-muted small font-weight-bold">Destino</label>
                    <select id="filtroGlobalDestino" class="form-control form-control-sm">
                        <option value="">Todos los destinos</option>
                        @foreach($destinosLista ?? [] as $dest)
                        <option value="{{ strtolower($dest) }}">{{ $dest }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="text-muted small font-weight-bold">Mín. cantidad</label>
                    <select id="filtroGlobalMinCant" class="form-control form-control-sm">
                        <option value="">Sin mínimo</option>
                        <option value="2">≥ 2</option>
                        <option value="3">≥ 3</option>
                        <option value="5">≥ 5</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="text-muted small font-weight-bold">Buscar en tablas</label>
                    <input type="text" id="filtroGlobalTexto" class="form-control form-control-sm" placeholder="Transportista, estado, destino...">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarGlobal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $c['total'] ?? 0 }}</h3><p>Asignaciones totales</p></div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-yellow">
                <div class="inner"><h3>{{ $c['pendientes'] ?? 0 }}</h3><p>Pendientes</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-teal">
                <div class="inner"><h3>{{ $c['asignados'] ?? 0 }}</h3><p>Asignados</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-blue">
                <div class="inner"><h3>{{ $c['en_ruta'] ?? 0 }}</h3><p>En ruta</p></div>
                <div class="icon"><i class="fas fa-route"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="small-box small-box-purple">
                <div class="inner"><h3>{{ $c['entregados'] ?? 0 }}</h3><p>Entregados</p></div>
                <div class="icon"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box small-box-orange">
                <div class="inner">
                    <h3>{{ number_format($c['stock_productos_todas_bodegas'] ?? 0, 0) }}</h3>
                    <p>Stock en bodegas</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box small-box-indigo">
                <div class="inner"><h3>{{ $c['lineas_inventario_envio'] ?? 0 }}</h3><p>Líneas inventario</p></div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
    </div>

    {{-- Top transportistas --}}
    <div class="card card-modulo-main mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-trophy text-warning mr-2"></i>Top transportistas</h3>
            <div class="card-tools">
                <span class="contador-filtro mr-2" id="contadorTopTransportistas"></span>
            </div>
        </div>
        <div class="filtros-panel">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="text-muted small font-weight-bold">Buscar transportista</label>
                    <input type="text" id="searchTopTransportista" class="form-control form-control-sm" placeholder="Nombre del transportista...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="text-muted small font-weight-bold">Mín. asignaciones</label>
                    <select id="filtroTopMinAsig" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="2">≥ 2</option>
                        <option value="5">≥ 5</option>
                        <option value="10">≥ 10</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="text-muted small font-weight-bold">Ordenar por</label>
                    <select id="ordenTopTransportista" class="form-control form-control-sm">
                        <option value="cant-desc">Más asignaciones</option>
                        <option value="cant-asc">Menos asignaciones</option>
                        <option value="nombre-asc">Nombre A-Z</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarTopTransportista">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:32px"></th>
                        <th>Transportista</th>
                        <th class="text-right" style="width:140px">Asignaciones</th>
                        <th style="width:120px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topTransportistas ?? [] as $t)
                    @php
                        $u = \App\Models\Usuario::find($t->transportista_usuarioid);
                        $nombre = trim(($u->nombre ?? '').' '.($u->apellido ?? '')) ?: 'N/A';
                        $lista = $enviosPorTransportistaId[$t->transportista_usuarioid] ?? [];
                        $uid = 'rep-trans-'.$t->transportista_usuarioid;
                    @endphp
                    <tr class="fila-estado-toggle fila-filtro-rep"
                        data-texto="{{ strtolower($nombre) }}"
                        data-cant="{{ $t->c }}"
                        data-target="#{{ $uid }}"
                        role="button"
                        tabindex="0"
                        aria-expanded="false"
                        aria-controls="{{ $uid }}">
                        <td><i class="fas fa-chevron-right chevron-estado"></i></td>
                        <td class="font-weight-bold">{{ $nombre }}</td>
                        <td class="text-right font-weight-bold">{{ $t->c }}</td>
                        <td class="text-right" onclick="event.stopPropagation()">
                            <a href="{{ route('envios.transportistas') }}" class="btn btn-xs btn-outline-secondary btn-sm">Ver todos</a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="p-0 border-0">
                            <div class="collapse detalle-estado-envios" id="{{ $uid }}">
                                @include('partials.envios-lista-detalle-collapse', [
                                    'lista' => $lista,
                                    'filtroId' => $uid,
                                    'placeholderFiltro' => 'Código, remitente, estado o destino...',
                                ])
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">Sin datos de transportistas.</td></tr>
                    @endforelse
                    <tr id="sinTopTransportista" style="display:none"><td colspan="4" class="text-muted text-center py-3">Sin coincidencias.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        {{-- Por estado --}}
        <div class="col-lg-6 mb-3">
            <div class="card card-modulo-main h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-chart-bar text-success mr-2"></i>Envíos por estado</h3>
                    <div class="card-tools">
                        <span class="contador-filtro" id="contadorPorEstado"></span>
                    </div>
                </div>
                <div class="filtros-panel">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small font-weight-bold">Estado</label>
                            <select id="selectPorEstado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($estadosLista ?? [] as $est)
                                <option value="{{ $est }}">{{ ucfirst(str_replace('_', ' ', $est)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small font-weight-bold">Buscar</label>
                            <input type="text" id="searchPorEstado" class="form-control form-control-sm" placeholder="Ej: entregado, pendiente...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="text-muted small font-weight-bold">Mín. cant.</label>
                            <select id="minCantPorEstado" class="form-control form-control-sm">
                                <option value="">—</option>
                                <option value="2">≥ 2</option>
                                <option value="5">≥ 5</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarPorEstado">Limpiar</button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-modulo table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:32px"></th>
                                <th>Estado</th>
                                <th class="text-right">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($porEstado ?? [] as $estado => $cant)
                            @php
                                $lista = $enviosPorEstado[$estado] ?? [];
                                $uid = 'rep-est-'.preg_replace('/[^a-z0-9]+/', '-', $estado);
                            @endphp
                            <tr class="fila-estado-toggle fila-filtro-rep-est"
                                data-texto="{{ $estado }}"
                                data-cant="{{ $cant }}"
                                data-target="#{{ $uid }}"
                                role="button"
                                tabindex="0"
                                aria-expanded="false"
                                aria-controls="{{ $uid }}">
                                <td><i class="fas fa-chevron-right chevron-estado"></i></td>
                                <td class="text-capitalize">{{ $estado }}</td>
                                <td class="text-right font-weight-bold">{{ $cant }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="p-0 border-0">
                                    <div class="collapse detalle-estado-envios" id="{{ $uid }}">
                                        @include('partials.envios-lista-detalle-collapse', ['lista' => $lista, 'filtroId' => $uid])
                                        <div class="p-2 border-top bg-white text-right">
                                            <a href="{{ route('envios.seguimiento') }}?estado={{ urlencode($estado) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-route mr-1"></i> Ver todos en seguimiento
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">Sin datos.</td></tr>
                            @endforelse
                            <tr id="sinPorEstado" style="display:none"><td colspan="3" class="text-muted text-center py-3">Sin coincidencias.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Por destino --}}
        <div class="col-lg-6 mb-3">
            <div class="card card-modulo-main h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-map-marker-alt text-success mr-2"></i>Envíos por destino</h3>
                    <div class="card-tools">
                        <span class="contador-filtro" id="contadorPorDestino"></span>
                    </div>
                </div>
                <div class="filtros-panel">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small font-weight-bold">Destino</label>
                            <select id="selectPorDestino" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($destinosLista ?? [] as $dest)
                                <option value="{{ strtolower($dest) }}">{{ $dest }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small font-weight-bold">Buscar</label>
                            <input type="text" id="searchPorDestino" class="form-control form-control-sm" placeholder="Planta, ciudad, almacén...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="text-muted small font-weight-bold">Mín. cant.</label>
                            <select id="minCantPorDestino" class="form-control form-control-sm">
                                <option value="">—</option>
                                <option value="2">≥ 2</option>
                                <option value="3">≥ 3</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarPorDestino">Limpiar</button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-modulo table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:32px"></th>
                                <th>Destino</th>
                                <th class="text-right">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($porDestino ?? [] as $destino => $cant)
                            @php
                                $lista = $enviosPorDestino[$destino] ?? [];
                                $uid = 'rep-dest-'.md5($destino);
                            @endphp
                            <tr class="fila-estado-toggle fila-filtro-rep-dest"
                                data-texto="{{ strtolower($destino) }}"
                                data-cant="{{ $cant }}"
                                data-target="#{{ $uid }}"
                                role="button"
                                tabindex="0"
                                aria-expanded="false"
                                aria-controls="{{ $uid }}">
                                <td><i class="fas fa-chevron-right chevron-estado"></i></td>
                                <td>{{ $destino }}</td>
                                <td class="text-right font-weight-bold">{{ $cant }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="p-0 border-0">
                                    <div class="collapse detalle-estado-envios" id="{{ $uid }}">
                                        @include('partials.envios-lista-detalle-collapse', ['lista' => $lista, 'filtroId' => $uid])
                                        <div class="p-2 border-top bg-white text-right">
                                            <a href="{{ route('envios.seguimiento') }}?destino={{ urlencode($destino) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-route mr-1"></i> Ver todos en seguimiento
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">Sin datos.</td></tr>
                            @endforelse
                            <tr id="sinPorDestino" style="display:none"><td colspan="3" class="text-muted text-center py-3">Sin coincidencias.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(function () {
    function initFiltroTabla(config) {
        const rows = Array.from(document.querySelectorAll(config.rowSelector));
        const total = rows.length;
        const searchEl = document.getElementById(config.searchId);
        const selectEl = config.selectId ? document.getElementById(config.selectId) : null;
        const minCantEl = config.minCantId ? document.getElementById(config.minCantId) : null;
        const contadorEl = document.getElementById(config.contadorId);
        const sinEl = document.getElementById(config.sinResultadosId);
        const tbody = rows[0]?.closest('tbody');

        function aplicar() {
            const q = (searchEl?.value || '').trim().toLowerCase();
            const sel = (selectEl?.value || '').trim().toLowerCase();
            const minCant = parseInt(minCantEl?.value || '0', 10) || 0;
            let visibles = 0;

            rows.forEach(tr => {
                const texto = (tr.dataset.texto || '').toLowerCase();
                const cant = parseInt(tr.dataset.cant || '0', 10);
                const matchQ = !q || texto.includes(q);
                const matchSel = !sel || texto === sel || texto.includes(sel);
                const matchMin = !minCant || cant >= minCant;
                const show = matchQ && matchSel && matchMin;
                tr.style.display = show ? '' : 'none';
                const next = tr.nextElementSibling;
                if (next && next.querySelector('.collapse')) {
                    if (!show) {
                        next.style.display = 'none';
                        $(next.querySelector('.collapse')).collapse('hide');
                    } else {
                        next.style.display = '';
                    }
                }
                if (show) visibles++;
            });

            if (contadorEl) {
                contadorEl.textContent = visibles === total
                    ? `${total} registro(s)`
                    : `${visibles} de ${total}`;
            }
            if (sinEl) sinEl.style.display = visibles === 0 && total > 0 ? '' : 'none';
        }

        searchEl?.addEventListener('input', aplicar);
        selectEl?.addEventListener('change', aplicar);
        minCantEl?.addEventListener('change', aplicar);
        document.getElementById(config.limpiarId)?.addEventListener('click', () => {
            if (searchEl) searchEl.value = '';
            if (selectEl) selectEl.value = '';
            if (minCantEl) minCantEl.value = '';
            aplicar();
        });
        aplicar();

        return { aplicar, rows, tbody };
    }

    const topConfig = initFiltroTabla({
        rowSelector: '.fila-filtro-rep',
        searchId: 'searchTopTransportista',
        minCantId: 'filtroTopMinAsig',
        contadorId: 'contadorTopTransportistas',
        sinResultadosId: 'sinTopTransportista',
        limpiarId: 'btnLimpiarTopTransportista'
    });

    document.getElementById('ordenTopTransportista')?.addEventListener('change', function () {
        const tbody = topConfig.tbody;
        if (!tbody) return;
        const pairs = topConfig.rows.map(tr => ({ tr, next: tr.nextElementSibling }));
        pairs.sort((a, b) => {
            const ca = parseInt(a.tr.dataset.cant || '0', 10);
            const cb = parseInt(b.tr.dataset.cant || '0', 10);
            const na = a.tr.dataset.texto || '';
            const nb = b.tr.dataset.texto || '';
            if (this.value === 'cant-asc') return ca - cb;
            if (this.value === 'nombre-asc') return na.localeCompare(nb);
            return cb - ca;
        });
        pairs.forEach(({ tr, next }) => {
            tbody.appendChild(tr);
            if (next) tbody.appendChild(next);
        });
    });

    initFiltroTabla({
        rowSelector: '.fila-filtro-rep-est',
        searchId: 'searchPorEstado',
        selectId: 'selectPorEstado',
        minCantId: 'minCantPorEstado',
        contadorId: 'contadorPorEstado',
        sinResultadosId: 'sinPorEstado',
        limpiarId: 'btnLimpiarPorEstado'
    });
    initFiltroTabla({
        rowSelector: '.fila-filtro-rep-dest',
        searchId: 'searchPorDestino',
        selectId: 'selectPorDestino',
        minCantId: 'minCantPorDestino',
        contadorId: 'contadorPorDestino',
        sinResultadosId: 'sinPorDestino',
        limpiarId: 'btnLimpiarPorDestino'
    });

    function aplicarFiltroGlobal() {
        const estado = (document.getElementById('filtroGlobalEstado')?.value || '').toLowerCase();
        const destino = (document.getElementById('filtroGlobalDestino')?.value || '').toLowerCase();
        const minCant = parseInt(document.getElementById('filtroGlobalMinCant')?.value || '0', 10) || 0;
        const texto = (document.getElementById('filtroGlobalTexto')?.value || '').trim().toLowerCase();

        document.querySelectorAll('.fila-filtro-rep, .fila-filtro-rep-est, .fila-filtro-rep-dest').forEach(tr => {
            const t = (tr.dataset.texto || '').toLowerCase();
            const cant = parseInt(tr.dataset.cant || '0', 10);
            let show = true;
            if (texto && !t.includes(texto)) show = false;
            if (minCant && cant < minCant) show = false;
            if (estado && tr.classList.contains('fila-filtro-rep-est') && t !== estado) show = false;
            if (destino && tr.classList.contains('fila-filtro-rep-dest') && t !== destino) show = false;
            tr.style.display = show ? '' : 'none';
            const next = tr.nextElementSibling;
            if (next && next.querySelector('.collapse')) {
                next.style.display = show ? '' : 'none';
                if (!show) $(next.querySelector('.collapse')).collapse('hide');
            }
        });
    }

    ['filtroGlobalEstado', 'filtroGlobalDestino', 'filtroGlobalMinCant'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', aplicarFiltroGlobal);
    });
    document.getElementById('filtroGlobalTexto')?.addEventListener('input', aplicarFiltroGlobal);
    document.getElementById('btnLimpiarGlobal')?.addEventListener('click', () => {
        ['filtroGlobalEstado', 'filtroGlobalDestino', 'filtroGlobalMinCant', 'filtroGlobalTexto'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        aplicarFiltroGlobal();
    });

    function aplicarFiltroDetalleEnvios(input) {
        const listId = input.dataset.lista;
        const ul = document.getElementById(listId);
        if (!ul) return;
        const q = (input.value || '').trim().toLowerCase();
        let visibles = 0;
        ul.querySelectorAll('.fila-detalle-envio').forEach(li => {
            const match = !q || (li.dataset.texto || '').includes(q);
            li.style.display = match ? '' : 'none';
            if (match) visibles++;
        });
        const sin = ul.querySelector('.sin-coincidencias-detalle');
        const total = ul.querySelectorAll('.fila-detalle-envio').length;
        if (sin) sin.style.display = visibles === 0 && total > 0 ? '' : 'none';
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('filtro-detalle-envios')) {
            aplicarFiltroDetalleEnvios(e.target);
        }
    });

    function toggleFilaReporte($row) {
        const target = $row.data('target');
        if (!target) return;
        $(target).collapse('toggle');
    }

    $(document).on('click', '.fila-estado-toggle[data-target]', function (e) {
        if ($(e.target).closest('a, button, .btn, input, select, label').length) return;
        toggleFilaReporte($(this));
    }).on('keydown', '.fila-estado-toggle[data-target]', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleFilaReporte($(this));
        }
    });

    $('.detalle-estado-envios').on('show.bs.collapse', function () {
        $('[data-target="#' + this.id + '"]').attr('aria-expanded', 'true');
    }).on('hide.bs.collapse', function () {
        $('[data-target="#' + this.id + '"]').attr('aria-expanded', 'false');
        $(this).find('.filtro-detalle-envios').each(function () {
            this.value = '';
            aplicarFiltroDetalleEnvios(this);
        });
    });
});
</script>
@endpush
