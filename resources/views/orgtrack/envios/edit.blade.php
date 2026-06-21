@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Editar Envío</h3>

    <form action="{{ route('orgtrack.envios.update', $envio->envioasignacionmultipleid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Estado</label>
            <input name="estado" class="form-control" value="{{ $envio->estado }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Vehículo (referencia)</label>
            <input name="vehiculo_ref" class="form-control" value="{{ $envio->vehiculo_ref }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Transportista</label>
            <select name="transportista_usuarioid" class="form-control">
                <option value="">-- Ninguno --</option>
                @foreach($transportistas as $t)
                    <option value="{{ $t->usuarioid }}" {{ $envio->transportista_usuarioid == $t->usuarioid ? 'selected' : '' }}>{{ $t->nombre }} {{ $t->apellido }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
