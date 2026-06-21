{{-- Lista de envíos dentro de fila expandible (con filtro opcional por lista) --}}
@php
    $filtroId = $filtroId ?? null;
    $placeholderFiltro = $placeholderFiltro ?? 'Buscar código, estado o destino...';
    $lista = $lista ?? [];
@endphp
@if($filtroId)
<div class="px-3 py-2 border-bottom bg-white">
    <label class="text-muted small mb-1 d-block">Filtrar envíos</label>
    <input type="text"
           class="form-control form-control-sm filtro-detalle-envios"
           data-lista="lista-{{ $filtroId }}"
           placeholder="{{ $placeholderFiltro }}">
</div>
@endif
<ul @if($filtroId) id="lista-{{ $filtroId }}" @endif class="list-group list-group-flush mb-0 lista-detalle-envios">
    @forelse($lista as $envio)
    @php
        $textoFiltro = strtolower(implode(' ', array_filter([
            (string) ($envio['externo_envio_id'] ?? ''),
            (string) ($envio['nombre_remitente'] ?? ''),
            (string) ($envio['estado_etiqueta'] ?? $envio['estado'] ?? ''),
            (string) ($envio['destino'] ?? ''),
        ])));
        $estadoVisible = $envio['estado_etiqueta'] ?? $envio['estado'] ?? '';
        $detalleUrl = $envio['ver_url']
            ?? (isset($envio['id']) ? route('logistica.asignaciones.show', $envio['id']) : '#');
    @endphp
    <li class="list-group-item d-flex justify-content-between align-items-center py-2 fila-detalle-envio"
        data-texto="{{ $textoFiltro }}">
        <span>
            <strong>{{ $envio['externo_envio_id'] ?? '#'.$envio['id'] }}</strong>
            <span class="text-muted small ml-2">{{ $envio['nombre_remitente'] ?? '' }}</span>
            @if($estadoVisible !== '')
                <span class="badge badge-light border ml-1">{{ $estadoVisible }}</span>
            @endif
            @if(!empty($envio['destino']))
                <span class="text-muted small d-block mt-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $envio['destino'] }}</span>
            @endif
        </span>
        <a href="{{ $detalleUrl }}" class="btn btn-outline-success btn-sm flex-shrink-0 ml-2" title="Ver detalle">
            <i class="fas fa-eye mr-1"></i> Detalle
        </a>
    </li>
    @empty
    <li class="list-group-item text-muted text-center py-3 sin-envios-detalle">Sin envíos en esta sección.</li>
    @endforelse
    @if(count($lista) > 0)
    <li class="list-group-item text-muted text-center py-3 sin-coincidencias-detalle" style="display:none">Sin coincidencias con el filtro.</li>
    @endif
</ul>
