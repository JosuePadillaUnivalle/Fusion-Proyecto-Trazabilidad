@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Registrar Estado de Lote</h3>
    </div>

    <form action="{{ route('estadolotes.store') }}" method="POST">
        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Lote</label>
                <select name="loteid" class="form-control" required>
                    @foreach($lotes as $l)
                        <option value="{{ $l->loteid }}">{{ $l->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de Estado</label>
                <select name="estadolotetipoid" class="form-control" required>
                    @foreach($tiposEstado as $t)
                        <option value="{{ $t->estadolotetipoid }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de Registro</label>
                <input type="date" name="fecharegistro" class="form-control">
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" maxlength="250"></textarea>
            </div>

            <div class="form-group">
                <label>URL de Imagen</label>
                <input type="text" name="imagenurl" class="form-control" maxlength="250">
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('estadolotes.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection