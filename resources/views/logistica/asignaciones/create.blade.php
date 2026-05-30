@extends('layouts.app')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0">Asignar envíos al chofer</h1>
            <p class="text-muted mb-0">Siga los 3 pasos. Puede volver atrás en cualquier momento.</p>
        </div>
        <a href="{{ route('logistica.asignaciones.index') }}" class="btn btn-outline-secondary mt-2 mt-md-0">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <div class="log-pasos" id="indicador-pasos">
            <div class="log-paso activo" data-paso="1">1. Chofer y camión</div>
            <div class="log-paso" data-paso="2">2. Elegir envíos</div>
            <div class="log-paso" data-paso="3">3. Carga (opcional)</div>
        </div>

        <div class="log-guia log-guia-compact">
            <strong>Consejo:</strong> los códigos de envío deben coincidir con los que aparecen en <em>Seguimiento de envíos</em> o al crear un envío.
        </div>

        <div id="asistente-asignacion">
            <div class="card x-card mb-3" id="step-1">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0 h5">Paso 1 — ¿Quién lleva la carga?</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Chofer responsable <span class="text-danger">*</span></label>
                            <select id="transportista" class="form-control" required>
                                <option value="">— Elija un chofer —</option>
                                @foreach(\App\Models\Usuario::where('role','transportista')->where('activo', true)->orderBy('nombre')->get() as $t)
                                    <option value="{{ $t->usuarioid }}">{{ $t->nombre }} {{ $t->apellido }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Persona con rol transportista en el sistema.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Placa o identificación del vehículo</label>
                            <input id="vehiculo_ref" class="form-control" placeholder="Ej: 1234-ABC" maxlength="80">
                            <small class="text-muted">Opcional, pero ayuda a identificar el camión en planta.</small>
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
                    <p class="text-muted">Marque con la casilla cada envío que este chofer debe transportar.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px">Elegir</th>
                                    <th>Código de envío</th>
                                    <th>Destino</th>
                                    <th>Dirección</th>
                                    <th>Situación</th>
                                </tr>
                            </thead>
                            <tbody id="envios-list">
                                @forelse(\App\Support\LocalOrgTrackFallback::enviosPayload()['data'] ?? [] as $env)
                                    <tr>
                                        <td><input type="checkbox" class="envio-checkbox" value="{{ $env['externo_envio_id'] ?? $env['id'] }}"></td>
                                        <td><strong>{{ $env['externo_envio_id'] ?? $env['id'] }}</strong></td>
                                        <td>{{ $env['destino'] ?? $env['nombre_destino'] ?? '—' }}</td>
                                        <td>{{ $env['direccion_destino'] ?? '—' }}</td>
                                        <td>{{ $env['estado'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No hay envíos disponibles. Cree envíos primero en el menú <strong>Envíos → Crear envío</strong> o <strong>Seguimiento</strong>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" id="back-step-1"><i class="fas fa-arrow-left mr-1"></i> Atrás</button>
                        <button type="button" class="btn btn-primary" id="to-step-3">Continuar <i class="fas fa-arrow-right ml-1"></i></button>
                    </div>
                </div>
            </div>

            <div class="card x-card mb-3 d-none" id="step-3">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0 h5">Paso 3 — Productos en el camión (opcional)</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Si lo desea, anote qué productos y cantidades van en el vehículo. Puede dejarlo vacío y guardar igual.</p>
                    <table class="table table-sm" id="productos-table">
                        <thead><tr><th>Producto o código</th><th>Cantidad</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                    <div class="form-row align-items-end">
                        <div class="col-md-5 form-group">
                            <label class="small">Nombre o código del producto</label>
                            <input id="producto-sku" class="form-control" placeholder="Ej: Tomate cherry">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small">Cantidad</label>
                            <input id="producto-cantidad" class="form-control" placeholder="Ej: 50" type="number" min="0" step="any">
                        </div>
                        <div class="col-md-4 form-group">
                            <button type="button" class="btn btn-outline-primary btn-block" id="add-producto">
                                <i class="fas fa-plus mr-1"></i> Agregar a la lista
                            </button>
                        </div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-secondary" id="back-step-2"><i class="fas fa-arrow-left mr-1"></i> Atrás</button>
                    <button type="button" class="btn btn-success btn-lg" id="submit-asignacion">
                        <i class="fas fa-check mr-1"></i> Guardar asignación
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    function marcarPaso(actual) {
        document.querySelectorAll('#indicador-pasos .log-paso').forEach(function (el) {
            const n = parseInt(el.getAttribute('data-paso'), 10);
            el.classList.remove('activo', 'hecho');
            if (n < actual) el.classList.add('hecho');
            if (n === actual) el.classList.add('activo');
        });
    }

    document.getElementById('to-step-2').addEventListener('click', function () {
        if (!document.getElementById('transportista').value) {
            alert('Por favor elija el chofer que llevará los envíos.');
            return;
        }
        document.getElementById('step-1').classList.add('d-none');
        document.getElementById('step-2').classList.remove('d-none');
        marcarPaso(2);
    });

    document.getElementById('back-step-1').addEventListener('click', function () {
        document.getElementById('step-2').classList.add('d-none');
        document.getElementById('step-1').classList.remove('d-none');
        marcarPaso(1);
    });

    document.getElementById('to-step-3').addEventListener('click', function () {
        const alguno = Array.from(document.querySelectorAll('.envio-checkbox')).some(function (c) { return c.checked; });
        if (!alguno) {
            alert('Marque al menos un envío de la lista.');
            return;
        }
        document.getElementById('step-2').classList.add('d-none');
        document.getElementById('step-3').classList.remove('d-none');
        marcarPaso(3);
    });

    document.getElementById('back-step-2').addEventListener('click', function () {
        document.getElementById('step-3').classList.add('d-none');
        document.getElementById('step-2').classList.remove('d-none');
        marcarPaso(2);
    });

    document.getElementById('add-producto').addEventListener('click', function (e) {
        e.preventDefault();
        const nombre = document.getElementById('producto-sku').value.trim();
        const cantidad = document.getElementById('producto-cantidad').value;
        if (!nombre || !cantidad) {
            alert('Escriba el producto y la cantidad, o pulse Guardar sin agregar productos.');
            return;
        }
        const tbody = document.querySelector('#productos-table tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = '<td>' + nombre + '</td><td>' + cantidad + '</td><td><button type="button" class="btn btn-sm btn-outline-danger remove">Quitar</button></td>';
        tbody.appendChild(tr);
        document.getElementById('producto-sku').value = '';
        document.getElementById('producto-cantidad').value = '';
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove')) {
            e.target.closest('tr').remove();
        }
    });

    document.getElementById('submit-asignacion').addEventListener('click', async function () {
        const transportista = document.getElementById('transportista').value;
        const vehiculo = document.getElementById('vehiculo_ref').value;
        const envioIds = Array.from(document.querySelectorAll('.envio-checkbox')).filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
        const productos = Array.from(document.querySelectorAll('#productos-table tbody tr')).map(function (r) {
            return { sku: r.cells[0].textContent.trim(), cantidad: parseFloat(r.cells[1].textContent.trim()) };
        });

        if (!transportista) { alert('Elija un chofer.'); return; }
        if (!envioIds.length) { alert('Marque al menos un envío.'); return; }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = new FormData();
        envioIds.forEach(function (id) { form.append('envio_ids[]', id); });
        form.append('transportista_usuarioid', transportista);
        form.append('vehiculo_ref', vehiculo);
        productos.forEach(function (p, i) {
            form.append('productos[' + i + '][sku]', p.sku);
            form.append('productos[' + i + '][cantidad]', p.cantidad);
        });

        const btn = document.getElementById('submit-asignacion');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando…';

        try {
            const res = await fetch('{{ route('logistica.asignaciones.store-batch') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: form
            });

            if (res.ok || res.redirected) {
                window.location.href = '{{ route('logistica.asignaciones.index') }}';
                return;
            }
            const text = await res.text();
            alert('No se pudo guardar. Revise los datos e intente de nuevo.');
            console.error(text);
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
