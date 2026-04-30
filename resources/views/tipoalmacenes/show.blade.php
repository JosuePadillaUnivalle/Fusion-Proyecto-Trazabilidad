@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Tipo de Almacén</h3>
    </div>

    <div class="card-body">
        <p><strong>ID:</strong> {{ $tipoalmacen->tipoalmacenid }}</p>
        <p><strong>Nombre:</strong> {{ $tipoalmacen->nombre }}</p>
        <p><strong>Descripción:</strong> {{ $tipoalmacen->descripcion }}</p>
    </div>

    <div class="card-footer">
        <a href="{{ route('tipoalmacenes.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('tipoalmacenes.edit', $tipoalmacen) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('tipoalmacenes.destroy', $tipoalmacen) }}"
              method="POST" class="d-inline"
              onsubmit="return confirm('¿Eliminar tipo de almacén?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection