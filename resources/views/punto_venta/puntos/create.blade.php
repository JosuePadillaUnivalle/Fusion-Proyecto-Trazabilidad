@extends('layouts.app')

@section('title', 'Nuevo punto de venta')
@section('page_title', 'Nuevo punto de venta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('punto_venta.partials.modulo-styles')
@endpush

@section('content')
    <div class="card pdv-card card-outline card-success">
        <div class="card-header bg-success text-white py-3">
            <h3 class="card-title mb-0"><i class="fas fa-store mr-2"></i>Registrar punto de venta</h3>
        </div>
        <form method="POST" action="{{ route('punto-venta.puntos.store') }}" id="formPdv">
            @csrf
            <div class="card-body">
                @include('punto_venta.puntos.partials.form')
            </div>
            <div class="card-footer d-flex justify-content-between bg-white">
                <a href="{{ route('punto-venta.puntos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Guardar punto de venta
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('formPdv')?.addEventListener('submit', function (e) {
    if (!document.getElementById('latitud').value || !document.getElementById('longitud').value) {
        e.preventDefault();
        alert('Marque la ubicación del punto de venta en el mapa.');
    }
});
</script>
@endpush
