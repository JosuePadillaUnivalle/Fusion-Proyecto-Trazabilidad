@extends('layouts.app')

@section('title', 'Nueva aplicación de insumo | AgroFusion')
@section('page_title', 'Nueva aplicación de insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lote-insumos.index') }}">Aplicación de insumos</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')
<style>
.page-aplicacion-form .form-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 14px rgba(0,0,0,.08);
}
.page-aplicacion-form .form-card .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px 12px 0 0 !important;
}
.page-aplicacion-form .guia-campo {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.55rem 0.8rem;
    font-size: 0.84rem;
    color: #495057;
    margin-bottom: 0.75rem;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-aplicacion-form">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card card-modulo-main">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-flask mr-2"></i>Nueva aplicación de insumo
                    </h3>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger m-3 mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('lote-insumos.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="guia-campo">
                            <i class="fas fa-info-circle text-success mr-1"></i>
                            Registra qué insumo se aplicó en un lote. La fecha y el responsable se asignan automáticamente; el stock se descuenta al guardar.
                        </div>

                        @include('partials.selector-catalogo', [
                            'id' => 'lote_insumo_lote',
                            'name' => 'loteid',
                            'label' => 'Lote',
                            'icon' => 'fa-map-marked-alt',
                            'value' => old('loteid'),
                            'labelSelected' => $loteLabel ?? '',
                            'endpoint' => route('catalogo-selector.lotes'),
                            'title' => 'Seleccionar lote',
                            'searchPlaceholder' => 'Nombre, código o ubicación…',
                            'required' => true,
                        ])

                        <div class="form-group">
                            <label class="text-muted small mb-1">Responsable del lote</label>
                            <input type="text" id="responsable_display" class="form-control bg-light" readonly
                                   placeholder="Se muestra al elegir el lote">
                        </div>

                        @include('partials.selector-catalogo', [
                            'id' => 'lote_insumo_insumo',
                            'name' => 'insumoid',
                            'label' => 'Insumo',
                            'icon' => 'fa-boxes',
                            'value' => old('insumoid'),
                            'labelSelected' => $insumoLabel ?? '',
                            'endpoint' => route('catalogo-selector.insumos'),
                            'params' => ['solo_con_stock' => '1'],
                            'title' => 'Seleccionar insumo',
                            'searchPlaceholder' => 'Nombre del insumo…',
                            'required' => true,
                        ])

                        <div class="form-group">
                            <label><i class="fas fa-balance-scale mr-1"></i> Cantidad a aplicar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="cantidadusada" id="cantidadusada"
                                       class="form-control" min="0.01" required value="{{ old('cantidadusada') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="unidad_display">ud</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Disponible: <span id="stock_display" class="font-weight-bold">—</span>
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <label><i class="fas fa-comment mr-1"></i> Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                      maxlength="200" placeholder="Opcional…">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between">
                        <a href="{{ route('lote-insumos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Registrar aplicación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selector_wrap_lote_insumo_lote')?.addEventListener('selector-catalogo:change', function (e) {
    const extra = e.detail.extra || {};
    document.getElementById('responsable_display').value = extra.responsable || 'Sin responsable asignado';
});

document.getElementById('selector_wrap_lote_insumo_insumo')?.addEventListener('selector-catalogo:change', function (e) {
    const extra = e.detail.extra || {};
    const stock = extra.stock ?? 0;
    const unidad = extra.unidad || 'ud';
    document.getElementById('stock_display').textContent = stock + ' ' + unidad;
    document.getElementById('unidad_display').textContent = unidad;
    document.getElementById('cantidadusada').setAttribute('max', stock);
});
</script>
@endpush
