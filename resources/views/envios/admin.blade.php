@extends('layouts.app')

@section('title', 'Dashboard Logistico')
@section('page_title', 'Dashboard logistico de envios')

@section('content')
    <div id="aviso-demo-local" class="alert alert-info d-none mb-3" role="alert"></div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box" style="background:#2c5530;color:white;">
                <div class="inner">
                    <h3 id="total-envios">0</h3>
                    <p>Total envíos</p>
                </div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3 id="metric-pendientes">0</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="metric-transito">0</h3>
                    <p>En tránsito</p>
                </div>
                <div class="icon"><i class="fas fa-route"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="metric-entregados">0</h3>
                    <p>Entregados</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-purple" style="background:#6f42c1;color:white;">
                <div class="inner">
                    <h3 id="metric-asignados">0</h3>
                    <p>Asignados</p>
                </div>
                <div class="icon"><i class="fas fa-user-tag"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="metric-transportistas">0</h3>
                    <p>Transportistas</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-maroon" style="background:#d73925;color:white;">
                <div class="inner">
                    <h3 id="metric-vehiculos">0</h3>
                    <p>Vehículos activos</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="metric-rutas">0</h3>
                    <p>Rutas activas</p>
                </div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="metric-incidentes">0</h3>
                    <p>Incidentes abiertos</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Estados de distribución</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody id="tabla-estados">
                    <tr><td colspan="2" class="text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const normalizeEstado = (raw) => String(raw || 'Sin estado').toLowerCase();

        const countPorEstado = (envios) => {
            const byEstado = {};
            envios.forEach(e => {
                const estado = normalizeEstado(e.estado || e.estado_actual || e.nombre_estado);
                byEstado[estado] = (byEstado[estado] || 0) + 1;
            });
            return Object.entries(byEstado);
        };

        fetch("{{ route('envios.api.envios') }}")
            .then(r => r.json())
            .then(data => {
                const meta = data._meta || {};
                const aviso = document.getElementById('aviso-demo-local');
                if (meta.fuente === 'fusion_local') {
                    aviso.textContent = meta.mensaje || 'Datos del sistema.';
                    aviso.classList.remove('d-none');
                }

                const envios = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
                const dash = data.local_dashboard || {};

                const pendientes = envios.filter(e =>
                    normalizeEstado(e.estado || e.estado_actual).includes('pendiente')).length;
                const enTransito = envios.filter(e =>
                    normalizeEstado(e.estado || e.estado_actual).includes('en_ruta')).length;
                const entregados = envios.filter(e =>
                    normalizeEstado(e.estado || e.estado_actual).includes('entregado')).length;
                const asignados = envios.filter(e =>
                    normalizeEstado(e.estado || e.estado_actual).includes('asignado')).length;

                document.getElementById('total-envios').textContent = envios.length;
                document.getElementById('metric-pendientes').textContent = dash.envios_pendientes ?? pendientes;
                document.getElementById('metric-transito').textContent = dash.envios_en_transito ?? enTransito;
                document.getElementById('metric-entregados').textContent = dash.envios_entregados ?? entregados;
                document.getElementById('metric-asignados').textContent = dash.envios_asignados ?? asignados;
                document.getElementById('metric-transportistas').textContent = dash.transportistas ?? '—';
                document.getElementById('metric-vehiculos').textContent = dash.vehiculos_activos ?? '—';
                document.getElementById('metric-rutas').textContent = dash.rutas_activas ?? '—';
                document.getElementById('metric-incidentes').textContent = dash.incidentes_abiertos ?? '—';

                const entries = countPorEstado(envios);
                const tbody = document.getElementById('tabla-estados');
                if (!entries.length) {
                    tbody.innerHTML = '<tr><td colspan="2" class="text-muted">No hay datos de envíos locales. Ejecute el seeder demo de envíos.</td></tr>';
                    return;
                }

                tbody.innerHTML = entries
                    .sort((a, b) => b[1] - a[1])
                    .map(([estado, cantidad]) => `<tr><td>${estado}</td><td>${cantidad}</td></tr>`)
                    .join('');
            })
            .catch(() => {
                document.getElementById('tabla-estados').innerHTML =
                    '<tr><td colspan="2" class="text-danger">No se pudo cargar la información de envíos.</td></tr>';
            });
    </script>
@endpush
