@extends('layouts.app')

@section('title', 'Reportes de distribución')
@section('page_title', 'Reportes de distribución de envíos')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card p-3">Total asignaciones<br><strong>{{ $counts['total'] }}</strong></div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">Pendientes<br><strong>{{ $counts['pendientes'] }}</strong></div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">Asignados<br><strong>{{ $counts['asignados'] }}</strong></div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">En ruta<br><strong>{{ $counts['en_ruta'] }}</strong></div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card p-3">Stock productos distribución (todas las bodegas)<br><strong>{{ number_format($counts['stock_productos_todas_bodegas'] ?? 0, 2) }}</strong></div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">Líneas inventario almacén–envío<br><strong>{{ $counts['lineas_inventario_envio'] ?? 0 }}</strong></div>
        </div>
    </div>

    <h5 class="mt-2">Top transportistas por asignaciones</h5>
    <table class="table table-sm table-bordered bg-white">
        <thead><tr><th>Transportista</th><th>Cantidad</th></tr></thead>
        <tbody>
        @forelse($topTransportistas as $t)
            <tr>
                <td>{{ optional(\App\Models\Usuario::find($t->transportista_usuarioid))->nombre ?? 'N/A' }}</td>
                <td>{{ $t->c }}</td>
            </tr>
        @empty
            <tr><td colspan="2" class="text-muted">Sin datos.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div id="aviso-demo-local" class="alert alert-info d-none mb-3" role="alert"></div>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Envíos por estado</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Estado</th><th>Cantidad</th></tr></thead>
                        <tbody id="rep-estados"><tr><td colspan="2" class="text-muted">Cargando...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Envíos por destino</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Destino</th><th>Cantidad</th></tr></thead>
                        <tbody id="rep-destino"><tr><td colspan="2" class="text-muted">Cargando...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const toArray = (data) => Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
        const countBy = (items, resolver) => {
            const map = {};
            items.forEach(item => {
                const key = (resolver(item) || 'Sin dato').toString().trim() || 'Sin dato';
                map[key] = (map[key] || 0) + 1;
            });
            return Object.entries(map).sort((a, b) => b[1] - a[1]);
        };

        fetch("{{ route('envios.api.envios') }}")
            .then(r => r.json())
            .then(raw => {
                const meta = raw._meta || {};
                const aviso = document.getElementById('aviso-demo-local');
                if (meta.fuente === 'fusion_local') {
                    aviso.textContent = meta.mensaje || 'Datos del sistema.';
                    aviso.classList.remove('d-none');
                }
                const envios = toArray(raw);
                const estados = countBy(envios, e => e.estado || e.estado_actual || e.nombre_estado);
                const destinos = countBy(envios, e => e.destino || e.direccion_destino || e.destino_direccion);

                const render = (id, entries, emptyMessage) => {
                    const tbody = document.getElementById(id);
                    if (!entries.length) {
                        tbody.innerHTML = `<tr><td colspan="2" class="text-warning">${emptyMessage}</td></tr>`;
                        return;
                    }
                    tbody.innerHTML = entries.map(([k, v]) => `<tr><td>${k}</td><td>${v}</td></tr>`).join('');
                };

                render('rep-estados', estados, 'No hay datos de estado.');
                render('rep-destino', destinos, 'No hay datos de destino.');
            })
            .catch(() => {
                document.getElementById('rep-estados').innerHTML =
                    '<tr><td colspan="2" class="text-danger">Error consultando API de envíos.</td></tr>';
                document.getElementById('rep-destino').innerHTML =
                    '<tr><td colspan="2" class="text-danger">Error consultando API de envíos.</td></tr>';
            });
    </script>
@endpush
