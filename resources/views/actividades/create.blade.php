@extends('layouts.app')

@section('title', 'Registrar Actividad | AgroFusion')
@section('page_title', 'Registrar Actividad')

@section('content')
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Registrar Actividad</h3>
    </div>

    <form action="{{ route('actividades.store') }}" method="POST" enctype="multipart/form-data" id="formActividad">
        @csrf
        @if(!empty($returnUrl))
            <input type="hidden" name="return" value="{{ $returnUrl }}">
        @endif
        @if(!empty($desdeTrazabilidad))
            <input type="hidden" name="desde_trazabilidad" value="1">
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

                    @if(!empty($puedeDesignarResponsable))
                        @include('partials.selector-catalogo', [
                            'id' => 'actividad_responsable',
                            'name' => 'usuarioid',
                            'label' => 'Responsable',
                            'icon' => 'fa-user',
                            'value' => old('usuarioid', $responsableInicial ?? ''),
                            'labelSelected' => $responsableLabel ?? '',
                            'endpoint' => route('catalogo-selector.usuarios'),
                            'params' => $responsableSelectorParams ?? ['roles' => 'agricultor'],
                            'title' => 'Seleccionar responsable',
                            'searchPlaceholder' => 'Nombre, correo o usuario…',
                            'help' => ! empty($esJefeAgricultorDesignando)
                                ? 'Asigne un agricultor de su equipo. Usted supervisa; el empleado recibirá la alerta y será el responsable.'
                                : 'Elija el agricultor operativo que ejecutará la actividad. Recibirá una alerta en su panel.',
                            'required' => true,
                        ])
                    @else
                        <div class="form-group">
                            <label><i class="fas fa-user mr-1"></i> Responsable</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ $responsableLabel ?? '' }}"
                                   placeholder="Usted es el responsable">
                            <input type="hidden" name="usuarioid" value="{{ auth()->user()->usuarioid }}">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Usted queda asignado automáticamente como responsable
                            </small>
                        </div>
                    @endif

                    <div class="form-group">
                        <label><i class="fas fa-clipboard-list mr-1"></i> Tipo de Actividad <span class="text-danger">*</span></label>
                        <select name="tipoactividadid" id="tipoactividadid" class="form-control" required>
                            <option value="">— Elija el tipo —</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t->tipoactividadid }}"
                                    @selected(old('tipoactividadid') == $t->tipoactividadid)>
                                    {{ ucfirst($t->nombre) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Al elegir el tipo se abrirá un cuadro para indicar insumos o tipo de riego.</small>
                        @error('detalle_actividad_json')
                            <div class="alert alert-danger small mt-2 mb-0 py-2">{{ $message }}</div>
                        @enderror
                        <div id="actDetError" class="alert alert-warning small mt-2 mb-0 py-2 d-none"></div>
                    </div>

                    @include('actividades.partials.detalle-actividad', ['modoSiembra' => false, 'sugerenciaSiembra' => null, 'insumosSiembra' => []])

                    <div class="form-group">
                        <label><i class="fas fa-align-left mr-1"></i> Descripción</label>
                        <input type="text" name="descripcion" class="form-control" maxlength="200"
                               value="{{ old('descripcion') }}">
                        <small class="form-text text-muted">Opcional. Si la deja vacía, se usará el tipo de actividad.</small>
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

                    @if(empty($puedeDesignarResponsable))
                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="completar_ahora" name="completar" value="1"
                                    @checked(old('completar', request()->boolean('completar')))>
                                <label class="custom-control-label" for="completar_ahora">
                                    Ya realicé esta actividad (marcar como completada al guardar)
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Si solo la registra para después, déjela sin marcar; quedará pendiente en su panel.
                            </small>
                            <div id="actividadEvidenciaWrap" class="mt-3 p-3 border rounded bg-light" style="{{ old('completar', request()->boolean('completar')) ? '' : 'display:none;' }}">
                                <label class="font-weight-bold d-block mb-1">
                                    <i class="fas fa-camera mr-1 text-success"></i> Foto de evidencia <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="evidencia_foto" id="actividadEvidenciaFoto"
                                       accept="image/jpeg,image/jpg,image/png,image/webp">
                                @error('evidencia_foto')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border small mb-0">
                            <i class="fas fa-bell text-info mr-1"></i>
                            La actividad quedará <strong>pendiente</strong> hasta que el agricultor asignado la marque como completada
                            (usted también puede completarla después desde Actividades).
                        </div>
                    @endif

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
                                <i class="fas fa-user mr-1"></i> <strong>Responsable:</strong>
                                @if(!empty($puedeDesignarResponsable))
                                    @if(!empty($esJefeAgricultorDesignando))
                                        Usted asigna a un empleado; él recibe la alerta
                                    @else
                                        Lo designa quien registra; el agricultor recibe alerta
                                    @endif
                                @else
                                    Usted (agricultor asignado al lote)
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card mt-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-list mr-1"></i> Tipos disponibles</h6>
                        </div>
                        <div class="card-body small">
                            <ul class="list-unstyled mb-0">
                                @php
                                    $iconosTipo = [
                                        'siembra' => ['fa-seedling', 'text-success'],
                                        'riego' => ['fa-tint', 'text-primary'],
                                        'fertilización' => ['fa-flask', 'text-teal'],
                                        'fertilizacion' => ['fa-flask', 'text-teal'],
                                        'control de plagas' => ['fa-bug', 'text-warning'],
                                    ];
                                @endphp
                                @forelse($tipos as $t)
                                    @php
                                        $clave = mb_strtolower(trim($t->nombre));
                                        $icono = $iconosTipo[$clave] ?? ['fa-tasks', 'text-secondary'];
                                    @endphp
                                    <li class="mb-1">
                                        <i class="fas {{ $icono[0] }} {{ $icono[1] }} mr-1"></i> {{ ucfirst($t->nombre) }}
                                    </li>
                                @empty
                                    <li class="text-muted">No hay tipos configurados.</li>
                                @endforelse
                            </ul>
                            <p class="text-muted mb-0 mt-2"><i class="fas fa-info-circle mr-1"></i> La cosecha se registra desde la trazabilidad del lote, no como actividad.</p>
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
(function () {
    var check = document.getElementById('completar_ahora');
    var wrap = document.getElementById('actividadEvidenciaWrap');
    var input = document.getElementById('actividadEvidenciaFoto');
    function toggle() {
        if (!wrap || !check) return;
        wrap.style.display = check.checked ? '' : 'none';
        if (input) input.required = check.checked;
    }
    check?.addEventListener('change', toggle);
    toggle();
})();

document.getElementById('selector_wrap_actividad_lote')?.addEventListener('selector-catalogo:change', function (e) {
    const extra = e.detail.extra || {};
    @if(!empty($puedeDesignarResponsable))
    const idsResponsablesPermitidos = @json(
        ($usuariosResponsables ?? collect())->pluck('usuarioid')->map(fn ($id) => (int) $id)->values()
    );
    if (extra.usuarioid && idsResponsablesPermitidos.includes(Number(extra.usuarioid)) && window.CatalogoSelector) {
        const respInput = document.querySelector('#selector_wrap_actividad_responsable .selector-catalogo-value');
        if (!respInput || !respInput.value) {
            window.CatalogoSelector.setValue('actividad_responsable', extra.usuarioid, extra.responsable || '');
        }
    }
    @endif
});
</script>
@endpush