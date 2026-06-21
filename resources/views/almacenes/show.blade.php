@php

    $ambitoActual = $ambito ?? \App\Support\AlmacenAmbito::AGRICOLA;

    $esMayorista = $ambitoActual === \App\Support\AlmacenAmbito::MAYORISTA;

    $tituloDetalle = match ($ambitoActual) {
        \App\Support\AlmacenAmbito::PLANTA => 'Detalle de Almacén Planta',
        \App\Support\AlmacenAmbito::MAYORISTA => 'Detalle de Almacén Mayorista',
        default => 'Detalle de Almacén Agrícola',
    };

    $coords = \App\Support\UbicacionGpsParser::coordsOrDefault($almacen->ubicacion ?? null);

    $ubicacionResuelta = \App\Support\UbicacionGpsParser::resolverAlmacen($almacen->almacenid, $almacen->nombre, $almacen->ubicacion ?? null);

    $direccionVisible = $ubicacionResuelta['direccion'];

    $tieneGps = \App\Support\UbicacionGpsParser::fromTexto($almacen->ubicacion ?? null) !== null;

@endphp

@extends('layouts.app')



@section('title', $tituloDetalle.' | AgroFusion')

@section('page_title', $tituloDetalle)



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>

    <li class="breadcrumb-item"><a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" style="color: #2c5530;">Almacenes</a></li>

    <li class="breadcrumb-item active">Detalle</li>

@endsection



@push('styles')

@include('partials.modulo-inventario-styles')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

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

.page-almacen-show .contenido-acciones {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.25rem;
    justify-content: center;
}

.page-almacen-show .contenido-acciones .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
    font-size: 0.85rem;
}

#mapaDetalleAlmacen {
    height: 220px;

    min-height: 220px;

    width: 100%;

    border-radius: 10px;

    border: 1px solid #e2e8f0;

    background: #e8eef3;

    z-index: 1;

}

.leaflet-container { font-family: inherit; }

