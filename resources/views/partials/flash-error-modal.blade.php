@if (session('error_modal'))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.ModalConfirmar) {
        window.ModalConfirmar.aviso({
            titulo: @json(session('error_modal_titulo', 'No se puede eliminar')),
            mensaje: @json(session('error')),
            tono: 'warning',
        });
    }
});
</script>
@endpush
@endif
