@extends('layouts.app')

@section('title', 'Transportistas')
@section('page_title', 'Gestion de transportistas')

@section('content')
    <div id="aviso-demo-local" class="alert alert-info d-none mb-3" role="alert"></div>
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Transportistas</h3>
            <small class="text-muted">Transportistas registrados en el sistema.</small>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-transportistas">
                    <tr><td colspan="4" class="text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const tbody = document.getElementById('tabla-transportistas');
        fetch("{{ route('envios.api.transportistas') }}")
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
                    tbody.innerHTML = '<tr><td colspan="4" class="text-warning">No hay datos disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = rows.map((t, i) => {
                    const persona = t.persona || {};
                    const nombre = [persona.nombre, persona.apellido].filter(Boolean).join(' ') || t.nombre || 'N/D';
                    const correo = t.usuario?.correo || t.correo || 'N/D';
                    const estado = t.estado?.nombre || t.estadotransportista?.nombre || t.estado || 'N/D';
                    return `<tr><td>${i + 1}</td><td>${nombre}</td><td>${correo}</td><td>${estado}</td></tr>`;
                }).join('');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="4" class="text-danger">No se pudo cargar la información de transportistas.</td></tr>';
            });
    </script>
@endpush
