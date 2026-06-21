@php
    $esEdicion = $esEdicion ?? false;
    $tieneDescripcion = $tieneDescripcion ?? false;
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
        <a href="{{ $esEdicion ? route($routePrefix.'.show', $item) : route($routePrefix.'.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> {{ $esEdicion ? 'Volver al detalle' : 'Volver al listado' }}
        </a>
    </div>

    <div class="card card-outline card-success card-form-modulo elevation-1 {{ $esEdicion ? 'card-edit' : '' }}">
        <div class="card-header">
            <h3 class="card-title mb-0 {{ $esEdicion ? '' : 'text-white' }}">
                <i class="fas {{ $esEdicion ? 'fa-edit' : 'fa-plus-circle' }} mr-1"></i>
                {{ $esEdicion ? 'Editar: '.$item->nombre : 'Nuevo '.$singular }}
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if($esEdicion)
                    @method('PUT')
                @endif

                @foreach($camposOcultos ?? [] as $campoNombre => $campoValor)
                    <input type="hidden" name="{{ $campoNombre }}" value="{{ $campoValor }}">
                @endforeach

                @include('catalogos.partials.simple-form-fields', [
                    'item' => $item,
                    'mostrarGuias' => ! $esEdicion,
                    'tieneDescripcion' => $tieneDescripcion,
                ])

                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Crear '.$singular }}
                    </button>
                    <a href="{{ $esEdicion ? route($routePrefix.'.show', $item) : route($routePrefix.'.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    @if(! $esEdicion)
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
