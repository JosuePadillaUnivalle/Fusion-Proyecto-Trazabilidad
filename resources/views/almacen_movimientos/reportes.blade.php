@extends('layouts.app')

@section('title', 'Reportes de almacén')
@section('page_title', 'Reportes de almacén')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#f2f7f3;border-bottom:0}
</style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-4"><div class="small-box bg-success"><div class="inner"><h3>{{ number_format((float) $resumenProducto->sum('ingresos'), 1) }}</h3><p>Ingresos acumulados</p></div><div class="icon"><i class="fas fa-arrow-down"></i></div></div></div>
        <div class="col-md-4"><div class="small-box bg-warning"><div class="inner"><h3>{{ number_format((float) $resumenProducto->sum('salidas'), 1) }}</h3><p>Salidas acumuladas</p></div><div class="icon"><i class="fas fa-arrow-up"></i></div></div></div>
        <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h3>{{ $movimientos->count() }}</h3><p>Movimientos consultados</p></div><div class="icon"><i class="fas fa-warehouse"></i></div></div></div>
    </div>
    <div class="card x-card">
        <div class="card-header">
            <h3 class="card-title mb-0">Filtros</h3>
        </div>
        <form method="GET" action="{{ route('almacen-movimientos.reportes') }}">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Almacén</label>
                        <select class="form-control" name="almacenid">
                            <option value="">Todos</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->almacenid }}" @selected((int) $almacenId === (int) $almacen->almacenid)>{{ $almacen->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Fecha desde</label>
                        <input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Fecha hasta</label>
                        <input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Aplicar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card x-card">
                <div class="card-header"><h3 class="card-title mb-0">Ingresos y salidas por producto</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped mb-0 x-table">
                        <thead><tr><th>Producto</th><th class="text-right">Ingresos</th><th class="text-right">Salidas</th></tr></thead>
                        <tbody>
                        @forelse($resumenProducto as $item)
                            <tr>
                                <td>{{ $item->producto }}</td>
                                <td class="text-right">{{ number_format((float) $item->ingresos, 3) }}</td>
                                <td class="text-right">{{ number_format((float) $item->salidas, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card x-card">
                <div class="card-header"><h3 class="card-title mb-0">Stock por almacén</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped mb-0 x-table">
                        <thead><tr><th>Almacén</th><th class="text-right">Stock total</th></tr></thead>
                        <tbody>
                        @forelse($stockPorAlmacen as $item)
                            <tr>
                                <td>{{ $item->almacen }}</td>
                                <td class="text-right">{{ number_format((float) $item->stock, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Sin datos</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card x-card">
        <div class="card-header"><h3 class="card-title mb-0">Movimientos recientes</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0 x-table">
                <thead>
                    <tr><th>Fecha</th><th>Almacén</th><th>Insumo</th><th>Tipo</th><th class="text-right">Cantidad</th></tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        <tr>
                            <td>{{ optional($mov->fecha)->format('Y-m-d') }}</td>
                            <td>{{ $mov->almacen?->nombre }}</td>
                            <td>{{ $mov->insumo?->nombre }}</td>
                            <td>{{ $mov->tipo?->nombre }}</td>
                            <td class="text-right">{{ number_format((float) $mov->cantidad, 3) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Sin datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
