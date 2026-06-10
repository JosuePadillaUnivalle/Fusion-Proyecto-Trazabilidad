@extends('layouts.app')

@section('title', 'Certificaciones')
@section('page_title', 'Certificaciones')

@section('content')
@php
    $esPlanta = ($ambito ?? 'planta') === 'planta';
@endphp
<style>
    .cert-kpi {
        border-radius: 12px;
        border: none;
        color: #fff;
        min-height: 100px;
    }
    .cert-kpi .kpi-value { font-size: 2rem; font-weight: 700; line-height: 1; }
    .lote-card {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        transition: box-shadow .2s ease, border-color .2s ease;
    }
    .lote-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.08); border-color: #28a745; }
    .lote-card.selected { border-color: #28a745; background: #f6fff8; }
    .cert-badge {
        font-family: ui-monospace, monospace;
        letter-spacing: .03em;
    }
    .cert-timeline { max-height: 520px; overflow-y: auto; }
    .cert-item { transition: background-color .15s ease; }
    .cert-item:hover { background-color: #f8f9fa; }
    .cert-ambito-tabs .btn { font-weight: 600; }
</style>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 cert-ambito-tabs">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('certificaciones.index', ['ambito' => 'planta']) }}"
               class="btn {{ $esPlanta ? 'btn-success' : 'btn-outline-success' }}">
                <i class="fas fa-industry mr-1"></i>Lotes de planta
            </a>
            <a href="{{ route('certificaciones.index', ['ambito' => 'campo']) }}"
               class="btn {{ ! $esPlanta ? 'btn-success' : 'btn-outline-success' }}">
                <i class="fas fa-seedling mr-1"></i>Lotes de campo
            </a>
        </div>
        <small class="text-muted">
            {{ $esPlanta ? 'Industrialización en planta' : 'Parcelas agrícolas cosechadas' }}
        </small>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card cert-kpi bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small opacity-75">Pendientes</div>
                            <div class="kpi-value">{{ $stats['pendientes'] }}</div>
                        </div>
                        <i class="fas {{ $esPlanta ? 'fa-flask' : 'fa-seedling' }} fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card cert-kpi bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small opacity-75">Certificados</div>
                            <div class="kpi-value">{{ $stats['certificados'] }}</div>
                        </div>
                        <i class="fas fa-certificate fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card cert-kpi bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-uppercase small opacity-75">{{ $esPlanta ? 'Lotes de planta' : 'Lotes elegibles' }}</div>
                            <div class="kpi-value">{{ $stats['total_lotes'] }}</div>
                        </div>
                        <i class="fas fa-layer-group fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border mb-4">
        <i class="fas fa-info-circle text-info mr-2"></i>
        @if($esPlanta)
            Los contadores reflejan solo <strong>lotes de producción en planta</strong>.
            Aparecen como pendientes cuando terminaron la transformación y aún no tienen evaluación final.
            La certificación se registra en <strong>Procesamiento de Lote</strong> (Certificado o No conforme).
        @else
            Lotes de <strong>campo</strong> en estado cosechado o listo para cosecha, sin certificado emitido.
            Puedes certificar uno a uno o usar selección múltiple.
        @endif
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card card-outline card-success card-modulo-main elevation-1 shadow-sm">
                <x-modulo-index-header
                    titulo="Lotes por certificar"
                    icono="fa-clipboard-check"
                    :registros="$lotesPendientes->count()"
                >
                    @if(! $esPlanta)
                    <x-slot:tools>
                        @can('certificaciones.create')
                            @if($lotesPendientes->isNotEmpty())
                                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" id="btnSeleccionarTodos">
                                    <i class="far fa-check-square mr-1"></i>Seleccionar todos
                                </button>
                                <button type="button" class="btn btn-success btn-sm ml-1" id="btnCertificarSeleccion" disabled>
                                    <i class="fas fa-certificate mr-1"></i>Certificar selección
                                </button>
                            @endif
                        @endcan
                    </x-slot:tools>
                    @endif
                </x-modulo-index-header>
                <div class="card-body">
                    @if(! $esPlanta)
                        @can('certificaciones.create')
                            @if($lotesPendientes->isNotEmpty())
                                <form action="{{ route('certificaciones.store-bulk') }}" method="POST" id="formCertMasivo" class="mb-3 p-3 bg-light rounded">
                                    @csrf
                                    <input type="hidden" name="modo" value="todos">
                                    <div class="d-flex flex-wrap align-items-end gap-2">
                                        <div class="flex-grow-1 mr-2 mb-2">
                                            <label class="small text-muted mb-1">Observación para certificación masiva (opcional)</label>
                                            <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Ej. Certificación de calidad — lote apto para venta local">
                                        </div>
                                        <button type="submit" class="btn btn-success mb-2" onclick="return confirm('¿Certificar todos los lotes pendientes?');">
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
                    @endif

                    @forelse($lotesPendientes as $lote)
                        @if($esPlanta)
                        <div class="lote-card p-3 mb-3">
                            <div class="d-flex justify-content-between flex-wrap align-items-start">
                                <div>
                                    <h5 class="mb-1">{{ $lote->nombre }}</h5>
                                    <div class="small text-muted mb-2">
                                        <code>{{ $lote->codigo_lote }}</code>
                                        @if($lote->producto)
                                            <span class="mx-1">·</span>{{ $lote->producto }}
                                        @endif
                                        @if($lote->plantillaTransformacion)
                                            <span class="mx-1">·</span>{{ $lote->plantillaTransformacion->nombre }}
                                        @endif
                                    </div>
                                    <span class="badge badge-info">Transformación completa</span>
                                </div>
                                <a href="{{ route('procesamiento.show', $lote) }}#certificacion" class="btn btn-sm btn-outline-success font-weight-bold">
                                    <i class="fas fa-stamp mr-1"></i>Certificar
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="lote-card p-3 mb-3" data-lote-id="{{ $lote->loteid }}">
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
                                        <i class="fas fa-leaf text-success mr-1"></i>{{ $lote->cultivo->nombre ?? 'Sin cultivo' }}
                                        <span class="mx-2">·</span>
                                        <i class="fas fa-flag mr-1"></i>{{ $lote->estadoTipo->nombre ?? 'Sin estado' }}
                                    </div>
                                    @can('certificaciones.create')
                                        <form action="{{ route('certificaciones.store') }}" method="POST" class="form-row align-items-center">
                                            @csrf
                                            <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                                            <div class="col-sm-8 mb-2 mb-sm-0">
                                                <input type="text" name="observaciones" class="form-control form-control-sm" placeholder="Observación (opcional)">
                                            </div>
                                            <div class="col-sm-4">
                                                <button class="btn btn-sm btn-outline-success btn-block" type="submit">
                                                    <i class="fas fa-stamp mr-1"></i>Certificar
                                                </button>
                                            </div>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">
                                @if($esPlanta)
                                    No hay lotes de planta pendientes de evaluación final.
                                @else
                                    Todos los lotes de campo elegibles ya están certificados.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card card-outline card-success card-modulo-main elevation-1 shadow-sm h-100">
                <x-modulo-index-header
                    titulo="{{ $esPlanta ? 'Evaluaciones registradas' : 'Certificados emitidos' }}"
                    icono="fa-history"
                    icon-class="text-primary"
                    :registros="$certificados->count()"
                />
                <div class="card-body cert-timeline p-0">
                    @forelse($certificados as $cert)
                        @if($esPlanta)
                        @php $lotePlanta = $cert->loteProduccionPedido; @endphp
                        <div class="border-bottom px-3 py-3 cert-item">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge badge-{{ $cert->razon === 'Certificado' ? 'success' : 'warning' }}">{{ $cert->razon }}</span>
                                <small class="text-muted">{{ $cert->fecha_evaluacion?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="font-weight-bold">{{ $lotePlanta?->nombre ?? 'Lote de planta' }}</div>
                            <div class="small text-muted">
                                {{ $lotePlanta?->codigo_lote ?? '—' }}
                                @if($lotePlanta?->producto) · {{ $lotePlanta->producto }} @endif
                            </div>
                            @if($cert->observaciones)
                                <p class="small mb-1 mt-2 text-secondary">{{ Str::limit($cert->observaciones, 80) }}</p>
                            @endif
                            @if($lotePlanta)
                            <a href="{{ route('procesamiento.show', $lotePlanta) }}" class="small text-primary d-inline-block mt-1">
                                <i class="fas fa-external-link-alt mr-1"></i>Ver lote
                            </a>
                            @endif
                        </div>
                        @else
                        <div class="border-bottom px-3 py-3 cert-item"
                             role="button"
                             tabindex="0"
                             title="Ver detalle del certificado"
                             data-cert-id="{{ $cert->certificacionid }}"
                             data-cert-url="{{ route('certificaciones.show', $cert) }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="cert-badge badge badge-success">{{ $cert->codigo_certificado }}</span>
                                <small class="text-muted">{{ $cert->fecha_certificacion?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="font-weight-bold">{{ $cert->lote->nombre ?? 'Lote #'.$cert->loteid }}</div>
                            <div class="small text-muted">Lote #{{ $cert->loteid }} · {{ $cert->lote->cultivo->nombre ?? '—' }}</div>
                            @if($cert->observaciones)
                                <p class="small mb-1 mt-2 text-secondary text-truncate">{{ Str::limit($cert->observaciones, 80) }}</p>
                            @endif
                            <div class="small text-primary mt-1">
                                <i class="fas fa-eye mr-1"></i>Ver detalle
                            </div>
                        </div>
                        <div id="cert-detail-{{ $cert->certificacionid }}" class="d-none cert-detail-template">
                            @include('certificaciones.partials.detalle-contenido', ['cert' => $cert])
                        </div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-5 px-3">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay {{ $esPlanta ? 'evaluaciones' : 'certificados' }} registrados.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@if(! $esPlanta)
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
            <div class="modal-body" id="modalCertDetalleBody">
                <div class="text-center text-muted py-4">Seleccione un certificado de la lista.</div>
            </div>
            <div class="modal-footer justify-content-between">
                <a href="#" id="modalCertDetalleLink" class="btn btn-outline-primary btn-sm d-none">
                    <i class="fas fa-external-link-alt mr-1"></i>Abrir en página completa
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if(! $esPlanta)
@push('scripts')
<script>
(function () {
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
            checks.forEach(c => { c.checked = todosMarcados; });
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
@endif
