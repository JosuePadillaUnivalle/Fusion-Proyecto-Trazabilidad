@extends('layouts.app')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-head{font-weight:700}
.x-table thead th{background:#f2f7f3;border-bottom:0}
.x-empty{padding:28px;text-align:center;color:#6c757d}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">
            @if(auth()->user()?->can('asignaciones.create'))
                Asignación múltiple de envíos
            @else
                Mis envíos asignados
            @endif
        </h1>
        @if(!auth()->user()?->can('asignaciones.create'))
            <p class="text-muted mb-0">Listado de envíos asignados a su cuenta (solo lectura).</p>
        @endif
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(auth()->user()?->can('asignaciones.create') || auth()->user()?->can('asignaciones.multiple'))
        <div class="row">
            @can('asignaciones.multiple')
            <div class="col-md-7">
                <div class="card x-card">
                    <div class="card-header bg-success"><h3 class="card-title x-head">Asignación por lote</h3></div>
                    <div class="card-body">
                        <p>Usa el asistente para realizar asignaciones por lote con selección por dropdown y añadir productos al vehículo.</p>
                        <a href="{{ route('logistica.asignaciones.create') }}" class="btn btn-success">Abrir Wizard de Asignación</a>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        @endif

        <div class="card x-card">
            <div class="card-header"><h3 class="card-title x-head">Historial de asignaciones</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover x-table">
                    <thead>
                        <tr>
                            <th>Envío</th>
                            <th>Transportista</th>
                            <th>Ruta</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            @can('asignaciones.create')
                            <th>Acción</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $asignacion)
                            <tr>
                                <td>{{ $asignacion->externo_envio_id }}</td>
                                <td>{{ $asignacion->transportista?->nombreusuario ?? 'N/D' }}</td>
                                <td>{{ $asignacion->ruta?->nombre ?? 'N/D' }}</td>
                                <td>{{ $asignacion->vehiculo_ref ?? 'N/D' }}</td>
                                <td><span class="badge badge-pill {{ $asignacion->estado === 'entregado' ? 'badge-success' : ($asignacion->estado === 'en_ruta' ? 'badge-info' : 'badge-warning') }}">{{ $asignacion->estado }}</span></td>
                                <td>{{ optional($asignacion->fecha_asignacion)->format('d/m/Y H:i') }}</td>
                                @can('asignaciones.create')
                                <td>
                                    @if($asignacion->estado !== 'entregado')
                                        <form method="POST" action="{{ route('logistica.asignaciones.mark-delivered', $asignacion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check-circle mr-1"></i>Registrar recepción
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Recepción registrada</span>
                                    @endif
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()?->can('asignaciones.create') ? 7 : 6 }}" class="x-empty"><i class="far fa-folder-open mr-1"></i>Sin asignaciones registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $asignaciones->links() }}</div>
        </div>
    </div>
</section>

<script>
document.addEventListener('submit', function(e) {
    if (e.target.action.includes('/asignaciones/lote')) {
        const raw = document.getElementById('envios_lote').value || '';
        const ids = raw.split(',').map(v => v.trim()).filter(Boolean);
        const oldHidden = e.target.querySelectorAll('input[name="envio_ids[]"]');
        oldHidden.forEach(node => node.remove());
        ids.forEach(id => {
            const i = document.createElement('input');
            i.type = 'hidden';
            i.name = 'envio_ids[]';
            i.value = id;
            e.target.appendChild(i);
        });
    }
});
</script>
@endsection

