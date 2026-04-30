@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Tipo de Insumo</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $tipoInsumo->tipoinsumoid }}</p>
        <p><strong>Nombre:</strong> {{ $tipoInsumo->nombre }}</p>

    </div>

    <div class="card-footer">

        <a href="{{ route('tipo-insumos.index') }}" class="btn btn-secondary">Volver</a>

        <a href="{{ route('tipo-insumos.edit', $tipoInsumo) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('tipo-insumos.destroy', $tipoInsumo) }}"
              method="POST" class="d-inline"
              onsubmit="return confirm('¿Eliminar tipo de insumo?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>

    </div>

</div>
@endsection