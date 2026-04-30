@extends('layouts.app')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#fff2f2;border-bottom:0}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Incidentes de envío</h1>
        <a href="{{ route('logistica.incidentes.create') }}" class="btn btn-primary">Nuevo incidente</a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card x-card">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover x-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Envío/Pedido</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Reportado por</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidentes as $incidente)
                            <tr>
                                <td>{{ optional($incidente->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $incidente->externo_envio_id ?? ('Pedido #'.$incidente->pedidoid) }}</td>
                                <td>{{ $incidente->tipo }}</td>
                                <td><span class="badge badge-{{ $incidente->estado === 'resuelto' ? 'success' : 'warning' }}">{{ $incidente->estado }}</span></td>
                                <td>{{ $incidente->reportadoPor?->nombreusuario ?? 'N/D' }}</td>
                                <td>
                                    @if($incidente->estado !== 'resuelto')
                                        @can('incidentes.resolve')
                                        <form method="POST" action="{{ route('logistica.incidentes.resolve', $incidente) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input name="nota_resolucion" class="form-control form-control-sm mb-1" placeholder="Nota de resolución">
                                            <button class="btn btn-sm btn-success">Resolver</button>
                                        </form>
                                        @else
                                        <small class="text-muted">Pendiente de cierre (sin permiso)</small>
                                        @endcan
                                    @else
                                        <small class="text-muted">Resuelto</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-shield-alt mr-1"></i>No hay incidentes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $incidentes->links() }}</div>
        </div>
    </div>
</section>
@endsection

