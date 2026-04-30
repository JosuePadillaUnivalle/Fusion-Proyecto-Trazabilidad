@extends('layouts.app')

@section('title', 'Unidades de Medida | AgroNexus')
@section('page_title', 'Unidades de Medida')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('catalogos.index') }}" style="color: #2c5530;">Catálogos</a></li>
    <li class="breadcrumb-item active">Unidades de Medida</li>
@endsection

@push('styles')
    <style>
        .catalog-header {
            background: linear-gradient(135deg, #343a40, #6c757d);
            border-radius: 15px;
            padding: 25px 30px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(52, 58, 64, 0.3);
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
            border-left: 4px solid #343a40;
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
            background: linear-gradient(135deg, #343a40, #6c757d);
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

        .badge-categoria {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-peso {
            background: #e3f2fd;
            color: #1565c0;
        }

        .badge-volumen {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-area {
            background: #fff3e0;
            color: #ef6c00;
        }

        .badge-longitud {
            background: #fce4ec;
            color: #c2185b;
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
            border-color: #343a40;
            box-shadow: 0 0 0 3px rgba(52, 58, 64, 0.1);
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
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: '{!! session("success") !!}', confirmButtonColor: '#343a40', timer: 3000, timerProgressBar: true });
            });
        </script>
    @endif

    <div class="catalog-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h2><i class="fas fa-ruler mr-2"></i>Unidades de Medida</h2>
                <p>Administra las unidades de medida para cantidades y conversiones</p>
                <div class="catalog-stats">
                    <div class="stat-item">
                        <i class="fas fa-database"></i>
                        <div><span>{{ $unidades->total() }}</span><small>Total registros</small></div>
                    </div>
                </div>
            </div>
            <a href="{{ route('unidades-medida.create') }}" class="btn btn-light btn-lg mt-3 mt-md-0">
                <i class="fas fa-plus mr-2"></i>Nueva Unidad
            </a>
        </div>
    </div>

    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar unidad de medida...">
    </div>

    <div id="itemsList">
        @forelse ($unidades as $unidad)
            @php
                $iconMap = ['peso' => 'weight-hanging', 'volumen' => 'tint', 'area' => 'vector-square', 'longitud' => 'ruler-horizontal'];
                $icon = $iconMap[$unidad->categoria ?? ''] ?? 'balance-scale';
                $badgeClass = 'badge-' . ($unidad->categoria ?? 'peso');
            @endphp
            <div class="item-card mb-3 search-item"
                data-nombre="{{ strtolower($unidad->nombre . ' ' . ($unidad->abreviatura ?? '')) }}">
                <div class="card-body">
                    <div class="item-info">
                        <div class="item-icon"><i class="fas fa-{{ $icon }}"></i></div>
                        <div class="item-details">
                            <h5>{{ $unidad->nombre }} <span class="text-muted">({{ $unidad->abreviatura }})</span></h5>
                            <div class="mt-1">
                                <span
                                    class="badge-categoria {{ $badgeClass }}">{{ ucfirst($unidad->categoria ?? 'General') }}</span>
                            </div>

                        </div>
                    </div>
                    <div class="item-actions">
                        <a href="{{ route('unidades-medida.edit', $unidad) }}" class="btn btn-warning btn-sm"><i
                                class="fas fa-edit"></i></a>
                        <form action="{{ route('unidades-medida.destroy', $unidad) }}" method="POST"
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
                    <i class="fas fa-ruler"></i>
                    <h4>No hay unidades de medida registradas</h4>
                    <a href="{{ route('unidades-medida.create') }}" class="btn btn-secondary"><i
                            class="fas fa-plus mr-2"></i>Agregar Unidad</a>
                </div>
            </div>
        @endforelse
    </div>

    @if($unidades->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $unidades->links() }}</div>@endif
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
                    title: '¿Eliminar unidad de medida?', text: 'Esta acción no se puede deshacer', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar', cancelButtonText: 'Cancelar', reverseButtons: true
                }).then((result) => { if (result.isConfirmed) form.submit(); });
            });
        });
    </script>
@endpush