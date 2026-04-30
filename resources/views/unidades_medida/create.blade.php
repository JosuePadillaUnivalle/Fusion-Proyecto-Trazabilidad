@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Crear Unidad de Medida</h3>
    </div>

    <form action="{{ route('unidades-medida.store') }}" method="POST">
        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       maxlength="20"
                       required>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('unidades-medida.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection