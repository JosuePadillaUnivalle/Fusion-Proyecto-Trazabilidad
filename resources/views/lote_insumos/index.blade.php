@extends('layouts.app')

@section('title', 'Aplicación de Insumos | AgroFusion')
@section('page_title', 'Aplicación de insumos en lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Aplicación de insumos</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-lote-insumos .aplicacion-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: box-shadow .2s, transform .2s;
    overflow: hidden;
}
.page-lote-insumos .aplicacion-card:hover {
    box-shadow: 0 6px 20px rgba(44, 85, 48, .12);
    transform: translateY(-2px);
}
.page-lote-insumos .aplicacion-card .card-top {
    background: linear-gradient(135deg, #f0f7f1, #fff);
    padding: 1rem 1.1rem .75rem;
    border-bottom: 1px solid #e8f0e9;
}
.page-lote-insumos .aplicacion-card .lote-nombre {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2c5530;
    margin: 0;
}
.page-lote-insumos .aplicacion-card .meta-chip {
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
.page-lote-insumos .aplicacion-card .meta-chip i { color: #2c5530; width: 14px; text-align: center; }
.page-lote-insumos .aplicacion-card .card-actions {
    border-top: 1px solid #eef2f6;
    background: #fafbfc;
    padding: .6rem 1rem;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-lote-insumos">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Aplicaciones registradas</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                <span class="small-box-footer">Historial completo</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['lotes'] }}</h3>
                    <p>Lotes tratados</p>
                </div>
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                <span class="small-box-footer">Lotes distintos</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['insumos'] }}</h3>
                    <p>Insumos utilizados</p>
                </div>
                <div class="icon"><i class="fas fa-flask"></i></div>
                <span class="small-box-footer">Insumos distintos</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ number_format($stats['cantidad_total'], 0) }}</h3>
                    <p>Unidades aplicadas</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
                <span class="small-box-footer">Total histórico registrado</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Aplicación de insumos"
            icono="fa-flask"
            :registros="$loteInsumos->total()"
            filtros-target="#filtrosAplicacionesPanel"
            :view-toggle="true"
            view-default="table"
            :nuevo-href="route('lote-insumos.create')"
            nuevo-text="Nueva Aplicación de Insumos"
            nuevo-can="inventario.create"
        />

        <div id="filtrosAplicacionesPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Lote, insumo o encargado...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Estado</label>
                    <select id="filterEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estadosFiltro as $estadoNombre)
                            <option value="{{ strtolower($estadoNombre) }}">{{ $estadoNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Encargado</label>
                    <select id="filterEncargado" class="form-control form-control-sm">
                        <option value="">Todos los encargados</option>
                        @foreach($encargadosFiltro as $encargado)
                            <option value="{{ strtolower($encargado) }}">{{ $encargado }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <x-filtros-client-actions />
        </div>

        <div id="tableView" class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Insumo</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Encargado</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loteInsumos as $li)
                        @php
                            $searchText = strtolower(trim(
                                ($li->lote->nombre ?? '') . ' ' .
                                ($li->insumo->nombre ?? '') . ' ' .
                                ($li->usuario->nombre ?? '')
                            ));
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ $searchText }}"
                            data-estado="{{ strtolower($li->estado->nombre ?? '') }}"
                            data-encargado="{{ strtolower($li->usuario->nombre ?? '') }}">
                            <td><strong class="text-success">{{ $li->lote->nombre ?? '—' }}</strong></td>
                            <td>{{ $li->insumo->nombre ?? '—' }}</td>
                            <td>{{ number_format((float) $li->cantidadusada, 2) }}</td>
                            <td>{{ $li->fechauo ? \Carbon\Carbon::parse($li->fechauo)->format('d/m/Y') : '—' }}</td>
                            <td><span class="badge badge-secondary">{{ $li->estado->nombre ?? '—' }}</span></td>
                            <td>{{ $li->usuario->nombre ?? '—' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('lote-insumos.show', $li) }}" class="btn btn-default" title="Ver"><i class="fas fa-eye text-info"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route('lote-insumos.edit', $li) }}" class="btn btn-default" title="Editar"><i class="fas fa-edit text-warning"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route('lote-insumos.destroy', $li) }}" method="POST" class="d-inline on-submit-confirm">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-default" title="Eliminar"><i class="fas fa-trash text-danger"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-clipboard-list fa-2x mb-2 text-light d-block"></i>
                                No hay aplicaciones registradas.
                                @can('inventario.create')
                                <a href="{{ route('lote-insumos.create') }}" class="d-block mt-2">Registrar primera aplicación</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cardView" class="p-3" style="display: none;">
            <div class="row">
            @forelse($loteInsumos as $li)
                @php
                    $searchText = strtolower(trim(
                        ($li->lote->nombre ?? '') . ' ' .
                        ($li->insumo->nombre ?? '') . ' ' .
                        ($li->usuario->nombre ?? '')
                    ));
                    $fechaTxt = $li->fechauo ? \Carbon\Carbon::parse($li->fechauo)->format('d/m/Y') : '—';
                    $unidad = $li->insumo->unidadMedida->abreviatura ?? 'ud';
                @endphp
                <div class="col-md-6 col-xl-4 mb-3 search-item"
                    data-nombre="{{ $searchText }}"
                    data-estado="{{ strtolower($li->estado->nombre ?? '') }}"
                    data-encargado="{{ strtolower($li->usuario->nombre ?? '') }}">
                    <div class="card aplicacion-card h-100 mb-0">
                        <div class="card-top">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="lote-nombre mb-1">
                                        <i class="fas fa-map-marker-alt mr-1 text-success"></i>
                                        {{ $li->lote->nombre ?? 'Sin lote' }}
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="fas fa-boxes mr-1"></i>{{ $li->insumo->nombre ?? 'Sin insumo' }}
                                    </p>
                                </div>
                                <span class="badge badge-success">{{ $li->estado->nombre ?? 'Aplicado' }}</span>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <div class="row no-gutters text-center">
                                <div class="col-4 mb-2">
                                    <div class="meta-chip d-block w-100">
                                        <i class="fas fa-balance-scale"></i>
                                        <span>{{ number_format((float) $li->cantidadusada, 2) }} {{ $unidad }}</span>
                                    </div>
                                </div>
                                <div class="col-4 mb-2">
                                    <div class="meta-chip d-block w-100">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ $fechaTxt }}</span>
                                    </div>
                                </div>
                                <div class="col-4 mb-2">
                                    <div class="meta-chip d-block w-100">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $li->usuario->nombre ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-actions d-flex justify-content-end">
                            <a href="{{ route('lote-insumos.show', $li) }}" class="btn btn-sm btn-outline-info mr-1" title="Ver"><i class="fas fa-eye"></i></a>
                            @can('inventario.update')
                            <a href="{{ route('lote-insumos.edit', $li) }}" class="btn btn-sm btn-outline-warning mr-1" title="Editar"><i class="fas fa-edit"></i></a>
                            @endcan
                            @can('inventario.delete')
                            <form action="{{ route('lote-insumos.destroy', $li) }}" method="POST" class="d-inline on-submit-confirm">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">No hay aplicaciones registradas.</div>
            @endforelse
            </div>
        </div>

        @if($loteInsumos->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-2">
            {{ $loteInsumos->links() }}
        </div>
        @endif
    </div>

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
        var estado = ($('#filterEstado').val() || '').toLowerCase();
        var encargado = ($('#filterEncargado').val() || '').toLowerCase();

        $('.search-item').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchEstado = !estado || ($(this).data('estado') || '') === estado;
            var matchEncargado = !encargado || ($(this).data('encargado') || '') === encargado;
            $(this).toggle(matchNombre && matchEstado && matchEncargado);
        });
        $('.search-item-row').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchEstado = !estado || ($(this).data('estado') || '') === estado;
            var matchEncargado = !encargado || ($(this).data('encargado') || '') === encargado;
            $(this).toggle(matchNombre && matchEstado && matchEncargado);
        });
    }

    $('#searchInput').on('keyup', aplicarFiltros);
    $('#filterEstado, #filterEncargado').on('change', aplicarFiltros);
    $('#btnAplicarFiltros').on('click', aplicarFiltros);

    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterEstado, #filterEncargado').val('');
        aplicarFiltros();
    });

    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar registro?',
            text: 'No podrás revertir esto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

});
</script>
@endpush
