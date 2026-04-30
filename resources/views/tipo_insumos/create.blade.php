@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Crear Tipo de Insumo</h3>
    </div>

    <form action="{{ route('tipo-insumos.store') }}" method="POST">
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

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('tipo-insumos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection