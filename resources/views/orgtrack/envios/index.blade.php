@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Envios (Fusion)</h3>
    </div>

    @include('partials.flash-messages')

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pedido</th>
                <th>Transportista</th>
                <th>Vehículo</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($envios as $e)
            <tr>
                <td>{{ $e->envioasignacionmultipleid }}</td>
                <td>{{ optional($e->pedido)->nombre_planta ?? '-' }}</td>
                <td>{{ optional($e->transportista)->nombre ?? '-' }}</td>
                <td>{{ $e->vehiculo_ref ?? '-' }}</td>
                <td>{{ $e->estado }}</td>
                <td>{{ optional($e->fecha_asignacion)->format('Y-m-d') ?? '-' }}</td>
                <td>
                    <a href="{{ route('orgtrack.envios.show', $e->envioasignacionmultipleid) }}" class="btn btn-sm btn-info">Ver</a>
                    <a href="{{ route('orgtrack.envios.edit', $e->envioasignacionmultipleid) }}" class="btn btn-sm btn-secondary">Editar</a>
                    <form action="{{ route('orgtrack.envios.destroy', $e->envioasignacionmultipleid) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Eliminar envío?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $envios->links() }}
</div>
@endsection
