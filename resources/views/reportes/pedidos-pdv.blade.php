@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $tabla = $datos['tabla'] ?? collect();
    $chartConfig = [
        'type' => 'doughnut',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'pedidos_pdv',
        'sheet' => 'Pedidos PDV',
        'headers' => ['Solicitud', 'Punto de venta', 'Estado', 'Fecha'],
        'rows' => $tabla->map(fn ($r) => [
            $r['solicitud'],
            $r['punto'],
            $r['estado'],
            $r['fecha'],
        ])->all(),
    ];
    $kpisDisplay = [
        ['value' => $kpis['total'] ?? 0, 'label' => 'Pedidos'],
        ['value' => $kpis['recibidos'] ?? 0, 'label' => 'Recibidos'],
        ['value' => $kpis['en_transito'] ?? 0, 'label' => 'En tránsito'],
        ['value' => $kpis['pendientes'] ?? 0, 'label' => 'Pendientes'],
    ];
    $kpisPdf = $kpisDisplay;
@endphp

@section('rpt_kpis')
    @include('reportes.partials.kpis', ['kpis' => $kpisDisplay])
@endsection

@section('rpt_body')
    <div class="row">
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="rpt-panel h-100">
                <div class="rpt-panel__head"><i class="fas fa-chart-pie mr-1"></i>Por estado</div>
                <div class="rpt-panel__body">
                    <div class="rpt-chart-wrap">
                        <canvas id="rptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="rpt-panel">
                <div class="rpt-panel__head"><i class="fas fa-store mr-1"></i>Pedidos a puntos de venta</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Punto de venta</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabla as $fila)
                                <tr>
                                    <td>{{ $fila['solicitud'] }}</td>
                                    <td>{{ $fila['punto'] }}</td>
                                    <td>{{ $fila['estado'] }}</td>
                                    <td>{{ $fila['fecha'] }}</td>
                                    <td class="text-right">
                                        @if(!empty($fila['url']))
                                            <a href="{{ $fila['url'] }}" class="btn btn-xs btn-outline-primary btn-sm py-0">Ver</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin pedidos en el período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
