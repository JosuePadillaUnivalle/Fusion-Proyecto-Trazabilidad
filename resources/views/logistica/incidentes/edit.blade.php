@extends('layouts.app')

@section('title', 'Editar incidente | AgroFusion')
@section('page_title', 'Editar incidente')

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card card-outline card-success elevation-1">
            <div class="card-header bg-white py-3"><h5 class="mb-0 font-weight-bold">Editar incidente #{{ $incidente->incidenteenvioid }}</h5></div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('logistica.incidentes.update', $incidente) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">ID de envío</label>
                            <input name="externo_envio_id" class="form-control" value="{{ old('externo_envio_id', $incidente->externo_envio_id) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">ID pedido</label>
                            <input type="number" name="pedidoid" class="form-control" value="{{ old('pedidoid', $incidente->pedidoid) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Tipo</label>
                            <input name="tipo" class="form-control" required value="{{ old('tipo', $incidente->tipo) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="5" required>{{ old('descripcion', $incidente->descripcion) }}</textarea>
                    </div>
                    @can('incidentes.resolve')
                    <div class="form-group">
                        <label class="small font-weight-bold">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="abierto" @selected(old('estado', $incidente->estado) === 'abierto')>Abierto</option>
                            <option value="pendiente" @selected(old('estado', $incidente->estado) === 'pendiente')>Pendiente</option>
                            <option value="resuelto" @selected(old('estado', $incidente->estado) === 'resuelto')>Resuelto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Nota de resolución</label>
                        <textarea name="nota_resolucion" class="form-control" rows="2">{{ old('nota_resolucion', $incidente->nota_resolucion) }}</textarea>
                    </div>
                    @endcan
                    <button class="btn btn-success"><i class="fas fa-save mr-1"></i>Guardar cambios</button>
                    <a href="{{ route('logistica.incidentes.show', $incidente) }}" class="btn btn-outline-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
