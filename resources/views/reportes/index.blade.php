@extends('layouts.app')

@section('title', 'Reportes | AgroNexus')
@section('page_title', 'Centro de Reportes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@push('styles')
<style>
    :root {
        --primary-color: #2c5530;
        --secondary-color: #4a7c59;
    }
    
    .report-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        height: 100%;
    }
    
    .report-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }
    
    .report-card .card-header {
        padding: 25px;
        border: none;
        color: white;
    }
    
    .report-card .card-header i {
        font-size: 3rem;
        opacity: 0.9;
    }
    
    .report-card .card-body {
        padding: 25px;
    }
    
    .report-card h4 {
        font-weight: 700;
        margin-bottom: 10px;
        color: #1a252f;
    }
    
    .report-card p {
        color: #6c757d;
        margin-bottom: 20px;
        min-height: 48px;
    }
    
    .bg-ventas { background: linear-gradient(135deg, #28a745, #20c997); }
    .bg-inventario { background: linear-gradient(135deg, #1890ff, #40a9ff); }
    .bg-produccion { background: linear-gradient(135deg, #fa8c16, #ffc53d); }
    .bg-climatico { background: linear-gradient(135deg, #17a2b8, #6dd5ed); }
    .bg-actividades { background: linear-gradient(135deg, #6f42c1, #9775fa); }
    
    .stat-quick {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        border-left: 4px solid var(--primary-color);
    }
    
    .stat-quick h3 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
    }
    
    .stat-quick p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .btn-report {
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-report:hover {
        transform: scale(1.05);
    }
</style>
@endpush

@section('content')
<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-quick">
            <h3>Bs. {{ number_format($stats['ventas_mes'] ?? 0, 0) }}</h3>
            <p><i class="fas fa-chart-line mr-1"></i> Ventas del Mes</p>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-quick">
            <h3>{{ number_format($stats['produccion_mes'] ?? 0, 0) }} kg</h3>
            <p><i class="fas fa-seedling mr-1"></i> Producción del Mes</p>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-quick" style="border-left-color: #dc3545;">
            <h3 class="text-danger">{{ $stats['insumos_criticos'] ?? 0 }}</h3>
            <p><i class="fas fa-exclamation-triangle mr-1"></i> Insumos Críticos</p>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-quick" style="border-left-color: #ffc107;">
            <h3 style="color: #f57c00;">{{ $stats['actividades_pendientes'] ?? 0 }}</h3>
            <p><i class="fas fa-tasks mr-1"></i> Actividades Pendientes</p>
        </div>
    </div>
</div>

<!-- Tarjetas de Reportes -->
<div class="row">
    <!-- Reporte de Ventas -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-ventas text-center">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-body text-center">
                <h4>Reporte de Ventas</h4>
                <p>Análisis de ventas, ingresos, clientes principales y tendencias de comercialización.</p>
                <a href="{{ route('reportes.ventas') }}" class="btn btn-success btn-report">
                    <i class="fas fa-chart-bar mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    
    <!-- Reporte de Inventario -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-inventario text-center">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="card-body text-center">
                <h4>Reporte de Inventario</h4>
                <p>Control de stock, alertas de insumos críticos, consumo y valorización del inventario.</p>
                <a href="{{ route('reportes.inventario') }}" class="btn btn-primary btn-report">
                    <i class="fas fa-warehouse mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    
    <!-- Reporte de Producción -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-produccion text-center">
                <i class="fas fa-tractor"></i>
            </div>
            <div class="card-body text-center">
                <h4>Reporte de Producción</h4>
                <p>Cosechas registradas, rendimiento por lote, producción por cultivo y tendencias.</p>
                <a href="{{ route('reportes.produccion') }}" class="btn btn-warning btn-report text-white">
                    <i class="fas fa-leaf mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    
    <!-- Reporte Climático -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-climatico text-center">
                <i class="fas fa-cloud-sun"></i>
            </div>
            <div class="card-body text-center">
                <h4>Reporte Climático</h4>
                <p>Condiciones actuales, pronóstico, historial de temperatura, humedad y precipitaciones.</p>
                <a href="{{ route('reportes.climatico') }}" class="btn btn-info btn-report">
                    <i class="fas fa-thermometer-half mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    
    <!-- Reporte de Actividades -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header bg-actividades text-center">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="card-body text-center">
                <h4>Reporte de Actividades</h4>
                <p>Seguimiento de tareas agrícolas, actividades por tipo, pendientes y completadas.</p>
                <a href="{{ route('reportes.actividades') }}" class="btn btn-report" style="background: #6f42c1; color: white;">
                    <i class="fas fa-clipboard-list mr-2"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    
    <!-- Exportar Datos -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card report-card">
            <div class="card-header text-center" style="background: linear-gradient(135deg, #2c5530, #4a7c59);">
                <i class="fas fa-file-export"></i>
            </div>
            <div class="card-body text-center">
                <h4>Exportar Datos</h4>
                <p>Descarga reportes en formato CSV para análisis y seguimiento en Excel u otras herramientas.</p>
                <div class="btn-group">
                    <a href="{{ route('reportes.exportar', 'ventas') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-csv"></i> Ventas
                    </a>
                    <a href="{{ route('reportes.exportar', 'produccion') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-file-csv"></i> Producción
                    </a>
                    <a href="{{ route('reportes.exportar', 'inventario') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-csv"></i> Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información adicional -->
<div class="card mt-4">
    <div class="card-header" style="background: var(--primary-color); color: white;">
        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Acerca de los Reportes</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6><i class="fas fa-filter text-primary mr-2"></i>Filtros Avanzados</h6>
                <p class="text-muted small">Cada reporte permite filtrar por fechas, cultivos, lotes y otros criterios para obtener información específica.</p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-chart-pie text-success mr-2"></i>Gráficos Interactivos</h6>
                <p class="text-muted small">Visualiza tus datos con gráficos dinámicos que facilitan el análisis y la toma de decisiones.</p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-download text-info mr-2"></i>Exportación</h6>
                <p class="text-muted small">Descarga los datos en formato CSV compatible con Excel para análisis adicional o respaldo.</p>
            </div>
        </div>
    </div>
</div>
@endsection