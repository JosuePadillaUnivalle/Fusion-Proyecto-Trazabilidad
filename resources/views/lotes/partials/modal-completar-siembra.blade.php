@php
    $actPend = $actividad_siembra_pendiente ?? null;
    $resumenSiembra = $resumen_siembra_completar ?? [];
@endphp
<div class="modal fade" id="modalCompletarSiembra" tabindex="-1" role="dialog" aria-labelledby="modalCompletarSiembraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <form method="POST" action="{{ route('lotes.siembra.completar.store', $lote) }}" enctype="multipart/form-data" id="formCompletarSiembraModal">
                @csrf
                <input type="hidden" name="return" value="{{ route('lotes.trazabilidad', $lote) }}">

                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #0f766e, #14b8a6); color: #fff;">
                    <h5 class="modal-title font-weight-bold mb-0" id="modalCompletarSiembraLabel">
                        <i class="fas fa-seedling mr-2"></i>Completar siembra
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body px-4 py-4">
                    <p class="text-muted small mb-3">
                        Revise el resumen de la siembra programada. Luego suba una foto como evidencia para completar la actividad.
                    </p>

                    <div class="siembra-modal-resumen bg-light rounded p-3 mb-4">
                        <p class="small text-uppercase text-muted font-weight-bold mb-2">Resumen — solo lectura</p>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4 text-muted">Lote</dt>
                            <dd class="col-sm-8 font-weight-bold mb-2">{{ $lote->nombre }}</dd>

                            @if($lote->cultivo_etiqueta ?? $lote->cultivo?->nombre)
                                <dt class="col-sm-4 text-muted">Cultivo</dt>
                                <dd class="col-sm-8 font-weight-bold mb-2">{{ $lote->cultivo_etiqueta ?? $lote->cultivo?->nombre }}</dd>
                            @endif

                            @if($lote->ubicacion)
                                <dt class="col-sm-4 text-muted">Dónde sembrar</dt>
                                <dd class="col-sm-8 font-weight-bold mb-2">{{ $lote->ubicacion }}</dd>
                            @endif

                            <dt class="col-sm-4 text-muted">Superficie</dt>
                            <dd class="col-sm-8 font-weight-bold mb-2">{{ $lote->superficie_etiqueta ?? ($lote->superficie.' ha') }}</dd>

                            @if($lote->codigo_trazabilidad)
                                <dt class="col-sm-4 text-muted">Código trazabilidad</dt>
                                <dd class="col-sm-8 font-weight-bold mb-2">{{ $lote->codigo_trazabilidad }}</dd>
                            @endif

                            @if(!empty($actPend))
                                <dt class="col-sm-4 text-muted">Actividad</dt>
                                <dd class="col-sm-8 font-weight-bold mb-2">{{ $actPend['titulo'] ?? 'Siembra' }}</dd>
                                @if(!empty($actPend['responsable']))
                                    <dt class="col-sm-4 text-muted">Responsable</dt>
                                    <dd class="col-sm-8 font-weight-bold mb-2">{{ $actPend['responsable'] }}</dd>
                                @endif
                                @if(!empty($actPend['fechainicio']))
                                    <dt class="col-sm-4 text-muted">Fecha programada</dt>
                                    <dd class="col-sm-8 font-weight-bold mb-2">{{ $actPend['fechainicio'] }}</dd>
                                @endif
                            @endif
                        </dl>
                    </div>

                    @include('lotes.partials.siembra-resumen-extendido', [
                        'resumen_siembra_completar' => $resumenSiembra,
                        'mapaId' => 'siembraModalMapa',
                        'mapaInitEvent' => '#modalCompletarSiembra',
                    ])

                    <hr class="my-3">

                    <label class="font-weight-bold d-block mb-2">
                        <i class="fas fa-camera text-teal mr-1"></i> Foto de evidencia <span class="text-danger">*</span>
                    </label>
                    @include('partials.evidencia-foto-campo', [
                        'inputId' => 'siembraModalEvidenciaInput',
                        'btnId' => 'siembraModalEvidenciaBtn',
                        'previewWrapId' => 'siembraModalEvidenciaPreviewWrap',
                        'previewImgId' => 'siembraModalEvidenciaPreviewImg',
                        'previewNombreId' => 'siembraModalEvidenciaPreviewNombre',
                        'btnLabel' => 'Tomar o subir foto de la siembra',
                        'hint' => 'Muestre el surco sembrado, las semillas o el trabajo realizado.',
                    ])

                    <div class="form-group mt-3 mb-0">
                        <label class="font-weight-bold small text-muted" for="siembraModalObservaciones">Comentario opcional</label>
                        <textarea name="observaciones" id="siembraModalObservaciones" class="form-control" rows="2" maxlength="250"
                                  placeholder="Ej: Surcos 1-4, buen estado del suelo…">{{ old('observaciones') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold" id="btnEnviarSiembraModal">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar evidencia y completar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.AgrofusionEvidenciaFotoCampo) {
        window.AgrofusionEvidenciaFotoCampo(
            'siembraModalEvidenciaInput',
            'siembraModalEvidenciaBtn',
            'siembraModalEvidenciaPreviewWrap',
            'siembraModalEvidenciaPreviewImg',
            'siembraModalEvidenciaPreviewNombre'
        );
    }

    @if($errors->has('evidencia_foto') || $errors->has('observaciones'))
    if (window.jQuery) {
        window.jQuery('#modalCompletarSiembra').modal('show');
    }
    @endif
});
</script>
@endpush
@endonce
