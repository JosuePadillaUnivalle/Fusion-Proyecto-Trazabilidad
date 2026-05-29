@extends('layouts.app')

@section('title', 'Catálogos | AgroNexus')
@section('page_title', 'Gestión de Catálogos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Catálogos</li>
@endsection

@push('styles')
<style>
    .catalog-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        border: none;
    }
    .catalog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }
    .catalog-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin: 0 auto 15px;
    }
    .catalog-card .card-body {
        padding: 30px 20px;
        text-align: center;
    }
    .catalog-card h5 {
        font-weight: 700;
        color: #1a252f;
        margin-bottom: 10px;
    }
    .catalog-card p {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    .catalog-count {
        display: inline-block;
        background: #f1f3f4;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
        color: #1a252f;
        font-size: 0.85rem;
    }
    .catalog-actions {
        padding: 15px 20px;
        background: #f8f9fc;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .catalog-actions .btn {
        border-radius: 20px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    /* Colores por categoría */
    .bg-cultivos { background: linear-gradient(135deg, #28a745, #20c997); }
    .bg-actividades { background: linear-gradient(135deg, #6f42c1, #9775fa); }
    .bg-insumos { background: linear-gradient(135deg, #fd7e14, #ffc107); }
    .bg-almacenes { background: linear-gradient(135deg, #17a2b8, #6dd5ed); }
    .bg-estados { background: linear-gradient(135deg, #dc3545, #f06595); }
    .bg-unidades { background: linear-gradient(135deg, #343a40, #6c757d); }
    .bg-prioridades { background: linear-gradient(135deg, #e83e8c, #f8a5c2); }

    .section-title {
        font-weight: 700;
        color: #1a252f;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 3px solid #2c5530;
        display: inline-block;
    }
    .section-title i {
        color: #2c5530;
        margin-right: 10px;
    }
</style>
@endpush

@section('content')
<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 15px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x mr-3" style="color: #1976d2;"></i>
                <div>
                    <strong>Catálogos del Sistema</strong>
                    <p class="mb-0">Administra todos los datos maestros del sistema fusionado desde esta sección centralizada.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Producción Agrícola -->
<h4 class="section-title"><i class="fas fa-seedling"></i>Producción Agrícola</h4>
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-cultivos">
                    <i class="fas fa-leaf"></i>
                </div>
                <h5>Cultivos</h5>
                <p>Tipos de cultivos disponibles para los lotes</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['cultivos'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('cultivos.index') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('cultivos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-actividades">
                    <i class="fas fa-tasks"></i>
                </div>
                <h5>Tipos de Actividad</h5>
                <p>Clasificación de actividades agrícolas</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['tiposActividad'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('tipo-actividad.index') }}" class="btn btn-outline-purple btn-sm" style="border-color: #6f42c1; color: #6f42c1;">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('tipo-actividad.create') }}" class="btn btn-sm" style="background: #6f42c1; color: white;">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-estados">
                    <i class="fas fa-flag"></i>
                </div>
                <h5>Estados de Lote</h5>
                <p>Estados del ciclo de vida de los lotes</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['estadosLote'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('estado-lote-tipos.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('estado-lote-tipos.create') }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-prioridades">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h5>Prioridades</h5>
                <p>Niveles de prioridad para actividades</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['prioridades'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('prioridades.index') }}" class="btn btn-outline-pink btn-sm" style="border-color: #e83e8c; color: #e83e8c;">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('prioridades.create') }}" class="btn btn-sm" style="background: #e83e8c; color: white;">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Inventario y Almacenamiento -->
<h4 class="section-title"><i class="fas fa-warehouse"></i>Inventario y Almacenamiento</h4>
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-insumos">
                    <i class="fas fa-boxes"></i>
                </div>
                <h5>Tipos de Insumo</h5>
                <p>Categorías de insumos agrícolas</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['tiposInsumo'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('tipo-insumos.index') }}" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('tipo-insumos.create') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-almacenes">
                    <i class="fas fa-store"></i>
                </div>
                <h5>Tipos de Almacén</h5>
                <p>Clasificación de almacenes y silos</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['tiposAlmacen'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('tipoalmacenes.index') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('tipoalmacenes.create') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon bg-unidades">
                    <i class="fas fa-ruler"></i>
                </div>
                <h5>Unidades de Medida</h5>
                <p>Unidades para cantidades y mediciones</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['unidadesMedida'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('unidades-medida.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('unidades-medida.create') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="catalog-card">
            <div class="card-body">
                <div class="catalog-icon" style="background: linear-gradient(135deg, #2c5530, #4a7c59);">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h5>Estados de Insumo</h5>
                <p>Estados de los insumos en lotes</p>
                <span class="catalog-count"><i class="fas fa-database mr-1"></i> {{ $counts['estadosInsumo'] ?? 0 }} registros</span>
            </div>
            <div class="catalog-actions">
                <a href="{{ route('estado-lote-insumos.index') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-list mr-1"></i> Ver
                </a>
                <a href="{{ route('estado-lote-insumos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection