@extends('layouts.app')

@section('title', 'Registrar cosecha | Fusion-Proyectos')
@section('page_title', 'Registrar Cosecha')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('producciones.index') }}" style="color: #2c5530;">Producciones</a></li>
    <li class="breadcrumb-item active">Nueva Cosecha</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
<style>
    .form-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    .form-card .card-header {
        background: linear-gradient(135deg, #2c5530, #4a7c59);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem;
    }
    
    .form-control {
        border-radius: 8px;
        border: 2px solid #dee2e6;
        padding: 12px 15px;
        height: auto;
        min-height: 46px;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: #2c5530;
        box-shadow: 0 0 0 0.2rem rgba(44,85,48,0.15);
    }
    select.form-control {
        padding-right: 35px;
    }

    /* Sección de almacenamiento */
    .almacen-section {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 20px;
        border: 2px dashed #6c757d;
        margin-top: 20px;
        transition: all 0.3s ease;
    }
    .almacen-section.active {
        border-color: #28a745;
        border-style: solid;
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
    }

    .almacen-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .almacen-card:hover {
        border-color: #28a745;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    }
    .almacen-card.selected {
        border-color: #28a745;
        background: #d4edda;
    }
    .almacen-card .almacen-icon {
        font-size: 1.8rem;
        color: #6c757d;
        width: 45px;
    }
    .almacen-card.selected .almacen-icon {
        color: #28a745;
    }
    .almacen-card .almacen-nombre {
        font-weight: 600;
        color: #1a252f;
    }
    .almacen-card .almacen-tipo {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .capacidad-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 8px;
    }
    .capacidad-bar .fill {
        height: 100%;
        border-radius: 3px;
    }
    .capacidad-bar .fill.low { background: #28a745; }
    .capacidad-bar .fill.medium { background: #ffc107; }
    .capacidad-bar .fill.high { background: #dc3545; }

    .info-panel {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        border-left: 4px solid #2c5530;
    }
    .form-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    .form-card .card-header {
        background: linear-gradient(135deg, #2c5530, #4a7c59);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem;
    }
    .guia-campo {
        background: #f8fbf8;
        border-left: 3px solid #2c5530;
        border-radius: 0 8px 8px 0;
        padding: 0.65rem 0.85rem;
        margin-bottom: 0.75rem;
        font-size: 0.85rem;
        color: #495057;
    }
    .guia-campo strong { color: #2c5530; }
</style>
@endpush

@section('content')
<div class="modulo-prod">
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card form-card card-modulo-main">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="fas fa-tractor mr-2"></i>Registrar Cosecha</h3>
            </div>

            @if($errors->any())
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('producciones.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    
                    {{-- Lote --}}
                    <div class="form-group">
                        <label><i class="fas fa-map-marked-alt mr-1 text-success"></i> Lote a cosechar <span class="text-danger">*</span></label>
                        <div class="guia-campo mb-2">
                            <strong>¿Para qué sirve?</strong> Identifica el campo parcelado que ya está en etapa productiva.
                            Solo aparecen lotes en estado <em>en producción</em> (listos para cosechar).
                        </div>
                        <select name="loteid" id="loteid" class="form-control" required>
                            <option value="">-- Seleccione un lote en producción --</option>
                            @foreach($lotes as $l)
                                <option value="{{ $l->loteid }}"
                                        @selected((string) ($lotePreseleccionado ?? '') === (string) $l->loteid)
                                        data-responsable="{{ trim(($l->usuario->nombre ?? '').' '.($l->usuario->apellido ?? '')) }}"
                                        data-cultivo="{{ $l->cultivo->nombre ?? 'Sin cultivo' }}"
                                        data-superficie="{{ $l->superficie }}">
                                    {{ $l->nombre }} - {{ $l->cultivo->nombre ?? 'Sin cultivo' }} ({{ $l->superficie }} ha)
                                </option>
                            @endforeach
                        </select>
                        @if($lotes->isEmpty())
                            <small class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> No hay lotes en estado «en producción».
                                Cambia el estado del lote en <a href="{{ route('lotes.index') }}">Gestión de lotes</a> antes de registrar la cosecha.
                            </small>
                        @else
                            <small class="form-text text-muted">
                                {{ $lotes->count() === 1 ? 'Un solo lote disponible — ya está preseleccionado.' : 'Solo lotes en estado «en producción».' }}
                            </small>
                        @endif
                    </div>

                    <div id="loteInfo" class="info-panel mb-3" style="{{ ($lotePreseleccionado ?? null) ? '' : 'display: none;' }}">
                        <strong><i class="fas fa-leaf mr-1 text-success"></i> Cultivo:</strong> <span id="infoCultivo"></span>
                        <span class="mx-2">|</span>
                        <strong><i class="fas fa-user mr-1"></i> Responsable:</strong> <span id="infoResponsable"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-cogs mr-1 text-success"></i> Proceso de planta</label>
                                <div class="guia-campo mb-2">
                                    <strong>Opcional.</strong> Indica qué tratamiento industrial aplicó la cosecha (lavado, secado, empaque, etc.).
                                    Catálogo en <a href="{{ route('procesos-planta.index') }}">Procesos de planta</a>.
                                </div>
                                <select name="procesoplantaid" class="form-control">
                                    <option value="">-- Sin proceso específico --</option>
                                    @foreach($procesos as $proceso)
                                        <option value="{{ $proceso->procesoplantaid }}">{{ $proceso->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-industry mr-1 text-success"></i> Máquina usada</label>
                                <div class="guia-campo mb-2">
                                    <strong>Opcional.</strong> Registra el equipo utilizado (cosechadora, secadora, embolsadora).
                                    Catálogo en <a href="{{ route('maquinas-planta.index') }}">Máquinas de planta</a>.
                                </div>
                                <select name="maquinaplantaid" class="form-control">
                                    <option value="">-- Sin máquina específica --</option>
                                    @foreach($maquinas as $maquina)
                                        <option value="{{ $maquina->maquinaplantaid }}">{{ $maquina->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Cantidad --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><i class="fas fa-balance-scale mr-1 text-success"></i> Cantidad cosechada <span class="text-danger">*</span></label>
                                <div class="guia-campo mb-2">
                                    <strong>Peso o volumen</strong> obtenido en esta cosecha. El sistema convierte automáticamente a kilogramos para inventario y almacén.
                                </div>
                                <input type="number" step="0.01" name="cantidad" id="cantidad"
                                       class="form-control" min="0.01" required value="{{ old('cantidad') }}"
                                       placeholder="Ej: 500">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Unidad <span class="text-danger">*</span></label>
                                <select name="unidadmedidaid" id="unidadmedidaid" class="form-control" required>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->unidadmedidaid }}" 
                                                data-abrev="{{ $u->abreviatura }}"
                                                {{ $u->abreviatura == 'kg' ? 'selected' : '' }}>
                                            {{ $u->abreviatura }} ({{ $u->nombre }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Sección de Almacenamiento --}}
                    <div class="almacen-section" id="almacenSection">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-warehouse mr-2"></i>Enviar a almacén</h6>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="enviarAlmacen" name="enviar_almacen" value="1">
                                <label class="custom-control-label" for="enviarAlmacen">Almacenar cosecha</label>
                            </div>
                        </div>
                        <div class="guia-campo mb-3">
                            <strong>Opcional pero recomendado.</strong> Si activas el interruptor, la cosecha ingresa al inventario del almacén elegido.
                            El sistema valida capacidad disponible y sugiere el almacén según el cultivo.
                        </div>
                        <p class="small text-muted mb-2" id="almacen-seleccionado">
                            <i class="fas fa-warehouse mr-1"></i> <strong>Almacén:</strong> ninguno seleccionado
                        </p>

                        <div id="almacenOptions" style="display: none;">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Seleccione el almacén o silo donde guardar la producción
                            </p>

                            <div class="row" id="almacenesContainer">
                                @forelse($almacenes as $almacen)
                                    @php
                                        $usado = $almacen->almacenamientos->whereNull('fechasalida')->sum('cantidad');
                                        $disponible = $almacen->capacidad - $usado;
                                        $porcentaje = $almacen->capacidad > 0 ? ($usado / $almacen->capacidad) * 100 : 0;
                                        $fillClass = $porcentaje < 50 ? 'low' : ($porcentaje < 80 ? 'medium' : 'high');
                                    @endphp
                                    <div class="col-md-6 mb-2">
                                        <div class="almacen-card" 
                                             data-id="{{ $almacen->almacenid }}" 
                                             data-disponible="{{ $disponible }}" 
                                             data-nombre="{{ $almacen->nombre }}" 
                                             data-um-almacen="{{ $almacen->unidadMedida->abreviatura }}"
                                             data-tipo="{{ strtolower($almacen->tipoAlmacen->nombre ?? 'general') }}"
                                             data-tags="{{ strtolower($almacen->nombre . ' ' . ($almacen->tipoAlmacen->nombre ?? '')) }}">
                                            <div class="d-flex align-items-start">
                                                <div class="almacen-icon mr-2 text-center">
                                                    @if(str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'silo'))
                                                        <i class="fas fa-database"></i>
                                                    @elseif(str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'bodega'))
                                                        <i class="fas fa-warehouse"></i>
                                                    @elseif(str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'fría') || str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'frio'))
                                                        <i class="fas fa-snowflake"></i>
                                                    @else
                                                        <i class="fas fa-box"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="almacen-nombre">{{ $almacen->nombre }}</div>
                                                    <div class="almacen-tipo">
                                                        {{ $almacen->tipoAlmacen->nombre ?? 'General' }}
                                                        @if($almacen->ubicacion)
                                                            • {{ $almacen->ubicacion }}
                                                        @endif
                                                    </div>
                                                    <div class="small mt-1">
                                                        <span class="text-success font-weight-bold">{{ number_format($disponible, 0) }}</span>
                                                        <span class="text-muted">/ {{ number_format($almacen->capacidad, 0) }} {{ $almacen->unidadMedida->abreviatura ?? 'kg' }}</span>
                                                    </div>
                                                    <div class="capacidad-bar">
                                                        <div class="fill {{ $fillClass }}" style="width: {{ min($porcentaje, 100) }}%"></div>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <i class="fas fa-check-circle text-success fa-lg" style="display: none;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            No hay almacenes registrados. 
                                            <a href="{{ route('almacenes.create') }}">Crear uno</a>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            <input type="hidden" name="almacenid" id="almacenid" value="">
                        </div>
                    </div>

                    {{-- Observaciones --}}
                    <div class="form-group mt-4">
                        <label><i class="fas fa-comment mr-1"></i> Observaciones</label>
                        <div class="guia-campo mb-2">
                            <strong>Notas libres:</strong> calidad del producto, humedad, daños, clima del día o cualquier detalle para trazabilidad.
                        </div>
                        <textarea name="observaciones" class="form-control" rows="2"
                                  placeholder="Calidad, condiciones de la cosecha, etc...">{{ old('observaciones') }}</textarea>
                    </div>

                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('producciones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" {{ $lotes->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-1"></i> Registrar Cosecha
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    function convertirAKg(cantidad, unidad) {
    unidad = unidad.toLowerCase().trim();

    const factores = {
        'kg': 1,
        'kilogramo': 1,
        'kilogramos': 1,

        'g': 0.001,
        'gramo': 0.001,
        'gramos': 0.001,

        't': 1000,
        'tn': 1000,
        'ton': 1000,
        'tonelada': 1000,
        'toneladas': 1000,

        'qq': 46,
        'quintal': 46,
        'quintales': 46,
    };

    return cantidad * (factores[unidad] || 1);
}

    $(document).ready(function() {

        if ($('#loteid').val()) {
            $('#loteid').trigger('change');
        }

        if ($('.almacen-card').length === 1 && $('#enviarAlmacen').is(':checked')) {
            $('.almacen-card').first().trigger('click');
        }
        
        // Función de recomendación inteligente
        function recomendarAlmacen(cultivo) {
            if (!cultivo) return;
            cultivo = cultivo.toLowerCase();

            // Palabras clave de mapeo (simple)
            const mapeo = {
                'maiz': ['silo', 'grano'],
                'maíz': ['silo', 'grano'],
                'soya': ['silo', 'grano'],
                'trigo': ['silo', 'grano'],
                'arroz': ['silo', 'grano'],
                'papa': ['bodega', 'frio', 'tuberculo'],
                'caña': ['bodega', 'zafra'],
                'fruta': ['frio', 'refrigerado'],
                'cítrico': ['bodega'],
            };

            // Buscar keywords genericas y la clave específica encontrada
            let keywords = [];
            let foundKey = null;

            for (const key in mapeo) {
                if (cultivo.includes(key)) {
                    keywords = mapeo[key];
                    foundKey = key; // Guardamos "caña", "papa", etc.
                    break;
                }
            }

            // Si no hay keywords específicas, usar el nombre del cultivo como fallback
            if (keywords.length === 0) {
                keywords = [cultivo];
            }

            // Filtrar almacenes
            let mejorMatch = null;
            let maxScore = 0;
            
            $('.almacen-card').each(function() {
                const tags = $(this).data('tags'); // ej: "bodega caña zona sur"
                let score = 0;

                // 1. Score por coincidencia de palabras clave genéricas (Bodega, Silo, etc.)
                keywords.forEach(word => {
                    if (tags.includes(word)) score += 2;
                });
                
                // 2. Score masivo por coincidencia de la clave específica (ej: "caña" en "Bodega Caña")
                if (foundKey && tags.includes(foundKey)) {
                    score += 10;
                }
                
                // 3. Score por coincidencia exacta del cultivo completo ("caña de azúcar")
                if (tags.includes(cultivo)) score += 5;

                // Debug para ver qué está pasando (solo visible en consola)
                // console.log(`Almacén: ${$(this).data('nombre')} | Score: ${score}`);

                if (score > 0) {
                    // Resaltar visualmente
                    if (score >= 10) {
                        // Match fuerte
                        $(this).css('border-color', '#2c5530').css('background', '#d4edda'); 
                    } else {
                        // Match débil (posiblemente solo por tipo 'bodega')
                        $(this).css('border-color', '#17a2b8').css('background', '#f0fcff');
                    }
                    
                    // Lógica para elegir el MEJOR, no el primero
                    if (score > maxScore) {
                        maxScore = score;
                        mejorMatch = $(this);
                    }
                } else {
                    $(this).css('border-color', '#dee2e6').css('background', 'white'); // Restaurar
                }
            });

            // Si encontramos un match y NO hay nada seleccionado aun...
            if (mejorMatch && !$('#almacenid').val()) {
                mejorMatch.trigger('click');
                
                // Mostrar notificación toast o pequeño mensaje
                const nombre = mejorMatch.data('nombre');
                
                // Limpiar alertas anteriores
                $('.alert-suggestion').remove();

                $('#almacenOptions').prepend(`
                    <div class="alert alert-info alert-dismissible fade show p-2 small mb-2 alert-suggestion" role="alert">
                        <i class="fas fa-lightbulb mr-1"></i> Sugerencia: <strong>${nombre}</strong> (adecuado para ${cultivo})
                        <button type="button" class="close p-2" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `);
            }
        }

        // Mostrar info del lote y activar recomendación
        $('#loteid').on('change', function() {
            const selected = $(this).find(':selected');
            if (selected.val()) {
                const cultivo = selected.data('cultivo');
                $('#infoCultivo').text(cultivo);
                $('#infoResponsable').text(selected.data('responsable'));
                $('#loteInfo').slideDown();
                
                // Activar "Almacenar cosecha" automáticamente para mejor UX
                if (!$('#enviarAlmacen').is(':checked')) {
                    $('#enviarAlmacen').prop('checked', true).trigger('change');
                }
                
                // Ejecutar recomendación
                recomendarAlmacen(cultivo);
            } else {
                $('#loteInfo').slideUp();
            }
        });

        // Switch de almacén
        $('#enviarAlmacen').on('change', function() {
            if ($(this).is(':checked')) {
                $('#almacenOptions').slideDown();
                $('#almacenSection').addClass('active');
            } else {
                $('#almacenOptions').slideUp();
                $('#almacenSection').removeClass('active');
                $('.almacen-card').removeClass('selected').css('background', 'white').css('border-color', '#dee2e6');
                $('.almacen-card .fa-check-circle').hide();
                $('#almacenid').val('');
                $('.alert-dismissible').remove();
                $('#almacen-seleccionado').html('<i class="fas fa-warehouse mr-1"></i> <strong>Almacén:</strong> ninguno seleccionado');
            }
        });

        // Seleccionar almacén
        $('.almacen-card').on('click', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const disponible = $(this).data('disponible');

            // Limpiar estilos previos
            $('.almacen-card').removeClass('selected').css('background', 'white').css('border-color', '#dee2e6');; 
            $('.almacen-card .fa-check-circle').hide();

            // Seleccionar este
            $(this).addClass('selected');
            $(this).find('.fa-check-circle').show();

            $('#almacenid').val(id);
            $('#almacen-seleccionado').html('<i class="fas fa-warehouse mr-1"></i> <strong>Almacén:</strong> ' + nombre);

            // Verificar capacidad
            const cantidad = parseFloat($('#cantidad').val()) || 0;
            if (cantidad > 0) {
                verificarCapacidad(cantidad, disponible, $(this));
            }
        });

        // Verificar al cambiar cantidad
        $('#cantidad').on('change keyup', function() {
            const cantidad = parseFloat($(this).val()) || 0;
            const almacenCard = $('.almacen-card.selected');
            if (almacenCard.length) {
                const disponible = almacenCard.data('disponible');
                verificarCapacidad(cantidad, disponible, almacenCard);
            }
        });

        function verificarCapacidad(cantidad, disponible, card) {
            const umProduccion = $('#unidadmedidaid option:selected').data('abrev');
            const umAlmacen = card.data('um-almacen');

            const cantidadKg = convertirAKg(cantidad, umProduccion);
            const disponibleKg = convertirAKg(disponible, umAlmacen);

            if (cantidadKg > disponibleKg) {
                // Usar toast o borde rojo en lugar de alert invasivo
                card.css('border-color', '#dc3545');
                if ($('#alertaCapacidad').length === 0) {
                     $('#almacenOptions').prepend(`
                        <div id="alertaCapacidad" class="alert alert-danger p-2 small mb-2">
                             ⚠️ Excede capacidad: ${cantidad} ${umProduccion} > disp. ${disponible} ${umAlmacen}
                        </div>
                    `);
                }
            } else {
                card.css('border-color', '#28a745');
                $('#alertaCapacidad').remove();
            }
        }

        // SMART UNIT CONVERSION v2 (Normalized Logic)
        function checkSmartConversion() {
            const cantidadInput = $('#cantidad');
            const unidadSelect = $('#unidadmedidaid');
            const cantidad = parseFloat(cantidadInput.val()) || 0;
            const unidadOption = unidadSelect.find('option:selected');
            const unidadNombre = unidadOption.text().toLowerCase();
            const unidadAbrev = unidadOption.data('abrev') ? unidadOption.data('abrev').toLowerCase() : '';

            // 1. Normalize to KG
            let cantidadKg = 0;
            // Detectar unidad actual
            if (unidadAbrev === 'kg' || unidadNombre.includes('kilo') || unidadNombre.includes('kg')) {
                cantidadKg = cantidad;
            } else if (unidadAbrev === 'g' || unidadNombre.includes('gramo') || unidadNombre.includes('gr')) {
                cantidadKg = cantidad / 1000;
            } else if (unidadAbrev === 't' || unidadNombre.includes('ton') || unidadNombre.includes('tonelada')) {
                cantidadKg = cantidad * 1000;
            } else if (unidadAbrev === 'lb' || unidadNombre.includes('libra')) {
                cantidadKg = cantidad * 0.453592;
            } else {
                return; // Unidad no soportada para conversión inteligente
            }

            $('#smartConversionAlert').remove();

            // 2. Determine Best Unit
            let target = null;

            // Priority: TON > KG
            if (cantidadKg >= 1000) {
                 // Suggest TON if current is NOT TON
                 if (!unidadNombre.includes('ton') && !unidadAbrev.includes('t')) {
                     target = { text: 'Ton', value: cantidadKg / 1000, keyword: 'ton', abrev: 't' };
                 }
            } 
            else if (cantidadKg >= 1) {
                // Suggest KG if current is NOT KG
                 if (!unidadNombre.includes('kilo') && !unidadNombre.includes('kg') && unidadAbrev !== 'kg') {
                     target = { text: 'Kg', value: cantidadKg, keyword: 'kilo', abrev: 'kg' };
                 }
            }

            if (target) {
                 mostrarSugerenciaConversion(cantidadInput, target.text, target.value, target.keyword, target.abrev);
            }
        }

        function mostrarSugerenciaConversion(inputElement, nuevaUnidadTexto, nuevoValor, keywordNuevaUnidad, nuevaAbrev) {
            // Formatear valor para mostrar (max 2 decimales si es entero, o los necesarios)
            const valorMostrado = Number.isInteger(nuevoValor) ? nuevoValor : nuevoValor.toFixed(3).replace(/\.?0+$/, '');

            const alertHtml = `
                <div id="smartConversionAlert" class="alert alert-warning p-2 mt-2 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 8px; cursor: pointer;">
                    <div>
                        <i class="fas fa-lightbulb text-warning mr-2"></i>
                        <strong>Sugerencia:</strong> ¿Convertir a <strong>${valorMostrado} ${nuevaUnidadTexto}</strong>?
                    </div>
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btnAplicarConversion">
                        Aplicar
                    </button>
                </div>
            `;
            
            inputElement.closest('.form-group').append(alertHtml);

            $('#btnAplicarConversion').on('click', function() {
                // Aplicar valor
                $('#cantidad').val(nuevoValor);
                
                // Buscar y seleccionar la nueva unidad en el select
                let unitFound = false;
                $('#unidadmedidaid option').each(function() {
                    const abrev = $(this).data('abrev') ? $(this).data('abrev').toLowerCase() : '';
                    const text = $(this).text().toLowerCase();
                    
                    // Match robusto
                    if ( (nuevaAbrev && abrev === nuevaAbrev.toLowerCase()) || 
                         (keywordNuevaUnidad && text.includes(keywordNuevaUnidad)) ) {
                        
                        $(this).prop('selected', true);
                        unitFound = true;
                        return false; 
                    }
                });

                if (unitFound) {
                    $('#smartConversionAlert').remove();
                    // Importante: disparar change en AMBOS para actualizar UI dependiente
                    $('#unidadmedidaid').trigger('change');
                    $('#cantidad').trigger('change');
                } else {
                    alert('No se encontró la unidad de medida destino en el sistema.');
                    $('#smartConversionAlert').remove();
                }
            });
        }

        $('#cantidad, #unidadmedidaid').on('change keyup blur', function() {
            checkSmartConversion();
        });
    });
</script>
@endpush