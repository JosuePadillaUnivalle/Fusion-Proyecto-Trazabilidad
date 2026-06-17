@extends('layouts.app')

@section('title', 'Registrar cosecha | AgroFusion')
@section('page_title', 'Registrar Cosecha')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('producciones.index') }}">Registro de Cosechas</a></li>
    <li class="breadcrumb-item active">Registrar cosecha</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('partials.almacen-envio-styles')
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
    .almacen-section { margin-top: 20px; }
    .cantidad-cosecha-row { align-items: flex-end; }
    .cantidad-cosecha-row .col-md-4 label {
        font-size: .9rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: .4rem;
    }
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

            <form action="{{ route('producciones.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(!empty($returnUrl))
                    <input type="hidden" name="return" value="{{ $returnUrl }}">
                @endif
                <div class="card-body">
                    
                    {{-- Lote --}}
                    <div class="form-group">
                        <label><i class="fas fa-map-marked-alt mr-1 text-success"></i> Lote a cosechar <span class="text-danger">*</span></label>
                        <div class="guia-campo mb-2">
                            <strong>¿Para qué sirve?</strong> Identifica el lote cuyas actividades de crecimiento ya están completas
                            (riego, control de plagas y fertilización) o que está en estado <em>listo para cosecha</em>.
                        </div>
                        @include('partials.selector-catalogo', [
                            'id' => 'produccion_lote',
                            'name' => 'loteid',
                            'value' => $lotePreseleccionado ?? '',
                            'labelSelected' => $lotePreseleccionadoLabel ?? '',
                            'endpoint' => route('catalogo-selector.lotes'),
                            'params' => ['solo_cosecha' => '1'],
                            'title' => 'Seleccionar lote listo para cosecha',
                            'searchPlaceholder' => 'Nombre, código TRAZ o ubicación…',
                            'inputGroup' => true,
                            'required' => true,
                        ])
                        @if($lotes->isEmpty())
                            <small class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle"></i> No hay lotes listos para cosechar.
                                Completa las actividades de crecimiento en <a href="{{ route('lotes.index') }}">Gestión de lotes</a> antes de registrar la cosecha.
                            </small>
                        @else
                            <small class="form-text text-muted">
                                {{ $lotes->count() === 1 ? 'Un solo lote disponible — ya está preseleccionado.' : 'Lotes con actividades de crecimiento completas o listos para cosecha.' }}
                            </small>
                        @endif
                    </div>

                    <div id="loteInfo" class="info-panel mb-3" style="{{ ($lotePreseleccionado ?? null) ? '' : 'display: none;' }}">
                        <strong><i class="fas fa-leaf mr-1 text-success"></i> Cultivo:</strong> <span id="infoCultivo"></span>
                        <span class="mx-2">|</span>
                        <strong><i class="fas fa-user mr-1"></i> Responsable:</strong> <span id="infoResponsable"></span>
                    </div>

                    {{-- Cantidad --}}
                    <div class="form-group">
                        <label><i class="fas fa-balance-scale mr-1 text-success"></i> Cantidad cosechada <span class="text-danger">*</span></label>
                        <div class="guia-campo mb-2">
                            <strong>Peso o volumen</strong> obtenido en esta cosecha. El sistema convierte automáticamente a kilogramos para inventario y almacén.
                        </div>
                        <div class="row cantidad-cosecha-row">
                            <div class="col-md-8">
                                <input type="number" step="0.01" name="cantidad" id="cantidad"
                                       class="form-control" min="0.01" required value="{{ old('cantidad') }}"
                                       placeholder="Ej: 500">
                            </div>
                            <div class="col-md-4">
                                <label class="d-block">Unidad <span class="text-danger">*</span></label>
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

                    @include('partials.almacen-envio-selector', [
                        'almacenes' => $almacenes,
                        'almacenesTodos' => $almacenesTodos ?? collect(),
                        'resumenesCapacidad' => $resumenesCapacidad ?? [],
                        'sectionId' => 'almacenSection',
                        'hiddenInputId' => 'almacenid',
                        'selectedAlmacenId' => old('almacenid'),
                        'etiquetaAmbito' => 'agrícola',
                        'almacenRequerido' => false,
                        'guiaTexto' => 'Puede registrar la cosecha sin almacén. Después de certificar el lote en Certificaciones, envíelo al almacén desde la trazabilidad del lote.',
                        'instruccion' => 'Si el lote ya está certificado, puede elegir ahora el almacén agrícola de destino',
                    ])
                    <div class="alert alert-light border small mt-2 mb-0">
                        <i class="fas fa-route text-success mr-1"></i>
                        El flujo es: <strong>cosecha → certificación → envío al almacén</strong>.
                        No necesita almacén ni certificación para registrar la cosecha.
                    </div>

                    {{-- Evidencia fotográfica --}}
                    <div class="form-group mt-4">
                        <label><i class="fas fa-camera mr-1 text-success"></i> Foto de la cosecha <span class="text-danger">*</span></label>
                        <div class="guia-campo mb-2">
                            <strong>Obligatorio.</strong> Suba una imagen que demuestre la cosecha realizada (producto cosechado, campo, etc.).
                        </div>
                        @include('partials.upload-evidencia-foto', [
                            'inputId' => 'cosechaEvidenciaFoto',
                            'inputName' => 'evidencia_foto',
                            'btnLabel' => 'Elegir imagen',
                            'required' => true,
                        ])
                        @error('evidencia_foto')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
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
                        <a href="{{ $returnUrl ?? route('producciones.index') }}" class="btn btn-secondary">
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
@include('partials.almacen-envio-scripts', [
    'sectionId' => 'almacenSection',
    'hiddenInputId' => 'almacenid',
    'formSelector' => 'form[action*="producciones"]',
    'almacenesCatalogo' => $almacenesCatalogo ?? [],
    'requiereAlmacen' => false,
])
<script>
    $(document).ready(function() {
        const wrapLoteProd = document.getElementById('selector_wrap_produccion_lote');

        function onLoteProduccionSeleccionado(extra) {
            if (extra && (extra.cultivo || extra.responsable)) {
                $('#infoCultivo').text(extra.cultivo || '—');
                $('#infoResponsable').text(extra.responsable || '—');
                $('#loteInfo').slideDown();
                if (window.AlmacenEnvio && typeof window.AlmacenEnvio.recomendar === 'function') {
                    window.AlmacenEnvio.recomendar(extra.cultivo || '');
                }
            } else {
                $('#loteInfo').slideUp();
            }
        }

        wrapLoteProd?.addEventListener('selector-catalogo:change', function (e) {
            onLoteProduccionSeleccionado(e.detail.extra || {});
        });

        @if($lotePreseleccionado && $lotes->isNotEmpty())
            @php $loteIni = $lotes->firstWhere('loteid', $lotePreseleccionado); @endphp
            @if($loteIni)
            onLoteProduccionSeleccionado({
                cultivo: @json($loteIni->cultivo->nombre ?? 'Sin cultivo'),
                responsable: @json(trim(($loteIni->usuario->nombre ?? '').' '.($loteIni->usuario->apellido ?? ''))),
            });
            @endif
        @endif

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