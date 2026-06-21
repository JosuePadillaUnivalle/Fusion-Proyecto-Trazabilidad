@extends('layouts.app')

@section('title', 'Mis ingresos | AgroFusion')
@section('page_title', 'Mis ingresos')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.ingresos-metric{border:0;border-radius:12px;box-shadow:0 4px 14px rgba(18,38,63,.08);color:#fff}
.ingresos-metric .inner{padding:.85rem 1rem}
.ingresos-metric h3{font-size:1.6rem;font-weight:800;margin:0}
.ingresos-metric p{font-size:.78rem;margin:0;opacity:.9}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center">
        <p class="text-muted mb-0">Ingresos por servicios de transporte completados (bolivianos).</p>
        <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Mis envíos
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => route('logistica.transportista.ingresos'),
        ])

        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <div class="small-box ingresos-metric" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                    <div class="inner">
                        <h3>{{ number_format($resumen['total_bs'], 2, ',', '.') }}</h3>
                        <p>Total ingresos ({{ $filtros->etiquetaPeriodo() }})</p>
                    </div>
                    <div class="icon"><i class="fas fa-coins"></i></div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="small-box ingresos-metric" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                    <div class="inner">
                        <h3>{{ $resumen['servicios'] }}</h3>
                        <p>Servicios completados</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-double"></i></div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="small-box ingresos-metric" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
                    <div class="inner">
                        <h3>{{ number_format($resumen['agricola_bs'], 2, ',', '.') }}</h3>
                        <p>Almacén → Planta</p>
                    </div>
                    <div class="icon"><i class="fas fa-warehouse"></i></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-list text-success mr-2"></i>Detalle de ingresos
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th class="text-right">Monto (Bs)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicios as $servicio)
                        <tr>
                            <td><span class="badge badge-{{ $servicio['tipo'] === 'agricola' ? 'success' : 'primary' }}">{{ $servicio['tipo_etiqueta'] }}</span></td>
                            <td class="font-weight-bold">{{ $servicio['codigo'] }}</td>
                            <td>{{ $servicio['descripcion'] }}</td>
                            <td class="text-muted">{{ $servicio['fecha']?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right font-weight-bold text-success">{{ number_format($servicio['costo_bs'], 2, ',', '.') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ $servicio['ver_url'] }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No hay servicios completados con costo registrado en este periodo.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($servicios->isNotEmpty())
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="4" class="text-right">Total periodo:</th>
                            <th class="text-right text-success">{{ number_format($resumen['total_bs'], 2, ',', '.') }} Bs</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
