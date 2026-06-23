@once
@push('scripts')
<script>
(function () {
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

    function init() {
        mountLoginNotifNodes();
        limpiarEstadoLoginNotif(false);
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
