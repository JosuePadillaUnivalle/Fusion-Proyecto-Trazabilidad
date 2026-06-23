@extends('layouts.app')

@section('title', 'Editar variable | AgroFusion')
@section('page_title', 'Editar variable estándar')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('variables-estandar.index') }}">Variables estándar</a></li>
    <li class="breadcrumb-item active">{{ $variable->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
<style>
.var-edit-hero {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff; border-radius: 14px; padding: 1.15rem 1.35rem; margin-bottom: 1rem;
}
.var-edit-panel { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; padding: 1.25rem 1.35rem; }
</style>
@endpush

@section('content')
<div class="modulo-prod">
    <div class="var-edit-hero">
        <h2 class="h5 font-weight-bold mb-1"><i class="fas fa-edit mr-2"></i>Editar variable</h2>
        <p class="mb-0 small opacity-90">{{ $variable->nombre }} · {{ $variable->codigo }}</p>
    </div>

    <div class="var-edit-panel">
        <form method="POST" action="{{ route('variables-estandar.update', $variable) }}">
            @csrf @method('PUT')
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            @include('variables_estandar.partials.form-fields', ['variable' => $variable])
            <input type="hidden" name="activo" value="1">
            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a href="{{ route('variables-estandar.show', $variable) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
