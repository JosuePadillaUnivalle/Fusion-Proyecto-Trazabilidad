@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Cultivo</h3>
    </div>

    <form action="{{ route('cultivos.update', $cultivo) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Nombre del cultivo</label>
                <input type="text" name="nombre" class="form-control"
                       value="{{ $cultivo->nombre }}" maxlength="100" required>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('cultivos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection