@extends('layouts.app')

@section('title', 'Selector de ubicación')
@section('page_title', 'Selector de ubicación')

@push('styles')
<style>
.selector-card{border:0;border-radius:12px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
#ubicaciones-tbody tr{cursor:pointer}
#ubicaciones-tbody tr:hover{background:#e8f4fd}
</style>
@endpush

@section('content')
    <div class="card selector-card">
        <div class="card-header">
            <h3 class="card-title mb-0">Buscar y filtrar ubicaciones disponibles</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2">
                <i class="fas fa-info-circle mr-1"></i>
                Seleccione una ubicación y se enviará automáticamente al formulario que abrió esta pestaña.
            </div>
            <div class="form-row mb-3">
                <div class="col-md-6">
                    <input type="text" id="filtro-texto" class="form-control" placeholder="Buscar por ubicación, ciudad, pedido, lote...">
                </div>
                <div class="col-md-4">
                    <select id="filtro-grupo" class="form-control">
                        <option value="">Todas las categorías</option>
                        @foreach($ubicacionesGrupos as $grupo)
                            <option value="{{ $grupo['grupo'] }}">{{ $grupo['grupo'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-right">
                    <button type="button" id="btn-limpiar" class="btn btn-outline-secondary btn-block">Limpiar</button>
                </div>
            </div>

            <div class="table-responsive" style="max-height:65vh;">
                <table class="table table-sm table-hover">
                    <thead class="thead-light" style="position: sticky; top: 0;">
                        <tr>
                            <th>Ubicación</th>
                            <th>Categoría</th>
                            <th>Detalle</th>
                            <th style="width:120px">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="ubicaciones-tbody">
                        @foreach($ubicacionesGrupos as $grupo)
                            @foreach($grupo['items'] as $item)
                                <tr data-valor="{{ strtolower($item['valor']) }}"
                                    data-detalle="{{ strtolower($item['detalle']) }}"
                                    data-grupo="{{ $grupo['grupo'] }}"
                                    data-dir-id="{{ $item['direccionlogisticaid'] ?? '' }}">
                                    <td>{{ $item['valor'] }}</td>
                                    <td>{{ $grupo['grupo'] }}</td>
                                    <td>{{ $item['detalle'] }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary btn-seleccionar"
                                                data-ubicacion="{{ $item['valor'] }}"
                                                data-dir-id="{{ $item['direccionlogisticaid'] ?? '' }}">
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p id="sin-resultados" class="text-muted mb-0" style="display:none;">No hay coincidencias con el filtro actual.</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    const texto = document.getElementById('filtro-texto');
    const grupo = document.getElementById('filtro-grupo');
    const tbody = document.getElementById('ubicaciones-tbody');
    const sinResultados = document.getElementById('sin-resultados');
    const filas = Array.from(tbody.querySelectorAll('tr'));

    function filtrar() {
        const q = (texto.value || '').trim().toLowerCase();
        const g = grupo.value || '';
        let visibles = 0;

        filas.forEach((tr) => {
            const matchGrupo = !g || tr.dataset.grupo === g;
            const textoFila = `${tr.dataset.valor} ${tr.dataset.detalle} ${tr.dataset.grupo.toLowerCase()}`;
            const matchTexto = !q || textoFila.includes(q);
            const ok = matchGrupo && matchTexto;
            tr.style.display = ok ? '' : 'none';
            if (ok) visibles++;
        });

        sinResultados.style.display = visibles ? 'none' : '';
    }

    function seleccionar(ubicacion, direccionLogisticaId) {
        const payload = {
            ubicacion: ubicacion || '',
            direccionlogisticaid: direccionLogisticaId || '',
        };

        try {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({ type: 'almacen-ubicacion-seleccionada', payload }, window.location.origin);
            }
            localStorage.setItem('almacen_ubicacion_selector', JSON.stringify(payload));
        } catch (e) {
            // Sin bloqueo del flujo principal.
        }

        window.close();
    }

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-seleccionar');
        const fila = e.target.closest('tr');
        if (!btn && !fila) return;
        const source = btn || fila;
        seleccionar(source.dataset.ubicacion || fila.dataset.valor, source.dataset.dirId || fila.dataset.dirId || '');
    });

    texto.addEventListener('input', filtrar);
    grupo.addEventListener('change', filtrar);
    document.getElementById('btn-limpiar').addEventListener('click', function () {
        texto.value = '';
        grupo.value = '';
        filtrar();
        texto.focus();
    });
});
</script>
@endpush
