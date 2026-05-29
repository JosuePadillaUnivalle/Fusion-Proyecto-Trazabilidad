@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Nuevo Registro de Historial</h3>
    </div>

    <form action="{{ route('historial-estados-lote.store') }}" method="POST">
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
                <label>Tipo de Estado</label>
                <select name="estadolotetipoid" class="form-control" required>
                    <option value="">Seleccione...</option>
                    @foreach($tiposEstado as $t)
                        <option value="{{ $t->estadolotetipoid }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Usuario (opcional)</label>
                <select name="usuarioid" class="form-control">
                    <option value="">-- Sin usuario --</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->usuarioid }}">{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de cambio</label>
                <input type="datetime-local" name="fecha_cambio" class="form-control">
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label>URL de imagen (opcional)</label>
                <input type="text" name="imagenurl" class="form-control" maxlength="250">
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('historial-estados-lote.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Guardar</button>
        </div>

    </form>
</div>
@endsection