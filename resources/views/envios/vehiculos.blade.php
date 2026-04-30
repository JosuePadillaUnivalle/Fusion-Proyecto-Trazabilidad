@extends('layouts.app')

@section('title', 'Vehiculos')
@section('page_title', 'Gestion de vehiculos')

@section('content')
    <div id="aviso-demo-local" class="alert alert-info d-none mb-3" role="alert"></div>
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Vehículos</h3>
            <small class="text-muted">Vehículos registrados en la operación del sistema.</small>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Capacidad</th>
                    </tr>
                </thead>
                <tbody id="tabla-vehiculos">
                    <tr><td colspan="5" class="text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const tbody = document.getElementById('tabla-vehiculos');
        fetch("{{ route('envios.api.vehiculos') }}")
            .then(r => r.json())
            .then(data => {
                const meta = data._meta || {};
                const aviso = document.getElementById('aviso-demo-local');
                if (meta.fuente === 'fusion_local') {
                    aviso.textContent = meta.mensaje || 'Datos del sistema.';
                    aviso.classList.remove('d-none');
                }
                const rows = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-warning">No hay datos disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = rows.map((v, i) => {
                    const tipo = v.tipo_vehiculo?.nombre || v.tipoVehiculo?.nombre || v.tipo || 'N/D';
                    const estado = v.estado_vehiculo?.nombre || v.estadoVehiculo?.nombre || v.estado || 'N/D';
                    const capacidad = v.capacidad_carga || v.capacidad || 'N/D';
                    return `<tr><td>${i + 1}</td><td>${v.placa || 'N/D'}</td><td>${tipo}</td><td>${estado}</td><td>${capacidad}</td></tr>`;
                }).join('');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="5" class="text-danger">No se pudo cargar la información de vehículos.</td></tr>';
            });
    </script>
@endpush
