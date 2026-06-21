<div class="modal fade" id="modalFotoMaquina" tabindex="-1" role="dialog" aria-labelledby="modalFotoMaquinaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalFotoMaquinaLabel">Foto de la máquina</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="modalFotoMaquinaImg" src="" alt="" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$('#modalFotoMaquina').on('show.bs.modal', function (e) {
    const btn = $(e.relatedTarget);
    const src = btn.data('src');
    const nombre = btn.data('nombre') || 'Máquina';
    $('#modalFotoMaquinaImg').attr({ src: src, alt: nombre });
    $('#modalFotoMaquinaLabel').text(nombre);
});
</script>
@endpush
