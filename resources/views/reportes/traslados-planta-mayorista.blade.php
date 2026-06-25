@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $tabla = $datos['tabla'] ?? collect();
    $kpisDisplay = [
        ['value' => $kpis['total'] ?? 0, 'label' => 'Traslados'],
        ['value' => $kpis['completados'] ?? 0, 'label' => 'Completados'],
        ['value' => $kpis['en_ruta'] ?? 0, 'label' => 'En ruta'],
        ['value' => $kpis['pendientes'] ?? 0, 'label' => 'Pendientes'],
    ];
    $kpisPdf = $kpisDisplay;
    $chartConfig = [
        'type' => 'doughnut',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'traslados_planta_mayorista',
        'sheet' => 'Traslados',
        'headers' => ['Código', 'Estado', 'Origen', 'Destino', 'Transportista', 'Fecha'],
        'rows' => $tabla->map(fn ($r) => [
            $r['codigo'],
            $r['estado'],
            $r['origen'],
            $r['destino'],
            $r['transportista'],
            $r['fecha'],
        ])->all(),
    ];
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
                <div class="rpt-panel__head"><i class="fas fa-dolly mr-1"></i>Listado de traslados</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Estado</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Transportista</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabla as $fila)
                                <tr>
                                    <td>{{ $fila['codigo'] }}</td>
                                    <td>{{ $fila['estado'] }}</td>
                                    <td>{{ $fila['origen'] }}</td>
                                    <td>{{ $fila['destino'] }}</td>
                                    <td>{{ $fila['transportista'] }}</td>
                                    <td>{{ $fila['fecha'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Sin traslados en el período</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
