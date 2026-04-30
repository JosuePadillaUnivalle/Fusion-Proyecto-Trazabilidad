@extends('layouts.app')

@section('title', 'Máquinas de planta')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <strong>No se pudo guardar la máquina.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-header"><strong>Nueva máquina</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('maquinas-planta.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4"><input name="nombre" class="form-control" placeholder="Nombre de máquina" value="{{ old('nombre') }}" required></div>
                <div class="col-md-2"><input name="codigo" class="form-control" placeholder="Código" value="{{ old('codigo') }}"></div>
                <div class="col-md-4"><input name="descripcion" class="form-control" placeholder="Descripción (opcional)" value="{{ old('descripcion') }}"></div>
                <div class="col-md-2"><button class="btn btn-primary btn-block">Crear</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Catálogo de máquinas</strong></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Nombre</th><th>Código</th><th>Descripción</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse($maquinas as $maquina)
                    <tr>
                        <td>{{ $maquina->nombre }}</td>
                        <td>{{ $maquina->codigo ?? '-' }}</td>
                        <td>{{ $maquina->descripcion ?? '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('maquinas-planta.destroy', $maquina) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar máquina?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Sin máquinas registradas. Agrega una para seleccionar equipo durante el registro de producción.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $maquinas->links() }}</div>
</div>
@endsection

