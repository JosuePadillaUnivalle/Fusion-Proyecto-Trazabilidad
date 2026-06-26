@extends('layouts.app')

@section('title', 'Procesamiento de Lote | AgroFusion')
@section('page_title', 'Procesamiento de Lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Procesamiento de Lote</li>
@endsection

@push('styles')
<style>
.pl-stats .pl-stat {
    border-radius: 14px; color: #fff; padding: 18px 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
}
.pl-stats .pl-stat h3 { font-size: 1.75rem; font-weight: 800; margin: 0 0 2px; }
.pl-stats .pl-stat p { margin: 0; font-size: .78rem; opacity: .92; text-transform: uppercase; letter-spacing: .04em; }
.pl-stats .bg-total { background: linear-gradient(135deg, #0e7490, #06b6d4); }
.pl-stats .bg-pend { background: linear-gradient(135deg, #b45309, #f59e0b); }
.pl-stats .bg-proc { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
.pl-stats .bg-done { background: linear-gradient(135deg, #065f46, #10b981); }

#modalNuevoLote .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,.18); }
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-content > form {
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 3.5rem);
    min-height: 0;
    overflow: hidden;
}
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    flex: 1 1 auto;
    min-height: 0;
    -webkit-overflow-scrolling: touch;
}
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-header,
#modalNuevoLote .modal-dialog.modal-dialog-scrollable .modal-footer {
    flex-shrink: 0;
}
#modalNuevoLote .modal-header-lote {
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #4a7c59 100%);
    color: #fff; border: 0; padding: 1.25rem 1.5rem;
}
#modalNuevoLote .modal-header-lote .close { color: #fff; opacity: .85; text-shadow: none; }
#modalNuevoLote .modal-body { padding: 1.25rem 1.5rem 1rem; background: #f8faf8; }
#modalNuevoLote .lote-section {
    background: #fff; border: 1px solid #e2ebe3; border-radius: 12px;
    padding: 1rem 1.15rem; margin-bottom: 1rem;
}
#modalNuevoLote .lote-section-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #2c5530; margin-bottom: .75rem;
}
#modalNuevoLote .lote-section-title i { opacity: .75; }
#modalNuevoLote .picker-field {
    display: flex; align-items: stretch; gap: 0;
    border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #fff;
}
#modalNuevoLote .picker-field:focus-within { border-color: #2c5530; box-shadow: 0 0 0 .15rem rgba(44,85,48,.12); }
#modalNuevoLote .picker-field .picker-display {
    flex: 1; border: 0; background: transparent; padding: .55rem .85rem;
    font-size: .95rem; min-height: 44px;
}
#modalNuevoLote .picker-field .picker-display.text-muted { color: #9ca3af !important; }
#modalNuevoLote .picker-actions { display: flex; border-left: 1px solid #e5e7eb; }
#modalNuevoLote .picker-actions .btn { border-radius: 0; border: 0; padding: 0 1rem; font-weight: 600; }
#modalNuevoLote .tabla-materias { border-radius: 10px; overflow: hidden; border: 1px solid #e2ebe3; }
#modalNuevoLote .tabla-materias thead th { background: #f0f7f1; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; border: 0; }
#modalNuevoLote .btn-agregar-mp {
    border: 2px dashed #2c5530; color: #2c5530; background: #f0fdf4;
    border-radius: 10px; font-weight: 600; width: 100%; padding: .65rem;
}
#modalNuevoLote .btn-agregar-mp:hover { background: #dcfce7; }
#modalNuevoLote .lote-controles-empaque.is-bloqueado { opacity: .55; pointer-events: none; user-select: none; }
#modalNuevoLote .modal-footer { background: #fff; border-top: 1px solid #e5e7eb; padding: 1rem 1.5rem; }
#modalNuevoLote #productoLote::-webkit-calendar-picker-indicator,
#modalNuevoLote #productoLote::-webkit-list-button { display: none !important; }
.pl-actions .btn { padding: .25rem .5rem; margin: 0 .15rem; }
.pl-actions .btn-danger { background: #dc3545; border-color: #dc3545; color: #fff; }
.pl-actions .btn-danger:hover { background: #c82333; color: #fff; }
</style>
@endpush

@section('content')
<div class="row mb-4 pl-stats">
    @foreach([
        ['total', 'Total lotes', 'bg-total'],
        ['pendientes', 'Pendientes', 'bg-pend'],
        ['en_proceso', 'En proceso', 'bg-proc'],
        ['completados', 'Completados', 'bg-done'],
    ] as [$key, $label, $bg])
    <div class="col-6 col-md-3 mb-2">
        <div class="pl-stat {{ $bg }}">
            <h3>{{ $stats[$key] ?? 0 }}</h3>
            <p>{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('procesamiento.index') }}" class="form-row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Buscar</label>
                <input type="search" name="q" class="form-control form-control-sm" value="{{ $busqueda }}" placeholder="Código, nombre, pedido…">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Producto</label>
                <input type="search" name="producto" class="form-control form-control-sm" value="{{ $productoFiltro }}" placeholder="Ej. Tomate, Ensalada…">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Estado</label>
                <select name="estado" class="custom-select custom-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" @selected($estadoFiltro === 'pendiente')>Pendiente</option>
                    <option value="en_proceso" @selected($estadoFiltro === 'en_proceso')>En proceso</option>
                    <option value="completado" @selected($estadoFiltro === 'completado')>Completado</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ $desde }}">
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $hasta }}">
            </div>
            <div class="col-md-1 mb-2 mb-md-0">
                <button type="submit" class="btn btn-success btn-sm btn-block"><i class="fas fa-filter"></i></button>
            </div>
        </form>
        @if($productoFiltro || $estadoFiltro || $busqueda || $desde || $hasta)
            <p class="small text-muted mb-0 mt-2">
                Filtros activos.
                <a href="{{ route('procesamiento.index') }}">Limpiar</a>
            </p>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white py-3" style="border-radius:14px 14px 0 0;">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-2 mb-md-0 font-weight-bold"><i class="fas fa-industry text-success mr-2"></i>Lotes de producción</h3>
            <div>
                @can('lote_produccion.create')
                <button type="button" class="btn btn-success btn-sm px-3" data-toggle="modal" data-target="#modalNuevoLote">
                    <i class="fas fa-plus mr-1"></i>Nuevo lote
                </button>
                @endcan
            </div>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Código</th>
                    <th>Producto / Lote</th>
                    <th>Estado</th>
                    <th>Pedido</th>
                    <th>Cant. objetivo</th>
                    <th>Materias usadas</th>
                    <th>Fecha</th>
                    <th class="text-center" style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lotes as $lote)
                    <tr>
                        <td><a href="{{ route('procesamiento.show', $lote) }}"><code class="text-success">{{ $lote->codigo_lote }}</code></a></td>
                        <td class="font-weight-bold">
                            <a href="{{ route('procesamiento.show', $lote) }}" class="text-dark">{{ $lote->nombre }}</a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $lote->estado_operativo === 'completado' ? 'success' : ($lote->estado_operativo === 'en_proceso' ? 'primary' : 'secondary') }}">{{ $lote->fase_label }}</span>
                        </td>
                        <td>{{ $lote->pedido?->numero_solicitud ?? '—' }}</td>
                        <td>
                            @php $objetivoLabel = \App\Support\ProductoPlantaCatalogo::etiquetaCantidadObjetivo($lote); @endphp
                            @if($objetivoLabel)
                                {{ $objetivoLabel }}
                            @else — @endif
                        </td>
                        <td>
                            @foreach($lote->materiasPrimas as $mp)
                                <span class="badge badge-light border mr-1">{{ $mp->insumo?->nombre ?? 'MP' }}: {{ number_format((float) $mp->cantidad_usada, 2) }}</span>
                            @endforeach
                        </td>
                        <td class="text-muted">{{ optional($lote->fecha_creacion)->format('d/m/Y') }}</td>
                        <td class="text-center pl-actions text-nowrap">
                            <a href="{{ route('procesamiento.show', $lote) }}" class="btn btn-sm btn-outline-info" title="Ver fases"><i class="fas fa-eye"></i></a>
                            @can('lote_produccion.create')
                                <a href="{{ route('procesamiento.edit', $lote) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                @if($lote->estado_operativo === 'pendiente')
                                <form action="{{ route('procesamiento.destroy', $lote) }}" method="POST" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                            data-confirm-modal
                                            data-confirm-title="Eliminar lote"
                                            data-confirm-message="¿Eliminar el lote «{{ $lote->nombre }}»? Se revertirá el stock de materias primas.">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>No hay lotes. Cree uno con «Nuevo lote».</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lotes->hasPages())<div class="card-footer bg-white">{{ $lotes->links() }}</div>@endif
