{{-- Script compartido: filtros client-side para tablas del módulo envíos --}}
<script>
(function (config) {
    const rows = document.querySelectorAll(config.rowSelector);
    const searchEl = document.getElementById(config.searchId);
    const contadorEl = document.getElementById(config.contadorId);
    const sinResultadosEl = document.getElementById(config.sinResultadosId);
    const total = rows.length;

    const filtrosSelect = (config.filterIds || []).map(id => document.getElementById(id)).filter(Boolean);

    function aplicarFiltros() {
        const q = (searchEl?.value || '').trim().toLowerCase();
        const valores = filtrosSelect.map(sel => (sel.value || '').trim().toLowerCase());
        let visibles = 0;
        let n = 0;

        rows.forEach(tr => {
            const texto = (tr.dataset.texto || '').toLowerCase();
            const matchQ = !q || texto.includes(q);
            const matchSelects = valores.every((val, i) => {
                if (!val) return true;
                const key = config.dataKeys[i] || '';
                return (tr.dataset[key] || '').toLowerCase() === val;
            });
            const show = matchQ && matchSelects;
            tr.style.display = show ? '' : 'none';
            if (show) {
                visibles++;
                const numCell = tr.querySelector('.col-num');
                if (numCell) numCell.textContent = ++n;
            }
        });

        if (contadorEl) {
            contadorEl.textContent = visibles === total
                ? `Mostrando ${total} registro(s)`
                : `Mostrando ${visibles} de ${total} registro(s)`;
        }
        if (sinResultadosEl) {
            sinResultadosEl.style.display = visibles === 0 && total > 0 ? '' : 'none';
        }
    }

    searchEl?.addEventListener('input', aplicarFiltros);
    filtrosSelect.forEach(sel => sel.addEventListener('change', aplicarFiltros));

    document.getElementById(config.limpiarId)?.addEventListener('click', function () {
        if (searchEl) searchEl.value = '';
        filtrosSelect.forEach(sel => { sel.value = ''; });
        aplicarFiltros();
    });

    aplicarFiltros();
})(@json($filtrosConfig));
</script>
