@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Crear transportista</h3>

    @include('partials.flash-messages')

    <form action="{{ route('orgtrack.transportistas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input name="apellido" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input name="telefono" class="form-control">
        </div>

        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