.ubicacion-texto { font-size: .9rem; color: #64748b; }

</style>

@endpush



@section('content')

<div class="modulo-inv page-almacen-show">

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-sm mb-4 border-left-info">

                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">

                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-warehouse mr-2"></i>Información del Almacén</h6>

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

                            <small class="text-uppercase text-muted font-weight-bold d-block mb-2">Ubicación en mapa</small>

                            <div id="mapaDetalleAlmacen" data-lat="{{ $coords['lat'] }}" data-lng="{{ $coords['lng'] }}" data-nombre="{{ $almacen->nombre }}"></div>

                            @if($direccionVisible)

                                <p class="ubicacion-texto mt-2 mb-0">

                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $direccionVisible }}

                                    @if($ubicacionResuelta['estimada'] ?? false)

                                        <span class="text-muted d-block small mt-1">Dirección referencial (edite el almacén y marque el mapa para fijar la calle exacta).</span>

                                    @endif

                                </p>

                            @else

                                <p class="ubicacion-texto mt-2 mb-0">Ubicación no especificada — mapa en Santa Cruz por defecto.</p>

                            @endif

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

            <div class="card shadow-sm mb-0">

                <div class="card-header bg-light">

                    <h6 class="m-0 font-weight-bold text-dark">Acciones</h6>

                </div>

                <div class="card-body">

                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.edit', $almacen) }}" class="btn btn-warning btn-block mb-3 shadow-sm">

                        <i class="fas fa-edit mr-2"></i>Editar Datos

                    </a>

                    <hr>

                    @php
                        $tieneRecursosAlmacen = ($resumenCapacidad['ocupado_kg'] ?? 0) > 0.001;
                        $mensajeEliminarConRecursos = app(\App\Services\AlmacenCapacidadService::class)->mensajeEliminacionConRecursos();
                    @endphp

                    @if($tieneRecursosAlmacen)
                        <button type="button"
                                class="btn btn-outline-danger btn-block btn-eliminar-almacen-bloqueado"
                                data-mensaje="{{ $mensajeEliminarConRecursos }}">
                            <i class="fas fa-trash-alt mr-2"></i>Eliminar Almacén
                        </button>
                    @else
                    <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.destroy', $almacen) }}" method="POST"

                        onsubmit="return confirm('¿Está seguro de eliminar este almacén? Esta acción no se puede deshacer.')">

                        @csrf

                        @method('DELETE')

                        <button type="submit" class="btn btn-outline-danger btn-block">

                            <i class="fas fa-trash-alt mr-2"></i>Eliminar Almacén

                        </button>

                    </form>
                    @endif

                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" class="btn btn-link btn-block mt-3 text-secondary">

                        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista

                    </a>

                </div>

            </div>



            <div class="card shadow-sm border-left-success mt-4">

                <div class="card-body">

                    <div class="row no-gutters align-items-center">

                        <div class="col mr-2">

                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock en depósito</div>

                            <div class="h5 mb-0 font-weight-bold text-gray-800">

                                @if(isset($resumenCapacidad))

                                    {{ number_format($resumenCapacidad['ocupado_kg'], 0) }} kg

                                    <small class="text-muted d-block font-weight-normal" style="font-size:.75rem">
                                        @if($esMayorista)
                                            Productos procesados listos para distribución
                                        @else
                                            Insumos, cosecha y producto de planta
                                        @endif
                                    </small>

                                @else — @endif

                            </div>

                        </div>

                        <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <div class="card contenido-card mt-2">

        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">

            <h5 class="mb-0 font-weight-bold text-success"><i class="fas fa-box-open mr-1"></i> Contenido del almacén</h5>

            <span class="badge badge-light border">{{ $contenidos->count() }} ítems</span>

        </div>

        <div class="card-body pb-2">

            @if(!$esMayorista && ($resumenCosechasPorCultivo ?? collect())->isNotEmpty())
                <div class="mb-3">
                    <label class="small text-muted mb-2 d-block">Resumen de cosecha en almacén</label>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        @foreach($resumenCosechasPorCultivo as $resumen)
                            <div class="border rounded px-3 py-2 bg-light" style="min-width:180px;">
                                <strong class="text-success d-block">{{ $resumen->cultivo }}</strong>
                                <span class="small text-muted">
                                    {{ number_format($resumen->cajas, 0, ',', '.') }} cajas
                                    · {{ number_format($resumen->unidades, 0, ',', '.') }} unidades
                                    · {{ number_format($resumen->kg, 0, ',', '.') }} kg
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="row mb-3">

                <div class="col-md-{{ $esMayorista ? '12' : '6' }} mb-2 mb-md-0">

                    <label class="small text-muted mb-1">Buscar</label>

                    <div class="input-group input-group-sm">

                        <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span></div>

                        <input type="text" id="contenidoSearch" class="form-control" placeholder="{{ $esMayorista ? 'Nombre de producto...' : 'Nombre, cultivo o tipo...' }}">

                    </div>

                </div>

                @unless($esMayorista)
                <div class="col-md-3 mb-2 mb-md-0">

                    <label class="small text-muted mb-1">Categoría</label>

                    <select id="contenidoFiltroCategoria" class="form-control form-control-sm">

                        <option value="">Todas</option>

                        <option value="insumo">Insumos</option>

                        <option value="cosecha">Cosecha</option>

                        <option value="producto_planta">Producto terminado</option>

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
                @endunless

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-modulo table-hover table-sm mb-0">

                <thead>

                    <tr>

                        <th>Producto</th>
                        @unless($esMayorista)<th>Categoría</th><th>Tipo</th>@endunless
                        <th class="text-right">Cantidad</th><th class="text-right">Equivalente (kg)</th><th>Detalle</th>

                        <th class="text-center text-nowrap">Acciones</th>

                    </tr>

                </thead>

                <tbody id="contenidoTableBody">

                    @forelse($contenidos as $item)

                        <tr class="contenido-row" data-search="{{ $item->search }}" data-categoria="{{ $item->categoria }}" data-tipo="{{ strtolower($item->tipo_label) }}">

                            <td><strong class="text-success">{{ $item->nombre }}</strong></td>

                            @unless($esMayorista)
                            <td><span class="badge badge-{{ match($item->categoria) { 'cosecha' => 'info', 'producto_planta' => 'warning', default => 'secondary' } }}">{{ match($item->categoria) { 'cosecha' => 'Cosecha', 'producto_planta' => 'Producto terminado', default => 'Insumo' } }}</span></td>

                            <td>{{ $item->tipo_label }}</td>
                            @endunless

                            <td class="text-right">
                                @if($item->categoria === 'cosecha' && ($item->cajas ?? null) !== null)
                                    <strong>{{ number_format($item->cajas, 0, ',', '.') }}</strong> <small class="text-muted">cajas</small>
                                    <br><span class="small text-muted">{{ number_format($item->unidades, 0, ',', '.') }} u.</span>
                                @else
                                    {{ number_format($item->cantidad, 2) }} <small class="text-muted">{{ $item->unidad }}</small>
                                @endif
                            </td>

                            <td class="text-right">{{ number_format($item->kg, 2) }} kg</td>

                            <td class="text-muted small">{{ $item->detalle }}</td>

                            <td class="text-center text-nowrap">
                                <div class="contenido-acciones">
                                    @if($item->categoria === 'producto_mayorista' && ! empty($item->insumoid))
                                        <a href="{{ route(($rutaPrefijo ?? 'almacen-mayorista').'.inventario.show', [$almacen, $item->insumoid]) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        @can('inventario.update')
                                        <a href="{{ route(($rutaPrefijo ?? 'almacen-mayorista').'.inventario.edit', [$almacen, $item->insumoid]) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('inventario.delete')
                                        <form action="{{ route(($rutaPrefijo ?? 'almacen-mayorista').'.inventario.destroy', [$almacen, $item->insumoid]) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar producto?" data-confirm-text="Se quitará este producto del inventario del almacén.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endcan
                                    @elseif($item->categoria === 'insumo' && ! empty($item->insumoid))
                                        <a href="{{ route('insumos.show', $item->insumoid) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        @can('inventario.update')
                                        <a href="{{ route('insumos.edit', $item->insumoid) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('inventario.delete')
                                        <form action="{{ route('insumos.destroy', $item->insumoid) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar insumo?" data-confirm-text="Se quitará este insumo del inventario.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endcan
                                    @elseif($item->categoria === 'cosecha' && ! empty($item->produccionid))
                                        <a href="{{ route('producciones.show', $item->produccionid) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('producciones.edit', $item->produccionid) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('producciones.destroy', $item->produccionid) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar cosecha?" data-confirm-text="Se eliminará el registro de producción y su almacenamiento.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @elseif($item->categoria === 'producto_planta' && ! empty($item->insumoid))
                                        <a href="{{ route(($rutaPrefijo ?? 'almacen-planta').'.inventario.show', [$almacen, $item->insumoid]) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        @can('inventario.update')
                                        <a href="{{ route(($rutaPrefijo ?? 'almacen-planta').'.inventario.edit', [$almacen, $item->insumoid]) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('inventario.delete')
                                        <form action="{{ route(($rutaPrefijo ?? 'almacen-planta').'.inventario.destroy', [$almacen, $item->insumoid]) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar producto terminado?" data-confirm-text="Se quitará este producto y su stock por presentación del almacén.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endcan
                                    @elseif($item->categoria === 'producto_planta' && ! empty($item->lote_produccion_pedido_id))
                                        <a href="{{ route('procesamiento.show', $item->lote_produccion_pedido_id) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('procesamiento.edit', $item->lote_produccion_pedido_id) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('procesamiento.destroy', $item->lote_produccion_pedido_id) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar lote de procesamiento?" data-confirm-text="Esta acción no se puede deshacer.">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr id="contenidoEmptyRow">

                            <td colspan="{{ $esMayorista ? 5 : 7 }}" class="text-center text-muted py-4">

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

