@extends('layouts.app')

@section('title', 'Nuevo Registro a Planta | AgroFusion')
@section('page_title', 'Nuevo Registro a Planta')

@push('styles')
<style>
.paso-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin-bottom:12px;transition:border-color .15s;}
.paso-card:hover{border-color:#10b981;}
.paso-num{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:.82rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.paso-head{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
.proceso-tab{padding:8px 16px;border-radius:20px;font-size:.82rem;font-weight:600;cursor:pointer;border:2px solid #e2e8f0;background:#fff;transition:all .14s;}
.proceso-tab.active{background:linear-gradient(135deg,#059669,#10b981);color:#fff;border-color:transparent;}
.var-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
.var-label{font-size:.78rem;font-weight:600;color:#475569;min-width:120px;}
.cumple-toggle{display:flex;gap:8px;}
.cumple-btn{flex:1;padding:8px;border-radius:8px;font-size:.8rem;font-weight:600;border:2px solid #e2e8f0;background:#fff;cursor:pointer;transition:all .14s;text-align:center;}
.cumple-btn.si.active{background:#d1fae5;border-color:#10b981;color:#065f46;}
.cumple-btn.no.active{background:#fee2e2;border-color:#ef4444;color:#b91c1c;}
.step-indicator{display:flex;align-items:center;gap:8px;margin-bottom:24px;}
.step{display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:600;color:#94a3b8;}
.step.active{color:#059669;}
.step.done{color:#10b981;}
.step-circle{width:28px;height:28px;border-radius:50%;border:2px solid currentColor;display:flex;align-items:center;justify-content:center;font-size:.72rem;}
.step.active .step-circle{background:#059669;border-color:#059669;color:#fff;}
.step.done .step-circle{background:#10b981;border-color:#10b981;color:#fff;}
.step-line{flex:1;height:2px;background:#e2e8f0;}
.step.done + .step-line, .step.active + .step-line{background:#10b981;}
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('registro-planta.store') }}" id="formRegistro">
@csrf

<div class="row">
    {{-- Step 1: Selección --}}
    <div class="col-12 mb-3" id="panelSeleccion">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-seedling text-success mr-2"></i>
                <strong>Paso 1 — Seleccionar lote y proceso</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        @include('partials.selector-catalogo', [
                            'id' => 'registro_planta_lote',
                            'name' => 'loteid',
                            'label' => 'Lote a ingresar a planta',
                            'icon' => 'fa-map-marked-alt',
                            'value' => old('loteid'),
                            'labelSelected' => $loteLabel ?? '',
                            'endpoint' => route('catalogo-selector.lotes'),
                            'title' => 'Seleccionar lote',
                            'searchPlaceholder' => 'Nombre, código o cultivo…',
                            'help' => 'Seleccioná el lote que ingresó a la planta para procesamiento.',
                            'required' => true,
                        ])
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Proceso a realizar <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2 mt-1" id="procesosTabs">
                            @foreach($procesos as $proceso)
                            <button type="button"
                                class="proceso-tab {{ $loop->first ? 'active' : '' }}"
                                data-proceso="{{ $proceso->procesoplantaid }}"
                                onclick="selectProceso({{ $proceso->procesoplantaid }}, this)">
                                {{ $proceso->nombre }}
                            </button>
                            @endforeach
                        </div>
                        <input type="hidden" id="procesoSeleccionado" value="{{ $procesos->first()?->procesoplantaid }}">
                    </div>
                </div>

                <div class="text-right">
                    <button type="button" class="btn btn-success" onclick="mostrarPasos()">
                        <i class="fas fa-arrow-right mr-1"></i>Continuar al registro
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 2: Pasos del proceso --}}
    <div class="col-12" id="panelPasos" style="display:none;">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-tasks text-success mr-2"></i>
                    <strong>Paso 2 — Registrar cada etapa del proceso</strong>
                    <span class="badge badge-success ml-2" id="labelProceso"></span>
                    · Lote: <span id="labelLote" class="font-weight-600 text-success"></span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="volverSeleccion()">
                    <i class="fas fa-arrow-left mr-1"></i>Cambiar selección
                </button>
            </div>
        </div>

        {{-- Pasos dinámicos per proceso --}}
        @foreach($procesos as $proceso)
        <div class="pasos-proceso" id="pasos-{{ $proceso->procesoplantaid }}"
             style="{{ $loop->first ? '' : 'display:none;' }}">
            @foreach($proceso->pasos->sortBy('orden_paso') as $paso)
            <div class="paso-card">
                <div class="paso-head">
                    <div class="paso-num">{{ $paso->orden_paso }}</div>
                    <div>
                        <div class="font-weight-700" style="font-size:.9rem;">{{ $paso->nombre }}</div>
                        <div class="text-muted" style="font-size:.76rem;">
                            <i class="fas fa-cog mr-1"></i>{{ $paso->maquina->nombre ?? 'N/D' }}
                            @if($paso->tiempo_estimado)
                                · <i class="fas fa-clock mr-1"></i>{{ $paso->tiempo_estimado }} min estimados
                            @endif
                        </div>
                    </div>
                </div>

                <input type="hidden" name="pasos[{{ $paso->procesomaquinaplantaid }}][procesomaquinaplantaid]"
                       value="{{ $paso->procesomaquinaplantaid }}">

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Hora inicio</label>
                        <input type="datetime-local" name="pasos[{{ $paso->procesomaquinaplantaid }}][hora_inicio]"
                               class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Hora fin</label>
                        <input type="datetime-local" name="pasos[{{ $paso->procesomaquinaplantaid }}][hora_fin]"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">¿Cumple estándar?</label>
                        <div class="cumple-toggle">
                            <div class="cumple-btn si active" onclick="setCumple(this, {{ $paso->procesomaquinaplantaid }}, 1)">
                                <i class="fas fa-check mr-1"></i>Sí cumple
                            </div>
                            <div class="cumple-btn no" onclick="setCumple(this, {{ $paso->procesomaquinaplantaid }}, 0)">
                                <i class="fas fa-times mr-1"></i>No cumple
                            </div>
                        </div>
                        <input type="hidden" name="pasos[{{ $paso->procesomaquinaplantaid }}][cumple_estandar]"
                               id="cumple-{{ $paso->procesomaquinaplantaid }}" value="1">
                    </div>
                </div>

                {{-- Variables medibles --}}
                @if($variables->isNotEmpty())
                <div class="mt-2">
                    <label class="form-label" style="font-size:.76rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em;">
                        Variables medidas (opcionales)
                    </label>
                    <div class="row">
                        @foreach($variables->take(4) as $var)
                        <div class="col-6 col-md-3 mb-2">
                            <label class="form-label" style="font-size:.75rem;">{{ $var->nombre }} ({{ $var->unidad }})</label>
                            <input type="number" step="0.01"
                                   name="pasos[{{ $paso->procesomaquinaplantaid }}][var_{{ $var->codigo }}]"
                                   class="form-control form-control-sm"
                                   placeholder="{{ $var->unidad }}">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-2">
                    <label class="form-label">Observaciones</label>
                    <textarea name="pasos[{{ $paso->procesomaquinaplantaid }}][observaciones]"
                              class="form-control form-control-sm" rows="2"
                              placeholder="Notas del operador sobre este paso…"></textarea>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
            <a href="{{ route('registro-planta.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times mr-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save mr-2"></i>Guardar registro de planta
            </button>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
var procesoActual = {{ $procesos->first()?->procesoplantaid ?? 'null' }};
var procesosData  = @json($procesos->map(fn($p) => ['id' => $p->procesoplantaid, 'nombre' => $p->nombre]));

function selectProceso(id, btn) {
    procesoActual = id;
    document.getElementById('procesoSeleccionado').value = id;
    document.querySelectorAll('.proceso-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function mostrarPasos() {
    var wrap = document.getElementById('selector_wrap_registro_planta_lote');
    var loteId = wrap ? wrap.querySelector('.selector-catalogo-value')?.value : '';
    var loteNombre = wrap ? wrap.querySelector('.selector-catalogo-label')?.value : '';
    if (!loteId) { alert('Seleccioná un lote primero.'); return; }
    var proc = procesosData.find(p => p.id == procesoActual);
    document.getElementById('labelLote').textContent = loteNombre || ('#' + loteId);
    document.getElementById('labelProceso').textContent = proc ? proc.nombre : '';
    document.getElementById('panelSeleccion').style.display = 'none';
    document.getElementById('panelPasos').style.display = '';
    // Show correct process steps
    document.querySelectorAll('.pasos-proceso').forEach(d => d.style.display = 'none');
    var el = document.getElementById('pasos-' + procesoActual);
    if (el) el.style.display = '';
}

function volverSeleccion() {
    document.getElementById('panelSeleccion').style.display = '';
    document.getElementById('panelPasos').style.display = 'none';
}

function setCumple(btn, pasoId, val) {
    var parent = btn.closest('.cumple-toggle');
    parent.querySelectorAll('.cumple-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('cumple-' + pasoId).value = val;
}
</script>
@endpush
