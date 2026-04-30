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
        <h1 class="m-0">{{ $ruta->nombre }}</h1>
        <a href="{{ route('logistica.rutas.index') }}" class="btn btn-default">Volver</a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title">Resumen</h3></div>
                    <div class="card-body">
                        <p><strong>Transportista:</strong> {{ $ruta->transportista?->nombreusuario ?? 'Sin asignar' }}</p>
                        <p><strong>Estado:</strong> <span class="badge badge-pill {{ $ruta->estado === 'completada' ? 'badge-success' : ($ruta->estado === 'en_ruta' ? 'badge-info' : ($ruta->estado === 'cancelada' ? 'badge-danger' : 'badge-warning')) }}">{{ $ruta->estado }}</span></p>
                        <p><strong>Salida:</strong> {{ optional($ruta->fecha_salida)->format('d/m/Y H:i') }}</p>
                        <p><strong>Cierre:</strong> {{ optional($ruta->fecha_cierre)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title">Actualizar estado</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('logistica.rutas.update', $ruta) }}">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" class="form-control" required>
                                    @foreach (['planificada', 'en_ruta', 'completada', 'cancelada'] as $estado)
                                        <option value="{{ $estado }}" @selected($ruta->estado === $estado)>{{ $estado }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title">Paradas</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover x-table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Destino</th>
                                    <th>Envío</th>
                                    <th>Pedido</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ruta->paradas as $parada)
                                    <tr>
                                        <td>{{ $parada->orden }}</td>
                                        <td>{{ $parada->destino ?? 'N/D' }}</td>
                                        <td>{{ $parada->externo_envio_id ?? 'N/D' }}</td>
                                        <td>{{ $parada->pedidoid ?? 'N/D' }}</td>
                                        <td><span class="badge badge-pill {{ $parada->estado === 'entregado' ? 'badge-success' : ($parada->estado === 'en_ruta' ? 'badge-info' : 'badge-warning') }}">{{ $parada->estado }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Esta ruta no tiene paradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

