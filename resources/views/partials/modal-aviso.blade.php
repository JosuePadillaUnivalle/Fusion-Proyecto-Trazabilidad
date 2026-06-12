{{-- Modal de aviso (sustituye alert() del navegador) --}}
<div class="modal fade" id="modalAviso" tabindex="-1" role="dialog" aria-labelledby="modalAvisoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 py-3 px-4" id="modalAvisoHeader" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0" id="modalAvisoTitulo">
                    <i class="fas fa-info-circle mr-2"></i>Aviso
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-4">
                <p class="mb-0 text-dark" id="modalAvisoMensaje" style="font-size: 1rem; line-height: 1.5;"></p>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-primary px-4 font-weight-bold" data-dismiss="modal">
                    <i class="fas fa-check mr-1"></i>Entendido
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.ModalAviso) return;

    window.ModalAviso = {
        mostrar(mensaje, titulo, tono) {
            const tituloEl = document.getElementById('modalAvisoTitulo');
            const mensajeEl = document.getElementById('modalAvisoMensaje');
            const headerEl = document.getElementById('modalAvisoHeader');
            const iconos = { info: 'info-circle', warning: 'exclamation-triangle', danger: 'times-circle', success: 'check-circle' };
            const fondos = {
                info: 'linear-gradient(135deg, #1e3a8a, #2563eb)',
                warning: 'linear-gradient(135deg, #b45309, #f59e0b)',
                danger: 'linear-gradient(135deg, #7f1d1d, #dc2626)',
                success: 'linear-gradient(135deg, #1e4620, #2c5530)',
            };
            const t = ['info', 'warning', 'danger', 'success'].includes(tono) ? tono : 'warning';
            if (headerEl) headerEl.style.background = fondos[t];
            if (tituloEl) {
                tituloEl.innerHTML = '<i class="fas fa-' + iconos[t] + ' mr-2"></i>' + (titulo || 'Aviso');
            }
            if (mensajeEl) mensajeEl.textContent = mensaje || '';
            if (window.jQuery) window.jQuery('#modalAviso').modal('show');
        },
    };
})();
</script>
@endpush
@endonce
