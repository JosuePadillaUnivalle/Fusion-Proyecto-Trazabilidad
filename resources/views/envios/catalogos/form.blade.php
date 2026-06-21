@extends('layouts.app')

@php $tituloForm = ($registro ? 'Editar' : 'Nuevo').' — '.($config['titulo'] ?? 'Catálogo'); @endphp

@section('title', $tituloForm.' | AgroFusion')
@section('page_title', $tituloForm)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.catalogos.index', $tipo) }}">{{ $config['titulo'] }}</a></li>
    <li class="breadcrumb-item active">{{ $registro ? 'Editar' : 'Nuevo' }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@include('envios.catalogos.partials.estilos')
@endpush

@section('content')
@php $tema = $config['tema'] ?? \App\Support\LogisticaCatalogoRegistry::tema($tipo); @endphp
<div class="modulo-env page-cat-log" style="--cat-accent: {{ $tema['accent'] }}; --cat-soft: {{ $tema['soft'] }}; --cat-mid: {{ $tema['mid'] ?? $tema['accent'] }};">
    @include('envios.partials.alertas')

    <div class="cat-log-layout">
        @include('envios.catalogos.partials.navegacion', ['tipo' => $tipo])

        <div class="cat-log-main">
            <div class="card card-modulo-main cat-log-card mb-0">
                <div class="cat-log-header">
                    <div class="cat-log-header__left">
                        @if(!empty($config['icono']))
                            <span class="cat-log-header__icon"><i class="fas {{ $config['icono'] }}"></i></span>
                        @endif
                        <div>
                            <h5 class="cat-log-header__title">{{ $tituloForm }}</h5>
                            <div class="cat-log-header__sub">{{ $config['titulo'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body cat-log-form">
                    <form method="POST" action="{{ $registro ? route('envios.catalogos.update', [$tipo, $registro->{$config['pk']}]) : route('envios.catalogos.store', $tipo) }}">
                        @csrf
                        @if($registro) @method('PUT') @endif

                        <div class="row">
                            @foreach($config['campos'] as $campo => $meta)
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="campo_{{ $campo }}">{{ $meta['label'] }}</label>

                                        @if(($meta['tipo'] ?? '') === 'checkbox')
                                            <div class="custom-control custom-checkbox mt-1">
                                                <input type="checkbox" class="custom-control-input" id="campo_{{ $campo }}" name="{{ $campo }}" value="1"
                                                    {{ old($campo, $registro?->{$campo} ?? false) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="campo_{{ $campo }}">{{ $meta['checkbox_label'] ?? 'Activo' }}</label>
                                            </div>
                                        @elseif(($meta['tipo'] ?? '') === 'select')
                                            @php
                                                $valorSelect = old($campo, $registro?->tiposTransporte
                                                    ? \App\Support\VehiculoTransporteCatalogo::idPrincipalDesdeColeccion($registro->tiposTransporte)
                                                    : $registro?->{$campo});
                                            @endphp
                                            <select name="{{ $campo }}" id="campo_{{ $campo }}" class="form-control @error($campo) is-invalid @enderror">
                                                <option value="">Seleccione…</option>
                                                @foreach(($meta['opciones'] ?? [])() as $val => $label)
                                                    <option value="{{ $val }}" @selected((string) $valorSelect === (string) $val)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($meta['tipo'] ?? '') === 'multiselect')
                                            @php
                                                $seleccionados = old($campo, $registro?->tiposTransporte?->pluck('tipotransporteid')->all() ?? []);
                                            @endphp
                                            <select name="{{ $campo }}[]" id="campo_{{ $campo }}" class="form-control @error($campo) is-invalid @enderror" multiple size="5">
                                                @foreach(($meta['opciones'] ?? [])() as $val => $label)
                                                    <option value="{{ $val }}" @selected(in_array((string) $val, array_map('strval', (array) $seleccionados), true))>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Mantenga Ctrl (o Cmd) para seleccionar varios.</small>
                                        @else
                                            <input type="{{ str_contains($meta['rules'], 'numeric') || str_contains($meta['rules'], 'integer') ? 'number' : 'text' }}"
                                                   step="any"
                                                   name="{{ $campo }}"
                                                   id="campo_{{ $campo }}"
                                                   class="form-control @error($campo) is-invalid @enderror"
                                                   value="{{ old($campo, $registro?->{$campo}) }}"
                                                   @if(!empty($meta['readonly'])) readonly @endif
                                                   @if(!empty($meta['placeholder'])) placeholder="{{ $meta['placeholder'] }}" @endif>
                                        @endif

                                        @if(!empty($meta['ayuda']))
                                            <small class="form-text text-muted">{{ $meta['ayuda'] }}</small>
                                        @endif

                                        @error($campo)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="cat-log-form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Guardar
                            </button>
                            <a href="{{ route('envios.catalogos.index', $tipo) }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
