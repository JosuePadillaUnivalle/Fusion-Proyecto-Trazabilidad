@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Tipo de Estado</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $estadoLoteTipo->estadolotetipoid }}</p>
        <p><strong>Nombre:</strong> {{ $estadoLoteTipo->nombre }}</p>
        <p><strong>Descripción:</strong> {{ $estadoLoteTipo->descripcion }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('estado-lote-tipos.index') }}" class="btn btn-secondary">Volver</a>

        <a href="{{ route('estado-lote-tipos.edit', $estadoLoteTipo) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('estado-lote-tipos.destroy', $estadoLoteTipo) }}"
              method="POST" class="d-inline"
              onsubmit="return confirm('¿Eliminar tipo de estado?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection