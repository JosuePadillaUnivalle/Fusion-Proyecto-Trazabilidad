@extends('layouts.app')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Asignar envíos al chofer</h1>
        <p class="text-muted mb-0">Asignación paso a paso: elija transportista, marque los pedidos pendientes y confirme. La carga al camión se registra después en el almacén.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="log-pasos" id="indicador-pasos">
            <div class="log-paso activo" data-paso="1">1. Transportista y vehículo</div>
            <div class="log-paso" data-paso="2">2. Elegir envíos</div>
        </div>

        <div class="log-guia log-guia-compact">
            <strong>Importante:</strong> solo puede asignar envíos cuyo pedido agrícola está <em>aceptado y listo para envío</em> (no los que siguen «En producción»).
        </div>

        <div id="asistente-asignacion">
            <div class="card x-card mb-3" id="step-1">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0 h5">Paso 1 — ¿Quién transporta los envíos?</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @include('partials.selector-catalogo', [
                                'id' => 'asignacion_transportista',
                                'name' => 'transportista_usuarioid',
                                'label' => 'Transportista',
                                'icon' => 'fa-id-card',
                                'required' => true,
                                'value' => $transportistaSeleccionado?->usuarioid ?? '',
                                'labelSelected' => $transportistaSeleccionado
                                    ? trim($transportistaSeleccionado->nombre.' '.($transportistaSeleccionado->apellido ?? ''))
                                    : '',
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
                            <input type="hidden" id="vehiculo_ref" value="{{ $vehiculoPlaca }}">
                            @include('partials.selector-catalogo', [
                                'id' => 'asignacion_vehiculo',
                                'name' => 'vehiculoid',
                                'label' => 'Vehículo asignado',
                                'icon' => 'fa-truck',
                                'required' => true,
                                'value' => '',
                                'labelSelected' => $vehiculoPlaca,
                                'endpoint' => route('catalogo-selector.vehiculos'),
                                'title' => 'Elegir vehículo',
                                'searchPlaceholder' => 'Buscar por placa, marca o modelo…',
                                'searchLabel' => 'Buscar vehículo',
                                'modalIcon' => 'fa-truck-pickup',
                                'rowIcon' => 'fa-truck',
                                'params' => ['solo_transportista' => '0'],
                                'filter' => [
                                    'param' => 'solo_transportista',
                                    'options' => [
                                        ['value' => '0', 'label' => 'Toda la flota activa'],
                                        ['value' => '1', 'label' => 'Solo del transportista elegido'],
                                    ],
                                ],
                                'help' => 'Puede asignar cualquier vehículo de la flota al transportista seleccionado.',
                            ])
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="to-step-2">
                        Continuar <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>

            <div class="card x-card mb-3 d-none" id="step-2">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0 h5">Paso 2 — ¿Qué envíos lleva?</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Marque con la casilla cada pedido o envío que este transportista debe llevar.</p>

                    @php
                        $situacionesEnvio = $enviosPendientes->pluck('estado')->filter()->unique()->sort()->values();
                        $totalEnvios = $enviosPendientes->count();
                    @endphp

                    @if($totalEnvios > 0)
                        <div class="envios-asignacion-filtros">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label class="small text-muted mb-1">Buscar</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                        </div>
                                        <input type="search" id="filtro-envios-buscar" class="form-control"
                                               placeholder="Código, destino o dirección…" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="small text-muted mb-1">Situación del envío</label>
                                    <select id="filtro-envios-situacion" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        @foreach($situacionesEnvio as $sit)
                                            <option value="{{ strtolower($sit) }}">{{ ucfirst(str_replace('_', ' ', $sit)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="small text-muted mb-1">Pedido agrícola</label>
                                    <select id="filtro-envios-pedido" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        <option value="1" selected>Listos para asignar</option>
                                        <option value="en produccion">En producción</option>
                                        <option value="0">Otros pendientes</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="filtro-envios-limpiar">
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="envios-asignacion-meta d-flex flex-wrap justify-content-between">
                            <span id="envios-filtro-contador">Mostrando {{ $totalEnvios }} de {{ $totalEnvios }} envíos</span>
                            <span id="envios-seleccion-contador">Seleccionados: 0</span>
                        </div>
                    @endif

                    <div class="envios-tabla-scroll">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px">Elegir</th>
                                    <th>Código de envío</th>
                                    <th>Destino</th>
                                    <th>Dirección</th>
                                    <th>Pedido agrícola</th>
                                    <th>Situación</th>
                                </tr>
                            </thead>
                            <tbody id="envios-list">
                                @forelse($enviosPendientes as $envio)
                                    @php
                                        $listo = \App\Support\PedidoCatalogo::listoParaLogistica($envio->pedido);
                                        $situacion = strtolower((string) ($envio->estado ?? 'pendiente'));
                                    @endphp
                                    @php
                                        $estadoPedido = (string) ($envio->pedido?->estado ?? '');
                                        $tituloCasilla = $listo
                                            ? 'Listo para asignar'
                                            : ($estadoPedido === 'en produccion'
                                                ? 'En producción agrícola — aún no listo para envío'
                                                : 'Pendiente de aceptación agrícola');
                                    @endphp
                                    <tr class="envio-row {{ $listo ? '' : 'text-muted bg-light' }}"
                                        data-codigo="{{ strtolower($envio->externo_envio_id) }}"
                                        data-destino="{{ strtolower($envio->pedido?->nombre_planta ?? '') }}"
                                        data-direccion="{{ strtolower($envio->pedido?->direccion_texto ?? '') }}"
                                        data-situacion="{{ $situacion }}"
                                        data-listo="{{ $listo ? '1' : '0' }}"
                                        data-estado-pedido="{{ $estadoPedido }}">
                                        <td>
                                            <input type="checkbox" class="envio-checkbox" value="{{ $envio->externo_envio_id }}"
                                                   @disabled(! $listo)
                                                   title="{{ $tituloCasilla }}">
                                        </td>
                                        <td><strong>{{ $envio->externo_envio_id }}</strong></td>
                                        <td>{{ $envio->pedido?->nombre_planta ?? '—' }}</td>
                                        <td>{{ $envio->pedido?->direccion_texto ?? '—' }}</td>
                                        <td>
                                            @if($envio->pedido)
                                                <span class="badge {{ $listo ? 'badge-success' : 'badge-warning' }}">
                                                    {{ \App\Support\PedidoCatalogo::etiquetaEstado($envio->pedido->estado) }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $envio->estado ?? 'pendiente' }}</td>
                                    </tr>
                                @empty
                                    <tr id="envios-lista-vacia">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No hay envíos pendientes. Registre una <strong>nueva solicitud</strong> en Envíos; producción agrícola debe aceptarla primero.
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="envios-sin-filtro" class="d-none">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Ningún envío coincide con los filtros. <a href="#" id="envios-sin-filtro-limpiar">Limpiar filtros</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" id="back-step-1"><i class="fas fa-arrow-left mr-1"></i> Atrás</button>
                        <button type="button" class="btn btn-success btn-lg" id="submit-asignacion">
                            <i class="fas fa-check mr-1"></i> Guardar asignación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('logistica.partials.modal-asignacion-exito')

@push('scripts')
<script>
(function () {
    function transportistaId() {
        return document.querySelector('#selector_wrap_asignacion_transportista .selector-catalogo-value')?.value || '';
    }

    function limpiarVehiculo() {
        const wrap = document.getElementById('selector_wrap_asignacion_vehiculo');
        if (!wrap) return;
        const hidden = wrap.querySelector('.selector-catalogo-value');
        const label = wrap.querySelector('.selector-catalogo-label');
        if (hidden) hidden.value = '';
        if (label) {
            label.value = '';
            label.classList.add('text-muted');
            label.placeholder = 'Clic en Buscar para elegir…';
        }
        document.getElementById('vehiculo_ref').value = '';
    }

    function syncParamsVehiculo() {
        if (!window.CatalogoSelector?.instances?.asignacion_vehiculo) return;
        CatalogoSelector.instances.asignacion_vehiculo.params = {
            transportista_usuarioid: transportistaId(),
            solo_transportista: '0',
        };
    }

    document.getElementById('selector_wrap_asignacion_transportista')?.addEventListener('selector-catalogo:change', function () {
        limpiarVehiculo();
        syncParamsVehiculo();
    });

    document.getElementById('selector_wrap_asignacion_vehiculo')?.addEventListener('selector-catalogo:change', function (e) {
        document.getElementById('vehiculo_ref').value = e.detail?.label || '';
    });

    document.addEventListener('DOMContentLoaded', syncParamsVehiculo);

    const totalEnvios = {{ $enviosPendientes->count() }};
    const filtroBuscar = document.getElementById('filtro-envios-buscar');
    const filtroSituacion = document.getElementById('filtro-envios-situacion');
    const filtroPedido = document.getElementById('filtro-envios-pedido');
    const filtroContador = document.getElementById('envios-filtro-contador');
    const seleccionContador = document.getElementById('envios-seleccion-contador');
    const filaSinFiltro = document.getElementById('envios-sin-filtro');

    function actualizarSeleccionContador() {
        if (!seleccionContador) return;
        const n = document.querySelectorAll('.envio-checkbox:checked').length;
        seleccionContador.textContent = 'Seleccionados: ' + n;
    }

    function aplicarFiltrosEnvios() {
        const filas = document.querySelectorAll('#envios-list .envio-row');
        if (!filas.length) return;

        const q = (filtroBuscar?.value || '').trim().toLowerCase();
        const sit = filtroSituacion?.value || '';
        const ped = filtroPedido?.value || '';
        let visibles = 0;

        filas.forEach(function (row) {
            const codigo = row.dataset.codigo || '';
            const destino = row.dataset.destino || '';
            const direccion = row.dataset.direccion || '';
            const coincideTexto = !q || codigo.includes(q) || destino.includes(q) || direccion.includes(q);
            const coincideSit = !sit || (row.dataset.situacion || '') === sit;
            let coincidePed = true;
            if (ped === '1') {
                coincidePed = (row.dataset.listo || '') === '1';
            } else if (ped === 'en produccion') {
                coincidePed = (row.dataset.estadoPedido || '') === 'en produccion';
            } else if (ped === '0') {
                coincidePed = (row.dataset.listo || '') !== '1' && (row.dataset.estadoPedido || '') !== 'en produccion';
            }
            const mostrar = coincideTexto && coincideSit && coincidePed;

            row.classList.toggle('d-none-filtro', !mostrar);
            if (mostrar) visibles++;
        });

        if (filtroContador) {
            filtroContador.textContent = 'Mostrando ' + visibles + ' de ' + totalEnvios + ' envíos';
        }
        if (filaSinFiltro) {
            filaSinFiltro.classList.toggle('d-none', visibles > 0);
        }
    }

    function limpiarFiltrosEnvios() {
        if (filtroBuscar) filtroBuscar.value = '';
        if (filtroSituacion) filtroSituacion.value = '';
        if (filtroPedido) filtroPedido.value = '';
        aplicarFiltrosEnvios();
    }

    filtroBuscar?.addEventListener('input', aplicarFiltrosEnvios);
    filtroSituacion?.addEventListener('change', aplicarFiltrosEnvios);
    filtroPedido?.addEventListener('change', aplicarFiltrosEnvios);
    document.getElementById('filtro-envios-limpiar')?.addEventListener('click', limpiarFiltrosEnvios);
    document.getElementById('envios-sin-filtro-limpiar')?.addEventListener('click', function (e) {
        e.preventDefault();
        limpiarFiltrosEnvios();
    });
    document.getElementById('envios-list')?.addEventListener('change', function (e) {
        if (e.target.classList.contains('envio-checkbox')) {
            actualizarSeleccionContador();
        }
    });
    actualizarSeleccionContador();

    function marcarPaso(actual) {
        document.querySelectorAll('#indicador-pasos .log-paso').forEach(function (el) {
            const n = parseInt(el.getAttribute('data-paso'), 10);
            el.classList.remove('activo', 'hecho');
            if (n < actual) el.classList.add('hecho');
            if (n === actual) el.classList.add('activo');
        });
    }

    document.getElementById('to-step-2').addEventListener('click', function () {
        if (!transportistaId()) {
            alert('Por favor elija el transportista que llevará los envíos.');
            return;
        }
        if (!document.getElementById('vehiculo_ref').value) {
            alert('Elija el vehículo (placa) que usará para estos envíos.');
            return;
        }
        document.getElementById('step-1').classList.add('d-none');
        document.getElementById('step-2').classList.remove('d-none');
        if (filtroPedido) {
            filtroPedido.value = '1';
            aplicarFiltrosEnvios();
        }
        marcarPaso(2);
    });

    document.getElementById('back-step-1').addEventListener('click', function () {
        document.getElementById('step-2').classList.add('d-none');
        document.getElementById('step-1').classList.remove('d-none');
        marcarPaso(1);
    });

    document.getElementById('submit-asignacion').addEventListener('click', async function () {
        const transportista = transportistaId();
        const vehiculo = document.getElementById('vehiculo_ref').value;
        const envioIds = Array.from(document.querySelectorAll('.envio-checkbox')).filter(function (c) { return c.checked; }).map(function (c) { return c.value; });

        if (!transportista) { alert('Elija un transportista.'); return; }
        if (!envioIds.length) { alert('Marque al menos un envío aceptado por producción agrícola.'); return; }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = new FormData();
        envioIds.forEach(function (id) { form.append('envio_ids[]', id); });
        form.append('transportista_usuarioid', transportista);
        form.append('vehiculo_ref', vehiculo);

        const btn = document.getElementById('submit-asignacion');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…';

        try {
            const res = await fetch('{{ route('logistica.asignaciones.store-batch') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: form
            });

            const json = await res.json().catch(function () { return null; });

            if (res.ok && json?.ok) {
                if (window.ModalAsignacionExito) {
                    window.ModalAsignacionExito.abrir(json);
                } else {
                    window.location.href = json.urls?.listado || '{{ route('logistica.asignaciones.create') }}';
                }
                return;
            }

            const msg = json?.message || 'No se pudo guardar. Revise los datos e intente de nuevo.';
            alert(msg);
        } catch (err) {
            alert('Error de conexión. Verifique su internet e intente otra vez.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Guardar asignación';
        }
    });
})();
</script>
@endpush
@endsection
