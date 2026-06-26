@once
@push('scripts')
<script>
(function () {
    var teclasPermitidas = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];

    function limpiarTelefono(valor) {
        return String(valor || '').replace(/[^\d+\s]/g, '').replace(/\s+/g, ' ');
    }

    function enlazarTelefonoBo(input) {
        if (!input || input.dataset.telefonoBoBound === '1') return;
        input.dataset.telefonoBoBound = '1';

        input.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            if (teclasPermitidas.indexOf(e.key) !== -1) return;
            if (e.key.length === 1 && !/[\d+]/.test(e.key) && e.key !== ' ') {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function () {
            var limpio = limpiarTelefono(this.value);
            if (limpio !== this.value) {
                this.value = limpio;
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var texto = limpiarTelefono((e.clipboardData || window.clipboardData).getData('text'));
            var inicio = this.selectionStart;
            var fin = this.selectionEnd;
            var actual = this.value;
            this.value = actual.slice(0, inicio) + texto + actual.slice(fin);
            this.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    function initTelefonosBo() {
        document.querySelectorAll('.js-telefono-bo').forEach(enlazarTelefonoBo);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTelefonosBo);
    } else {
        initTelefonosBo();
    }
})();
</script>
@endpush
@endonce
