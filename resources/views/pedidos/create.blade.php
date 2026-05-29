@extends('layouts.app')

@section('title', 'Nuevo Pedido')
@section('page_title', 'Crear Pedido')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Registro de pedido</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('pedidos.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Numero de solicitud</label>
                        <input type="text" name="numero_solicitud" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Nombre de planta</label>
                        <input type="text" name="nombre_planta" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="en produccion">En produccion</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Latitud</label>
                        <input type="number" step="0.000001" name="latitud" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Longitud</label>
                        <input type="number" step="0.000001" name="longitud" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Direccion</label>
                        <input type="text" name="direccion_texto" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Detalle principal</label>
                    <div class="form-row">
                        <div class="col-md-5">
                            <input type="text" name="detalles[0][cultivo_personalizado]" class="form-control" placeholder="Producto/cultivo" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="detalles[0][cantidad]" class="form-control" placeholder="Cantidad" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="detalles[0][observaciones]" class="form-control" placeholder="Observaciones">
                        </div>
                    </div>
                </div>

                @can('pedidos.create')
                    <button type="submit" class="btn btn-primary">Guardar pedido</button>
                @endcan
                <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection

