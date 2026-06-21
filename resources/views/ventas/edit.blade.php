@extends('layouts.app')

@section('title', 'Editar Venta | Fusion-Proyectos')
@section('page_title', 'Editar Venta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ventas.show', $venta) }}">#{{ $venta->ventaid }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('partials.modulo-ventas-styles')
@endpush

@section('content')
<div class="modulo-ven">

    <div class="ven-page-header edit">
        <div class="ven-page-header-inner">
            <div class="ven-page-header-icon"><i class="fas fa-edit"></i></div>
            <div class="ven-page-header-text">
                <h2>Editar venta #{{ $venta->ventaid }}</h2>
                <p>Al guardar se ajusta el stock del almacén según los cambios de cantidad o producción.</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('ventas.update', $venta) }}" method="POST">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-outline card-warning card-form-modulo card-edit elevation-1">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-edit mr-1"></i> Datos de la venta</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-user mr-1"></i> Cliente <span class="text-danger">*</span></label>
                                <input type="text" name="cliente" class="form-control" value="{{ old('cliente', $venta->cliente) }}" maxlength="100" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-calendar mr-1"></i> Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fechaventa" class="form-control"
                                    value="{{ old('fechaventa', $venta->fechaventa ? \Carbon\Carbon::parse($venta->fechaventa)->format('Y-m-d') : '') }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-boxes mr-1"></i> Producción <span class="text-danger">*</span></label>
                            <select name="produccionid" class="form-control" required>
                                @foreach($producciones as $p)
                                <option value="{{ $p->produccionid }}" @selected(old('produccionid', $venta->produccionid) == $p->produccionid)>
                                    {{ $p->lote->nombre ?? 'Lote' }}
                                    @if($p->lote?->cultivo) — {{ $p->lote->cultivo->nombre }} @endif
                                    ({{ number_format($p->cantidad ?? 0, 2) }} kg)
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-balance-scale mr-1"></i> Cantidad <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cantidad" id="cantidad" class="form-control" min="0.01"
                                    value="{{ old('cantidad', $venta->cantidad) }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold"><i class="fas fa-tag mr-1"></i> Precio unitario <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
                                    <input type="number" step="0.01" name="preciounitario" id="preciounitario" class="form-control" min="0"
                                        value="{{ old('preciounitario', $venta->preciounitario) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold"><i class="fas fa-ruler mr-1"></i> Unidad</label>
                            <select name="unidadmedidaid" class="form-control" required>
                                @foreach($unidades as $u)
                                <option value="{{ $u->unidadmedidaid }}" @selected(old('unidadmedidaid', $venta->unidadmedidaid) == $u->unidadmedidaid)>{{ $u->nombre }} ({{ $u->abreviatura }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-3 mb-0">
                            <label class="font-weight-bold"><i class="fas fa-comment mr-1"></i> Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" maxlength="200">{{ old('observaciones', $venta->observaciones) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('ventas.show', $venta) }}" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> Cancelar</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-lateral">
                    <div class="panel-header"><i class="fas fa-calculator mr-1"></i> Total</div>
                    <div class="panel-body">
                        <div class="total-estimado-box">
                            <small class="text-muted d-block mb-1">Total de la venta</small>
                            <div class="amount" id="total-preview">Bs. {{ number_format(($venta->cantidad ?? 0) * ($venta->preciounitario ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="guia-venta">
                    <strong><i class="fas fa-info-circle mr-1"></i> Importante</strong><br>
                    Si cambias la cantidad, el sistema devuelve el stock anterior y descuenta la nueva cantidad del almacén vinculado a la producción.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function calcularTotal() {
        var c = parseFloat($('#cantidad').val()) || 0;
        var p = parseFloat($('#preciounitario').val()) || 0;
        $('#total-preview').text('Bs. ' + (c * p).toFixed(2));
    }
    $('#cantidad, #preciounitario').on('input', calcularTotal);
});
</script>
@endpush
