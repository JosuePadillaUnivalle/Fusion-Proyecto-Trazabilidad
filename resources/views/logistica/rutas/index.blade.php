@extends('layouts.app')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#f2f7f3;border-bottom:0}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            @can('rutas_multi.create')
                Rutas multi-entrega
            @else
                Mis rutas
            @endcan
        </h1>
        @can('rutas_multi.create')
        <a href="{{ route('logistica.rutas.create') }}" class="btn btn-primary">Nueva ruta</a>
        @endcan
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
                            <th>Nombre</th>
                            <th>Transportista</th>
                            <th>Paradas</th>
                            <th>Estado</th>
                            <th>Salida</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rutas as $ruta)
                            <tr>
                                <td>{{ $ruta->nombre }}</td>
                                <td>{{ $ruta->transportista?->nombreusuario ?? 'Sin asignar' }}</td>
                                <td>{{ $ruta->paradas_count }}</td>
                                <td><span class="badge badge-pill {{ $ruta->estado === 'completada' ? 'badge-success' : ($ruta->estado === 'en_ruta' ? 'badge-info' : ($ruta->estado === 'cancelada' ? 'badge-danger' : 'badge-warning')) }}">{{ $ruta->estado }}</span></td>
                                <td>{{ optional($ruta->fecha_salida)->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('logistica.rutas.show', $ruta) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-route mr-1"></i>No hay rutas creadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $rutas->links() }}</div>
        </div>
    </div>
</section>
@endsection

