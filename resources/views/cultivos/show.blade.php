@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Cultivo</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $cultivo->cultivoid }}</p>
        <p><strong>Nombre:</strong> {{ $cultivo->nombre }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('cultivos.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('cultivos.destroy', $cultivo) }}" method="POST" 
              class="d-inline" onsubmit="return confirm('¿Eliminar cultivo?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection