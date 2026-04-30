@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles de la Unidad de Medida</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $unidad->unidadmedidaid }}</p>
        <p><strong>Nombre:</strong> {{ $unidad->nombre }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('unidades-medida.index') }}" class="btn btn-secondary">Volver</a>

        <a href="{{ route('unidades-medida.edit', $unidad) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('unidades-medida.destroy', $unidad) }}" method="POST"
              class="d-inline"
              onsubmit="return confirm('¿Eliminar unidad de medida?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection