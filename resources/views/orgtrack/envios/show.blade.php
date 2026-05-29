@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Detalle Envío</h3>

    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $envio->envioasignacionmultipleid }}</td></tr>
        <tr><th>Pedido</th><td>{{ optional($envio->pedido)->nombre_planta ?? '-' }}</td></tr>
        <tr><th>Transportista</th><td>{{ optional($envio->transportista)->nombre ?? '-' }}</td></tr>
        <tr><th>Vehículo</th><td>{{ $envio->vehiculo_ref ?? '-' }}</td></tr>
        <tr><th>Estado</th><td>{{ $envio->estado }}</td></tr>
        <tr><th>Fecha asignación</th><td>{{ optional($envio->fecha_asignacion)->format('Y-m-d H:i') ?? '-' }}</td></tr>
    </table>

    <a href="{{ route('orgtrack.envios.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection
