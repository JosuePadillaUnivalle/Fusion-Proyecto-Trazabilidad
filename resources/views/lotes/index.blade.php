@extends('layouts.app')

@section('title', 'Lotes | Fusion-Proyectos')
@section('page_title', 'Gestión de Lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Lotes</li>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
        }

        .stats-row {
            margin-bottom: 20px;
        }

        .stat-box {
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s;
            color: white;
        }

        .stat-box:hover {
            transform: translateY(-3px);
        }

        .stat-box h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: white;
        }

        .stat-box p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
        }

        .stat-box.produccion {
            background: linear-gradient(135deg, #388e3c, #66bb6a);
        }

        .stat-box.produccion h3 {
            color: white;
        }

        .stat-box.sembrado {
            background: linear-gradient(135deg, #0288d1, #4fc3f7);
        }

        .stat-box.sembrado h3 {
            color: white;
        }

        .stat-box.cosechado {
            background: linear-gradient(135deg, #f57c00, #ffb74d);
        }

        .stat-box.cosechado h3 {
            color: white;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            padding: 15px 20px;
        }

        .lote-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .lote-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .lote-card.en-produccion {
            border-left-color: #28a745;
        }

        .lote-card.sembrado {
            border-left-color: #17a2b8;
        }

        .lote-card.cosechado {
            border-left-color: #ffc107;
        }

        .lote-card.disponible {
            border-left-color: #6c757d;
        }

        .lote-card.en-preparacion {
            border-left-color: #6f42c1;
        }

        .lote-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lote-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
        }

        .lote-body {
            padding: 15px 20px;
        }

        .lote-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .lote-info-item {
            flex: 1;
            min-width: 120px;
        }

        .lote-info-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .lote-info-item span {
            font-weight: 600;
            color: #1a252f;
        }

        .lote-footer {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .estado-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn-action {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .filter-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .view-toggle .btn {
            border-radius: 8px;
        }

        .view-toggle .btn.active {
            background: var(--primary-color);
            color: white;
        }

        .table-view .table {
            margin-bottom: 0;
        }

        .table-view .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 500;
            padding: 12px 15px;
        }

        .table-view .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table-view .table tbody tr:hover {
            background: #f8f9fc;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .pagination {
            margin: 0;
        }

        .sync-ayuda {
            line-height: 1.35;
        }

        .lotes-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .lotes-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .lotes-vista-toggle {
            flex-shrink: 0;
        }

        .lotes-vista-toggle .btn {
            min-width: 42px;
        }
    </style>
@endpush

@section('content')
    <!-- Estadísticas -->
    <div class="row stats-row">
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p><i class="fas fa-map mr-1"></i> Total Lotes</p>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box produccion">
                <h3>{{ $stats['en_produccion'] ?? 0 }}</h3>
                <p><i class="fas fa-leaf mr-1"></i> En Producción</p>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box sembrado">
                <h3>{{ $stats['sembrados'] ?? 0 }}</h3>
                <p><i class="fas fa-seedling mr-1"></i> Sembrados</p>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-box cosechado">
                <h3>{{ number_format($stats['hectareas'] ?? 0, 2) }}</h3>
                <p><i class="fas fa-ruler-combined mr-1"></i> Hectáreas</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <h3 class="card-title mb-0"><i class="fas fa-filter mr-2"></i>Filtrar lotes</h3>
        </div>
        <div class="card-body py-3">
            <form method="GET" action="{{ route('lotes.index') }}" class="row align-items-end">
                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           value="{{ $filtros['q'] ?? '' }}"
                           placeholder="Nombre, ubicación o código TRAZ">
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted mb-1">Cultivo</label>
                    <select name="cultivoid" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($cultivos as $c)
                            <option value="{{ $c->cultivoid }}" @selected(($filtros['cultivoid'] ?? '') == $c->cultivoid)>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted mb-1">Estado</label>
                    <select name="estadolotetipoid" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($estados as $e)
                            <option value="{{ $e->estadolotetipoid }}" @selected(($filtros['estadolotetipoid'] ?? '') == $e->estadolotetipoid)>{{ ucfirst($e->nombre) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted mb-1">Propietario</label>
                    <select name="usuarioid" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->usuarioid }}" @selected(($filtros['usuarioid'] ?? '') == $u->usuarioid)>{{ $u->nombre }} {{ $u->apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label class="small text-muted mb-1">Ubicación GPS</label>
                    <select name="con_mapa" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="1" @selected(($filtros['con_mapa'] ?? '') === '1')>Con mapa</option>
                        <option value="0" @selected(($filtros['con_mapa'] ?? '') === '0')>Sin coordenadas</option>
                    </select>
                </div>
                <div class="col-md-1 col-sm-6 mb-2 d-flex">
                    <button type="submit" class="btn btn-success btn-sm btn-block mr-1" title="Aplicar filtros">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
            @if(collect($filtros ?? [])->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty())
                <div class="small text-muted mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Mostrando {{ $lotes->total() }} resultado(s) con filtros activos.
                </div>
            @endif
        </div>
    </div>

    <!-- Acciones -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="lotes-toolbar">
                <div class="lotes-toolbar-actions">
                    @can('lotes.create')
                        <a href="{{ route('lotes.create') }}" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i> Nuevo Lote
                        </a>
                    @endcan
                    @can('lotes.update')
                        <form action="{{ route('lotes.sincronizar-operacion') }}" method="POST" class="d-inline mb-0">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-sync mr-1"></i> Sincronizar operación
                            </button>
                        </form>
                    @endcan
                    <a href="{{ route('lotes.mapa') }}" class="btn btn-outline-success">
                        <i class="fas fa-map-marked-alt mr-1"></i> Ver Mapa
                    </a>
                </div>
                <div class="lotes-vista-toggle">
                    <span class="small text-muted mr-2 d-none d-sm-inline">Vista:</span>
                    <div class="btn-group view-toggle" role="group" aria-label="Cambiar vista de lotes">
                        <button type="button" class="btn btn-outline-secondary active" id="btnCardView" title="Tarjetas">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnTableView" title="Lista">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
            @can('lotes.update')
                <p class="small text-muted mb-0 mt-2 sync-ayuda">
                    <i class="fas fa-magic mr-1 text-info"></i>
                    <strong>Sincronizar operación:</strong> actualiza el clima de cada lote, crea actividades desde insumos y cosechas registrados, y programa riegos pendientes.
                </p>
            @endcan
        </div>
    </div>

    <!-- Vista de Tarjetas -->
    <div id="cardView">
        @forelse($lotes as $l)
            @php
                $estadoNombre = strtolower($l->estadoTipo->nombre ?? 'disponible');
                $estadoClass = str_replace(' ', '-', $estadoNombre);
                $estadoColors = [
                    'disponible' => 'bg-secondary',
                    'en preparación' => 'bg-info',
                    'sembrado' => 'bg-primary',
                    'en producción' => 'bg-success',
                    'cosechado' => 'bg-warning text-dark',
                    'en descanso' => 'bg-dark'
                ];
                $badgeClass = $estadoColors[$estadoNombre] ?? 'bg-secondary';
            @endphp
            <div class="lote-card {{ $estadoClass }}">
                <div class="lote-header">
                    <h5><i class="fas fa-map-marker-alt mr-2 text-success"></i>{{ $l->nombre }}</h5>
                    <span class="estado-badge {{ $badgeClass }}">{{ ucfirst($l->estadoTipo->nombre ?? 'Sin estado') }}</span>
                </div>
                <div class="lote-body">
                    <div class="lote-info">
                        <div class="lote-info-item">
                            <label>Propietario</label>
                            <span>{{ $l->usuario->nombre ?? '-' }}</span>
                        </div>
                        <div class="lote-info-item">
                            <label>Cultivo</label>
                            <span>
                                @if($l->cultivo)
                                    <span class="badge badge-success">{{ $l->cultivo->nombre }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </span>
                        </div>
                        <div class="lote-info-item">
                            <label>Superficie</label>
                            <span>{{ $l->superficie }} ha</span>
                        </div>
                        <div class="lote-info-item">
                            <label>Ubicación</label>
                            <span>{{ Str::limit($l->ubicacion ?? 'No especificada', 25) }}</span>
                        </div>
                        <div class="lote-info-item">
                            <label>Fecha Siembra</label>
                            <span>{{ $l->fechasiembra ? \Carbon\Carbon::parse($l->fechasiembra)->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="lote-footer">
                    <small class="text-muted">
                        @if($l->latitud && $l->longitud)
                            <i class="fas fa-map-pin text-success mr-1"></i> Con ubicación
                        @else
                            <i class="fas fa-map-pin text-muted mr-1"></i> Sin coordenadas
                        @endif
                    </small>
                    <div>
                        <a href="{{ route('lotes.show', $l) }}" class="btn btn-sm btn-info btn-action" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('lotes.update')
                            <a href="{{ route('lotes.edit', $l) }}" class="btn btn-sm btn-warning btn-action" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endcan
                        @can('lotes.delete')
                            <form action="{{ route('lotes.destroy', $l) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este lote?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-action" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="empty-state">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>No hay lotes registrados</h4>
                    <p class="text-muted">Comienza agregando tu primer lote de cultivo.</p>
                    @can('lotes.create')
                        <a href="{{ route('lotes.create') }}" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i> Crear Lote
                        </a>
                    @endcan
                </div>
            </div>
        @endforelse
    </div>

    <!-- Vista de Tabla (oculta por defecto) -->
    <div id="tableView" style="display: none;">
        <div class="card table-view">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Propietario</th>
                            <th>Cultivo</th>
                            <th>Estado</th>
                            <th>Superficie</th>
                            <th>Fecha Siembra</th>
                            <th class="text-center" style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $l)
                            @php
                                $estadoNombre = strtolower($l->estadoTipo->nombre ?? 'disponible');
                                $estadoColors = [
                                    'disponible' => 'bg-secondary',
                                    'en preparación' => 'bg-info',
                                    'sembrado' => 'bg-primary',
                                    'en producción' => 'bg-success',
                                    'cosechado' => 'bg-warning text-dark',
                                    'en descanso' => 'bg-dark'
                                ];
                                $badgeClass = $estadoColors[$estadoNombre] ?? 'bg-secondary';
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $l->nombre }}</strong>
                                    <br><small class="text-muted">{{ Str::limit($l->ubicacion ?? '', 30) }}</small>
                                </td>
                                <td>{{ $l->usuario->nombre ?? '-' }}</td>
                                <td>
                                    @if($l->cultivo)
                                        <span class="badge badge-success">{{ $l->cultivo->nombre }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><span
                                        class="badge {{ $badgeClass }}">{{ ucfirst($l->estadoTipo->nombre ?? 'Sin estado') }}</span>
                                </td>
                                <td>{{ $l->superficie }} ha</td>
                                <td>{{ $l->fechasiembra ? \Carbon\Carbon::parse($l->fechasiembra)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('lotes.show', $l) }}" class="btn btn-info" title="Ver"><i
                                                class="fas fa-eye"></i></a>
                                        @can('lotes.update')
                                            <a href="{{ route('lotes.edit', $l) }}" class="btn btn-warning" title="Editar"><i
                                                    class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('lotes.delete')
                                            <form action="{{ route('lotes.destroy', $l) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Eliminar?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger" title="Eliminar"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No hay lotes registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($lotes->hasPages())
        <div class="card mt-4">
            <div class="card-body d-flex justify-content-center">
                {{ $lotes->links() }}
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#btnCardView').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('#cardView').show();
                $('#tableView').hide();
            });

            $('#btnTableView').on('click', function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('#tableView').show();
                $('#cardView').hide();
            });
        });
    </script>
@endpush