@endsection



@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>

(function () {

    function initMapaDetalleAlmacen() {

        const mapEl = document.getElementById('mapaDetalleAlmacen');

        if (!mapEl || typeof window.L === 'undefined') {

            return;

        }

        if (mapEl._leafletMap) {

            mapEl._leafletMap.invalidateSize();

            return;

        }

        const lat = parseFloat(mapEl.dataset.lat) || -17.7833;

        const lng = parseFloat(mapEl.dataset.lng) || -63.1821;

        const nombre = mapEl.dataset.nombre || 'Almacén';

        const map = L.map(mapEl, { scrollWheelZoom: false }).setView([lat, lng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {

            maxZoom: 19,

            attribution: '&copy; OpenStreetMap'

        }).addTo(map);

        L.marker([lat, lng]).addTo(map).bindPopup(nombre).openPopup();

        mapEl._leafletMap = map;

        setTimeout(function () { map.invalidateSize(); }, 100);

        setTimeout(function () { map.invalidateSize(); }, 500);

    }



    document.addEventListener('DOMContentLoaded', initMapaDetalleAlmacen);

    window.addEventListener('load', initMapaDetalleAlmacen);



    const esMayorista = @json($esMayorista);

    const q = document.getElementById('contenidoSearch');

    const fCat = document.getElementById('contenidoFiltroCategoria');

    const fTipo = document.getElementById('contenidoFiltroTipo');

    const rows = Array.from(document.querySelectorAll('.contenido-row'));

    const sinResultados = document.getElementById('contenidoSinResultados');



    function aplicarFiltroContenido() {

        const texto = (q?.value || '').toLowerCase();

        const cat = esMayorista ? '' : (fCat?.value || '').toLowerCase();

        const tipo = esMayorista ? '' : (fTipo?.value || '').toLowerCase();

        let visibles = 0;

        rows.forEach(function (row) {

            const show = (!texto || (row.dataset.search || '').includes(texto))

                && (!cat || (row.dataset.categoria || '') === cat)

                && (!tipo || (row.dataset.tipo || '') === tipo);

            row.style.display = show ? '' : 'none';

            if (show) visibles++;

        });

        if (sinResultados) sinResultados.classList.toggle('d-none', visibles > 0 || rows.length === 0);

    }

    if (q) q.addEventListener('input', aplicarFiltroContenido);

    if (fCat) fCat.addEventListener('change', aplicarFiltroContenido);

    if (fTipo) fTipo.addEventListener('change', aplicarFiltroContenido);

    document.querySelectorAll('.on-submit-confirm').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var el = this;
            if (typeof Swal === 'undefined') {
                if (confirm(el.dataset.confirmText || '¿Confirmar eliminación?')) {
                    el.submit();
                }
                return;
            }
            Swal.fire({
                title: el.dataset.confirmTitle || '¿Eliminar?',
                text: el.dataset.confirmText || 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    el.submit();
                }
            });
        });
    });

    document.querySelectorAll('.btn-eliminar-almacen-bloqueado').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var mensaje = btn.dataset.mensaje || 'Debe vaciar el almacén primero para poder eliminarlo.';
            if (typeof Swal === 'undefined') {
                alert(mensaje);
                return;
            }
            Swal.fire({
                icon: 'error',
                title: 'No se puede eliminar',
                text: mensaje,
                confirmButtonColor: '#2c5530',
            });
        });
    });

})();
</script>

@endpush

