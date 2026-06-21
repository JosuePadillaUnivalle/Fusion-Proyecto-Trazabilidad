@php
    $item = $item ?? null;
    $mostrarGuias = $mostrarGuias ?? false;
    $tieneDescripcion = $tieneDescripcion ?? false;
@endphp

@if($mostrarGuias)
<div class="guia-campo mb-4">
    <strong>Catálogo maestro.</strong> Los registros aquí se reutilizan en lotes, inventario y operaciones.
    Usa nombres claros y únicos para evitar confusiones en reportes.
</div>
@endif

<div class="form-group">
    <label for="nombre">Nombre <span class="text-danger">*</span></label>
    @if($mostrarGuias)
    <div class="guia-campo mb-2">
        <strong>Identificador.</strong> Texto corto que verán los usuarios al seleccionar este valor en formularios.
    </div>
    @endif
    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
        value="{{ old('nombre', $item->nombre ?? '') }}" maxlength="100" required>
    @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

@if($tieneDescripcion)
<div class="form-group">
    <label for="descripcion">Descripción</label>
    @if($mostrarGuias)
    <div class="guia-campo mb-2">
        <strong>Opcional.</strong> Aclara el uso o alcance de este registro para el equipo operativo.
    </div>
    @endif
    <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion"
        rows="3" maxlength="200">{{ old('descripcion', $item->descripcion ?? '') }}</textarea>
    @error('descripcion')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
@endif
