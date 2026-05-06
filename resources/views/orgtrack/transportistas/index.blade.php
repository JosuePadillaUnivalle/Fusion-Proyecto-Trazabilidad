@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Transportistas</h3>
        <a href="{{ route('orgtrack.transportistas.create') }}" class="btn btn-primary">Nuevo</a>
    </div>

    @include('partials.flash-messages')

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transportistas as $t)
            <tr>
                <td>{{ $t->usuarioid }}</td>
                <td>{{ $t->nombre }} {{ $t->apellido }}</td>
                <td>{{ $t->email }}</td>
                <td>{{ $t->telefono }}</td>
                <td>{{ $t->activo ? 'Sí' : 'No' }}</td>
                <td>
                    <a href="{{ route('orgtrack.transportistas.edit', $t->usuarioid) }}" class="btn btn-sm btn-secondary">Editar</a>
                    <form action="{{ route('orgtrack.transportistas.destroy', $t->usuarioid) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Eliminar transportista?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $transportistas->links() }}
</div>
@endsection
