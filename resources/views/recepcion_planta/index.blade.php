@extends('layouts.app')

@section('title', 'Recepción en planta | AgroFusion')
@section('page_title', 'Recepción en planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Recepción en planta</li>
@endsection

@section('content')
@php $recepcionService = app(\App\Services\RecepcionPlantaEnvioService::class); @endphp
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i>Envíos en transporte hacia planta</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Transportista</th>
                            <th>Producto (ref.)</th>
                            <th>Estado</th>
                            <th>Confirmar recepción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendientes as $envio)
                            @php $sug = $recepcionService->sugerenciaDesdeEnvio($envio); @endphp
                            <tr>
                                <td><code>{{ $envio->externo_envio_id }}</code></td>
                                <td>{{ $envio->transportista?->nombreusuario ?? '—' }}</td>
                                <td>{{ $sug['producto'] ?? '—' }} @if($sug['cantidad'] > 0)<small class="text-muted">({{ number_format($sug['cantidad'], 2) }})</small>@endif</td>
                                <td>
                                    <span class="badge badge-info">{{ \App\Support\EnvioAsignacionEstadoCatalogo::etiqueta($envio->estado) }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalRecepcion{{ $envio->envioasignacionmultipleid }}">
                                        <i class="fas fa-warehouse mr-1"></i>Confirmar llegada
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No hay envíos en camino hacia planta.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pendientes->hasPages())
                <div class="card-footer">{{ $pendientes->links() }}</div>
            @endif
        </div>
    </div>
</div>

@foreach($pendientes as $envio)
    @php $sug = $recepcionService->sugerenciaDesdeEnvio($envio); @endphp
    <div class="modal fade" id="modalRecepcion{{ $envio->envioasignacionmultipleid }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('recepcion-planta.confirmar', $envio) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar recepción — {{ $envio->externo_envio_id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Se registrará la fecha y hora de confirmación y el ingreso al almacén seleccionado.</p>
                        <div class="form-group">
                            <label>Almacén de destino <span class="text-danger">*</span></label>
                            <select name="almacenid" class="form-control js-almacen-select" data-envio="{{ $envio->envioasignacionmultipleid }}" data-producto="{{ $sug['producto'] ?? '' }}" required>
                                <option value="">Seleccione…</option>
                                @foreach($almacenes as $alm)
                                    <option value="{{ $alm->almacenid }}">{{ $alm->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Insumo / cosecha a almacenar <span class="text-danger">*</span></label>
                            <select name="insumoid" class="form-control js-insumo-select" data-envio="{{ $envio->envioasignacionmultipleid }}" required>
                                <option value="">Primero elija almacén…</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad" class="form-control" step="0.001" min="0.001" value="{{ $sug['cantidad'] > 0 ? $sug['cantidad'] : '' }}" required>
                        </div>
                        <div class="form-group mb-0">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar y guardar en almacén</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-check-circle mr-2 text-success"></i>Últimas recepciones confirmadas</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Envío</th>
                            <th>Almacén</th>
                            <th>Confirmado por</th>
                            <th>Fecha confirmación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recibidos as $r)
                            <tr>
                                <td><code>{{ $r->externo_envio_id }}</code></td>
                                <td>{{ $r->almacen?->nombre ?? '—' }}</td>
                                <td>{{ $r->recepcionConfirmadaPor?->nombreusuario ?? '—' }}</td>
                                <td>{{ optional($r->fecha_recepcion_planta)->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin recepciones registradas aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recibidos->hasPages())
                <div class="card-footer">{{ $recibidos->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-almacen-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var almacenId = this.value;
        var producto = this.dataset.producto || '';
        var insumoSel = document.querySelector('.js-insumo-select[data-envio="' + this.dataset.envio + '"]');
        if (!insumoSel || !almacenId) return;
        insumoSel.innerHTML = '<option value="">Cargando…</option>';
        fetch('{{ route('recepcion-planta.insumos') }}?almacenid=' + encodeURIComponent(almacenId) + '&producto=' + encodeURIComponent(producto))
            .then(r => r.json())
            .then(function(data) {
                var html = '<option value="">Seleccione insumo…</option>';
                (data.insumos || []).forEach(function(i) {
                    var sel = data.insumo_sugerido_id == i.id ? ' selected' : '';
                    html += '<option value="' + i.id + '"' + sel + '>' + i.nombre + ' (stock: ' + i.stock + ')</option>';
                });
                insumoSel.innerHTML = html;
            });
    });
});
</script>
@endpush
