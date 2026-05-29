@extends('layouts.app')

@section('content')
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Detalles de la Actividad</h3>
    </div>

    <div class="card-body">

        <p><strong>ID:</strong> {{ $actividad->actividadid }}</p>
        <p><strong>Lote:</strong> {{ $actividad->lote->nombre ?? '-' }}</p>
        <p><strong>Usuario:</strong> {{ $actividad->usuario->nombre ?? '-' }}</p>
        <p><strong>Descripción:</strong> {{ $actividad->descripcion }}</p>
        <p><strong>Tipo:</strong> {{ $actividad->tipoActividad->nombre ?? '-' }}</p>
        <p><strong>Prioridad:</strong> {{ $actividad->prioridad->nombre ?? '-' }}</p>
        <p><strong>Fecha Inicio:</strong> {{ $actividad->fechainicio }}</p>
        <p><strong>Fecha Fin:</strong> {{ $actividad->fechafin }}</p>
        <p><strong>Observaciones:</strong> {{ $actividad->observaciones }}</p>

    </div>

    <div class="card-footer">
        <a href="{{ route('actividades.index') }}" class="btn btn-secondary">Volver</a>
        @can('lotes.update')
        <a href="{{ route('actividades.edit', $actividad) }}" class="btn btn-warning">Editar</a>

        <form action="{{ route('actividades.destroy', $actividad) }}" method="POST" 
              class="d-inline" onsubmit="return confirm('¿Eliminar actividad?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Eliminar</button>
        </form>
        @endcan
    </div>

</div>
@endsection