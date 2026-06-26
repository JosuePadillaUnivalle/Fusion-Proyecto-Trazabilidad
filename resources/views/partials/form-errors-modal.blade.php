@if($errors->any())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ag-flash--error').forEach(function (el) { el.remove(); });
    if (window.ModalConfirmar && typeof ModalConfirmar.aviso === 'function') {
        ModalConfirmar.aviso({
            titulo: @json($titulo ?? 'Revisa el formulario'),
            mensaje: @json(implode("\n", $errors->all())),
            tono: 'warning',
        });
    }
});
</script>
@endpush
@endif
