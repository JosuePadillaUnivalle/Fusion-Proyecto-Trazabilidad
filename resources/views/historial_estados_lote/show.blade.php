@extends('layouts.app')

@section('title', 'Detalle historial | Fusion-Proyectos')
@section('page_title', 'Detalle de historial')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('historial-estados-lote.index') }}">Historial de estados</a></li>
    <li class="breadcrumb-item active">#{{ $registro->historial_estado_id }}</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
<div class="modulo-cat">

<div class="mb-3 d-flex flex-wrap" style="gap: 8px;">
        <a href="{{ route('historial-estados-lote.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Listado
        </a>
        <a href="{{ route('historial-estados-lote.edit', $registro) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
    </div>

    <div class="card card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-history text-success mr-1"></i>
                Registro #{{ $registro->historial_estado_id }}
            </h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3 text-muted">Lote</dt>
                <dd class="col-sm-9"><strong>{{ $registro->lote->nombre ?? '—' }}</strong></dd>
                <dt class="col-sm-3 text-muted">Estado</dt>
                <dd class="col-sm-9">
                    <span class="badge badge-light border">{{ $registro->estadoTipo->nombre ?? '—' }}</span>
                </dd>
                <dt class="col-sm-3 text-muted">Fecha del cambio</dt>
                <dd class="col-sm-9">{{ $registro->fecha_cambio ? $registro->fecha_cambio->format('d/m/Y H:i') : '—' }}</dd>
                <dt class="col-sm-3 text-muted">Usuario</dt>
                <dd class="col-sm-9">
                    @if($registro->usuario)
                        {{ $registro->usuario->nombre }} {{ $registro->usuario->apellido }}
                    @else
                        —
                    @endif
                </dd>
                <dt class="col-sm-3 text-muted">Observaciones</dt>
                <dd class="col-sm-9">{{ $registro->observaciones ?: '—' }}</dd>
                @if($registro->imagenurl)
                <dt class="col-sm-3 text-muted">Imagen</dt>
                <dd class="col-sm-9"><a href="{{ $registro->imagenurl }}" target="_blank" rel="noopener">Ver imagen</a></dd>
                @endif
            </dl>
        </div>
        <div class="card-footer d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('historial-estados-lote.edit', $registro) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <form action="{{ route('historial-estados-lote.destroy', $registro) }}" method="POST" class="d-inline"
                onsubmit="return confirm('¿Eliminar este registro?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
