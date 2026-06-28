<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var qrInstance = null;
    var canvas = document.getElementById('qrInventarioCanvas');
    var modal = document.getElementById('modalQrInventario');
    if (!canvas || !modal) return;

    document.querySelectorAll('.btn-qr-inventario').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var endpoint = btn.getAttribute('data-url');
            var producto = btn.getAttribute('data-producto') || 'Producto';
            document.getElementById('modalQrTitulo').textContent = producto;
            canvas.innerHTML = '<div class="text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

            if (window.jQuery) window.jQuery(modal).modal('show');

            fetch(endpoint, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    canvas.innerHTML = '';
                    qrInstance = new QRCode(canvas, {
                        text: data.url,
                        width: 240,
                        height: 240,
                        colorDark: '#1e4620',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.L
                    });
                    document.getElementById('modalQrUrl').textContent = data.url;
                    document.getElementById('modalQrAbrir').href = data.url;
                })
                .catch(function () {
                    canvas.innerHTML = '<p class="text-danger small mb-0">No se pudo generar el código QR.</p>';
                });
        });
    });

    if (window.jQuery) {
        window.jQuery(modal).on('hidden.bs.modal', function () {
            canvas.innerHTML = '';
            qrInstance = null;
        });
    }
});
</script>
