@push('scripts')
<script>
(function () {
    const input = document.getElementById('imagenMaquina');
    const preview = document.getElementById('previewImagen');
    const zona = document.getElementById('zonaPreviewFoto');
    const placeholder = document.getElementById('previewPlaceholder');
    const previewNueva = document.getElementById('previewNuevaImagen');

    if (!input) return;

    input.addEventListener('change', function () {
        const file = input.files?.[0];
        const img = previewNueva || preview;
        if (!file || !img) {
            if (!file && img) {
                img.classList.add('d-none');
                placeholder?.classList.remove('d-none');
                zona?.classList.remove('has-foto');
            }
            return;
        }

        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
        placeholder?.classList.add('d-none');
        zona?.classList.add('has-foto');
    });
})();
</script>
@endpush
