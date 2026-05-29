@php
    $esEdicion = $esEdicion ?? false;
@endphp

<div class="modulo-cat">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Revisa el formulario.</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="mb-3">
        <a href="{{ $esEdicion ? route('historial-estados-lote.show', $registro) : route('historial-estados-lote.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> {{ $esEdicion ? 'Volver al detalle' : 'Volver al listado' }}
        </a>
    </div>

    <div class="card card-outline card-success card-form-modulo elevation-1 {{ $esEdicion ? 'card-edit' : '' }}">
        <div class="card-header">
            <h3 class="card-title mb-0 {{ $esEdicion ? '' : 'text-white' }}">
                <i class="fas {{ $esEdicion ? 'fa-edit' : 'fa-history' }} mr-1"></i>
                {{ $esEdicion ? 'Editar registro #'.$registro->historial_estado_id : 'Nuevo registro de historial' }}
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if($esEdicion)
                    @method('PUT')
                @endif

                @include('catalogos.partials.historial-form-fields', [
                    'registro' => $registro,
                    'lotes' => $lotes,
                    'tiposEstado' => $tiposEstado,
                    'usuarios' => $usuarios,
                    'mostrarGuias' => ! $esEdicion,
                ])

                <div class="d-flex flex-wrap mt-3" style="gap: 8px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Registrar cambio' }}
                    </button>
                    <a href="{{ $esEdicion ? route('historial-estados-lote.show', $registro) : route('historial-estados-lote.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
