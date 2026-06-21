@php
    $rutaPrefijo = $rutaPrefijo ?? 'almacen-mayorista';
    $tituloModulo = $tituloModulo ?? 'Almacén mayorista';
    $unidad = $producto->unidadMedida?->abreviatura ?? $producto->unidadMedida?->nombre ?? 'u.';
    $stockMinimo = old('stockminimo', $producto->stockminimo ?? \App\Support\InsumoCatalogo::UMBRAL_ALERTA_STOCK);
@endphp

@extends('layouts.app')

@section('title', 'Editar '.$producto->nombre.' | Inventario')
@section('page_title', 'Editar producto del almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route($rutaPrefijo.'.index') }}">Almacenes</a></li>
    <li class="breadcrumb-item"><a href="{{ route($rutaPrefijo.'.show', $almacen) }}">{{ $almacen->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar producto</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-inv-edit .edit-shell {
    border: none;
    border-radius: 14px;
    box-shadow: 0 4px 22px rgba(44, 85, 48, .1);
    overflow: hidden;
}
.page-inv-edit .edit-head {
    background: linear-gradient(135deg, #1e4620, #2c5530 60%, #4a7c59);
    color: #fff;
    padding: 1.25rem 1.5rem;
}
.page-inv-edit .preview-panel {
    background: linear-gradient(180deg, #f8faf8, #eef4ee);
    border-right: 1px solid #e2ebe3;
    padding: 1.5rem;
}
.page-inv-edit .preview-img-wrap {
    background: #fff;
    border: 1px solid #e2ebe3;
    border-radius: 12px;
    padding: .75rem;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}
.page-inv-edit .preview-img {
    max-width: 100%;
    max-height: 180px;
    object-fit: contain;
    border-radius: 8px;
}
.page-inv-edit .preview-stock {
    background: #fff;
    border: 1px solid #dce9de;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}
.page-inv-edit .preview-stock .valor {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e4620;
}
.page-inv-edit .form-panel { padding: 1.5rem 1.75rem; }
.page-inv-edit .field-hint {
    font-size: .8rem;
    color: #6c757d;
    margin-top: .25rem;
}
.page-inv-edit .stock-minimo-box {
    background: #f0f7f1;
    border: 1px solid #dce9de;
    border-radius: 10px;
    padding: .85rem 1rem;
}
.page-inv-edit .form-control:focus {
    border-color: #4a7c59;
    box-shadow: 0 0 0 .15rem rgba(74, 124, 89, .2);
}
.page-inv-edit .img-picker {
    margin-top: .75rem;
    text-align: center;
}
.page-inv-edit .img-picker label.btn {
    font-size: .85rem;
}
.page-inv-edit .img-picker-name {
    display: block;
    font-size: .75rem;
    color: #6c757d;
    margin-top: .35rem;
}
</style>
@endpush

@section('content')
<div class="page-inv-edit">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card edit-shell">
                <div class="edit-head d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1"><i class="fas fa-edit mr-2"></i>Editar producto</h4>
                        <small class="opacity-90">
                            Inventario de <strong>{{ $almacen->nombre }}</strong> — {{ $tituloModulo }}
                        </small>
                    </div>
                    <a href="{{ route($rutaPrefijo.'.show', $almacen) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>

                <form action="{{ route($rutaPrefijo.'.inventario.update', [$almacen, $producto]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row no-gutters">
                        <div class="col-lg-4 preview-panel">
                            <p class="text-muted small text-uppercase mb-2" style="letter-spacing:.05em;">Vista previa</p>
                            <div class="preview-img-wrap">
                                <img src="{{ $producto->imagenSrc(360) }}"
                                     alt="{{ $producto->nombre }}"
                                     class="preview-img"
                                     id="previewImagen"
                                     loading="lazy">
                            </div>
                            <div class="img-picker">
                                <input type="file" name="imagen" id="imagen" class="d-none"
                                       accept="image/jpeg,image/png,image/webp,image/gif">
                                <label for="imagen" class="btn btn-outline-secondary btn-sm mb-0">
                                    <i class="fas fa-camera mr-1"></i> Cambiar imagen
                                </label>
                                <span class="img-picker-name" id="imagenNombre">JPG, PNG o WebP · máx. 4 MB</span>
                                @if(\App\Support\InsumoImagenCatalogo::esImagenPersonalizada($producto->imagenurl))
                                <div class="custom-control custom-checkbox mt-2 text-left">
                                    <input type="checkbox" class="custom-control-input" name="quitar_imagen"
                                           id="quitar_imagen" value="1">
                                    <label class="custom-control-label small" for="quitar_imagen">
                                        Quitar imagen personalizada
                                    </label>
                                </div>
                                @endif
                            </div>
                            <div class="preview-stock">
                                <div class="text-muted small mb-1">Stock en depósito</div>
                                <div class="valor">
                                    <span id="previewStock">{{ number_format($producto->stock, 2) }}</span>
                                    <span class="text-muted" style="font-size:1rem;">{{ $unidad }}</span>
                                </div>
                                <div class="text-muted small mt-1" id="previewNombre">{{ $producto->nombre }}</div>
                            </div>
                        </div>

                        <div class="col-lg-8 form-panel">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="nombre">Nombre del producto <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control form-control-lg"
                                       maxlength="100" required
                                       value="{{ old('nombre', $producto->nombre) }}">
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="stock">Cantidad disponible <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="stock" id="stock"
                                           class="form-control" required
                                           value="{{ old('stock', $producto->stock) }}">
                                    <div class="field-hint">Unidades actuales en este almacén.</div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="unidadmedidaid">Unidad <span class="text-danger">*</span></label>
                                    <select name="unidadmedidaid" id="unidadmedidaid" class="form-control" required>
                                        @foreach($unidades as $u)
                                            <option value="{{ $u->unidadmedidaid }}" @selected((int) old('unidadmedidaid', $producto->unidadmedidaid) === (int) $u->unidadmedidaid)>
                                                {{ $u->nombre }}@if($u->abreviatura) ({{ $u->abreviatura }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="stockminimo">Stock mínimo</label>
                                    <input type="number" step="0.01" min="0" name="stockminimo" id="stockminimo"
                                           class="form-control"
                                           value="{{ $stockMinimo }}">
                                    <div class="field-hint">Aviso cuando baje de este valor.</div>
                                </div>
                            </div>

                            <div class="stock-minimo-box mb-3 small">
                                <i class="fas fa-info-circle text-success mr-1"></i>
                                El <strong>stock mínimo</strong> define cuándo verás la alerta de reposición en el detalle del almacén.
                                No bloquea pedidos; solo ayuda a vigilar el inventario.
                            </div>

                            <div class="form-group mb-4">
                                <label for="descripcion">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="3" class="form-control"
                                          maxlength="500" placeholder="Notas sobre el producto en este almacén…">{{ old('descripcion', $producto->descripcion) }}</textarea>
                            </div>

                            <div class="d-flex justify-content-between flex-wrap">
                                <a href="{{ route($rutaPrefijo.'.show', $almacen) }}" class="btn btn-secondary mb-2">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver al almacén
                                </a>
                                <button type="submit" class="btn btn-success mb-2 px-4">
                                    <i class="fas fa-save mr-1"></i> Guardar cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var nombre = document.getElementById('nombre');
    var stock = document.getElementById('stock');
    var previewNombre = document.getElementById('previewNombre');
    var previewStock = document.getElementById('previewStock');
    var inputImagen = document.getElementById('imagen');
    var previewImagen = document.getElementById('previewImagen');
    var imagenNombre = document.getElementById('imagenNombre');

    if (inputImagen && previewImagen) {
        inputImagen.addEventListener('change', function () {
            var file = inputImagen.files && inputImagen.files[0];
            if (imagenNombre) {
                imagenNombre.textContent = file ? file.name : 'JPG, PNG o WebP · máx. 4 MB';
            }
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                previewImagen.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    if (nombre && previewNombre) {
        nombre.addEventListener('input', function () {
            previewNombre.textContent = this.value || '{{ $producto->nombre }}';
        });
    }
    if (stock && previewStock) {
        stock.addEventListener('input', function () {
            var v = parseFloat(this.value);
            previewStock.textContent = isNaN(v) ? '0.00' : v.toFixed(2);
        });
    }
})();
</script>
@endpush
