@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Estado de Insumo</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $estadoLoteInsumo->estadoloteinsumoid }}</p>
        <p><strong>Nombre:</strong> {{ $estadoLoteInsumo->nombre }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('estado-lote-insumos.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('estado-lote-insumos.edit', $estadoLoteInsumo) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('estado-lote-insumos.destroy', $estadoLoteInsumo) }}" method="POST"
              class="d-inline"
              onsubmit="return confirm('¿Eliminar estado de insumo?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection