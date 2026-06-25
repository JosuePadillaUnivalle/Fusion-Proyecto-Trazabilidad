@once
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .actividad-modal-resumen {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
    }
    .actividad-modal-mapa {
        height: 200px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #d1e7dd;
        background: #f8fafc;
    }
    .actividad-modal-mapa--vacio {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: .85rem;
        text-align: center;
        padding: 1rem;
    }
    .actividad-insumo-card {
        display: flex;
        align-items: center;
        gap: .75rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: .65rem .85rem;
        margin-bottom: .5rem;
    }
    .actividad-insumo-card img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }
    .actividad-modal-obs {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: .65rem .85rem;
    }
    #actividadModalResumenCargando {
        min-height: 120px;
    }
</style>
@endpush
@endonce

<div class="modal fade" id="modalCompletarEvidencia" tabindex="-1" role="dialog" aria-labelledby="modalCompletarEvidenciaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <form id="formCompletarEvidencia" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e4620, #2c5530); color: #fff;">
                    <h5 class="modal-title font-weight-bold mb-0" id="modalCompletarEvidenciaTitulo">
                        <i class="fas fa-clipboard-check mr-2"></i>Completar actividad
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="text-muted small mb-3" id="modalCompletarEvidenciaMensaje">
                        Revise el resumen de la actividad asignada. Luego suba una foto como evidencia de que ya fue realizada.
                    </p>

                    <div id="actividadModalResumenCargando" class="text-center text-muted py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                        <p class="small mb-0">Cargando resumen…</p>
                    </div>

                    <div id="actividadModalResumen" class="actividad-modal-resumen mb-4" style="display: none;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: .35rem;">
                            <p class="small text-uppercase text-muted font-weight-bold mb-0">Resumen — solo lectura</p>
                            <span id="actividadModalPrioridad" class="badge badge-secondary" style="display: none;"></span>
                        </div>
                        <dl class="row mb-0 small" id="actividadModalDatosLote"></dl>
                        <div id="actividadModalMapaWrap" class="mb-3" style="display: none;">
                            <p class="small text-uppercase text-muted font-weight-bold mb-2">
                                <i class="fas fa-map-marked-alt mr-1"></i> Ubicación del lote
                            </p>
                            <div id="actividadModalMapa" class="actividad-modal-mapa" role="img" aria-label="Mapa del lote"></div>
                            <p class="small text-muted mb-0 mt-2" id="actividadModalUbicacionTexto"></p>
                        </div>
                        <div id="actividadModalMapaVacio" class="mb-3" style="display: none;">
                            <p class="small text-uppercase text-muted font-weight-bold mb-2">
                                <i class="fas fa-map-marked-alt mr-1"></i> Ubicación del lote
                            </p>
                            <div class="actividad-modal-mapa actividad-modal-mapa--vacio" id="actividadModalMapaVacioInner"></div>
                        </div>
                        <div id="actividadModalInsumos" style="display: none;"></div>
                        <div id="actividadModalRiego" class="alert alert-info small py-2 mb-2" style="display: none;"></div>
                        <div id="actividadModalObservaciones" class="actividad-modal-obs small mb-0" style="display: none;">
                            <strong class="d-block text-primary mb-1"><i class="fas fa-comment-dots mr-1"></i> Observaciones registradas</strong>
                            <span id="actividadModalObservacionesTexto"></span>
                        </div>
                    </div>

                    <hr class="my-3" id="actividadModalSeparadorFoto" style="display: none;">

                    <div id="actividadModalSeccionFoto" style="display: none;">
                        <label class="font-weight-bold d-block mb-2">
                            <i class="fas fa-camera text-success mr-1"></i> Foto de evidencia <span class="text-danger">*</span>
                        </label>
                        @include('partials.evidencia-foto-campo', [
                            'inputId' => 'actividadModalEvidenciaInput',
                            'btnId' => 'actividadModalEvidenciaBtn',
                            'previewWrapId' => 'actividadModalEvidenciaPreviewWrap',
                            'previewImgId' => 'actividadModalEvidenciaPreviewImg',
                            'previewNombreId' => 'actividadModalEvidenciaPreviewNombre',
                            'btnLabel' => 'Tomar o subir foto de la aplicación',
                            'hint' => 'Muestre el trabajo realizado: riego, aplicación de insumo, etc.',
                        ])
                        @error('evidencia_foto')
                            <div class="alert alert-danger small mt-2 mb-0">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold" id="btnEnviarCompletar" disabled>
                        <i class="fas fa-paper-plane mr-1"></i> Enviar evidencia y completar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
@include('lotes.partials.mapa-superficie-helper')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    window.AgroFusionSiembraResumenMapa = window.AgroFusionSiembraResumenMapa || {
        init: function (mapaId, config) {
            var el = document.getElementById(mapaId);
            if (!el || !window.L || !window.AgroFusionLoteMapa) {
                return null;
            }
            if (el._siembraMapaLeaflet) {
                el._siembraMapaLeaflet.invalidateSize();
                return el._siembraMapaLeaflet;
            }
            var lat = parseFloat(config.lat);
            var lng = parseFloat(config.lng);
            var ha = parseFloat(config.superficie_ha) || 0;
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return null;
            }
            var map = L.map(el, {
                zoomControl: true,
                dragging: true,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                touchZoom: true,
            }).setView([lat, lng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
            }).addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup(config.ubicacion || 'Parcela');
            var circleRef = { current: null };
            window.AgroFusionLoteMapa.actualizarCirculo(map, circleRef, lat, lng, ha, {
                color: '#0f766e',
                fillColor: '#14b8a6',
                fillOpacity: 0.28,
                ajustarVista: true,
            });
            el._siembraMapaInicializado = true;
            el._siembraMapaLeaflet = map;
            setTimeout(function () { map.invalidateSize(); }, 120);
            return map;
        },
    };
})();
</script>
<script>
(function () {
    if (window.ModalCompletarEvidencia) return;

    var form = document.getElementById('formCompletarEvidencia');
    var btnEnviar = document.getElementById('btnEnviarCompletar');
    var resumenEl = document.getElementById('actividadModalResumen');
    var cargandoEl = document.getElementById('actividadModalResumenCargando');
    var seccionFoto = document.getElementById('actividadModalSeccionFoto');
    var separadorFoto = document.getElementById('actividadModalSeparadorFoto');
    var mapaConfigActual = null;

    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    function fmtNum(n) {
        return Number(n).toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
    }

    function setScrollTo(value) {
        if (!form) return;
        var el = form.querySelector('input[name="scroll_to"]');
        if (!value) {
            if (el) el.remove();
            return;
        }
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = 'scroll_to';
            form.appendChild(el);
        }
        el.value = value;
    }

    function resetFoto() {
        var input = document.getElementById('actividadModalEvidenciaInput');
        var previewWrap = document.getElementById('actividadModalEvidenciaPreviewWrap');
        var previewImg = document.getElementById('actividadModalEvidenciaPreviewImg');
        var previewNombre = document.getElementById('actividadModalEvidenciaPreviewNombre');
        if (input) input.value = '';
        if (previewWrap) previewWrap.style.display = 'none';
        if (previewImg) previewImg.removeAttribute('src');
        if (previewNombre) previewNombre.textContent = '';
        if (btnEnviar) btnEnviar.disabled = true;
    }

    function resetResumen() {
        if (resumenEl) resumenEl.style.display = 'none';
        if (cargandoEl) cargandoEl.style.display = 'none';
        if (seccionFoto) seccionFoto.style.display = 'none';
        if (separadorFoto) separadorFoto.style.display = 'none';
        document.getElementById('actividadModalDatosLote').innerHTML = '';
        document.getElementById('actividadModalInsumos').innerHTML = '';
        document.getElementById('actividadModalInsumos').style.display = 'none';
        document.getElementById('actividadModalRiego').style.display = 'none';
        document.getElementById('actividadModalObservaciones').style.display = 'none';
        document.getElementById('actividadModalMapaWrap').style.display = 'none';
        document.getElementById('actividadModalMapaVacio').style.display = 'none';
        var prioridadBadge = document.getElementById('actividadModalPrioridad');
        if (prioridadBadge) {
            prioridadBadge.style.display = 'none';
            prioridadBadge.textContent = '';
        }
        mapaConfigActual = null;
        var mapEl = document.getElementById('actividadModalMapa');
        if (mapEl && mapEl._siembraMapaLeaflet) {
            mapEl._siembraMapaLeaflet.remove();
            mapEl._siembraMapaInicializado = false;
            mapEl._siembraMapaLeaflet = null;
        }
    }

    function resetForm() {
        if (form) form.reset();
        resetFoto();
        resetResumen();
        setScrollTo('');
    }

    function renderDlItem(dt, dd) {
        return '<dt class="col-sm-4 text-muted">' + escHtml(dt) + '</dt>'
            + '<dd class="col-sm-8 font-weight-bold mb-2">' + dd + '</dd>';
    }

    function pintarResumen(data) {
        var dl = document.getElementById('actividadModalDatosLote');
        var html = '';
        html += renderDlItem('Actividad', escHtml(data.titulo || data.tipo || 'Actividad'));
        if (data.lote) {
            html += renderDlItem('Lote', escHtml(data.lote.nombre));
            if (data.lote.cultivo) {
                html += renderDlItem('Cultivo', escHtml(data.lote.cultivo));
            }
            if (data.lote.superficie) {
                html += renderDlItem('Superficie', escHtml(data.lote.superficie));
            }
            if (data.lote.codigo) {
                html += renderDlItem('Código trazabilidad', escHtml(data.lote.codigo));
            }
            if (data.lote.ubicacion && !(data.mapa && data.mapa.tiene_coordenadas)) {
                html += renderDlItem('Ubicación', escHtml(data.lote.ubicacion));
            }
        }
        dl.innerHTML = html;

        var prioridadBadge = document.getElementById('actividadModalPrioridad');
        if (data.prioridad && prioridadBadge) {
            prioridadBadge.textContent = data.prioridad.charAt(0).toUpperCase() + data.prioridad.slice(1);
            prioridadBadge.className = 'badge badge-' + (data.prioridad_badge || 'secondary');
            prioridadBadge.style.display = '';
        }

        var mapa = data.mapa || {};
        if (mapa.tiene_coordenadas) {
            document.getElementById('actividadModalMapaWrap').style.display = '';
            document.getElementById('actividadModalMapaVacio').style.display = 'none';
            var ubicTxt = document.getElementById('actividadModalUbicacionTexto');
            if (ubicTxt) {
                ubicTxt.innerHTML = mapa.ubicacion
                    ? '<i class="fas fa-location-dot mr-1"></i>' + escHtml(mapa.ubicacion)
                    : '';
            }
            mapaConfigActual = {
                lat: mapa.lat,
                lng: mapa.lng,
                superficie_ha: mapa.superficie_ha || 0,
                ubicacion: mapa.ubicacion || '',
            };
        } else {
            document.getElementById('actividadModalMapaWrap').style.display = 'none';
            document.getElementById('actividadModalMapaVacio').style.display = '';
            var vacioInner = document.getElementById('actividadModalMapaVacioInner');
            if (vacioInner) {
                vacioInner.innerHTML = '<span><i class="fas fa-map-pin d-block mb-2" style="font-size:1.25rem;opacity:.5;"></i>'
                    + 'Sin coordenadas GPS en el lote.<br>'
                    + (mapa.superficie_ha ? 'Superficie: <strong>' + fmtNum(mapa.superficie_ha) + ' ha</strong>' : '')
                    + '</span>';
            }
        }

        var insumosWrap = document.getElementById('actividadModalInsumos');
        var insumos = data.insumos || [];
        if (insumos.length > 0) {
            var insHtml = '<p class="small text-uppercase text-muted font-weight-bold mb-2 mt-2">'
                + '<i class="fas fa-flask mr-1"></i> Insumos a aplicar</p>';
            insumos.forEach(function (ins) {
                insHtml += '<div class="actividad-insumo-card">';
                if (ins.imagen) {
                    insHtml += '<img src="' + escHtml(ins.imagen) + '" alt="">';
                } else {
                    insHtml += '<div class="text-muted" style="width:48px;text-align:center;"><i class="fas fa-box fa-lg"></i></div>';
                }
                insHtml += '<div><strong>' + escHtml(ins.nombre) + '</strong>'
                    + '<div class="text-success font-weight-bold">'
                    + fmtNum(ins.cantidad) + ' ' + escHtml(ins.unidad || 'ud')
                    + '</div></div></div>';
            });
            insumosWrap.innerHTML = insHtml;
            insumosWrap.style.display = '';
        }

        if (data.riego) {
            var riegoEl = document.getElementById('actividadModalRiego');
            var riegoTxt = typeof data.riego === 'string'
                ? data.riego
                : (data.riego.label || data.riego.nombre || '');
            riegoEl.innerHTML = '<i class="fas fa-tint mr-1"></i><strong>Tipo de riego:</strong> ' + escHtml(riegoTxt);
            riegoEl.style.display = '';
        }

        if (data.observaciones) {
            document.getElementById('actividadModalObservacionesTexto').textContent = data.observaciones;
            document.getElementById('actividadModalObservaciones').style.display = '';
        }

        if (resumenEl) resumenEl.style.display = '';
        if (separadorFoto) separadorFoto.style.display = '';
        if (seccionFoto) seccionFoto.style.display = '';

        if (mapaConfigActual && window.AgroFusionSiembraResumenMapa) {
            setTimeout(function () {
                window.AgroFusionSiembraResumenMapa.init('actividadModalMapa', mapaConfigActual);
            }, 280);
        }
    }

    function abrir(action, titulo, lote, scrollTo, resumenUrl) {
        if (!form) return;
        form.action = action;
        resetForm();
        setScrollTo(scrollTo || '');

        var tituloModal = document.getElementById('modalCompletarEvidenciaTitulo');
        if (tituloModal) {
            tituloModal.innerHTML = '<i class="fas fa-clipboard-check mr-2"></i>' + escHtml(titulo || 'Completar actividad');
        }

        if (window.jQuery) window.jQuery('#modalCompletarEvidencia').modal('show');

        if (resumenUrl) {
            if (cargandoEl) cargandoEl.style.display = '';
            fetch(resumenUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('No se pudo cargar el resumen');
                    return r.json();
                })
                .then(function (data) {
                    if (cargandoEl) cargandoEl.style.display = 'none';
                    pintarResumen(data);
                })
                .catch(function () {
                    if (cargandoEl) cargandoEl.style.display = 'none';
                    var dl = document.getElementById('actividadModalDatosLote');
                    if (dl) {
                        dl.innerHTML = renderDlItem('Actividad', escHtml(titulo))
                            + (lote ? renderDlItem('Lote', escHtml(lote)) : '');
                    }
                    if (resumenEl) resumenEl.style.display = '';
                    if (separadorFoto) separadorFoto.style.display = '';
                    if (seccionFoto) seccionFoto.style.display = '';
                });
        } else {
            var dl = document.getElementById('actividadModalDatosLote');
            if (dl) {
                dl.innerHTML = renderDlItem('Actividad', escHtml(titulo))
                    + (lote ? renderDlItem('Lote', escHtml(lote)) : '');
            }
            if (resumenEl) resumenEl.style.display = '';
            if (separadorFoto) separadorFoto.style.display = '';
            if (seccionFoto) seccionFoto.style.display = '';
        }
    }

    window.ModalCompletarEvidencia = { abrir: abrir };

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-completar-evidencia');
        if (!btn) return;
        e.preventDefault();
        abrir(
            btn.getAttribute('data-action') || '',
            btn.getAttribute('data-titulo') || 'Actividad',
            btn.getAttribute('data-lote') || '',
            btn.getAttribute('data-scroll-to') || '',
            btn.getAttribute('data-resumen-url') || ''
        );
    });

    document.getElementById('modalCompletarEvidencia')?.addEventListener('shown.bs.modal', function () {
        if (mapaConfigActual && window.AgroFusionSiembraResumenMapa) {
            setTimeout(function () {
                window.AgroFusionSiembraResumenMapa.init('actividadModalMapa', mapaConfigActual);
            }, 200);
        }
    });

    document.getElementById('modalCompletarEvidencia')?.addEventListener('hidden.bs.modal', resetForm);

    document.addEventListener('DOMContentLoaded', function () {
        if (window.AgrofusionEvidenciaFotoCampo) {
            window.AgrofusionEvidenciaFotoCampo(
                'actividadModalEvidenciaInput',
                'actividadModalEvidenciaBtn',
                'actividadModalEvidenciaPreviewWrap',
                'actividadModalEvidenciaPreviewImg',
                'actividadModalEvidenciaPreviewNombre'
            );
        }
        var inputFoto = document.getElementById('actividadModalEvidenciaInput');
        inputFoto?.addEventListener('change', function () {
            if (btnEnviar && inputFoto.files && inputFoto.files[0]) {
                btnEnviar.disabled = false;
            }
        });
    });

    @if($errors->has('evidencia_foto'))
    if (window.jQuery) {
        window.jQuery(function () {
            window.jQuery('#modalCompletarEvidencia').modal('show');
        });
    }
    @endif
})();
</script>
@endpush
@endonce
