<div class="modal fade" id="modalVerEvidencia" tabindex="-1" role="dialog" aria-labelledby="modalVerEvidenciaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e3a5f, #2563eb); color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0" id="modalVerEvidenciaTitulo">
                    <i class="fas fa-image mr-2"></i>Evidencia fotográfica
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-4 text-center">
                <p class="text-muted small mb-3" id="modalVerEvidenciaSubtitulo"></p>
                <img id="modalVerEvidenciaImg" alt="Evidencia" class="img-fluid rounded border shadow-sm" style="max-height: 70vh;">
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3 justify-content-between">
                <a href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm" id="btnAbrirEvidenciaNueva">
                    <i class="fas fa-external-link-alt mr-1"></i> Abrir en pestaña nueva
                </a>
                <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.ModalVerEvidencia) return;

    const img = document.getElementById('modalVerEvidenciaImg');
    const subtitulo = document.getElementById('modalVerEvidenciaSubtitulo');
    const titulo = document.getElementById('modalVerEvidenciaTitulo');
    const link = document.getElementById('btnAbrirEvidenciaNueva');

    function abrir(url, etiqueta) {
        if (!url || !img) return;
        img.src = url;
        if (link) {
            link.href = url;
            link.style.display = '';
        }
        if (subtitulo) subtitulo.textContent = etiqueta || '';
        if (titulo) {
            titulo.innerHTML = '<i class="fas fa-image mr-2"></i>' + (etiqueta ? 'Evidencia: ' + etiqueta : 'Evidencia fotográfica');
        }
        if (window.jQuery) window.jQuery('#modalVerEvidencia').modal('show');
    }

    window.ModalVerEvidencia = { abrir: abrir };

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-ver-evidencia');
        if (!btn) return;
        e.preventDefault();
        abrir(btn.getAttribute('data-url'), btn.getAttribute('data-titulo') || '');
    });
})();
</script>
@endpush
@endonce
