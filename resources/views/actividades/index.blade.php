@extends('layouts.app')

@section('title', 'Actividades | AgroNexus')
@section('page_title', 'Gestión de Actividades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Actividades</li>
@endsection

@push('styles')
<style>
    .small-box { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid rgba(0,0,0,0.05); transition: transform 0.3s ease; }
    .small-box:hover { transform: translateY(-2px); }
    .small-box .icon { font-size: 70px !important; }
    .small-box-green { background: linear-gradient(135deg, #28a745, #34ce57) !important; }
    .small-box-blue { background: linear-gradient(135deg, #17a2b8, #20c997) !important; }
    .small-box-yellow { background: linear-gradient(135deg, #ffc107, #ffca2c) !important; }
    .small-box-red { background: linear-gradient(135deg, #dc3545, #e74a3b) !important; }

    .actividad-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 15px; overflow: hidden; transition: all 0.3s ease; border-left: 4px solid #2c5530; }
    .actividad-card:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,0,0,0.12); }
    .actividad-card.completada { border-left-color: #28a745; }
    .actividad-card.pendiente { border-left-color: #ffc107; }
    .actividad-header { padding: 15px 20px; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f1f3f4; }
    .actividad-body { padding: 15px 20px; }
    .actividad-info { display: flex; flex-wrap: wrap; gap: 20px; }
    .actividad-info-item { flex: 1; min-width: 140px; }
    .actividad-info-item label { display: block; font-size: 0.75rem; color: #6c757d; text-transform: uppercase; margin-bottom: 3px; }
    .actividad-info-item span { font-weight: 600; color: #1a252f; }
    .actividad-footer { padding: 12px 20px; background: #f8f9fc; display: flex; justify-content: space-between; align-items: center; }
    .actividad-tipo { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: white; background: #17a2b8; }
    .view-toggle .btn.active { background: #2c5530; color: white; }
    
    .btn-realizada {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        color: white;
        font-weight: 600;
        padding: 6px 15px;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .btn-realizada:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        color: white;
    }
</style>
@endpush

@section('content')

{{-- Alertas con SweetAlert2 --}}
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '¡Completado!',
            html: '{!! session("success") !!}',
            showConfirmButton: true,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#28a745',
            timer: 5000,
            timerProgressBar: true,
            showClass: { popup: 'animate__animated animate__fadeInDown' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp' }
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: '{!! session("error") !!}',
            confirmButtonColor: '#dc3545'
        });
    });
</script>
@endif

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box small-box-green">
            <div class="inner"><h3>{{ $actividades->total() }}</h3><p>Total Actividades</p></div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
            <a href="{{ route('actividades.calendario') }}" class="small-box-footer">Ver calendario <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box small-box-yellow">
            <div class="inner"><h3>{{ $actividades->filter(fn($a) => $a->fechafin === null)->count() }}</h3><p>Pendientes</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box small-box-blue">
            <div class="inner"><h3>{{ $actividades->filter(fn($a) => $a->fechafin !== null)->count() }}</h3><p>Completadas</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box small-box-red">
            <div class="inner"><h3>{{ $actividades->filter(fn($a) => $a->fechainicio && \Carbon\Carbon::parse($a->fechainicio)->isToday())->count() }}</h3><p>Hoy</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                @can('lotes.update')
                <a href="{{ route('actividades.create') }}" class="btn btn-success mr-2"><i class="fas fa-plus mr-1"></i> Nueva Actividad</a>
                @endcan
                <a href="{{ route('actividades.calendario') }}" class="btn btn-outline-success"><i class="fas fa-calendar-alt mr-1"></i> Calendario</a>
            </div>
            <div class="col-md-6 text-md-right mt-3 mt-md-0">
                <div class="btn-group view-toggle">
                    <button type="button" class="btn btn-outline-secondary active" id="btnCardView"><i class="fas fa-th-large"></i></button>
                    <button type="button" class="btn btn-outline-secondary" id="btnTableView"><i class="fas fa-list"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="cardView">
    @forelse($actividades as $act)
        @php $esCompletada = $act->fechafin !== null; $cardClass = $esCompletada ? 'completada' : 'pendiente'; @endphp
        <div class="actividad-card {{ $cardClass }}">
            <div class="actividad-header">
                <div>
                    <span class="actividad-tipo"><i class="fas fa-tasks mr-1"></i> {{ $act->tipoActividad->nombre ?? 'Sin tipo' }}</span>
                    @if($act->prioridad)<span class="badge badge-{{ strtolower($act->prioridad->nombre) == 'alta' ? 'danger' : (strtolower($act->prioridad->nombre) == 'media' ? 'warning' : 'success') }} ml-2">{{ $act->prioridad->nombre }}</span>@endif
                </div>
                <span class="badge badge-{{ $esCompletada ? 'success' : 'warning' }}">{{ $esCompletada ? 'Completada' : 'Pendiente' }}</span>
            </div>
            <div class="actividad-body">
                <div class="actividad-info">
                    <div class="actividad-info-item"><label><i class="fas fa-map-marker-alt mr-1"></i> Lote</label><span>{{ $act->lote->nombre ?? '-' }}</span></div>
                    <div class="actividad-info-item"><label><i class="fas fa-user mr-1"></i> Responsable</label><span>{{ $act->usuario->nombre ?? '-' }}</span></div>
                    <div class="actividad-info-item"><label><i class="fas fa-calendar mr-1"></i> Inicio</label><span>{{ $act->fechainicio ? \Carbon\Carbon::parse($act->fechainicio)->format('d/m/Y') : '-' }}</span></div>
                    <div class="actividad-info-item"><label><i class="fas fa-calendar-check mr-1"></i> Fin</label><span>{{ $act->fechafin ? \Carbon\Carbon::parse($act->fechafin)->format('d/m/Y') : '-' }}</span></div>
                </div>
                @if($act->descripcion)<p class="mt-3 mb-0 text-muted"><i class="fas fa-comment mr-1"></i> {{ Str::limit($act->descripcion, 100) }}</p>@endif
            </div>
            <div class="actividad-footer">
                <small class="text-muted">@if($act->lote && $act->lote->cultivo)<span class="badge badge-light"><i class="fas fa-seedling mr-1"></i> {{ $act->lote->cultivo->nombre }}</span>@endif</small>
                <div class="d-flex align-items-center">
                    @if(!$esCompletada)
                        @can('lotes.update')
                        <form action="{{ route('actividades.marcar-realizada', $act) }}" method="POST" class="d-inline mr-2 form-realizada">
                            @csrf
                            <button type="button" class="btn btn-realizada btn-marcar-realizada" 
                                    data-tipo="{{ $act->tipoActividad->nombre ?? 'Actividad' }}"
                                    data-lote="{{ $act->lote->nombre ?? 'Sin lote' }}">
                                <i class="fas fa-check mr-1"></i> Realizada
                            </button>
                        </form>
                        @endcan
                    @endif
                    <a href="{{ route('actividades.show', $act) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    @can('lotes.update')
                    <a href="{{ route('actividades.edit', $act) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('actividades.destroy', $act) }}" method="POST" class="d-inline form-eliminar">@csrf @method('DELETE')<button type="button" class="btn btn-sm btn-danger btn-eliminar"><i class="fas fa-trash"></i></button></form>
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <div class="card"><div class="card-body text-center py-5"><i class="fas fa-tasks fa-4x text-muted mb-3"></i><h4>No hay actividades</h4><a href="{{ route('actividades.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i> Nueva Actividad</a></div></div>
    @endforelse
</div>

<div id="tableView" style="display: none;">
    <div class="card"><div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light"><tr><th>Tipo</th><th>Lote</th><th>Responsable</th><th>Descripción</th><th>Inicio</th><th>Estado</th><th style="width: 180px;">Acciones</th></tr></thead>
            <tbody>
                @forelse($actividades as $act)
                    @php $esCompletada = $act->fechafin !== null; @endphp
                    <tr>
                        <td><span class="badge badge-info">{{ $act->tipoActividad->nombre ?? '-' }}</span></td>
                        <td>{{ $act->lote->nombre ?? '-' }}</td>
                        <td>{{ $act->usuario->nombre ?? '-' }}</td>
                        <td>{{ Str::limit($act->descripcion ?? '-', 40) }}</td>
                        <td>{{ $act->fechainicio ? \Carbon\Carbon::parse($act->fechainicio)->format('d/m/Y') : '-' }}</td>
                        <td><span class="badge badge-{{ $esCompletada ? 'success' : 'warning' }}">{{ $esCompletada ? 'Completada' : 'Pendiente' }}</span></td>
                        <td>
                            @if(!$esCompletada)
                                @can('lotes.update')
                                <form action="{{ route('actividades.marcar-realizada', $act) }}" method="POST" class="d-inline form-realizada">@csrf<button type="button" class="btn btn-sm btn-success btn-marcar-realizada" data-tipo="{{ $act->tipoActividad->nombre ?? 'Actividad' }}" data-lote="{{ $act->lote->nombre ?? '' }}"><i class="fas fa-check"></i></button></form>
                                @endcan
                            @endif
                            <a href="{{ route('actividades.show', $act) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            @can('lotes.update')
                            <a href="{{ route('actividades.edit', $act) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('actividades.destroy', $act) }}" method="POST" class="d-inline form-eliminar">@csrf @method('DELETE')<button type="button" class="btn btn-sm btn-danger btn-eliminar"><i class="fas fa-trash"></i></button></form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4">No hay actividades</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
</div>

@if($actividades->hasPages())<div class="card mt-4"><div class="card-body d-flex justify-content-center">{{ $actividades->links() }}</div></div>@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    $('#btnCardView').on('click', function() { $(this).addClass('active').siblings().removeClass('active'); $('#cardView').show(); $('#tableView').hide(); });
    $('#btnTableView').on('click', function() { $(this).addClass('active').siblings().removeClass('active'); $('#tableView').show(); $('#cardView').hide(); });

    // SweetAlert para marcar como realizada
    $(document).on('click', '.btn-marcar-realizada', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var tipo = $(this).data('tipo');
        var lote = $(this).data('lote');
        
        Swal.fire({
            title: '¿Marcar como realizada?',
            html: `
                <div style="text-align: center;">
                    <i class="fas fa-clipboard-check fa-4x text-success mb-3"></i>
                    <p class="mb-2">Vas a completar la actividad:</p>
                    <p><strong class="text-primary" style="font-size: 1.2rem;">${tipo}</strong></p>
                    <p class="text-muted">Lote: <strong>${lote}</strong></p>
                    <hr>
                    <small class="text-info"><i class="fas fa-info-circle mr-1"></i>El estado del lote se actualizará automáticamente</small>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check mr-1"></i> Sí, marcar realizada',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'animated fadeInDown'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    html: '<i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Actualizando actividad y estado del lote</p>',
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
                form.submit();
            }
        });
    });

    // SweetAlert para eliminar
    $(document).on('click', '.btn-eliminar', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        Swal.fire({
            title: '¿Eliminar actividad?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush