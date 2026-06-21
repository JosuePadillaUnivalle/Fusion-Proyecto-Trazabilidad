@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Registro Climático</h3>
    </div>

    <form action="{{ route('climas.update', $clima) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Lote</label>
                <select name="loteid" class="form-control">
                    @foreach($lotes as $l)
                        <option value="{{ $l->loteid }}"
                            {{ $l->loteid == $clima->loteid ? 'selected' : '' }}>
                            {{ $l->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fecha</label>
                <input type="datetime-local" name="fecha" class="form-control"
                       value="{{ $clima->fecha }}">
            </div>

            <div class="form-group">
                <label>Temperatura (°C)</label>
                <input type="number" step="0.01" name="temperatura"
                       class="form-control" value="{{ $clima->temperatura }}">
            </div>

            <div class="form-group">
                <label>Humedad (%)</label>
                <input type="number" step="0.01" name="humedad"
                       class="form-control" value="{{ $clima->humedad }}" min="0" max="100">
            </div>

            <div class="form-group">
                <label>Lluvia (mm)</label>
                <input type="number" step="0.01" name="lluvia"
                       class="form-control" value="{{ $clima->lluvia }}" min="0">
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" maxlength="200">{{ $clima->observaciones }}</textarea>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('climas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection