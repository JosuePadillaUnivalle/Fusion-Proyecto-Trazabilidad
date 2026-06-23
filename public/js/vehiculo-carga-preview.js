import { mountVehCaja3d, disposeVehCaja3d } from './veh-caja-3d.js';

(function (window) {
    'use strict';

    const PREVIEW_URL = window.VehiculoCargaPreviewUrl
        || (document.querySelector('meta[name="app-url"]')?.content || '').replace(/\/$/, '')
        + '/catalogo-selector/vehiculos';

    let onCloseCallback = null;

    function ensurePreviewModalOnBody() {
        const modal = document.getElementById('modalVehiculoCargaPreview');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        return modal;
    }

    function fmtNum(n, dec) {
        if (n === null || n === undefined || isNaN(n)) return '—';
        return Number(n).toLocaleString('es-BO', {
            minimumFractionDigits: dec ?? 0,
            maximumFractionDigits: dec ?? 1,
        });
    }

    function barClass(pct) {
        if (pct >= 100) return 'is-danger';
        if (pct >= 85) return 'is-warning';
        return '';
    }

    function updateDimsPanel(data) {
        const dims = data.dimensiones || {};
        const largo = parseFloat(dims.largo_m) || 0;
        const ancho = parseFloat(dims.ancho_m) || 0;
        const alto = parseFloat(dims.alto_m) || 0;
        const leyendaCaja = document.getElementById('vehCargaPreviewLeyendaCaja');
        const dimLargo = document.getElementById('vehCargaPreviewDimLargo');
        const dimAncho = document.getElementById('vehCargaPreviewDimAncho');
        const dimAlto = document.getElementById('vehCargaPreviewDimAlto');
        const volText = document.getElementById('vehCargaPreviewVolText');

        if (leyendaCaja) {
            leyendaCaja.textContent = 'Caja (' + fmtNum(largo, 2) + ' × ' + fmtNum(ancho, 2) + ' × ' + fmtNum(alto, 2) + ' m)';
        }
        if (dimLargo) dimLargo.textContent = fmtNum(largo, 2) + ' m';
        if (dimAncho) dimAncho.textContent = fmtNum(ancho, 2) + ' m';
        if (dimAlto) dimAlto.textContent = fmtNum(alto, 2) + ' m';
        if (volText && dims.volumen_m3 != null) {
            const factor = (parseFloat(dims.factor_volumen_util) || 0.85) * 100;
            volText.textContent = 'Volumen bruto ' + fmtNum(dims.volumen_m3, 1) + ' m³ · útil '
                + fmtNum(dims.m3_util ?? data.m3_util, 1) + ' m³ (' + fmtNum(factor, 0) + '%)';
            volText.style.display = '';
        } else if (volText) {
            volText.style.display = 'none';
        }
    }

    function updateBars(data) {
        const pctPeso = data.porcentaje_peso ?? 0;
        const pctVol = data.porcentaje_volumen ?? 0;
        const fillPct = Math.max(pctVol, pctPeso);

        const barPeso = document.getElementById('vehCargaPreviewBarPeso');
        const barVol = document.getElementById('vehCargaPreviewBarVol');
        const txtPeso = document.getElementById('vehCargaPreviewTxtPeso');
        const txtVol = document.getElementById('vehCargaPreviewTxtVol');
        const pctPesoEl = document.getElementById('vehCargaPreviewPctPeso');
        const pctVolEl = document.getElementById('vehCargaPreviewPctVol');
        const okEl = document.getElementById('vehCargaPreviewOk');
        const errEl = document.getElementById('vehCargaPreviewError');
        const badge = document.getElementById('vehCargaPreviewTipoBadge');

        if (badge) {
            badge.textContent = data.vehiculo?.tipo_nombre || data.vehiculo?.tipo_codigo || 'Vehículo';
        }
        if (barPeso) {
            barPeso.style.width = Math.min(100, pctPeso) + '%';
            barPeso.className = 'veh-carga-preview-bar__fill veh-carga-preview-bar__fill--peso ' + barClass(pctPeso);
        }
        if (barVol) {
            barVol.style.width = Math.min(100, pctVol) + '%';
            barVol.className = 'veh-carga-preview-bar__fill veh-carga-preview-bar__fill--vol ' + barClass(pctVol);
        }
        if (pctPesoEl) pctPesoEl.textContent = fmtNum(pctPeso, 1) + '%';
        if (pctVolEl) pctVolEl.textContent = pctVol > 0 ? fmtNum(pctVol, 1) + '%' : '—';
        if (txtPeso) {
            txtPeso.textContent = fmtNum(data.carga_peso_kg, 1) + ' kg de '
                + fmtNum(data.capacidad_kg, 0) + ' kg máx.';
        }
        if (txtVol) {
            txtVol.textContent = data.carga_volumen_m3 != null
                ? fmtNum(data.carga_volumen_m3, 2) + ' m³ de ' + fmtNum(data.m3_util, 1) + ' m³ útiles'
                : 'Sin dimensiones de empaque — volumen estimado por densidad.';
        }
        if (okEl) {
            okEl.style.display = data.ok && (data.carga_peso_kg > 0) ? 'block' : 'none';
            okEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + (data.recomendacion || '');
        }
        if (errEl) {
            const showErr = !data.ok || (data.carga_peso_kg > 0 && (pctPeso >= 100 || pctVol >= 100));
            errEl.style.display = showErr ? 'block' : 'none';
            errEl.innerHTML = showErr
                ? '<i class="fas fa-exclamation-triangle mr-1"></i>' + (
                    data.ok
                        ? 'La carga excede la capacidad. Reduzca cantidades o programe otro envío.'
                        : (data.mensaje || data.recomendacion || 'La carga no cabe en este vehículo.')
                )
                : '';
        }

        return fillPct;
    }

    function loadPreviewData(vehiculoId, carga) {
        const params = new URLSearchParams();
        params.set('peso_kg', String(carga.peso_kg || 0));
        if (carga.volumen_m3 != null && carga.volumen_m3 !== '') {
            params.set('volumen_m3', String(carga.volumen_m3));
        }
        const url = (window.VehiculoCargaPreviewUrl || PREVIEW_URL)
            + '/' + encodeURIComponent(vehiculoId) + '/preview-carga?' + params.toString();
        return fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        });
    }

    function renderScene(host, data) {
        disposeVehCaja3d(host);
        const dims = data.dimensiones || {};
        const pctVol = data.porcentaje_volumen ?? 0;
        const pctPeso = data.porcentaje_peso ?? 0;
        try {
            mountVehCaja3d(host, {
                largo: dims.largo_m,
                ancho: dims.ancho_m,
                alto: dims.alto_m,
                tipo: data.vehiculo?.tipo_codigo,
                nombre: data.vehiculo?.tipo_nombre || data.vehiculo?.placa,
                fillPct: Math.max(pctVol, pctPeso),
            });
        } catch (err) {
            host.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-danger px-3 text-center small">No se pudo iniciar la vista 3D.</div>';
        }
    }

    function applyPreviewData(host, subtitulo, data) {
        if (subtitulo && data.vehiculo?.placa) {
            subtitulo.textContent = data.vehiculo.placa + ' · ' + (data.vehiculo.tipo_nombre || '');
        }
        updateDimsPanel(data);
        updateBars(data);
        renderScene(host, data);
    }

    function waitModalShown($modal) {
        return new Promise(function (resolve) {
            if ($modal.hasClass('show')) {
                resolve();
                return;
            }
            $modal.one('shown.bs.modal', resolve);
            setTimeout(resolve, 400);
        });
    }

    window.VehiculoCargaPreview = {
        previewUrlBase: PREVIEW_URL,

        open(vehiculoId, label, carga, opts) {
            const modal = ensurePreviewModalOnBody();
            const host = document.getElementById('veh-carga-preview-3d');
            const subtitulo = document.getElementById('vehCargaPreviewSubtitulo');
            if (!modal || !host || !window.jQuery) return;

            onCloseCallback = opts && typeof opts.onClose === 'function' ? opts.onClose : null;
            carga = carga || { peso_kg: 0, volumen_m3: null };

            if (subtitulo) {
                subtitulo.textContent = label || 'Vehículo seleccionado';
            }

            host.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Cargando modelado…</div>';

            const $modal = window.jQuery(modal);

            $modal.one('hidden.bs.modal', function () {
                disposeVehCaja3d(host);
                if (onCloseCallback) {
                    const cb = onCloseCallback;
                    onCloseCallback = null;
                    setTimeout(cb, 80);
                }
            });

            $modal.modal('show');

            Promise.all([
                loadPreviewData(vehiculoId, carga),
                waitModalShown($modal),
            ])
                .then(function (results) {
                    applyPreviewData(host, subtitulo, results[0]);
                })
                .catch(function () {
                    host.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-danger px-3 text-center"><span><i class="fas fa-exclamation-triangle d-block mb-2"></i>No se pudo cargar la vista del vehículo.</span></div>';
                });
        },
    };
})(window);
