@extends('layouts.app')

@section('title', 'Editar Aplicación | AgroFusion')
@section('page_title', 'Editar Registro de Aplicación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lote-insumos.index') }}" style="color: #2c5530;">Aplicaciones</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-lg border-0 bg-white">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Editar Aplicación de Insumo</h4>
            </div>

            <form action="{{ route('lote-insumos.update', $loteInsumo) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body p-4">
                    
                    <div class="alert alert-warning border-left-warning shadow-sm mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
                            <div>
                                <h6 class="font-weight-bold mb-0">¡Atención!</h6>
                                <small class="text-dark">Modificar la cantidad o el insumo afectará el inventario actual. Los ajustes de stock se realizarán automáticamente.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loteid" class="font-weight-bold">Lote Destino <span class="text-danger">*</span></label>
                                <select name="loteid" class="form-control" required>
                                    @foreach($lotes as $l)
                                        <option value="{{ $l->loteid }}"
                                            {{ $l->loteid == $loteInsumo->loteid ? 'selected' : '' }}>
                                            {{ $l->nombre }} ({{ $l->cultivo->nombre ?? 'Sin cultivo' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="insumoid" class="font-weight-bold">Insumo Utilizado <span class="text-danger">*</span></label>
                                <select name="insumoid" class="form-control" required>
                                    @foreach($insumos as $i)
                                        <option value="{{ $i->insumoid }}"
                                            {{ $i->insumoid == $loteInsumo->insumoid ? 'selected' : '' }}>
                                            {{ $i->nombre }} (Stock: {{ $i->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cantidadusada" class="font-weight-bold">Cantidad Usada <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="cantidadusada"
                                       class="form-control"
                                       value="{{ $loteInsumo->cantidadusada }}" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <!-- Field hidden but kept for logic if controller needs it, though usually calculated -->
                            <div class="form-group">
                                <label for="usuarioid" class="font-weight-bold">Responsable</label>
                                <select name="usuarioid" class="form-control" readonly style="pointer-events: none; background-color: #f8f9fa;">
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->usuarioid }}"
                                            {{ $u->usuarioid == $loteInsumo->usuarioid ? 'selected' : '' }}>
                                            {{ $u->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Asignado por Lote</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fechauo" class="font-weight-bold">Fecha de Aplicación</label>
                                <input type="datetime-local" name="fechauo"
                                       class="form-control" value="{{ $loteInsumo->fechauo ? \Carbon\Carbon::parse($loteInsumo->fechauo)->format('Y-m-d\TH:i') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estadoloteinsumoid" class="font-weight-bold">Estado</label>
                                <select name="estadoloteinsumoid" class="form-control">
                                    <option value="">-- Sin estado --</option>
                                    @foreach($estados as $e)
                                        <option value="{{ $e->estadoloteinsumoid }}"
                                            {{ $e->estadoloteinsumoid == $loteInsumo->estadoloteinsumoid ? 'selected' : '' }}>
                                            {{ $e->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observaciones" class="font-weight-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" maxlength="200" pattern="^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ\.]+$">{{ $loteInsumo->observaciones }}</textarea>
                        <small class="text-muted">Solo letras, números y puntos.</small>
                    </div>

                </div>

                <div class="card-footer bg-light text-right py-3">
                    <a href="{{ route('lote-insumos.index') }}" class="btn btn-secondary mr-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check mr-1"></i> Actualizar Aplicación
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    .border-left-warning { border-left: .25rem solid #f6c23e!important; }
</style>
@endsection