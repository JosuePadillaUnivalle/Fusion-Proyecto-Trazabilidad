@extends('layouts.app')

@section('title', 'Reporte de Inventario | AgroNexus')
@section('page_title', 'Reporte de Inventario de Insumos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" style="color: #2c5530;">Reportes</a></li>
    <li class="breadcrumb-item active">Inventario</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --primary-color: #2c5530;
            --inventory-blue: #1890ff;
            --inventory-red: #f5222d;
            --inventory-green: #52c41a;
            --inventory-orange: #fa8c16;
        }

        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-inventory-total { background: linear-gradient(135deg, var(--inventory-blue), #40a9ff) !important; }
        .small-box-inventory-low { background: linear-gradient(135deg, var(--inventory-red), #ff7875) !important; }
        .small-box-inventory-good { background: linear-gradient(135deg, var(--inventory-green), #73d13d) !important; }
        .small-box-inventory-value { background: linear-gradient(135deg, var(--inventory-orange), #ffa940) !important; }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            font-weight: 600;
        }

        .chart-container {
            height: 300px;
        }

        /* Styles for Table Status Badges */
        .badge-critico { background-color: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2; }
        .badge-bajo { background-color: #fff8e1; color: #f57c00; border: 1px solid #ffecb3; }
        .badge-bueno { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-optimo { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
        }
    </style>
@endpush

@section('content')
    <!-- Métricas principales -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-inventory-total">
                <div class="inner">
                    <h3>{{ $stats['total_insumos'] }}</h3>
                    <p>Total de Insumos</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <a href="{{ route('insumos.index') }}" class="small-box-footer">Ver catálogo <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-inventory-low">
                <div class="inner">
                    <h3>{{ $stats['stock_critico'] }}</h3>
                    <p>Stock Crítico</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="#alertas" class="small-box-footer">Ver alertas <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-inventory-good">
                <div class="inner">
                    <h3>{{ $stats['stock_disponible'] }}</h3>
                    <p>Stock Disponible</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-inventory-value">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['valor_total'], 0) }}</h3>
                    <p>Valor Total</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Stock por Tipo</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="stockTipoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-warehouse mr-2"></i>Almacenes</h3>
                </div>
                <div class="card-body" style="overflow-y: auto; max-height: 340px;">
                     @forelse($stockAlmacenes as $almacen)
                        @php
                            $porcentaje = $almacen->capacidadmaxima > 0 ? ($almacen->stockactual / $almacen->capacidadmaxima) * 100 : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $almacen->nombre }}</strong>
                                <small>{{ number_format($almacen->stockactual, 0) }} / {{ number_format($almacen->capacidadmaxima, 0) }} kg</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ min($porcentaje, 100) }}%; background-color: var(--primary-color);"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No hay almacenes configurados</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detalle Inventario -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Detalle de Inventario</h3>
                    <div>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'inventario', 'formato' => 'csv']) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                        </a>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'inventario', 'formato' => 'pdf']) }}" class="btn btn-sm btn-danger ml-2">
                            <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="inventarioTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Insumo</th>
                                    <th>Tipo</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Estado</th>
                                    <th>Valor Estimado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insumos as $insumo)
                                    @php
                                        $stockMin = $insumo->stockminimo ?? 10;
                                        $porcentaje = $stockMin > 0 ? ($insumo->stock / $stockMin) * 100 : 100;
                                        if ($porcentaje < 30) $estado = 'critico';
                                        elseif ($porcentaje < 80) $estado = 'bajo';
                                        elseif ($porcentaje < 150) $estado = 'bueno';
                                        else $estado = 'optimo';
                                    @endphp
                                    <tr>
                                        <td>
                                            <b>{{ $insumo->nombre }}</b><br>
                                            <small class="text-muted">{{ Str::limit($insumo->descripcion, 40) }}</small>
                                        </td>
                                        <td>{{ $insumo->tipo->nombre ?? '-' }}</td>
                                        <td>
                                            <b>{{ number_format($insumo->stock, 2) }}</b> 
                                            <small>{{ $insumo->unidadMedida->abreviatura ?? '' }}</small>
                                        </td>
                                        <td>{{ number_format($stockMin, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $estado }} px-2 py-1 text-uppercase" style="font-size: 10px;">
                                                {{ ucfirst($estado) }}
                                            </span>
                                        </td>
                                        <td>Bs. {{ number_format(($insumo->stock * $insumo->preciounitario), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            $('#inventarioTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                order: [[4, 'asc']] // Organizar por estado (críticos primero si coincide alfabeticamente)
            });

            // Gráfico
            var tiposData = @json($insumosPorTipo);
            new Chart(document.getElementById('stockTipoChart'), {
                type: 'bar',
                data: {
                    labels: tiposData.map(t => (t.tipo || 'Otros')),
                    datasets: [{
                        label: 'Stock Total',
                        data: tiposData.map(t => parseFloat(t.stock) || 0),
                        backgroundColor: '#2c5530'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        });
    </script>
@endpush