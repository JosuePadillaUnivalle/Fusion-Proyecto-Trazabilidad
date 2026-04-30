@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Tipo de Estado</h3>
    </div>

    <form action="{{ route('estado-lote-tipos.update', $estadoLoteTipo) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       value="{{ $estadoLoteTipo->nombre }}"
                       maxlength="50"
                       required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"
                          class="form-control"
                          maxlength="200">{{ $estadoLoteTipo->descripcion }}</textarea>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('estado-lote-tipos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection