@extends('layouts.app')

@section('title', 'Detalle de Almacén | AgroNexus')
@section('page_title', 'Detalle de Almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" style="color: #2c5530;">Almacenes</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-almacen-show .border-left-info { border-left: .25rem solid #36b9cc !important; }
.page-almacen-show .border-left-success { border-left: .25rem solid #1cc88a !important; }
.page-almacen-show .contenido-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.page-almacen-show .contenido-card .card-header {
    background: linear-gradient(135deg, #f0f7f1, #fff);
    border-bottom: 1px solid #e8f0e9;
}
</style>
@endpush

<div class="modulo-inv page-almacen-show">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 border-left-info">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-warehouse mr-2"></i>Información del
                        Almacén</h6>
                    @if(isset($resumenCapacidad))
                        <span class="badge badge-{{ ($resumenCapacidad['porcentaje'] ?? 0) > 85 ? 'danger' : 'success' }} px-3 py-2">
                            {{ $resumenCapacidad['porcentaje'] ?? 0 }}% ocupado
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Nombre</small>
                            <h4 class="font-weight-bold text-dark">{{ $almacen->nombre }}</h4>
                        </div>
                        @if($almacen->descripcion)
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Descripción</small>
                            <p class="mb-0">{{ $almacen->descripcion }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Ubicación</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                                <span class="h5 mb-0">{{ $almacen->ubicacion ?? 'No especificada' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Capacidad total</small>
                            <div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-ruler-combined text-primary mr-2"></i>
                                    <span class="h4 font-weight-bold mb-0 text-primary">
                                        {{ number_format($almacen->capacidad, 0) }} <span class="h6 text-muted">kg</span>
                                    </span>
                                </div>
                                @if(isset($resumenCapacidad))
                                <p class="small text-muted mb-0 mt-2 pl-4">
                                    Ocupado: {{ number_format($resumenCapacidad['ocupado_kg'], 0) }} kg ·
                                    Disponible: {{ number_format($resumenCapacidad['disponible_kg'], 0) }} kg
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-dark">Acciones</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.edit', $almacen) }}" class="btn btn-warning btn-block mb-3 shadow-sm">
                        <i class="fas fa-edit mr-2"></i>Editar Datos
                    </a>

                    <hr>

                    <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.destroy', $almacen) }}" method="POST"
                        onsubmit="return confirm('¿Está seguro de eliminar este almacén? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash-alt mr-2"></i>Eliminar Almacén
                        </button>
                    </form>

                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" class="btn btn-link btn-block mt-3 text-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock en depósito</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(isset($resumenCapacidad))
                                    {{ number_format($resumenCapacidad['ocupado_kg'], 0) }} kg
                                    <small class="text-muted d-block font-weight-normal" style="font-size:.75rem">
                                        Insumos + cosecha almacenada
                                    </small>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card contenido-card mt-2">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-success">
                <i class="fas fa-box-open mr-1"></i> Contenido del almacén
            </h5>
            <span class="badge badge-light border">{{ $contenidos->count() }} ítems</span>
        </div>
        <div class="card-body pb-2">
            <div class="row mb-3">
                <div class="col-md-6 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="contenidoSearch" class="form-control" placeholder="Nombre, cultivo o tipo...">
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Categoría</label>
                    <select id="contenidoFiltroCategoria" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="insumo">Insumos</option>
                        <option value="cosecha">Cosecha</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select id="contenidoFiltroTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposContenidoFiltro as $tipo)
                            <option value="{{ strtolower($tipo) }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-modulo table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Equivalente (kg)</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody id="contenidoTableBody">
                    @forelse($contenidos as $item)
                        <tr class="contenido-row"
                            data-search="{{ $item->search }}"
                            data-categoria="{{ $item->categoria }}"
                            data-tipo="{{ strtolower($item->tipo_label) }}">
                            <td><strong class="text-success">{{ $item->nombre }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $item->categoria === 'cosecha' ? 'info' : 'secondary' }}">
                                    {{ $item->categoria === 'cosecha' ? 'Cosecha' : 'Insumo' }}
                                </span>
                            </td>
                            <td>{{ $item->tipo_label }}</td>
                            <td class="text-right">
                                {{ number_format($item->cantidad, 2) }}
                                <small class="text-muted">{{ $item->unidad }}</small>
                            </td>
                            <td class="text-right">{{ number_format($item->kg, 2) }} kg</td>
                            <td class="text-muted small">{{ $item->detalle }}</td>
                        </tr>
                    @empty
                        <tr id="contenidoEmptyRow">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block text-light"></i>
                                Este almacén no tiene stock registrado todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-2 d-none" id="contenidoSinResultados">
            <span class="text-muted small"><i class="fas fa-filter mr-1"></i> Ningún ítem coincide con la búsqueda.</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const q = document.getElementById('contenidoSearch');
    const fCat = document.getElementById('contenidoFiltroCategoria');
    const fTipo = document.getElementById('contenidoFiltroTipo');
    const rows = Array.from(document.querySelectorAll('.contenido-row'));
    const sinResultados = document.getElementById('contenidoSinResultados');

    function aplicarFiltroContenido() {
        const texto = (q?.value || '').toLowerCase();
        const cat = (fCat?.value || '').toLowerCase();
        const tipo = (fTipo?.value || '').toLowerCase();
        let visibles = 0;

        rows.forEach(function (row) {
            const matchTexto = !texto || (row.dataset.search || '').includes(texto);
            const matchCat = !cat || (row.dataset.categoria || '') === cat;
            const matchTipo = !tipo || (row.dataset.tipo || '') === tipo;
            const show = matchTexto && matchCat && matchTipo;
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });

        if (sinResultados) {
            sinResultados.classList.toggle('d-none', visibles > 0 || rows.length === 0);
        }
    }

    if (q) q.addEventListener('input', aplicarFiltroContenido);
    if (fCat) fCat.addEventListener('change', aplicarFiltroContenido);
    if (fTipo) fTipo.addEventListener('change', aplicarFiltroContenido);
});
</script>
@endpush
@endsection