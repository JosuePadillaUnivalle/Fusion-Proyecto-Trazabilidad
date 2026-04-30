@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Prioridad</h3>
    </div>

    <form action="{{ route('prioridades.update', $prioridad) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre de la prioridad</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       value="{{ $prioridad->nombre }}"
                       maxlength="30"
                       required>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('prioridades.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection