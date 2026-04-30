@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles del Estado de Lote</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $estadolote->estadoid }}</p>
        <p><strong>Lote:</strong> {{ $estadolote->lote->nombre ?? '-' }}</p>
        <p><strong>Tipo de Estado:</strong> {{ $estadolote->estadoTipo->nombre ?? '-' }}</p>
        <p><strong>Fecha Registro:</strong> {{ $estadolote->fecharegistro }}</p>
        <p><strong>Observaciones:</strong> {{ $estadolote->observaciones }}</p>

        <p><strong>Imagen:</strong></p>
        @if($estadolote->imagenurl)
            <img src="{{ $estadolote->imagenurl }}" width="200" class="img-thumbnail">
        @else
            <p>No hay imagen.</p>
        @endif

    </div>

    <div class="card-footer">
        <a href="{{ route('estadolotes.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('estadolotes.edit', $estadolote) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('estadolotes.destroy', $estadolote) }}" method="POST" 
              class="d-inline" onsubmit="return confirm('¿Eliminar estado?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
    </div>

</div>
@endsection