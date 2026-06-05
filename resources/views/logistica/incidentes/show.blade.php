@extends('layouts.app')

@section('title', 'Detalle incidente | AgroFusion')
@section('page_title', 'Detalle del incidente')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.incidente-det-card{border-radius:14px;overflow:hidden}
.incidente-det-card .card-body{padding:1.5rem 1.75rem}
.incidente-det-card .card-footer{padding:1.15rem 1.75rem}
.inc-desc-box{background:#fff8f8;border-left:4px solid #dc3545;border-radius:0 10px 10px 0;padding:1.15rem 1.35rem}
.inc-res-box{background:#f0fdf4;border-left:4px solid #28a745;border-radius:0 10px 10px 0;padding:1.15rem 1.35rem}
</style>
@endpush

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card card-outline card-success elevation-1 incidente-det-card mb-3">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4">
                <h5 class="mb-0 font-weight-bold text-success"><i class="fas fa-shield-alt mr-2"></i>{{ $incidente->tipo }}</h5>
                <span class="badge badge-{{ $incidente->estado === 'resuelto' ? 'success' : 'warning' }} px-3 py-2">{{ ucfirst($incidente->estado) }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Fecha reporte</small>
                        <strong>{{ optional($incidente->created_at)->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Envío / pedido</small>
                        <strong>{{ $incidente->externo_envio_id ?? ('Pedido #'.$incidente->pedidoid) }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Reportado por</small>
                        <strong>{{ $incidente->reportadoPor?->nombreusuario ?? 'N/D' }}</strong>
                    </div>
                </div>

                <div class="mb-4 inc-desc-box">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-align-left mr-1"></i>¿Qué ocurrió?</h6>
                    <p class="mb-0 text-dark" style="white-space:pre-wrap;line-height:1.65;">{{ $incidente->descripcion }}</p>
                </div>

                @if($incidente->estado === 'resuelto')
                <div class="inc-res-box">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-check-circle mr-1"></i>Resolución</h6>
                    <p class="mb-1">{{ $incidente->nota_resolucion ?: 'Sin nota de cierre.' }}</p>
                    <small class="text-muted">
                        {{ optional($incidente->fecha_resolucion)->format('d/m/Y H:i') }}
                        @if($incidente->resueltoPor) · {{ $incidente->resueltoPor->nombreusuario }} @endif
                    </small>
                </div>
                @elseif(auth()->user()?->can('incidentes.resolve'))
                <form method="POST" action="{{ route('logistica.incidentes.resolve', $incidente) }}" class="p-4 rounded border bg-light">
                    @csrf
                    @method('PATCH')
                    <label class="small font-weight-bold d-block mb-2">Cerrar incidente</label>
                    <textarea name="nota_resolucion" class="form-control mb-3" rows="3" placeholder="Nota de resolución"></textarea>
                    <button class="btn btn-success px-4 py-2"><i class="fas fa-check mr-1"></i>Marcar resuelto</button>
                </form>
                @endif
            </div>
            <div class="card-footer bg-white d-flex flex-wrap crud-actions">
                <a href="{{ route('logistica.incidentes.index') }}" class="btn btn-outline-secondary px-4 py-2">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
                @can('incidentes.update')
                <a href="{{ route('logistica.incidentes.edit', $incidente) }}" class="btn btn-warning px-4 py-2">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                @endcan
                @can('incidentes.delete')
                <form action="{{ route('logistica.incidentes.destroy', $incidente) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este incidente?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger px-4 py-2"><i class="fas fa-trash mr-1"></i>Eliminar</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</section>
@endsection
