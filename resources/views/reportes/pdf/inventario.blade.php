@extends('reportes.pdf.layout')

@section('content')
    <div class="report-title">
        <h2>Reporte de Inventario de Insumos</h2>
        <p>Estado actual del stock — alerta automática ≤ {{ \App\Support\InsumoCatalogo::UMBRAL_ALERTA_STOCK }} unidades</p>
    </div>

    @php
        $umbral = \App\Support\InsumoCatalogo::UMBRAL_ALERTA_STOCK;
        $totalItems = $datos->count();
        $bajosDeStock = $datos->filter(fn ($i) => \App\Support\InsumoCatalogo::stockCritico((float) $i->stock))->count();
    @endphp

    <table class="summary-cards">
        <tr>
            <td class="card">
                <span class="label">Total Ítems</span>
                <span class="value">{{ $totalItems }}</span>
            </td>
            <td class="card">
                <span class="label">Umbral de alerta</span>
                <span class="value">≤ {{ $umbral }} u.</span>
            </td>
            <td class="card" style="{{ $bajosDeStock > 0 ? 'background:#fff3cd; border-color:#ffeeba;' : '' }}">
                <span class="label">Alertas de Stock</span>
                <span class="value" style="{{ $bajosDeStock > 0 ? 'color:#856404;' : '' }}">{{ $bajosDeStock }} ítems críticos</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%">Insumo / Descripción</th>
                <th style="width: 20%">Tipo</th>
                <th style="width: 20%" class="numeric">Stock actual</th>
                <th style="width: 10%">Unidad</th>
                <th style="width: 15%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $insumo)
                @php
                    $isLow = \App\Support\InsumoCatalogo::stockCritico((float) $insumo->stock);
                    $estado = $isLow ? 'Crítico' : (\App\Support\InsumoCatalogo::stockMedio((float) $insumo->stock) ? 'Atención' : 'Normal');
                @endphp
                <tr style="{{ $isLow ? 'background-color: #fff5f5;' : '' }}">
                    <td>
                        <b>{{ $insumo->nombre }}</b>
                        @if($isLow)
                            <br><span style="color:#d32f2f; font-size:9px; font-weight:bold;">⚠ STOCK BAJO</span>
                        @endif
                    </td>
                    <td>{{ $insumo->tipo->nombre ?? '-' }}</td>
                    <td class="numeric" style="{{ $isLow ? 'color:#d32f2f; font-weight:bold;' : '' }}">
                        {{ number_format($insumo->stock, 2) }}
                    </td>
                    <td>{{ $insumo->unidadMedida->abreviatura ?? '' }}</td>
                    <td>{{ $estado }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
