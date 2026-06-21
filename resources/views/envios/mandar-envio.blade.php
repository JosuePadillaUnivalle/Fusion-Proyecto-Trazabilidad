@extends('layouts.app')

@section('title', 'Crear envío | AgroFusion')

@section('page_title', 'Crear envío')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Envíos</a></li>
    <li class="breadcrumb-item active">Crear envío</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env page-mandar-envio">

    <div id="cola-alert" class="cola-pendientes-card" style="display: none;">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-md-0">
                <i class="fas fa-clock text-warning mr-2"></i>
                <strong>Envíos en cola local</strong>
                <span class="small text-muted d-block d-md-inline ml-md-2">Se sincronizarán al recuperar conexión.</span>
            </div>
            <button type="button" class="btn btn-warning btn-sm" onclick="ToleranciaFallos.sincronizarPendientes()">
                <i class="fas fa-sync-alt mr-1"></i> Sincronizar ahora
            </button>
        </div>
    </div>

    <div class="card card-modulo-main mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1"><i class="fas fa-truck-loading text-success mr-2"></i>Nueva solicitud de envío</h5>
                    <p class="text-muted mb-0 small">Completa los 3 pasos: ubicación, detalles y confirmación. Si no hay red, el borrador se guarda en este equipo.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0">
                    <span id="conexion-indicator" class="env-conexion-aviso is-offline d-none mr-2" role="status">
                        <i class="fas fa-wifi-slash"></i>
                        <span id="conexion-texto">Sin conexión</span>
                    </span>
                    <span class="badge badge-warning d-none mr-2" id="pendientes-badge">0 pendientes</span>
                    <a href="{{ route('envios.seguimiento') }}" class="btn btn-outline-secondary btn-sm" rel="prefetch">
                        <i class="fas fa-list mr-1"></i> Ver seguimiento
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-modulo-main mb-3 wizard-progress">
        <div class="card-body py-3">
            <div class="row">
                <div class="col-md-4 step-item step-indicator active" data-step="1">
                    <span class="step-badge badge mb-2">1</span>
                    <h6 class="font-weight-bold mb-0">Ubicación</h6>
                    <small class="text-muted">Origen y destino en el mapa</small>
                </div>
                <div class="col-md-4 step-item step-indicator pending" data-step="2">
                    <span class="step-badge badge mb-2">2</span>
                    <h6 class="mb-0">Detalles</h6>
                    <small class="text-muted">Cargas y transporte</small>
                </div>
                <div class="col-md-4 step-item step-indicator pending" data-step="3">
                    <span class="step-badge badge mb-2">3</span>
                    <h6 class="mb-0">Confirmación</h6>
                    <small class="text-muted">Resumen y envío</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Content -->
    <div class="wizard-content">

        <!-- STEP 1: UBICACIÓN -->
        <div class="wizard-step active" data-step="1">
            <div class="row equal-height-row">
                <!-- Formulario -->
                <div class="col-md-4">
                    <div class="card card-outline card-success h-100">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-user mr-2"></i> Datos del remitente</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nº de Solicitud</label>
                                <input type="text" class="form-control readonly-input" id="numero_solicitud"
                                    value="{{ $numeroSolicitud ?? '' }}" readonly maxlength="50">
                                <small class="form-text text-muted">
                                    <i class="fas fa-magic mr-1"></i> Se genera automáticamente al abrir el formulario
                                </small>
                            </div>
                            <div class="form-group">
                                <label>Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_remitente" placeholder="Ej: Juan Pérez"
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Teléfono <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefono_remitente" placeholder="Ej: 77123456"
                                    required>
                            </div>
                            <div class="form-group">
                                <label>Email <small class="text-muted">(opcional)</small></label>
                                <input type="email" class="form-control" id="email_remitente"
                                    placeholder="correo@example.com">
                            </div>
                            <hr>
                            <div class="form-group">
                                <label class="text-success"><i class="fas fa-map-marker-alt"></i> Origen</label>
                                <input type="text" class="form-control readonly-input" id="txtNombreOrigen" readonly
                                    placeholder="Marca el origen en el mapa...">
                                <small id="txtOrigen" class="form-text text-muted"></small>
                            </div>
                            <div class="form-group">
                                <label class="text-danger"><i class="fas fa-map-marker-alt"></i> Destino</label>
                                <input type="text" class="form-control readonly-input" id="txtNombreDestino" readonly
                                    placeholder="Marca el destino en el mapa...">
                                <small id="txtDestino" class="form-text text-muted"></small>
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-muted small mb-1">Instrucciones de recogida <span class="text-muted">(opcional)</span></label>
                                <textarea class="form-control form-control-sm" id="instrucciones_recogida" rows="2"
                                    placeholder="Ej: Llamar 10 min antes de llegar…"></textarea>
                            </div>
                            <div class="form-group mb-2">
                                <label class="text-muted small mb-1">Instrucciones de entrega <span class="text-muted">(opcional)</span></label>
                                <textarea class="form-control form-control-sm" id="instrucciones_entrega" rows="2"
                                    placeholder="Ej: Entregar en muelle 2…"></textarea>
                            </div>
                            <div class="callout callout-info">
                                <p class="mb-0"><i class="fas fa-info-circle"></i> Haz clic en el mapa para marcar primero
                                    el <strong>Origen</strong> y luego el <strong>Destino</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa -->
                <div class="col-md-8">
                    <div class="card card-modulo-main h-100">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-map text-success mr-2"></i> Mapa interactivo</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="btnResetMap">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0" style="flex: 1; display: flex;">
                            <div id="map" style="width: 100%;"></div>
                        </div>
                        <div class="card-footer py-2 small text-muted" id="rutaResumenEnvio" style="display: none;">
                            <i class="fas fa-route text-primary mr-1"></i>
                            <span id="rutaDistancia">--</span> · <span id="rutaDuracion">--</span>
                            <span class="text-muted"> (OSRM)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: PARTICIONES -->
        <div class="wizard-step" data-step="2">
            <div id="particionesContainer"></div>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-outline-primary btn-lg" id="btnAgregarParticion">
                    <i class="fas fa-plus-circle"></i> Agregar otro camión / partición
                </button>
            </div>
        </div>

        <!-- STEP 3: CONFIRMACIÓN -->
        <div class="wizard-step" data-step="3">
            <div class="card card-modulo-main">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-check-circle text-success mr-2"></i> Resumen de la solicitud</h3>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <!-- Datos Remitente -->
                    <h5 class="text-primary border-bottom pb-2 mb-3">Datos del Remitente</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nombre</span>
                                    <span class="info-box-number" id="resNombre">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-phone"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Teléfono</span>
                                    <span class="info-box-number" id="resTelefono">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-envelope"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Email</span>
                                    <span class="info-box-number" style="font-size: 0.9rem;" id="resEmail">--</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ruta -->
                    <h5 class="text-primary border-bottom pb-2 mb-3">Ruta del Envío</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="callout callout-success">
                                <h6><i class="fas fa-map-marker-alt"></i> Origen</h6>
                                <p id="resOrigen" class="mb-0">--</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="callout callout-danger">
                                <h6><i class="fas fa-map-marker-alt"></i> Destino</h6>
                                <p id="resDestino" class="mb-0">--</p>
                            </div>
                        </div>
                    </div>

                    <!-- Particiones -->
                    <h5 class="text-primary border-bottom pb-2 mb-3">Detalle de Envíos / Particiones</h5>
                    <div id="resumenParticiones"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Botones de navegación -->
    <div class="row mt-4">
        <div class="col-6">
            <button type="button" class="btn btn-default" id="btnPrev" style="display: none;">
                <i class="fas fa-arrow-left"></i> Anterior
            </button>
        </div>
        <div class="col-6 text-right">
            <button type="button" class="btn btn-success" id="btnNext">
                Siguiente <i class="fas fa-arrow-right"></i>
            </button>
            <button type="button" class="btn btn-success" id="btnFinish" style="display: none;">
                <i class="fas fa-check mr-1"></i> Confirmar y crear envío
            </button>
        </div>
    </div>

    <!-- Template Partición -->
    <template id="tplParticion">
        <div class="card card-outline card-primary mb-3" data-index="{index}">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck"></i> Envío / Camión #<span class="num">1</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-danger" onclick="removeParticion(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Transporte Fields (Igual que antes) -->
                <div class="form-group">
                    <label>Tipo de Transporte <span class="text-danger">*</span></label>
                    <select class="form-control js-tipo-transporte" required>
                        <option value="">Seleccione...</option>
                    </select>
                    <small class="text-muted js-transporte-offline" style="display: none;">
                        <i class="fas fa-info-circle"></i> Tipos de transporte cargados desde cache local
                    </small>
                </div>
                <div class="form-group">
                    <label>Vehículo <span class="text-danger">*</span></label>
                    <select class="form-control js-vehiculo" required>
                        <option value="">Seleccione...</option>
                    </select>
                    <small class="text-muted js-capacidad-resumen d-block mt-1"></small>
                    <div class="alert alert-warning py-1 px-2 small mt-1 mb-0 js-capacidad-alerta" style="display: none;"></div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha Recogida <span class="text-danger">*</span></label>
                            <input type="date" class="form-control js-fecha-recogida" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hora Recogida <span class="text-danger">*</span></label>
                            <input type="time" class="form-control js-hora-recogida" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hora Entrega estimada <span class="text-danger">*</span></label>
                            <input type="time" class="form-control js-hora-entrega readonly-input" readonly required>
                            <small class="form-text text-muted">Auto: ruta OSRM + 15 min buffer</small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-primary mb-0"><i class="fas fa-boxes"></i> Cargas / Productos</h5>
                    <button type="button" class="btn btn-outline-success btn-sm btn-add-carga" onclick="addCarga(this)">
                        <i class="fas fa-plus"></i> Agregar Otro Producto
                    </button>
                </div>

                <div class="cargas-container">
                    <!-- Cargas se insertan aqui -->
                </div>

            </div>
        </div>
    </template>

    <!-- Template Carga (NUEVO DISEÑO) -->
    <template id="tplCarga">
        <div class="card card-outline card-secondary mb-3 carga-item">
            <div class="card-header py-1">
                <h3 class="card-title text-sm"><i class="fas fa-box"></i> Producto</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-danger" onclick="removeCarga(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">

                <h6 class="text-info border-bottom pb-1 mb-3"><i class="fas fa-seedling"></i> Verdura / cultivo <span
                        class="text-danger">*</span></h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small">Producto</label>
                            <select class="form-control form-control-sm js-producto" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small">Tipo de Empaque</label>
                            <select class="form-control form-control-sm js-tipo-empaque" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <h6 class="text-info border-bottom pb-1 mb-3 mt-2"><i class="fas fa-ruler-combined"></i> Tamaño / Conteo
                    <span class="text-danger">*</span>
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Conteo por Empaque (calibre) *</label>
                            <select class="form-control form-control-sm js-calibre" required>
                                <option value="">Seleccione...</option>
                            </select>
                            <small class="form-text text-muted" style="font-size: 0.75rem;">De etiqueta caja</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Peso Promedio Unidad (kg) *</label>
                            <input type="number" step="0.001" class="form-control form-control-sm js-peso-unidad" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Capacidad por Empaque</label>
                            <input type="number" class="form-control form-control-sm js-capacidad-empaque" readonly>
                            <small class="form-text text-muted" style="font-size: 0.75rem;">Auto-completado</small>
                        </div>
                    </div>
                </div>

                <h6 class="text-info border-bottom pb-1 mb-3 mt-2"><i class="fas fa-arrows-alt"></i> Medidas y Peso del
                    Empaque</h6>
                <div class="row bg-light py-2 rounded">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small">Largo (cm)</label>
                            <input type="text" class="form-control form-control-sm js-largo" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small">Ancho (cm)</label>
                            <input type="text" class="form-control form-control-sm js-ancho" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small">Alto (cm)</label>
                            <input type="text" class="form-control form-control-sm js-alto" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="small">Peso Neto (kg)</label>
                            <input type="text" class="form-control form-control-sm js-peso-neto" readonly>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small">Tara / Envase (kg)</label>
                                    <input type="text" class="form-control form-control-sm js-tara" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small">Peso Bruto (kg)</label>
                                    <input type="text" class="form-control form-control-sm js-peso-bruto" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="text-info border-bottom pb-1 mb-3 mt-3"><i class="fas fa-calculator"></i> Forma de Pedir y
                    Cantidad <span class="text-danger">*</span></h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">¿Cómo quiere pedir? *</label>
                            <select class="form-control form-control-sm js-forma-pedido" required>
                                <option value="unidades">Por Unidades</option>
                                <option value="empaques">Por Empaques</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Cantidad de Pedido *</label>
                            <input type="number" class="form-control form-control-sm js-cantidad-pedido" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Empaques Calculados</label>
                            <input type="text" class="form-control form-control-sm js-empaques-calculados" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small">Unidades por Pallet</label>
                            <input type="text" class="form-control form-control-sm js-unidades-pallet" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="small">Nº de Pallets</label>
                            <input type="text" class="form-control form-control-sm js-num-pallets" readonly>
                            <small class="form-text text-muted" style="font-size: 0.75rem;">Auto-calculado</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </template>

</div>{{-- .modulo-env --}}
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/ruta-por-calles.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // URLs de las APIs
        // API_URL se usa para verificar disponibilidad del servicio de envíos
        const API_URL = '{{ config("external_api.orgtrack_url") }}';
        // LOCAL_API_URL se usa para todas las llamadas (proxy local para evitar CORS)
        const LOCAL_API_URL = '/envios/api';
        const CATALOGO_SELECTOR_URL = '/catalogo-selector';
        const ETA_BUFFER_MIN = 15;
        const ORS_KEY = '5b3ce3597851110001cf6248dbff311ed4d34185911c2eb9e6c50080';



        // ========================================
        // SISTEMA DE TOLERANCIA A FALLOS
        // ========================================
        const ToleranciaFallos = {
            conectado: true,
            ultimaVerificacion: null,
            colaLocal: [],

            init: function () {
                this.cargarColaLocal();
                this.conectado = true;
                this.actualizarIndicador();
                this.verificarConexion();
                setInterval(() => this.verificarConexion(), 60000);
            },

            verificarConexion: async function () {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 2000);

                    const response = await fetch(`${LOCAL_API_URL}/ping`, {
                        method: 'GET',
                        signal: controller.signal,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    clearTimeout(timeoutId);
                    if (!response.ok) {
                        this.conectado = navigator.onLine !== false;
                    } else {
                        const body = await response.json().catch(() => ({}));
                        this.conectado = body?.ok === true || response.ok;
                    }

                    if (this.conectado && this.colaLocal.length > 0) {
                        this.sincronizarPendientes();
                    }
                } catch (error) {
                    console.warn('API no disponible:', error.message);
                    this.conectado = navigator.onLine !== false;
                }

                this.actualizarIndicador();
                return this.conectado;
            },

            actualizarIndicador: function () {
                const indicator = document.getElementById('conexion-indicator');
                const texto = document.getElementById('conexion-texto');
                const badge = document.getElementById('pendientes-badge');
                const colaAlert = document.getElementById('cola-alert');

                if (this.conectado) {
                    indicator.classList.add('d-none');
                    indicator.classList.remove('is-offline');
                } else {
                    indicator.classList.remove('d-none');
                    indicator.classList.add('is-offline');
                    indicator.querySelector('i').className = 'fas fa-wifi-slash';
                    texto.textContent = 'Sin conexión — se guardará en este equipo';
                }

                if (this.colaLocal.length > 0) {
                    badge.classList.remove('d-none');
                    badge.textContent = `${this.colaLocal.length} pendiente(s)`;
                    colaAlert.style.display = 'block';
                } else {
                    badge.classList.add('d-none');
                    colaAlert.style.display = 'none';
                }
            },

            guardarEnCola: function (datos) {
                const envio = {
                    id: Date.now(),
                    datos: datos,
                    fecha: new Date().toISOString(),
                    intentos: 0,
                    estado: 'pendiente'
                };

                this.colaLocal.push(envio);
                localStorage.setItem('AgroFusion_envios_pendientes', JSON.stringify(this.colaLocal));
                this.actualizarIndicador();

                return envio;
            },

            cargarColaLocal: function () {
                try {
                    const stored = localStorage.getItem('AgroFusion_envios_pendientes');
                    this.colaLocal = stored ? JSON.parse(stored) : [];
                } catch (e) {
                    this.colaLocal = [];
                }
            },

            sincronizarPendientes: async function () {
                if (!this.conectado || this.colaLocal.length === 0) return;

                Swal.fire({
                    title: 'Sincronizando...',
                    text: `Procesando ${this.colaLocal.length} envío(s) pendiente(s)`,
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                let sincronizados = 0;
                const pendientes = [...this.colaLocal];

                for (const envio of pendientes) {
                    if (envio.estado === 'enviado') continue;

                    try {
                        const resDireccion = await fetch(`${API_URL}/api/public/direccion`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(envio.datos.direccion)
                        });

                        if (!resDireccion.ok) continue;

                        const { id_direccion } = await resDireccion.json();
                        envio.datos.envio.id_direccion = id_direccion;

                        const resEnvio = await fetch(`${API_URL}/api/public/envios`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(envio.datos.envio)
                        });

                        if (resEnvio.ok) {
                            envio.estado = 'enviado';
                            sincronizados++;
                        }
                    } catch (error) {
                        console.error('Error sincronizando:', error);
                    }
                }

                this.colaLocal = this.colaLocal.filter(e => e.estado !== 'enviado');
                localStorage.setItem('AgroFusion_envios_pendientes', JSON.stringify(this.colaLocal));
                this.actualizarIndicador();

                Swal.fire({
                    icon: sincronizados > 0 ? 'success' : 'warning',
                    title: sincronizados > 0 ? '¡Sincronización exitosa!' : 'Sin cambios',
                    text: `${sincronizados} envío(s) sincronizado(s)`,
                    timer: 3000
                });
            }
        };

        // ========================================
        // ESTADO Y VARIABLES GLOBALES
        // ========================================
        const state = {
            currentStep: 1,
            map: null,
            markers: { origin: null, destination: null },
            routeLayer: null,
            originCoords: null,
            destinationCoords: null,
            geoJSON: null,
            routeDistanceM: null,
            routeDurationS: null,
            tiposTransporte: [],
            vehiculos: [],

            // Cache Catalogos
            catalogos: {
                productos: [],
                tiposEmpaque: [],
                tamanoConteo: []
            }
        };

        let partitionCounter = 0;

        // ========================================
        // INICIALIZACIÓN
        // ========================================
        document.addEventListener('DOMContentLoaded', async () => {
            ToleranciaFallos.init();
            initMap();
            await loadTiposTransporte();
            await loadCatalogos();
            await loadVehiculos();
            addPartition();
            setupEventListeners();
            setMinDate();
        });

        async function loadCatalogos() {
            try {
                const [productos, tiposEmpaque, tamanoConteo] = await Promise.all([
                    fetch(`${LOCAL_API_URL}/catalogo-productos`).then(r => r.json()),
                    fetch(`${LOCAL_API_URL}/catalogo-tipos-empaque`).then(r => r.json()),
                    fetch(`${LOCAL_API_URL}/catalogo-tamano-conteo`).then(r => r.json()),
                ]);
                state.catalogos.productos = Array.isArray(productos) ? productos : [];
                state.catalogos.tiposEmpaque = Array.isArray(tiposEmpaque) ? tiposEmpaque : [];
                state.catalogos.tamanoConteo = Array.isArray(tamanoConteo) ? tamanoConteo : [];
            } catch (e) {
                console.error('Error cargando catálogos AgroFusion', e);
            }
        }

        async function loadVehiculos() {
            try {
                const res = await fetch(`${CATALOGO_SELECTOR_URL}/vehiculos?per_page=200`);
                const data = await res.json();
                state.vehiculos = (data.data || data.items || data || []).map(v => ({
                    id: v.id,
                    label: v.label || v.placa,
                    meta: v.meta || '',
                    capacidad_kg: parseFloat(v.extra?.kg || 0) || 0,
                }));
            } catch (e) {
                console.warn('No se pudieron cargar vehículos', e);
                state.vehiculos = [];
            }
        }

        function setMinDate() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('.js-fecha-recogida').forEach(input => {
                input.min = today;
            });
        }

        function setupEventListeners() {
            document.getElementById('btnNext').addEventListener('click', nextStep);
            document.getElementById('btnPrev').addEventListener('click', prevStep);
            document.getElementById('btnFinish').addEventListener('click', submitForm);
            document.getElementById('btnResetMap').addEventListener('click', resetMap);
            document.getElementById('btnAgregarParticion').addEventListener('click', addPartition);
        }

        // ========================================
        // MAPA
        // ========================================
        function initMap() {
            state.map = L.map('map').setView([-17.3935, -66.1570], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(state.map);
            state.map.on('click', onMapClick);
        }

        async function onMapClick(e) {
            const { lat, lng } = e.latlng;

            if (!state.markers.origin) {
                state.markers.origin = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: '<i class="fas fa-map-marker-alt" style="color: #28a745; font-size: 32px;"></i>',
                        className: 'custom-marker',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    })
                }).addTo(state.map);

                state.originCoords = { lat, lng };
                document.getElementById('txtOrigen').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                const address = await reverseGeocode(lat, lng);
                document.getElementById('txtNombreOrigen').value = address;

            } else if (!state.markers.destination) {
                state.markers.destination = L.marker([lat, lng], {
                    icon: L.divIcon({
                        html: '<i class="fas fa-map-marker-alt" style="color: #dc3545; font-size: 32px;"></i>',
                        className: 'custom-marker',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    })
                }).addTo(state.map);

                state.destinationCoords = { lat, lng };
                document.getElementById('txtDestino').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                const address = await reverseGeocode(lat, lng);
                document.getElementById('txtNombreDestino').value = address;
                await drawRoute();
            }
        }

        async function reverseGeocode(lat, lng) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await res.json();
                return data.display_name || `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            } catch (e) {
                return `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            }
        }

        async function drawRoute() {
            const { origin, destination } = state.markers;
            if (!origin || !destination) return;

            const start = origin.getLatLng();
            const end = destination.getLatLng();
            const waypoints = [{ lat: start.lat, lng: start.lng }, { lat: end.lat, lng: end.lng }];

            if (state.routeLayer) state.map.removeLayer(state.routeLayer);

            const routeResult = await RutaPorCalles.fetchRoute(waypoints);
            if (routeResult?.geojson) {
                state.routeLayer = L.geoJSON(routeResult.geojson, {
                    style: {
                        color: routeResult.straight ? '#e67e22' : '#2563eb',
                        weight: 5,
                        opacity: 0.85,
                        dashArray: routeResult.straight ? '8,8' : null,
                    },
                }).addTo(state.map);
                state.map.fitBounds(state.routeLayer.getBounds(), { padding: [50, 50] });
                state.geoJSON = JSON.stringify(routeResult.geojson);
                state.routeDistanceM = routeResult.distance_m || routeResult.geojson?.features?.[0]?.properties?.distance_m || null;
                state.routeDurationS = routeResult.duration_s || routeResult.geojson?.features?.[0]?.properties?.duration_s || null;
                actualizarResumenRuta();
                actualizarHorasEntregaTodas();
            }
        }

        function actualizarResumenRuta() {
            const box = document.getElementById('rutaResumenEnvio');
            if (!state.routeDistanceM && !state.routeDurationS) {
                box.style.display = 'none';
                return;
            }
            const km = state.routeDistanceM ? (state.routeDistanceM / 1000).toFixed(1) : '--';
            const min = state.routeDurationS ? Math.ceil(state.routeDurationS / 60) : '--';
            document.getElementById('rutaDistancia').textContent = `${km} km`;
            document.getElementById('rutaDuracion').textContent = `${min} min viaje`;
            box.style.display = 'block';
        }

        function calcularHoraEntrega(horaRecogida, durationS) {
            if (!horaRecogida || !durationS) return '';
            const [h, m] = horaRecogida.split(':').map(Number);
            if (Number.isNaN(h) || Number.isNaN(m)) return '';
            const totalMin = h * 60 + m + Math.ceil(durationS / 60) + ETA_BUFFER_MIN;
            const nh = Math.floor(totalMin / 60) % 24;
            const nm = totalMin % 60;
            return `${String(nh).padStart(2, '0')}:${String(nm).padStart(2, '0')}`;
        }

        function actualizarHorasEntregaTodas() {
            document.querySelectorAll('#particionesContainer > .card').forEach(card => {
                const horaRecogida = card.querySelector('.js-hora-recogida')?.value;
                const horaEntrega = card.querySelector('.js-hora-entrega');
                if (horaEntrega && horaRecogida && state.routeDurationS) {
                    horaEntrega.value = calcularHoraEntrega(horaRecogida, state.routeDurationS);
                }
            });
        }

        function resetMap() {
            if (state.markers.origin) state.map.removeLayer(state.markers.origin);
            if (state.markers.destination) state.map.removeLayer(state.markers.destination);
            if (state.routeLayer) state.map.removeLayer(state.routeLayer);
            state.markers = { origin: null, destination: null };
            state.routeLayer = null;
            state.originCoords = null;
            state.destinationCoords = null;
            state.geoJSON = null;
            state.routeDistanceM = null;
            state.routeDurationS = null;
            document.getElementById('rutaResumenEnvio').style.display = 'none';
            document.getElementById('txtOrigen').textContent = '';
            document.getElementById('txtDestino').textContent = '';
            document.getElementById('txtNombreOrigen').value = '';
            document.getElementById('txtNombreDestino').value = '';
        }

        // ========================================
        // TIPOS DE TRANSPORTE (con cache local)
        // ========================================
        async function loadTiposTransporte() {
            // Intentar cargar desde cache primero
            const cached = localStorage.getItem('tipos_transporte_cache');
            if (cached) {
                state.tiposTransporte = JSON.parse(cached);
            }

            try {
                const res = await fetch(`${LOCAL_API_URL}/tipo-transporte`);
                if (res.ok) {
                    state.tiposTransporte = await res.json();
                    // Guardar en cache
                    localStorage.setItem('tipos_transporte_cache', JSON.stringify(state.tiposTransporte));
                }
            } catch (e) {
                console.warn('Error cargando tipos de transporte, usando cache:', e);
            }

            // Si no hay datos, mostrar mensaje
            if (state.tiposTransporte.length === 0) {
                console.warn('No hay tipos de transporte disponibles');
            }
        }

        // ========================================
        // LOGICA DE CARGA Y CALCULOS
        // ========================================

        function initCargaEvents(cargaElement) {
            const selects = {
                producto: cargaElement.querySelector('.js-producto'),
                empaque: cargaElement.querySelector('.js-tipo-empaque'),
                calibre: cargaElement.querySelector('.js-calibre'),
                formaPedido: cargaElement.querySelector('.js-forma-pedido')
            };

            const inputs = {
                cantidadPedido: cargaElement.querySelector('.js-cantidad-pedido')
            };

            state.catalogos.productos.forEach(p => {
                const opt = new Option(p.nombre, p.id);
                opt.dataset.pesoPromedio = p.peso_promedio || '';
                selects.producto.appendChild(opt);
            });

            selects.producto.addEventListener('change', () => {
                selects.calibre.innerHTML = '<option value="">Seleccione...</option>';
                const prodId = parseInt(selects.producto.value, 10);
                if (!prodId) return;

                const calibres = state.catalogos.tamanoConteo.filter(tc => parseInt(tc.id_producto, 10) === prodId);
                calibres.forEach(c => {
                    const opt = new Option(c.nombre, c.id);
                    opt.dataset.conteo = c.conteo_por_empaque;
                    opt.dataset.peso = c.peso_promedio_unidad || c.peso_promedio_kg;
                    opt.dataset.tipoEmpaque = c.id_tipo_empaque || '';
                    selects.calibre.appendChild(opt);
                });

                const selectedProd = selects.producto.options[selects.producto.selectedIndex];
                if (selectedProd) {
                    cargaElement.querySelector('.js-peso-unidad').value = selectedProd.dataset.pesoPromedio || '';
                }
            });

            state.catalogos.tiposEmpaque.forEach(e => {
                const opt = new Option(e.nombre, e.id);
                opt.dataset.largo = e.largo;
                opt.dataset.ancho = e.ancho;
                opt.dataset.alto = e.alto;
                opt.dataset.tara = e.tara;
                opt.dataset.capacidad = e.capacidad;
                opt.dataset.unidadesPallet = e.unidades_por_pallet;
                selects.empaque.appendChild(opt);
            });

            selects.empaque.addEventListener('change', () => {
                const opt = selects.empaque.options[selects.empaque.selectedIndex];
                if (opt?.value) {
                    cargaElement.querySelector('.js-largo').value = opt.dataset.largo || '';
                    cargaElement.querySelector('.js-ancho').value = opt.dataset.ancho || '';
                    cargaElement.querySelector('.js-alto').value = opt.dataset.alto || '';
                    cargaElement.querySelector('.js-tara').value = opt.dataset.tara || '';
                    cargaElement.querySelector('.js-unidades-pallet').value = opt.dataset.unidadesPallet || '';
                }
                calcularTotales(cargaElement);
            });

            selects.calibre.addEventListener('change', () => {
                const opt = selects.calibre.options[selects.calibre.selectedIndex];
                if (opt?.value) {
                    cargaElement.querySelector('.js-peso-unidad').value = opt.dataset.peso || '';
                    cargaElement.querySelector('.js-capacidad-empaque').value = opt.dataset.conteo || '';
                    if (opt.dataset.tipoEmpaque && selects.empaque) {
                        selects.empaque.value = opt.dataset.tipoEmpaque;
                        selects.empaque.dispatchEvent(new Event('change'));
                    }
                }
                calcularTotales(cargaElement);
            });

            selects.formaPedido.addEventListener('change', () => calcularTotales(cargaElement));
            inputs.cantidadPedido.addEventListener('input', () => calcularTotales(cargaElement));
        }

        async function calcularTotales(el) {
            const calibreId = parseInt(el.querySelector('.js-calibre')?.value, 10);
            const empaqueId = parseInt(el.querySelector('.js-tipo-empaque')?.value, 10);
            const formaPedido = el.querySelector('.js-forma-pedido')?.value;
            const cantidadPedido = parseFloat(el.querySelector('.js-cantidad-pedido')?.value);

            if (!calibreId || !formaPedido || !cantidadPedido) return;

            try {
                const res = await fetch(`${LOCAL_API_URL}/carga/calcular`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        catalogo_tamano_conteo_id: calibreId,
                        tipo_empaque_id: empaqueId || null,
                        forma_pedido: formaPedido,
                        cantidad_pedido: cantidadPedido,
                    }),
                });
                const json = await res.json();
                const d = json.data || {};
                el.querySelector('.js-empaques-calculados').value = d.empaques_calculados ?? '';
                el.querySelector('.js-num-pallets').value = d.numero_pallets ?? '';
                el.querySelector('.js-peso-neto').value = d.peso_neto_kg ?? '';
                el.querySelector('.js-peso-bruto').value = d.peso_bruto_kg ?? '';
                if (d.capacidad_por_empaque) {
                    el.querySelector('.js-capacidad-empaque').value = d.capacidad_por_empaque;
                }
            } catch (e) {
                console.warn('Cálculo local de carga', e);
            }

            const partitionCard = el.closest('.card[data-index]');
            if (partitionCard) {
                validarCapacidadParticion(partitionCard);
            }
        }

        function pesoBrutoParticion(card) {
            let total = 0;
            card.querySelectorAll('.carga-item').forEach(c => {
                total += parseFloat(c.querySelector('.js-peso-bruto')?.value) || 0;
            });
            return total;
        }

        function sugerirVehiculo(pesoKg) {
            if (!pesoKg || state.vehiculos.length === 0) return null;
            const ordenados = [...state.vehiculos]
                .filter(v => v.capacidad_kg > 0)
                .sort((a, b) => a.capacidad_kg - b.capacidad_kg);
            return ordenados.find(v => v.capacidad_kg >= pesoKg) || ordenados[ordenados.length - 1] || null;
        }

        async function validarCapacidadParticion(card) {
            const select = card.querySelector('.js-vehiculo');
            const resumen = card.querySelector('.js-capacidad-resumen');
            const alerta = card.querySelector('.js-capacidad-alerta');
            if (!select || !resumen) return;

            const pesoKg = pesoBrutoParticion(card);
            if (pesoKg <= 0) {
                resumen.textContent = '';
                alerta.style.display = 'none';
                return;
            }

            if (!select.value) {
                const sugerido = sugerirVehiculo(pesoKg);
                if (sugerido) {
                    select.value = String(sugerido.id);
                }
            }

            if (!select.value) {
                resumen.textContent = `Carga estimada: ${pesoKg.toFixed(1)} kg`;
                return;
            }

            try {
                const res = await fetch(`${LOCAL_API_URL}/capacidad/validar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ vehiculo_id: parseInt(select.value, 10), peso_kg: pesoKg }),
                });
                const data = await res.json();
                const pct = data.porcentaje_uso ?? 0;
                resumen.textContent = `${data.vehiculo}: ${pesoKg.toFixed(1)} / ${data.capacidad_kg} kg (${pct}%)`;

                if (!data.ok || pct > 100) {
                    alerta.textContent = data.mensaje || 'La carga supera el 100% de la capacidad del vehículo.';
                    alerta.style.display = 'block';
                    select.classList.add('is-invalid');
                } else {
                    alerta.style.display = 'none';
                    select.classList.remove('is-invalid');
                }
            } catch (e) {
                console.warn('Validación capacidad', e);
            }
        }


        // ========================================
        // PARTICIONES
        // ========================================
        function addPartition() {
            partitionCounter++;
            const template = document.getElementById('tplParticion');
            const clone = template.content.cloneNode(true);
            const card = clone.querySelector('.card');
            card.dataset.index = partitionCounter;
            card.querySelector('.num').textContent = partitionCounter;

            const select = clone.querySelector('.js-tipo-transporte');
            const offlineMsg = clone.querySelector('.js-transporte-offline');

            state.tiposTransporte.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.id;
                option.textContent = tipo.nombre;
                select.appendChild(option);
            });

            // Mostrar mensaje si estamos en modo offline
            if (!ToleranciaFallos.conectado && state.tiposTransporte.length > 0) {
                offlineMsg.style.display = 'block';
            }

            const today = new Date().toISOString().split('T')[0];
            clone.querySelector('.js-fecha-recogida').value = today;
            clone.querySelector('.js-fecha-recogida').min = today;

            const vehiculoSelect = clone.querySelector('.js-vehiculo');
            state.vehiculos.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.meta ? `${v.label} — ${v.meta}` : v.label;
                opt.dataset.capacidadKg = v.capacidad_kg;
                vehiculoSelect.appendChild(opt);
            });
            const horaRecogidaInput = clone.querySelector('.js-hora-recogida');
            horaRecogidaInput.addEventListener('change', () => actualizarHorasEntregaTodas());

            document.getElementById('particionesContainer').appendChild(clone);
            const addedCard = document.querySelector(`.card[data-index="${partitionCounter}"]`);
            vehiculoSelect.addEventListener('change', () => validarCapacidadParticion(addedCard));

            // Agregar primer carga pro defecto
            addCarga(addedCard.querySelector('.btn-add-carga'));
        }

        function removeParticion(btn) {
            btn.closest('.card').remove();
            renumberParticiones();
        }

        function renumberParticiones() {
            document.querySelectorAll('#particionesContainer .card').forEach((card, idx) => {
                card.querySelector('.num').textContent = idx + 1;
            });
        }

        function addCarga(btn) {
            const cardBody = btn.closest('.card-body');
            const container = cardBody.querySelector('.cargas-container');
            const template = document.getElementById('tplCarga');
            const clone = template.content.cloneNode(true);
            const div = clone.querySelector('.carga-item');

            container.appendChild(clone); // Append first to be in DOM

            const lastCarga = container.lastElementChild;
            initCargaEvents(lastCarga);
        }

        function removeCarga(btn) {
            // Find the closest .carga-item and remove it
            const item = btn.closest('.carga-item');
            if (item) item.remove();
        }

        // ========================================
        // NAVEGACIÓN WIZARD
        // ========================================
        async function nextStep() {
            if (state.currentStep === 2) {
                const capacidadOk = await validarCapacidadTodasParticiones();
                if (!capacidadOk) return;
            }
            if (validateCurrentStep()) {
                goToStep(state.currentStep + 1);
            }
        }

        async function validarCapacidadTodasParticiones() {
            const cards = [...document.querySelectorAll('#particionesContainer > .card')]
                .filter(c => c.offsetParent !== null);
            for (const card of cards) {
                await validarCapacidadParticion(card);
                const alerta = card.querySelector('.js-capacidad-alerta');
                if (alerta && alerta.style.display !== 'none') {
                    Swal.fire('Capacidad excedida', alerta.textContent, 'warning');
                    return false;
                }
            }
            return true;
        }

        function prevStep() {
            goToStep(state.currentStep - 1);
        }

        function goToStep(step) {
            document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
            document.querySelector(`.wizard-step[data-step="${step}"]`).classList.add('active');

            document.querySelectorAll('.step-indicator').forEach(ind => {
                const stepNum = parseInt(ind.dataset.step, 10);
                ind.classList.remove('active', 'done', 'pending');
                if (stepNum === step) {
                    ind.classList.add('active');
                } else if (stepNum < step) {
                    ind.classList.add('done');
                } else {
                    ind.classList.add('pending');
                }
            });

            state.currentStep = step;

            document.getElementById('btnPrev').style.display = step === 1 ? 'none' : 'inline-block';

            if (step === 3) {
                document.getElementById('btnNext').style.display = 'none';
                document.getElementById('btnFinish').style.display = 'inline-block';
                updateSummary();
            } else {
                document.getElementById('btnNext').style.display = 'inline-block';
                document.getElementById('btnFinish').style.display = 'none';
            }

            if (step === 1 && state.map) {
                setTimeout(() => state.map.invalidateSize(), 200);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function validateCurrentStep() {
            if (state.currentStep === 1) {
                const nombre = document.getElementById('nombre_remitente').value.trim();
                const telefono = document.getElementById('telefono_remitente').value.trim();
                if (!nombre || !telefono) {
                    Swal.fire('Campos requeridos', 'Por favor completa tu nombre y teléfono.', 'warning');
                    return false;
                }
                if (!state.markers.origin || !state.markers.destination) {
                    Swal.fire('Ubicación requerida', 'Por favor marca el origen y destino en el mapa.', 'warning');
                    return false;
                }
                return true;
            }

            if (state.currentStep === 2) {
                const cards = document.querySelectorAll('#particionesContainer > .card');
                if (cards.length === 0) {
                    Swal.fire('Sin envíos', 'Debes agregar al menos un envío/camión.', 'warning');
                    return false;
                }
                let isValid = true;
                let missingFields = [];

                console.log(`Validando: Encontradas ${cards.length} particiones.`);

                cards.forEach((card, idx) => {
                    // Ignore hidden cards (Ghost partitions)
                    if (card.offsetParent === null) {
                        console.log(`Envío #${idx + 1} ignorado por estar oculto.`);
                        return;
                    }

                    // 1. Check Inputs
                    const inputs = card.querySelectorAll('input[required], select[required]');
                    inputs.forEach(input => {
                        // Check visibility: standard check + legacy check
                        const isVisible = !!(input.offsetWidth || input.offsetHeight || input.getClientRects().length);
                        if (!isVisible) return; // Ignore hidden

                        if (!input.value || input.value.trim() === '') {
                            input.classList.add('is-invalid');
                            isValid = false;

                            // Get label
                            const formGroup = input.closest('.form-group');
                            let label = 'Campo sin nombre';
                            if (formGroup) {
                                const labelEl = formGroup.querySelector('label');
                                if (labelEl) label = labelEl.innerText.replace('*', '').trim();
                            }
                            missingFields.push(`Envío #${idx + 1}: ${label}`);
                            console.log(`Campo invalido: Envío ${idx + 1} - ${label}`, input);
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });

                    // 2. Check Cargas
                    const cargas = card.querySelectorAll('.carga-item');
                    if (cargas.length === 0) {
                        isValid = false;
                        missingFields.push(`Envío #${idx + 1}: Falta agregar productos`);
                        console.log(`Envío ${idx + 1} sin cargas`);
                    }
                });

                if (!isValid) {
                    const msg = missingFields.length > 0
                        ? 'Por favor corrige los siguientes errores:\n\n' + [...new Set(missingFields)].join('\n')
                        : 'Error desconocido en validación.';

                    Swal.fire({
                        title: 'Datos Incompletos',
                        text: msg, // Use text mostly, or html if needed, but text handles newlines better in standard alerts usually? 
                        // Swal text doesn't always handle \n well, use html
                        html: msg.replace(/\n/g, '<br>'),
                        icon: 'warning'
                    });
                }
                return isValid;
            }

            return true;
        }

        function updateSummary() {
            // Datos Remitente
            document.getElementById('resNombre').textContent = document.getElementById('nombre_remitente').value;
            document.getElementById('resTelefono').textContent = document.getElementById('telefono_remitente').value;
            document.getElementById('resEmail').textContent = document.getElementById('email_remitente').value || 'N/A';

            // Ruta
            document.getElementById('resOrigen').textContent = document.getElementById('txtNombreOrigen').value;
            document.getElementById('resDestino').textContent = document.getElementById('txtNombreDestino').value;

            // Particiones
            const container = document.getElementById('resumenParticiones');
            container.innerHTML = '';

            document.querySelectorAll('#particionesContainer > .card').forEach((card, idx) => {
                const transporteSelect = card.querySelector('.js-tipo-transporte');
                if (!transporteSelect) return; // Skip if not a partition card
                const transporteNombre = transporteSelect.options[transporteSelect.selectedIndex]?.text || 'No seleccionado';

                const cargas = [];
                card.querySelectorAll('.carga-item').forEach(c => {
                    const prodSel = c.querySelector('.js-producto');
                    const prod = prodSel?.options[prodSel.selectedIndex]?.text;
                    const cant = c.querySelector('.js-cantidad-pedido').value;
                    const forma = c.querySelector('.js-forma-pedido').value;
                    if (prod) cargas.push(`${prod} (${cant} ${forma})`);
                });

                const html = `
                                                                                            <div class="callout callout-info mb-2">
                                                                                                <h5>Envío #${idx + 1}: ${transporteNombre}</h5>
                                                                                                <p class="mb-1"><strong>Recogida:</strong> ${card.querySelector('.js-fecha-recogida').value} ${card.querySelector('.js-hora-recogida').value}</p>
                                                                                                <p class="mb-0"><strong>Cargas:</strong> ${cargas.join(', ') || 'Sin cargas aun'}</p>
                                                                                            </div>
                                                                                        `;
                container.insertAdjacentHTML('beforeend', html);
            });
        }

        async function submitForm() {
            // Build particiones array with the correct structure
            const particiones = [];

            document.querySelectorAll('#particionesContainer > .card').forEach(card => {
                const cargas = [];

                card.querySelectorAll('.carga-item').forEach(c => {
                    cargas.push({
                        id_insumo: parseInt(c.querySelector('.js-producto')?.value, 10) || null,
                        id_producto: parseInt(c.querySelector('.js-producto')?.value, 10) || null,
                        id_tipo_empaque: parseInt(c.querySelector('.js-tipo-empaque')?.value) || null,
                        cantidad: parseFloat(c.querySelector('.js-cantidad-pedido')?.value) || 1,
                        peso: parseFloat(c.querySelector('.js-peso-neto')?.value) || 0,
                        // Optional specs
                        conteo_por_empaque: parseInt(c.querySelector('.js-calibre')?.options?.[c.querySelector('.js-calibre')?.selectedIndex]?.dataset?.conteo) || null,
                        peso_promedio_unidad: parseFloat(c.querySelector('.js-peso-unidad')?.value) || null,
                        largo_cm: parseFloat(c.querySelector('.js-largo')?.value) || null,
                        ancho_cm: parseFloat(c.querySelector('.js-ancho')?.value) || null,
                        alto_cm: parseFloat(c.querySelector('.js-alto')?.value) || null,
                        peso_neto_kg: parseFloat(c.querySelector('.js-peso-neto')?.value) || null,
                        tara_kg: parseFloat(c.querySelector('.js-tara')?.value) || null,
                        peso_bruto_kg: parseFloat(c.querySelector('.js-peso-bruto')?.value) || null,
                        forma_pedido: ['empaques', 'cajas', 'bolsas', 'pallets'].includes(c.querySelector('.js-forma-pedido')?.value) ? c.querySelector('.js-forma-pedido').value : null,
                        cantidad_pedido: parseInt(c.querySelector('.js-cantidad-pedido')?.value) || null
                    });
                });

                particiones.push({
                    id_tipo_transporte: parseInt(card.querySelector('.js-tipo-transporte')?.value, 10) || 1,
                    id_vehiculo: parseInt(card.querySelector('.js-vehiculo')?.value, 10) || null,
                    cargas: cargas,
                    recogidaEntrega: {
                        fecha_recogida: card.querySelector('.js-fecha-recogida')?.value,
                        hora_recogida: card.querySelector('.js-hora-recogida')?.value,
                        hora_entrega: card.querySelector('.js-hora-entrega')?.value,
                        instrucciones_recogida: document.getElementById('instrucciones_recogida')?.value || null,
                        instrucciones_entrega: document.getElementById('instrucciones_entrega')?.value || null,
                    }
                });
            });

            console.log("Particiones:", particiones);

            if (!ToleranciaFallos.conectado) {
                // Offline mode
                const offlinePayload = {
                    direccion: {
                        nombreorigen: document.getElementById('txtNombreOrigen').value,
                        nombredestino: document.getElementById('txtNombreDestino').value,
                        origen_lat: state.originCoords?.lat,
                        origen_lng: state.originCoords?.lng,
                        destino_lat: state.destinationCoords?.lat,
                        destino_lng: state.destinationCoords?.lng,
                    },
                    envio: {
                        nombre_remitente: document.getElementById('nombre_remitente').value,
                        telefono_remitente: document.getElementById('telefono_remitente').value,
                        email_remitente: document.getElementById('email_remitente').value,
                        numero_solicitud: document.getElementById('numero_solicitud').value,
                        particiones: particiones
                    }
                };
                ToleranciaFallos.guardarEnCola(offlinePayload);
                Swal.fire('Guardado Offline', 'El envío se ha guardado localmente.', 'info').then(() => {
                    window.location.href = "{{ route('envios.seguimiento') }}";
                });
            } else {
                try {
                    // 1. Create direccion (single record with origen AND destino)
                    const direccionPayload = {
                        nombreorigen: document.getElementById('txtNombreOrigen').value,
                        nombredestino: document.getElementById('txtNombreDestino').value,
                        origen_lat: state.originCoords?.lat || 0,
                        origen_lng: state.originCoords?.lng || 0,
                        destino_lat: state.destinationCoords?.lat || 0,
                        destino_lng: state.destinationCoords?.lng || 0,
                        rutageojson: state.geoJSON || null
                    };

                    console.log("Creando dirección:", direccionPayload);
                    const resDireccion = await fetch(`${LOCAL_API_URL}/direccion`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(direccionPayload)
                    });

                    if (!resDireccion.ok) {
                        const err = await resDireccion.json();
                        console.error('Error creando dirección:', err);
                        Swal.fire('Error', 'Error creando dirección: ' + (err.mensaje || JSON.stringify(err.detalles || err)), 'error');
                        return;
                    }

                    const direccionData = await resDireccion.json();
                    console.log("Dirección creada con ID:", direccionData.id_direccion);

                    // 2. Create envio with id_direccion and particiones
                    const envioPayload = {
                        nombre_remitente: document.getElementById('nombre_remitente').value,
                        telefono_remitente: document.getElementById('telefono_remitente').value,
                        email_remitente: document.getElementById('email_remitente').value || null,
                        numero_solicitud: document.getElementById('numero_solicitud').value || null,
                        id_direccion: direccionData.id_direccion,
                        particiones: particiones
                    };

                    console.log("Creando envío:", envioPayload);
                    const resEnvio = await fetch(`${LOCAL_API_URL}/crear-envio`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(envioPayload)
                    });

                    if (resEnvio.ok) {
                        const envioData = await resEnvio.json();
                        console.log('Envío creado:', envioData);
                        Swal.fire('¡Éxito!', `Envío #${envioData.id_envio || ''} creado correctamente.`, 'success').then(() => {
                            window.location.href = "{{ route('envios.seguimiento') }}";
                        });
                    } else {
                        const err = await resEnvio.json();
                        console.error('Error creando envío:', err);
                        Swal.fire('Error', 'Error al crear envío: ' + (err.error || err.mensaje || JSON.stringify(err.detalles || err)), 'error');
                    }
                } catch (e) {
                    console.error('Error:', e);
                    Swal.fire('Error', 'Error de conexión: ' + e.message, 'error');
                }
            }
        }
    </script>
@endpush