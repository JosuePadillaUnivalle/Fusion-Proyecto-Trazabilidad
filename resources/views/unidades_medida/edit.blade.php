@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Unidad de Medida</h3>
    </div>

    <form action="{{ route('unidades-medida.update', $unidad) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       value="{{ $unidad->nombre }}"
                       maxlength="20"
                       required>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('unidades-medida.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection