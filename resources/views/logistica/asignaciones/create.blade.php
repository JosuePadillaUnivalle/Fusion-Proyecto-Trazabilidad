@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Asignación múltiple (Wizard)</h3>

    @include('partials.flash-messages')

    <div id="wizard">
        <div class="card p-3 mb-3" id="step-1">
            <h5>Paso 1 — Seleccionar transportista y vehículo</h5>
            <div class="row">
                <div class="col-md-4">
                    <label>Transportista</label>
                    <select id="transportista" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach(\App\Models\Usuario::where('role','transportista')->where('activo', true)->get() as $t)
                            <option value="{{ $t->usuarioid }}">{{ $t->nombre }} {{ $t->apellido }} ({{ $t->nombreusuario ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Vehículo</label>
                    <select id="vehiculo_ref" class="form-control">
                        <option value="">-- Ninguno --</option>
                        @foreach(\App\Support\LocalOrgTrackFallback::vehiculosPayload()['data'] ?? [] as $v)
                            <option value="{{ $v['placa'] ?? ($v['tipoVehiculo']['nombre'] ?? '') }}">{{ $v['placa'] ?? '—' }} — {{ $v['tipoVehiculo']['nombre'] ?? ($v['tipo_vehiculo']['nombre'] ?? '') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Ruta existente (opcional)</label>
                    <select id="rutamultientregaid" class="form-control">
                        <option value="">-- Ninguna --</option>
                        @foreach(\App\Models\RutaMultiEntrega::orderByDesc('created_at')->limit(200)->get() as $r)
                            <option value="{{ $r->rutamultientregaid }}">{{ $r->nombre }} @if($r->transportista) — {{ $r->transportista->nombreusuario }} @endif</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" id="to-step-2">Siguiente</button>
            </div>
        </div>

        <div class="card p-3 mb-3 d-none" id="step-2">
            <h5>Paso 2 — Seleccionar envíos</h5>
            <p>Marca los envíos que quieres asignar al transportista/vehículo.</p>
                <table class="table table-sm">
                <thead><tr><th></th><th>SKU (ID Env.)</th><th>Cantidad</th><th>Pedido/Destino</th><th>Estado</th></tr></thead>
                <tbody id="envios-list">
                @foreach(\App\Support\LocalOrgTrackFallback::enviosPayload()['data'] ?? [] as $env)
                    @php $sku = $env['externo_envio_id'] ?? $env['id']; $qty = $env['cantidad'] ?? '' @endphp
                    <tr>
                        <td><input type="checkbox" class="envio-checkbox" data-sku="{{ $sku }}" data-cantidad="{{ $qty }}" value="{{ $sku }}"></td>
                        <td>{{ $sku }}</td>
                        <td>{{ $qty !== null && $qty !== '' ? $qty : '-' }}</td>
                        <td>{{ $env['destino'] ?? $env['nombre_destino'] ?? '-' }}</td>
                        <td>{{ $env['estado'] ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                <button class="btn btn-secondary" id="back-step-1">Atrás</button>
                <button class="btn btn-primary" id="to-step-3">Siguiente</button>
            </div>
        </div>

        <div class="card p-3 mb-3 d-none" id="step-3">
            <h5>Paso 3 — Añadir productos al vehículo</h5>
            <p>Los productos aquí se asociarán a cada asignación creada (campo JSON).</p>

            <table class="table" id="productos-table">
                <thead><tr><th>SKU/Nombre</th><th>Cantidad</th><th></th></tr></thead>
                <tbody></tbody>
            </table>

            <div class="d-flex gap-2">
                        <input id="producto-sku" class="form-control" placeholder="SKU o nombre">
                        <input id="producto-cantidad" class="form-control" placeholder="Cantidad" type="number">
                        <button class="btn btn-outline-primary" id="add-producto">Añadir</button>
                    </div>

            <div class="mt-3">
                <button class="btn btn-secondary" id="back-step-2">Atrás</button>
                <button class="btn btn-success" id="submit-wizard">Asignar selecciones</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('to-step-2').addEventListener('click', () => {
    if (!document.getElementById('transportista').value) { alert('Selecciona un transportista'); return; }
    document.getElementById('step-1').classList.add('d-none');
    document.getElementById('step-2').classList.remove('d-none');
});
document.getElementById('back-step-1').addEventListener('click', () => {
    document.getElementById('step-2').classList.add('d-none');
    document.getElementById('step-1').classList.remove('d-none');
});
document.getElementById('to-step-3').addEventListener('click', () => {
    const any = Array.from(document.querySelectorAll('.envio-checkbox')).some(c => c.checked);
    if (!any) { alert('Selecciona al menos un envío'); return; }
    // Prefill productos table using selected envíos (SKU = envío id, cantidad from payload)
    const productosTbody = document.querySelector('#productos-table tbody');
    productosTbody.innerHTML = '';
    Array.from(document.querySelectorAll('.envio-checkbox')).filter(c=>c.checked).forEach((c)=>{
        const sku = c.dataset.sku || c.value;
        const cantidad = c.dataset.cantidad || '';
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${sku}</td><td><input class="form-control producto-cant" value="${cantidad}" /></td><td><button class="btn btn-sm btn-danger remove">X</button></td>`;
        productosTbody.appendChild(tr);
    });
    document.getElementById('step-2').classList.add('d-none');
    document.getElementById('step-3').classList.remove('d-none');
});
document.getElementById('back-step-2').addEventListener('click', () => {
    document.getElementById('step-3').classList.add('d-none');
    document.getElementById('step-2').classList.remove('d-none');
});

document.getElementById('add-producto').addEventListener('click', (e) => {
    e.preventDefault();
    const sku = document.getElementById('producto-sku').value.trim();
    const cantidad = document.getElementById('producto-cantidad').value;
    if (!sku || !cantidad) { alert('SKU y cantidad requeridos'); return; }
    const tbody = document.querySelector('#productos-table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${sku}</td><td>${cantidad}</td><td><button class="btn btn-sm btn-danger remove">X</button></td>`;
    tbody.appendChild(tr);
    document.getElementById('producto-sku').value = '';
    document.getElementById('producto-cantidad').value = '';
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove')) e.target.closest('tr').remove();
});

document.getElementById('submit-wizard').addEventListener('click', async () => {
    const transportista = document.getElementById('transportista').value;
    const vehiculo = document.getElementById('vehiculo_ref').value;
    const rutamultientregaid = document.getElementById('rutamultientregaid').value || '';
    const envioIds = Array.from(document.querySelectorAll('.envio-checkbox')).filter(c=>c.checked).map(c=>c.value);
    const productos = Array.from(document.querySelectorAll('#productos-table tbody tr')).map(r=>({
        sku: r.cells[0].textContent.trim(),
        cantidad: parseFloat((r.querySelector('.producto-cant')?.value || r.cells[1].textContent).trim() || 0)
    }));

    if (!transportista) { alert('Transportista requerido'); return; }
    if (!envioIds.length) { alert('Seleccione envíos'); return; }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const form = new FormData();
    envioIds.forEach(id => form.append('envio_ids[]', id));
    form.append('transportista_usuarioid', transportista);
    form.append('vehiculo_ref', vehiculo);
    if (rutamultientregaid) form.append('rutamultientregaid', rutamultientregaid);

    // Append productos as form fields so Laravel validation sees them as arrays
    productos.forEach((p, i) => {
        form.append(`productos[${i}][sku]`, p.sku);
        form.append(`productos[${i}][cantidad]`, p.cantidad);
    });

    try {
        const res = await fetch('{{ route('logistica.asignaciones.store-batch') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            body: form
        });

        console.log('Asignación response status:', res.status);

        if (res.status >= 200 && res.status < 300) {
            // If server redirects (post-redirect), follow; otherwise reload to show flash
            if (res.redirected) {
                window.location = res.url;
            } else {
                window.location.reload();
            }
            return;
        }

        // Try to show error details
        let text = await res.text();
        console.error('Asignación error response:', res.status, text);
        alert('Error al crear asignaciones: ' + (text || res.status));
    } catch (err) {
        console.error('Fetch error', err);
        alert('Error de red al crear asignaciones: ' + err.message);
    }
});
</script>
@endpush

@endsection
