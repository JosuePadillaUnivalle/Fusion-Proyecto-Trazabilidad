{{-- Modal reutilizable de confirmación (sustituye confirm() del navegador) --}}
<div class="modal fade" id="modalConfirmarAccion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #7f1d1d, #dc2626); color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0" id="modalConfirmarTitulo">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar acción
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-4">
                <p class="mb-0 text-dark" id="modalConfirmarMensaje" style="font-size: 1rem; line-height: 1.5;"></p>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger px-4 font-weight-bold" id="btnConfirmarAccion">
                    <i class="fas fa-check mr-1"></i>Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.ModalConfirmar) return;

    let formPendiente = null;

    window.ModalConfirmar = {
        abrir(form, titulo, mensaje) {
            formPendiente = form;
            const tituloEl = document.getElementById('modalConfirmarTitulo');
            const mensajeEl = document.getElementById('modalConfirmarMensaje');
            if (tituloEl) tituloEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>' + (titulo || 'Confirmar acción');
            if (mensajeEl) mensajeEl.textContent = mensaje || '¿Desea continuar?';
            if (window.jQuery) {
                window.jQuery('#modalConfirmarAccion').modal('show');
            }
        },
    };

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm-modal]');
        if (!btn) return;
        e.preventDefault();
        const form = btn.closest('form');
        if (!form) return;
        window.ModalConfirmar.abrir(
            form,
            btn.getAttribute('data-confirm-title') || 'Confirmar acción',
            btn.getAttribute('data-confirm-message') || '¿Desea continuar?'
        );
    });

    document.getElementById('btnConfirmarAccion')?.addEventListener('click', function () {
        if (formPendiente) formPendiente.submit();
        formPendiente = null;
        if (window.jQuery) window.jQuery('#modalConfirmarAccion').modal('hide');
    });

    document.getElementById('modalConfirmarAccion')?.addEventListener('hidden.bs.modal', function () {
        formPendiente = null;
    });
})();
</script>
@endpush
@endonce
