@extends('layouts.app')

@section('title', 'Lotes | AgroNexus')
@section('page_title', 'Gestión de lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Lotes</li>
@endsection

@php
    use App\Support\EstadoLoteCatalogo;

    $estadoBadge = fn ($nombre) => match (EstadoLoteCatalogo::slugFromNombre($nombre) ?? '') {
        'planificado' => 'secondary',
        'sembrado' => 'primary',
        'en_crecimiento' => 'success',
        'listo_para_cosecha' => 'info',
        'cosechado' => 'warning',
        'finalizado' => 'dark',
        default => 'secondary',
    };
    $filtrosActivos = collect($filtros ?? [])->filter(fn ($v) => $v !== null && $v !== '');
    $filtrosAbiertos = EstadoLoteCatalogo::filtrosPanelAbierto(request(), $filtrosActivos->isNotEmpty());
@endphp

@push('styles')
@include('partials.modulo-lotes-actividades-styles')
<style>
.page-lotes .table-lotes thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.page-lotes .table-lotes tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.page-lotes .table-lotes tbody tr:hover { background: #f8fbf8; }
.page-lotes .lote-nombre {
    font-weight: 600;
    color: #2c5530;
}
.page-lotes .lote-nombre:hover { color: #1e3d22; text-decoration: none; }
.page-lotes .meta-chip {
    display: inline-block;
    font-size: 0.75rem;
    color: #6c757d;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 2px 8px;
    margin: 2px 4px 2px 0;
}
.page-lotes .lote-row-card {
    display: flex;
    align-items: center;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #f1f3f4;
    transition: background 0.15s ease;
}
.page-lotes .lote-row-card:hover { background: #f8fbf8; }
.page-lotes .lote-row-card .lote-avatar {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    background: #e8f5e9;
    color: #2c5530;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 1rem;
}
.page-lotes .btn-actions .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
}
.page-lotes .card-header .badge-registros {
    font-size: 0.78rem;
    font-weight: 500;
    padding: 0.35em 0.65em;
    white-space: nowrap;
}
.page-lotes .sync-hint {
    font-size: 0.8rem;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="modulo-la page-lotes">

<div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total lotes</p>
                </div>
                <div class="icon"><i class="fas fa-map"></i></div>
                <span class="small-box-footer">{{ $stats['sembrados'] }} sembrados</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['en_produccion'] }}</h3>
                    <p>En crecimiento</p>
                </div>
                <div class="icon"><i class="fas fa-leaf"></i></div>
                <span class="small-box-footer">Lotes activos</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ number_format($stats['hectareas'], 1) }}</h3>
                    <p>Hectáreas</p>
                </div>
                <div class="icon"><i class="fas fa-ruler-combined"></i></div>
                <span class="small-box-footer">Superficie registrada</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['con_mapa'] }}</h3>
                    <p>Con GPS</p>
                </div>
                <div class="icon"><i class="fas fa-map-pin"></i></div>
                <a href="{{ route('lotes.mapa') }}" class="small-box-footer">
                    {{ $stats['sin_gps'] }} sin GPS · Ver mapa <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="alert alert-light border small mb-3">
        <i class="fas fa-info-circle text-success mr-1"></i>
        <strong>Estados del lote:</strong> al crear queda en <em>Planificado</em>.
        Pasa a <em>Sembrado</em> al completar siembra; a <em>En crecimiento</em> con riego, fumigación o fertilización;
        y a <em>Cosechado</em> al registrar la cosecha. También puedes cambiarlo manualmente al editar el lote.
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Lotes"
            icono="fa-seedling"
            :registros="$lotes->total()"
            filtros-target="#filtrosLotesPanel"
            :view-toggle="true"
            view-default="table"
            :nuevo-href="route('lotes.create')"
            nuevo-can="lotes.create"
        />

        <div id="filtrosLotesPanel" class="filtros-panel collapse {{ $filtrosAbiertos ? 'show' : '' }}">
            <form method="GET" action="{{ route('lotes.index') }}">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="q" class="form-control"
                                value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre, ubicación o código TRAZ">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">Cultivo</label>
                        <select name="cultivoid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($cultivos as $c)
                                <option value="{{ $c->cultivoid }}" @selected(($filtros['cultivoid'] ?? '') == $c->cultivoid)>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estadolotetipoid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($estados as $e)
                                @php $slug = \App\Support\EstadoLoteCatalogo::slugFromNombre($e->nombre); @endphp
                                <option value="{{ $e->estadolotetipoid }}"
                                    @selected(($filtros['estadolotetipoid'] ?? '') == $e->estadolotetipoid)
                                    title="{{ $slug ? \App\Support\EstadoLoteCatalogo::descripcion($slug) : '' }}">
                                    {{ $e->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">Encargado</label>
                        <select name="usuarioid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->usuarioid }}" @selected(($filtros['usuarioid'] ?? '') == $u->usuarioid)>{{ $u->nombre }} {{ $u->apellido }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">GPS</label>
                        <select name="con_mapa" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="1" @selected(($filtros['con_mapa'] ?? '') === '1')>Con GPS</option>
                            <option value="0" @selected(($filtros['con_mapa'] ?? '') === '0')>Sin GPS</option>
                        </select>
                    </div>
                </div>
                <x-filtros-form-actions
                    :limpiar-url="route('lotes.index', ['filtros_abiertos' => 1])"
                    :resultados="$filtrosActivos->isNotEmpty() ? $lotes->total() : null"
                />
            </form>
        </div>

        {{-- Vista tabla (por defecto) --}}
        <div id="tableView" class="table-responsive">
            <table class="table table-lotes table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Encargado</th>
                        <th>Cultivo</th>
                        <th>Estado</th>
                        <th class="text-right">Superficie</th>
                        <th>GPS</th>
                        <th class="text-center" style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lotes as $l)
                        @php $badge = $estadoBadge($l->estadoTipo->nombre ?? ''); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('lotes.show', $l) }}" class="lote-nombre d-block">{{ $l->nombre }}</a>
                                @if($l->ubicacion)
                                <small class="text-muted">{{ Str::limit($l->ubicacion, 36) }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $l->usuario->nombre ?? '—' }}</td>
                            <td>{{ $l->cultivo->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $badge }}">{{ ucfirst($l->estadoTipo->nombre ?? '—') }}</span>
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format((float) $l->superficie, 1) }} ha</td>
                            <td>
                                @if($l->latitud && $l->longitud)
                                    <i class="fas fa-check-circle text-success" title="Con coordenadas"></i>
                                @else
                                    <i class="fas fa-minus-circle text-muted" title="Sin GPS"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('lotes.show', $l) }}" class="btn btn-default" title="Ver"><i class="fas fa-eye text-info"></i></a>
                                    @can('lotes.update')
                                    <a href="{{ route('lotes.edit', $l) }}" class="btn btn-default" title="Editar"><i class="fas fa-edit text-warning"></i></a>
                                    @include('lotes.partials.btn-cambiar-estado', ['lote' => $l])
                                    @endcan
                                    @can('lotes.delete')
                                    <form action="{{ route('lotes.destroy', $l) }}" method="POST" class="d-inline on-submit-confirm">
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
                                <i class="fas fa-map-marked-alt fa-2x mb-2 text-light d-block"></i>
                                No hay lotes que coincidan.
                                @can('lotes.create')
                                <a href="{{ route('lotes.create') }}" class="d-block mt-2">Crear primer lote</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista tarjetas (alternativa) --}}
        <div id="cardView" style="display: none;">
            @forelse($lotes as $l)
                @php
                    $estadoNombre = $l->estadoTipo->nombre ?? 'Sin estado';
                    $badge = $estadoBadge($estadoNombre);
                @endphp
                <div class="lote-row-card">
                    <div class="lote-avatar">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0 mr-2">
                        <a href="{{ route('lotes.show', $l) }}" class="lote-nombre">{{ $l->nombre }}</a>
                        <div class="mt-1">
                            <span class="meta-chip">{{ $l->usuario->nombre ?? '—' }}</span>
                            <span class="meta-chip">{{ $l->cultivo->nombre ?? 'Sin cultivo' }}</span>
                            <span class="meta-chip">{{ number_format((float) $l->superficie, 1) }} ha</span>
                            @if($l->fechasiembra)
                            <span class="meta-chip">{{ \Carbon\Carbon::parse($l->fechasiembra)->format('d/m/Y') }}</span>
                            @endif
                            @if($l->latitud && $l->longitud)
                            <span class="meta-chip text-success"><i class="fas fa-map-pin mr-1"></i>GPS</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge badge-{{ $badge }} mr-2 d-none d-md-inline">{{ ucfirst($estadoNombre) }}</span>
                    <div class="btn-group btn-group-sm btn-actions flex-shrink-0">
                        <a href="{{ route('lotes.show', $l) }}" class="btn btn-default"><i class="fas fa-eye text-info"></i></a>
                        @can('lotes.update')
                        <a href="{{ route('lotes.edit', $l) }}" class="btn btn-default"><i class="fas fa-edit text-warning"></i></a>
                        @include('lotes.partials.btn-cambiar-estado', ['lote' => $l])
                        @endcan
                        @can('lotes.delete')
                        <form action="{{ route('lotes.destroy', $l) }}" method="POST" class="d-inline on-submit-confirm">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-default"><i class="fas fa-trash text-danger"></i></button>
                        </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">No hay lotes registrados.</div>
            @endforelse
        </div>

        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-2">
            <div>
                @can('lotes.update')
                <form action="{{ route('lotes.sincronizar-operacion') }}" method="POST" class="d-inline mb-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Sincronizar operación
                    </button>
                </form>
                <span class="sync-hint ml-2 d-none d-md-inline">Clima, actividades y riegos automáticos</span>
                @endcan
            </div>
            @if($lotes->hasPages())
            <div class="mb-0">{{ $lotes->links() }}</div>
            @endif
        </div>
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

    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar lote?',
            text: 'No podrás revertir esto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then(function (r) {
            if (r.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
