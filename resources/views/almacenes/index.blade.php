@extends('layouts.app')

@section('title', 'Almacenes | AgroFusion')
@section('page_title', $tituloModulo ?? 'Almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Almacenes</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-almacenes .almacen-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: box-shadow .2s, transform .2s;
    overflow: hidden;
}
.page-almacenes .almacen-card:hover {
    box-shadow: 0 6px 20px rgba(44, 85, 48, .12);
    transform: translateY(-2px);
}
.page-almacenes .almacen-card .card-top {
    background: linear-gradient(135deg, #f0f7f1, #fff);
    padding: 1rem 1.1rem .75rem;
    border-bottom: 1px solid #e8f0e9;
}
.page-almacenes .almacen-card .meta-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .35rem .6rem;
    font-size: .8rem;
    color: #475569;
}
.page-almacenes .almacen-card .meta-chip i { color: #2c5530; width: 14px; text-align: center; }
.page-almacenes .ocupacion-bar {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
}
.page-almacenes .ocupacion-bar .fill {
    height: 100%;
    background: linear-gradient(90deg, #4a7c59, #2c5530);
}
.page-almacenes .almacen-acciones {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.25rem;
    justify-content: center;
}
.page-almacenes .almacen-acciones .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-almacenes">

<div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total de almacenes</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <span class="small-box-footer">Registrados</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-orange">
                <div class="inner">
                    <h3>{{ number_format($stats['capacidad_total'], 0) }}</h3>
                    <p>Capacidad combinada</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
                <span class="small-box-footer">Capacidad en kg (página)</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ number_format($stats['ocupado_total'] ?? 0, 0) }}</h3>
                    <p>kg ocupados{{ ($ambito ?? '') === 'planta' ? ' (planta)' : ' (cosecha)' }}</p>
                </div>
                <div class="icon"><i class="fas {{ ($ambito ?? '') === 'planta' ? 'fa-industry' : 'fa-seedling' }}"></i></div>
                <span class="small-box-footer">En esta página</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['ocupacion_promedio'] ?? 0 }}%</h3>
                    <p>Uso promedio de almacenaje</p>
                </div>
                <div class="icon"><i class="fas fa-chart-pie"></i></div>
                <span class="small-box-footer">Capacidad utilizada</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            :titulo="$tituloModulo ?? 'Almacenes'"
            icono="fa-warehouse"
            :registros="$almacenes->total()"
            filtros-target="#filtrosAlmacenesPanel"
            :view-toggle="true"
            view-default="table"
            :nuevo-href="route(($rutaPrefijo ?? 'almacen-agricola').'.create')"
            nuevo-can="inventario.create"
        >
            <x-slot:tools>
                @if(!empty($almacenesMapa))
                <button type="button" class="btn btn-outline-success btn-sm ml-1" id="btnVerMapaAlmacenes">
                    <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                </button>
                @endif
            </x-slot:tools>
        </x-modulo-index-header>

        <div id="filtrosAlmacenesPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar almacén...">
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Nivel de almacenaje</label>
                    <select id="filterOcupacion" class="form-control form-control-sm">
                        <option value="">Todos los niveles</option>
                        <option value="baja">Espacio disponible (menos del 50% usado)</option>
                        <option value="media">Medio lleno (50% – 85% usado)</option>
                        <option value="alta">Casi lleno (más del 85% usado)</option>
                    </select>
                    <small class="text-muted d-block mt-1">Filtra por cuánto espacio físico está en uso, no por actividad operativa.</small>
                </div>
            </div>
            <x-filtros-client-actions />
        </div>

        <div id="tableView" class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Capacidad (kg)</th>
                        <th>Almacenaje</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($almacenes as $a)
                        @php
                            $oc = $ocupacionPorId[$a->almacenid] ?? ['porcentaje' => 0, 'ocupado_kg' => 0, 'capacidad_kg' => 0];
                            $pct = $oc['porcentaje'];
                            $ocupacionFiltro = $pct > 85 ? 'alta' : ($pct >= 50 ? 'media' : 'baja');
                            $direccionAlmacen = \App\Support\UbicacionGpsParser::resolverAlmacen($a->almacenid, $a->nombre, $a->ubicacion)['direccion'];
                            $searchText = strtolower(trim(($a->nombre ?? '') . ' ' . ($a->ubicacion ?? '') . ' ' . $direccionAlmacen));
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ $searchText }}"
                            data-ocupacion="{{ $ocupacionFiltro }}">
                            <td>
                                <strong class="text-success">{{ $a->nombre }}</strong>
                                @if($a->descripcion)
                                <br><small class="text-muted">{{ Str::limit($a->descripcion, 40) }}</small>
                                @endif
                            </td>
                            <td>{{ $direccionAlmacen ?: '—' }}</td>
                            <td>{{ number_format((float) $a->capacidad, 0) }} kg</td>
                            <td>
                                <div class="ocupacion-bar mb-1" style="max-width:120px">
                                    <div class="fill" style="width:{{ min(100, $pct) }}%"></div>
                                </div>
                                <small>{{ number_format($oc['ocupado_kg'], 0) }} / {{ number_format($oc['capacidad_kg'], 0) }} kg ({{ $pct }}%)</small>
                            </td>
                            <td class="text-center">
                                <div class="almacen-acciones">
                                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.show', $a) }}" class="btn btn-sm btn-outline-info" title="Ver detalles"><i class="fas fa-eye"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.edit', $a) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.destroy', $a) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar almacén?" data-confirm-text="Se eliminará este almacén y su configuración.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-warehouse fa-2x mb-2 text-light d-block"></i>
                                No hay almacenes registrados.
                                @can('inventario.create')
                                <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.create') }}" class="d-block mt-2">Crear primer almacén</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cardView" class="p-3" style="display: none;">
            <div class="row">
            @forelse($almacenes as $a)
                @php
                    $oc = $ocupacionPorId[$a->almacenid] ?? ['porcentaje' => 0, 'ocupado_kg' => 0, 'capacidad_kg' => 0, 'disponible_kg' => 0];
                    $pct = $oc['porcentaje'];
                    $ocupacionFiltro = $pct > 85 ? 'alta' : ($pct >= 50 ? 'media' : 'baja');
                    $direccionAlmacen = \App\Support\UbicacionGpsParser::resolverAlmacen($a->almacenid, $a->nombre, $a->ubicacion)['direccion'];
                    $searchText = strtolower(trim(($a->nombre ?? '') . ' ' . ($a->ubicacion ?? '') . ' ' . $direccionAlmacen));
                @endphp
                <div class="col-md-6 col-xl-4 mb-3 search-item"
                    data-nombre="{{ $searchText }}"
                    data-ocupacion="{{ $ocupacionFiltro }}">
                    <div class="card almacen-card h-100 mb-0">
                        <div class="card-top">
                            <div class="d-flex justify-content-between align-items-start">
                                <p class="mb-1 font-weight-bold text-success" style="font-size:1.05rem">
                                    <i class="fas fa-warehouse mr-1"></i>{{ $a->nombre }}
                                </p>
                                <span class="badge badge-{{ $pct > 85 ? 'danger' : ($pct >= 50 ? 'warning' : 'success') }}">
                                    {{ $pct }}% usado
                                </span>
                            </div>
                            @if($direccionAlmacen)
                            <p class="mb-0 small text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ Str::limit($direccionAlmacen, 50) }}</p>
                            @endif
                        </div>
                        <div class="card-body py-3">
                            <div class="ocupacion-bar mb-2">
                                <div class="fill" style="width:{{ min(100, $pct) }}%"></div>
                            </div>
                            <div class="row no-gutters text-center">
                                <div class="col-6 mb-2">
                                    <div class="meta-chip d-block w-100">
                                        <i class="fas fa-balance-scale"></i>
                                        <span>{{ number_format((float) $a->capacidad, 0) }} kg máx.</span>
                                    </div>
                                </div>
                                <div class="col-6 mb-2">
                                    <div class="meta-chip d-block w-100">
                                        <i class="fas fa-box-open"></i>
                                        <span>{{ number_format($oc['disponible_kg'], 0) }} kg libres</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-end py-2">
                            <div class="almacen-acciones">
                            <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.show', $a) }}" class="btn btn-sm btn-outline-info" title="Ver detalles"><i class="fas fa-eye"></i></a>
                            @can('inventario.update')
                            <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.edit', $a) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                            @endcan
                            @can('inventario.delete')
                            <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.destroy', $a) }}" method="POST" class="d-inline m-0 on-submit-confirm" data-confirm-title="¿Eliminar almacén?" data-confirm-text="Se eliminará este almacén y su configuración.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">No hay almacenes registrados.</div>
            @endforelse
            </div>
        </div>

        @if($almacenes->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-2">
            {{ $almacenes->links() }}
        </div>
        @endif
    </div>

    @include('almacenes.partials.mapa-index')

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#btnCardView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#cardView').show();
        $('#tableView').hide();
    });
    $('#btnTableView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#tableView').show();
        $('#cardView').hide();
    });

    function aplicarFiltros() {
        var val = ($('#searchInput').val() || '').toLowerCase();
        var ocupacion = ($('#filterOcupacion').val() || '').toLowerCase();

        $('.search-item, .search-item-row').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchOcup = !ocupacion || ($(this).data('ocupacion') || '') === ocupacion;
            $(this).toggle(matchNombre && matchOcup);
        });
    }

    $('#searchInput').on('keyup', aplicarFiltros);
    $('#filterOcupacion').on('change', aplicarFiltros);
    $('#btnAplicarFiltros').on('click', aplicarFiltros);

    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterOcupacion').val('');
        aplicarFiltros();
    });

    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var titulo = form.dataset.confirmTitle || '¿Está seguro?';
        var texto = form.dataset.confirmText || 'Esta acción no se puede deshacer.';
        if (typeof Swal === 'undefined') {
            if (confirm(texto)) {
                form.submit();
            }
            return;
        }
        Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
