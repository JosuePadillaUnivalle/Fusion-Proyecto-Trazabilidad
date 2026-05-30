@extends('layouts.app')

@section('title', 'Registrar Actividad | AgroFusion')
@section('page_title', 'Registrar Actividad')

@section('content')
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Registrar Actividad</h3>
    </div>

    @if($errors->any())
        <div class="alert alert-danger m-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('actividades.store') }}" method="POST">
        @csrf
        @if(!empty($returnUrl))
            <input type="hidden" name="return" value="{{ $returnUrl }}">
            <input type="hidden" name="completar" value="1">
        @endif
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">

                    @include('partials.selector-catalogo', [
                        'id' => 'actividad_lote',
                        'name' => 'loteid',
                        'label' => 'Lote',
                        'icon' => 'fa-map-marked-alt',
                        'value' => old('loteid', $loteid ?? null),
                        'labelSelected' => $loteLabel ?? '',
                        'endpoint' => route('catalogo-selector.lotes'),
                        'title' => 'Seleccionar lote',
                        'searchPlaceholder' => 'Nombre, código o ubicación…',
                        'required' => true,
                    ])

                    <div class="form-group">
                        <label><i class="fas fa-user mr-1"></i> Responsable</label>
                        <input type="text" id="responsable_display" class="form-control bg-light" readonly 
                               placeholder="Se asigna automaticamente al seleccionar el lote">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> El responsable se toma automaticamente del lote
                        </small>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-clipboard-list mr-1"></i> Tipo de Actividad <span class="text-danger">*</span></label>
                        <select name="tipoactividadid" class="form-control" required>
                            <option value="">-- Seleccione tipo --</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->tipoactividadid }}"
                                    @selected(old('tipoactividadid', $tipoPreselect?->tipoactividadid) == $t->tipoactividadid)>
                                    {{ ucfirst($t->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-align-left mr-1"></i> Descripcion <span class="text-danger">*</span></label>
                        <input type="text" name="descripcion" class="form-control" required maxlength="200"
                               placeholder="Describa brevemente la actividad" value="{{ old('descripcion', $tipoPreselect?->nombre ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-flag mr-1"></i> Prioridad <span class="text-danger">*</span></label>
                        <select name="prioridadid" class="form-control" required>
                            @foreach($prioridades as $p)
                                <option value="{{ $p->prioridadid }}">{{ ucfirst($p->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-comment mr-1"></i> Observaciones</label>
                        <textarea name="observaciones" class="form-control" maxlength="250" rows="2"
                                  placeholder="Opcional...">{{ old('observaciones') }}</textarea>
                    </div>

                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Informacion</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-2">
                                <i class="fas fa-calendar mr-1"></i> <strong>Fecha inicio:</strong> Se registra automaticamente (ahora)
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-user mr-1"></i> <strong>Responsable:</strong> Se asigna del lote seleccionado
                            </p>
                        </div>
                    </div>

                    <div class="card mt-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-list mr-1"></i> Tipos de Actividad</h6>
                        </div>
                        <div class="card-body small">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><i class="fas fa-seedling text-success mr-1"></i> Siembra</li>
                                <li class="mb-1"><i class="fas fa-tint text-primary mr-1"></i> Riego</li>
                                <li class="mb-1"><i class="fas fa-spray-can text-warning mr-1"></i> Fumigacion</li>
                                <li class="mb-1"><i class="fas fa-truck-loading text-info mr-1"></i> Cosecha</li>
                                <li><i class="fas fa-tractor text-secondary mr-1"></i> Labranza</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <a href="{{ $returnUrl ?? route('actividades.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-save mr-1"></i> Registrar Actividad
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selector_wrap_actividad_lote')?.addEventListener('selector-catalogo:change', function (e) {
    const extra = e.detail.extra || {};
    document.getElementById('responsable_display').value = extra.responsable || 'Sin responsable asignado';
});
</script>
@endpush