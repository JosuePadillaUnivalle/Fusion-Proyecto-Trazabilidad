@extends('layouts.app')

@section('title', 'Gestión de Insumos | AgroNexus')
@section('page_title', 'Inventario de Insumos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Insumos</li>
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
            box-shadow: 0 4px 15px rgba(44, 85, 48, 0.2);
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

        .stat-box.stock-bajo {
            background: linear-gradient(135deg, #c62828, #e53935);
        }

        .stat-box.valor {
            background: linear-gradient(135deg, #1565c0, #42a5f5);
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .insumo-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            height: 100%;
        }

        .insumo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .insumo-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .insumo-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
        }

        .insumo-body {
            padding: 15px 20px;
        }

        .insumo-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }

        .info-label {
            color: #6c757d;
        }

        .info-value {
            font-weight: 600;
            color: #1a252f;
        }

        .insumo-actions {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }

        .badge-stock {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-stock.high {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-stock.medium {
            background: #fff3e0;
            color: #ef6c00;
        }

        .badge-stock.low {
            background: #ffebee;
            color: #c62828;
        }

        .search-box input {
            border-radius: 20px;
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 85, 48, 0.25);
        }

        .btn-custom-action {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .view-toggle .btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Estilos para Vista Lista (List Cards) */
        .insumo-list-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .insumo-list-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .insumo-list-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f3f4;
        }

        .insumo-list-body {
            padding: 15px 20px;
        }

        .insumo-list-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .insumo-list-item {
            flex: 1;
            min-width: 120px;
        }

        .insumo-list-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .insumo-list-item span {
            font-weight: 600;
            color: #1a252f;
        }

        .insumo-list-footer {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
@endpush

@section('content')
    <!-- Estadísticas -->
    <div class="row stats-row">
        <div class="col-md-4 mb-3">
            <div class="stat-box">
                <h3>{{ $insumos->total() }}</h3>
                <p><i class="fas fa-boxes mr-1"></i> Total Insumos</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box stock-bajo">
                <h3>{{ $insumos->filter(fn($i) => $i->stock <= $i->stockminimo)->count() }}</h3>
                <p><i class="fas fa-exclamation-triangle mr-1"></i> Stock Bajo</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box valor">
                <h3>{{ $insumos->unique('tipo_id')->count() }}</h3>
                <p><i class="fas fa-tags mr-1"></i> Categorías</p>
            </div>
        </div>
    </div>

    <!-- Filtros y Acciones -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="input-group search-box">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control border-left-0"
                            placeholder="Buscar insumo...">
                    </div>
                </div>
                <div class="col-md-7 text-md-right mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2">
                    @can('inventario.create')
                        <a href="{{ route('insumos.create') }}" class="btn btn-success text-white mr-3"
                            style="border-radius: 20px; background-color: #28a745; border-color: #28a745;">
                            <i class="fas fa-plus mr-1"></i> Nuevo Insumo
                        </a>
                    @endcan
                    <div class="btn-group view-toggle">
                        <button type="button" class="btn btn-outline-secondary active" id="btnCardView"><i
                                class="fas fa-th-large"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnTableView"><i
                                class="fas fa-list"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista Tarjetas -->
    <!-- Vista Tarjetas (Por defecto: Wide List Cards) -->
    <div id="cardView">
        @forelse($insumos as $i)
            @php
                $stockClass = 'high';
                if ($i->stock <= $i->stockminimo)
                    $stockClass = 'low';
                else if ($i->stock < $i->stockminimo * 1.5)
                    $stockClass = 'medium';

                $icon = 'box';
                $tipo = strtolower($i->tipo->nombre ?? '');
                if (str_contains($tipo, 'fertil'))
                    $icon = 'flask';
                else if (str_contains($tipo, 'semilla'))
                    $icon = 'seedling';
                else if (str_contains($tipo, 'pest'))
                    $icon = 'bug';
            @endphp
            <div class="insumo-list-card search-item" data-nombre="{{ strtolower($i->nombre) }}">
                <div class="insumo-list-header">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 mr-2" style="color: #2c5530;">
                            <i class="fas fa-{{ $icon }}"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold" style="color: #1a252f;">{{ $i->nombre }}</h5>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-stock {{ $stockClass }}" style="font-size: 0.9rem;">
                            Stock: {{ $i->stock }}
                        </span>
                    </div>
                </div>
                <div class="insumo-list-body">
                    <div class="insumo-list-info">
                        <div class="insumo-list-item">
                            <label><i class="fas fa-tag mr-1"></i> Tipo</label>
                            <span>{{ $i->tipo->nombre ?? '-' }}</span>
                        </div>
                        <div class="insumo-list-item">
                            <label><i class="fas fa-balance-scale mr-1"></i> Unidad</label>
                            <span>{{ $i->unidadMedida->nombre ?? '-' }}</span>
                        </div>
                        <div class="insumo-list-item">
                            <label><i class="fas fa-exclamation-circle mr-1"></i> Mínimo</label>
                            <span>{{ $i->stockminimo }}</span>
                        </div>
                    </div>
                </div>
                <div class="insumo-list-footer">
                    <small class="text-muted"></small>
                    <div class="d-flex align-items-center gap-1">
                        @can('inventario.update')
                            <a href="{{ route('insumos.edit', $i) }}" class="btn btn-sm btn-warning text-white" title="Editar"><i
                                    class="fas fa-edit"></i></a>
                        @endcan
                        @can('inventario.delete')
                            <form action="{{ route('insumos.destroy', $i) }}" method="POST" class="d-inline on-submit-confirm"
                                title="Eliminar">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light text-center">
                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay insumos registrados</h4>
                    @can('inventario.create')
                        <a href="{{ route('insumos.create') }}" class="btn btn-success mt-2">Agregar Primer Insumo</a>
                    @endcan
                </div>
            </div>
        @endforelse
    </div>

    <!-- Vista Tabla -->
    <!-- Vista Tabla (Restaurada) -->
    <div id="tableView" style="display: none;">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th>Unidad</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($insumos as $i)
                                <tr class="search-item-row" data-nombre="{{ strtolower($i->nombre) }}">
                                    <td class="font-weight-bold" style="color: #2c5530;">{{ $i->nombre }}</td>
                                    <td>{{ $i->tipo->nombre ?? '-' }}</td>
                                    <td>{{ $i->unidadMedida->nombre ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-stock {{ $i->stock <= $i->stockminimo ? 'low' : 'high' }}">
                                            {{ $i->stock }}
                                        </span>
                                    </td>
                                    <td>{{ $i->stockminimo }}</td>
                                    <td class="text-right">
                                        @can('inventario.update')
                                            <a href="{{ route('insumos.edit', $i) }}" class="btn btn-sm btn-warning text-white"><i
                                                    class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('inventario.delete')
                                            <form action="{{ route('insumos.destroy', $i) }}" method="POST"
                                                class="d-inline on-submit-confirm">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $insumos->links() }}
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            // Toggle Vistas
            $('#btnCardView').click(function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('#cardView').fadeIn();
                $('#tableView').hide();
            });
            $('#btnTableView').click(function () {
                $(this).addClass('active').siblings().removeClass('active');
                $('#tableView').fadeIn();
                $('#cardView').hide();
            });

            // Buscador
            $('#searchInput').keyup(function () {
                var val = $(this).val().toLowerCase();
                $('.search-item').each(function () {
                    var match = $(this).data('nombre').indexOf(val) > -1;
                    $(this).toggle(match);
                });
                // Filtrar tabla también
                $('.search-item-row').each(function () {
                    var match = $(this).data('nombre').indexOf(val) > -1;
                    $(this).toggle(match);
                });
            });

            // Confirmar eliminación
            $('.on-submit-confirm').submit(function (e) {
                e.preventDefault();
                var form = this;
                Swal.fire({
                    title: '¿Eliminar insumo?',
                    text: "No podrás revertir esto",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Hecho!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#2c5530',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
                    });
    </script>
@endpush