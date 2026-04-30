@extends('reportes.pdf.layout')

@section('content')
    <!-- Título y Filtros -->
    <div class="report-title">
        <h2>Reporte de Ventas</h2>
        <p>
            Período: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} 
            al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        </p>
    </div>

    <!-- Resumen Ejecutivo (KPIs) -->
    @php
        $totalVentas = $datos->sum(fn($v) => $v->cantidad * $v->preciounitario);
        $totalVolumen = $datos->sum('cantidad');
        $transacciones = $datos->count();
        $ticketPromedio = $transacciones > 0 ? $totalVentas / $transacciones : 0;
    @endphp

    <table class="summary-cards">
        <tr>
            <td class="card">
                <span class="label">Ingreso Total</span>
                <span class="value">Bs. {{ number_format($totalVentas, 2) }}</span>
            </td>
            <td class="card">
                <span class="label">Volumen Vendido</span>
                <span class="value">{{ number_format($totalVolumen, 0) }} Unidades</span>
            </td>
            <td class="card">
                <span class="label">Transacciones</span>
                <span class="value">{{ $transacciones }}</span>
            </td>
            <td class="card">
                <span class="label">Ticket Promedio</span>
                <span class="value">Bs. {{ number_format($ticketPromedio, 2) }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabla de Datos -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%">Fecha</th>
                <th style="width: 20%">Cliente</th>
                <th style="width: 18%">Cultivo / Lote</th>
                <th style="width: 15%" class="numeric">Cantidad</th>
                <th style="width: 15%" class="numeric">Precio Unit. (Bs)</th>
                <th style="width: 20%" class="numeric">Total (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $venta)
                <tr>
                    <td>{{ $venta->fechaventa instanceof \Carbon\Carbon ? $venta->fechaventa->format('d/m/Y') : $venta->fechaventa }}</td>
                    <td><b>{{ $venta->cliente ?? 'Consumidor Final' }}</b></td>
                    <td>
                        {{ $venta->produccion->lote->cultivo->nombre ?? '-' }}<br>
                        <small style="color:#666; font-size:8px;">{{ $venta->produccion->lote->nombre ?? '' }}</small>
                    </td>
                    <td class="numeric">
                        {{ number_format($venta->cantidad, 2) }} 
                        <small>{{ $venta->unidadMedida->abreviatura ?? '' }}</small>
                    </td>
                    <td class="numeric">{{ number_format($venta->preciounitario, 2) }}</td>
                    <td class="numeric" style="font-weight:bold;">
                        {{ number_format($venta->cantidad * $venta->preciounitario, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Final -->
    <div class="total-section">
        <div class="total-box">
            <span class="total-label">INGRESOS TOTALES</span>
            <span class="total-value">Bs. {{ number_format($totalVentas, 2) }}</span>
        </div>
    </div>
@endsection