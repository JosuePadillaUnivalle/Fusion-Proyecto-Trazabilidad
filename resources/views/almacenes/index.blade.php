@extends('layouts.app')

@section('title', 'Almacenes | AgroNexus')
@section('page_title', 'Gestión de Almacenes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Almacenes</li>
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

        .stat-box.capacidad {
            background: linear-gradient(135deg, #FF9800, #ffb74d);
        }

        .stat-box.activos {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .almacen-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            height: 100%;
        }

        .almacen-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .almacen-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .almacen-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
            font-size: 1.1rem;
        }

        .almacen-body {
            padding: 15px 20px;
        }

        .almacen-info {
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

        .almacen-actions {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-status.active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-status.inactive {
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

        /* Estilos para Vista Lista (List Cards) Reutilizables */
        .list-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .list-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .list-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f3f4;
        }

        .list-body {
            padding: 15px 20px;
        }

        .list-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .list-item {
            flex: 1;
            min-width: 120px;
        }

        .list-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .list-item span {
            font-weight: 600;
            color: #1a252f;
        }

        .list-footer {
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
                <h3>{{ $almacenes->total() }}</h3>
                <p><i class="fas fa-warehouse mr-1"></i> Total Almacenes</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box capacity">
                <h3>{{ $almacenes->sum('capacidad') }}</h3>
                <p><i class="fas fa-balance-scale mr-1"></i> Capacidad Combinada</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box activos">
                <h3>{{ $almacenes->where('activo', true)->count() }}</h3>
                <p><i class="fas fa-check-circle mr-1"></i> Almacenes Activos</p>
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
                            placeholder="Buscar almacén...">
                    </div>
                </div>
                <div class="col-md-7 text-md-right mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2">
                    @can('inventario.create')
                    <a href="{{ route('almacenes.create') }}" class="btn btn-success text-white mr-3"
                        style="border-radius: 20px; background-color: #28a745; border-color: #28a745;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Almacén
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
        @forelse($almacenes as $a)
            <div class="list-card search-item"
                data-nombre="{{ strtolower($a->nombre) }} {{ strtolower($a->tipoAlmacen->nombre ?? '') }}">
                <div class="list-header">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 mr-3"
                            style="color: #2c5530; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold" style="color: #1a252f;">{{ $a->nombre }}</h5>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-status {{ $a->activo ? 'active' : 'inactive' }}" style="font-size: 0.9rem;">
                            {{ $a->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
                <div class="list-body">
                    <div class="list-info">
                        <div class="list-item">
                            <label><i class="fas fa-cubes mr-1"></i> Tipo</label>
                            <span>{{ $a->tipoAlmacen->nombre ?? 'Sin Tipo' }}</span>
                        </div>
                        <div class="list-item">
                            <label><i class="fas fa-balance-scale mr-1"></i> Capacidad</label>
                            <span>{{ $a->capacidad }}</span>
                        </div>
                        <div class="list-item">
                            <label><i class="fas fa-ruler mr-1"></i> Unidad</label>
                            <span>{{ $a->unidadMedida->nombre ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="list-footer">
                    <small class="text-muted"></small>
                    <div>
                        <a href="{{ route('almacenes.show', $a) }}" class="btn btn-sm btn-info text-white"><i
                                class="fas fa-eye"></i></a>
                        @can('inventario.update')
                        <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-sm btn-warning text-white"><i
                                class="fas fa-edit"></i></a>
                        @endcan
                        @can('inventario.delete')
                        <form action="{{ route('almacenes.destroy', $a) }}" method="POST" class="d-inline on-submit-confirm">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay almacenes registrados</h4>
                    @can('inventario.create')
                    <a href="{{ route('almacenes.create') }}" class="btn btn-success mt-2">Crear Primer Almacén</a>
                    @endcan
                </div>
            </div>
        @endforelse
    </div>

    <!-- Vista Tabla -->
    <!-- Vista Tabla (Convertida a Lista de Tarjetas) -->
    <!-- Vista Tabla (Restaurada) -->
    <div id="tableView" style="display: none;">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th>Unidad</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($almacenes as $a)
                                <tr class="search-item-row"
                                    data-nombre="{{ strtolower($a->nombre) }} {{ strtolower($a->tipoAlmacen->nombre ?? '') }}">
                                    <td class="font-weight-bold" style="color: #2c5530;">{{ $a->nombre }}</td>
                                    <td>{{ $a->tipoAlmacen->nombre ?? '-' }}</td>
                                    <td>{{ $a->capacidad }}</td>
                                    <td>{{ $a->unidadMedida->nombre ?? '-' }}</td>
                                    <td>
                                        @if($a->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('almacenes.show', $a) }}" class="btn btn-sm btn-info text-white"><i
                                                class="fas fa-eye"></i></a>
                                        @can('inventario.update')
                                        <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-sm btn-warning text-white"><i
                                                class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('inventario.delete')
                                        <form action="{{ route('almacenes.destroy', $a) }}" method="POST"
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
        {{ $almacenes->links() }}
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
                    title: '¿Eliminar almacén?',
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