@push('scripts')
<script>
(function () {
    const nombreInput = document.querySelector('input[name="nombre"]');
    const codigoInput = document.querySelector('input[name="codigo"]');
    if (!nombreInput || !codigoInput) return;

    let codigoEditadoManual = codigoInput.value.trim() !== '';

    codigoInput.addEventListener('input', function () {
        codigoEditadoManual = codigoInput.value.trim() !== '';
        codigoInput.dataset.auto = codigoEditadoManual ? '0' : '1';
    });

    function sugerirCodigo(nombre) {
        const palabras = nombre.trim().split(/\s+/).filter(Boolean);
        if (!palabras.length) return '';

        let base = palabras[0].replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 6);
        if (palabras.length > 1) {
            const resto = palabras.slice(1).join(' ').replace(/[^A-Za-z]/g, '').toUpperCase();
            const consonantes = resto.replace(/[AEIOUÁÉÍÓÚÜ]/g, '');
            base += (consonantes || resto).slice(0, 3);
        }
        return base.slice(0, 12);
    }

    function aplicarSugerencia() {
        if (codigoEditadoManual && codigoInput.dataset.auto !== '1') return;
        const sugerido = sugerirCodigo(nombreInput.value);
        if (!sugerido) return;
        codigoInput.value = sugerido;
        codigoInput.dataset.auto = '1';
    }

    nombreInput.addEventListener('input', aplicarSugerencia);
    nombreInput.addEventListener('blur', aplicarSugerencia);

    if (!codigoInput.value && nombreInput.value) {
        aplicarSugerencia();
    } else if (codigoInput.value) {
        codigoInput.dataset.auto = '0';
    }
})();
</script>
@endpush
