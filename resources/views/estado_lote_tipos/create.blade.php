@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Crear Tipo de Estado</h3>
    </div>

    <form action="{{ route('estado-lote-tipos.store') }}" method="POST">
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
                <label>Descripción</label>
                <textarea name="descripcion"
                          class="form-control"
                          maxlength="200"></textarea>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('estado-lote-tipos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection