<div class="modal fade" id="modalCompletarEvidencia" tabindex="-1" role="dialog" aria-labelledby="modalCompletarEvidenciaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <form id="formCompletarEvidencia" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e4620, #2c5530); color: #fff;">
                    <h5 class="modal-title font-weight-bold mb-0" id="modalCompletarEvidenciaTitulo">
                        <i class="fas fa-camera mr-2"></i>Completar actividad
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="text-muted small mb-3" id="modalCompletarEvidenciaMensaje">
                        Suba una foto que demuestre que la actividad ya fue realizada (riego aplicado, control de plagas, etc.). Sin foto no se puede marcar como completada.
                    </p>
                    <p class="font-weight-bold mb-2" id="modalCompletarEvidenciaActividad"></p>
                    <input type="file" name="evidencia_foto" id="inputEvidenciaFoto" accept="image/jpeg,image/jpg,image/png,image/webp" required class="d-none">
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-outline-success" id="btnSeleccionarEvidencia">
                            <i class="fas fa-upload mr-1"></i> Seleccionar foto
                        </button>
                    </div>
                    <div id="evidenciaPreviewWrap" class="text-center" style="display: none;">
                        <img id="evidenciaPreviewImg" alt="Vista previa" class="img-fluid rounded border" style="max-height: 220px;">
                        <p class="small text-muted mt-2 mb-0" id="evidenciaPreviewNombre"></p>
                    </div>
                    @error('evidencia_foto')
                        <div class="alert alert-danger small mt-2 mb-0">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold" id="btnEnviarCompletar" disabled>
                        <i class="fas fa-check mr-1"></i> Completar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.ModalCompletarEvidencia) return;

    const form = document.getElementById('formCompletarEvidencia');
    const input = document.getElementById('inputEvidenciaFoto');
    const btnSeleccionar = document.getElementById('btnSeleccionarEvidencia');
    const btnEnviar = document.getElementById('btnEnviarCompletar');
    const previewWrap = document.getElementById('evidenciaPreviewWrap');
    const previewImg = document.getElementById('evidenciaPreviewImg');
    const previewNombre = document.getElementById('evidenciaPreviewNombre');
    const tituloActividad = document.getElementById('modalCompletarEvidenciaActividad');

    function setScrollTo(value) {
        if (!form) return;
        var el = form.querySelector('input[name="scroll_to"]');
        if (!value) {
            if (el) el.remove();
            return;
        }
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = 'scroll_to';
            form.appendChild(el);
        }
        el.value = value;
    }

    function resetForm() {
        if (form) form.reset();
        if (input) input.value = '';
        if (previewWrap) previewWrap.style.display = 'none';
        if (previewImg) previewImg.removeAttribute('src');
        if (previewNombre) previewNombre.textContent = '';
        if (btnEnviar) btnEnviar.disabled = true;
        setScrollTo('');
    }

    function abrir(action, titulo, lote, scrollTo) {
        if (!form) return;
        form.action = action;
        if (tituloActividad) {
            tituloActividad.textContent = titulo + (lote ? ' — Lote: ' + lote : '');
        }
        resetForm();
        setScrollTo(scrollTo || '');
        if (window.jQuery) window.jQuery('#modalCompletarEvidencia').modal('show');
    }

    window.ModalCompletarEvidencia = { abrir: abrir };

    btnSeleccionar?.addEventListener('click', function () {
        input?.click();
    });

    input?.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) {
            resetForm();
            return;
        }
        if (!file.type.startsWith('image/')) {
            resetForm();
            if (window.ModalConfirmar) {
                window.ModalConfirmar.aviso({ titulo: 'Archivo no válido', mensaje: 'Seleccione una imagen (JPG, PNG o WebP).' });
            } else {
                alert('Seleccione una imagen (JPG, PNG o WebP).');
            }
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            if (previewImg) previewImg.src = e.target.result;
            if (previewNombre) previewNombre.textContent = file.name;
            if (previewWrap) previewWrap.style.display = 'block';
            if (btnEnviar) btnEnviar.disabled = false;
        };
        reader.readAsDataURL(file);
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-completar-evidencia');
        if (!btn) return;
        e.preventDefault();
        abrir(
            btn.getAttribute('data-action') || btn.closest('form')?.action || '',
            btn.getAttribute('data-titulo') || 'Actividad',
            btn.getAttribute('data-lote') || '',
            btn.getAttribute('data-scroll-to') || ''
        );
    });

    document.getElementById('modalCompletarEvidencia')?.addEventListener('hidden.bs.modal', resetForm);

    @if($errors->has('evidencia_foto'))
    if (window.jQuery) {
        window.jQuery(function () {
            window.jQuery('#modalCompletarEvidencia').modal('show');
        });
    }
    @endif
})();
</script>
@endpush
@endonce
