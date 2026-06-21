@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Registro Climático</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $clima->climaid }}</p>
        <p><strong>Lote:</strong> {{ $clima->lote->nombre ?? '-' }}</p>
        <p><strong>Fecha:</strong> {{ $clima->fecha }}</p>
        <p><strong>Temperatura:</strong> {{ $clima->temperatura }} °C</p>
        <p><strong>Humedad:</strong> {{ $clima->humedad }} %</p>
        <p><strong>Lluvia:</strong> {{ $clima->lluvia }} mm</p>
        <p><strong>Observaciones:</strong> {{ $clima->observaciones }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('climas.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('climas.edit', $clima) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('climas.destroy', $clima) }}" method="POST" 
              class="d-inline" onsubmit="return confirm('¿Eliminar este registro?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection