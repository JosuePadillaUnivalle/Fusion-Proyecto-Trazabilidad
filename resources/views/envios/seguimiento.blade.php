@extends('layouts.app')

@section('title', 'Seguimiento de Envíos')

@section('page_title', 'Seguimiento de envíos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Seguimiento de envíos</li>
@endsection

@section('content')
    <style>
        .filter-card {
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-card:hover {
            transform: translateY(-2px);
        }

        .filter-card.active {
            border: 2px solid #007bff;
        }

        .envio-card {
            cursor: pointer;
            transition: all 0.3s;
        }

        .envio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
        }

        .envio-route {
            border-left: 3px solid #dee2e6;
            padding-left: 1rem;
        }

        .text-truncate-2lines {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4em;
            min-height: 2.8em;
            max-height: 2.8em;
        }

        /* Indicador de conexión */
        #conexion-status {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .status-online {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-offline {
            background: linear-gradient(135deg, #dc3545, #e74a3b);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .offline-banner {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .envio-local {
            border: 2px dashed #ffc107 !important;
            background: #fffdf5;
        }

        .badge-local {
            background: #ffc107;
            color: #000;
        }
    </style>

    <!-- Indicador de conexión -->
    <div id="conexion-status" class="status-online">
        <i class="fas fa-wifi" id="conexion-icon"></i>
        <span id="conexion-text">Conectado</span>
    </div>

    <!-- Banner offline -->
    <div id="offline-banner" class="offline-banner" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                <strong>Modo Offline</strong>
                <p class="mb-0 small text-muted">Mostrando datos guardados localmente. Algunos envíos pueden no estar
                    actualizados.</p>
            </div>
            <button class="btn btn-warning btn-sm" onclick="verificarConexion()">
                <i class="fas fa-sync-alt"></i> Reintentar
            </button>
        </div>
    </div>

    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box filter-card" data-filter="todos">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-clipboard-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Todos</span>
                    <span class="info-box-number" id="statTodos">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box filter-card" data-filter="pendientes">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes</span>
                    <span class="info-box-number" id="statPendientes">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box filter-card" data-filter="asignados">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-file-signature"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Asignados</span>
                    <span class="info-box-number" id="statAsignados">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box filter-card" data-filter="curso">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">En curso</span>
                    <span class="info-box-number" id="statCurso">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box filter-card" data-filter="parcial">
                <span class="info-box-icon bg-orange elevation-1"><i class="fas fa-shipping-fast"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Parcial</span>
                    <span class="info-box-number" id="statParcial">0</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <div class="info-box filter-card" data-filter="completados">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completados</span>
                    <span class="info-box-number" id="statCompletados">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Envíos locales pendientes -->
    <div id="envios-locales-section" class="card mb-3" style="display: none;">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-cloud-upload-alt mr-2"></i>Envíos Pendientes de Sincronización</h3>
        </div>
        <div class="card-body">
            <div class="row" id="enviosLocalesGrid"></div>
        </div>
    </div>

    <!-- Main content -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de envíos</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" id="inputBuscarEnvio" class="form-control" placeholder="Buscar envíos...">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row p-3" id="envioGrid">
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Cargando envíos...
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            // URLs de las APIs
            const API_URL = '{{ config("external_api.orgtrack_url") }}';
            const LOCAL_API_URL = '/envios/api';
            const CACHE_KEY = 'agronexus_envios_cache';
            const CACHE_EXPIRY = 5 * 60 * 1000; // 5 minutos

            let conectado = true;
            let envios = [];
            let enviosLocales = [];
            let activeFilter = 'todos';
            let searchTerm = '';

            const grid = document.getElementById('envioGrid');
            const searchInput = document.getElementById('inputBuscarEnvio');
            const filterCards = document.querySelectorAll('.filter-card');

            const STATUS_GROUPS = {
                pendientes: (estado) => ['pendiente', 'sin estado', 'sin asignar'].includes(estado),
                asignados: (estado) => ['asignado'].includes(estado),
                curso: (estado) => ['en curso'].includes(estado),
                parcial: (estado) => ['parcialmente entregado'].includes(estado),
                completados: (estado) => ['entregado', 'finalizado', 'completado'].includes(estado),
                todos: () => true
            };

            const STATUS_META = {
                'pendiente': { label: 'Pendiente', badge: 'badge-warning' },
                'sin estado': { label: 'Pendiente', badge: 'badge-warning' },
                'sin asignar': { label: 'Pendiente', badge: 'badge-warning' },
                'asignado': { label: 'Asignado', badge: 'badge-info' },
                'en curso': { label: 'En curso', badge: 'badge-primary' },
                'parcialmente entregado': { label: 'Parcial', badge: 'badge-orange' },
                'entregado': { label: 'Completado', badge: 'badge-success' },
                'finalizado': { label: 'Completado', badge: 'badge-success' },
                'completado': { label: 'Completado', badge: 'badge-success' },
            };

            // Event listeners
            filterCards.forEach(card => {
                card.addEventListener('click', () => {
                    const filter = card.getAttribute('data-filter');
                    if (!filter || filter === activeFilter) return;
                    activeFilter = filter;
                    filterCards.forEach(c => c.classList.toggle('active', c.getAttribute('data-filter') === filter));
                    renderGrid();
                });
            });

            searchInput.addEventListener('input', (event) => {
                searchTerm = event.target.value.trim().toLowerCase();
                renderGrid();
            });

            // ========================================
            // TOLERANCIA A FALLOS
            // ========================================

            async function verificarConexion() {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 5000);

                    const res = await fetch(`${LOCAL_API_URL}/tipo-transporte`, {
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);
                    conectado = res.ok;
                } catch (e) {
                    conectado = false;
                }

                actualizarIndicador();
                return conectado;
            }

            function actualizarIndicador() {
                const status = document.getElementById('conexion-status');
                const icon = document.getElementById('conexion-icon');
                const text = document.getElementById('conexion-text');
                const banner = document.getElementById('offline-banner');

                if (conectado) {
                    status.className = 'status-online';
                    icon.className = 'fas fa-wifi';
                    text.textContent = 'Conectado';
                    banner.style.display = 'none';
                } else {
                    status.className = 'status-offline';
                    icon.className = 'fas fa-wifi-slash';
                    text.textContent = 'Sin Conexión';
                    banner.style.display = 'block';
                }
            }

            function guardarEnCache(data) {
                const cacheData = {
                    envios: data,
                    timestamp: Date.now()
                };
                localStorage.setItem(CACHE_KEY, JSON.stringify(cacheData));
            }

            function obtenerDeCache() {
                try {
                    const cached = localStorage.getItem(CACHE_KEY);
                    if (!cached) return null;

                    const data = JSON.parse(cached);
                    // Verificar si el cache no ha expirado
                    if (Date.now() - data.timestamp < CACHE_EXPIRY) {
                        return data.envios;
                    }
                    return data.envios; // Retornar aunque esté expirado si no hay conexión
                } catch (e) {
                    return null;
                }
            }

            function cargarEnviosLocales() {
                try {
                    const stored = localStorage.getItem('agronexus_envios_pendientes');
                    enviosLocales = stored ? JSON.parse(stored) : [];
                } catch (e) {
                    enviosLocales = [];
                }
                renderEnviosLocales();
            }

            function renderEnviosLocales() {
                const section = document.getElementById('envios-locales-section');
                const grid = document.getElementById('enviosLocalesGrid');

                const pendientes = enviosLocales.filter(e => e.estado === 'pendiente');

                if (pendientes.length === 0) {
                    section.style.display = 'none';
                    return;
                }

                section.style.display = 'block';
                grid.innerHTML = pendientes.map(envio => `
                    <div class="col-md-4 mb-3">
                        <div class="card card-outline card-warning envio-local">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cloud-upload-alt mr-1"></i>
                                    Local #${envio.id}
                                    <span class="badge badge-local float-right">Pendiente</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-2">
                                    <i class="far fa-calendar mr-1"></i>
                                    ${new Date(envio.fecha).toLocaleDateString('es-BO')}
                                </p>
                                <p class="mb-1"><strong>Remitente:</strong> ${envio.datos?.envio?.nombre_remitente || 'N/A'}</p>
                                <p class="mb-0 small text-muted">
                                    <i class="fas fa-info-circle"></i> Se sincronizará automáticamente
                                </p>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // ========================================
            // CARGA DE DATOS
            // ========================================

            async function fetchEnvios() {
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando envíos...</div>';

                // Verificar conexión primero
                await verificarConexion();

                if (conectado) {
                    try {
                        const res = await fetch(`${LOCAL_API_URL}/envios`);
                        if (!res.ok) throw new Error('No se pudieron cargar los envíos');

                        const data = await res.json();
                        envios = Array.isArray(data) ? data : [];

                        // Guardar en cache
                        guardarEnCache(envios);

                    } catch (error) {
                        console.error('Error cargando envíos:', error);
                        conectado = false;
                        actualizarIndicador();

                        // Intentar cargar desde cache
                        const cached = obtenerDeCache();
                        if (cached) {
                            envios = cached;
                        } else {
                    grid.innerHTML = `<div class="col-12 text-center text-danger py-5"><i class="fas fa-exclamation-triangle mr-2"></i>Sin conexión y sin datos en caché local.</div>`;
                            return;
                        }
                    }
                } else {
                    // Modo offline - cargar desde cache
                    const cached = obtenerDeCache();
                    if (cached) {
                        envios = cached;
                    } else {
                        grid.innerHTML = `<div class="col-12 text-center text-warning py-5"><i class="fas fa-wifi-slash mr-2"></i>Sin conexión. No hay envíos guardados en caché local.</div>`;
                        return;
                    }
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
                    .filter(envio => coincideBusqueda(envio, searchTerm));

                if (!filtrados.length) {
                    grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="fas fa-search mr-2"></i>No hay resultados con ese filtro o búsqueda.</div>';
                    return;
                }

                grid.innerHTML = filtrados.map(envio => crearCard(envio)).join('');
            }

            function crearCard(envio) {
                const estado = normalizarEstado(envio.estado);
                const meta = STATUS_META[estado] || { label: envio.estado || 'Sin estado', badge: 'badge-secondary' };
                const fecha = formatearFecha(envio.fecha_creacion);
                const remitente = envio.nombre_remitente || 'Sin remitente';
                const urlDetalle = `{{ url('/envios') }}/${envio.id}`;

                return `
                    <div class="col-xl-4 col-lg-6 mb-3">
                        <div class="card card-outline card-primary envio-card" onclick="window.location.href='${urlDetalle}'">
                            <div class="card-header">
                                <h5 class="card-title mb-0" style="width: 100%;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>#${envio.id}</strong>
                                        ${envio.numero_solicitud ? `<span class="badge badge-light border ml-2">${envio.numero_solicitud}</span>` : ''}
                                        <span class="badge ${meta.badge} ml-auto">${meta.label}</span>
                                    </div>
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3"><i class="far fa-calendar mr-1"></i>${fecha}</p>

                                <div class="mb-3 pb-3 envio-route">
                                    <div class="mb-2">
                                        <small class="text-muted text-uppercase">Recogida</small>
                                        <div class="font-weight-bold text-truncate-2lines">${envio.direccion_origen || 'Sin origen'}</div>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase">Entrega</small>
                                        <div class="font-weight-bold text-truncate-2lines">${envio.direccion_destino || 'Sin destino'}</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted text-uppercase">Remitente</small>
                                        <div class="font-weight-bold">${remitente}</div>
                                    </div>
                                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </button>
                                </div>
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

            // Exponer función para reintentar conexión
            window.verificarConexion = async function () {
                await verificarConexion();
                if (conectado) {
                    fetchEnvios();
                }
            };

            // Verificar conexión cada 30 segundos
            setInterval(async () => {
                const wasOffline = !conectado;
                await verificarConexion();
                if (wasOffline && conectado) {
                    // Si recuperamos conexión, recargar datos
                    fetchEnvios();
                }
            }, 30000);

            // Inicializar
            cargarEnviosLocales();
            fetchEnvios();
        })();
    </script>
@endpush