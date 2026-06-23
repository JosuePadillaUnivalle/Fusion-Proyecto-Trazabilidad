@if(session('lote_creado_modal'))
@php
    $modalLote = session('lote_creado_modal');
@endphp
<div class="modal fade" id="modalLoteCreado" tabindex="-1" role="dialog" aria-labelledby="modalLoteCreadoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title" id="modalLoteCreadoTitulo">
                    <i class="fas fa-check-circle mr-2"></i>Lote creado con éxito
                </h5>
            </div>
            <div class="modal-body py-4">
                <p class="mb-2">
                    Se creó correctamente el lote
                    <strong>«{{ $modalLote['nombre'] ?? '—' }}»</strong>.
                </p>
                <p class="text-muted small mb-0">
                    Si desea registrar actividades o ver la trazabilidad del lote, puede ir ahora o cerrar este aviso.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="{{ $modalLote['trazabilidad_url'] ?? route('lotes.index') }}" class="btn btn-success">
                    <i class="fas fa-route mr-1"></i> Ir ahora
                </a>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(function () {
    $('#modalLoteCreado').modal('show');
});
</script>
@endpush
@endif
