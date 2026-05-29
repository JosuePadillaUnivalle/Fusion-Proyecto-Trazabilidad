@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Tipo de Insumo</h3>
    </div>

    <form action="{{ route('tipo-insumos.update', $tipoInsumo) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text"
                       name="nombre"
                       class="form-control"
                       value="{{ $tipoInsumo->nombre }}"
                       maxlength="50"
                       required>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('tipo-insumos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection