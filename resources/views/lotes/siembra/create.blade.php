@extends('layouts.app')

@section('title', 'Registrar Siembra | AgroFusion')
@section('page_title', 'Registrar Siembra')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.trazabilidad', $lote) }}">{{ $lote->nombre }}</a></li>
    <li class="breadcrumb-item active">Siembra</li>
@endsection

@push('styles')
<style>
    .siembra-page { --siembra-primary: #0d9488; --siembra-dark: #0f766e; --siembra-light: #ccfbf1; }
    .siembra-hero {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 55%, #5eead4 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 28px rgba(13, 148, 136, .25);
        position: relative;
        overflow: hidden;
    }
    .siembra-hero::after {
        content: '';
        position: absolute;
        right: -30px;
        top: -30px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .siembra-hero__icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(255,255,255,.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin-bottom: .75rem;
    }
    .siembra-hero h2 { font-weight: 800; font-size: 1.55rem; margin: 0 0 .35rem; }
    .siembra-hero p { margin: 0; opacity: .92; font-size: .95rem; max-width: 36rem; }
    .siembra-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, .07);
        overflow: hidden;
    }
    .siembra-card .card-body { padding: 1.5rem 1.75rem; }
    .siembra-lote-panel {
        background: linear-gradient(160deg, #f0fdfa, #fff);
        border: 1px solid #99f6e4;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.25rem;
    }
    .siembra-lote-panel__title {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #0f766e;
        font-weight: 700;
        margin-bottom: .5rem;
    }
    .siembra-lote-panel__name {
        font-size: 1.15rem;
        font-weight: 800;
        color: #134e4a;
        margin-bottom: .65rem;
    }
    .siembra-chip {
        display: inline-flex;
        align-items: center;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
        background: #fff;
        border: 1px solid #ccfbf1;
        color: #115e59;
        margin: 0 .35rem .35rem 0;
    }
    .siembra-guia {
        background: #f0fdfa;
        border-left: 3px solid var(--siembra-primary);
        border-radius: 0 10px 10px 0;
        padding: .7rem .9rem;
        margin-bottom: .85rem;
        font-size: .86rem;
        color: #334155;
    }
    .siembra-guia strong { color: #0f766e; }
    .siembra-steps {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .siembra-steps li {
        display: flex;
        gap: .75rem;
        padding: .75rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .siembra-steps li:last-child { border-bottom: 0; }
    .siembra-steps__num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--siembra-light);
        color: var(--siembra-dark);
        font-weight: 800;
        font-size: .8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .siembra-aside {
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 1.1rem 1.2rem;
    }
    .siembra-aside h6 {
        font-weight: 700;
        color: #0f766e;
        margin-bottom: .85rem;
    }
    .siembra-form .form-control {
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        min-height: 46px;
    }
    .siembra-form .form-control:focus {
        border-color: var(--siembra-primary);
        box-shadow: 0 0 0 .15rem rgba(13, 148, 136, .15);
    }
    .btn-siembra {
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        border: none;
        color: #fff;
        font-weight: 700;
        padding: .65rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 14px rgba(13, 148, 136, .3);
    }
    .btn-siembra:hover { color: #fff; filter: brightness(1.05); }
    .siembra-fase-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255,255,255,.2);
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 999px;
        padding: .25rem .75rem;
        font-size: .78rem;
        font-weight: 600;
        margin-top: .75rem;
    }
</style>
@endpush

@section('content')
<div class="siembra-page">
    <div class="siembra-hero">
        <div class="siembra-hero__icon"><i class="fas fa-seedling"></i></div>
        <h2>Registrar siembra del lote</h2>
        <p>
            Hito único del ciclo productivo. Asigne quién ejecutará la siembra y, cuando se realice en campo,
            el responsable deberá subir la evidencia fotográfica para marcarla como completada.
        </p>
        <span class="siembra-fase-badge"><i class="fas fa-layer-group mr-1"></i> Fase: Siembra</span>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card siembra-card siembra-form">
                <form action="{{ route('lotes.siembra.store', $lote) }}" method="POST" enctype="multipart/form-data" id="formSiembra">
                    @csrf
                    <input type="hidden" name="return" value="{{ $returnUrl }}">

                    <div class="card-body">
                        <div class="siembra-lote-panel">
                            <div class="siembra-lote-panel__title"><i class="fas fa-map-marked-alt mr-1"></i> Lote seleccionado</div>
                            <div class="siembra-lote-panel__name">{{ $lote->nombre }}</div>
                            <div>
                                @if($lote->cultivo_etiqueta)
                                    <span class="siembra-chip"><i class="fas fa-leaf mr-1"></i>{{ $lote->cultivo_etiqueta }}</span>
                                @endif
                                <span class="siembra-chip"><i class="fas fa-ruler-combined mr-1"></i>{{ $lote->superficie_etiqueta }}</span>
                                @if($lote->codigo_trazabilidad)
                                    <span class="siembra-chip"><i class="fas fa-qrcode mr-1"></i>{{ $lote->codigo_trazabilidad }}</span>
                                @endif
                                @if($lote->estadoTipo)
                                    <span class="siembra-chip"><i class="fas fa-circle mr-1" style="font-size:.4rem;"></i>{{ ucfirst($lote->estadoTipo->nombre) }}</span>
                                @endif
                            </div>
                        </div>

                        @if(!empty($puedeDesignarResponsable))
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-user mr-1 text-teal"></i> Agricultor responsable <span class="text-danger">*</span></label>
                                <div class="siembra-guia">
                                    <strong>¿Quién siembra?</strong> La persona asignada recibirá la alerta y deberá completar la actividad con foto en campo.
                                </div>
                                @include('partials.selector-catalogo', [
                                    'id' => 'siembra_responsable',
                                    'name' => 'usuarioid',
                                    'value' => old('usuarioid', $responsableInicial ?? ''),
                                    'labelSelected' => $responsableLabel ?? '',
                                    'endpoint' => route('catalogo-selector.usuarios'),
                                    'params' => $responsableSelectorParams ?? ['roles' => 'agricultor'],
                                    'title' => 'Seleccionar agricultor',
                                    'searchPlaceholder' => 'Nombre, correo o usuario…',
                                    'required' => true,
                                ])
                            </div>
                        @else
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-user mr-1 text-teal"></i> Responsable</label>
                                <input type="text" class="form-control bg-light" readonly value="{{ $responsableLabel ?? '' }}">
                                <input type="hidden" name="usuarioid" value="{{ auth()->user()->usuarioid }}">
                                <small class="form-text text-muted">Usted ejecutará la siembra en campo.</small>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-day mr-1 text-teal"></i> Fecha programada</label>
                                    <div class="siembra-guia">
                                        <strong>¿Cuándo?</strong> Día planificado para sembrar. Al completar, quedará como fecha de siembra del lote.
                                    </div>
                                    <input type="date" name="fechainicio" class="form-control"
                                           value="{{ old('fechainicio', now()->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-flag mr-1 text-teal"></i> Prioridad</label>
                                    <select name="prioridadid" class="form-control">
                                        @foreach($prioridades as $p)
                                            <option value="{{ $p->prioridadid }}" @selected(old('prioridadid', $prioridades->first()?->prioridadid) == $p->prioridadid)>
                                                {{ ucfirst($p->nombre) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        @include('actividades.partials.detalle-actividad', [
                            'modoSiembra' => true,
                            'sugerenciaSiembra' => $sugerenciaSiembra ?? null,
                            'insumosSiembra' => $insumosSiembra ?? [],
                        ])

                        <div class="form-group">
                            <label class="font-weight-bold"><i class="fas fa-align-left mr-1 text-teal"></i> Notas de siembra</label>
                            <input type="text" name="descripcion" class="form-control" maxlength="200"
                                   value="{{ old('descripcion') }}"
                                   placeholder="Ej: Surco 1-4, variedad confirmada, densidad 25 cm…">
                            <small class="form-text text-muted">Opcional. Detalles útiles para trazabilidad.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold"><i class="fas fa-comment mr-1 text-teal"></i> Observaciones</label>
                            <textarea name="observaciones" class="form-control" maxlength="250" rows="2"
                                      placeholder="Condiciones del suelo, clima, insumos previstos…">{{ old('observaciones') }}</textarea>
                        </div>

                        @if(empty($puedeDesignarResponsable))
                            <div class="custom-control custom-checkbox mt-3">
                                <input type="checkbox" class="custom-control-input" id="completar_siembra" name="completar" value="1"
                                    @checked(old('completar', request()->boolean('completar')))>
                                <label class="custom-control-label" for="completar_siembra">
                                    Ya realicé la siembra (marcar como completada al guardar)
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Si aún no se sembró, déjela pendiente; podrá completarla con foto desde Actividades o el panel de trazabilidad.
                            </small>

                            <div id="siembraEvidenciaWrap" class="mt-3" style="{{ old('completar', request()->boolean('completar')) ? '' : 'display:none;' }}">
                                <label class="font-weight-bold d-block">
                                    <i class="fas fa-camera mr-1 text-teal"></i> Foto de evidencia <span class="text-danger">*</span>
                                </label>
                                <div class="siembra-guia mb-2">
                                    <strong>Obligatorio al completar.</strong> Suba una imagen del surco sembrado, semillas aplicadas o el trabajo realizado en campo.
                                </div>
                                <input type="file" name="evidencia_foto" id="siembraEvidenciaFoto"
                                       class="form-control-file" accept="image/jpeg,image/jpg,image/png,image/webp">
                                <div id="siembraEvidenciaPreview" class="text-center mt-2" style="display:none;">
                                    <img id="siembraEvidenciaImg" alt="Vista previa" class="img-fluid rounded border" style="max-height:200px;">
                                </div>
                                @error('evidencia_foto')
                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>
                        @else
                            <div class="alert alert-light border small mt-3 mb-0">
                                <i class="fas fa-camera text-teal mr-1"></i>
                                La siembra quedará <strong>pendiente</strong> hasta que el agricultor la complete con evidencia fotográfica.
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                        <a href="{{ $returnUrl }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-1"></i> Volver a trazabilidad
                        </a>
                        <button type="submit" class="btn btn-siembra">
                            <i class="fas fa-seedling mr-1"></i> Registrar siembra
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="siembra-aside mb-3">
                <h6><i class="fas fa-route mr-1"></i> ¿Qué pasa después?</h6>
                <ol class="siembra-steps">
                    <li>
                        <span class="siembra-steps__num">1</span>
                        <div>
                            <strong>Asignación</strong>
                            <p class="small text-muted mb-0">Se crea la actividad de siembra y el agricultor recibe la alerta.</p>
                        </div>
                    </li>
                    <li>
                        <span class="siembra-steps__num">2</span>
                        <div>
                            <strong>Ejecución en campo</strong>
                            <p class="small text-muted mb-0">Al sembrar, debe subir una foto como evidencia.</p>
                        </div>
                    </li>
                    <li>
                        <span class="siembra-steps__num">3</span>
                        <div>
                            <strong>En crecimiento</strong>
                            <p class="small text-muted mb-0">Luego podrá registrar riego, control de plagas y fertilización las veces que necesite.</p>
                        </div>
                    </li>
                </ol>
            </div>

            <div class="siembra-aside">
                <h6><i class="fas fa-info-circle mr-1"></i> Importante</h6>
                <ul class="small text-muted pl-3 mb-0">
                    <li class="mb-2">La <strong>siembra es un hito único</strong> por lote.</li>
                    <li class="mb-2">Riego, fertilización y control de plagas sí pueden repetirse durante el crecimiento.</li>
                    <li>La cosecha se registra en un módulo aparte, cuando el lote esté listo.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var check = document.getElementById('completar_siembra');
    var wrap = document.getElementById('siembraEvidenciaWrap');
    var input = document.getElementById('siembraEvidenciaFoto');
    var preview = document.getElementById('siembraEvidenciaPreview');
    var img = document.getElementById('siembraEvidenciaImg');
    var form = document.getElementById('formSiembra');

    function toggleEvidencia() {
        if (!wrap || !check) return;
        var show = check.checked;
        wrap.style.display = show ? '' : 'none';
        if (input) input.required = show;
        if (!show && input) {
            input.value = '';
            if (preview) preview.style.display = 'none';
            if (img) img.removeAttribute('src');
        }
    }

    check?.addEventListener('change', toggleEvidencia);
    toggleEvidencia();

    input?.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file || !preview || !img) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    form?.addEventListener('submit', function (e) {
        if (check && check.checked && input && !input.files.length) {
            e.preventDefault();
            alert('Debe subir una foto de evidencia para marcar la siembra como completada.');
        }
    });
})();
</script>
@endpush
