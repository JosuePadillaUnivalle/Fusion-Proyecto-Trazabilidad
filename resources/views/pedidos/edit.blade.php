@extends('layouts.app')

@section('title', 'Editar pedido #'.$pedido->pedidoid.' | AgroFusion')
@section('page_title', 'Editar pedido')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.pedido-edit-wrap { padding: 0 .25rem; }
.pedido-edit-hero {
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #3d6b42 100%);
    border-radius: 14px;
    color: #fff;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 8px 24px rgba(30, 70, 32, .18);
}
.pedido-edit-hero h4 { font-weight: 700; margin-bottom: .25rem; }
.pedido-edit-hero .hero-meta { opacity: .88; font-size: .9rem; }
.pedido-edit-hero .hero-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .78rem;
    margin-top: .65rem;
    margin-right: .35rem;
}
.pedido-edit-section {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(18, 38, 63, .07);
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.pedido-edit-section .card-header {
    background: #f8faf9;
    border-bottom: 1px solid #e8f0ea;
    padding: .9rem 1.25rem;
}
.pedido-edit-section .card-header h6 {
    margin: 0;
    font-weight: 700;
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #2c5530;
}
.pedido-edit-section .card-body { padding: 1.25rem 1.35rem; }
.pedido-picker-field {
    display: flex;
    align-items: stretch;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.pedido-picker-field:focus-within {
    border-color: #2c5530;
    box-shadow: 0 0 0 .15rem rgba(44, 85, 48, .1);
}
.pedido-picker-field .picker-display {
    flex: 1;
    border: 0;
    background: transparent;
    padding: .6rem .85rem;
    font-size: .9rem;
    min-height: 44px;
}
.pedido-picker-field .picker-actions {
    display: flex;
    border-left: 1px solid #e5e7eb;
}
.pedido-picker-field .picker-actions .btn {
    border-radius: 0;
    border: 0;
    padding: 0 .9rem;
    font-weight: 600;
    font-size: .82rem;
}
.pedido-field-label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #64748b;
    margin-bottom: .4rem;
}
.pedido-logistica-readonly {
    background: #f8faf9;
    border: 1px solid #e8f0ea;
    border-radius: 10px;
    padding: 1rem 1.15rem;
}
</style>
@endpush

@section('content')
@php
    $log = $logistica ?? [];
    $transportistaId = old('transportista_usuarioid', $log['transportista_usuarioid'] ?? '');
    $transportistaLabel = old('transportista_label', $log['transportista_nombre'] ?? '');
    $vehiculoId = old('vehiculoid', $log['vehiculoid'] ?? '');
    $vehiculoLabel = old('vehiculo_label', '');
    if ($vehiculoLabel === '' && ! empty($log['placa'])) {
        $vehiculoLabel = $log['placa'];
        if (($log['vehiculo_nombre'] ?? '—') !== '—') {
            $vehiculoLabel .= ' — '.$log['vehiculo_nombre'];
        }
    }
@endphp

<section class="content pedido-edit-wrap">
    <div class="container-fluid px-3 px-lg-4">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="pedido-edit-hero d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4><i class="fas fa-edit mr-2"></i>Editar pedido #{{ $pedido->pedidoid }}</h4>
                <div class="hero-meta">{{ $pedido->numero_solicitud }}</div>
                <span class="hero-chip"><i class="fas fa-calendar-alt"></i> {{ optional($pedido->fechapedido)->format('d/m/Y') }}</span>
                <span class="hero-chip"><i class="fas fa-tag"></i> {{ \App\Support\PedidoCatalogo::etiquetaEstado($pedido->estado) }}</span>
            </div>
            <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-sm btn-light text-dark">
                <i class="fas fa-eye mr-1"></i> Ver detalle
            </a>
        </div>

        <form action="{{ route('pedidos.update', $pedido) }}" method="POST" id="formEditarPedido">
            @csrf
            @method('PUT')

            <div class="card pedido-edit-section">
                <div class="card-header"><h6><i class="fas fa-info-circle mr-1"></i> Datos del pedido</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="pedido-field-label">Planta / destino</label>
                            <input type="text" name="nombre_planta" class="form-control"
                                   value="{{ old('nombre_planta', $pedido->nombre_planta) }}"
                                   placeholder="Nombre de planta o destino">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="pedido-field-label">Fecha entrega deseada</label>
                            <input type="date" name="fechaEntregaDeseada" class="form-control"
                                   value="{{ old('fechaEntregaDeseada', optional($pedido->fechaEntregaDeseada)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-12 form-group mb-md-0">
                            <label class="pedido-field-label">Dirección de entrega</label>
                            <input type="text" name="direccion_texto" class="form-control"
                                   value="{{ old('direccion_texto', $pedido->direccion_texto) }}"
                                   placeholder="Almacén de planta o dirección">
                        </div>
                    </div>
                </div>
            </div>

            @if($puedeAsignarLogistica)
            <div class="card pedido-edit-section">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-truck mr-1"></i> Logística — transportista y vehículo</h6>
                    @if($logistica)
                        <span class="badge badge-{{ $logistica['cargado_en_ruta'] ? 'info' : ($logistica['recibido_planta'] ? 'success' : 'secondary') }}">
                            {{ $logistica['estado_etiqueta'] }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Use los buscadores para asignar o cambiar el transportista y el vehículo que realizará el envío.
                    </p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="pedido-field-label">Transportista</label>
                            <div class="pedido-picker-field">
                                <input type="text" id="txtTransportistaEdit" class="picker-display {{ $transportistaLabel ? '' : 'text-muted' }}"
                                       readonly placeholder="Buscar transportista…"
                                       value="{{ $transportistaLabel }}">
                                <div class="picker-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnBuscarTransportistaEdit">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarTransportistaEdit" title="Quitar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="transportista_usuarioid" id="transportista_usuarioid_edit" value="{{ $transportistaId }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="pedido-field-label">Vehículo del transportista</label>
                            <div class="pedido-picker-field">
                                <input type="text" id="txtVehiculoEdit" class="picker-display {{ $vehiculoLabel ? '' : 'text-muted' }}"
                                       readonly placeholder="Primero elija transportista…"
                                       value="{{ $vehiculoLabel }}">
                                <div class="picker-actions">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarVehiculoEdit" @disabled(! $transportistaId)>
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarVehiculoEdit" title="Quitar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="vehiculoid" id="vehiculoid_edit" value="{{ $vehiculoId }}">
                            <small class="text-muted">Filtre por placa, marca o modelo.</small>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($logistica)
            <div class="card pedido-edit-section">
                <div class="card-header"><h6><i class="fas fa-truck mr-1"></i> Logística</h6></div>
                <div class="card-body pedido-logistica-readonly">
                    <strong>{{ $logistica['transportista_nombre'] }}</strong>
                    · {{ $logistica['vehiculo_nombre'] }} ({{ $logistica['placa'] }})
                    <br><small class="text-muted">{{ $logistica['estado_etiqueta'] }}</small>
                </div>
            </div>
            @endif

            <div class="card pedido-edit-section">
                <div class="card-header"><h6><i class="fas fa-clipboard-check mr-1"></i> Estado y observaciones</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="pedido-field-label">Estado del pedido</label>
                            <select name="estado" class="form-control">
                                @foreach(\App\Support\PedidoCatalogo::opcionesEstadoEnSelector($pedido) as $estadoOpt => $etiqueta)
                                    <option value="{{ $estadoOpt }}" @selected(old('estado', $pedido->estado) === $estadoOpt)>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label class="pedido-field-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="4"
                                      placeholder="Notas internas sobre el pedido…">{{ old('observaciones', $pedido->observaciones) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap mb-4" style="gap:.65rem;">
                <button type="submit" class="btn btn-success px-4 py-2">
                    <i class="fas fa-save mr-1"></i> Guardar cambios
                </button>
                <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary px-4 py-2">Cancelar</a>
            </div>
        </form>
    </div>
</section>

@once
    @include('partials.selector-catalogo-modal')
@endonce
@endsection

@push('scripts')
<script src="{{ asset('js/selector-catalogo.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.CatalogoSelector) return;

    const txtTransportista = document.getElementById('txtTransportistaEdit');
    const txtVehiculo = document.getElementById('txtVehiculoEdit');
    const inputTransportista = document.getElementById('transportista_usuarioid_edit');
    const inputVehiculo = document.getElementById('vehiculoid_edit');
    const btnBuscarVehiculo = document.getElementById('btnBuscarVehiculoEdit');

    if (!txtTransportista) return;

    function syncVehiculoBtn() {
        if (btnBuscarVehiculo) {
            btnBuscarVehiculo.disabled = !inputTransportista.value;
        }
    }

    function limpiarVehiculo() {
        inputVehiculo.value = '';
        txtVehiculo.value = '';
        txtVehiculo.classList.add('text-muted');
        txtVehiculo.placeholder = inputTransportista.value ? 'Buscar vehículo…' : 'Primero elija transportista…';
    }

    function limpiarTransportista() {
        inputTransportista.value = '';
        txtTransportista.value = '';
        txtTransportista.classList.add('text-muted');
        limpiarVehiculo();
        syncVehiculoBtn();
    }

    CatalogoSelector.register('edit_pedido_transportista', {
        endpoint: @json(route('catalogo-selector.usuarios')),
        title: 'Buscar transportista',
        searchPlaceholder: 'Nombre, usuario o correo…',
        params: { roles: 'transportista' },
        onSelect(item) {
            inputTransportista.value = item.id;
            txtTransportista.value = item.label;
            txtTransportista.classList.remove('text-muted');
            limpiarVehiculo();
            syncVehiculoBtn();
            CatalogoSelector.instances.edit_pedido_vehiculo.params = {
                transportista_usuarioid: item.id,
                solo_transportista: '1',
            };
        },
    });

    CatalogoSelector.register('edit_pedido_vehiculo', {
        endpoint: @json(route('catalogo-selector.vehiculos')),
        title: 'Buscar vehículo',
        searchPlaceholder: 'Placa, marca, modelo…',
        params: {
            transportista_usuarioid: inputTransportista.value || '',
            solo_transportista: '1',
        },
        filter: {
            param: 'solo_transportista',
            options: [
                { value: '1', label: 'Vehículos del transportista' },
                { value: '0', label: 'Toda la flota activa' },
            ],
        },
        onSelect(item) {
            inputVehiculo.value = item.id;
            txtVehiculo.value = item.label + (item.meta ? ' — ' + item.meta : '');
            txtVehiculo.classList.remove('text-muted');
        },
    });

    document.getElementById('btnBuscarTransportistaEdit')?.addEventListener('click', function () {
        CatalogoSelector.open('edit_pedido_transportista');
    });
    document.getElementById('btnLimpiarTransportistaEdit')?.addEventListener('click', limpiarTransportista);
    document.getElementById('btnBuscarVehiculoEdit')?.addEventListener('click', function () {
        if (!inputTransportista.value) return;
        CatalogoSelector.instances.edit_pedido_vehiculo.params = {
            transportista_usuarioid: inputTransportista.value,
            solo_transportista: '1',
        };
        CatalogoSelector.open('edit_pedido_vehiculo');
    });
    document.getElementById('btnLimpiarVehiculoEdit')?.addEventListener('click', limpiarVehiculo);

    syncVehiculoBtn();
});
</script>
@endpush
