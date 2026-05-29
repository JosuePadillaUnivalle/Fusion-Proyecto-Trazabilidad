@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Crear Estado de Insumo</h3>
    </div>

    <form action="{{ route('estado-lote-insumos.store') }}" method="POST">
        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Nombre del estado</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       maxlength="50"
                       required>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('estado-lote-insumos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection