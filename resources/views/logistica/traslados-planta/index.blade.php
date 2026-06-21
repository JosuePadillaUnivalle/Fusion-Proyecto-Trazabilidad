@extends('layouts.app')

@section('title', 'Traslados planta → mayorista')
@section('page_title', $esVistaMayorista ?? false ? 'Recepciones de planta' : 'Traslados planta → mayorista')

@push('styles')
@include('partials.logistica-modulo-styles')
@endpush

@section('content')
<div class="modulo-inv">
    @if(($pendientesCount ?? 0) > 0 && ($esVistaMayorista ?? false))
        <div class="alert alert-warning">
            <i class="fas fa-bell mr-1"></i>
            Tiene <strong>{{ $pendientesCount }}</strong> traslado(s) desde planta pendiente(s) de su aprobación.
        </div>
    @endif

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-truck-loading mr-2"></i>
                {{ ($esVistaMayorista ?? false) ? 'Envíos desde planta' : 'Traslados planta → mayorista' }}
            </h3>
            @if(! ($esVistaMayorista ?? false))
                @can('asignaciones.create')
                <a href="{{ route('pedidos.create', ['destino' => 'mayorista']) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo traslado
                </a>
                @endcan
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Código</th>
                            <th>Origen (planta)</th>
                            <th>Destino (mayorista)</th>
                            <th>Estado</th>
                            <th>Productos</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($traslados as $t)
                            @php $badge = \App\Support\RutaDistribucionCatalogo::badgeEstado($t); @endphp
                            <tr>
                                <td class="font-weight-bold text-nowrap">{{ $t->codigo }}</td>
                                <td>{{ $t->almacenPlantaOrigen?->nombre ?? '—' }}</td>
                                <td>{{ $t->almacenMayoristaDestino?->nombre ?? '—' }}</td>
                                <td><span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span></td>
                                <td>{{ $t->detallesTraslado->count() }}</td>
                                <td class="text-right">
                                    <a href="{{ route(($rutaPrefijo ?? 'logistica.traslados-planta').'.show', $t) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay traslados registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($traslados->hasPages())
            <div class="card-footer">{{ $traslados->links() }}</div>
        @endif
    </div>
</div>
@endsection