</div>

@can('lote_produccion.create')
@php
    $unidadKgId = $unidadesMedida->first(fn ($um) => str_contains(strtolower($um->abreviatura ?? $um->nombre ?? ''), 'kg'))?->unidadmedidaid;
@endphp
<div class="modal fade" id="modalNuevoLote" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('procesamiento.store') }}" id="formNuevoLote">
                @csrf
                <div class="modal-header modal-header-lote">
                    <div>
                        <h5 class="modal-title mb-1 font-weight-bold"><i class="fas fa-flask mr-2"></i>Nuevo lote de producción</h5>
                        <p class="mb-0 small opacity-90">Industrialización de materia prima desde almacén de planta</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="lote-section">
                        <div class="lote-section-title"><i class="fas fa-tag mr-1"></i> Producto a procesar</div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Producto <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="producto"
                                   id="productoLote"
                                   class="form-control"
                                   list="productosLoteList"
                                   value="{{ old('producto') }}"
                                   required
                                   maxlength="100"
                                   placeholder="Ej. Papas Fritas"
                                   autocomplete="off">
                            <datalist id="productosLoteList">
                                @foreach($productosLote as $prod)
                                    <option value="{{ $prod }}"></option>
                                @endforeach
                            </datalist>
                            <small class="text-muted">Elegí uno existente o escribí un producto nuevo.</small>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold"><i class="fas fa-project-diagram mr-1 text-success"></i> Proceso de transformación <span class="text-muted font-weight-normal">(opcional)</span></label>
                            <div class="picker-field">
                                <input type="text"
                                       id="plantilla_display"
                                       class="picker-display {{ $plantillaLabel ? '' : 'text-muted' }}"
                                       readonly
                                       placeholder="Sin proceso asignado"
                                       value="{{ $plantillaLabel ?? '' }}">
                                <input type="hidden" name="plantillatransformacionid" id="plantillatransformacionid" value="{{ old('plantillatransformacionid') }}">
                                <div class="picker-actions">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarPlantilla"><i class="fas fa-search mr-1"></i>Buscar</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarPlantilla" title="Quitar"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">Abre un buscador para filtrar, ver etapas y elegir el proceso manualmente.</small>
                        </div>
                        @include('procesamiento.partials.parametros-lote-plantilla')
                        <div class="alert alert-light border mb-0 py-2 px-3 small">
                            <i class="fas fa-magic text-success mr-1"></i>
                            Se creará como: <strong id="nombreLotePreview" class="text-success">—</strong>
                        </div>
                    </div>

                    <div class="lote-section mb-0">
                        <div class="lote-section-title"><i class="fas fa-boxes mr-1"></i> Materia prima <span class="text-danger">*</span></div>
                        <p class="small text-muted mb-2">Paso 1: elija el insumo del almacén de planta. El empaquetado y el cálculo se habilitan después, según el stock de ese insumo.</p>
                        <div id="alertaStockMateriaPrima" class="alert alert-danger small py-2 px-3 mb-2 d-none" role="alert">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <span id="alertaStockMateriaPrimaTexto"></span>
                        </div>
                        <div class="table-responsive tabla-materias mb-2">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Insumo</th><th style="width:130px">Cantidad</th><th style="width:44px"></th></tr></thead>
                                <tbody id="tbodyMaterias">
                                    <tr id="filaMateriasVacia"><td colspan="3" class="text-center text-muted py-3 small">Sin materias. Use el botón de abajo para buscar insumos del almacén de planta.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-agregar-mp" id="btnBuscarInsumo">
                            <i class="fas fa-plus-circle mr-1"></i> Agregar materia prima
                        </button>
                    </div>

                    <div class="lote-section" id="seccionEmpaquetadoPlanificado">
                        <div class="lote-section-title"><i class="fas fa-box-open mr-1"></i> Empaquetado planificado <span class="text-danger">*</span></div>
                        <div id="avisoEmpaqueBloqueado" class="alert alert-warning small py-2 px-3 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Primero agregue la materia prima. El empaquetado y el estimado se calculan según el insumo elegido.
                        </div>
                        <div id="loteControlesEmpaque" class="lote-controles-empaque is-bloqueado">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Presentación comercial</label>
                            <select name="empaque_catalogo_slug" id="empaqueCatalogoSlug" class="form-control form-control-sm" disabled>
                                <option value="">Seleccione empaque…</option>
                                @foreach($empaquesPlanta ?? [] as $emp)
                                    <option value="{{ $emp['slug'] }}"
                                            data-peso="{{ $emp['peso_neto_kg'] ?? '' }}"
                                            data-etiqueta="{{ $emp['etiqueta_unidad'] ?? 'unidades' }}"
                                            @selected(old('empaque_catalogo_slug') === $emp['slug'])>
                                        {{ $emp['nombre'] }}@if(!empty($emp['peso_etiqueta'])) ({{ $emp['peso_etiqueta'] }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="bloqueEmpaquePersonalizado" class="border rounded p-2 mb-2 bg-light {{ old('empaque_catalogo_slug') === 'personalizado' ? '' : 'd-none' }}">
                            <div class="form-row">
                                <div class="col-md-6 form-group mb-md-0">
                                    <label class="small font-weight-bold">Nombre</label>
                                    <input type="text" name="empaque_nombre_personalizado" id="empaqueNombrePersonalizado" class="form-control form-control-sm" maxlength="120" value="{{ old('empaque_nombre_personalizado') }}" placeholder="Ej. Bidón 20 kg">
                                </div>
                                <div class="col-md-3 form-group mb-md-0">
                                    <label class="small font-weight-bold">Peso neto (kg)</label>
                                    <input type="number" name="empaque_peso_neto_kg" id="empaquePesoNetoKg" class="form-control form-control-sm" step="0.001" min="0.001" value="{{ old('empaque_peso_neto_kg') }}" placeholder="5">
                                </div>
                                <div class="col-md-3 form-group mb-0">
                                    <label class="small font-weight-bold">Tipo envase</label>
                                    <select name="empaque_tipo_envase" id="empaqueTipoEnvase" class="form-control form-control-sm">
                                        @foreach(['bolsa' => 'Bolsa', 'lata' => 'Lata', 'frasco' => 'Frasco', 'bidon' => 'Bidón', 'caja' => 'Caja'] as $val => $lbl)
                                            <option value="{{ $val }}" @selected(old('empaque_tipo_envase', 'bolsa') === $val)>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="modo_planificacion" id="modoPlanificacion" value="{{ old('modo_planificacion', 'empaques') }}">
                        <div class="btn-group btn-group-toggle btn-group-sm d-flex mb-2" role="group" id="toggleModoPlanificacion">
                            <button type="button" class="btn btn-outline-success flex-fill active" data-modo="empaques"><i class="fas fa-cubes mr-1"></i>Por empaques</button>
                            <button type="button" class="btn btn-outline-success flex-fill" data-modo="materia_prima"><i class="fas fa-weight mr-1"></i>Por materia prima (kg)</button>
                        </div>
                        <div id="bloqueModoEmpaques">
                            <label class="small font-weight-bold">Cantidad de empaques objetivo</label>
                            <input type="number" name="cantidad_empaques_objetivo" id="cantidadEmpaquesObjetivo" class="form-control form-control-sm" step="1" min="1" value="{{ old('cantidad_empaques_objetivo') }}" placeholder="Ej. 100">
                        </div>
                        <div id="bloqueModoMateriaPrima" class="d-none">
                            <label class="small font-weight-bold">Materia prima disponible (kg)</label>
                            <input type="number" name="cantidad_objetivo" id="cantidadObjetivoLote" class="form-control form-control-sm" step="0.01" min="0.01" value="{{ old('cantidad_objetivo') }}" placeholder="Ej. 50" disabled>
                        </div>
                        <input type="hidden" name="unidadmedidaid" id="unidadObjetivoLoteKg" value="{{ $unidadKgId }}">
                        <div id="planificacionEmpaqueBox" class="alert alert-light border small py-2 px-3 mt-2 mb-0 d-none">
                            <i class="fas fa-calculator text-success mr-1"></i>
                            <span id="planificacionEmpaqueTexto"></span>
                        </div>
                        <small class="text-muted d-block mt-1">Rendimiento estándar {{ \App\Support\EmpaquePlantaCatalogo::rendimientoPorcentaje() }}&nbsp;%. Al almacenar se calcula la producción real según la materia prima consumida.</small>
                        </div>
                    </div>

                    <div class="lote-section mt-3 mb-0">
                        <label class="small font-weight-bold text-muted">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" maxlength="500" placeholder="Opcional…">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4" id="btnCrearLote"><i class="fas fa-check mr-1"></i>Crear lote</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.selector-plantilla-transformacion-modal')
@endcan

@include('partials.modal-confirmar-accion')
@endsection

@can('lote_produccion.create')
@push('styles')
<style>
#modalSelectorCatalogo .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
#modalSelectorCatalogo .modal-header { background: linear-gradient(135deg, #1e4620, #4a7c59); }
#modalSelectorCatalogo .selector-catalogo-row { cursor: pointer; }
#modalSelectorCatalogo .selector-catalogo-row:hover { background: #eef7ef; }
.recomendacion-mp-pure {
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 1px solid #a7d9b4;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    font-size: 0.84rem;
    color: #1e4620;
}
.recomendacion-mp-pure .recomendacion-mp-texto strong { color: #2c5530; }
.mp-rec-hint { font-size: 0.75rem; }
#modalSelectorPlantilla .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
.selector-plantilla-lista { max-height: 420px; overflow-y: auto; background: #fafcfb; }
.selector-plantilla-card {
    display: block; width: 100%; text-align: left; border: 0; border-bottom: 1px solid #e8efe9;
    background: #fff; padding: .75rem .9rem; transition: background .15s;
}
.selector-plantilla-card:hover, .selector-plantilla-card.active { background: #eef7ef; }
.selector-plantilla-card:last-child { border-bottom: 0; }
.selector-plantilla-detalle { min-height: 420px; background: #fff; }
.selector-plantilla-pasos { max-height: 280px; overflow-y: auto; }
.selector-plantilla-paso {
    display: flex; align-items: flex-start; gap: .65rem;
    padding: .55rem 0; border-bottom: 1px dashed #e5ebe6;
}
.selector-plantilla-paso:last-child { border-bottom: 0; }
.selector-plantilla-paso-num {
    flex-shrink: 0; width: 1.65rem; height: 1.65rem; border-radius: 50%;
    background: #2c5530; color: #fff; font-size: .72rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
}
.selector-plantilla-paso--cierre .selector-plantilla-paso-num { background: #17a2b8; }
#modalSelectorCatalogo.sel-theme-materia-prima .sel-modal-header {
    background: linear-gradient(135deg, #14532d, #2c5530);
}
#modalSelectorCatalogo.sel-theme-materia-prima .sel-modal-search-panel {
    background: linear-gradient(180deg, #f0fdf4, #fff);
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: .85rem;
}
#modalSelectorCatalogo.sel-theme-materia-prima .sel-modal-table-wrap { border-color: #86efac; }
#modalSelectorCatalogo.sel-theme-materia-prima .sel-modal-table thead th {
    background: #ecfdf5;
    color: #166534;
    border-bottom: 2px solid #86efac;
}
#modalSelectorCatalogo.sel-theme-materia-prima .selector-catalogo-row:hover { background: #dcfce7; }
#modalSelectorCatalogo.sel-theme-materia-prima .sel-col-nombre .sel-row-icon {
    background: #dcfce7;
    color: #15803d;
}
</style>
@endpush

@push('scripts')
@php
    $empaqueCfgJs = [
        'rendimiento' => \App\Support\EmpaquePlantaCatalogo::RENDIMIENTO_TRANSFORMACION,
        'rendimientoPct' => \App\Support\EmpaquePlantaCatalogo::rendimientoPorcentaje(),
        'modoEmpaques' => \App\Support\EmpaquePlantaCatalogo::MODO_EMPAQUES,
        'modoMateriaPrima' => \App\Support\EmpaquePlantaCatalogo::MODO_MATERIA_PRIMA,
        'slugPersonalizado' => \App\Support\EmpaquePlantaCatalogo::SLUG_PERSONALIZADO,
    ];
    $urlParametrosPlantillaJs = url('/plantillas-transformacion/__ID__/parametros');
@endphp
<script src="{{ asset('js/selector-catalogo.js') }}"></script>
<script src="{{ asset('js/selector-plantilla-transformacion.js') }}"></script>
<script>
(function() {
    const EMPAQUE_CFG = @json($empaqueCfgJs);
    const UNIDAD_KG_ID = @json($unidadKgId);
    const materias = [];
    const tbody = document.getElementById('tbodyMaterias');
    const filaVacia = document.getElementById('filaMateriasVacia');
    const btnBuscarInsumo = document.getElementById('btnBuscarInsumo');
    const btnCrearLote = document.getElementById('btnCrearLote');
    const alertaStock = document.getElementById('alertaStockMateriaPrima');
    const alertaStockTexto = document.getElementById('alertaStockMateriaPrimaTexto');
    const plantillaDisplay = document.getElementById('plantilla_display');
    const plantillaInput = document.getElementById('plantillatransformacionid');
    const productoInput = document.getElementById('productoLote');
    const nombrePreview = document.getElementById('nombreLotePreview');
    const urlSiguienteNombre = @json(route('procesamiento.siguiente-nombre'));
    let previewTimer = null;

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }

    function formatoNumero(n) {
        return Number(n).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatoEntero(n) {
        return Math.round(Number(n)).toLocaleString('es-BO', { maximumFractionDigits: 0 });
    }

    function formatoKg(n) {
        return Number(n).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esUnidadKg(unidad) {
        const u = String(unidad || '').toLowerCase();
        return u === 'kg' || u.includes('kilogram');
    }

    function pesoNetoEmpaqueSeleccionado() {
        const sel = document.getElementById('empaqueCatalogoSlug');
        const slug = sel?.value || '';
        if (!slug) return 0;
        if (slug === EMPAQUE_CFG.slugPersonalizado) {
            return parseFloat(document.getElementById('empaquePesoNetoKg')?.value) || 0;
        }
        const opt = sel.options[sel.selectedIndex];
        return parseFloat(opt?.dataset?.peso || 0);
    }

    function etiquetaUnidadEmpaque() {
        const sel = document.getElementById('empaqueCatalogoSlug');
        const opt = sel?.options[sel.selectedIndex];
        return opt?.dataset?.etiqueta || 'unidades';
    }

    function modoPlanificacionActual() {
        return document.getElementById('modoPlanificacion')?.value || EMPAQUE_CFG.modoEmpaques;
    }

    function tieneMateriaPrima() {
        return materias.length > 0;
    }

    function limpiarEmpaquePlanificado() {
        const sel = document.getElementById('empaqueCatalogoSlug');
        if (sel) sel.value = '';
        const emp = document.getElementById('cantidadEmpaquesObjetivo');
        if (emp) emp.value = '';
        const mp = document.getElementById('cantidadObjetivoLote');
        if (mp) mp.value = '';
        document.getElementById('planificacionEmpaqueBox')?.classList.add('d-none');
        toggleEmpaquePersonalizado();
    }

    function actualizarEstadoSeccionEmpaque() {
        const habilitado = tieneMateriaPrima();
        const controles = document.getElementById('loteControlesEmpaque');
        const aviso = document.getElementById('avisoEmpaqueBloqueado');
        const selEmpaque = document.getElementById('empaqueCatalogoSlug');
        const inputEmp = document.getElementById('cantidadEmpaquesObjetivo');
        const inputMp = document.getElementById('cantidadObjetivoLote');
        const toggles = document.querySelectorAll('#toggleModoPlanificacion [data-modo]');

        aviso?.classList.toggle('d-none', habilitado);
        controles?.classList.toggle('is-bloqueado', !habilitado);

        if (selEmpaque) {
            selEmpaque.disabled = !habilitado;
            selEmpaque.required = habilitado;
        }
        toggles.forEach(btn => { btn.disabled = !habilitado; });

        const esEmpaques = modoPlanificacionActual() === EMPAQUE_CFG.modoEmpaques;
        if (inputEmp) {
            inputEmp.disabled = !habilitado || !esEmpaques;
            inputEmp.required = habilitado && esEmpaques;
        }
        if (inputMp) {
            inputMp.disabled = !habilitado || esEmpaques;
            inputMp.required = habilitado && !esEmpaques;
        }

        ['empaqueNombrePersonalizado', 'empaquePesoNetoKg', 'empaqueTipoEnvase'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.disabled = !habilitado;
        });

        if (!habilitado) {
            document.getElementById('planificacionEmpaqueBox')?.classList.add('d-none');
        } else {
            actualizarPlanificacionEmpaque();
        }
    }

    function calcularPlanificacionLocal() {
        if (!tieneMateriaPrima()) return null;

        const peso = pesoNetoEmpaqueSeleccionado();
        const slug = document.getElementById('empaqueCatalogoSlug')?.value || '';
        if (!slug || peso <= 0) return null;

        if (modoPlanificacionActual() === EMPAQUE_CFG.modoEmpaques) {
            const und = parseFloat(document.getElementById('cantidadEmpaquesObjetivo')?.value) || 0;
            if (und <= 0) return null;
            const salidaKg = Math.round(und * peso * 10000) / 10000;
            const entradaKg = Math.round((salidaKg / EMPAQUE_CFG.rendimiento) * 100) / 100;
            return { unidades: und, salida_kg: salidaKg, entrada_kg: entradaKg, etiqueta_unidad: etiquetaUnidadEmpaque() };
        }

        const entradaKg = parseFloat(document.getElementById('cantidadObjetivoLote')?.value) || 0;
        if (entradaKg <= 0) return null;
        const salidaKg = Math.round(entradaKg * EMPAQUE_CFG.rendimiento * 10000) / 10000;
        const und = Math.floor(salidaKg / peso);
        return { unidades: und, salida_kg: salidaKg, entrada_kg: entradaKg, etiqueta_unidad: etiquetaUnidadEmpaque() };
    }

    function aplicarModoPlanificacion(modo) {
        const inputModo = document.getElementById('modoPlanificacion');
        const bloqueEmp = document.getElementById('bloqueModoEmpaques');
        const bloqueMp = document.getElementById('bloqueModoMateriaPrima');
        const inputEmp = document.getElementById('cantidadEmpaquesObjetivo');
        const inputMp = document.getElementById('cantidadObjetivoLote');
        if (inputModo) inputModo.value = modo;

        document.querySelectorAll('#toggleModoPlanificacion [data-modo]').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.modo === modo);
        });

        const esEmpaques = modo === EMPAQUE_CFG.modoEmpaques;
        const habilitado = tieneMateriaPrima();
        bloqueEmp?.classList.toggle('d-none', !esEmpaques);
        bloqueMp?.classList.toggle('d-none', esEmpaques);
        if (inputEmp) {
            inputEmp.disabled = !habilitado || !esEmpaques;
            inputEmp.required = habilitado && esEmpaques;
        }
        if (inputMp) {
            inputMp.disabled = !habilitado || esEmpaques;
            inputMp.required = habilitado && !esEmpaques;
        }
        actualizarPlanificacionEmpaque();
    }

    function actualizarRecomendacionMp() {
        autoRellenarMateriaPrima();
    }

    function toggleEmpaquePersonalizado() {
        const slug = document.getElementById('empaqueCatalogoSlug')?.value || '';
        const bloque = document.getElementById('bloqueEmpaquePersonalizado');
        const habilitado = tieneMateriaPrima();
        bloque?.classList.toggle('d-none', slug !== EMPAQUE_CFG.slugPersonalizado || !habilitado);
        if (habilitado) {
            actualizarPlanificacionEmpaque();
        }
    }

    function cantidadKgMateriaSeleccionada() {
        const inp = tbody?.querySelector('.mp-cantidad-input');
        return inp ? (parseFloat(inp.value) || 0) : 0;
    }

    function kgRequeridosMateriaPrima() {
        const calc = calcularPlanificacionLocal();
        const manual = cantidadKgMateriaSeleccionada();
        if (calc && calc.entrada_kg > 0) {
            return calc.entrada_kg;
        }
        return manual;
    }

    function validarStockMateriaPrima() {
        if (!materias.length) {
            alertaStock?.classList.add('d-none');
            btnCrearLote?.setAttribute('disabled', 'disabled');
            btnBuscarInsumo?.classList.remove('d-none');
            return false;
        }

        btnBuscarInsumo?.classList.add('d-none');

        const m = materias[0];
        const usoKg = Math.max(kgRequeridosMateriaPrima(), cantidadKgMateriaSeleccionada());

        if (esUnidadKg(m.unidad) && usoKg > m.stock) {
            const msg = '«' + m.label + '» tiene ' + formatoKg(m.stock) + ' kg en stock y el lote requiere ' + formatoKg(usoKg) + ' kg. Reduzca empaques o elija otra materia prima.';
            if (alertaStockTexto) {
                alertaStockTexto.textContent = msg;
            }
            alertaStock?.classList.remove('d-none');
            btnCrearLote?.setAttribute('disabled', 'disabled');
            return false;
        }

        alertaStock?.classList.add('d-none');
        btnCrearLote?.removeAttribute('disabled');
        return true;
    }

    function actualizarPlanificacionEmpaque() {
        const box = document.getElementById('planificacionEmpaqueBox');
        const texto = document.getElementById('planificacionEmpaqueTexto');
        if (!box || !texto || !tieneMateriaPrima()) {
            box?.classList.add('d-none');
            return;
        }

        const calc = calcularPlanificacionLocal();
        if (!calc) {
            box.classList.add('d-none');
            return;
        }

        const m = materias[0];
        const stockTxt = esUnidadKg(m.unidad)
            ? ' · disp. <strong>' + formatoKg(m.stock) + ' kg</strong>'
            : '';

        texto.innerHTML =
            'Con <strong>' + esc(m.label) + '</strong>' + stockTxt + ': estimado ' +
            '<strong>' + formatoEntero(calc.unidades) + ' ' + calc.etiqueta_unidad + '</strong> ' +
            '(~' + formatoKg(calc.salida_kg) + ' kg de producto). ' +
            'Se usarán <strong>' + formatoKg(calc.entrada_kg) + ' kg</strong> de este insumo.';
        box.classList.remove('d-none');
        autoRellenarMateriaPrima();
        validarStockMateriaPrima();
    }

    function kgMateriaPrimaSugerido() {
        const calc = calcularPlanificacionLocal();
        return calc ? calc.entrada_kg : null;
    }

    function autoRellenarMateriaPrima() {
        const entradaKg = kgMateriaPrimaSugerido();
        if (entradaKg == null || entradaKg <= 0 || !materias.length) {
            return;
        }

        const inputsKg = Array.from(tbody.querySelectorAll('.mp-cantidad-input')).filter(inp => esUnidadKg(inp.dataset.unidad));
        if (!inputsKg.length) {
            return;
        }

        if (inputsKg.length === 1) {
            inputsKg[0].value = entradaKg;
            validarStockMateriaPrima();
            return;
        }

        const porInsumo = Math.round((entradaKg / inputsKg.length) * 100) / 100;
        inputsKg.forEach(inp => { inp.value = porInsumo; });
        validarStockMateriaPrima();
    }

    function renderMaterias() {
        tbody.querySelectorAll('tr:not(#filaMateriasVacia)').forEach(r => r.remove());
        if (!materias.length) {
            filaVacia.style.display = '';
            limpiarEmpaquePlanificado();
            actualizarEstadoSeccionEmpaque();
            validarStockMateriaPrima();
            return;
        }
        filaVacia.style.display = 'none';
        materias.forEach((m, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><strong>' + esc(m.label) + '</strong><br><small class="text-muted">' + esc(m.meta) + '</small>' +
                '<input type="hidden" name="materias[' + i + '][insumoid]" value="' + m.id + '"></td>' +
                '<td><div class="input-group input-group-sm">' +
                '<input type="number" name="materias[' + i + '][cantidad]" class="form-control mp-cantidad-input" data-unidad="' + esc(m.unidad) + '" data-stock="' + m.stock + '" step="0.001" min="0.001" max="' + m.stock + '" required>' +
                '<div class="input-group-append"><span class="input-group-text">' + esc(m.unidad) + '</span></div></div></td>' +
                '<td><button type="button" class="btn btn-outline-danger btn-sm btn-quitar-materia" data-idx="' + i + '" title="Quitar materia prima"><i class="fas fa-trash"></i></button></td>';
            tbody.appendChild(tr);
        });
        actualizarEstadoSeccionEmpaque();
        validarStockMateriaPrima();
    }

    function aplicarInsumo(payload) {
        if (!payload || !payload.id) return;
        if (materias.length >= 1) {
            alert('Solo puede usar una materia prima por lote. Quite la actual para cambiarla.');
            return;
        }
        const extra = payload.extra || {};
        if (extra.sin_stock || (extra.stock ?? 0) <= 0) {
            alert('El insumo seleccionado no tiene stock disponible.');
            return;
        }
        if (materias.some(m => String(m.id) === String(payload.id))) {
            alert('Ese insumo ya está en la lista.');
            return;
        }
        materias.push({
            id: payload.id,
            label: payload.label,
            meta: payload.meta || ((extra.almacen || '') + ' · Stock: ' + (extra.stock ?? 0) + ' ' + (extra.unidad || '')),
            stock: extra.stock ?? 999999,
            unidad: extra.unidad || 'ud',
        });
        renderMaterias();
    }

    function actualizarNombrePreview() {
        const producto = (productoInput?.value || '').trim();
        if (!producto) {
            if (nombrePreview) nombrePreview.textContent = '—';
            return;
        }
        if (nombrePreview) nombrePreview.textContent = '…';
        fetch(urlSiguienteNombre + '?producto=' + encodeURIComponent(producto), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(j => { if (nombrePreview) nombrePreview.textContent = j.nombre || '—'; })
            .catch(() => { if (nombrePreview) nombrePreview.textContent = producto + ' - Lote 001'; });
    }

    productoInput?.addEventListener('input', function () {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(actualizarNombrePreview, 280);
    });
    productoInput?.addEventListener('change', actualizarNombrePreview);

    document.getElementById('empaqueCatalogoSlug')?.addEventListener('change', toggleEmpaquePersonalizado);
    document.getElementById('empaquePesoNetoKg')?.addEventListener('input', function () {
        actualizarPlanificacionEmpaque();
        actualizarRecomendacionMp();
    });
    document.getElementById('cantidadEmpaquesObjetivo')?.addEventListener('input', function () {
        actualizarPlanificacionEmpaque();
        actualizarRecomendacionMp();
    });
    document.getElementById('cantidadObjetivoLote')?.addEventListener('input', function () {
        actualizarPlanificacionEmpaque();
        actualizarRecomendacionMp();
    });
    document.querySelectorAll('#toggleModoPlanificacion [data-modo]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            aplicarModoPlanificacion(btn.dataset.modo || EMPAQUE_CFG.modoEmpaques);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('procesamiento_insumo', {
            endpoint: @json(route('catalogo-selector.insumos')),
            title: 'Seleccionar materia prima (cosecha)',
            searchPlaceholder: 'Buscar verdura o tubérculo…',
            colNombre: 'Materia prima',
            colDetalle: 'Almacén y stock',
            theme: 'materia-prima',
            params: { ambito_planta: '1', solo_con_stock: '1', solo_materia_prima_cosecha: '1' },
            filter: { param: 'almacenid', options: @json($filtroAlmacenes) },
            onSelect(item) {
                aplicarInsumo({ id: item.id, label: item.label, meta: item.meta, extra: item.extra });
            },
        });

        document.getElementById('btnBuscarInsumo')?.addEventListener('click', function () {
            CatalogoSelector.open('procesamiento_insumo');
        });
        document.getElementById('btnBuscarPlantilla')?.addEventListener('click', function () {
            PlantillaSelector.open();
        });

        PlantillaSelector.configure({
            endpoint: @json(route('catalogo-selector.plantillas-transformacion')),
            onSelect(item) {
                plantillaInput.value = item.id;
                plantillaDisplay.value = item.label;
                plantillaDisplay.classList.remove('text-muted');
                cargarParametrosPlantilla(item.id);
            },
        });

        actualizarNombrePreview();
        toggleEmpaquePersonalizado();
        aplicarModoPlanificacion(document.getElementById('modoPlanificacion')?.value || EMPAQUE_CFG.modoEmpaques);
        actualizarEstadoSeccionEmpaque();
        validarStockMateriaPrima();
    });

    document.getElementById('btnLimpiarPlantilla')?.addEventListener('click', function () {
        plantillaInput.value = '';
        plantillaDisplay.value = '';
        plantillaDisplay.classList.add('text-muted');
        plantillaDisplay.placeholder = 'Sin proceso asignado';
        cargarParametrosPlantilla(null);
    });

    const urlParametrosPlantilla = @json($urlParametrosPlantillaJs);
    const panelParametros = document.getElementById('panelParametrosLote');
    const listaParametros = document.getElementById('parametrosLoteLista');
    const vacioParametros = document.getElementById('parametrosLoteVacio');
    const chkPersonalizar = document.getElementById('chkPersonalizarParametros');
    let datosParametrosPlantilla = null;

    function combinarLimitesLote(maq, escala) {
        if (!maq && !escala) return null;
        if (!maq) return escala;
        if (!escala) return maq;
        const min = Math.max(maq.min, escala.min);
        const max = Math.min(maq.max, escala.max);
        return max < min ? maq : { min, max };
    }

    function limitesEscalaUnidad(unidad) {
        if (!unidad) return null;
        const m = String(unidad).toLowerCase().match(/escala\s*(\d+(?:[.,]\d+)?)\s*[-–]\s*(\d+(?:[.,]\d+)?)/);
        if (!m) return null;
        return { min: parseFloat(m[1].replace(',', '.')), max: parseFloat(m[2].replace(',', '.')) };
    }

    function acotarValorLote(input, lim) {
        if (!input || !lim) return;
        let v = parseFloat(input.value);
        if (!Number.isFinite(v)) return;
        if (v < lim.min) input.value = String(lim.min);
        if (v > lim.max) input.value = String(lim.max);
    }

    function renderParametrosLote() {
        if (!listaParametros || !panelParametros) return;
        listaParametros.innerHTML = '';
        const personalizar = chkPersonalizar?.checked;
        listaParametros.classList.toggle('d-none', !datosParametrosPlantilla || !datosParametrosPlantilla.pasos?.length);
        vacioParametros?.classList.toggle('d-none', !!(datosParametrosPlantilla && datosParametrosPlantilla.pasos?.length));

        if (!datosParametrosPlantilla?.pasos?.length) return;

        let idx = 0;
        datosParametrosPlantilla.pasos.forEach(function (paso) {
            if (!paso.variables?.length) return;
            const bloque = document.createElement('div');
            bloque.className = 'lote-param-paso';
            bloque.innerHTML = '<div class="lote-param-paso__head">Paso ' + paso.orden + ' · ' + esc(paso.proceso) + (paso.maquina ? ' · ' + esc(paso.maquina) : '') + '</div>';
            paso.variables.forEach(function (v) {
                const maq = (v.maq_minimo != null && v.maq_maximo != null) ? { min: v.maq_minimo, max: v.maq_maximo } : null;
                const lim = combinarLimitesLote(maq, limitesEscalaUnidad(v.unidad));
                const fila = document.createElement('div');
                fila.className = 'lote-param-var' + (personalizar ? '' : ' is-readonly');
                const limTxt = lim ? 'Límite máquina: ' + lim.min + '–' + lim.max : '';
                fila.innerHTML =
                    '<div><strong>' + esc(v.nombre) + '</strong>' + (v.unidad ? ' <span class="text-muted">(' + esc(v.unidad) + ')</span>' : '') +
                    (limTxt ? '<div class="lote-param-limite">' + limTxt + '</div>' : '') + '</div>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm lote-param-min" value="' + v.valor_minimo + '" ' + (personalizar ? '' : 'readonly tabindex="-1"') + '>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm lote-param-max" value="' + v.valor_maximo + '" ' + (personalizar ? '' : 'readonly tabindex="-1"') + '>' +
                    '<input type="hidden" name="parametros_lote[' + idx + '][plantillapasoid]" value="' + paso.plantillapasoid + '">' +
                    '<input type="hidden" name="parametros_lote[' + idx + '][variableestandarid]" value="' + v.variableestandarid + '">';
                const minIn = fila.querySelector('.lote-param-min');
                const maxIn = fila.querySelector('.lote-param-max');
                minIn.name = 'parametros_lote[' + idx + '][valor_minimo]';
                maxIn.name = 'parametros_lote[' + idx + '][valor_maximo]';
                if (lim) {
                    minIn.min = lim.min;
                    minIn.max = lim.max;
                    maxIn.min = lim.min;
                    maxIn.max = lim.max;
                    minIn.title = 'Rango permitido: ' + lim.min + ' – ' + lim.max;
                    maxIn.title = minIn.title;
                    minIn.addEventListener('blur', function () {
                        acotarValorLote(minIn, lim);
                        acotarValorLote(maxIn, lim);
                        if (parseFloat(minIn.value) > parseFloat(maxIn.value)) {
                            maxIn.value = minIn.value;
                        }
                    });
                    maxIn.addEventListener('blur', function () {
                        acotarValorLote(maxIn, lim);
                        acotarValorLote(minIn, lim);
                        if (parseFloat(minIn.value) > parseFloat(maxIn.value)) {
                            minIn.value = maxIn.value;
                        }
                    });
                }
                if (!personalizar) {
                    fila.querySelectorAll('input').forEach(function (inp) { inp.disabled = true; });
                }
                bloque.appendChild(fila);
                idx++;
            });
            listaParametros.appendChild(bloque);
        });
    }

    async function cargarParametrosPlantilla(id) {
        datosParametrosPlantilla = null;
        if (!id) {
            panelParametros?.classList.add('d-none');
            renderParametrosLote();
            return;
        }
        panelParametros?.classList.remove('d-none');
        if (vacioParametros) vacioParametros.textContent = 'Cargando parámetros…';
        try {
            const res = await fetch(urlParametrosPlantilla.replace('__ID__', String(id)), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error();
            datosParametrosPlantilla = await res.json();
        } catch (e) {
            if (vacioParametros) vacioParametros.textContent = 'No se pudieron cargar los parámetros.';
            listaParametros?.classList.add('d-none');
            return;
        }
        renderParametrosLote();
    }

    chkPersonalizar?.addEventListener('change', renderParametrosLote);

    if (plantillaInput?.value) {
        cargarParametrosPlantilla(plantillaInput.value);
    }

    tbody.addEventListener('input', function (e) {
        if (e.target.classList.contains('mp-cantidad-input')) {
            validarStockMateriaPrima();
        }
    });

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-quitar-materia');
        if (!btn) return;
        materias.splice(parseInt(btn.dataset.idx, 10), 1);
        renderMaterias();
    });

    document.getElementById('formNuevoLote')?.addEventListener('submit', function (e) {
        if (!materias.length) { e.preventDefault(); alert('Seleccione una materia prima.'); return; }
        if (materias.length > 1) { e.preventDefault(); alert('Solo puede usar una materia prima por lote.'); return; }
        if (!validarStockMateriaPrima()) {
            e.preventDefault();
            alert(alertaStockTexto?.textContent || 'La cantidad supera el stock disponible.');
            return;
        }
        const slug = document.getElementById('empaqueCatalogoSlug')?.value;
        if (!slug) { e.preventDefault(); alert('Seleccione la presentación comercial (empaque).'); return; }
        if (slug === EMPAQUE_CFG.slugPersonalizado) {
            const nom = (document.getElementById('empaqueNombrePersonalizado')?.value || '').trim();
            const peso = parseFloat(document.getElementById('empaquePesoNetoKg')?.value || '0');
            if (!nom || peso <= 0) { e.preventDefault(); alert('Complete nombre y peso de la presentación personalizada.'); return; }
        }
    });

    if (plantillaInput.value && plantillaDisplay.value) plantillaDisplay.classList.remove('text-muted');

    @if($errors->any() || old('producto'))
    $('#modalNuevoLote').modal('show');
    @endif
})();
</script>
@endpush
@endcan
