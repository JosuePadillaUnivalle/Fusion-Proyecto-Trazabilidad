@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles de la Prioridad</h3>
    </div>

    <div class="card-body">
        <p><strong>ID:</strong> {{ $prioridad->prioridadid }}</p>
        <p><strong>Nombre:</strong> {{ $prioridad->nombre }}</p>
    </div>

    <div class="card-footer">
        <a href="{{ route('prioridades.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('prioridades.edit', $prioridad) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('prioridades.destroy', $prioridad) }}" method="POST"
              class="d-inline"
              onsubmit="return confirm('¿Eliminar prioridad?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection