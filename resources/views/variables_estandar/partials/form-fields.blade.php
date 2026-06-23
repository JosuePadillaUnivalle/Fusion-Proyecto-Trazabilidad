@php
    $variable = $variable ?? null;
    $esEdicion = (bool) $variable;
@endphp

<div class="form-group">
    <label>Código <span class="text-danger">*</span></label>
    <input name="codigo" class="form-control text-uppercase" maxlength="50" required
           value="{{ old('codigo', $variable?->codigo) }}" placeholder="Ej. TEMP, PRESION">
    <small class="text-muted">Identificador único (mayúsculas).</small>
</div>
<div class="form-group">
    <label>Nombre <span class="text-danger">*</span></label>
    <input name="nombre" class="form-control" maxlength="100" required
           value="{{ old('nombre', $variable?->nombre) }}" placeholder="Ej. Temperatura">
</div>
<div class="form-group">
    <label>Unidad de medida</label>
    <input name="unidad" class="form-control" maxlength="50"
           value="{{ old('unidad', $variable?->unidad) }}" placeholder="Ej. °C, PSI, %">
</div>
<div class="form-group">
    <label>Descripción</label>
    <textarea name="descripcion" class="form-control" rows="2" maxlength="255"
              placeholder="Breve descripción del parámetro">{{ old('descripcion', $variable?->descripcion) }}</textarea>
</div>
<div class="form-group mb-0">
    <input type="hidden" name="activo" value="0">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="activoVariable" name="activo" value="1"
            @checked(old('activo', $variable?->activo ?? true))>
        <label class="custom-control-label" for="activoVariable">Variable activa (disponible en plantillas y máquinas)</label>
    </div>
</div>
