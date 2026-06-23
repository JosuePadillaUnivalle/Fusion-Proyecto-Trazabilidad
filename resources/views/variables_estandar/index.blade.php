@extends('layouts.app')

@section('title', 'Variables estándar | AgroFusion')
@section('page_title', 'Variables estándar')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Variables estándar</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Variables estándar"
            icono="fa-sliders-h"
            :registros="$variables->total()"
            filtros-target="#filtrosVariablesPanel"
            :nuevo-href="route('variables-estandar.create')"
        />

        <div id="filtrosVariablesPanel" class="filtros-panel collapse {{ request()->filled('buscar') || request('filtros_abiertos') === '1' ? 'show' : '' }}">
            <form method="GET" action="{{ route('variables-estandar.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-9 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}"
                               placeholder="Nombre, código, unidad o descripción…">
                    </div>
                    <div class="col-md-3">
                        <x-filtros-form-actions :limpiar-url="route('variables-estandar.index', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:55px">ID</th>
                        <th>Nombre</th>
                        <th>Unidad</th>
                        <th>Descripción</th>
                        <th style="width:130px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variables as $variable)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $variable->variableestandarid }}</td>
                        <td>
                            <strong>{{ $variable->nombre }}</strong>
                            <br><small class="text-muted">{{ $variable->codigo }}</small>
                        </td>
                        <td>{{ $variable->unidad ?: '—' }}</td>
                        <td class="text-muted">{{ \Illuminate\Support\Str::limit(preg_replace('/\[MOD-PLANTA\]\s*/', '', (string) $variable->descripcion), 80) ?: '—' }}</td>
                        <td class="text-center btn-actions">
                            <a href="{{ route('variables-estandar.show', $variable) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('variables-estandar.edit', $variable) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('variables-estandar.destroy', $variable) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la variable «{{ $variable->nombre }}»?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fas fa-sliders-h fa-3x mb-3 d-block"></i>
                            No hay variables registradas.
                            <br><a href="{{ route('variables-estandar.create') }}" class="btn btn-success btn-sm mt-2">Crear variable</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variables->hasPages())
        <div class="card-footer">{{ $variables->links() }}</div>
        @endif
    </div>
</div>
@endsection
