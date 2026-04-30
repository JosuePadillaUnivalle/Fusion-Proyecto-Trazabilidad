@extends('layouts.app')

@section('title', 'Actores de abastecimiento')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <strong>No se pudo guardar el actor.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="alert alert-info">
    Este módulo unifica productores y proveedores en una sola entidad operativa para evitar duplicados.
</div>

<div class="card mb-3">
    <div class="card-header"><strong>Nuevo actor de abastecimiento</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('actores-abastecimiento.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4"><input name="nombre" class="form-control" placeholder="Nombre" value="{{ old('nombre') }}" required></div>
                <div class="col-md-2">
                    <select name="tipo_actor" class="form-control" required>
                        <option value="productor" {{ old('tipo_actor') === 'productor' ? 'selected' : '' }}>Productor</option>
                        <option value="proveedor" {{ old('tipo_actor') === 'proveedor' ? 'selected' : '' }}>Proveedor</option>
                        <option value="mixto" {{ old('tipo_actor') === 'mixto' ? 'selected' : '' }}>Mixto</option>
                    </select>
                </div>
                <div class="col-md-2"><input name="telefono" class="form-control" placeholder="Teléfono" value="{{ old('telefono') }}"></div>
                <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email" value="{{ old('email') }}"></div>
                <div class="col-md-1"><button class="btn btn-primary btn-block">Crear</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Actores consolidados</strong></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actores as $actor)
                    <tr>
                        <td>{{ $actor->nombre }}</td>
                        <td>{{ ucfirst($actor->tipo_actor) }}</td>
                        <td>{{ $actor->telefono ?? '-' }}<br>{{ $actor->email ?? '' }}</td>
                        <td>{{ $actor->activo ? 'Activo' : 'Inactivo' }}</td>
                        <td class="d-flex">
                            <form method="POST" action="{{ route('actores-abastecimiento.destroy', $actor) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar actor?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No hay actores registrados. Crea el primero con el formulario superior.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $actores->links() }}</div>
</div>
@endsection

