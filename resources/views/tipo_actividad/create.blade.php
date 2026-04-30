@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Crear Tipo de Actividad</h3>
    </div>

    <form action="{{ route('tipo-actividad.store') }}" method="POST">
        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       maxlength="50"
                       required>
            </div>

            <div class="form-group">
                <label>Descripción (opcional)</label>
                <textarea name="descripcion"
                          class="form-control"
                          maxlength="200"></textarea>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('tipo-actividad.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection