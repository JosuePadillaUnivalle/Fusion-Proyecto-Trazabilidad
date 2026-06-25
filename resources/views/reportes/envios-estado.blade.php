@extends('reportes.layout-reporte')

@php
    $kpis = $datos['kpis'] ?? [];
    $tabla = $datos['tabla'] ?? collect();
    $detalle = $datos['detalleMovimientos'] ?? collect();
    $chartConfig = [
        'type' => 'doughnut',
        'labels' => $datos['chart']['labels'] ?? [],
        'values' => $datos['chart']['values'] ?? [],
    ];
    $exportConfig = [
        'filename' => 'envios_estado',
        'sections' => [
            [
                'title' => 'Resumen por estado',
                'headers' => ['Estado', 'Cantidad', 'Cancelados', '%'],
                'rows' => $tabla->map(fn ($r) => [
                    $r['estado'],
                    $r['total'],
                    $r['cancelados'] ?? 0,
                    ($r['porcentaje'] ?? 0).'%',
                ])->all(),
            ],
            [
                'title' => 'Movimientos del período',
                'headers' => ['Fecha', 'Canal', 'Referencia', 'Estado', 'Origen', 'Destino', 'Transportista'],
                'rows' => $detalle->map(fn ($r) => [
                    $r['fecha'],
                    $r['canal'],
                    $r['referencia'],
                    $r['estado'],
                    $r['origen'],
                    $r['destino'],
                    $r['transportista'] ?: '—',
                ])->all(),
            ],
        ],
    ];
    $variacion = $kpis['variacion'] ?? 0;
    $kpisDisplay = [
        ['value' => number_format($kpis['total'] ?? 0), 'label' => 'Total en período'],
        ['value' => ($variacion >= 0 ? '+' : '').$variacion.'%', 'label' => 'vs. período anterior ('.($kpis['anterior'] ?? 0).')'],
        ['value' => $kpis['estados'] ?? 0, 'label' => 'Estados distintos'],
        ['value' => ($kpis['cancelacion'] ?? 0).'%', 'label' => 'Tasa cancelación'],
    ];
    $kpisPdf = $kpisDisplay;
@endphp

@section('rpt_kpis')
    @include('reportes.partials.kpis', ['kpis' => $kpisDisplay])
@endsection

@section('rpt_body')
    <div class="row">
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="rpt-panel h-100">
                <div class="rpt-panel__head"><i class="fas fa-chart-pie mr-1"></i>Distribución por estado</div>
                <div class="rpt-panel__body">
                    <div class="rpt-chart-wrap">
                        <canvas id="rptChart"></canvas>
                    </div>
                    @if($tabla->isEmpty())
                        <p class="text-muted text-center small mb-0 mt-2">Sin datos en el período seleccionado.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="rpt-panel">
                <div class="rpt-panel__head"><i class="fas fa-table mr-1"></i>Resumen por estado</div>
                <div class="rpt-panel__body rpt-panel__body--table table-responsive">
                    <table class="table table-hover mb-0" id="rptTable">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Cancelados</th>
                                <th class="text-right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tabla as $fila)
                                <tr>
                                    <td>{{ $fila['estado'] }}</td>
                                    <td class="text-right font-weight-bold">{{ $fila['total'] }}</td>
                                    <td class="text-right">{{ $fila['cancelados'] ?? 0 }}</td>
                                    <td class="text-right">{{ $fila['porcentaje'] ?? 0 }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin registros</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="rpt-panel mt-3">
        <div class="rpt-panel__head"><i class="fas fa-list mr-1"></i>Movimientos del período (detalle)</div>
        <div class="rpt-panel__body rpt-panel__body--table table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Canal</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Transportista</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detalle as $fila)
                        <tr>
                            <td class="text-nowrap">{{ $fila['fecha'] }}</td>
                            <td><span class="badge badge-light border">{{ $fila['canal'] }}</span></td>
                            <td class="font-weight-bold">{{ $fila['referencia'] }}</td>
                            <td>{{ $fila['estado'] }}</td>
                            <td>{{ $fila['origen'] }}</td>
                            <td>{{ $fila['destino'] }}</td>
                            <td>{{ $fila['transportista'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin movimientos en el período</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
