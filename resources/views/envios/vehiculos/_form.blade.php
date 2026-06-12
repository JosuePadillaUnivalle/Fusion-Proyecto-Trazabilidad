@php $v = $vehiculo ?? null; @endphp
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Placa <span class="text-danger">*</span></label>
            <input name="placa" class="form-control text-uppercase" value="{{ old('placa', $v?->placa) }}" required maxlength="20">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Marca</label>
            <input name="marca" class="form-control" value="{{ old('marca', $v?->marca) }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Modelo</label>
            <input name="modelo" class="form-control" value="{{ old('modelo', $v?->modelo) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Año</label>
            <input type="number" name="anio" class="form-control" value="{{ old('anio', $v?->anio) }}" min="1980" max="2100">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Color</label>
            <input name="color" class="form-control" value="{{ old('color', $v?->color) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo de vehículo <span class="text-danger">*</span></label>
            <select name="tipovehiculoid" class="form-control" required>
                <option value="">— Seleccionar —</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->tipovehiculoid }}" @selected((string) old('tipovehiculoid', $v?->tipovehiculoid) === (string) $tipo->tipovehiculoid)>
                        {{ $tipo->nombre }}@if($tipo->capacidad_kg) ({{ number_format((float) $tipo->capacidad_kg, 0) }} kg)@endif
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">La capacidad (kg) se toma del catálogo de tipos.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Categoría <span class="text-danger">*</span></label>
            <select name="ambito_flota" class="form-control" required>
                @foreach(\App\Support\TransportistaFlotaCatalogo::etiquetas() as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('ambito_flota', $v?->ambito_flota ?? 'agricola') === $valor)>
                    {{ \App\Support\TransportistaFlotaCatalogo::categoriaCorta($valor) }}
                </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Agrícola: flota de envíos a planta. Planta: reparto a minoristas.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado operativo</label>
            <select name="estadovehiculoid" class="form-control">
                <option value="">— Seleccionar —</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->estadovehiculoid }}" @selected((string) old('estadovehiculoid', $v?->estadovehiculoid) === (string) $estado->estadovehiculoid)>
                        {{ $estado->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-12">
        <input type="hidden" name="activo" value="0">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="activoVehiculo" name="activo" value="1"
                   @checked(old('activo', $v?->activo ?? true))>
            <label class="custom-control-label" for="activoVehiculo">Vehículo activo en flota</label>
        </div>
    </div>
</div>
