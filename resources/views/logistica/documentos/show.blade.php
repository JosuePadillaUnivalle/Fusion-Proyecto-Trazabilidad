@extends('layouts.app')

@section('title', 'Detalle documento | AgroFusion')
@section('page_title', 'Detalle del documento')

@push('styles')
@include('logistica.documentos.partials.documentos-estilos')
@endpush

@php
    use App\Support\DocumentoEntregaCatalogo as DocCat;
    $esAutomatico = DocCat::esAutomatico($documento);
    $tipoEtiqueta = DocCat::etiquetaTipo($documento->tipo_documento);
    $nombreArchivo = $documento->metadata['original_name'] ?? basename($documento->archivo_path ?? '');
@endphp

@section('content')
<div class="log-doc-det">
    <div class="log-doc-det__toolbar">
        <a href="{{ route('logistica.documentos.index') }}" class="log-doc-det__back">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 mb-3" style="border-radius:10px">
            {{ session('error') }}
        </div>
    @endif

    @if(!empty($pdfPendiente) && !($puedePrevisualizar ?? false))
        <div class="alert alert-info border-0 mb-3" style="border-radius:10px">
            El PDF se está generando en segundo plano. Recargue esta página en unos segundos para ver el documento completo con firmas.
        </div>
    @endif

    <div class="log-doc-det-card">
        <div class="log-doc-det-card__head">
            <div class="log-doc-det-card__head-inner">
                <div class="log-doc-det-card__tipo">
                    <i class="fas fa-file-contract fa-xs"></i> {{ $tipoEtiqueta }}
                </div>
                <h1 class="log-doc-det-card__title">{{ $documento->titulo }}</h1>
                @if($esAutomatico)
                    <span class="log-doc-auto-tag"><i class="fas fa-bolt fa-xs"></i> Generado automáticamente</span>
                @endif
            </div>
        </div>

        <div class="log-doc-meta">
            <div class="log-doc-meta__item">
                <div class="log-doc-meta__lbl">Envío / pedido</div>
                <div class="log-doc-meta__val log-doc-meta__val--navy">{{ DocCat::etiquetaVinculo($documento) }}</div>
            </div>
            <div class="log-doc-meta__item">
                <div class="log-doc-meta__lbl">Generado por</div>
                <div class="log-doc-meta__val">{{ DocCat::etiquetaUsuario($documento->usuario) }}</div>
            </div>
            <div class="log-doc-meta__item">
                <div class="log-doc-meta__lbl">Fecha de emisión</div>
                <div class="log-doc-meta__val">{{ optional($documento->created_at)->format('d/m/Y H:i') }}</div>
            </div>
            <div class="log-doc-meta__item">
                <div class="log-doc-meta__lbl">Archivo</div>
                <div class="log-doc-meta__val log-doc-meta__val--accent text-break">{{ $nombreArchivo }}</div>
            </div>
        </div>

        <div class="log-doc-det-actions">
            @if($puedePrevisualizar ?? false)
                <button type="button" class="log-doc-btn log-doc-btn--primary" id="btn-toggle-preview">
                    <i class="fas fa-eye" id="toggle-preview-icon"></i>
                    <span id="toggle-preview-label">Ver documento</span>
                </button>
            @endif
            <a href="{{ route('logistica.documentos.download', $documento) }}" class="log-doc-btn log-doc-btn--secondary">
                <i class="fas fa-download"></i> Descargar PDF
            </a>
            @if(\App\Support\DocumentoEntregaCatalogo::puedeEditar($documento, auth()->user()))
                <a href="{{ route('logistica.documentos.edit', $documento) }}" class="log-doc-btn log-doc-btn--secondary">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
            @if(\App\Support\DocumentoEntregaCatalogo::puedeEliminar($documento, auth()->user()))
                <form action="{{ route('logistica.documentos.destroy', $documento) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este documento?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="log-doc-btn log-doc-btn--secondary text-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>

        @if($puedePrevisualizar ?? false)
        <div class="log-doc-preview log-doc-preview--hidden" id="vista-documento">
            <iframe
                id="preview-frame"
                class="log-doc-preview__frame"
                data-src="{{ route('logistica.documentos.preview', $documento) }}"
                title="Vista previa del documento PDF"
            ></iframe>
        </div>
        @else
        <div class="log-doc-preview__empty">
            <i class="far fa-file-pdf d-block mb-2" style="font-size:2rem;color:#2c5530;"></i>
            Vista previa no disponible. Use descargar para abrir el archivo.
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($puedePrevisualizar ?? false)
<script>
(function () {
    var btn = document.getElementById('btn-toggle-preview');
    var panel = document.getElementById('vista-documento');
    var frame = document.getElementById('preview-frame');
    var label = document.getElementById('toggle-preview-label');
    var icon = document.getElementById('toggle-preview-icon');
    if (!btn || !panel || !frame) return;

    var visible = false;

    btn.addEventListener('click', function () {
        visible = !visible;
        if (visible) {
            panel.classList.remove('log-doc-preview--hidden');
            btn.classList.add('is-active');
            if (!frame.getAttribute('src') && frame.dataset.src) {
                frame.setAttribute('src', frame.dataset.src);
            }
            label.textContent = 'Ocultar documento';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            setTimeout(function () {
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 80);
        } else {
            panel.classList.add('log-doc-preview--hidden');
            btn.classList.remove('is-active');
            label.textContent = 'Ver documento';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
})();
</script>
@endif
@endpush
