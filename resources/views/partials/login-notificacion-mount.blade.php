@once
@push('scripts')
<script>
(function () {
    var descartarUrl = @json(route('login-notificaciones.descartar'));
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';

    function modalLoginNotifVisible() {
        return !!document.querySelector('.login-notif-modal-root.show, .login-notif-modal-root.in');
    }

    function limpiarEstadoLoginNotif(forzar) {
        if (!forzar && modalLoginNotifVisible()) {
            return;
        }
        document.body.classList.remove('login-notif-modal-open', 'modal-open');
        document.querySelectorAll('.login-notif-scrim').forEach(function (el) {
            el.classList.remove('is-visible');
        });
        if (window.jQuery) {
            window.jQuery('.login-notif-modal-root').modal('hide');
        }
    }

    function mountLoginNotifNodes() {
        document.querySelectorAll('.login-notif-scrim, .login-notif-modal-root').forEach(function (el) {
            if (el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
        });
    }

    function parseClaves(modalEl) {
        var raw = modalEl.getAttribute('data-login-notif-claves') || '[]';
        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function descartarNotificacion(modalEl) {
        var alcance = modalEl.getAttribute('data-login-notif-alcance');
        var claves = parseClaves(modalEl);
        if (!alcance || claves.length === 0 || !csrfToken) {
            return;
        }

        fetch(descartarUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ alcance: alcance, claves: claves }),
            credentials: 'same-origin'
        }).catch(function () { /* noop */ });
    }

    function enlazarDescarteModales() {
        document.querySelectorAll('.login-notif-modal-root[data-login-notif-alcance]').forEach(function (modalEl) {
            if (modalEl.dataset.loginNotifBound === '1') {
                return;
            }
            modalEl.dataset.loginNotifBound = '1';

            if (window.jQuery) {
                window.jQuery(modalEl).on('hidden.bs.modal', function () {
                    descartarNotificacion(modalEl);
                });
            }
        });
    }

    function init() {
        mountLoginNotifNodes();
        limpiarEstadoLoginNotif(false);
        enlazarDescarteModales();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('pageshow', function (e) {
        if (e.persisted || (window.location.pathname || '').indexOf('/login') !== -1) {
            limpiarEstadoLoginNotif(true);
        }
    });
})();
</script>
@endpush
@endonce
