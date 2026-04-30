@extends('layouts.app')

@section('title', 'Direcciones')
@section('page_title', 'Direcciones de envios')

@section('content')
    <div id="aviso-demo-local" class="alert alert-info d-none mb-3" role="alert"></div>
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Direcciones unificadas (origen y destino)</h3>
            <small class="text-muted">Direcciones registradas en la operación del sistema.</small>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Direccion</th>
                    </tr>
                </thead>
                <tbody id="tabla-direcciones">
                    <tr><td colspan="3" class="text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const getText = (value) => (value || '').toString().trim();
        const getDir = (envio, keys) => keys.map(k => envio?.[k]).find(v => getText(v) !== '') || '';

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
                const uniques = [];
                const seen = new Set();

                envios.forEach(e => {
                    const origen = getDir(e, ['direccion_origen', 'origen_direccion', 'origen']);
                    const destino = getDir(e, ['direccion_destino', 'destino_direccion', 'destino']);
                    [
                        ['Origen', origen],
                        ['Destino', destino],
                    ].forEach(([tipo, valor]) => {
                        if (!valor) return;
                        const key = `${tipo}|${valor}`;
                        if (seen.has(key)) return;
                        seen.add(key);
                        uniques.push({ tipo, valor });
                    });
                });

                const tbody = document.getElementById('tabla-direcciones');
                if (!uniques.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-warning">No hay direcciones disponibles.</td></tr>';
                    return;
                }

                tbody.innerHTML = uniques.map((d, i) =>
                    `<tr><td>${i + 1}</td><td>${d.tipo}</td><td>${d.valor}</td></tr>`
                ).join('');
            })
            .catch(() => {
                document.getElementById('tabla-direcciones').innerHTML =
                    '<tr><td colspan="3" class="text-danger">No se pudo consultar la información de direcciones.</td></tr>';
            });
    </script>
@endpush
