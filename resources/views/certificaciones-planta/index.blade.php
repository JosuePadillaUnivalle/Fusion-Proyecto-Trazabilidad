@extends('layouts.app')

@section('title', 'Certificaciones de planta')
@section('page_title', 'Certificaciones de planta')

@section('content')
<style>
    .cert-page-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 45%, #3b82f6 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 6px 24px rgba(30, 58, 95, .18);
    }
    .cert-page-hero h2 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0 0 .35rem;
    }
    .cert-page-hero p {
        margin: 0;
        font-size: .9rem;
        opacity: .92;
        max-width: 42rem;
    }
    .cert-kpi {
        border-radius: 14px;
        border: none;
        color: #fff;
        min-height: 108px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .08);
    }
    .cert-kpi .card-body { padding: 1.1rem 1.25rem; }
    .cert-kpi .kpi-value { font-size: 2.1rem; font-weight: 800; line-height: 1; }
    .cert-kpi .kpi-label { font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; opacity: .85; }
    .lote-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: box-shadow .2s ease, border-color .2s ease;
    }
    .lote-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.06); border-color: #93c5fd; }
    .cert-badge {
        font-family: ui-monospace, monospace;
        letter-spacing: .03em;
        font-size: .78rem;
    }
    .cert-timeline { max-height: 520px; overflow-y: auto; }
    .cert-item {
        transition: background-color .15s ease;
        cursor: pointer;
    }
    .cert-item:hover { background-color: #f8fafc; }
    .cert-info-strip {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: .85rem 1rem;
        font-size: .88rem;
        color: #1e40af;
        margin-bottom: 1.25rem;
    }
</style>

<div class="container-fluid">
    <div class="cert-page-hero">
        <h2><i class="fas fa-certificate mr-2"></i>Certificaciones de lotes de planta</h2>
        <p>
            Evalúe lotes con <strong>transformación completada</strong> como Certificado o No conforme.
            Solo los certificados pueden ingresar al almacén de planta.
        </p>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card cert-kpi bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Pendientes</div>
                            <div class="kpi-value">{{ $stats['pendientes'] }}</div>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card cert-kpi bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Certificados</div>
                            <div class="kpi-value">{{ $stats['certificados'] }}</div>
                        </div>
                        <i class="fas fa-certificate fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card cert-kpi bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">No conformes</div>
                            <div class="kpi-value">{{ $stats['no_conformes'] ?? 0 }}</div>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card cert-kpi bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Lotes de planta</div>
                            <div class="kpi-value">{{ $stats['total_lotes'] }}</div>
                        </div>
                        <i class="fas fa-industry fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cert-info-strip">
        <i class="fas fa-info-circle mr-2"></i>
        Lotes con transformación terminada sin evaluación final. Certifique los aptos o marque <strong>No conforme</strong> si hay problemas de calidad — esos lotes no podrán ingresar al almacén de planta.
    </div>

    @php $filtros = $filtros ?? []; @endphp
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('certificaciones-planta.index') }}" class="form-row align-items-end">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Buscar</label>
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ $filtros['q'] ?? '' }}" placeholder="Código, lote, plantilla…">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Producto</label>
                    <input type="search" name="producto" class="form-control form-control-sm" value="{{ $filtros['producto'] ?? '' }}" placeholder="Nombre del producto…">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Resultado</label>
                    <select name="resultado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="certificado" @selected(($filtros['resultado'] ?? '') === 'certificado')>Certificado</option>
                        <option value="no_conforme" @selected(($filtros['resultado'] ?? '') === 'no_conforme')>No conforme</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="{{ $filtros['desde'] ?? '' }}">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="small text-muted mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $filtros['hasta'] ?? '' }}">
                </div>
                <div class="col-md-1 mb-2 mb-md-0">
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i></button>
                </div>
            </form>
            @if(array_filter($filtros))
                <p class="small text-muted mb-0 mt-2">
                    Filtros activos.
                    <a href="{{ route('certificaciones-planta.index') }}">Limpiar</a>
                </p>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card card-outline card-primary card-modulo-main elevation-1 shadow-sm">
                <x-modulo-index-header
                    titulo="Lotes por certificar"
                    icono="fa-clipboard-check"
                    :registros="$lotesPendientes->count()"
                />
                <div class="card-body">
                    @forelse($lotesPendientes as $lote)
                        <div class="lote-card p-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <h5 class="mb-1">{{ $lote->nombre }}</h5>
                                        <span class="badge badge-secondary cert-badge">{{ $lote->codigo_lote ?? '#'.$lote->loteproduccionpedidoid }}</span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-box text-primary mr-1"></i>{{ $lote->producto ?? 'Sin producto' }}
                                        <span class="mx-2">·</span>
                                        <i class="fas fa-project-diagram mr-1"></i>{{ $lote->plantillaTransformacion->nombre ?? 'Sin plantilla' }}
                                    </div>
                                    @can('lote_produccion.create')
                                        <form action="{{ route('procesamiento.certificar', $lote) }}" method="POST" class="mb-2">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="certificaciones-planta">
                                            <input type="hidden" name="razon" value="Certificado">
                                            <div class="form-row align-items-center">
                                                <div class="col-sm-8 mb-2 mb-sm-0">
                                                    <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Observación (opcional)">
                                                </div>
                                                <div class="col-sm-4">
                                                    <button class="btn btn-sm btn-success btn-block px-3 py-2" type="submit">
                                                        <i class="fas fa-stamp mr-1"></i>Certificar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        <form action="{{ route('procesamiento.certificar', $lote) }}" method="POST" class="mb-0" onsubmit="return confirm('¿Marcar este lote como No conforme? No podrá ingresar al almacén.');">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="certificaciones-planta">
                                            <input type="hidden" name="razon" value="No conforme">
                                            <div class="form-row">
                                                <div class="col-sm-6 mb-2">
                                                    <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Motivo obligatorio: calidad, empaque, etc." required>
                                                </div>
                                                <div class="col-sm-6 mb-2">
                                                    <input type="text" name="recomendaciones" class="form-control form-control-sm" placeholder="Recomendaciones para mejorar (opcional)">
                                                </div>
                                                <div class="col-sm-12">
                                                    <button class="btn btn-sm btn-outline-danger px-3 py-2" type="submit">
                                                        <i class="fas fa-times-circle mr-1"></i>No conforme
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    @else
                                        <a href="{{ route('procesamiento.show', $lote) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt mr-1"></i>Ver en procesamiento
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">No hay lotes de planta pendientes de certificación.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card card-outline card-primary card-modulo-main elevation-1 shadow-sm h-100">
                <x-modulo-index-header
                    titulo="Historial de evaluaciones"
                    icono="fa-history"
                    icon-class="text-primary"
                    :registros="$certificados->count()"
                />
                <div class="card-body cert-timeline p-0">
                    @forelse($certificados as $eval)
                        @php
                            $loteEval = $eval->loteProduccionPedido;
                            $codigoEval = $loteEval?->codigo_lote ?? ('LP-'.$eval->loteproduccionpedidoid);
                        @endphp
                        <div class="border-bottom px-3 py-3 cert-item"
                             role="button"
                             tabindex="0"
                             title="Ver detalle de la evaluación"
                             data-eval-id="{{ $eval->evaluacionfinalloteid }}"
                             data-eval-url="{{ route('certificaciones-planta.show', $eval) }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                @if($eval->esNoConforme())
                                    <span class="badge badge-warning">No conforme</span>
                                @else
                                    <span class="cert-badge badge badge-success">{{ $codigoEval }}</span>
                                @endif
                                <small class="text-muted">{{ $eval->fecha_evaluacion?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="font-weight-bold">{{ $loteEval->nombre ?? 'Lote #'.$eval->loteproduccionpedidoid }}</div>
                            <div class="small text-muted">
                                {{ $loteEval->producto ?? '—' }}
                                · {{ $loteEval->plantillaTransformacion->nombre ?? '—' }}
                            </div>
                            @if($eval->observaciones)
                                <p class="small mb-1 mt-2 text-secondary text-truncate">{{ Str::limit($eval->observaciones, 80) }}</p>
                            @endif
                            <div class="small text-primary mt-1">
                                <i class="fas fa-eye mr-1"></i>Ver detalle
                            </div>
                        </div>
                        <div id="eval-detail-{{ $eval->evaluacionfinalloteid }}" class="d-none eval-detail-template">
                            @include('certificaciones-planta.partials.detalle-contenido', ['eval' => $eval])
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay evaluaciones registradas en planta.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEvalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalEvalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEvalDetalleLabel">
                    <i class="fas fa-certificate text-primary mr-2"></i>Detalle de certificación
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-3" id="modalEvalDetalleBody">
                <div class="text-center text-muted py-4">Seleccione una evaluación de la lista.</div>
            </div>
            <div class="modal-footer justify-content-between px-4 py-3">
                <a href="#" id="modalEvalDetalleLink" class="btn btn-outline-primary px-3 py-2 d-none" style="border-radius:10px;font-weight:600;">
                    <i class="fas fa-external-link-alt mr-1"></i>Abrir en página completa
                </a>
                <button type="button" class="btn btn-secondary px-4 py-2" style="border-radius:10px;font-weight:600;" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modal = $('#modalEvalDetalle');
    const modalBody = document.getElementById('modalEvalDetalleBody');
    const modalLink = document.getElementById('modalEvalDetalleLink');

    function abrirDetalle(evalId, urlCompleta) {
        const tpl = document.getElementById('eval-detail-' + evalId);
        if (!tpl || !modalBody) return;
        modalBody.innerHTML = tpl.innerHTML;
        if (modalLink && urlCompleta) {
            modalLink.href = urlCompleta;
            modalLink.classList.remove('d-none');
        } else if (modalLink) {
            modalLink.classList.add('d-none');
        }
        modal.modal('show');
    }

    document.querySelectorAll('.cert-item[data-eval-id]').forEach(item => {
        const evalId = item.getAttribute('data-eval-id');
        const url = item.getAttribute('data-eval-url');
        const abrir = () => abrirDetalle(evalId, url);
        item.addEventListener('click', abrir);
        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                abrir();
            }
        });
    });
})();
</script>
@endpush
