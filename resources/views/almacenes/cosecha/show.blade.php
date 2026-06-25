@php
    $rutaPrefijo = $rutaPrefijo ?? 'almacen-planta';
@endphp

@extends('layouts.app')

@section('title', $nombre.' | Cosecha en almacén')
@section('page_title', 'Detalle de cosecha')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route($rutaPrefijo.'.index') }}">Almacenes</a></li>
    <li class="breadcrumb-item"><a href="{{ route($rutaPrefijo.'.show', $almacen) }}">{{ $almacen->nombre }}</a></li>
    <li class="breadcrumb-item active">{{ $nombre }}</li>
@endsection

@push('styles')
<style>
.page-cosecha-detalle .contenido-acciones {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.25rem;
    justify-content: center;
}
.page-cosecha-detalle .contenido-acciones .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<section class="content page-cosecha-detalle">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card card-outline card-success elevation-1 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 font-weight-bold text-success">
                        <i class="fas fa-seedling mr-1"></i> {{ $nombre }}
                    </h5>
                    <p class="mb-0 small text-muted">Stock consolidado en {{ $almacen->nombre }}</p>
                </div>
                <div class="text-right">
                    <div class="h4 mb-0 font-weight-bold text-success">{{ number_format($kgTotal, 2) }} kg</div>
                    <small class="text-muted">{{ $lineas->count() }} {{ $lineas->count() === 1 ? 'entrada' : 'entradas' }}</small>
                </div>
            </div>
        </div>

        <div class="card card-outline card-success elevation-1" id="gestionar">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 font-weight-bold">Historial de entradas</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-modulo table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Presentación</th>
                            <th class="text-right">Cantidad</th>
                            <th class="text-right">Kg</th>
                            <th>Origen</th>
                            <th>Procedencia</th>
                            <th class="text-center text-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lineas as $linea)
                            <tr>
                                <td class="text-nowrap">{{ $linea['fecha']?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    <strong>{{ $linea['titulo'] }}</strong>
                                    <div class="small text-muted">
                                        {{ $linea['tipo'] === 'recepcion_pedido' ? 'Recepción de pedido' : 'Cosecha de campo' }}
                                    </div>
                                </td>
                                <td class="text-right">
                                    @php
                                        $esUnidadLinea = ! str_contains(strtolower($linea['unidad'] ?? ''), 'kg');
                                    @endphp
                                    {{ number_format($linea['cantidad'], $esUnidadLinea ? 0 : 2) }}
                                    <small class="text-muted">{{ $linea['unidad'] }}</small>
                                </td>
                                <td class="text-right">{{ number_format($linea['kg'], 2) }} kg</td>
                                <td>
                                    @if(! empty($linea['url_origen']))
                                        <a href="{{ $linea['url_origen'] }}">{{ $linea['origen_etiqueta'] }}</a>
                                    @else
                                        {{ $linea['origen_etiqueta'] }}
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $linea['origen_detalle'] }}</td>
                                <td class="text-center text-nowrap">
                                    <div class="contenido-acciones">
                                        @if(! empty($linea['url_origen']))
                                            <a href="{{ $linea['url_origen'] }}" class="btn btn-sm btn-outline-info" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                        @endif
                                        @if(! empty($linea['url_edit']))
                                            @can('inventario.update')
                                            <a href="{{ $linea['url_edit'] }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                            @endcan
                                        @endif
                                        @if(! empty($linea['url_destroy']))
                                            @can('inventario.delete')
                                            <form action="{{ $linea['url_destroy'] }}" method="POST" class="d-inline m-0 on-submit-confirm"
                                                  data-confirm-title="¿Eliminar esta entrada?"
                                                  data-confirm-text="Se quitará {{ number_format($linea['cantidad'], $esUnidadLinea ? 0 : 2) }} {{ $linea['unidad'] }} del almacén{{ ($ambito ?? '') === 'planta' ? ' de planta' : ' agrícola' }}.">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <a href="{{ route($rutaPrefijo.'.show', $almacen) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al almacén
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.on-submit-confirm').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var el = this;
            if (typeof Swal === 'undefined') {
                if (confirm(el.dataset.confirmText || '¿Está seguro de eliminar esta entrada?')) {
                    el.submit();
                }
                return;
            }
            Swal.fire({
                title: el.dataset.confirmTitle || '¿Está seguro?',
                text: el.dataset.confirmText || 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    el.submit();
                }
            });
        });
    });
})();
</script>
@endpush
