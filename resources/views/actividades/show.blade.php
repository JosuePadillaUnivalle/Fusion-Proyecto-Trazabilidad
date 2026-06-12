@extends('layouts.app')

@section('title', 'Actividad | AgroFusion')
@section('page_title', 'Detalle de actividad')

@section('content')
@php
    $completada = $actividad->fechafin !== null;
@endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-tasks mr-2"></i>{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}
        </h3>
        <span class="badge badge-{{ $completada ? 'success' : 'warning' }}">
            {{ $completada ? 'Completada' : 'Pendiente' }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Lote:</strong> {{ $actividad->lote->nombre ?? '—' }}</p>
                <p><strong>Responsable:</strong> {{ trim(($actividad->usuario->nombre ?? '').' '.($actividad->usuario->apellido ?? '')) ?: '—' }}</p>
                <p><strong>Prioridad:</strong> {{ $actividad->prioridad->nombre ?? '—' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Fecha inicio:</strong>
                    {{ $actividad->fechainicio ? \Carbon\Carbon::parse($actividad->fechainicio)->format('d/m/Y H:i') : '—' }}
                </p>
                <p><strong>Fecha fin:</strong>
                    @if($completada)
                        {{ \Carbon\Carbon::parse($actividad->fechafin)->format('d/m/Y H:i') }}
                    @else
                        <span class="text-muted">Pendiente de ejecución</span>
                    @endif
                </p>
            </div>
        </div>

        @if($actividad->descripcion)
            <p class="mb-1"><strong>Descripción:</strong></p>
            <p class="text-muted">{{ $actividad->descripcion }}</p>
        @endif

        @if($actividad->observaciones)
            <p class="mb-1"><strong>Observaciones:</strong></p>
            <p class="text-muted">{{ $actividad->observaciones }}</p>
        @endif
    </div>

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
        <a href="{{ route('actividades.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
        <div class="d-flex flex-wrap" style="gap: 6px;">
            @if(!empty($puedeMarcarCompletada))
                <form action="{{ route('actividades.marcar-realizada', $actividad) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-success"
                        data-confirm-modal
                        data-confirm-title="¿Marcar como completada?"
                        data-confirm-message="Se registrará «{{ $actividad->tipoActividad->nombre ?? 'Actividad' }}» en el lote «{{ $actividad->lote->nombre ?? 'Sin lote' }}» y se quitará la alerta del responsable."
                        data-confirm-tone="success">
                        <i class="fas fa-check mr-1"></i> Marcar como completada
                    </button>
                </form>
            @endif
            @can('lotes.update')
                <a href="{{ route('actividades.edit', $actividad) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <form action="{{ route('actividades.destroy', $actividad) }}" method="POST" class="d-inline on-submit-confirm">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>

@include('partials.modal-confirmar-accion')
@endsection
