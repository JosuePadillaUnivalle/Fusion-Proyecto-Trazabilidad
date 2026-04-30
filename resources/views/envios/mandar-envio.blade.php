@extends('layouts.app')

@section('title', 'Crear envío')

@section('page_title', 'Crear envío')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Crear envío</li>
@endsection

@section('content')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
        }

        #map {
            height: 100%;
        }

        .readonly-input {
            background-color: #f4f6f9;
            cursor: not-allowed;
        }

        .equal-height-row {
            display: flex;
            flex-wrap: wrap;
        }

        .equal-height-row>[class*='col-'] {
            display: flex;
            flex-direction: column;
        }

        .equal-height-row .card {
            flex: 1;
        }

        /* Estilos del indicador de conexión */
        #conexion-indicator {
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

        .indicador-online {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .indicador-offline {
            background: linear-gradient(135deg, #dc3545, #e74a3b);
            color: white;
            animation: pulse-offline 2s infinite;
        }

        @keyframes pulse-offline {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .cola-pendientes-card {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>

    <!-- Indicador de conexión -->
    <div id="conexion-indicator" class="indicador-online">
        <i class="fas fa-wifi"></i>
        <span id="conexion-texto">Verificando conexión...</span>
        <span class="badge badge-light ml-2" id="pendientes-badge" style="display: none;">0 pendientes</span>
    </div>

    <!-- Alert de cola local si hay pendientes -->
    <div id="cola-alert" class="cola-pendientes-card" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-clock text-warning mr-2"></i>
                <strong>Tienes envíos en cola local</strong>
                <p class="mb-0 small text-muted">Se sincronizarán automáticamente cuando la conexión esté disponible</p>
            </div>
            <button class="btn btn-warning btn-sm" onclick="ToleranciaFallos.sincronizarPendientes()">
                <i class="fas fa-sync-alt"></i> Sincronizar Ahora
            </button>
        </div>
    </div>

    <!-- Alert informativo -->
    <div class="alert alert-info alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="icon fas fa-info-circle"></i> Guía rápida</h5>
        Crea tu solicitud de envío en 3 simples pasos: <strong>Ubicación</strong>, <strong>Detalles del envío</strong> y
        <strong>Confirmación</strong>.
        <br><small class="text-muted"><i class="fas fa-shield-alt"></i> Sistema con tolerancia a fallos: tus envíos se
            guardarán localmente si no hay conexión.</small>
    </div>

    <!-- Progress Steps usando BS4 -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4 step-indicator" data-step="1">
                    <div class="mb-2">
                        <span class="step-badge badge badge-primary badge-lg"
                            style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.5rem;">1</span>
                    </div>
                    <h6 class="font-weight-bold">Paso 1: Ubicación</h6>
                    <small class="text-muted d-none d-md-block">Origen y Destino</small>
                </div>
                <div class="col-md-4 step-indicator" data-step="2">
                    <div class="mb-2">
                        <span class="step-badge badge badge-secondary badge-lg"
                            style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.5rem;">2</span>
                    </div>
                    <h6>Paso 2: Detalles</h6>
                    <small class="text-muted d-none d-md-block">Cargas y Transporte</small>
                </div>
                <div class="col-md-4 step-indicator" data-step="3">
                    <div class="mb-2">
                        <span class="step-badge badge badge-secondary badge-lg"
                            style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.5rem;">3</span>
                    </div>
                    <h6>Paso 3: Confirmación</h6>
                    <small class="text-muted d-none d-md-block">Resumen y Envío</small>
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
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Datos del Envío</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nº de Solicitud</label>
                                <input type="text" class="form-control" id="numero_solicitud" placeholder="Ej: SOL-001"
                                    maxlength="50">
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
                            <div class="callout callout-info">
                                <p class="mb-0"><i class="fas fa-info-circle"></i> Haz clic en el mapa para marcar primero
                                    el <strong>Origen</strong> y luego el <strong>Destino</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa -->
                <div class="col-md-8">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-map"></i> Mapa Interactivo</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="btnResetMap">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0" style="flex: 1; display: flex;">
                            <div id="map" style="width: 100%;"></div>
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
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle"></i> Resumen de la Solicitud</h3>
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
            <button type="button" class="btn btn-primary" id="btnNext">
                Siguiente <i class="fas fa-arrow-right"></i>
            </button>
            <button type="button" class="btn btn-success" id="btnFinish" style="display: none;">
                <i class="fas fa-check"></i> Confirmar y Crear Envío
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
                            <label>Hora Entrega <span class="text-danger">*</span></label>
                            <input type="time" class="form-control js-hora-entrega" required>
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

                <h6 class="text-info border-bottom pb-1 mb-3"><i class="fas fa-tags"></i> Información del Producto <span
                        class="text-danger">*</span></h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Categoría</label>
                            <select class="form-control form-control-sm js-categoria" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="small">Producto</label>
                            <select class="form-control form-control-sm js-producto" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
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

@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // URLs de las APIs
        // API_URL se usa para verificar disponibilidad del servicio de envíos
        const API_URL = '{{ config("external_api.orgtrack_url") }}';
        // LOCAL_API_URL se usa para todas las llamadas (proxy local para evitar CORS)
        const LOCAL_API_URL = '/envios/api';
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
                this.verificarConexion();
                setInterval(() => this.verificarConexion(), 30000);
            },

            verificarConexion: async function () {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 5000);

                    const response = await fetch(`${LOCAL_API_URL}/tipo-transporte`, {
                        method: 'GET',
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);
                    this.conectado = response.ok;

                    if (this.conectado && this.colaLocal.length > 0) {
                        this.sincronizarPendientes();
                    }
                } catch (error) {
                    console.warn('API no disponible:', error.message);
                    this.conectado = false;
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
                    indicator.className = 'indicador-online';
                    indicator.querySelector('i').className = 'fas fa-wifi';
                    texto.textContent = 'Conectado';
                } else {
                    indicator.className = 'indicador-offline';
                    indicator.querySelector('i').className = 'fas fa-wifi-slash';
                    texto.textContent = 'Sin Conexión - Modo Offline';
                }

                if (this.colaLocal.length > 0) {
                    badge.style.display = 'inline';
                    badge.textContent = `${this.colaLocal.length} pendientes`;
                    colaAlert.style.display = 'block';
                } else {
                    badge.style.display = 'none';
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
                localStorage.setItem('agronexus_envios_pendientes', JSON.stringify(this.colaLocal));
                this.actualizarIndicador();

                return envio;
            },

            cargarColaLocal: function () {
                try {
                    const stored = localStorage.getItem('agronexus_envios_pendientes');
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
                localStorage.setItem('agronexus_envios_pendientes', JSON.stringify(this.colaLocal));
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
            tiposTransporte: [],

            // Cache Catalogos
            catalogos: {
                categorias: [],
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
            await loadCatalogos(); // Cargar catalogos
            addPartition();
            setupEventListeners();
            setMinDate();
        });

        async function loadCatalogos() {
            try {
                state.catalogos.categorias = await (await fetch(`${LOCAL_API_URL}/catalogo-categorias`)).json();
                state.catalogos.productos = await (await fetch(`${LOCAL_API_URL}/catalogo-productos`)).json();
                state.catalogos.tiposEmpaque = await (await fetch(`${LOCAL_API_URL}/catalogo-tipos-empaque`)).json();
                state.catalogos.tamanoConteo = await (await fetch(`${LOCAL_API_URL}/catalogo-tamano-conteo`)).json();
            } catch (e) {
                console.error("Error cargando catálogos mock", e);
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

            try {
                const url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${ORS_KEY}&start=${start.lng},${start.lat}&end=${end.lng},${end.lat}`;
                const res = await fetch(url);
                const data = await res.json();

                if (data.features && data.features.length > 0) {
                    const geoJSON = data.features[0];
                    if (state.routeLayer) state.map.removeLayer(state.routeLayer);
                    state.routeLayer = L.geoJSON(geoJSON, {
                        style: { color: '#3b82f6', weight: 5, opacity: 0.8 }
                    }).addTo(state.map);
                    state.map.fitBounds(state.routeLayer.getBounds(), { padding: [50, 50] });
                    state.geoJSON = JSON.stringify({ type: "FeatureCollection", features: [geoJSON] });
                }
            } catch (e) {
                const line = [[start.lat, start.lng], [end.lat, end.lng]];
                if (state.routeLayer) state.map.removeLayer(state.routeLayer);
                state.routeLayer = L.polyline(line, { color: 'red', weight: 4, dashArray: '10, 10' }).addTo(state.map);
                state.map.fitBounds(state.routeLayer.getBounds(), { padding: [50, 50] });
                state.geoJSON = JSON.stringify({
                    type: "FeatureCollection",
                    features: [{ type: "Feature", geometry: { type: "LineString", coordinates: [[start.lng, start.lat], [end.lng, end.lat]] } }]
                });
            }
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

        // Iniciar inputs y eventos para una carga especifica
        function initCargaEvents(cargaElement) {
            const selects = {
                categoria: cargaElement.querySelector('.js-categoria'),
                producto: cargaElement.querySelector('.js-producto'),
                empaque: cargaElement.querySelector('.js-tipo-empaque'),
                calibre: cargaElement.querySelector('.js-calibre'),
                formaPedido: cargaElement.querySelector('.js-forma-pedido')
            };

            const inputs = {
                cantidadPedido: cargaElement.querySelector('.js-cantidad-pedido')
            };

            // Llenar Categorias
            state.catalogos.categorias.forEach(cat => {
                selects.categoria.appendChild(new Option(cat.nombre, cat.id));
            });

            // Evento Categoria -> Productos
            selects.categoria.addEventListener('change', () => {
                selects.producto.innerHTML = '<option value="">Seleccione...</option>';
                selects.calibre.innerHTML = '<option value="">Seleccione...</option>';
                const catId = parseInt(selects.categoria.value);
                const productos = state.catalogos.productos.filter(p => p.id_categoria === catId);

                productos.forEach(p => {
                    const opt = new Option(p.nombre, p.id);
                    opt.dataset.pesoPromedio = p.peso_promedio;
                    selects.producto.appendChild(opt);
                });
            });

            // Evento Producto -> Calibres
            selects.producto.addEventListener('change', () => {
                selects.calibre.innerHTML = '<option value="">Seleccione...</option>';
                const prodId = parseInt(selects.producto.value);
                const calibres = state.catalogos.tamanoConteo.filter(tc => tc.id_producto === prodId);

                calibres.forEach(c => {
                    const opt = new Option(c.nombre, c.id);
                    opt.dataset.conteo = c.conteo_por_empaque;
                    opt.dataset.peso = c.peso_promedio_unidad;
                    selects.calibre.appendChild(opt);
                });

                // Set peso promedio default from producto if specific calibre not selected yet
                const selectedProd = selects.producto.options[selects.producto.selectedIndex];
                if (selectedProd) {
                    cargaElement.querySelector('.js-peso-unidad').value = selectedProd.dataset.pesoPromedio || '';
                }
            });

            // Llenar Tipos Empaque
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

            // Evento Tipo Empaque -> Medidas
            selects.empaque.addEventListener('change', () => {
                const opt = selects.empaque.options[selects.empaque.selectedIndex];
                if (opt && opt.value) {
                    cargaElement.querySelector('.js-largo').value = opt.dataset.largo;
                    cargaElement.querySelector('.js-ancho').value = opt.dataset.ancho;
                    cargaElement.querySelector('.js-alto').value = opt.dataset.alto;
                    cargaElement.querySelector('.js-tara').value = opt.dataset.tara;
                    cargaElement.querySelector('.js-unidades-pallet').value = opt.dataset.unidadesPallet;
                    // Default capacidad if not overridden
                    // cargaElement.querySelector('.js-capacidad-empaque').value = opt.dataset.capacidad;
                }
                calcularTotales(cargaElement);
            });

            // Evento Calibre -> Conteo y Peso
            selects.calibre.addEventListener('change', () => {
                const opt = selects.calibre.options[selects.calibre.selectedIndex];
                if (opt && opt.value) {
                    // Actualizar peso uni y capacidad
                    cargaElement.querySelector('.js-peso-unidad').value = opt.dataset.peso;
                    // Capacidad por empaque viene del conteo
                    cargaElement.querySelector('.js-capacidad-empaque').value = opt.dataset.conteo;
                }
                calcularTotales(cargaElement);
            });

            // Eventos Calculo
            selects.formaPedido.addEventListener('change', () => calcularTotales(cargaElement));
            inputs.cantidadPedido.addEventListener('input', () => calcularTotales(cargaElement));
        }

        function calcularTotales(el) {
            // Variables de Entrada
            const conteoVal = el.querySelector('.js-capacidad-empaque').value;
            const pesoPromedioVal = el.querySelector('.js-peso-unidad').value;
            const cantidadPedidoVal = el.querySelector('.js-cantidad-pedido').value;
            const formaPedido = el.querySelector('.js-forma-pedido').value;
            const unidadesPalletVal = el.querySelector('.js-unidades-pallet').value;
            const taraVal = el.querySelector('.js-tara').value;

            // Validar mínimos
            if (!conteoVal || !pesoPromedioVal || !cantidadPedidoVal || !formaPedido) return;

            const conteo = parseFloat(conteoVal);
            const pesoPromedio = parseFloat(pesoPromedioVal); // Kg
            const cantidadPedido = parseFloat(cantidadPedidoVal);
            const unidadesPallet = parseFloat(unidadesPalletVal) || 48; // Default 48
            const tara = parseFloat(taraVal) || 0;
            const capacidadEmpaque = conteo; // Asumimos capacidad = conteo por ahora

            let empaquesCalculados = 0;
            let cantidadTotal = 0;

            // A. Cálculo de Cantidades
            if (formaPedido === 'unidades') {
                empaquesCalculados = Math.ceil(cantidadPedido / conteo);
                cantidadTotal = cantidadPedido;
            } else if (formaPedido === 'empaques') {
                empaquesCalculados = cantidadPedido;
                cantidadTotal = capacidadEmpaque * cantidadPedido;
            }

            // B. Cálculo de Pesos y Pallets
            const pesoNeto = cantidadTotal * pesoPromedio;
            const pesoBrutoPorEmpaque = (capacidadEmpaque * pesoPromedio) + tara;
            const pesoBrutoTotal = pesoBrutoPorEmpaque * empaquesCalculados;
            const numeroPallets = Math.ceil(empaquesCalculados / unidadesPallet);

            // Actualizar UI
            el.querySelector('.js-empaques-calculados').value = empaquesCalculados;
            el.querySelector('.js-num-pallets').value = numeroPallets;
            el.querySelector('.js-peso-neto').value = pesoNeto.toFixed(2);
            el.querySelector('.js-peso-bruto').value = pesoBrutoTotal.toFixed(2);
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

            document.getElementById('particionesContainer').appendChild(clone);
            const addedCard = document.querySelector(`.card[data-index="${partitionCounter}"]`);

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
        function nextStep() {
            if (validateCurrentStep()) {
                goToStep(state.currentStep + 1);
            }
        }

        function prevStep() {
            goToStep(state.currentStep - 1);
        }

        function goToStep(step) {
            document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
            document.querySelector(`.wizard-step[data-step="${step}"]`).classList.add('active');

            document.querySelectorAll('.step-indicator').forEach(ind => {
                const badge = ind.querySelector('.step-badge');
                const stepNum = parseInt(ind.dataset.step);
                const h6 = ind.querySelector('h6');

                if (stepNum === step) {
                    badge.classList.remove('badge-secondary', 'badge-success');
                    badge.classList.add('badge-primary');
                    h6.classList.add('font-weight-bold', 'text-primary');
                } else if (stepNum < step) {
                    badge.classList.remove('badge-secondary', 'badge-primary');
                    badge.classList.add('badge-success');
                    h6.classList.remove('font-weight-bold', 'text-primary');
                } else {
                    badge.classList.remove('badge-primary', 'badge-success');
                    badge.classList.add('badge-secondary');
                    h6.classList.remove('font-weight-bold', 'text-primary');
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
                        id_categoria: parseInt(c.querySelector('.js-categoria')?.value) || null,
                        id_producto: parseInt(c.querySelector('.js-producto')?.value) || null,
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
                    id_tipo_transporte: parseInt(card.querySelector('.js-tipo-transporte')?.value) || 1,
                    cargas: cargas,
                    recogidaEntrega: {
                        fecha_recogida: card.querySelector('.js-fecha-recogida')?.value,
                        hora_recogida: card.querySelector('.js-hora-recogida')?.value,
                        hora_entrega: card.querySelector('.js-hora-entrega')?.value
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