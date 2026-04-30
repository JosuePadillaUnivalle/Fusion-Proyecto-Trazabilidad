@extends('layouts.app')

@section('title', ucfirst($naturaleza) . ' de almacén')
@section('page_title', ucfirst($naturaleza) . ' de almacén')
@push('styles')
<style>.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}</style>
@endpush

@section('content')
    <div class="card x-card">
        <div class="card-header">
            <h3 class="card-title mb-0">Registrar {{ $naturaleza === 'ingreso' ? 'ingreso' : 'salida' }}</h3>
        </div>
        <form method="POST" action="{{ route('almacen-movimientos.store', ['naturaleza' => $naturaleza]) }}">
            @csrf
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Almacén</label>
                        <select name="almacenid" class="form-control" required>
                            <option value="">Seleccione...</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->almacenid }}" @selected(old('almacenid') == $almacen->almacenid)>{{ $almacen->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Insumo</label>
                        <select name="insumoid" class="form-control" required>
                            <option value="">Seleccione...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->insumoid }}" @selected(old('insumoid') == $insumo->insumoid)>
                                    {{ $insumo->nombre }} (Stock: {{ number_format((float) $insumo->stock, 3) }} {{ $insumo->unidadMedida?->abreviatura }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Tipo</label>
                        <select name="tipo_movimiento_almacenid" class="form-control" required>
                            <option value="">Seleccione...</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->tipo_movimiento_almacenid }}" @selected(old('tipo_movimiento_almacenid') == $tipo->tipo_movimiento_almacenid)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->toDateString()) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="cantidad" class="form-control" value="{{ old('cantidad') }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Referencia</label>
                        <input type="text" name="referencia" class="form-control" value="{{ old('referencia') }}" maxlength="100">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Destino / motivo</label>
                        <input type="text" name="destino_motivo" class="form-control" value="{{ old('destino_motivo') }}" maxlength="150">
                    </div>
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3" class="form-control">{{ old('observaciones') }}</textarea>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('almacen-movimientos.index') }}" class="btn btn-secondary">Volver</a>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
@endsection
