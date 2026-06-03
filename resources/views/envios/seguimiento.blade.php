@extends('layouts.app')

@section('title', 'Seguimiento de Envíos | AgroFusion')
@section('page_title', 'Seguimiento de envíos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Seguimiento de envíos</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
@php $stats = $statsIniciales ?? ['total' => 0, 'pendientes' => 0, 'asignados' => 0, 'curso' => 0, 'parcial' => 0, 'completados' => 0]; @endphp
<div class="modulo-env page-env-seguimiento">

    <div class="card card-modulo-main mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1"><i class="fas fa-shipping-fast text-success mr-2"></i>Panel de seguimiento</h5>
                    <p class="text-muted mb-0 small">
                        {{ $stats['total'] }} envío(s) registrados.
                        @if(($metaInicial['fuente'] ?? '') === 'fusion_local')
                            <span class="text-success">· Datos del sistema local</span>
                        @endif
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('envios.mandar') }}" class="btn btn-success btn-sm mr-1">
                        <i class="fas fa-plus mr-1"></i> Crear envío
                    </a>
                    <a href="{{ route('envios.admin') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-chart-pie mr-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="offline-banner" class="offline-banner" style="display: none;">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                <strong>Modo sin conexión</strong>
                <span class="small text-muted d-block d-md-inline ml-md-2">Mostrando la última copia guardada en este navegador.</span>
            </div>
            <button type="button" class="btn btn-warning btn-sm" id="btnReintentar">
                <i class="fas fa-sync-alt mr-1"></i> Reintentar
            </button>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="small-box small-box-green filter-box active-filter" data-filter="todos">
                <div class="inner">
                    <h3 id="statTodos">{{ $stats['total'] }}</h3>
                    <p>Todos</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <span class="small-box-footer">Ver todos</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="small-box small-box-yellow filter-box" data-filter="pendientes">
                <div class="inner">
                    <h3 id="statPendientes">{{ $stats['pendientes'] }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <span class="small-box-footer">Filtrar</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="small-box small-box-teal filter-box" data-filter="asignados">
                <div class="inner">
                    <h3 id="statAsignados">{{ $stats['asignados'] }}</h3>
                    <p>Asignados</p>
                </div>
                <div class="icon"><i class="fas fa-file-signature"></i></div>
                <span class="small-box-footer">Filtrar</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="small-box small-box-blue filter-box" data-filter="curso">
                <div class="inner">
                    <h3 id="statCurso">{{ $stats['curso'] }}</h3>
                    <p>En curso</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
                <span class="small-box-footer">Filtrar</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="small-box small-box-orange filter-box" data-filter="parcial">
                <div class="inner">
                    <h3 id="statParcial">{{ $stats['parcial'] }}</h3>
                    <p>Parcial</p>
                </div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
                <span class="small-box-footer">Filtrar</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="small-box small-box-purple filter-box" data-filter="completados">
                <div class="inner">
                    <h3 id="statCompletados">{{ $stats['completados'] }}</h3>
                    <p>Completados</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Filtrar</span>
            </div>
        </div>
    </div>

    <div id="envios-locales-section" class="card card-outline card-warning mb-3" style="display: none;">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-cloud-upload-alt mr-2"></i>Pendientes de sincronización</h3>
        </div>
        <div class="card-body">
            <div class="row" id="enviosLocalesGrid"></div>
        </div>
    </div>

    <div class="card card-modulo-main">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-route mr-2 text-success"></i>Listado de envíos</h3>
            <div class="card-tools d-flex align-items-center">
                <span id="sync-hint" class="env-conexion-aviso is-syncing d-none mr-2">
                    <i class="fas fa-sync-alt fa-spin"></i> Actualizando...
                </span>
                <span class="contador-filtro mr-2" id="contadorSeguimiento"></span>
            </div>
        </div>

        <div class="filtros-panel">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label class="text-muted">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="inputBuscarEnvio" class="form-control" placeholder="ID, ruta, remitente...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label class="text-muted">Estado exacto</label>
                    <select id="filterEstadoEnvio" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estadosFiltro ?? [] as $est)
                            <option value="{{ $est }}">{{ ucfirst($est) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label class="text-muted">Destino</label>
                    <select id="filterDestinoEnvio" class="form-control form-control-sm">
                        <option value="">Todos los destinos</option>
                        @foreach($destinosFiltro ?? [] as $dest)
                            <option value="{{ strtolower($dest) }}">{{ $dest }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltrosSeguimiento">
                        <i class="fas fa-times mr-1"></i> Limpiar filtros
                    </button>
                </div>
            </div>
            <p class="small text-muted mb-0 mt-2">
                <i class="fas fa-info-circle mr-1"></i> También puedes filtrar con los recuadros de colores de arriba.
            </p>
        </div>

        <div class="card-body">
            <div class="row" id="envioGrid">
                @forelse($enviosIniciales ?? [] as $envio)
                    @include('partials.envio-card-item', ['envio' => $envio])
                @empty
                    <div class="col-12 text-center text-muted py-5" id="gridEmpty">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Cargando envíos...
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const LOCAL_API_URL = '{{ url('/envios/api') }}';
    const CACHE_KEY = 'AgroFusion_envios_cache_v2';
    const FETCH_LIMIT = 150;
    const ENVIOS_TIMEOUT_MS = 8000;
    const TIENE_DATOS_SERVIDOR = @json(count($enviosIniciales ?? []) > 0);

    let conectado = true;
    let sincronizando = false;
    let envios = @json($enviosIniciales ?? []);
    let enviosLocales = [];
    let activeFilter = 'todos';
    let searchTerm = '';
    let renderTimer = null;

    const grid = document.getElementById('envioGrid');
    const searchInput = document.getElementById('inputBuscarEnvio');
    const filterEstadoSelect = document.getElementById('filterEstadoEnvio');
    const filterDestinoSelect = document.getElementById('filterDestinoEnvio');
    const contadorSeguimiento = document.getElementById('contadorSeguimiento');
    const filterBoxes = document.querySelectorAll('.filter-box');

    let filterEstadoExacto = '';
    let filterDestino = '';

    const FETCH_OPTS = {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    };

    const STATUS_GROUPS = {
        pendientes: (estado) => ['pendiente', 'sin estado', 'sin asignar'].includes(estado),
        asignados: (estado) => ['asignado'].includes(estado),
        curso: (estado) => ['en curso', 'en_ruta', 'en ruta'].includes(estado),
        parcial: (estado) => ['parcialmente entregado', 'parcial'].includes(estado),
        completados: (estado) => ['entregado', 'finalizado', 'completado'].includes(estado),
        todos: () => true
    };

    const STATUS_META = {
        'pendiente': { label: 'Pendiente', badge: 'badge-warning' },
        'sin estado': { label: 'Pendiente', badge: 'badge-warning' },
        'sin asignar': { label: 'Pendiente', badge: 'badge-warning' },
        'asignado': { label: 'Asignado', badge: 'badge-info' },
        'en curso': { label: 'En curso', badge: 'badge-primary' },
        'en_ruta': { label: 'En curso', badge: 'badge-primary' },
        'en ruta': { label: 'En curso', badge: 'badge-primary' },
        'parcialmente entregado': { label: 'Parcial', badge: 'badge-orange' },
        'parcial': { label: 'Parcial', badge: 'badge-warning' },
        'entregado': { label: 'Completado', badge: 'badge-success' },
        'finalizado': { label: 'Completado', badge: 'badge-success' },
        'completado': { label: 'Completado', badge: 'badge-success' },
    };

    function normalizarListaEnvios(payload) {
        if (Array.isArray(payload)) return payload;
        if (payload && Array.isArray(payload.data)) return payload.data;
        return [];
    }

    const ESTADO_A_GRUPO = {
        pendiente: 'pendientes', 'sin estado': 'pendientes', 'sin asignar': 'pendientes',
        asignado: 'asignados',
        'en curso': 'curso', en_ruta: 'curso', 'en ruta': 'curso',
        parcial: 'parcial', 'parcialmente entregado': 'parcial',
        entregado: 'completados', finalizado: 'completados', completado: 'completados',
    };

    filterBoxes.forEach(box => {
        box.addEventListener('click', () => {
            const filter = box.getAttribute('data-filter');
            if (!filter || filter === activeFilter) return;
            activeFilter = filter;
            filterEstadoExacto = '';
            if (filterEstadoSelect) filterEstadoSelect.value = '';
            filterBoxes.forEach(b => b.classList.toggle('active-filter', b.getAttribute('data-filter') === filter));
            scheduleRenderGrid();
        });
    });

    searchInput?.addEventListener('input', (event) => {
        searchTerm = event.target.value.trim().toLowerCase();
        scheduleRenderGrid();
    });

    filterEstadoSelect?.addEventListener('change', function () {
        filterEstadoExacto = (this.value || '').trim().toLowerCase();
        if (filterEstadoExacto) {
            activeFilter = ESTADO_A_GRUPO[filterEstadoExacto] || 'todos';
            filterBoxes.forEach(b => b.classList.toggle('active-filter', b.getAttribute('data-filter') === activeFilter));
        }
        scheduleRenderGrid();
    });

    filterDestinoSelect?.addEventListener('change', function () {
        filterDestino = (this.value || '').trim().toLowerCase();
        scheduleRenderGrid();
    });

    document.getElementById('btnLimpiarFiltrosSeguimiento')?.addEventListener('click', function () {
        searchTerm = '';
        filterEstadoExacto = '';
        filterDestino = '';
        activeFilter = 'todos';
        if (searchInput) searchInput.value = '';
        if (filterEstadoSelect) filterEstadoSelect.value = '';
        if (filterDestinoSelect) filterDestinoSelect.value = '';
        filterBoxes.forEach(b => b.classList.toggle('active-filter', b.getAttribute('data-filter') === 'todos'));
        scheduleRenderGrid();
    });

    function aplicarFiltrosUrl() {
        const params = new URLSearchParams(window.location.search);
        const estado = (params.get('estado') || '').trim().toLowerCase();
        const destino = (params.get('destino') || '').trim().toLowerCase();
        if (estado && filterEstadoSelect) {
            filterEstadoSelect.value = estado;
            filterEstadoExacto = estado;
            activeFilter = ESTADO_A_GRUPO[estado] || 'todos';
            filterBoxes.forEach(b => b.classList.toggle('active-filter', b.getAttribute('data-filter') === activeFilter));
        }
        if (destino && filterDestinoSelect) {
            const opt = Array.from(filterDestinoSelect.options).find(o => o.value === destino || o.textContent.toLowerCase() === destino);
            if (opt) filterDestinoSelect.value = opt.value;
            filterDestino = destino;
        }
    }

    function scheduleRenderGrid() {
        clearTimeout(renderTimer);
        renderTimer = setTimeout(renderGrid, 150);
    }

    function actualizarIndicador() {
        const syncHint = document.getElementById('sync-hint');
        const banner = document.getElementById('offline-banner');

        if (sincronizando) {
            syncHint?.classList.remove('d-none');
            banner.style.display = 'none';
            return;
        }

        syncHint?.classList.add('d-none');

        if (conectado) {
            banner.style.display = 'none';
        } else {
            banner.style.display = 'block';
        }
    }

    function guardarEnCache(data) {
        try {
            const slice = data.slice(0, FETCH_LIMIT);
            localStorage.setItem(CACHE_KEY, JSON.stringify({ envios: slice, timestamp: Date.now() }));
        } catch (e) {
            console.warn('No se pudo guardar caché de envíos', e);
        }
    }

    function obtenerDeCache() {
        try {
            const cached = localStorage.getItem(CACHE_KEY);
            if (!cached) return null;
            const data = JSON.parse(cached);
            return Array.isArray(data.envios) ? data.envios : null;
        } catch (e) {
            return null;
        }
    }

    function cargarEnviosLocales() {
        try {
            const stored = localStorage.getItem('AgroFusion_envios_pendientes');
            enviosLocales = stored ? JSON.parse(stored) : [];
        } catch (e) {
            enviosLocales = [];
        }
        renderEnviosLocales();
    }

    function renderEnviosLocales() {
        const section = document.getElementById('envios-locales-section');
        const localGrid = document.getElementById('enviosLocalesGrid');
        const pendientes = enviosLocales.filter(e => e.estado === 'pendiente');

        if (pendientes.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        localGrid.innerHTML = pendientes.map(envio => `
            <div class="col-md-4 mb-3">
                <div class="card card-outline card-warning envio-local">
                    <div class="card-header py-2">
                        <strong><i class="fas fa-cloud-upload-alt mr-1"></i> Local #${envio.id}</strong>
                        <span class="badge badge-warning float-right">Pendiente sync</span>
                    </div>
                    <div class="card-body py-2">
                        <p class="small text-muted mb-1"><i class="far fa-calendar mr-1"></i>${new Date(envio.fecha).toLocaleDateString('es-BO')}</p>
                        <p class="mb-0 small">${envio.datos?.envio?.nombre_remitente || 'Remitente no indicado'}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function fetchEnviosDesdeRed() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), ENVIOS_TIMEOUT_MS);

        try {
            const res = await fetch(`${LOCAL_API_URL}/envios?limit=${FETCH_LIMIT}`, {
                ...FETCH_OPTS,
                signal: controller.signal,
                cache: 'no-store',
            });
            clearTimeout(timeoutId);

            if (!res.ok) throw new Error('Respuesta no válida');

            const data = await res.json();
            const lista = normalizarListaEnvios(data);

            if (lista.length) {
                envios = lista;
                guardarEnCache(envios);
            } else if (!envios.length) {
                const cached = obtenerDeCache();
                if (cached?.length) envios = cached;
            }

            conectado = true;
            return true;
        } catch (error) {
            clearTimeout(timeoutId);
            console.warn('Error cargando envíos:', error);
            if (envios.length) {
                conectado = true;
                return true;
            }
            const cached = obtenerDeCache();
            if (cached?.length) {
                envios = cached;
                conectado = false;
                return true;
            }
            conectado = false;
            return false;
        }
    }

    async function fetchEnvios(opciones = {}) {
        const { silencioso = false } = opciones;

        if (!silencioso && !envios.length) {
            grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando envíos...</div>';
        }

        sincronizando = true;
        actualizarIndicador();

        const ok = await fetchEnviosDesdeRed();

        sincronizando = false;
        actualizarIndicador();

        if (!ok && !envios.length) {
            grid.innerHTML = `<div class="col-12 text-center py-5">
                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                <p class="text-muted mb-3">No hay envíos cargados todavía.</p>
                <a href="{{ route('envios.mandar') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i> Crear primer envío</a>
                <button type="button" class="btn btn-outline-secondary ml-2" id="btnReintentarInline">Reintentar</button>
            </div>`;
            document.getElementById('btnReintentarInline')?.addEventListener('click', () => fetchEnvios());
            return;
        }

        renderSummary();
        renderGrid();
    }

    function renderSummary() {
        const counts = { pendientes: 0, asignados: 0, curso: 0, parcial: 0, completados: 0, todos: envios.length };

        envios.forEach(envio => {
            const estado = normalizarEstado(envio.estado);
            if (STATUS_GROUPS.pendientes(estado)) counts.pendientes++;
            if (STATUS_GROUPS.asignados(estado)) counts.asignados++;
            if (STATUS_GROUPS.curso(estado)) counts.curso++;
            if (STATUS_GROUPS.parcial(estado)) counts.parcial++;
            if (STATUS_GROUPS.completados(estado)) counts.completados++;
        });

        document.getElementById('statTodos').textContent = counts.todos;
        document.getElementById('statPendientes').textContent = counts.pendientes;
        document.getElementById('statAsignados').textContent = counts.asignados;
        document.getElementById('statCurso').textContent = counts.curso;
        document.getElementById('statParcial').textContent = counts.parcial;
        document.getElementById('statCompletados').textContent = counts.completados;
    }

    function renderGrid() {
        if (!envios.length) {
            grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="fas fa-inbox mr-2"></i>No hay envíos registrados todavía.</div>';
            return;
        }

        const filtrados = envios
            .filter(envio => STATUS_GROUPS[activeFilter]?.(normalizarEstado(envio.estado)) ?? true)
            .filter(envio => {
                if (!filterEstadoExacto) return true;
                return normalizarEstado(envio.estado) === filterEstadoExacto;
            })
            .filter(envio => {
                if (!filterDestino) return true;
                const dest = (envio.direccion_destino || envio.destino || '').toString().trim().toLowerCase();
                return dest === filterDestino || dest.includes(filterDestino);
            })
            .filter(envio => coincideBusqueda(envio, searchTerm));

        if (contadorSeguimiento) {
            contadorSeguimiento.textContent = filtrados.length === envios.length
                ? `Mostrando ${envios.length} envío(s)`
                : `Mostrando ${filtrados.length} de ${envios.length} envío(s)`;
        }

        if (!filtrados.length) {
            grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="fas fa-search mr-2"></i>No hay resultados con ese filtro o búsqueda.</div>';
            return;
        }

        const fragment = document.createDocumentFragment();

        filtrados.forEach(envio => {
            const col = document.createElement('div');
            col.className = 'col-xl-4 col-lg-6 mb-3';
            col.innerHTML = crearCardHtml(envio);
            col.querySelector('.envio-card')?.addEventListener('click', () => {
                window.location.href = `{{ url('/envios') }}/${envio.id}`;
            });
            fragment.appendChild(col);
        });

        grid.innerHTML = '';
        grid.appendChild(fragment);
    }

    function crearCardHtml(envio) {
        const estado = normalizarEstado(envio.estado);
        const meta = STATUS_META[estado] || { label: envio.estado || 'Sin estado', badge: 'badge-secondary' };
        const fecha = formatearFecha(envio.fecha_creacion);
        const remitente = envio.nombre_remitente || 'Sin remitente';
        const codigo = envio.externo_envio_id || ('#' + envio.id);

        return `
            <div class="card card-outline card-success envio-card h-100 shadow-sm">
                <div class="card-header py-2 bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="mr-2 overflow-hidden">
                            <div class="font-weight-bold text-truncate">${codigo}</div>
                            ${envio.numero_solicitud ? `<small class="text-muted">${envio.numero_solicitud}</small>` : ''}
                        </div>
                        <span class="badge badge-estado ${meta.badge}">${meta.label}</span>
                    </div>
                </div>
                <div class="card-body py-2">
                    <p class="text-muted small mb-2"><i class="far fa-calendar mr-1"></i>${fecha}</p>
                    <div class="envio-route mb-3">
                        <div class="mb-2">
                            <span class="badge badge-light border text-success mr-1"><i class="fas fa-arrow-up"></i></span>
                            <small class="text-muted">Origen</small>
                            <div class="font-weight-bold text-truncate-2lines small pl-1">${envio.direccion_origen || 'Sin origen'}</div>
                        </div>
                        <div>
                            <span class="badge badge-light border text-danger mr-1"><i class="fas fa-arrow-down"></i></span>
                            <small class="text-muted">Destino</small>
                            <div class="font-weight-bold text-truncate-2lines small pl-1">${envio.direccion_destino || 'Sin destino'}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <div class="overflow-hidden mr-2">
                            <small class="text-muted">Remitente</small>
                            <div class="font-weight-bold small text-truncate">${remitente}</div>
                        </div>
                        <span class="btn btn-sm btn-success flex-shrink-0"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
            </div>`;
    }

    function normalizarEstado(estado) {
        return (estado || '').toString().trim().toLowerCase() || 'sin estado';
    }

    function formatearFecha(value) {
        if (!value) return 'Sin fecha';
        try {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleDateString('es-BO', { weekday: 'short', day: 'numeric', month: 'short' });
        } catch {
            return value;
        }
    }

    function coincideBusqueda(envio, termino) {
        if (!termino) return true;
        const texto = [
            `#${envio.id}`,
            envio.numero_solicitud || '',
            envio.direccion_origen || '',
            envio.direccion_destino || '',
            envio.nombre_remitente || '',
            envio.estado || ''
        ].join(' ').toLowerCase();
        return texto.includes(termino);
    }

    document.getElementById('btnReintentar')?.addEventListener('click', () => fetchEnvios());

    setInterval(() => {
        if (!sincronizando) fetchEnvios({ silencioso: true });
    }, 300000);

    window.addEventListener('online', () => fetchEnvios({ silencioso: true }));

    cargarEnviosLocales();

    aplicarFiltrosUrl();

    if (TIENE_DATOS_SERVIDOR) {
        conectado = true;
        actualizarIndicador();
        renderSummary();
        guardarEnCache(envios);
    } else {
        const cacheInicial = obtenerDeCache();
        if (cacheInicial?.length) {
            envios = cacheInicial;
            aplicarFiltrosUrl();
            renderSummary();
            renderGrid();
            fetchEnvios({ silencioso: true });
        } else {
            fetchEnvios();
        }
    }
})();
</script>
@endpush
