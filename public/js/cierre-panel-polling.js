/**
 * Actualiza el panel de cierre operativo sin recargar manualmente
 * (llegada al destino, firmas QR, cierre completado).
 */
(function () {
    const root = document.getElementById('cierre-panel-polling');
    if (!root) return;

    const pollingUrl = root.dataset.pollingUrl;
    const rutaId = root.dataset.pollingRuta;
    const asignacionId = root.dataset.pollingAsignacion;
    if (!pollingUrl || (!rutaId && !asignacionId)) return;

    const params = new URLSearchParams();
    if (rutaId) params.set('ruta', rutaId);
    if (asignacionId) params.set('asignacion', asignacionId);

    const inicial = {
        llegada: root.dataset.inicialLlegada === '1',
        puedeLlegada: root.dataset.inicialPuedeLlegada === '1',
        firmaRecepcion: root.dataset.inicialFirmaRecepcion === '1',
        completado: root.dataset.inicialCompletado === '1',
    };

    let activo = true;

    function debeRecargar(data) {
        if (!data) return false;
        if (data.completado && !inicial.completado) return true;
        if (data.llegada_confirmada && !inicial.llegada) return true;
        if (data.firma_recepcion && !inicial.firmaRecepcion) return true;
        if ((data.puede_confirmar_llegada || data.esperando_confirmacion) && !inicial.puedeLlegada) {
            return true;
        }
        return false;
    }

    function tick() {
        if (!activo) return;

        fetch(pollingUrl + '?' + params.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (debeRecargar(data)) {
                    activo = false;
                    window.location.reload();
                }
            })
            .catch(function () { /* silencioso */ })
            .finally(function () {
                if (activo) window.setTimeout(tick, 4000);
            });
    }

    window.setTimeout(tick, 3500);
})();
