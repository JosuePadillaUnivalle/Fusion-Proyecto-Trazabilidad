@php $d = $direccion ?? null; @endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre del punto <span class="text-danger">*</span></label>
            <input name="nombre" class="form-control" value="{{ old('nombre', $d?->nombre) }}" required maxlength="255">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Tipo de punto <span class="text-danger">*</span></label>
            <select name="tipo_punto" class="form-control" required>
                <option value="origen" @selected(old('tipo_punto', $d?->tipo_punto) === 'origen')>Origen</option>
                <option value="destino" @selected(old('tipo_punto', $d?->tipo_punto) === 'destino')>Destino</option>
                <option value="hub" @selected(old('tipo_punto', $d?->tipo_punto ?? 'hub') === 'hub')>Hub / punto logístico</option>
            </select>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>Dirección completa <span class="text-danger">*</span></label>
            <textarea name="direccion_completa" class="form-control" rows="2" required>{{ old('direccion_completa', $d?->direccion_completa) }}</textarea>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Ciudad <span class="text-danger">*</span></label>
            <input name="ciudad" class="form-control" value="{{ old('ciudad', $d?->ciudad) }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Departamento</label>
            <input name="departamento" class="form-control" value="{{ old('departamento', $d?->departamento) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>País</label>
            <input name="pais" class="form-control" value="{{ old('pais', $d?->pais ?? 'Bolivia') }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Latitud</label>
            <input type="number" step="any" name="latitud" class="form-control" value="{{ old('latitud', $d?->latitud) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Longitud</label>
            <input type="number" step="any" name="longitud" class="form-control" value="{{ old('longitud', $d?->longitud) }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Referencia</label>
            <input name="referencia" class="form-control" value="{{ old('referencia', $d?->referencia) }}">
        </div>
    </div>
    <div class="col-md-12">
        <input type="hidden" name="activo" value="0">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="activoDireccion" name="activo" value="1"
                   @checked(old('activo', $d?->activo ?? true))>
            <label class="custom-control-label" for="activoDireccion">Dirección activa</label>
        </div>
    </div>
</div>
