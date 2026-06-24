/**
 * Polling de estado de firmas QR en panel de cierre operativo.
 */
(function () {
    const root = document.getElementById('cierre-qr-recepcion');
    if (!root) return;

    const pollingUrl = root.dataset.pollingUrl;
    const rutaId = root.dataset.pollingRuta;
    const asignacionId = root.dataset.pollingAsignacion;
    const icon = document.getElementById('cierre-qr-polling-icon');
    const text = document.getElementById('cierre-qr-polling-text');

    if (!pollingUrl || (!rutaId && !asignacionId)) return;

    const params = new URLSearchParams();
    if (rutaId) params.set('ruta', rutaId);
    if (asignacionId) params.set('asignacion', asignacionId);

    let activo = true;

    function tick() {
        if (!activo) return;
        if (icon) icon.style.display = 'inline-block';

        fetch(pollingUrl + '?' + params.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;
                if (data.firma_recepcion || data.completado) {
                    activo = false;
                    if (text) text.textContent = 'Firma recibida. Actualizando…';
                    window.location.reload();
                }
            })
            .catch(function () { /* silencioso en LAN */ })
            .finally(function () {
                if (icon) icon.style.display = 'none';
                if (activo) window.setTimeout(tick, 4000);
            });
    }

    window.setTimeout(tick, 3000);
})();
