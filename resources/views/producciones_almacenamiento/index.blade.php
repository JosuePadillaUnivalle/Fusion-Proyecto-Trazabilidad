@extends('layouts.app')

@section('title', 'Almacenamiento | AgroNexus')
@section('page_title', 'Almacenamiento de Producciones')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Almacenamiento</li>
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

        .stat-box.temp {
            background: linear-gradient(135deg, #FF5722, #ff7043);
        }

        .stat-box.almacenes {
            background: linear-gradient(135deg, #009688, #4db6ac);
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .storage-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
            height: 100%;
        }

        .storage-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .storage-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .storage-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
            font-size: 1rem;
        }

        .storage-body {
            padding: 15px 20px;
        }

        .storage-info {
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

        .conditions-grid {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .condition-item {
            flex: 1;
            text-align: center;
            background: #f8f9fc;
            border-radius: 8px;
            padding: 5px;
        }

        .condition-item small {
            display: block;
            color: #6c757d;
            font-size: 0.75rem;
        }

        .condition-item strong {
            color: #2c5530;
            font-size: 0.9rem;
        }

        .storage-actions {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
        }

        .badge-unit {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            background: #e8f5e8;
            color: #2c5530;
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
                <h3>{{ $registros->total() }}</h3>
                <p><i class="fas fa-box-open mr-1"></i> Registros de Almacenamiento</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box almacenes">
                <h3>{{ $registros->unique('almacenid')->count() }}</h3>
                <p><i class="fas fa-warehouse mr-1"></i> Almacenes en Uso</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box temp">
                <h3>{{ $registros->where('temperatura', '>', 25)->count() }}</h3>
                <p><i class="fas fa-thermometer-half mr-1"></i> Temp > 25°C</p>
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
                            placeholder="Buscar por almacén o producción...">
                    </div>
                </div>
                <div class="col-md-7 text-md-right mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2">
                    <a href="{{ route('producciones_almacenamiento.create') }}" class="btn btn-success text-white mr-3"
                        style="border-radius: 20px; background-color: #28a745; border-color: #28a745;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Registro
                    </a>
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
        @forelse($registros as $r)
            <div class="list-card search-item"
                data-nombre="{{ strtolower($r->almacen->nombre ?? '') }} {{ strtolower($r->produccion->lote->nombre ?? '') }}">
                <div class="list-header">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 mr-3"
                            style="color: #2c5530; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold" style="color: #1a252f;">Producción N°{{ $r->produccionid }}</h5>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-unit">
                            {{ $r->unidadMedida->nombre ?? '-' }}
                        </span>
                    </div>
                </div>
                <div class="list-body">
                    <div class="list-info">
                        <div class="list-item">
                            <label><i class="fas fa-warehouse mr-1"></i> Almacén</label>
                            <span>{{ $r->almacen->nombre ?? 'Sin Almacén' }}</span>
                        </div>
                        <div class="list-item">
                            <label><i class="fas fa-map-marker-alt mr-1"></i> Lote Origen</label>
                            <span>
                                @if($r->produccion && $r->produccion->lote)
                                    {{ $r->produccion->lote->nombre }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="list-item">
                            <label><i class="fas fa-balance-scale mr-1"></i> Cantidad</label>
                            <span>{{ $r->cantidad }}</span>
                        </div>
                        <div class="list-item">
                            <label><i class="fas fa-thermometer-half mr-1"></i> Condiciones</label>
                            <span>{{ $r->temperatura }}°C / {{ $r->humedad }}%</span>
                        </div>
                    </div>
                </div>
                <div class="list-footer">
                    <small class="text-muted"></small>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('producciones_almacenamiento.show', $r) }}"
                            class="btn btn-custom-action btn-info text-white" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('producciones_almacenamiento.edit', $r) }}"
                            class="btn btn-custom-action btn-warning text-white" title="Editar"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('producciones_almacenamiento.destroy', $r) }}" method="POST"
                            class="d-inline on-submit-confirm" title="Eliminar">
                            @csrf @method('DELETE')
                            <button class="btn btn-custom-action btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay registros de almacenamiento</h4>
                    <a href="{{ route('producciones_almacenamiento.create') }}" class="btn btn-success mt-2">Crear Primer
                        Registro</a>
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
                                <th>Producción</th>
                                <th>Almacén</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th>Temp</th>
                                <th>Humedad</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registros as $r)
                                <tr class="search-item-row"
                                    data-nombre="{{ strtolower($r->almacen->nombre ?? '') }} {{ strtolower($r->produccion->lote->nombre ?? '') }}">
                                    <td class="font-weight-bold" style="color: #2c5530;">
                                        Producción N°{{ $r->produccionid }}
                                        <br><small class="text-muted">{{ $r->produccion->lote->nombre ?? '-' }}</small>
                                    </td>
                                    <td>{{ $r->almacen->nombre ?? '-' }}</td>
                                    <td>{{ $r->cantidad }}</td>
                                    <td>{{ $r->unidadMedida->nombre ?? '-' }}</td>
                                    <td>{{ $r->temperatura }}°C</td>
                                    <td>{{ $r->humedad }}%</td>
                                    <td class="text-right">
                                        <a href="{{ route('producciones_almacenamiento.show', $r) }}"
                                            class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('producciones_almacenamiento.edit', $r) }}"
                                            class="btn btn-sm btn-warning text-white"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('producciones_almacenamiento.destroy', $r) }}" method="POST"
                                            class="d-inline on-submit-confirm">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
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
        {{ $registros->links() }}
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
                    title: '¿Eliminar registro?',
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