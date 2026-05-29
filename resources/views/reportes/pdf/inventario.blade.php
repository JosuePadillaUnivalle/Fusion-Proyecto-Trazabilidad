@extends('reportes.pdf.layout')

@section('content')
    <div class="report-title">
        <h2>Reporte de Inventario de Insumos</h2>
        <p>Estado actual del stock en almacenes</p>
    </div>

    @php
        $totalItems = $datos->count();
        $valorTotal = $datos->sum(fn($i) => $i->stock * ($i->preciounitario ?? 0));
        $bajosDeStock = $datos->filter(fn($i) => $i->stock <= ($i->stockminimo ?? 0))->count();
    @endphp

    <table class="summary-cards">
        <tr>
            <td class="card">
                <span class="label">Total Ítems</span>
                <span class="value">{{ $totalItems }}</span>
            </td>
            <td class="card">
                <span class="label">Valorización Total</span>
                <span class="value">Bs. {{ number_format($valorTotal, 2) }}</span>
            </td>
            <td class="card" style="{{ $bajosDeStock > 0 ? 'background:#fff3cd; border-color:#ffeeba;' : '' }}">
                <span class="label">Alertas de Stock</span>
                <span class="value" style="{{ $bajosDeStock > 0 ? 'color:#856404;' : '' }}">{{ $bajosDeStock }} Ítems
                    Críticos</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%">Insumo / Descripción</th>
                <th style="width: 15%">Tipo</th>
                <th style="width: 15%" class="numeric">Stock Actual</th>
                <th style="width: 15%" class="numeric">Mínimo</th>
                <th style="width: 10%">Unidad</th>
                <th style="width: 15%" class="numeric">Valor Unit.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $insumo)
                @php
                    $isLow = $insumo->stock <= ($insumo->stockminimo ?? 0);
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
                    <td class="numeric" style="color:#7f8c8d;">
                        {{ number_format($insumo->stockminimo, 2) }}
                    </td>
                    <td>{{ $insumo->unidadMedida->abreviatura ?? '' }}</td>
                    <td class="numeric">{{ number_format($insumo->preciounitario ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection