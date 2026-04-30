@extends('layouts.app')

@section('title', 'Ventas | Fusion-Proyectos')
@section('page_title', 'Gestión de Ventas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@push('styles')
    <style>
        /* Cards estilo dashboard */
        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-2px);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-green {
            background: linear-gradient(135deg, #28a745, #34ce57) !important;
        }

        .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
        }

        .small-box-yellow {
            background: linear-gradient(135deg, #ffc107, #ffca2c) !important;
        }

        .small-box-red {
            background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
        }

        /* Tarjetas de listado */
        .venta-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }

        .venta-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .venta-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f3f4;
        }

        .venta-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
            font-size: 1rem;
        }

        .venta-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #28a745;
        }

        .venta-body {
            padding: 15px 20px;
        }

        .venta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .venta-info-item {
            flex: 1;
            min-width: 120px;
        }

        .venta-info-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .venta-info-item span {
            font-weight: 600;
            color: #1a252f;
        }

        .venta-footer {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-action {
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .view-toggle .btn.active {
            background: #2c5530;
            color: white;
        }
    </style>
@endpush

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- Cards estilo dashboard -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $ventas->total() }}</h3>
                    <p>Total Ventas</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>Bs.{{ number_format($ventas->sum(fn($v) => ($v->cantidad ?? 0) * ($v->preciounitario ?? 0)), 0) }}
                    </h3>
                    <p>Ingresos Totales</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ number_format($ventas->sum('cantidad'), 0) }}<span style="font-size: 18px;">kg</span></h3>
                    <p>Kg Vendidos</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    @php
                        $promedio = $ventas->count() > 0 ? $ventas->sum(fn($v) => ($v->cantidad ?? 0) * ($v->preciounitario ?? 0)) / $ventas->count() : 0;
                    @endphp
                    <h3>Bs.{{ number_format($promedio, 0) }}</h3>
                    <p>Promedio/Venta</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    @can('ventas.create')
                        <a href="{{ route('ventas.create') }}" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i> Nueva Venta
                        </a>
                    @endcan
                </div>
                <div class="col-md-6 text-md-right mt-3 mt-md-0">
                    <div class="btn-group view-toggle">
                        <button type="button" class="btn btn-outline-secondary active" id="btnCardView">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnTableView">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista de Tarjetas -->
    <div id="cardView">
        @forelse($ventas as $v)
            @php
                $total = ($v->cantidad ?? 0) * ($v->preciounitario ?? 0);
            @endphp
            <div class="venta-card">
                <div class="venta-header">
                    <div>
                        <h5>
                            <i class="fas fa-user mr-2 text-muted"></i>{{ $v->cliente ?? 'Cliente no especificado' }}
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $v->fechaventa ? \Carbon\Carbon::parse($v->fechaventa)->format('d/m/Y') : '-' }}
                        </small>
                    </div>
                    <span class="venta-total">Bs. {{ number_format($total, 2) }}</span>
                </div>
                <div class="venta-body">
                    <div class="venta-info">
                        <div class="venta-info-item">
                            <label><i class="fas fa-boxes mr-1"></i> Producción</label>
                            <span>
                                @if($v->produccion)
                                    {{ $v->produccion->lote->nombre ?? 'Lote' }}
                                    @if($v->produccion->lote && $v->produccion->lote->cultivo)
                                        <small class="badge badge-success">{{ $v->produccion->lote->cultivo->nombre }}</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="venta-info-item">
                            <label><i class="fas fa-weight-hanging mr-1"></i> Cantidad</label>
                            <span>{{ number_format($v->cantidad ?? 0, 2) }} {{ $v->unidadMedida->abreviatura ?? 'kg' }}</span>
                        </div>
                        <div class="venta-info-item">
                            <label><i class="fas fa-tag mr-1"></i> Precio Unitario</label>
                            <span>Bs. {{ number_format($v->preciounitario ?? 0, 2) }}</span>
                        </div>

                    </div>
                </div>
                <div class="venta-footer">
                    <small class="text-muted">
                        @if($v->produccion && $v->produccion->fechacosecha)
                            <i class="fas fa-tractor mr-1"></i> Cosechado:
                            {{ \Carbon\Carbon::parse($v->produccion->fechacosecha)->format('d/m/Y') }}
                        @endif
                    </small>
                    <div>
                        <a href="{{ route('ventas.show', $v) }}" class="btn btn-sm btn-info btn-action" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('ventas.update')
                            <a href="{{ route('ventas.edit', $v) }}" class="btn btn-sm btn-warning btn-action" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endcan
                        @can('ventas.delete')
                            <form action="{{ route('ventas.destroy', $v) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar esta venta?')">
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
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h4>No hay ventas registradas</h4>
                    <p class="text-muted">Comienza registrando tu primera venta.</p>
                    @can('ventas.create')
                        <a href="{{ route('ventas.create') }}" class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i> Nueva Venta
                        </a>
                    @endcan
                </div>
            </div>
        @endforelse
    </div>

    <!-- Vista de Tabla -->
    <div id="tableView" style="display: none;">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Producción</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th class="text-center" style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $v)
                            @php $total = ($v->cantidad ?? 0) * ($v->preciounitario ?? 0); @endphp
                            <tr>
                                <td><strong>{{ $v->cliente ?? '-' }}</strong></td>
                                <td>
                                    @if($v->produccion && $v->produccion->lote)
                                        {{ $v->produccion->lote->nombre }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ number_format($v->cantidad ?? 0, 2) }} {{ $v->unidadMedida->abreviatura ?? 'kg' }}</td>
                                <td>Bs. {{ number_format($v->preciounitario ?? 0, 2) }}</td>
                                <td><strong class="text-success">Bs. {{ number_format($total, 2) }}</strong></td>
                                <td>{{ $v->fechaventa ? \Carbon\Carbon::parse($v->fechaventa)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('ventas.show', $v) }}" class="btn btn-sm btn-info"><i
                                            class="fas fa-eye"></i></a>
                                    @can('ventas.update')
                                        <a href="{{ route('ventas.edit', $v) }}" class="btn btn-sm btn-warning"><i
                                                class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('ventas.delete')
                                        <form action="{{ route('ventas.destroy', $v) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('¿Eliminar?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No hay ventas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($ventas->hasPages())
        <div class="card mt-4">
            <div class="card-body d-flex justify-content-center">
                {{ $ventas->links() }}
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