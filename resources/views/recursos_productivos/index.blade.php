@extends('layouts.app')

@section('title', 'Recursos productivos')

@section('content')
<div class="alert alert-info">
    Vista consolidada de materias primas agrícolas: a la izquierda cultivos y a la derecha insumos disponibles.
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><strong>Cultivos</strong></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($cultivos as $cultivo)
                        <li class="list-group-item">{{ $cultivo->nombre }}</li>
                    @empty
                        <li class="list-group-item text-muted">Sin cultivos registrados. Cárgalos desde Catálogos > Cultivos.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><strong>Materia prima e insumos</strong></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Stock</th>
                            <th>Actor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insumos as $insumo)
                            <tr>
                                <td>{{ $insumo->nombre }}</td>
                                <td>{{ $insumo->tipo->nombre ?? '-' }}</td>
                                <td>{{ number_format((float)$insumo->stock, 2) }} {{ $insumo->unidadMedida->abreviatura ?? '' }}</td>
                                <td>{{ $insumo->actorAbastecimiento->nombre ?? $insumo->proveedor ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Sin insumos registrados. Puedes crearlos desde Inventario > Insumos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

