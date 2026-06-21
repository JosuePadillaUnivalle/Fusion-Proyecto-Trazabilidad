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
    .cosecha-estimada-panel {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 1rem 1.1rem;
        margin-top: .75rem;
    }
    .cosecha-estimada-panel__title {
        font-weight: 600;
        color: #166534;
        font-size: .92rem;
        margin-bottom: .65rem;
    }
    .cosecha-estimada-panel__metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
    }
    @media (min-width: 768px) {
        .cosecha-estimada-panel__metrics { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    .cosecha-estimada-metric {
        background: #fff;
        border: 1px solid #dcfce7;
        border-radius: 8px;
        padding: .55rem .65rem;
        text-align: center;
    }
    .cosecha-estimada-metric__value {
        display: block;
        font-weight: 700;
        color: #14532d;
        font-size: 1.05rem;
        line-height: 1.2;
    }
    .cosecha-estimada-metric__label {
        display: block;
        font-size: .72rem;
        color: #64748b;
        margin-top: .15rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .cosecha-estimada-panel__note {
        font-size: .78rem;
        color: #64748b;
        margin: .65rem 0 0;
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
                            <strong>Peso o volumen</strong> obtenido en esta cosecha. Al cambiar la unidad (kg, tonelada, quintal…)
                            la cantidad se convierte automáticamente. Internamente todo se guarda en kilogramos.
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

                        <div id="cosechaEstimadaPanel" class="cosecha-estimada-panel" style="display: none;">
                            <div class="cosecha-estimada-panel__title">
                                <i class="fas fa-chart-line mr-1"></i> Estimación según planificación del lote
                            </div>
                            <div class="cosecha-estimada-panel__metrics">
                                <div class="cosecha-estimada-metric">
                                    <span class="cosecha-estimada-metric__value" id="estKg">—</span>
                                    <span class="cosecha-estimada-metric__label">Kg estimados</span>
                                </div>
                                <div class="cosecha-estimada-metric">
                                    <span class="cosecha-estimada-metric__value" id="estEmpaques">—</span>
                                    <span class="cosecha-estimada-metric__label" id="estEmpaqueLabel">Cajas</span>
                                </div>
                                <div class="cosecha-estimada-metric">
                                    <span class="cosecha-estimada-metric__value" id="estUnidades">—</span>
                                    <span class="cosecha-estimada-metric__label">Unidades totales</span>
                                </div>
                                <div class="cosecha-estimada-metric">
                                    <span class="cosecha-estimada-metric__value" id="estUnidadesCaja">—</span>
                                    <span class="cosecha-estimada-metric__label">Unidades por caja</span>
                                </div>
                            </div>
                            <p class="cosecha-estimada-panel__note mb-0" id="estCalibreNote" style="display: none;"></p>
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
                        'almacenRequerido' => true,
                        'guiaTexto' => 'Seleccione el almacén agrícola donde ingresará la producción cosechada. El sistema valida que haya espacio disponible.',
                        'instruccion' => 'Elija el almacén de destino para esta cosecha',
                    ])
                    @error('almacenid')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                    @enderror

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
    'requiereAlmacen' => true,
])
<script>
    $(document).ready(function() {
        const wrapLoteProd = document.getElementById('selector_wrap_produccion_lote');
        const estimacionesPorLote = @json($estimacionesPorLote ?? []);
        const cantidadInicial = @json(old('cantidad'));

        function fmtNum(n, dec) {
            return Number(n || 0).toLocaleString('es-BO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: dec ?? 0
            });
        }

        let unidadAnteriorId = $('#unidadmedidaid').val();
        let convirtiendoUnidad = false;

        function factorUnidadAKg(option) {
            const abrev = String($(option).data('abrev') || '').toLowerCase();
            const text = $(option).text().toLowerCase();

            if (abrev === 'kg' || text.includes('kilo') || text.includes('kg')) return 1;
            if (abrev === 'g' || text.includes('gramo') || abrev === 'gr') return 0.001;
            if (abrev === 't' || text.includes('ton')) return 1000;
            if (abrev === 'qq' || text.includes('quintal')) return 46;
            if (abrev === 'lb' || text.includes('libra')) return 0.453592;

            return 1;
        }

        function formatearCantidadConvertida(valor) {
            if (!Number.isFinite(valor) || valor <= 0) return '';
            const redondeado = Math.round(valor * 10000) / 10000;

            return Number.isInteger(redondeado)
                ? String(redondeado)
                : String(parseFloat(redondeado.toFixed(4)));
        }

        function seleccionarUnidadKgSinConversion(cantidadKg) {
            convirtiendoUnidad = true;
            $('#unidadmedidaid option').each(function() {
                const abrev = ($(this).data('abrev') || '').toLowerCase();
                if (abrev === 'kg') {
                    $(this).prop('selected', true);
                    unidadAnteriorId = $(this).val();
                    return false;
                }
            });
            $('#cantidad').val(formatearCantidadConvertida(cantidadKg));
            convirtiendoUnidad = false;
        }

        function aplicarEstimacionCosecha(est, autocompletarCantidad) {
            const panel = $('#cosechaEstimadaPanel');
            if (!est || !est.kg_cosecha_estimados) {
                panel.slideUp();
                return;
            }

            $('#estKg').text(fmtNum(est.kg_cosecha_estimados, 0));
            $('#estEmpaques').text(fmtNum(est.empaques_estimados, 0));
            $('#estEmpaqueLabel').text(est.empaque_label || 'Cajas');
            $('#estUnidades').text(fmtNum(est.unidades_estimadas, 0));
            $('#estUnidadesCaja').text(est.unidades_por_caja ? fmtNum(est.unidades_por_caja, 0) : '—');

            if (est.calibre_nombre) {
                $('#estCalibreNote').html(
                    '<i class="fas fa-balance-scale-left mr-1"></i> Calibre: <strong>' + est.calibre_nombre + '</strong>'
                ).show();
            } else {
                $('#estCalibreNote').hide();
            }

            panel.slideDown();

            if (autocompletarCantidad && !cantidadInicial) {
                seleccionarUnidadKgSinConversion(est.kg_cosecha_estimados);
                if (window.AlmacenEnvio && typeof window.AlmacenEnvio.actualizarPreview === 'function') {
                    window.AlmacenEnvio.actualizarPreview();
                }
            }
        }

        function onLoteProduccionSeleccionado(extra, loteId) {
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

            const est = extra?.estimacion_cosecha
                || (loteId ? estimacionesPorLote[loteId] : null)
                || null;
            aplicarEstimacionCosecha(est, true);
        }

        wrapLoteProd?.addEventListener('selector-catalogo:change', function (e) {
            onLoteProduccionSeleccionado(e.detail.extra || {}, e.detail.id || null);
        });

        @if($lotePreseleccionado && $lotes->isNotEmpty())
            @php $loteIni = $lotes->firstWhere('loteid', $lotePreseleccionado); @endphp
            @if($loteIni)
            onLoteProduccionSeleccionado({
                cultivo: @json($loteIni->cultivo->nombre ?? 'Sin cultivo'),
                responsable: @json(trim(($loteIni->usuario->nombre ?? '').' '.($loteIni->usuario->apellido ?? ''))),
                estimacion_cosecha: @json($estimacionesPorLote[$lotePreseleccionado] ?? null),
            }, @json($lotePreseleccionado));
            @endif
        @endif

        function convertirCantidadAlCambiarUnidad() {
            if (convirtiendoUnidad) return;

            const unidadSelect = $('#unidadmedidaid');
            const nuevaId = unidadSelect.val();
            const cantidad = parseFloat($('#cantidad').val());

            if (!nuevaId || nuevaId === unidadAnteriorId || !cantidad || cantidad <= 0) {
                unidadAnteriorId = nuevaId;
                return;
            }

            const optAnterior = unidadSelect.find('option[value="' + unidadAnteriorId + '"]');
            const optNueva = unidadSelect.find('option:selected');
            const kg = cantidad * factorUnidadAKg(optAnterior);
            const nuevaCantidad = kg / factorUnidadAKg(optNueva);

            convirtiendoUnidad = true;
            $('#cantidad').val(formatearCantidadConvertida(nuevaCantidad));
            convirtiendoUnidad = false;
            unidadAnteriorId = nuevaId;
            if (window.AlmacenEnvio && typeof window.AlmacenEnvio.actualizarPreview === 'function') {
                window.AlmacenEnvio.actualizarPreview();
            }
        }

        $('#unidadmedidaid').on('change', convertirCantidadAlCambiarUnidad);
    });
</script>
@endpush