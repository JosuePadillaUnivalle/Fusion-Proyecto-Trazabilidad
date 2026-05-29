@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Tipo de Almacén</h3>
    </div>

    <form action="{{ route('tipoalmacenes.update', $tipoalmacen) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control"
                       maxlength="50" required
                       value="{{ $tipoalmacen->nombre }}">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="descripcion" class="form-control"
                       maxlength="200" value="{{ $tipoalmacen->descripcion }}">
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('tipoalmacenes.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection