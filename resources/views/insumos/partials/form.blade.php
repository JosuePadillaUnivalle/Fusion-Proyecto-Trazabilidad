@php
    use App\Support\InsumoCatalogo;

    $insumo = $insumo ?? null;
    $tipos = $tipos ?? collect();
    $unidadesPorTipo = $unidadesPorTipo ?? [];
    $tipoSlugInicial = $insumo
        ? (InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre) ?? 'material_siembra')
        : (InsumoCatalogo::slugFromNombreTipo($tipos->first()?->nombre) ?? 'material_siembra');
    $umbral = InsumoCatalogo::UMBRAL_ALERTA_STOCK;
@endphp

@push('styles')
<style>
.page-insumo-form .form-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 14px rgba(0,0,0,.08);
}
.page-insumo-form .form-card .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px 12px 0 0 !important;
    padding: 1.1rem 1.25rem;
}
.page-insumo-form .guia-campo {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.6rem 0.85rem;
    margin-bottom: 0.65rem;
    font-size: 0.84rem;
    color: #495057;
}
.page-insumo-form .guia-campo strong { color: #2c5530; }
.page-insumo-form .alerta-stock-info {
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
}
.page-insumo-form .form-control {
    border-radius: 8px;
    border: 2px solid #dee2e6;
    min-height: 44px;
}
.page-insumo-form .form-control:focus {
    border-color: #2c5530;
    box-shadow: 0 0 0 0.15rem rgba(44,85,48,.15);
}
.page-insumo-form .tipo-icon-preview {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #e8f5e9;
    color: #2c5530;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
</style>
@endpush

<div class="modulo-inv page-insumo-form">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card card-modulo-main">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-boxes mr-2"></i>{{ $tituloFormulario ?? ($insumo ? 'Editar insumo' : 'Registrar insumo') }}
                    </h3>
                </div>

                <form action="{{ $formAction }}" method="POST">
                    @csrf
                    @if($formMethod ?? false)
                        @method($formMethod)
                    @endif

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alerta-stock-info mb-4">
                            <i class="fas fa-bell text-warning mr-1"></i>
                            <strong>Alertas automáticas:</strong> el sistema avisará cuando el stock sea
                            <strong>{{ $umbral }} o menos</strong> unidades (según la unidad elegida). No hace falta configurar un mínimo manual.
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-tag text-success mr-1"></i> Nombre del insumo <span class="text-danger">*</span>
                            </label>
                            <div class="guia-campo">
                                Nombre comercial o interno con el que identificarás el producto en inventario y en aplicaciones a lotes.
                            </div>
                            <input type="text" name="nombre" class="form-control" maxlength="100" required
                                value="{{ old('nombre', $insumo->nombre ?? '') }}"
                                placeholder="Ej. Semilla híbrida F1, Urea 46%, Manguera 2&quot;">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold d-flex align-items-center" style="gap: 8px;">
                                        <span class="tipo-icon-preview" id="tipoIconPreview"><i class="fas fa-seedling"></i></span>
                                        Tipo de insumo <span class="text-danger">*</span>
                                    </label>
                                    <div class="guia-campo">
                                        Define qué unidades de medida podrás usar. Los tipos disponibles son:
                                        Material de Siembra, Fertilizantes, Pesticidas y Material de Riego.
                                    </div>
                                    <select name="tipoinsumoid" id="tipoinsumoid" class="form-control" required>
                                        @foreach($tipos as $t)
                                            @php $slug = InsumoCatalogo::slugFromNombreTipo($t->nombre); @endphp
                                            <option value="{{ $t->tipoinsumoid }}"
                                                data-slug="{{ $slug }}"
                                                @selected((int) old('tipoinsumoid', $insumo->tipoinsumoid ?? 0) === (int) $t->tipoinsumoid)>
                                                {{ $t->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-balance-scale text-success mr-1"></i> Unidad de medida <span class="text-danger">*</span>
                                    </label>
                                    <div class="guia-campo" id="guiaUnidad">
                                        @if($tipoSlugInicial === 'pesticidas')
                                            Para pesticidas: peso (kg, g) o volumen (ml, L) según presentación en polvo, granulado, líquido o gas.
                                        @elseif($tipoSlugInicial === 'material_riego')
                                            Mangueras y rollos por <strong>metro</strong>; aspersores, válvulas y equipos por <strong>unidad</strong>.
                                        @else
                                            Elija la unidad con la que contará y descontará stock este insumo.
                                        @endif
                                    </div>
                                    <select name="unidadmedidaid" id="unidadmedidaid" class="form-control" required
                                        data-selected="{{ old('unidadmedidaid', $insumo->unidadmedidaid ?? '') }}">
                                        <option value="">— Seleccione tipo primero —</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-cubes text-success mr-1"></i> Stock actual <span class="text-danger">*</span>
                            </label>
                            <div class="guia-campo">
                                Cantidad disponible hoy en inventario, expresada en la unidad seleccionada arriba.
                                Si queda en {{ $umbral }} o menos, aparecerá como <strong>stock bajo</strong>.
                            </div>
                            <input type="number" step="0.01" name="stock" class="form-control" min="0" required
                                value="{{ old('stock', $insumo->stock ?? '') }}"
                                placeholder="Ej. 120">
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">
                                <i class="fas fa-comment-alt text-muted mr-1"></i> Descripción (opcional)
                            </label>
                            <div class="guia-campo">
                                Notas de uso, lote de compra, concentración, ubicación en bodega u otras referencias para tu equipo.
                            </div>
                            <textarea name="descripcion" class="form-control" rows="3"
                                placeholder="Ej. Aplicar en etapa vegetativa; guardar en lugar seco.">{{ old('descripcion', $insumo->descripcion ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between flex-wrap" style="gap: 8px;">
                        <a href="{{ route('insumos.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> {{ $botonGuardar ?? 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const unidadesPorTipo = @json($unidadesPorTipo);
    const guiasUnidad = {
        material_siembra: 'Semillas y material de siembra: use kg, g, quintales o unidades (bolsas, sobres).',
        fertilizantes: 'Fertilizantes: generalmente en kg, g, quintales o litros según formulación líquida o sólida.',
        pesticidas: 'Pesticidas: peso (kg, g) o volumen (ml, L) para polvo, granulado, líquido o gas.',
        material_riego: 'Material de riego: <strong>metro</strong> para mangueras y rollos; <strong>unidad</strong> para equipos (aspersores, válvulas, etc.).',
    };
    const iconosTipo = {
        material_siembra: 'fa-seedling',
        fertilizantes: 'fa-flask',
        pesticidas: 'fa-bug',
        material_riego: 'fa-tint',
    };

    const selTipo = document.getElementById('tipoinsumoid');
    const selUm = document.getElementById('unidadmedidaid');
    const guia = document.getElementById('guiaUnidad');
    const iconPrev = document.getElementById('tipoIconPreview');

    function slugTipo() {
        const opt = selTipo.options[selTipo.selectedIndex];
        return opt ? (opt.getAttribute('data-slug') || '') : '';
    }

    function pintarUnidades() {
        const slug = slugTipo();
        const lista = unidadesPorTipo[slug] || [];
        const prev = selUm.getAttribute('data-selected') || selUm.value;

        selUm.innerHTML = '';
        if (!lista.length) {
            selUm.innerHTML = '<option value="">Sin unidades para este tipo</option>';
            return;
        }
        lista.forEach(function (u) {
            const o = document.createElement('option');
            o.value = u.id;
            o.textContent = u.nombre + (u.abreviatura ? ' (' + u.abreviatura + ')' : '');
            if (String(u.id) === String(prev)) {
                o.selected = true;
            }
            selUm.appendChild(o);
        });
        if (!selUm.value && lista.length) {
            selUm.selectedIndex = 0;
        }
        selUm.removeAttribute('data-selected');
    }

    function actualizarGuia() {
        const slug = slugTipo();
        if (guia) {
            guia.innerHTML = guiasUnidad[slug] || 'Elija la unidad con la que contará stock este insumo.';
        }
        if (iconPrev) {
            const ic = iconosTipo[slug] || 'fa-box';
            iconPrev.innerHTML = '<i class="fas ' + ic + '"></i>';
        }
        pintarUnidades();
    }

    selTipo.addEventListener('change', actualizarGuia);
    actualizarGuia();
})();
</script>
@endpush
