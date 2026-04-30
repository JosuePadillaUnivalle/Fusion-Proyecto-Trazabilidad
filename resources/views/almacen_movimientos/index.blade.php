@extends('layouts.app')

@section('title', 'Movimientos de almacén')
@section('page_title', 'Movimientos de almacén')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#f2f7f3;border-bottom:0}
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4"><div class="small-box bg-success"><div class="inner"><h3>{{ $movimientos->where('tipo.naturaleza','ingreso')->count() }}</h3><p>Ingresos listados</p></div><div class="icon"><i class="fas fa-arrow-down"></i></div></div></div>
        <div class="col-md-4"><div class="small-box bg-warning"><div class="inner"><h3>{{ $movimientos->where('tipo.naturaleza','salida')->count() }}</h3><p>Salidas listadas</p></div><div class="icon"><i class="fas fa-arrow-up"></i></div></div></div>
        <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h3>{{ $movimientos->total() }}</h3><p>Total de movimientos</p></div><div class="icon"><i class="fas fa-exchange-alt"></i></div></div></div>
    </div>
    <div class="card x-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Registro de ingresos y salidas</h3>
            <div class="d-flex gap-2">
                @can('almacen.ingresos.create')
                    <a class="btn btn-success btn-sm" href="{{ route('almacen-movimientos.create', ['naturaleza' => 'ingreso']) }}">Nuevo ingreso</a>
                @endcan
                @can('almacen.salidas.create')
                    <a class="btn btn-warning btn-sm" href="{{ route('almacen-movimientos.create', ['naturaleza' => 'salida']) }}">Nueva salida</a>
                @endcan
                @can('almacen.reportes.view')
                    <a class="btn btn-info btn-sm" href="{{ route('almacen-movimientos.reportes') }}">Reportes</a>
                @endcan
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0 x-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Almacen</th>
                        <th>Insumo</th>
                        <th class="text-right">Cantidad</th>
                        <th>Responsable</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ optional($mov->fecha)->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge {{ $mov->tipo?->naturaleza === 'ingreso' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $mov->tipo?->nombre ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $mov->almacen?->nombre ?? '-' }}</td>
                            <td>{{ $mov->insumo?->nombre ?? '-' }}</td>
                            <td class="text-right">{{ number_format((float) $mov->cantidad, 3) }} {{ $mov->insumo?->unidadMedida?->abreviatura }}</td>
                            <td>{{ trim(($mov->usuario?->nombre ?? '') . ' ' . ($mov->usuario?->apellido ?? '')) ?: '-' }}</td>
                            <td>{{ $mov->referencia ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $movimientos->links() }}
        </div>
    </div>
@endsection
