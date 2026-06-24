/**
 * Canvas de firma táctil/ratón para cierre operativo AgroFusion.
 */
(function () {
    const pads = new Map();

    function initCanvas(canvas) {
        const key = canvas.dataset.firmaCanvas;
        if (!key || pads.has(key)) return;

        const ctx = canvas.getContext('2d');
        const ratio = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#1e3a22';
        ctx.lineWidth = 2.2;

        let drawing = false;
        let hasStroke = false;
        let last = null;

        function pos(e) {
            const r = canvas.getBoundingClientRect();
            const t = e.touches ? e.touches[0] : e;
            return { x: t.clientX - r.left, y: t.clientY - r.top };
        }

        function start(e) {
            e.preventDefault();
            drawing = true;
            last = pos(e);
        }

        function move(e) {
            if (!drawing) return;
            e.preventDefault();
            const p = pos(e);
            ctx.beginPath();
            ctx.moveTo(last.x, last.y);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            last = p;
            hasStroke = true;
        }

        function end() {
            drawing = false;
            last = null;
        }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', end);

        pads.set(key, {
            canvas,
            clear() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasStroke = false;
            },
            toDataUrl() {
                if (!hasStroke) return '';
                return canvas.toDataURL('image/png');
            },
        });
    }

    function csrf() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    document.querySelectorAll('[data-firma-canvas]').forEach(initCanvas);

    window.AgroFusionFirmaPads = pads;

    document.querySelectorAll('.btn-limpiar-firma').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const pad = pads.get(btn.dataset.target);
            if (pad) pad.clear();
        });
    });

    document.querySelectorAll('.btn-guardar-firma').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const pad = pads.get(btn.dataset.target);
            if (!pad) return;
            const data = pad.toDataUrl();
            if (!data) {
                alert('Dibuje su firma antes de guardar.');
                return;
            }
            btn.disabled = true;
            fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ imagen_firma: data }),
                credentials: 'same-origin',
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                .then(function (res) {
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        alert(res.j.mensaje || 'No se pudo guardar la firma.');
                        btn.disabled = false;
                    }
                })
                .catch(function () {
                    alert('Error de conexión al guardar la firma.');
                    btn.disabled = false;
                });
        });
    });
})();
