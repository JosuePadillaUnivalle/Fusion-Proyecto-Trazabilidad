@extends('layouts.app')

@section('title', 'Editar proceso | AgroFusion')
@section('page_title', 'Editar proceso de transformación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('plantillas-transformacion.index') }}">Procesos de transformación</a></li>
    <li class="breadcrumb-item active">{{ $plantilla->nombre }}</li>
@endsection

@push('styles')@include('partials.modulo-produccion-styles')@endpush

@section('content')
<div class="modulo-prod">
    <form method="POST" action="{{ route('plantillas-transformacion.update', $plantilla) }}">
        @csrf @method('PUT')
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @php
            $pasosOld = old('pasos');
            $pasosIniciales = $pasosOld ?: $plantilla->pasos->map(fn ($p) => [
                'procesoplantaid' => $p->procesoplantaid,
                'maquinaplantaid' => $p->maquinaplantaid,
                'notas' => $p->notas,
                'variables' => $p->variables->map(fn ($v) => [
                    'variableestandarid' => $v->variableestandarid,
                    'valor_minimo' => $v->valor_minimo,
                    'valor_maximo' => $v->valor_maximo,
                    'obligatorio' => $v->obligatorio,
                ])->all(),
            ])->all();
        @endphp

        @include('plantillas_transformacion.partials.form-proceso-layout', [
            'tituloHero' => 'Editar: '.$plantilla->nombre,
            'nombreValor' => old('nombre', $plantilla->nombre),
            'descripcionValor' => old('descripcion', $plantilla->descripcion),
            'pasosIniciales' => $pasosIniciales,
        ])

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('plantillas-transformacion.show', $plantilla) }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
