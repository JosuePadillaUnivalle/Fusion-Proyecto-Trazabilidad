@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Editar Estado de Lote</h3>
    </div>

    <form action="{{ route('estadolotes.update', $estadolote) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Lote</label>
                <select name="loteid" class="form-control">
                    @foreach($lotes as $l)
                        <option value="{{ $l->loteid }}" {{ $l->loteid == $estadolote->loteid ? 'selected' : '' }}>
                            {{ $l->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de Estado</label>
                <select name="estadolotetipoid" class="form-control">
                    @foreach($tiposEstado as $t)
                        <option value="{{ $t->estadolotetipoid }}"
                            {{ $t->estadolotetipoid == $estadolote->estadolotetipoid ? 'selected' : '' }}>
                            {{ $t->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de Registro</label>
                <input type="date" name="fecharegistro" class="form-control"
                       value="{{ $estadolote->fecharegistro }}">
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observaciones" class="form-control" maxlength="250">
                    {{ $estadolote->observaciones }}
                </textarea>
            </div>

            <div class="form-group">
                <label>URL de Imagen</label>
                <input type="text" name="imagenurl" class="form-control" maxlength="250"
                       value="{{ $estadolote->imagenurl }}">
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('estadolotes.index') }}" class="btn btn-secondary">Cancelar</a>
            <button class="btn btn-primary">Actualizar</button>
        </div>

    </form>
</div>
@endsection