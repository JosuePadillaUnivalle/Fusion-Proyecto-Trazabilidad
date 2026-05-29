@extends('layouts.app')

@section('title', 'Estados de Lote | AgroNexus')
@section('page_title', 'Estados de Lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('catalogos.index') }}" style="color: #2c5530;">Catálogos</a></li>
    <li class="breadcrumb-item active">Estados de Lote</li>
@endsection

@push('styles')
    <style>
        .catalog-header {
            background: linear-gradient(135deg, #dc3545, #f06595);
            border-radius: 15px;
            padding: 25px 30px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .catalog-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .catalog-header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .catalog-stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-item i {
            font-size: 1.5rem;
            opacity: 0.8;
        }

        .stat-item span {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            display: block;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .item-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            border-left: 4px solid #dc3545;
        }

        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .item-card .card-body {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .item-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .item-details h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
        }

        .item-details small {
            color: #6c757d;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .item-actions .btn {
            border-radius: 8px;
            padding: 8px 12px;
        }

        /* Colores según estado */
        .icon-disponible {
            background: linear-gradient(135deg, #6c757d, #adb5bd);
        }

        .icon-preparacion {
            background: linear-gradient(135deg, #fd7e14, #ffc107);
        }

        .icon-sembrado {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .icon-produccion {
            background: linear-gradient(135deg, #17a2b8, #6dd5ed);
        }

        .icon-cosechado {
            background: linear-gradient(135deg, #ffc107, #ffca2c);
        }

        .icon-descanso {
            background: linear-gradient(135deg, #6f42c1, #9775fa);
        }

        .icon-default {
            background: linear-gradient(135deg, #dc3545, #f06595);
        }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-box input {
            padding-left: 45px;
            border-radius: 25px;
            border: 2px solid #e9ecef;
            height: 50px;
        }

        .search-box input:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
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
    </style>
@endpush

@section('content')
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: '{!! session("success") !!}', confirmButtonColor: '#dc3545', timer: 3000, timerProgressBar: true });
            });
        </script>
    @endif

    <div class="catalog-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h2><i class="fas fa-flag mr-2"></i>Estados de Lote</h2>
                <p>Define los estados del ciclo de vida de tus lotes agrícolas</p>
                <div class="catalog-stats">
                    <div class="stat-item">
                        <i class="fas fa-database"></i>
                        <div><span>{{ $tipos->total() }}</span><small>Total registros</small></div>
                    </div>
                </div>
            </div>
            <a href="{{ route('estado-lote-tipos.create') }}" class="btn btn-light btn-lg mt-3 mt-md-0">
                <i class="fas fa-plus mr-2"></i>Nuevo Estado
            </a>
        </div>
    </div>

    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar estado...">
    </div>

    <div id="itemsList">
        @forelse ($tipos as $tipo)
            @php
                $nombre = strtolower($tipo->nombre);
                $iconData = [
                    'disponible' => ['icon' => 'check-circle', 'class' => 'icon-disponible'],
                    'preparación' => ['icon' => 'cog', 'class' => 'icon-preparacion'],
                    'preparacion' => ['icon' => 'cog', 'class' => 'icon-preparacion'],
                    'sembrado' => ['icon' => 'seedling', 'class' => 'icon-sembrado'],
                    'producción' => ['icon' => 'leaf', 'class' => 'icon-produccion'],
                    'produccion' => ['icon' => 'leaf', 'class' => 'icon-produccion'],
                    'cosechado' => ['icon' => 'tractor', 'class' => 'icon-cosechado'],
                    'descanso' => ['icon' => 'bed', 'class' => 'icon-descanso'],
                ];
                $icon = 'flag';
                $iconClass = 'icon-default';
                foreach ($iconData as $key => $val) {
                    if (str_contains($nombre, $key)) {
                        $icon = $val['icon'];
                        $iconClass = $val['class'];
                        break;
                    }
                }
            @endphp
            <div class="item-card mb-3 search-item" data-nombre="{{ $nombre }}">
                <div class="card-body">
                    <div class="item-info">
                        <div class="item-icon {{ $iconClass }}"><i class="fas fa-{{ $icon }}"></i></div>
                        <div class="item-details">
                            <h5>{{ ucfirst($tipo->nombre) }}</h5>

                        </div>
                    </div>
                    <div class="item-actions">
                        <a href="{{ route('estado-lote-tipos.edit', $tipo) }}" class="btn btn-warning btn-sm"><i
                                class="fas fa-edit"></i></a>
                        <form action="{{ route('estado-lote-tipos.destroy', $tipo) }}" method="POST"
                            class="d-inline form-eliminar">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar"><i
                                    class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="item-card">
                <div class="empty-state">
                    <i class="fas fa-flag"></i>
                    <h4>No hay estados de lote registrados</h4>
                    <a href="{{ route('estado-lote-tipos.create') }}" class="btn btn-danger"><i
                            class="fas fa-plus mr-2"></i>Agregar Estado</a>
                </div>
            </div>
        @endforelse
    </div>

    @if($tipos->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $tipos->links() }}</div>@endif
    <div class="mt-4"><a href="{{ route('catalogos.index') }}" class="btn btn-outline-secondary"><i
                class="fas fa-arrow-left mr-2"></i>Volver a Catálogos</a></div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            $('#searchInput').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('.search-item').each(function () { $(this).toggle($(this).data('nombre').indexOf(value) > -1); });
            });
            $(document).on('click', '.btn-eliminar', function (e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: '¿Eliminar estado de lote?', text: 'Esta acción no se puede deshacer', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar', cancelButtonText: 'Cancelar', reverseButtons: true
                }).then((result) => { if (result.isConfirmed) form.submit(); });
            });
        });
    </script>
@endpush