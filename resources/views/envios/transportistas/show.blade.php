@extends('layouts.app')

@section('title', 'Transportista | AgroFusion')
@section('page_title', 'Detalle del transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.transportistas') }}">Transportistas</a></li>
    <li class="breadcrumb-item active">{{ $transportista->nombreCompleto() }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    @include('envios.partials.alertas')

    <div class="mb-3">
        <a href="{{ route('envios.transportistas') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
        @can('transportistas.update')
        <a href="{{ route('envios.transportistas.edit', $transportista) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        @endcan
        @can('transportistas.delete')
        <form method="POST" action="{{ route('envios.transportistas.destroy', $transportista) }}" class="d-inline"
              onsubmit="return confirm('¿Eliminar a {{ $transportista->nombreCompleto() }}?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash mr-1"></i> Eliminar</button>
        </form>
        @endcan
    </div>

    <div class="card card-modulo-main">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-user mr-2"></i>{{ $transportista->nombreCompleto() }}</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Categoría</dt>
                <dd class="col-sm-9">
                    @php $ambito = $transportista->perfilTransportista?->ambito_flota ?? \App\Support\TransportistaFlotaCatalogo::AGRICOLA; @endphp
                    <span class="badge {{ \App\Support\TransportistaFlotaCatalogo::badgeClase($ambito) }}">
                        {{ \App\Support\TransportistaFlotaCatalogo::categoriaCorta($ambito) }}
                    </span>
                </dd>
                <dt class="col-sm-3">Correo</dt>
                <dd class="col-sm-9">{{ $transportista->email }}</dd>
                <dt class="col-sm-3">Usuario</dt>
                <dd class="col-sm-9">{{ $transportista->nombreusuario ?? '—' }}</dd>
                <dt class="col-sm-3">Teléfono</dt>
                <dd class="col-sm-9">{{ $transportista->telefono ?? '—' }}</dd>
                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">
                    @if($transportista->activo)
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-secondary">Inactivo</span>
                    @endif
                </dd>
                <dt class="col-sm-3">Registrado</dt>
                <dd class="col-sm-9">{{ $transportista->fecharegistro ? \Carbon\Carbon::parse($transportista->fecharegistro)->format('d/m/Y H:i') : '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
