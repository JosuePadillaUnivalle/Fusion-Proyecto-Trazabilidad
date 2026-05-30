<script>
(function () {
    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

    var pathKey = 'agrofusion_vista_' + window.location.pathname.replace(/\//g, '_');
    var params = new URLSearchParams(window.location.search);

    function applyVista(mode) {
        var btnCard = qs('#btnCardView');
        var btnTable = qs('#btnTableView');
        var cardView = qs('#cardView');
        var tableView = qs('#tableView');
        if (!btnCard || !btnTable || !cardView || !tableView) return;

        if (mode === 'cards') {
            btnCard.classList.add('active');
            btnTable.classList.remove('active');
            cardView.style.display = '';
            tableView.style.display = 'none';
        } else if (mode === 'table') {
            btnTable.classList.add('active');
            btnCard.classList.remove('active');
            tableView.style.display = '';
            cardView.style.display = 'none';
        }
    }

    var vistaUrl = params.get('vista');
    if (vistaUrl === 'cards' || vistaUrl === 'table') {
        applyVista(vistaUrl);
        try { localStorage.setItem(pathKey, vistaUrl); } catch (e) {}
    } else {
        try {
            var saved = localStorage.getItem(pathKey);
            if (saved === 'cards' || saved === 'table') applyVista(saved);
        } catch (e) {}
    }

    qsa('#btnCardView, #btnTableView').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var mode = btn.id === 'btnCardView' ? 'cards' : 'table';
            try { localStorage.setItem(pathKey, mode); } catch (e) {}
        });
    });

    if (params.get('filtros_abiertos') === '1') {
        qsa('.filtros-panel').forEach(function (panel) {
            panel.classList.add('show');
        });
    }

    qsa('.filtros-panel form, .filtros-trz').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (!qs('[name="filtros_abiertos"]', form)) {
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'filtros_abiertos';
                h.value = '1';
                form.appendChild(h);
            }
            var mode = null;
            try { mode = localStorage.getItem(pathKey); } catch (e) {}
            if ((mode === 'cards' || mode === 'table') && !qs('[name="vista"]', form)) {
                var v = document.createElement('input');
                v.type = 'hidden';
                v.name = 'vista';
                v.value = mode;
                form.appendChild(v);
            }
        });
    });

    qsa('.filtros-limpiar, .filtros-panel a.btn-outline-secondary').forEach(function (link) {
        if (!link.classList.contains('filtros-limpiar') && !/limpiar/i.test(link.textContent || '')) {
            return;
        }
        try {
            var url = new URL(link.href, window.location.origin);
            var hash = link.hash || (link.href.includes('#') ? link.href.split('#')[1] : '');
            url.searchParams.set('filtros_abiertos', '1');
            var vista = params.get('vista');
            if (vista === 'cards' || vista === 'table') {
                url.searchParams.set('vista', vista);
            } else {
                try {
                    var saved = localStorage.getItem(pathKey);
                    if (saved === 'cards' || saved === 'table') {
                        url.searchParams.set('vista', saved);
                    }
                } catch (e) {}
            }
            link.href = url.pathname + url.search + (hash ? '#' + hash.replace(/^#/, '') : '');
        } catch (e) {}
    });
})();
</script>
