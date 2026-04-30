@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Tipo de Actividad</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $tipoActividad->tipoactividadid }}</p>
        <p><strong>Nombre:</strong> {{ $tipoActividad->nombre }}</p>
        <p><strong>Descripción:</strong> {{ $tipoActividad->descripcion ?? '-' }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('tipo-actividad.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('tipo-actividad.edit', $tipoActividad) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('tipo-actividad.destroy', $tipoActividad) }}" method="POST"
              class="d-inline"
              onsubmit="return confirm('¿Eliminar tipo de actividad?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection