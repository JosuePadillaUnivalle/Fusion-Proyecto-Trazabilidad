@extends('layouts.app')

@section('title', 'Certificaciones')
@section('page_title', 'Certificaciones')

@section('content')
<style>
    .cert-page-hero {
        background: linear-gradient(135deg, #14532d 0%, #166534 45%, #22c55e 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 6px 24px rgba(20, 83, 45, .18);
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
    .lote-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.06); border-color: #86efac; }
    .lote-card.selected { border-color: #16a34a; background: #f0fdf4; }
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
    .cert-toolbar .btn {
        padding: .45rem .9rem;
        font-weight: 600;
        border-radius: 10px;
    }
    .cert-info-strip {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: .85rem 1rem;
        font-size: .88rem;
        color: #166534;
        margin-bottom: 1.25rem;
    }
    .cert-list-filter {
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .cert-list-filter .input-group-text {
        background: #fff;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .cert-list-filter .form-control {
        border-color: #e2e8f0;
        font-size: .88rem;
    }
    .cert-list-filter .form-control:focus {
        border-color: #86efac;
        box-shadow: 0 0 0 .12rem rgba(34, 197, 94, .12);
    }
</style>

<div class="container-fluid">
    <div class="cert-page-hero">
        <h2><i class="fas fa-certificate mr-2"></i>Certificaciones de lotes de campo</h2>
        <p>
            Evalúe lotes <strong>cosechados</strong> como Certificado o No conforme.
            Solo los certificados pueden enviarse al almacén. Use No conforme ante daños, plagas o calidad deficiente.
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
                        <i class="fas fa-seedling fa-2x opacity-50"></i>
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
                            <div class="kpi-label">Cosechados</div>
                            <div class="kpi-value">{{ $stats['total_lotes'] }}</div>
                        </div>
                        <i class="fas fa-layer-group fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cert-info-strip">
        <i class="fas fa-info-circle mr-2"></i>
        Lotes <strong>cosechados</strong> sin evaluación. Certifique los aptos o marque <strong>No conforme</strong> si hay daños, plagas o problemas de calidad — esos lotes no podrán ingresar al almacén.
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card card-outline card-success card-modulo-main elevation-1 shadow-sm">
                <x-modulo-index-header
                    titulo="Lotes por certificar"
                    icono="fa-clipboard-check"
                    :registros="$lotesPendientes->count()"
                >
                    <x-slot:tools>
                        <div class="cert-toolbar d-flex flex-wrap">
                            @can('certificaciones.create')
                                @if($lotesPendientes->isNotEmpty())
                                    <button type="button" class="btn btn-outline-secondary btn-sm mr-1 mb-1" id="btnSeleccionarTodos">
                                        <i class="far fa-check-square mr-1"></i>Seleccionar todos
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm mb-1" id="btnCertificarSeleccion" disabled>
                                        <i class="fas fa-certificate mr-1"></i>Certificar selección
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </x-slot:tools>
                </x-modulo-index-header>
                @if($lotesPendientes->isNotEmpty())
                    <div class="cert-list-filter px-3 py-3">
                        <label class="small text-muted mb-1 d-block" for="filtroLotesCertificar">Buscar lote</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="search"
                                   class="form-control"
                                   id="filtroLotesCertificar"
                                   placeholder="Nombre, #lote, cultivo o estado…"
                                   autocomplete="off">
                        </div>
                        <small class="text-muted d-block mt-1 cert-filter-meta" id="filtroLotesCertificarMeta" hidden></small>
                    </div>
                @endif
                <div class="card-body">
                    @can('certificaciones.create')
                        @if($lotesPendientes->isNotEmpty())
                            <form action="{{ route('certificaciones.store-bulk') }}" method="POST" id="formCertMasivo" class="mb-3 p-3 bg-light rounded">
                                @csrf
                                <input type="hidden" name="modo" value="todos">
                                <div class="d-flex flex-wrap align-items-end">
                                    <div class="flex-grow-1 mr-2 mb-2" style="min-width:200px;">
                                        <label class="small text-muted mb-1">Observación para certificación masiva (opcional)</label>
                                        <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Ej. Certificación de calidad — lote apto para venta local">
                                    </div>
                                    <button type="submit" class="btn btn-success mb-2 px-3 py-2" onclick="return confirm('¿Certificar todos los lotes pendientes?');">
                                        <i class="fas fa-bolt mr-1"></i>Certificar todos ({{ $lotesPendientes->count() }})
                                    </button>
                                </div>
                            </form>

                            <form action="{{ route('certificaciones.store-bulk') }}" method="POST" id="formCertSeleccion">
                                @csrf
                                <input type="hidden" name="modo" value="seleccion">
                                <input type="hidden" name="observaciones" id="obsSeleccionHidden" value="">
                            </form>
                        @endif
                    @endcan

                    @forelse($lotesPendientes as $lote)
                        <div class="lote-card p-3 mb-3"
                             data-lote-id="{{ $lote->loteid }}"
                             data-search="{{ strtolower(trim($lote->nombre.' #'.$lote->loteid.' '.($lote->cultivo_etiqueta ?? '').' '.($lote->estadoTipo->nombre ?? ''))) }}">
                            <div class="d-flex align-items-start">
                                @can('certificaciones.create')
                                    <div class="custom-control custom-checkbox mr-3 pt-1">
                                        <input type="checkbox" class="custom-control-input lote-check" id="lote-{{ $lote->loteid }}" value="{{ $lote->loteid }}">
                                        <label class="custom-control-label" for="lote-{{ $lote->loteid }}"></label>
                                    </div>
                                @endcan
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <h5 class="mb-1">{{ $lote->nombre }}</h5>
                                        <span class="badge badge-secondary">#{{ $lote->loteid }}</span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-leaf text-success mr-1"></i>{{ $lote->cultivo_etiqueta ?? 'Sin semilla' }}
                                        <span class="mx-2">·</span>
                                        <i class="fas fa-flag mr-1"></i>{{ $lote->estadoTipo->nombre ?? 'Sin estado' }}
                                    </div>
                                    @can('certificaciones.create')
                                        <form action="{{ route('certificaciones.store') }}" method="POST" class="mb-2">
                                            @csrf
                                            <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                                            <input type="hidden" name="resultado" value="Certificado">
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
                                        <form action="{{ route('certificaciones.store') }}" method="POST" class="mb-0" onsubmit="return confirm('¿Marcar este lote como No conforme? No podrá enviarse al almacén.');">
                                            @csrf
                                            <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                                            <input type="hidden" name="resultado" value="No conforme">
                                            <div class="form-row">
                                                <div class="col-sm-6 mb-2">
                                                    <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Motivo obligatorio: daños, plagas, calidad…" required>
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
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">Todos los lotes de campo elegibles ya están certificados.</p>
                        </div>
                    @endforelse
                    <div id="filtroLotesCertificarVacio" class="text-center text-muted py-5 d-none">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p class="mb-0">Ningún lote coincide con la búsqueda.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card card-outline card-success card-modulo-main elevation-1 shadow-sm h-100">
                <x-modulo-index-header
                    titulo="Evaluaciones registradas"
                    icono="fa-history"
                    icon-class="text-primary"
                    :registros="$certificados->count()"
                />
                @if($certificados->isNotEmpty())
                    <div class="cert-list-filter px-3 py-3">
                        <label class="small text-muted mb-1 d-block" for="filtroEvaluacionesRegistradas">Buscar evaluación</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="search"
                                   class="form-control"
                                   id="filtroEvaluacionesRegistradas"
                                   placeholder="Código, lote, cultivo u observación…"
                                   autocomplete="off">
                        </div>
                        <small class="text-muted d-block mt-1 cert-filter-meta" id="filtroEvaluacionesRegistradasMeta" hidden></small>
                    </div>
                @endif
                <div class="card-body cert-timeline p-0">
                    @forelse($certificados as $cert)
                        <div class="border-bottom px-3 py-3 cert-item"
                             role="button"
                             tabindex="0"
                             title="Ver detalle de la evaluación"
                             data-cert-id="{{ $cert->certificacionid }}"
                             data-cert-url="{{ route('certificaciones.show', $cert) }}"
                             data-search="{{ strtolower(trim(($cert->codigo_certificado ?? '').' '.$cert->loteid.' '.($cert->lote->nombre ?? '').' '.($cert->lote->cultivo->nombre ?? '').' '.($cert->observaciones ?? '').' '.($cert->esNoConforme() ? 'no conforme' : 'certificado'))) }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                @if($cert->esNoConforme())
                                    <span class="badge badge-warning">No conforme</span>
                                @else
                                    <span class="cert-badge badge badge-success">{{ $cert->codigo_certificado }}</span>
                                @endif
                                <small class="text-muted">{{ $cert->fecha_certificacion?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="font-weight-bold">{{ $cert->lote->nombre ?? 'Lote #'.$cert->loteid }}</div>
                            <div class="small text-muted">Lote #{{ $cert->loteid }} · {{ $cert->lote->cultivo->nombre ?? '—' }}</div>
                            @if($cert->observaciones)
                                <p class="small mb-1 mt-2 text-secondary text-truncate">{{ Str::limit($cert->observaciones, 80) }}</p>
                            @endif
                            @if($cert->recomendaciones)
                                <p class="small mb-0 text-muted"><strong>Recomendaciones:</strong> {{ Str::limit($cert->recomendaciones, 100) }}</p>
                            @endif
                            <div class="small text-primary mt-1">
                                <i class="fas fa-eye mr-1"></i>Ver detalle
                            </div>
                        </div>
                        <div id="cert-detail-{{ $cert->certificacionid }}" class="d-none cert-detail-template">
                            @include('certificaciones.partials.detalle-contenido', ['cert' => $cert])
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay evaluaciones registradas.</p>
                        </div>
                    @endforelse
                    <div id="filtroEvaluacionesRegistradasVacio" class="text-center text-muted py-5 px-3 d-none">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p class="mb-0">Ninguna evaluación coincide con la búsqueda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCertDetalle" tabindex="-1" role="dialog" aria-labelledby="modalCertDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCertDetalleLabel">
                    <i class="fas fa-certificate text-success mr-2"></i>Detalle del certificado
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-3" id="modalCertDetalleBody">
                <div class="text-center text-muted py-4">Seleccione un certificado de la lista.</div>
            </div>
            <div class="modal-footer justify-content-between px-4 py-3">
                <a href="#" id="modalCertDetalleLink" class="btn btn-outline-primary px-3 py-2 d-none" style="border-radius:10px;font-weight:600;">
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
    function normalizarTexto(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function filtrarLista(config) {
        const input = document.getElementById(config.inputId);
        if (!input) return;

        const items = document.querySelectorAll(config.itemSelector);
        const emptyEl = config.emptyId ? document.getElementById(config.emptyId) : null;
        const metaEl = config.metaId ? document.getElementById(config.metaId) : null;

        function aplicar() {
            const q = normalizarTexto(input.value.trim());
            let visibles = 0;

            items.forEach(function (item) {
                const coincide = !q || normalizarTexto(item.getAttribute('data-search')).includes(q);
                item.classList.toggle('d-none', !coincide);
                if (coincide) visibles++;
            });

            if (emptyEl) {
                emptyEl.classList.toggle('d-none', visibles > 0 || items.length === 0);
            }

            if (metaEl) {
                if (q && items.length) {
                    metaEl.hidden = false;
                    metaEl.textContent = visibles + ' de ' + items.length + ' coinciden';
                } else {
                    metaEl.hidden = true;
                }
            }

            if (typeof config.onChange === 'function') {
                config.onChange(visibles);
            }
        }

        input.addEventListener('input', aplicar);
    }

    filtrarLista({
        inputId: 'filtroLotesCertificar',
        itemSelector: '.lote-card[data-search]',
        emptyId: 'filtroLotesCertificarVacio',
        metaId: 'filtroLotesCertificarMeta',
        onChange: function () {
            if (typeof actualizarSeleccion === 'function') {
                actualizarSeleccion();
            }
        }
    });

    filtrarLista({
        inputId: 'filtroEvaluacionesRegistradas',
        itemSelector: '.cert-item[data-search]',
        emptyId: 'filtroEvaluacionesRegistradasVacio',
        metaId: 'filtroEvaluacionesRegistradasMeta'
    });

    const modal = $('#modalCertDetalle');
    const modalBody = document.getElementById('modalCertDetalleBody');
    const modalLink = document.getElementById('modalCertDetalleLink');

    function abrirDetalleCertificado(certId, urlCompleta) {
        const tpl = document.getElementById('cert-detail-' + certId);
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

    document.querySelectorAll('.cert-item[data-cert-id]').forEach(item => {
        const certId = item.getAttribute('data-cert-id');
        const url = item.getAttribute('data-cert-url');
        const abrir = () => abrirDetalleCertificado(certId, url);
        item.addEventListener('click', abrir);
        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                abrir();
            }
        });
    });

    const checks = document.querySelectorAll('.lote-check');
    const btnSel = document.getElementById('btnCertificarSeleccion');
    const btnTodos = document.getElementById('btnSeleccionarTodos');
    const formSel = document.getElementById('formCertSeleccion');

    function checksVisibles() {
        return document.querySelectorAll('.lote-card:not(.d-none) .lote-check');
    }

    if (!checks.length || !formSel) return;

    function actualizarSeleccion() {
        const marcados = document.querySelectorAll('.lote-check:checked');
        if (btnSel) btnSel.disabled = marcados.length === 0;
        document.querySelectorAll('.lote-card').forEach(card => {
            const id = card.getAttribute('data-lote-id');
            const chk = document.getElementById('lote-' + id);
            card.classList.toggle('selected', chk && chk.checked);
        });
    }

    checks.forEach(chk => chk.addEventListener('change', actualizarSeleccion));

    if (btnTodos) {
        let todosMarcados = false;
        btnTodos.addEventListener('click', () => {
            todosMarcados = !todosMarcados;
            checksVisibles().forEach(c => { c.checked = todosMarcados; });
            btnTodos.innerHTML = todosMarcados
                ? '<i class="far fa-square mr-1"></i>Desmarcar todos'
                : '<i class="far fa-check-square mr-1"></i>Seleccionar todos';
            actualizarSeleccion();
        });
    }

    if (btnSel) {
        btnSel.addEventListener('click', () => {
            const marcados = [...document.querySelectorAll('.lote-check:checked')].map(c => c.value);
            if (!marcados.length) return;
            const obs = prompt('Observación opcional para los lotes seleccionados:', '') ?? '';
            formSel.querySelectorAll('input[name="lotes[]"]').forEach(el => el.remove());
            marcados.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'lotes[]';
                input.value = id;
                formSel.appendChild(input);
            });
            const obsHidden = document.getElementById('obsSeleccionHidden');
            if (obsHidden) obsHidden.value = obs;
            if (confirm('¿Certificar ' + marcados.length + ' lote(s) seleccionado(s)?')) {
                formSel.submit();
            }
        });
    }
})();
</script>
@endpush
