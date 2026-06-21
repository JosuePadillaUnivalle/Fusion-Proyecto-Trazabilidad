@extends('layouts.app')

@section('title', 'Editar cosecha | AgroFusion')
@section('page_title', 'Editar Cosecha')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('producciones.index') }}">Registro de Cosechas</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .form-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 20px;
        }

        .form-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f4;
        }

        .form-group label {
            font-weight: 600;
            color: #1a252f;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #dee2e6;
            padding: 12px 15px;
            transition: border-color 0.2s ease;
            height: auto;
            min-height: 46px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 85, 48, 0.15);
        }

        select.form-control {
            padding-right: 35px;
            background-position: right 12px center;
        }

        .input-group-text {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            min-height: 46px;
        }

        .btn-action {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .cantidad-preview {
            background: #e8f5e9;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .cantidad-preview .amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #28a745;
        }

        .cantidad-preview .label {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <!-- Header -->
    <div class="form-header">
        <h2><i class="fas fa-edit mr-2"></i>Editar Cosecha</h2>
        <p class="mb-0 mt-2" style="opacity: 0.9;">
            <i class="fas fa-map-marker-alt mr-1"></i> {{ $produccion->lote->nombre ?? 'Lote' }}
            @if($produccion->lote && $produccion->lote->cultivo)
                - {{ $produccion->lote->cultivo->nombre }}
            @endif
        </p>
    </div>

    <form action="{{ route('producciones.update', $produccion) }}" method="POST">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="row">
            <div class="col-lg-9 col-md-8">
                <!-- Información del Lote -->
                <div class="form-card">
                    <h5><i class="fas fa-map-marked-alt mr-2"></i>Lote y Cultivo</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field"><i class="fas fa-map-marker-alt mr-1"></i> Lote</label>
                                <select name="loteid" class="form-control" required>
                                    <option value="">Seleccionar lote...</option>
                                    @foreach($lotes as $l)
                                        <option value="{{ $l->loteid }}" {{ $l->loteid == $produccion->loteid ? 'selected' : '' }}>
                                            {{ $l->nombre }}
                                            @if($l->cultivo)
                                                - {{ $l->cultivo->nombre }}
                                            @endif
                                            (@superficie($l->superficie))
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field"><i class="fas fa-calendar mr-1"></i> Fecha de Cosecha</label>
                                <input type="date" name="fechacosecha" class="form-control"
                                    value="{{ $produccion->fechacosecha ? \Carbon\Carbon::parse($produccion->fechacosecha)->format('Y-m-d') : '' }}"
                                    required>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Cantidad y Destino -->
                <div class="form-card">
                    <h5><i class="fas fa-weight-hanging mr-2"></i>Cantidad y Destino</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required-field"><i class="fas fa-balance-scale mr-1"></i> Cantidad</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="cantidad" id="cantidad" class="form-control"
                                        min="0.01" value="{{ $produccion->cantidad }}" required>
                                    <div class="input-group-append">
                                        <span
                                            class="input-group-text">{{ $produccion->unidadMedida->abreviatura ?? 'kg' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="required-field"><i class="fas fa-ruler mr-1"></i> Unidad de Medida</label>
                                <select name="unidadmedidaid" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->unidadmedidaid }}" {{ $produccion->unidadmedidaid == $u->unidadmedidaid ? 'selected' : '' }}>
                                            {{ $u->nombre }} ({{ $u->abreviatura }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label @class(['required-field' => $puedeEnviarAlmacen && ! $esNoConforme])>
                                    <i class="fas fa-warehouse mr-1"></i> Almacén
                                </label>
                                <select name="almacenid" class="form-control" @disabled($esNoConforme) @required($puedeEnviarAlmacen && ! $esNoConforme)>
                                    <option value="">Seleccionar almacén...</option>
                                    @foreach($almacenes as $a)
                                        @php
                                            $resumen = $resumenesAlmacen[$a->almacenid] ?? ['disponible_kg' => 0];
                                            $disponible = number_format($resumen['disponible_kg'], 0);
                                            $um = $a->unidadMedida->abreviatura ?? 'kg';
                                        @endphp
                                        <option value="{{ $a->almacenid }}" @selected((string) old('almacenid', $almacenActualId) === (string) $a->almacenid)>
                                            {{ $a->nombre }} ({{ $disponible }} {{ $um }} disponibles)
                                        </option>
                                    @endforeach
                                </select>
                                @if($esNoConforme)
                                    <small class="text-danger d-block mt-1">
                                        <i class="fas fa-ban mr-1"></i>{{ $mensajeBloqueoAlmacen }}
                                    </small>
                                @elseif(! $puedeEnviarAlmacen)
                                    <div class="alert alert-light border small mt-2 mb-0">
                                        <i class="fas fa-certificate text-success mr-1"></i>
                                        El envío al almacén requiere que el lote esté <strong>certificado</strong> en Certificaciones.
                                        Puede guardar la cosecha sin almacén y almacenar después de certificar.
                                    </div>
                                @else
                                    <small class="text-muted">Indica dónde está almacenada esta cosecha. Puede moverla a otro almacén agrícola.</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-3 col-md-4">
                <!-- Vista Previa -->
                <div class="form-card">
                    <h5><i class="fas fa-eye mr-2"></i>Vista Previa</h5>

                    <div class="cantidad-preview">
                        <div class="label">Cantidad</div>
                        <div class="amount" id="cantidad-preview">{{ number_format($produccion->cantidad ?? 0, 2) }} kg
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $produccion->fechacosecha ? \Carbon\Carbon::parse($produccion->fechacosecha)->format('d/m/Y') : '-' }}
                        </small>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="form-card">
                    <h5><i class="fas fa-cogs mr-2"></i>Acciones</h5>

                    <button type="submit" class="btn btn-success btn-block btn-action mb-2">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>

                    <a href="{{ route('producciones.show', $produccion) }}" class="btn btn-info btn-block btn-action mb-2">
                        <i class="fas fa-eye mr-1"></i> Ver Detalle
                    </a>

                    <a href="{{ route('producciones.index') }}" class="btn btn-secondary btn-block btn-action">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>

                <!-- Info -->
                <div class="form-card">
                    <h5><i class="fas fa-info-circle mr-2"></i>Información</h5>
                    <small class="text-muted">
                        <p><i class="fas fa-asterisk text-danger"></i> Campos obligatorios</p>
                        <p class="mb-0">Los cambios en la cantidad pueden afectar el inventario disponible en almacén.</p>
                    </small>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#cantidad').on('input', function () {
                var cantidad = parseFloat($(this).val()) || 0;
                $('#cantidad-preview').text(cantidad.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kg');
                checkSmartConversion();
            });

            $('#unidadmedidaid').on('change', function () {
                checkSmartConversion();
            });

            // SMART UNIT CONVERSION
            function checkSmartConversion() {
                const cantidadInput = $('#cantidad');
                const unidadSelect = $('select[name="unidadmedidaid"]');
                const cantidad = parseFloat(cantidadInput.val()) || 0;
                const unidadOption = unidadSelect.find('option:selected');
                const unidadNombre = unidadOption.text().toLowerCase();

                // Limpiar sugerencias
                $('#smartConversionAlert').remove();

                // KG -> TON
                if (unidadNombre.includes('kilo') || unidadNombre.includes('kg')) {
                    if (cantidad >= 1000) {
                        const toneladas = cantidad / 1000;
                        mostrarSugerenciaConversion(cantidadInput, 'Ton', toneladas, 'tonelada');
                    }
                }
                // GRAMOS -> KG
                else if (unidadNombre.includes('gramo') || unidadNombre.includes(' gr')) {
                    if (cantidad >= 1000) {
                        const kilos = cantidad / 1000;
                        mostrarSugerenciaConversion(cantidadInput, 'Kg', kilos, 'kilo');
                    }
                }
            }

            function mostrarSugerenciaConversion(inputElement, nuevaUnidadTexto, nuevoValor, keywordNuevaUnidad) {
                const alertHtml = `
                <div id="smartConversionAlert" class="alert alert-info p-2 mt-2 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: 8px;">
                    <div>
                        <i class="fas fa-lightbulb text-info mr-2"></i>
                        <strong>Sugerencia:</strong> ¿Convertir a <strong>${nuevoValor} ${nuevaUnidadTexto}</strong>?
                    </div>
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" id="btnAplicarConversion">
                        Sí, cambiar
                    </button>
                </div>
            `;

                if ($('#smartConversionAlert').length === 0) {
                    inputElement.closest('.form-group').append(alertHtml);
                }

                $('#btnAplicarConversion').on('click', function (e) {
                    e.preventDefault();
                    $('#cantidad').val(nuevoValor);

                    // Buscar y seleccionar nueva unidad
                    $('select[name="unidadmedidaid"] option').each(function () {
                        const text = $(this).text().toLowerCase();
                        if (text.includes(keywordNuevaUnidad)) {
                            $(this).prop('selected', true);
                            return false;
                        }
                    });

                    $('#smartConversionAlert').remove();
                    $('#cantidad').trigger('input'); // Actualizar preview
                });
            }
        });
    </script>
@endpush