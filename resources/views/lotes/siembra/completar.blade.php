@extends('layouts.app')

@section('title', 'Completar Siembra | AgroFusion')
@section('page_title', 'Completar Siembra')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.trazabilidad', $lote) }}">{{ $lote->nombre }}</a></li>
    <li class="breadcrumb-item active">Completar siembra</li>
@endsection

@push('styles')
<style>
    .siembra-resumen-page { max-width: 720px; margin: 0 auto; }
    .siembra-resumen-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .08);
        overflow: hidden;
    }
    .siembra-resumen-card__head {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: #fff;
        padding: 1.5rem 1.75rem;
    }
    .siembra-resumen-card__head h2 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0 0 .35rem;
    }
    .siembra-resumen-dl { margin: 0; }
    .siembra-resumen-dl dt {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .siembra-resumen-dl dd {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    .siembra-resumen-hint {
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        font-size: .9rem;
        color: #134e4a;
        margin-bottom: 1.25rem;
    }
    .btn-siembra-enviar {
        background: linear-gradient(135deg, #15803d, #22c55e);
        border: none;
        color: #fff;
        font-weight: 700;
        padding: .7rem 1.6rem;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(34, 197, 94, .3);
    }
    .btn-siembra-enviar:hover { color: #fff; filter: brightness(1.05); }
</style>
@endpush

@section('content')
<div class="siembra-resumen-page">
    <div class="card siembra-resumen-card">
        <div class="siembra-resumen-card__head">
            <h2><i class="fas fa-seedling mr-2"></i>Completar siembra</h2>
            <p class="mb-0 small opacity-90">Revise el resumen y envíe una foto del trabajo realizado en campo.</p>
        </div>

        <form action="{{ route('lotes.siembra.completar.store', $lote) }}" method="POST" enctype="multipart/form-data" id="formCompletarSiembra">
            @csrf
            <input type="hidden" name="return" value="{{ $returnUrl }}">

            <div class="card-body p-4">
                <dl class="siembra-resumen-dl">
                    <dt>Lote</dt>
                    <dd>{{ $lote->nombre }}</dd>

                    @if($lote->cultivo_etiqueta ?? $lote->cultivo?->nombre)
                        <dt>Cultivo</dt>
                        <dd>{{ $lote->cultivo_etiqueta ?? $lote->cultivo?->nombre }}</dd>
                    @endif

                    <dt>Superficie</dt>
                    <dd>{{ $lote->superficie_etiqueta ?? ($lote->superficie.' ha') }}</dd>

                    @if($lote->codigo_trazabilidad)
                        <dt>Código de trazabilidad</dt>
                        <dd>{{ $lote->codigo_trazabilidad }}</dd>
                    @endif

                    @if(!empty($actividadPendiente))
                        <dt>Actividad asignada</dt>
                        <dd>{{ $actividadPendiente->descripcion ?: ($tipoSiembra->nombre ?? 'Siembra') }}</dd>
                        @if($actividadPendiente->fechainicio)
                            <dt>Fecha programada</dt>
                            <dd>{{ \Carbon\Carbon::parse($actividadPendiente->fechainicio)->format('d/m/Y') }}</dd>
                        @endif
                    @endif
                </dl>

                @include('lotes.partials.siembra-resumen-extendido', [
                    'resumen_siembra_completar' => $resumenSiembraCompletar ?? [],
                    'mapaId' => 'siembraPageMapa',
                ])

                @if(empty($resumenSiembraCompletar['proyeccion']) && empty($resumenSiembraCompletar['insumo']['cantidad_total']))
                    <div class="siembra-resumen-hint">
                        <i class="fas fa-camera mr-1"></i>
                        Al enviar la evidencia, la siembra quedará registrada y el lote pasará a <strong>en crecimiento</strong>.
                    </div>
                @endif

                <label class="font-weight-bold d-block mb-2">
                    <i class="fas fa-camera text-teal mr-1"></i> Foto de evidencia <span class="text-danger">*</span>
                </label>
                @include('partials.evidencia-foto-campo', [
                    'btnLabel' => 'Tomar o subir foto de la siembra',
                    'hint' => 'Muestre el surco sembrado, las semillas o el trabajo realizado.',
                ])

                <div class="form-group mt-3 mb-0">
                    <label class="font-weight-bold small text-muted" for="observaciones">Comentario opcional</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2" maxlength="250"
                              placeholder="Ej: Surcos 1-4, buen estado del suelo…">{{ old('observaciones') }}</textarea>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap px-4 py-3" style="gap:.5rem;">
                <a href="{{ $returnUrl }}" class="btn btn-light">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                <button type="submit" class="btn btn-siembra-enviar" id="btnEnviarSiembra">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar evidencia y completar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.AgrofusionEvidenciaFotoCampo) {
        window.AgrofusionEvidenciaFotoCampo(
            'evidenciaFotoInput',
            'evidenciaFotoBtn',
            'evidenciaFotoPreviewWrap',
            'evidenciaFotoPreviewImg',
            'evidenciaFotoPreviewNombre'
        );
    }
});
</script>
@endpush
