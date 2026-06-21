@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Nuevo Registro Climático</h3>
    </div>

    <form action="{{ route('climas.store') }}" method="POST">
        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Lote</label>
                <select name="loteid" class="form-control" required>
                    <option value="">Seleccione...</option>
                    @foreach($lotes as $l)
                        <option value="{{ $l->loteid }}">{{ $l->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fecha</label>
                <input type="datetime-local" name="fecha" class="form-control">
            </div>

            <div class="form-group">
                <label>Temperatura (°C)</label>
                <input type="number" step="0.01" name="temperatura" class="form-control">
            </div>

            <div class="form-group">
                <label>Humedad (%)</label>
                <input type="number" step="0.01" name="humedad" class="form-control" min="0" max="100">
            </div>

            <div class="form-group">
                <label>Lluvia (mm)</label>
                <input type="number" step="0.01" name="lluvia" class="form-control" min="0">
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" maxlength="200"></textarea>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('climas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection