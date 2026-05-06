@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Editar transportista</h3>

    @include('partials.flash-messages')

    <form action="{{ route('orgtrack.transportistas.update', $transportista->usuarioid) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="nombre" class="form-control" value="{{ $transportista->nombre }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input name="apellido" class="form-control" value="{{ $transportista->apellido }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ $transportista->email }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input name="telefono" class="form-control" value="{{ $transportista->telefono }}">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="activo" class="form-check-input" value="1" {{ $transportista->activo ? 'checked' : '' }}>
            <label class="form-check-label">Activo</label>
        </div>

        <button class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
