@extends('layouts.app')

@section('title', ($config['titulo'] ?? 'Catálogo').' | AgroFusion')
@section('page_title', $config['titulo'] ?? 'Catálogo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procesamiento.index') }}">Producción planta</a></li>
    <li class="breadcrumb-item active">{{ $config['titulo'] }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@include('envios.catalogos.partials.estilos')
@endpush

@section('content')
@php
    $tema = $config['tema'] ?? \App\Support\PlantaCatalogoRegistry::tema($tipo);
@endphp
<div class="modulo-env page-cat-log" style="--cat-accent: {{ $tema['accent'] }}; --cat-soft: {{ $tema['soft'] }}; --cat-mid: {{ $tema['mid'] ?? $tema['accent'] }};">
    @include('envios.partials.alertas')

    <div class="card card-modulo-main cat-log-card mb-0">
        <div class="cat-log-header">
            <div class="cat-log-header__left">
                @if(!empty($config['icono']))
                    <span class="cat-log-header__icon"><i class="fas {{ $config['icono'] }}"></i></span>
                @endif
                <div>
                    <h5 class="cat-log-header__title">{{ $config['titulo'] }}</h5>
                    <div class="cat-log-header__sub">{{ $config['subtitulo'] ?? 'Empaques comerciales para productos terminados de planta' }}</div>
                </div>
            </div>
            @canany(['lote_produccion.create', 'envios.create'])
                <a href="{{ route('produccion-planta.catalogos.create', $tipo) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
            @endcanany
        </div>

        <div class="table-responsive">
            <table class="table cat-log-table table-hover mb-0">
                <thead>
                    <tr>
                        @foreach($config['columnas'] as $col)
                            <th>{{ \App\Support\PlantaCatalogoRegistry::etiquetaColumna($config, $col) }}</th>
                        @endforeach
                        <th class="text-right" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $row)
                        <tr>
                            @foreach($config['columnas'] as $i => $col)
                                @php
                                    $valor = $row;
                                    foreach (explode('.', $col) as $part) {
                                        $valor = $valor?->{$part};
                                    }
                                    if (is_bool($valor)) {
                                        $display = $valor ? 'Sí' : 'No';
                                    } else {
                                        $display = ($valor === null || $valor === '') ? '—' : $valor;
                                    }
                                @endphp
                                <td @class(['cat-log-cell--primary' => $i === 0])>{{ $display }}</td>
                            @endforeach
                            <td class="text-right text-nowrap">
                                @canany(['lote_produccion.update', 'envios.update'])
                                    <a href="{{ route('produccion-planta.catalogos.edit', [$tipo, $row->{$config['pk']}]) }}" class="btn btn-outline-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcanany
                                @canany(['lote_produccion.delete', 'envios.delete'])
                                    <form action="{{ route('produccion-planta.catalogos.destroy', [$tipo, $row->{$config['pk']}]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este registro?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endcanany
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($config['columnas']) + 1 }}" class="text-center text-muted cat-log-empty">
                                <div class="cat-log-empty__icon"><i class="fas {{ $config['icono'] ?? 'fa-inbox' }}"></i></div>
                                Sin registros en este catálogo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registros->hasPages())
            <div class="card-footer border-top">{{ $registros->links() }}</div>
        @endif
    </div>
</div>
@endsection
