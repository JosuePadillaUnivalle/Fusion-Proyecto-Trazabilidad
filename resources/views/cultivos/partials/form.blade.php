@php
    $cultivo = $cultivo ?? null;
    $formAction = $formAction ?? route('cultivos.store');
    $esEdicion = $esEdicion ?? false;
    $camposOcultos = $camposOcultos ?? [];
@endphp

@if($errors->any())
<div class="alert alert-danger border-0 shadow-sm">
    <ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="cv-guia">
    <i class="fas fa-seedling mr-1 text-success"></i>
    <strong>Catálogo de cultivos.</strong> El nombre identifica el cultivo en lotes y formularios.
    El <strong>detalle</strong> describe la variedad o uso (aparece en el selector y en reportes).
</div>

<div class="cv-card">
    <div class="cv-card-hd">
        <i class="fas {{ $esEdicion ? 'fa-edit' : 'fa-plus-circle' }} mr-2 text-success"></i>
        {{ $esEdicion ? 'Editar cultivo' : 'Nuevo cultivo' }}
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ $formAction }}">
            @csrf
            @if($esEdicion) @method('PUT') @endif
            @foreach($camposOcultos as $campoNombre => $campoValor)
                <input type="hidden" name="{{ $campoNombre }}" value="{{ $campoValor }}">
            @endforeach

            <div class="form-group">
                <label for="nombre" class="cv-form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
                       value="{{ old('nombre', $cultivo?->nombre) }}" maxlength="100" required
                       placeholder="Ej: Maíz Dulce" style="border-radius:10px;">
                <p class="cv-field-hint">Texto corto y único. Ej: «Papa Yungay», «Tomate Cherry».</p>
                @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group mb-0">
                <label for="detalle" class="cv-form-label">Detalle</label>
                <textarea class="form-control @error('detalle') is-invalid @enderror" id="detalle" name="detalle"
                          rows="4" maxlength="500" placeholder="Ej: Cosecha temprana para consumo fresco."
                          style="border-radius:10px;">{{ old('detalle', $cultivo?->detalle) }}</textarea>
                <p class="cv-field-hint">Descripción que verá el operario al seleccionar el cultivo (columna «Detalle» del buscador).</p>
                @error('detalle')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="d-flex flex-wrap mt-4" style="gap:.5rem;">
                <button type="submit" class="btn btn-success cv-btn">
                    <i class="fas fa-save mr-1"></i>{{ $esEdicion ? 'Guardar cambios' : 'Crear cultivo' }}
                </button>
                <a href="{{ $esEdicion ? route('cultivos.show', $cultivo) : route('cultivos.index') }}" class="btn btn-outline-secondary cv-btn">Cancelar</a>
                @if(! $esEdicion)
                <button type="reset" class="btn btn-outline-secondary cv-btn"><i class="fas fa-eraser mr-1"></i>Limpiar</button>
                @endif
            </div>
        </form>
    </div>
</div>
