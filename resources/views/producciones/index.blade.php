@extends('layouts.app')

@section('title', 'Producción | AgroNexus')
@section('page_title', 'Gestión de Producción')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Producción</li>
@endsection

@push('styles')
    <style>
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

        .produccion-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }

        .produccion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .produccion-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f3f4;
        }

        .produccion-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1a252f;
            font-size: 1rem;
        }

        .produccion-cantidad {
            font-size: 1.3rem;
            font-weight: 700;
            color: #28a745;
        }

        .produccion-body {
            padding: 15px 20px;
        }

        .produccion-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .produccion-info-item {
            flex: 1;
            min-width: 120px;
        }

        .produccion-info-item label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .produccion-info-item span {
            font-weight: 600;
            color: #1a252f;
        }

        .produccion-footer {
            padding: 12px 20px;
            background: #f8f9fc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-toggle .btn.active {
            background: #2c5530;
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $producciones->total() }}</h3>
                    <p>Total Cosechas</p>
                </div>
                <div class="icon"><i class="fas fa-tractor"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ number_format($producciones->sum('cantidad'), 0) }}<span style="font-size: 18px;">kg</span></h3>
                    <p>Kg Totales</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $producciones->unique('loteid')->count() }}</h3>
                    <p>Lotes Productivos</p>
                </div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    @php
                        $promedio = $producciones->count() > 0 ? $producciones->sum('cantidad') / $producciones->count() : 0;
                    @endphp
                    <h3>{{ number_format($promedio, 0) }}<span style="font-size: 18px;">kg</span></h3>
                    <p>Promedio/Cosecha</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="{{ route('producciones.create') }}" class="btn btn-success">
                        <i class="fas fa-plus mr-1"></i> Nueva Producción
                    </a>
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

    <div id="cardView">
        @forelse($producciones as $p)
            <div class="produccion-card">
                <div class="produccion-header">
                    <div>
                        <h5><i class="fas fa-map-marker-alt mr-2 text-success"></i>{{ $p->lote->nombre ?? 'Sin lote' }}</h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '-' }}
                        </small>
                    </div>
                    <span class="produccion-cantidad">
                        {{ number_format($p->cantidad ?? 0, 2) }} {{ $p->unidadMedida->abreviatura ?? 'kg' }}
                    </span>
                </div>
                <div class="produccion-body">
                    <div class="produccion-info">
                        <div class="produccion-info-item">
                            <label><i class="fas fa-seedling mr-1"></i> Cultivo</label>
                            <span>
                                @if($p->lote && $p->lote->cultivo)
                                    <span class="badge badge-success">{{ $p->lote->cultivo->nombre }}</span>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="produccion-info-item">
                            <label><i class="fas fa-warehouse mr-1"></i> Destino</label>
                            <span>
                                <span class="badge badge-info">{{ $p->destino->nombre ?? 'No especificado' }}</span>
                            </span>
                        </div>
                        <div class="produccion-info-item">
                            <label><i class="fas fa-balance-scale mr-1"></i> Unidad</label>
                            <span>{{ $p->unidadMedida->nombre ?? '-' }}</span>
                        </div>

                    </div>
                </div>
                <div class="produccion-footer">
                    <small class="text-muted">
                        @if($p->lote)
                            <i class="fas fa-ruler-combined mr-1"></i> {{ $p->lote->superficie ?? 0 }} ha
                        @endif
                    </small>
                    <div>
                        <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('producciones.edit', $p) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('producciones.destroy', $p) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('¿Eliminar?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-tractor fa-4x text-muted mb-3"></i>
                    <h4>No hay producciones</h4>
                    <a href="{{ route('producciones.create') }}" class="btn btn-success">
                        <i class="fas fa-plus mr-1"></i> Nueva Producción
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <div id="tableView" style="display: none;">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Lote</th>
                            <th>Cultivo</th>
                            <th>Cantidad</th>
                            <th>Fecha</th>
                            <th>Destino</th>
                            <th style="width: 130px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($producciones as $p)
                            <tr>
                                <td><strong>{{ $p->lote->nombre ?? '-' }}</strong></td>
                                <td>
                                    @if($p->lote && $p->lote->cultivo)
                                        <span class="badge badge-success">{{ $p->lote->cultivo->nombre }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-success">
                                        {{ number_format($p->cantidad ?? 0, 2) }} {{ $p->unidadMedida->abreviatura ?? 'kg' }}
                                    </strong>
                                </td>
                                <td>{{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '-' }}</td>
                                <td><span class="badge badge-info">{{ $p->destino->nombre ?? '-' }}</span></td>
                                <td>
                                    <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('producciones.edit', $p) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('producciones.destroy', $p) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay producciones</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($producciones->hasPages())
        <div class="card mt-4">
            <div class="card-body d-flex justify-content-center">
                {{ $producciones->links() }}
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