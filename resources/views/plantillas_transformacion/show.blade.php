@extends('layouts.app')



@section('title', $plantilla->nombre.' | Procesos de transformación')

@section('page_title', 'Detalle del proceso')



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('plantillas-transformacion.index') }}">Procesos de transformación</a></li>

    <li class="breadcrumb-item active">{{ $plantilla->nombre }}</li>

@endsection



@push('styles')

@include('partials.modulo-produccion-styles')

<style>

.ruta-paso-item { border-left: 3px solid #2c5530; background: #f8fbf8; border-radius: 8px; padding: .75rem 1rem; margin-bottom: .5rem; }

.ruta-paso-item--cierre { border-left-color: #17a2b8; background: #f0fafb; }

.ruta-paso-item--mantenimiento { border-left-color: #e0a800; background: #fffdf5; }

.ruta-paso-item .paso-num { width: 28px; height: 28px; border-radius: 50%; background: #2c5530; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; margin-right: .5rem; }

.ruta-paso-item--cierre .paso-num { background: #17a2b8; }

.ruta-paso-item--mantenimiento .paso-num { background: #e0a800; }

.kw-auto-box { background: #f8fbf8; border: 1px dashed #c3d9c5; border-radius: 8px; padding: .75rem 1rem; }

</style>

@endpush



@section('content')

<div class="modulo-prod">

    @if($plantilla->bloqueadaPorMantenimiento())

    <div class="alert alert-warning border mb-3">

        <i class="fas fa-wrench mr-1"></i>

        <strong>Proceso temporalmente no disponible.</strong>

        Hay máquinas en mantenimiento en este proceso:

        <strong>{{ $plantilla->maquinasEnMantenimiento()->pluck('nombre')->join(', ') }}</strong>.

        No se podrá asignar a nuevos lotes hasta que vuelvan a estar activas.

    </div>

    @endif



    <div class="card card-outline card-success mb-3">

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-start">

                <div class="flex-grow-1">

                    <h4 class="font-weight-bold text-success mb-1"><i class="fas fa-project-diagram mr-2"></i>{{ $plantilla->nombre }}</h4>

                    <p class="text-muted mb-3">{{ $plantilla->descripcion ?: 'Sin descripción' }}</p>



                    <div class="mb-2">

                        @if($plantilla->producto_ejemplo)

                            <span class="badge badge-light border px-2 py-1 mr-1">

                                <i class="fas fa-box-open text-muted mr-1"></i>

                                Producto de referencia: <strong>{{ $plantilla->producto_ejemplo }}</strong>

                            </span>

                        @endif

                        @include('plantillas_transformacion.partials.badge-estado', ['plantilla' => $plantilla])

                    </div>



                    @php $keywords = $plantilla->palabrasClaveLista(); @endphp

                    @if(count($keywords))

                    <div class="kw-auto-box mt-2">

                        <div class="small text-muted mb-2">

                            <i class="fas fa-magic text-success mr-1"></i>

                            <strong>Asignación automática al crear un lote</strong> — el sistema elige este proceso si el nombre del producto contiene alguna de estas palabras:

                        </div>

                        <div class="d-flex flex-wrap" style="gap:6px;">

                            @foreach($keywords as $kw)

                                <span class="badge badge-success px-2 py-1" title="Palabra clave para detectar el producto">

                                    <i class="fas fa-tag mr-1" style="opacity:.8;"></i>{{ $kw }}

                                </span>

                            @endforeach

                        </div>

                    </div>

                    @endif

                </div>

            </div>

            <div class="mt-3">

                <a href="{{ route('plantillas-transformacion.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>

                <a href="{{ route('plantillas-transformacion.edit', $plantilla) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit mr-1"></i> Editar</a>

            </div>

        </div>

    </div>



    <div class="card card-outline card-success card-modulo-main">

        <div class="card-header">

            <h3 class="card-title mb-0">

                <i class="fas fa-list-ol mr-1"></i>

                Etapas del proceso ({{ $plantilla->pasos->count() }} pasos)

            </h3>

        </div>

        <div class="card-body">

            <p class="small text-muted mb-3">

                Al procesar un lote con este proceso, cada etapa se registra en orden.

                La transformación finaliza al completar el último paso: <strong>Empaquetado</strong>.

            </p>

            @forelse($plantilla->pasos as $paso)

            @php

                $esCierre = \App\Support\ProcesoPlantaCatalogo::esCierreTransformacion($paso->proceso?->nombre);

                $maqMantenimiento = $paso->maquina && ! $paso->maquina->activo;

            @endphp

            <div class="ruta-paso-item d-flex align-items-start {{ $maqMantenimiento ? 'ruta-paso-item--mantenimiento' : ($esCierre ? 'ruta-paso-item--cierre' : '') }}">

                <span class="paso-num">{{ $paso->orden }}</span>

                <div class="flex-grow-1">

                    <strong>{{ $paso->proceso?->nombre ?? '—' }}</strong>

                    @if($esCierre)

                        <span class="badge badge-info ml-1">Cierra la transformación</span>

                    @endif

                    @if($paso->maquina)

                        <br><small class="text-muted"><i class="fas fa-cogs mr-1"></i>{{ $paso->maquina->nombre }}@if($paso->maquina->codigo) ({{ $paso->maquina->codigo }})@endif</small>

                        @if($maqMantenimiento)

                            <br><small class="text-warning"><i class="fas fa-wrench mr-1"></i>En mantenimiento</small>

                        @endif

                    @else

                        <br><small class="text-muted"><i class="fas fa-cogs mr-1"></i>Cualquiera compatible</small>

                    @endif

                    @if($paso->notas)<br><small class="text-secondary">{{ $paso->notas }}</small>@endif

                </div>

            </div>

            @empty

            <p class="text-muted mb-0">Sin pasos definidos.</p>

            @endforelse

        </div>

    </div>

</div>

@endsection

