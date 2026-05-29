@extends('layouts.app')

@section('title', 'Certificaciones | AgroNexus')
@section('page_title', 'Certificaciones')

@push('styles')
<style>
    /* ── Stat boxes ── */
    .cert-stat {
        border-radius: 14px;
        padding: 22px 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
        transition: transform .18s, box-shadow .18s;
    }
    .cert-stat:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.18) !important; }
    .cert-stat .stat-icon {
        position: absolute; right: 18px; top: 50%;
        transform: translateY(-50%);
        font-size: 2.4rem; opacity: .22;
    }
    .cert-stat .stat-num { font-size: 2.2rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
    .cert-stat .stat-lbl { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; opacity: .88; }
    .cert-stat.pendientes { background: linear-gradient(135deg,#b45309,#d97706); }
    .cert-stat.certificados { background: linear-gradient(135deg,#059669,#10b981); }
    .cert-stat.total { background: linear-gradient(135deg,#0369a1,#0ea5e9); }

    /* ── Lote card ── */
    .lote-cert-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 10px;
        transition: border-color .15s, box-shadow .15s;
    }
    .lote-cert-card:hover { border-color: #10b981; box-shadow: 0 4px 14px rgba(16,185,129,.1); }
    .lote-cert-card.selected { border-color: #10b981; background: #f0fdf4; }

    .lote-badge-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 50%;
        background: #f1f5f9; color: #475569;
        font-size: .72rem; font-weight: 700; flex-shrink: 0;
    }
    .lote-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 20px; font-size: .72rem; font-weight: 600;
    }
    .lote-tag.cultivo { background: #d1fae5; color: #065f46; }
    .lote-tag.estado  { background: #e0f2fe; color: #0369a1; }

    /* ── Cert card emitted ── */
    .cert-emitted-card {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 14px 16px; margin-bottom: 10px;
    }
    .cert-code-badge {
        display: inline-block; background: #d1fae5; color: #065f46;
        font-size: .7rem; font-weight: 700; padding: 2px 8px; border-radius: 20px;
        letter-spacing: .04em;
    }

    /* ── Batch bar ── */
    .batch-bar {
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 14px 18px;
        display: flex; align-items: center; gap: 12px;
        flex-wrap: wrap; margin-bottom: 16px;
    }

    /* ── Section header ── */
    .section-title {
        font-size: .92rem; font-weight: 700; color: #0f172a;
        display: flex; align-items: center; gap: 8px; margin-bottom: 14px;
    }
    .section-title i { color: #10b981; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- Stat boxes --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="cert-stat pendientes">
            <div class="stat-num">{{ $stats['pendientes'] }}</div>
            <div class="stat-lbl">Pendientes</div>
            <div class="stat-icon"><i class="fas fa-seedling"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="cert-stat certificados">
            <div class="stat-num">{{ $stats['certificados'] }}</div>
            <div class="stat-lbl">Certificados</div>
            <div class="stat-icon"><i class="fas fa-certificate"></i></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="cert-stat total">
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-lbl">Lotes en sistema</div>
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center gap-2" style="font-size:.84rem;">
    <i class="fas fa-info-circle mr-2"></i>
    Certifica lotes trazables antes del despacho. Podés certificar uno a uno o seleccionar varios y usar <strong class="ml-1">Certificar selección</strong>.
</div>

<div class="row">

    {{-- Left: Lotes por certificar --}}
    <div class="col-lg-7 mb-4">
        <div class="section-title">
            <i class="fas fa-clipboard-check"></i>
            Lotes por certificar
            <span class="badge badge-warning ml-1" style="font-size:.72rem;">{{ $stats['pendientes'] }}</span>
        </div>

        @can('certificaciones.create')
        {{-- Batch bar --}}
        <div class="batch-bar">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="selectAll">
                <label class="custom-control-label font-weight-600" for="selectAll" style="font-size:.83rem;">Seleccionar todos</label>
            </div>

            <button type="button" id="btnCertSel" class="btn btn-sm btn-success ml-2" disabled>
                <i class="fas fa-certificate mr-1"></i>Certificar selección
            </button>

            @if($stats['pendientes'] > 0)
            <form id="formBatch" action="{{ route('certificaciones.batch') }}" method="POST" class="d-inline ml-auto">
                @csrf
                <input type="text" id="batchObs" name="observaciones" class="form-control form-control-sm d-inline-block"
                    style="width:220px;" placeholder="Ej. Certificación de calidad…">
                <div id="batchLotesHidden"></div>
                <button type="submit" class="btn btn-sm btn-warning ml-1" id="btnCertTodos">
                    <i class="fas fa-bolt mr-1"></i>Certificar todos ({{ $stats['pendientes'] }})
                </button>
            </form>
            @endif
        </div>
        @endcan

        @forelse($lotesPendientes as $lote)
        <div class="lote-cert-card" data-loteid="{{ $lote->loteid }}">
            <div class="d-flex align-items-start gap-3">
                @can('certificaciones.create')
                <div class="custom-control custom-checkbox mt-1 flex-shrink-0">
                    <input type="checkbox" class="custom-control-input lote-check" id="lote{{ $lote->loteid }}" value="{{ $lote->loteid }}">
                    <label class="custom-control-label" for="lote{{ $lote->loteid }}"></label>
                </div>
                @endcan

                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="font-weight-700" style="font-size:.9rem;">{{ $lote->nombre }}</span>
                        @if($lote->cultivo)
                            <span class="lote-tag cultivo"><i class="fas fa-leaf"></i>{{ $lote->cultivo->nombre }}</span>
                        @endif
                        @if($lote->estadoTipo)
                            <span class="lote-tag estado">{{ $lote->estadoTipo->nombre }}</span>
                        @endif
                    </div>

                    @can('certificaciones.create')
                    <form action="{{ route('certificaciones.store') }}" method="POST" class="d-flex gap-2 mt-2">
                        @csrf
                        <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
                        <input type="text" name="observaciones" class="form-control form-control-sm"
                            placeholder="Observación (opcional)" style="flex:1;">
                        <button type="submit" class="btn btn-sm btn-success flex-shrink-0">
                            <i class="fas fa-certificate mr-1"></i>Certificar
                        </button>
                    </form>
                    @endcan
                </div>

                <span class="lote-badge-num flex-shrink-0">#{{ $lote->loteid }}</span>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="fas fa-check-double fa-2x mb-3 d-block text-success"></i>
            <p class="mb-0">Todos los lotes están certificados.</p>
        </div>
        @endforelse
    </div>

    {{-- Right: Certificados emitidos --}}
    <div class="col-lg-5 mb-4">
        <div class="section-title">
            <i class="fas fa-award"></i>
            Certificados emitidos
        </div>

        @forelse($certificados as $cert)
        <div class="cert-emitted-card">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="cert-code-badge">{{ $cert->codigo_certificado }}</span>
                <small class="text-muted">{{ $cert->fecha_certificacion?->format('d/m/Y H:i') }}</small>
            </div>
            <div class="font-weight-700" style="font-size:.87rem; color:#0f172a;">
                {{ $cert->lote->nombre ?? 'N/D' }}
            </div>
            <div class="text-muted" style="font-size:.77rem;">
                Lote #{{ $cert->loteid }}
                @if($cert->lote?->cultivo)
                    · {{ $cert->lote->cultivo->nombre }}
                @endif
            </div>
            @if($cert->observaciones)
            <div class="mt-1" style="font-size:.78rem; color:#475569;">{{ $cert->observaciones }}</div>
            @endif
            @if(Route::has('certificaciones.show'))
            <a href="{{ route('certificaciones.show', $cert) }}" class="text-success" style="font-size:.77rem;">
                <i class="fas fa-eye mr-1"></i>Ver detalle
            </a>
            @endif
        </div>
        @empty
        <div class="text-center text-muted py-4">
            <i class="fas fa-certificate fa-2x mb-2 d-block"></i>
            <p class="mb-0 small">Aún no hay certificados emitidos.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Select all toggle
    $('#selectAll').on('change', function () {
        $('.lote-check').prop('checked', this.checked).trigger('change');
    });

    // Individual check → update batch button
    $(document).on('change', '.lote-check', function () {
        var checked = $('.lote-check:checked').length;
        $('#btnCertSel').prop('disabled', checked === 0).text(
            checked > 0 ? 'Certificar selección (' + checked + ')' : 'Certificar selección'
        );
        $('#selectAll').prop('indeterminate', checked > 0 && checked < $('.lote-check').length);
        $('#selectAll').prop('checked', checked === $('.lote-check').length && checked > 0);

        // Highlight card
        $(this).closest('.lote-cert-card').toggleClass('selected', this.checked);
    });

    // Certificar selección → build form with selected IDs and submit batch
    $('#btnCertSel').on('click', function () {
        var ids = $('.lote-check:checked').map(function () { return this.value; }).get();
        if (!ids.length) return;
        var $hidden = $('#batchLotesHidden').empty();
        ids.forEach(function (id) {
            $hidden.append('<input type="hidden" name="lotes[]" value="' + id + '">');
        });
        $('#formBatch').submit();
    });

    // Certificar todos → add all pending lote IDs to form
    $('#btnCertTodos').on('click', function (e) {
        e.preventDefault();
        var $hidden = $('#batchLotesHidden').empty();
        $('.lote-check').each(function () {
            $hidden.append('<input type="hidden" name="lotes[]" value="' + this.value + '">');
        });
        $('#formBatch').submit();
    });
});
</script>
@endpush
