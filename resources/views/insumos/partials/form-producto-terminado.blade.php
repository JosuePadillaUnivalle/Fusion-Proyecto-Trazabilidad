@php
    $insumo = $insumo ?? null;
    $urlRetorno = $urlRetorno ?? route('insumos.index');
    $unidades = $unidades ?? collect();
@endphp

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h3 class="card-title mb-0">{{ $tituloFormulario ?? 'Editar producto' }}</h3>
    </div>
    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if(($formMethod ?? '') === 'PUT')
            @method('PUT')
        @endif
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-light border small mb-3">
                <i class="fas fa-box mr-1 text-success"></i>
                Producto terminado en <strong>{{ $insumo?->almacen?->nombre ?? 'almacén' }}</strong>.
                El tipo no se modifica desde aquí.
            </div>

            <div class="form-group">
                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" id="nombre" class="form-control" maxlength="100"
                       value="{{ old('nombre', $insumo->nombre ?? '') }}" required>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="stock">Stock <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="stock" id="stock" class="form-control"
                           value="{{ old('stock', $insumo->stock ?? 0) }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="unidadmedidaid">Unidad <span class="text-danger">*</span></label>
                    <select name="unidadmedidaid" id="unidadmedidaid" class="form-control" required>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->unidadmedidaid }}" @selected((int) old('unidadmedidaid', $insumo->unidadmedidaid ?? 0) === (int) $unidad->unidadmedidaid)>
                                {{ $unidad->nombre }}@if($unidad->abreviatura) ({{ $unidad->abreviatura }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group mb-0">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="3" class="form-control" maxlength="500">{{ old('descripcion', $insumo->descripcion ?? '') }}</textarea>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="{{ $urlRetorno }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save mr-1"></i> {{ $botonGuardar ?? 'Guardar' }}
            </button>
        </div>
    </form>
</div>
