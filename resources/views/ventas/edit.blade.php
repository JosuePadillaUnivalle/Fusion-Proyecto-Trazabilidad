@extends('layouts.app')

@section('title', 'Editar Venta | AgroNexus')
@section('page_title', 'Editar Venta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}" style="color: #2c5530;">Ventas</a></li>
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
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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

    .total-preview {
        background: #e8f5e9;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }

    .total-preview .amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #28a745;
    }

    .total-preview .label {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="form-header">
    <h2><i class="fas fa-edit mr-2"></i>Editar Venta</h2>
    <p class="mb-0 mt-2" style="opacity: 0.9;">
        <i class="fas fa-user mr-1"></i> {{ $venta->cliente ?? 'Cliente no especificado' }}
    </p>
</div>

<form action="{{ route('ventas.update', $venta) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-9 col-md-8">
            <!-- Información del Cliente -->
            <div class="form-card">
                <h5><i class="fas fa-user mr-2"></i>Información del Cliente</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field"><i class="fas fa-user mr-1"></i> Cliente</label>
                            <input type="text" name="cliente" class="form-control" value="{{ $venta->cliente }}" maxlength="100" placeholder="Nombre del cliente" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field"><i class="fas fa-calendar mr-1"></i> Fecha de Venta</label>
                            <input type="date" name="fechaventa" class="form-control" value="{{ $venta->fechaventa ? \Carbon\Carbon::parse($venta->fechaventa)->format('Y-m-d') : '' }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producción y Cantidad -->
            <div class="form-card">
                <h5><i class="fas fa-boxes mr-2"></i>Producción y Cantidad</h5>
                
                <div class="form-group">
                    <label class="required-field"><i class="fas fa-tractor mr-1"></i> Producción</label>
                    <select name="produccionid" class="form-control" required>
                        <option value="">Seleccionar producción...</option>
                        @foreach($producciones as $p)
                            <option value="{{ $p->produccionid }}" {{ $p->produccionid == $venta->produccionid ? 'selected' : '' }}>
                                {{ $p->lote->nombre ?? 'Lote' }}
                                @if($p->lote && $p->lote->cultivo)
                                    - {{ $p->lote->cultivo->nombre }}
                                @endif
                                ({{ number_format($p->cantidad ?? 0, 2) }} kg disponibles)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field"><i class="fas fa-weight-hanging mr-1"></i> Cantidad</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="cantidad" id="cantidad" class="form-control" min="0.01" value="{{ $venta->cantidad }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ $venta->unidadMedida->abreviatura ?? 'kg' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field"><i class="fas fa-tag mr-1"></i> Precio Unitario</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="number" step="0.01" name="preciounitario" id="preciounitario" class="form-control" min="0.01" value="{{ $venta->preciounitario }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="form-card">
                <h5><i class="fas fa-comment mr-2"></i>Observaciones</h5>
                
                <div class="form-group mb-0">
                    <textarea name="observaciones" class="form-control" rows="3" maxlength="200" placeholder="Notas adicionales sobre la venta...">{{ $venta->observaciones }}</textarea>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-3 col-md-4">
            <!-- Vista Previa del Total -->
            <div class="form-card">
                <h5><i class="fas fa-calculator mr-2"></i>Total</h5>
                
                <div class="total-preview">
                    <div class="label">Total de la Venta</div>
                    <div class="amount" id="total-preview">Bs. {{ number_format(($venta->cantidad ?? 0) * ($venta->preciounitario ?? 0), 2) }}</div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="form-card">
                <h5><i class="fas fa-cogs mr-2"></i>Acciones</h5>
                
                <button type="submit" class="btn btn-success btn-block btn-action mb-2">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios
                </button>
                
                <a href="{{ route('ventas.show', $venta) }}" class="btn btn-info btn-block btn-action mb-2">
                    <i class="fas fa-eye mr-1"></i> Ver Detalle
                </a>
                
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-block btn-action">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
            </div>

            <!-- Info -->
            <div class="form-card">
                <h5><i class="fas fa-info-circle mr-2"></i>Información</h5>
                <small class="text-muted">
                    <p><strong>ID:</strong> #{{ $venta->ventaid }}</p>
                    <p class="mb-0"><i class="fas fa-asterisk text-danger"></i> Campos obligatorios</p>
                </small>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function calcularTotal() {
        var cantidad = parseFloat($('#cantidad').val()) || 0;
        var precio = parseFloat($('#preciounitario').val()) || 0;
        var total = cantidad * precio;
        $('#total-preview').text('Bs. ' + total.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $('#cantidad, #preciounitario').on('input', calcularTotal);
});
</script>
@endpush