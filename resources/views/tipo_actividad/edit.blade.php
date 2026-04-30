@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Tipo de Actividad</h3>
    </div>

    <form action="{{ route('tipo-actividad.update', $tipoActividad) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       value="{{ $tipoActividad->nombre }}"
                       maxlength="50"
                       required>
            </div>

            <div class="form-group">
                <label>Descripción (opcional)</label>
                <textarea name="descripcion"
                          class="form-control"
                          maxlength="200">{{ $tipoActividad->descripcion }}</textarea>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('tipo-actividad.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection