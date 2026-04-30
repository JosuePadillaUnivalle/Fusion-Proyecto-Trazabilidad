@extends('layouts.app')

@section('title', 'Procesos de planta')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <strong>No se pudo guardar el proceso.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header"><strong>Nuevo proceso</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('procesos-planta.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4"><input name="nombre" class="form-control" placeholder="Nombre del proceso" value="{{ old('nombre') }}" required></div>
                <div class="col-md-6"><input name="descripcion" class="form-control" placeholder="Descripción (opcional)" value="{{ old('descripcion') }}"></div>
                <div class="col-md-2"><button class="btn btn-primary btn-block">Crear</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Catálogo de procesos</strong></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse($procesos as $proceso)
                    <tr>
                        <td>{{ $proceso->nombre }}</td>
                        <td>{{ $proceso->descripcion ?? '-' }}</td>
                        <td>{{ $proceso->activo ? 'Activo' : 'Inactivo' }}</td>
                        <td>
                            <form method="POST" action="{{ route('procesos-planta.destroy', $proceso) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar proceso?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Sin procesos registrados. Agrega uno para vincularlo en el registro de producción.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $procesos->links() }}</div>
</div>
@endsection

