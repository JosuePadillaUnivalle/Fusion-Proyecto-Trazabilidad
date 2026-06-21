@extends('layouts.app')

@section('title', 'Editar envío '.$asignacion->externo_envio_id.' | AgroFusion')
@section('page_title', 'Editar asignación')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
<style>
.asig-edit-section { margin-bottom: 1.25rem; }
.asig-edit-section .card-header {
    background: #f8faf9;
    border-bottom: 1px solid #e8f0ea;
    font-weight: 700;
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #2c5530;
}
.asig-picker-field {
    display: flex;
    align-items: stretch;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.asig-picker-field .picker-display {
    flex: 1;
    border: 0;
    background: transparent;
    padding: .6rem .85rem;
    font-size: .9rem;
    min-height: 44px;
}
.asig-picker-field .picker-actions {
    display: flex;
    border-left: 1px solid #e5e7eb;
}
.asig-picker-field .picker-actions .btn {
    border-radius: 0;
    border: 0;
    padding: 0 .85rem;
    font-size: .82rem;
}
.origen-extra-row { margin-bottom: .5rem; }
.asig-readonly-value {
    background: #f8faf9;
    border: 1px solid #e8f0ea;
    border-radius: 8px;
    padding: .6rem .85rem;
    font-size: .9rem;
    min-height: 44px;
}
</style>
@endpush

@section('content')
@php
    use App\Support\PedidoCatalogo;
    $edicionCompleta = $nivelEdicion === PedidoCatalogo::EDICION_ASIGNACION_COMPLETA;
@endphp

<div class="content-header">
    <div class="container-fluid">
        <p class="text-muted mb-0">
            {{ PedidoCatalogo::etiquetaNivelEdicionAsignacion($nivelEdicion) }}
        </p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-edit mr-2"></i>{{ $asignacion->externo_envio_id }}
                        </h3>
                        @if($pedido)
                            @php $badge = PedidoCatalogo::badgeEstadoLista(null, $pedido); @endphp
                            <span class="badge {{ $badge['clase'] }} ml-2">{{ $badge['etiqueta'] }}</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('logistica.asignaciones.update', $asignacion) }}" id="formEditarAsignacion">
                        @csrf
                        @method('PUT')
                        <div class="card-body">

                            <div class="card asig-edit-section border">
                                <div class="card-header py-2">
                                    <i class="fas fa-truck mr-1"></i> Logística
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            @include('partials.selector-catalogo', [
                                                'id' => 'edit_asignacion_transportista',
                                                'name' => 'transportista_usuarioid',
                                                'label' => 'Transportista',
                                                'icon' => 'fa-id-card',
                                                'allowEmpty' => true,
                                                'emptyLabel' => '— Sin transportista —',
                                                'placeholderEmpty' => 'Sin transportista asignado',
                                                'value' => old('transportista_usuarioid', $asignacion->transportista_usuarioid ?? ''),
                                                'labelSelected' => old('transportista_label', $asignacion->transportista
                                                    ? trim($asignacion->transportista->nombre.' '.($asignacion->transportista->apellido ?? ''))
                                                    : ''),
                                                'endpoint' => route('catalogo-selector.usuarios'),
                                                'title' => 'Elegir transportista',
                                                'searchPlaceholder' => 'Nombre, correo, teléfono o placa…',
                                                'searchLabel' => 'Buscar transportista',
                                                'modalIcon' => 'fa-truck',
                                                'rowIcon' => 'fa-user-tie',
                                                'params' => ['roles' => 'transportista'],
                                                'filter' => [
                                                    'param' => 'con_vehiculo',
                                                    'options' => [
                                                        ['value' => '', 'label' => 'Todos'],
                                                        ['value' => '1', 'label' => 'Con vehículo'],
                                                        ['value' => '0', 'label' => 'Sin vehículo'],
                                                    ],
                                                ],
                                                'help' => 'Ventana flotante con filtros; no abandona esta pantalla.',
                                            ])
                                        </div>
                                        <div class="col-md-6">
                                            @if($edicionCompleta)
                                                @include('partials.selector-catalogo', [
                                                    'id' => 'edit_asignacion_vehiculo',
                                                    'name' => 'vehiculoid',
                                                    'label' => 'Vehículo',
                                                    'icon' => 'fa-truck',
                                                    'allowEmpty' => true,
                                                    'emptyLabel' => '— Sin vehículo —',
                                                    'placeholderEmpty' => 'Sin vehículo asignado',
                                                    'value' => old('vehiculoid', $vehiculo?->vehiculoid ?? ''),
                                                    'labelSelected' => old('vehiculo_label', $vehiculoLabel),
                                                    'endpoint' => route('catalogo-selector.vehiculos'),
                                                    'title' => 'Elegir vehículo',
                                                    'searchPlaceholder' => 'Buscar por placa, marca o modelo…',
                                                    'searchLabel' => 'Buscar vehículo',
                                                    'modalIcon' => 'fa-truck-pickup',
                                                    'rowIcon' => 'fa-truck',
                                                    'params' => [
                                                        'transportista_usuarioid' => old('transportista_usuarioid', $asignacion->transportista_usuarioid ?? ''),
                                                        'solo_transportista' => '0',
                                                    ],
                                                    'filter' => [
                                                        'param' => 'solo_transportista',
                                                        'options' => [
                                                            ['value' => '0', 'label' => 'Toda la flota activa'],
                                                            ['value' => '1', 'label' => 'Solo del transportista elegido'],
                                                        ],
                                                    ],
                                                    'help' => 'Asigne transportista y vehículo juntos, o deje ambos vacíos.',
                                                ])
                                            @else
                                                <label><i class="fas fa-truck mr-1"></i> Vehículo</label>
                                                <div class="asig-readonly-value text-muted">
                                                    {{ $vehiculoLabel ?: ($asignacion->vehiculo_ref ?: 'Sin vehículo asignado') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($pedido)
                                    <div class="form-group mb-0">
                                        <label><i class="fas fa-calendar-alt mr-1"></i> Fecha de entrega deseada</label>
                                        @if($edicionCompleta)
                                            <input type="date" name="fechaEntregaDeseada" class="form-control"
                                                   value="{{ $fechaEntregaValor }}">
                                            <small class="text-muted">Si no elige fecha, se usará la de hoy al guardar.</small>
                                        @else
                                            <div class="asig-readonly-value">
                                                {{ \Carbon\Carbon::parse($fechaEntregaValor)->format('d/m/Y') }}
                                            </div>
                                        @endif
                                        @error('fechaEntregaDeseada')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if($pedido && $edicionCompleta)
                            <div class="card asig-edit-section border mb-0">
                                <div class="card-header py-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Puntos de recogida
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        Puede cambiar el almacén de recogida principal o agregar paradas adicionales antes del destino.
                                        @if($rutaEsAutomatica)
                                            <span class="d-block mt-1 text-info">La ruta actual se generó automáticamente desde estas recogidas.</span>
                                        @endif
                                    </p>

                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-1">Recogida 1 <span class="text-danger">*</span></label>
                                        <div class="asig-picker-field">
                                            <input type="text" id="txtOrigenEdit" class="picker-display {{ old('origen_direccion', $origenLabel) ? '' : 'text-muted' }}"
                                                   readonly placeholder="Buscar almacén agrícola…"
                                                   value="{{ old('origen_direccion', $origenLabel) }}">
                                            <div class="picker-actions">
                                                <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarOrigenEdit">
                                                    <i class="fas fa-search mr-1"></i> Buscar
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarOrigenEdit" title="Quitar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small id="txtOrigenCoordsEdit" class="form-text text-muted"></small>
                                        @error('origen_latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                    </div>

                                    <div id="recogidas-extra-container"></div>

                                    <button type="button" class="btn btn-sm btn-outline-success mb-2" id="btnAgregarRecogidaEdit">
                                        <i class="fas fa-plus mr-1"></i> Agregar otro almacén de recogida
                                    </button>
                                    <small class="text-muted d-block">Opcional: útil cuando el camión recoge en 2 o más almacenes.</small>

                                    <input type="hidden" name="origen_latitud" id="origen_latitud_edit"
                                           value="{{ old('origen_latitud', $pedido->origen_latitud) }}">
                                    <input type="hidden" name="origen_longitud" id="origen_longitud_edit"
                                           value="{{ old('origen_longitud', $pedido->origen_longitud) }}">
                                    <input type="hidden" name="origen_direccion" id="origen_direccion_edit"
                                           value="{{ old('origen_direccion', $origenLabel ?: $pedido->origen_direccion) }}">
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('logistica.asignaciones.show', $asignacion) }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const EDICION_COMPLETA = @json($edicionCompleta);
    const MAX_RECOGIDAS = 5;

    function transportistaId() {
        return document.querySelector('#selector_wrap_edit_asignacion_transportista .selector-catalogo-value')?.value || '';
    }

    function limpiarVehiculo() {
        const wrap = document.getElementById('selector_wrap_edit_asignacion_vehiculo');
        if (!wrap) return;
        const hidden = wrap.querySelector('.selector-catalogo-value');
        const label = wrap.querySelector('.selector-catalogo-label');
        if (hidden) hidden.value = '';
        if (label) {
            label.value = '';
            label.classList.add('text-muted');
        }
    }

    function syncParamsVehiculo() {
        if (!EDICION_COMPLETA || !window.CatalogoSelector?.instances?.edit_asignacion_vehiculo) return;
        CatalogoSelector.instances.edit_asignacion_vehiculo.params = {
            transportista_usuarioid: transportistaId(),
            solo_transportista: '0',
        };
    }

    document.getElementById('selector_wrap_edit_asignacion_transportista')?.addEventListener('selector-catalogo:change', function () {
        if (!EDICION_COMPLETA) return;
        limpiarVehiculo();
        syncParamsVehiculo();
    });

    document.addEventListener('DOMContentLoaded', syncParamsVehiculo);

    if (!EDICION_COMPLETA) {
        return;
    }

    function actualizarCoordsOrigen() {
        const lat = document.getElementById('origen_latitud_edit')?.value;
        const lng = document.getElementById('origen_longitud_edit')?.value;
        const el = document.getElementById('txtOrigenCoordsEdit');
        if (!el) return;
        el.textContent = (lat && lng) ? ('Coordenadas: ' + lat + ', ' + lng) : '';
    }

    function aplicarOrigen(item) {
        const lat = parseFloat(item.extra?.lat);
        const lng = parseFloat(item.extra?.lng);
        if (isNaN(lat) || isNaN(lng)) return;
        const label = item.label || item.extra?.nombre || '';
        document.getElementById('origen_latitud_edit').value = lat.toFixed(7);
        document.getElementById('origen_longitud_edit').value = lng.toFixed(7);
        document.getElementById('origen_direccion_edit').value = label;
        const display = document.getElementById('txtOrigenEdit');
        display.value = label;
        display.classList.remove('text-muted');
        actualizarCoordsOrigen();
    }

    function limpiarOrigen() {
        ['origen_latitud_edit', 'origen_longitud_edit', 'origen_direccion_edit'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const display = document.getElementById('txtOrigenEdit');
        if (display) {
            display.value = '';
            display.classList.add('text-muted');
        }
        actualizarCoordsOrigen();
    }

    function crearFilaRecogidaExtra(datos) {
        datos = datos || {};
        const row = document.createElement('div');
        row.className = 'origen-extra-row';
        row.innerHTML =
            '<label class="small text-muted mb-1 recogida-label">Recogida adicional</label>' +
            '<div class="asig-picker-field">' +
            '  <input type="text" class="picker-display recogida-display ' + (datos.direccion ? '' : 'text-muted') + '" readonly ' +
            '         placeholder="Buscar almacén agrícola…" value="' + (datos.direccion || '').replace(/"/g, '&quot;') + '">' +
            '  <div class="picker-actions">' +
            '    <button type="button" class="btn btn-outline-success btn-sm btn-buscar-recogida"><i class="fas fa-search mr-1"></i> Buscar</button>' +
            '    <button type="button" class="btn btn-outline-secondary btn-sm btn-quitar-recogida" title="Quitar"><i class="fas fa-times"></i></button>' +
            '  </div>' +
            '</div>' +
            '<input type="hidden" data-field="latitud" value="' + (datos.latitud || '') + '">' +
            '<input type="hidden" data-field="longitud" value="' + (datos.longitud || '') + '">' +
            '<input type="hidden" data-field="direccion" value="' + (datos.direccion || '').replace(/"/g, '&quot;') + '">';
        row.querySelector('.btn-buscar-recogida').addEventListener('click', function () {
            window._recogidaExtraActiva = row;
            CatalogoSelector.open('edit_recogida_extra');
        });
        row.querySelector('.btn-quitar-recogida').addEventListener('click', function () {
            row.remove();
            renumerarRecogidas();
        });
        return row;
    }

    function renumerarRecogidas() {
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            const n = i + 2;
            const lbl = row.querySelector('.recogida-label');
            if (lbl) lbl.textContent = 'Recogida ' + n;
            row.querySelector('[data-field="latitud"]').setAttribute('name', 'recogidas[' + i + '][latitud]');
            row.querySelector('[data-field="longitud"]').setAttribute('name', 'recogidas[' + i + '][longitud]');
            row.querySelector('[data-field="direccion"]').setAttribute('name', 'recogidas[' + i + '][direccion]');
        });
    }

    function aplicarRecogidaExtra(item) {
        const row = window._recogidaExtraActiva;
        if (!row) return;
        const lat = parseFloat(item.extra?.lat);
        const lng = parseFloat(item.extra?.lng);
        if (isNaN(lat) || isNaN(lng)) return;
        const label = item.label || item.extra?.nombre || '';
        row.querySelector('[data-field="latitud"]').value = lat.toFixed(7);
        row.querySelector('[data-field="longitud"]').value = lng.toFixed(7);
        row.querySelector('[data-field="direccion"]').value = label;
        const display = row.querySelector('.recogida-display');
        display.value = label;
        display.classList.remove('text-muted');
        window._recogidaExtraActiva = null;
    }

    document.getElementById('btnBuscarOrigenEdit')?.addEventListener('click', function () {
        if (window.CatalogoSelector) CatalogoSelector.open('edit_recogida_principal');
    });
    document.getElementById('btnLimpiarOrigenEdit')?.addEventListener('click', limpiarOrigen);

    document.getElementById('btnAgregarRecogidaEdit')?.addEventListener('click', function () {
        const total = 1 + document.querySelectorAll('.origen-extra-row').length;
        if (total >= MAX_RECOGIDAS) {
            alert('Máximo de ' + MAX_RECOGIDAS + ' puntos de recogida.');
            return;
        }
        document.getElementById('recogidas-extra-container').appendChild(crearFilaRecogidaExtra());
        renumerarRecogidas();
    });

    document.getElementById('formEditarAsignacion')?.addEventListener('submit', function (e) {
        const origenField = document.getElementById('origen_latitud_edit');
        if (origenField && !origenField.value) {
            e.preventDefault();
            alert('Seleccione el almacén de recogida principal.');
            return;
        }
        const tId = transportistaId();
        const vId = document.querySelector('#selector_wrap_edit_asignacion_vehiculo .selector-catalogo-value')?.value || '';
        if ((tId && !vId) || (!tId && vId)) {
            e.preventDefault();
            alert('Seleccione transportista y vehículo juntos, o deje ambos vacíos.');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('edit_recogida_principal', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — recogida 1',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            onSelect: aplicarOrigen,
        });

        CatalogoSelector.register('edit_recogida_extra', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — recogida adicional',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            onSelect: aplicarRecogidaExtra,
        });

        actualizarCoordsOrigen();

        @if(is_array(old('recogidas')))
            @foreach(old('recogidas') as $rec)
                document.getElementById('recogidas-extra-container')?.appendChild(crearFilaRecogidaExtra(@json($rec)));
            @endforeach
            renumerarRecogidas();
        @elseif(!empty($recogidasExtra))
            @foreach($recogidasExtra as $rec)
                document.getElementById('recogidas-extra-container')?.appendChild(crearFilaRecogidaExtra(@json($rec)));
            @endforeach
            renumerarRecogidas();
        @endif
    });
})();
</script>
@endpush
