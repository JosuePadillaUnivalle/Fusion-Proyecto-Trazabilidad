@extends('layouts.app')

@section('title', 'Historial de Estados | AgroNexus')
@section('page_title', 'Historial de Estados de Lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Historial de Estados</li>
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

        /* Estilos para Vista Lista (List Cards) */
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
            justify-content: flex-end; /* Alineado a la derecha como Insumos */
            align-items: center;
        }

        .btn-custom-action {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        .search-box input {
            border-radius: 20px;
        }
        
        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 85, 48, 0.25);
        }

        .view-toggle .btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
@endpush

@section('content')

    <!-- Estadísticas Rápidas -->
    <div class="row stats-row">
        <div class="col-md-12 mb-3">
            <div class="stat-box">
                <h3>{{ $historial->total() }}</h3>
                <p><i class="fas fa-history mr-1"></i> Registros de Historial</p>
            </div>
        </div>
    </div>

    <!-- Filtros y Acciones -->
    <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <div class="input-group search-box">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control border-left-0" placeholder="Buscar por lote, estado o usuario...">
                    </div>
                </div>
                <div class="col-md-7 text-md-right mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2">
                    <a href="{{ route('historial-estados-lote.create') }}" class="btn btn-success text-white mr-3" style="border-radius: 20px; background-color: #28a745; border-color: #28a745;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Registro
                    </a>
                    <div class="btn-group view-toggle">
                        <button type="button" class="btn btn-outline-secondary active" id="btnCardView"><i class="fas fa-th-large"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnTableView"><i class="fas fa-list"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista Cards (Default) -->
    <div id="cardView">
        @forelse($historial as $h)
            <div class="list-card search-item" data-nombre="{{ strtolower($h->lote->nombre ?? '') }} {{ strtolower($h->estadoTipo->nombre ?? '') }} {{ strtolower($h->usuario->nombre ?? '') }}">
                <div class="list-header">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 mr-3" style="color: #2c5530; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h5 class="m-0 font-weight-bold" style="color: #1a252f;">{{ $h->lote->nombre ?? 'Lote Eliminado' }}</h5>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-info">{{ $h->estadoTipo->nombre ?? 'Desconocido' }}</span>
                    </div>
                </div>
                <div class="list-body">
                    <div class="list-info">
                        <div class="list-item">
                            <label><i class="fas fa-calendar mr-1"></i> Fecha Cambio</label>
                            <span>{{ $h->fecha_cambio }}</span>
                        </div>
                        <div class="list-item">
                            <label><i class="fas fa-user mr-1"></i> Usuario</label>
                            <span>{{ $h->usuario->nombre ?? 'Sistema' }}</span>
                        </div>
                        <div class="list-item" style="flex: 2;">
                            <label><i class="fas fa-comment-alt mr-1"></i> Observaciones</label>
                            <span class="text-muted">{{ Str::limit($h->observaciones, 50) ?: 'Sin observaciones' }}</span>
                        </div>
                    </div>
                </div>
                <div class="list-footer">
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('historial-estados-lote.show', $h) }}" class="btn btn-custom-action btn-info text-white" title="Ver Detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('historial-estados-lote.edit', $h) }}" class="btn btn-custom-action btn-warning text-white" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('historial-estados-lote.destroy', $h) }}" method="POST" class="d-inline on-submit-confirm">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-custom-action btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay registros de historial</h4>
                    <p class="text-muted mb-3">Registra los cambios de estado de tus lotes para llevar un seguimiento.</p>
                    <a href="{{ route('historial-estados-lote.create') }}" class="btn btn-success mt-2">Registrar Cambio</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Vista Tabla (Oculta) -->
    <div id="tableView" style="display: none;">
        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Lote</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Fecha Cambio</th>
                            <th>Observaciones</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $h)
                            <tr class="search-item-row" data-nombre="{{ strtolower($h->lote->nombre ?? '') }} {{ strtolower($h->estadoTipo->nombre ?? '') }} {{ strtolower($h->usuario->nombre ?? '') }}">
                                <td class="font-weight-bold" style="color: #2c5530;">{{ $h->lote->nombre ?? '-' }}</td>
                                <td><span class="badge badge-light border">{{ $h->estadoTipo->nombre ?? '-' }}</span></td>
                                <td>{{ $h->usuario->nombre ?? '-' }}</td>
                                <td>{{ $h->fecha_cambio }}</td>
                                <td>{{ Str::limit($h->observaciones, 30) }}</td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('historial-estados-lote.show', $h) }}" class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('historial-estados-lote.edit', $h) }}" class="btn btn-sm btn-warning text-white"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('historial-estados-lote.destroy', $h) }}" method="POST" class="d-inline on-submit-confirm">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $historial->links() }}
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            // Toggle Vistas
            $('#btnCardView').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('#cardView').fadeIn();
                $('#tableView').hide();
            });
            $('#btnTableView').click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                $('#tableView').fadeIn();
                $('#cardView').hide();
            });

            // Buscador
            $('#searchInput').keyup(function() {
                var val = $(this).val().toLowerCase();
                $('.search-item').each(function() {
                    var match = $(this).data('nombre').indexOf(val) > -1;
                    $(this).toggle(match);
                });
                $('.search-item-row').each(function() {
                    var match = $(this).data('nombre').indexOf(val) > -1;
                    $(this).toggle(match);
                });
            });

            // Confirmar eliminación
            $('.on-submit-confirm').submit(function(e) {
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