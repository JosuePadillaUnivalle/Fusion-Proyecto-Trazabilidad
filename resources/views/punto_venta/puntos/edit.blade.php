@extends('layouts.app')

@section('title', 'Editar — '.$punto->nombre)
@section('page_title', 'Editar punto de venta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('punto_venta.partials.modulo-styles')
@endpush

@section('content')
    <div class="card pdv-card card-outline card-success">
        <div class="card-header bg-white py-3 border-bottom">
            <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-edit text-success mr-2"></i>{{ $punto->nombre }}</h3>
        </div>
        <form method="POST" action="{{ route('punto-venta.puntos.update', $punto) }}" id="formPdv">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('punto_venta.puntos.partials.form', ['punto' => $punto])
            </div>
            <div class="card-footer d-flex justify-content-between flex-wrap bg-white" style="gap:.5rem;">
                <div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
                    <a href="{{ route('punto-venta.puntos.show', $punto) }}" class="btn btn-default">Cancelar</a>
                </div>
            </div>
        </form>
    </div>

    @can('punto_venta.delete')
    @if($evalEliminacion['ok'] ?? false)
    <form method="POST" action="{{ route('punto-venta.puntos.destroy', $punto) }}" class="mt-2">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-outline-danger btn-sm"
            data-confirm-modal
            data-confirm-title="Eliminar punto de venta"
            data-confirm-message="¿Eliminar «{{ $punto->nombre }}»? Esta acción no se puede deshacer."
            data-confirm-tone="danger">
            <i class="fas fa-trash mr-1"></i> Eliminar punto de venta
        </button>
    </form>
    @else
    <button type="button" class="btn btn-outline-danger btn-sm mt-2" disabled
        title="{{ $evalEliminacion['mensaje'] ?? 'Vacíe el depósito para eliminar' }}">
        <i class="fas fa-trash mr-1"></i> Eliminar punto de venta
    </button>
    <p class="small text-muted mt-1 mb-0">{{ $evalEliminacion['mensaje'] ?? '' }}</p>
    @endif
    @include('partials.modal-confirmar-accion')
    @endcan
@endsection
