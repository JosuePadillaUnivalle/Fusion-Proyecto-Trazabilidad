    <div class="rpt-back no-print">
        <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver al centro
        </a>
    </div>
</div>

@php
    $tienePeriodo = collect($filtrosCampos ?? [])->contains(fn ($c) => ($c['tipo'] ?? '') === 'periodo');
    $periodoTexto = $tienePeriodo
        ? (($periodo['desde'] ?? request('fecha_desde')).' - '.($periodo['hasta'] ?? request('fecha_hasta', request('fecha_fin'))))
        : '';
    $filtrosTexto = implode(' | ', $filtrosEtiquetas ?? []);
@endphp

<div class="d-none" id="rpt-print-meta"
    data-title="{{ $item['title'] }}"
    data-accent="{{ $item['accent'] ?? 'forest' }}"
    data-periodo="{{ $periodoTexto }}"
    data-filtros="{{ $filtrosTexto }}"
    data-generated="{{ now()->format('d/m/Y H:i') }}"></div>
