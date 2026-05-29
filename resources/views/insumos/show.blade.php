@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Insumo</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $insumo->insumoid }}</p>
        <p><strong>Nombre:</strong> {{ $insumo->nombre }}</p>
        <p><strong>Tipo:</strong> {{ $insumo->tipo->nombre ?? '-' }}</p>
        <p><strong>Unidad:</strong> {{ $insumo->unidadMedida->nombre ?? '-' }}</p>
        <p><strong>Stock:</strong> {{ $insumo->stock }}</p>
        <p><strong>Stock mínimo:</strong> {{ $insumo->stockminimo }}</p>
        <p><strong>Proveedor:</strong> {{ $insumo->proveedor ?? '-' }}</p>
        <p><strong>Precio unitario:</strong> {{ $insumo->preciounitario ?? '-' }}</p>
        <p><strong>Descripción:</strong> {{ $insumo->descripcion ?? '-' }}</p>

    </div>

    <div class="card-footer">

        <a href="{{ route('insumos.index') }}" class="btn btn-secondary">Volver</a>

        <a href="{{ route('insumos.edit', $insumo) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('insumos.destroy', $insumo) }}" method="POST"
              class="d-inline" onsubmit="return confirm('¿Eliminar insumo?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>

    </div>

</div>
@endsection