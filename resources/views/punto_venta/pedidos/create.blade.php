@extends('layouts.app')

@section('title', 'Solicitar producto')
@section('page_title', 'Solicitar producto')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('punto_venta.partials.modulo-styles')
<style>
.pedido-sol-page { max-width: 920px; margin: 0 auto; }
.pedido-sol-page .sol-card {
    border: 1px solid #e2e8f0; border-radius: 12px; background: #fff;
    box-shadow: 0 1px 4px rgba(15,23,42,.06); overflow: hidden;
}
.pedido-sol-page .sol-top {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .65rem .85rem; background: linear-gradient(90deg, #14532d, #16a34a); color: #fff;
}
.pedido-sol-page .sol-top h2 { font-size: .95rem; font-weight: 800; margin: 0; }
.pedido-sol-page .sol-top .sol-ref { font-size: .78rem; opacity: .9; }
.pedido-sol-page .sol-body { padding: .85rem; }
.pedido-sol-page .sol-grid {
    display: grid; grid-template-columns: 1fr 1.15fr; gap: .85rem 1rem;
}
@media (max-width: 767.98px) { .pedido-sol-page .sol-grid { grid-template-columns: 1fr; } }
.pedido-sol-page .sol-panel {
    border: 1px solid #f1f5f9; border-radius: 10px; padding: .65rem .75rem; background: #fafafa;
}
.pedido-sol-page .sol-panel-title {
    font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
    color: #64748b; margin-bottom: .5rem;
}
.pedido-sol-page .pdv-field-label {
    font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    color: #64748b; margin-bottom: .25rem;
}
.pedido-sol-page .sol-row-entrega { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: .5rem; }
.pedido-sol-page .sol-map-btn {
    display: flex; align-items: center; justify-content: center; gap: .45rem;
    width: 100%; margin-top: .45rem; padding: .45rem .75rem;
    font-size: .78rem; font-weight: 700; letter-spacing: .01em;
    color: #1e3a5f; background: #f8fafc;
    border: 1.5px solid #94a3b8; border-radius: 8px;
    transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
}
.pedido-sol-page .sol-map-btn:hover:not(:disabled) {
    background: #eff6ff; border-color: #64748b; color: #0f172a;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .08);
}
.pedido-sol-page .sol-map-btn:disabled { opacity: .55; cursor: not-allowed; color: #94a3b8; border-color: #e2e8f0; }
.pedido-sol-page .sol-map-btn i { font-size: .9rem; color: #334155; }
#modalMapaPdvDestino .modal-dialog { max-width: min(960px, 94vw); }
#modalMapaPdvDestino .modal-content { border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
#modalMapaPdvDestino .modal-header {
    background: #1e293b; border-bottom: 1px solid #334155; padding: .65rem 1rem;
}
#modalMapaPdvDestino .modal-title { font-size: .95rem; font-weight: 700; }
#modalMapaPdvDestino .modal-body { padding: .75rem 1rem 1rem; background: #f8fafc; }
#modalMapaPdvDestino .pdv-mapa-wrap {
    height: min(62vh, 520px); min-height: 360px;
    border-radius: 10px; border: 1px solid #cbd5e1;
    box-shadow: inset 0 1px 3px rgba(15, 23, 42, .06);
}
#modalMapaPdvDestino .pdv-mapa-leyenda {
    display: flex; flex-wrap: wrap; align-items: center; gap: .35rem .75rem;
    margin-top: .65rem; font-size: .72rem; color: #64748b;
}
#modalMapaPdvDestino .pdv-mapa-lista {
    display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .55rem;
    max-height: 110px; overflow-y: auto;
}
#modalMapaPdvDestino .pdv-mapa-chip {
    border: 1px solid #cbd5e1; background: #fff; border-radius: 999px;
    padding: .28rem .65rem; font-size: .74rem; font-weight: 600; color: #334155;
    cursor: pointer; transition: all .12s ease;
}
#modalMapaPdvDestino .pdv-mapa-chip:hover { border-color: #64748b; background: #f1f5f9; }
#modalMapaPdvDestino .pdv-mapa-chip.is-active {
    border-color: #1e293b; background: #1e293b; color: #fff;
}
.pdv-mapa-pin { background: transparent; border: 0; }
.pdv-mapa-pin__bubble {
    display: flex; flex-direction: column; align-items: center; filter: drop-shadow(0 3px 6px rgba(15, 23, 42, .28));
}
.pdv-mapa-pin__icon {
    width: 34px; height: 34px; border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg); background: linear-gradient(145deg, #334155, #1e293b);
    border: 2.5px solid #fff; display: flex; align-items: center; justify-content: center;
}
.pdv-mapa-pin__icon i {
    transform: rotate(45deg); color: #fff; font-size: .78rem;
}
.pdv-mapa-pin__label {
    margin-top: 4px; max-width: 120px; padding: .15rem .45rem;
    background: rgba(15, 23, 42, .88); color: #fff; border-radius: 6px;
    font-size: .65rem; font-weight: 700; text-align: center; line-height: 1.2;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.pdv-mapa-pin.is-selected .pdv-mapa-pin__icon {
    background: linear-gradient(145deg, #1e40af, #1e3a8a);
    box-shadow: 0 0 0 3px rgba(30, 64, 175, .35);
}
.pedido-sol-page .pdv-producto-pick.is-locked { opacity: .5; pointer-events: none; }
.pedido-sol-page .sol-footer {
    display: flex; justify-content: space-between; align-items: center; gap: .5rem;
    padding: .65rem .85rem; border-top: 1px solid #f1f5f9; background: #f8fafc;
}
.pedido-dist-mapa-pin { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .65rem; border: 2px solid #fff; }
#modalMapaMayoristaCatalogo { z-index: 1070 !important; }
#modalMapaMayoristaCatalogo .modal-dialog { max-width: min(960px, 94vw); }
#modalMapaMayoristaCatalogo .modal-content { border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; }
#modalMapaMayoristaCatalogo .modal-body { padding: .75rem 1rem 1rem; background: #f8fafc; }
#modalMapaMayoristaCatalogo .pdv-mapa-wrap {
    height: min(62vh, 520px); min-height: 360px; width: 100%;
    border-radius: 10px; border: 1px solid #cbd5e1;
    box-shadow: inset 0 1px 3px rgba(15, 23, 42, .06);
    background: #e2e8f0;
}
#modalMapaMayoristaCatalogo .pdv-mapa-leyenda {
    margin-top: .65rem; font-size: .78rem; color: #64748b;
}
body.modal-mapa-mayorista-open .modal-backdrop:last-of-type { z-index: 1065 !important; }
</style>
@endpush

@section('content')
<div class="pedido-sol-page">
    <div class="sol-card">
        <div class="sol-top">
            <h2><i class="fas fa-paper-plane mr-1"></i> Solicitar producto al mayorista</h2>
            <span class="sol-ref"><i class="fas fa-hashtag"></i> {{ $numeroSolicitud }}</span>
        </div>

        <div class="sol-body">
            @if($errors->any())<div class="alert alert-danger py-2 mb-2">{{ $errors->first() }}</div>@endif
            @if(session('error'))<div class="alert alert-danger py-2 mb-2">{{ session('error') }}</div>@endif

            <form method="POST" action="{{ route('punto-venta.pedidos.store') }}" id="formPedidoDist">
                @csrf
                <input type="hidden" name="ctx" value="pdv">

                <div class="sol-grid">
                    <div class="sol-panel">
                        <div class="sol-panel-title"><i class="fas fa-store mr-1"></i> Entrega</div>

                        @if($esAdmin ?? false)
                        <div class="form-group mb-2">
                            <label class="pdv-field-label">Minorista <span class="text-danger">*</span></label>
                            @include('partials.selector-catalogo', [
                                'id' => 'dist_minorista',
                                'name' => 'minorista_usuarioid',
                                'value' => old('minorista_usuarioid', $oldMinoristaId ?? ''),
                                'labelSelected' => $oldMinoristaLabel ?? '',
                                'endpoint' => route('catalogo-selector.usuarios'),
                                'params' => ['roles' => 'minorista'],
                                'title' => 'Minorista',
                                'searchPlaceholder' => 'Nombre o correo…',
                                'required' => true,
                                'inputGroup' => true,
                            ])
                        </div>
                        @endif

                        @if(($esMinorista ?? false) && ($puntosMinorista ?? collect())->isEmpty())
                            <div class="alert alert-warning py-2 mb-0 small">
                                Registre un punto de venta activo.
                                <a href="{{ route('punto-venta.puntos.create') }}">Crear PDV</a>
                            </div>
                        @else
                            <div class="form-group mb-1 {{ ($esAdmin ?? false) && empty($oldMinoristaId) ? 'opacity-50' : '' }}" id="wrapPdvSelector">
                                <label class="pdv-field-label">Punto de venta <span class="text-danger">*</span></label>
                                @include('partials.selector-catalogo', [
                                    'id' => 'dist_punto_venta',
                                    'name' => 'puntoventaid',
                                    'value' => old('puntoventaid', $oldPuntoId ?? ''),
                                    'labelSelected' => $oldPuntoLabel ?? '',
                                    'endpoint' => route('catalogo-selector.puntos-venta'),
                                    'title' => ($esMinorista ?? false) ? 'Mis puntos de venta' : 'Puntos del minorista',
                                    'searchPlaceholder' => 'Nombre o dirección…',
                                    'required' => true,
                                    'inputGroup' => true,
                                    'params' => ($esAdmin ?? false) && ! empty($oldMinoristaId)
                                        ? ['minorista_usuarioid' => (string) $oldMinoristaId]
                                        : [],
                                ])
                                <button type="button" class="sol-map-btn" id="btnVerMapaPdvDestino" @if(empty($puntosVentaMapa)) disabled @endif>
                                    <i class="fas fa-map-marked-alt"></i>
                                    <span>Ver puntos de venta en mapa</span>
                                </button>
                                @if($esAdmin ?? false)
                                <small class="form-text text-muted mb-0" id="txtAyudaMapaPdv">El mapa muestra solo los puntos del minorista seleccionado.</small>
                                @endif
                            </div>
                        @endif

                        <div class="sol-row-entrega">
                            <div>
                                <label class="pdv-field-label" for="fecha_entrega_deseada">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_entrega_deseada" id="fecha_entrega_deseada" class="form-control form-control-sm" required
                                    min="{{ now()->toDateString() }}" value="{{ old('fecha_entrega_deseada') }}">
                            </div>
                            <div>
                                <label class="pdv-field-label" for="hora_entrega_deseada">Hora</label>
                                <input type="time" name="hora_entrega_deseada" id="hora_entrega_deseada" class="form-control form-control-sm"
                                    value="{{ old('hora_entrega_deseada') }}">
                            </div>
                        </div>
                    </div>

                    <div class="sol-panel">
                        <div class="sol-panel-title"><i class="fas fa-box mr-1"></i> Producto</div>
                        @include('punto_venta.pedidos.partials.form-producto-fase3')
                    </div>
                </div>

                <div class="form-group mt-2 mb-0">
                    <label class="pdv-field-label" for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="2" class="form-control form-control-sm"
                        placeholder="Instrucciones de entrega (opcional)">{{ old('observaciones') }}</textarea>
                </div>
            </form>
        </div>

        <div class="sol-footer">
            <a href="{{ route('punto-venta.pedidos.index', ['ctx' => 'pdv']) }}" class="btn btn-light btn-sm border">Cancelar</a>
            <button type="submit" form="formPedidoDist" class="btn btn-success btn-sm" id="btnEnviarPedido">
                <i class="fas fa-paper-plane mr-1"></i> Enviar solicitud
            </button>
        </div>
    </div>
</div>

@if(! (($esMinorista ?? false) && ($puntosMinorista ?? collect())->isEmpty()))
<div class="modal fade" id="modalMapaPdvDestino" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt mr-1"></i> Seleccione punto de venta en el mapa</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="mapaPdvDestino" class="pdv-mapa-wrap"></div>
                <p class="pdv-mapa-leyenda mb-0">
                    <i class="fas fa-hand-pointer mr-1"></i> Haga clic en un pin o en la lista para elegir el destino de entrega.
                </p>
                <div class="pdv-mapa-lista" id="listaPdvMapaDestino"></div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="modalMapaMayoristaCatalogo" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#047857,#10b981);">
                <h5 class="modal-title" id="tituloMapaMayoristaCatalogo"><i class="fas fa-warehouse mr-1"></i> Ubicación del mayorista</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="background:#f8fafc;padding:.75rem 1rem 1rem;">
                <div id="mapaMayoristaCatalogo" class="pdv-mapa-wrap"></div>
                <p class="pdv-mapa-leyenda mb-0" id="txtMapaMayoristaCatalogo"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var puntosVentaMapa = @json($puntosVentaMapa ?? []);
    var pdvSeleccionadoId = @json(old('puntoventaid', $oldPuntoId ?? ''));
    var esAdmin = @json($esAdmin ?? false);
    var minoristaSeleccionadoId = @json(old('minorista_usuarioid', $oldMinoristaId ?? ''));
    var stockMaxUnidades = null;
    var stockUnidadEtiqueta = 'unidades';
    var presentacionesEndpoint = @json(route('catalogo-selector.presentaciones-producto'));
    var presentacionesExtra = {};
    var oldPresentacionId = @json(old('insumo_presentacionid', ''));
    var oldInsumoId = @json(old('insumoid', ''));
    var oldAlmacenMayoristaId = @json(old('almacen_mayorista_origenid', ''));
    var productoMayoristaExtra = null;
    var nombreProductoActual = '';
    var capacidadPdvEndpoint = @json(route('catalogo-selector.punto-venta-capacidad-pdv'));
    var capacidadPdvExcede = false;
    var capacidadPdvTimer = null;

    function fmtKg(n) {
        return Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getPdvSeleccionadoId() {
        var val = document.querySelector('#selector_wrap_dist_punto_venta .selector-catalogo-value')?.value;
        return val ? String(val) : (pdvSeleccionadoId ? String(pdvSeleccionadoId) : '');
    }

    function renderCapacidadPdv(data) {
        var panel = document.getElementById('panelCapacidadPdv');
        if (!panel || !data) return;

        panel.classList.remove('d-none');
        capacidadPdvExcede = !!data.excede_capacidad;
        panel.classList.toggle('is-excedido', capacidadPdvExcede);

        var txtNombre = document.getElementById('txtCapacidadPdvNombre');
        if (txtNombre) txtNombre.textContent = (data.punto_nombre || 'Punto de venta') + ' · ' + (data.almacen_nombre || 'Almacén');

        var txtTotal = document.getElementById('txtCapacidadTotal');
        var txtDisp = document.getElementById('txtCapacidadDisponible');
        var txtPct = document.getElementById('txtCapacidadPorcentaje');
        if (txtTotal) txtTotal.textContent = fmtKg(data.capacidad_kg);
        if (txtDisp) {
            txtDisp.textContent = fmtKg(data.ingreso_kg > 0 ? data.disponible_despues_kg : data.disponible_kg);
        }
        if (txtPct) txtPct.textContent = String(data.ingreso_kg > 0 ? data.porcentaje_despues : data.porcentaje);

        var rowIngreso = document.getElementById('rowCapacidadIngreso');
        var txtIngreso = document.getElementById('txtCapacidadIngresoKg');
        if (data.ingreso_kg > 0 && rowIngreso && txtIngreso) {
            rowIngreso.classList.remove('d-none');
            txtIngreso.textContent = fmtKg(data.ingreso_kg) + ' kg';
        } else {
            rowIngreso?.classList.add('d-none');
        }

        var barBase = document.getElementById('barCapacidadBase');
        var barProy = document.getElementById('barCapacidadProyeccion');
        var pctBase = Math.min(100, parseFloat(data.porcentaje || 0));
        var pctDespues = Math.min(100, parseFloat(data.ingreso_kg > 0 ? data.porcentaje_despues : data.porcentaje || 0));
        if (barBase) barBase.style.width = pctBase + '%';
        if (barProy) {
            if (data.ingreso_kg > 0) {
                barProy.classList.remove('d-none');
                barProy.style.width = pctDespues + '%';
            } else {
                barProy.classList.add('d-none');
                barProy.style.width = '0%';
            }
        }

        var hint = document.getElementById('txtCapacidadHint');
        var alerta = document.getElementById('alertaCapacidadPdv');
        var txtAlerta = document.getElementById('txtAlertaCapacidadPdv');
        if (data.ingreso_kg > 0 && capacidadPdvExcede) {
            hint?.classList.add('d-none');
            alerta?.classList.remove('d-none');
            if (txtAlerta) {
                txtAlerta.textContent = 'Necesita ' + fmtKg(data.ingreso_kg) + ' kg y solo hay ' + fmtKg(data.disponible_kg) + ' kg libres en el almacén del punto de venta.';
            }
        } else if (data.ingreso_kg > 0) {
            hint?.classList.add('d-none');
            alerta?.classList.add('d-none');
        } else if (hint) {
            hint.classList.remove('d-none');
            alerta?.classList.add('d-none');
            hint.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Ocupado: ' + fmtKg(data.ocupado_kg) + ' kg · Disponible: ' + fmtKg(data.disponible_kg) + ' kg. Indique cantidad para proyectar el ingreso.';
        }
    }

    function actualizarPreviewCapacidadPdv() {
        if (capacidadPdvTimer) clearTimeout(capacidadPdvTimer);
        capacidadPdvTimer = setTimeout(function () {
            var pdvId = getPdvSeleccionadoId();
            var panel = document.getElementById('panelCapacidadPdv');
            if (!pdvId || !panel) {
                panel?.classList.add('d-none');
                capacidadPdvExcede = false;
                return;
            }

            var extra = extraPresentacionSeleccionada();
            var qty = cantidadSolicitada();
            var params = new URLSearchParams({ puntoventaid: pdvId });
            if (extra && qty > 0 && parseFloat(extra.peso_neto_kg || 0) > 0) {
                params.set('cantidad', String(qty));
                params.set('peso_neto_kg', String(extra.peso_neto_kg));
            }

            fetch(capacidadPdvEndpoint + '?' + params.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (json) {
                    if (json && json.data) {
                        renderCapacidadPdv(json.data);
                    } else {
                        panel.classList.add('d-none');
                        capacidadPdvExcede = false;
                    }
                })
                .catch(function () {
                    panel.classList.add('d-none');
                    capacidadPdvExcede = false;
                });
        }, 200);
    }

    function renderMapaMayoristaCatalogo(lat, lng, popupText) {
        var el = document.getElementById('mapaMayoristaCatalogo');
        if (!el || !window.L) return;
        if (window._mapaMayoristaCatalogo) {
            try { window._mapaMayoristaCatalogo.remove(); } catch (e) { /* noop */ }
            window._mapaMayoristaCatalogo = null;
            window._markerMayoristaCatalogo = null;
            el.innerHTML = '';
        }
        window._mapaMayoristaCatalogo = L.map(el, { scrollWheelZoom: true }).setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(window._mapaMayoristaCatalogo);
        window._markerMayoristaCatalogo = L.marker([lat, lng]).addTo(window._mapaMayoristaCatalogo);
        if (popupText) {
            window._markerMayoristaCatalogo.bindPopup(popupText).openPopup();
        }
        setTimeout(function () {
            if (window._mapaMayoristaCatalogo) {
                window._mapaMayoristaCatalogo.invalidateSize();
            }
        }, 220);
    }

    function abrirMapaMayoristaDesdeCatalogo(extra) {
        if (extra.lat == null || extra.lng == null || !window.L) return;
        var lat = parseFloat(extra.lat);
        var lng = parseFloat(extra.lng);
        if (isNaN(lat) || isNaN(lng)) return;

        var titulo = document.getElementById('tituloMapaMayoristaCatalogo');
        var txt = document.getElementById('txtMapaMayoristaCatalogo');
        var popupText = [extra.almacen_nombre, extra.ubicacion].filter(Boolean).join(' · ');
        if (titulo) {
            titulo.innerHTML = '<i class="fas fa-warehouse mr-1"></i> ' + (extra.mayorista_nombre || 'Mayorista');
        }
        if (txt) {
            txt.textContent = popupText || 'Ubicación del almacén mayorista';
        }

        var $mapModal = window.jQuery('#modalMapaMayoristaCatalogo');
        if (!$mapModal.parent().is('body')) {
            $mapModal.appendTo('body');
        }

        $mapModal.off('show.bs.modal.mapaMayorista shown.bs.modal.mapaMayorista hidden.bs.modal.mapaMayorista');
        $mapModal.on('show.bs.modal.mapaMayorista', function () {
            document.body.classList.add('modal-mapa-mayorista-open');
            var zIndex = 1050 + (10 * window.jQuery('.modal.show').length);
            window.jQuery(this).css('z-index', zIndex);
            setTimeout(function () {
                window.jQuery('.modal-backdrop').last()
                    .css('z-index', zIndex - 5)
                    .addClass('mapa-mayorista-backdrop');
            }, 0);
        });
        $mapModal.on('shown.bs.modal.mapaMayorista', function () {
            renderMapaMayoristaCatalogo(lat, lng, popupText);
        });
        $mapModal.on('hidden.bs.modal.mapaMayorista', function () {
            document.body.classList.remove('modal-mapa-mayorista-open');
        });

        $mapModal.modal('show');
    }

    function aplicarSeleccionProductoMayorista(item) {
        var extra = item?.extra || {};
        productoMayoristaExtra = extra;
        nombreProductoActual = extra.producto_nombre || (item?.label ? String(item.label).split('·')[0].trim() : '');

        var insumoReal = document.getElementById('insumoid_real');
        var almacenMay = document.getElementById('almacen_mayorista_origenid');
        if (insumoReal) insumoReal.value = extra.insumoid ? String(extra.insumoid) : '';
        if (almacenMay) almacenMay.value = extra.almacen_mayorista_origenid ? String(extra.almacen_mayorista_origenid) : '';

        var panelMeta = document.getElementById('panelProductoMeta');
        if (panelMeta) {
            actualizarMetaProducto(extra);
        }

        actualizarPanelStock(extra, null);

        if (extra.insumoid) {
            document.getElementById('pdvPresentacionPick')?.classList.remove('is-locked');
            cargarPresentaciones(extra.insumoid, null, extra);
        } else {
            limpiarPresentacion();
        }
    }

    function obtenerNombreProducto() {
        if (nombreProductoActual) return nombreProductoActual;
        var wrap = document.getElementById('selector_wrap_dist_producto_mayorista');
        var label = wrap?.querySelector('.selector-catalogo-label')?.value || '';
        return label.split('·')[0].trim() || 'este producto';
    }

    function extraPresentacionSeleccionada() {
        var id = document.getElementById('selectPresentacionMayorista')?.value || '';
        return id ? presentacionesExtra[id] : null;
    }

    function actualizarMetaProducto(extra) {
        var panel = document.getElementById('panelProductoMeta');
        if (!panel) return;
        if (!extra || (!extra.mayorista_nombre && !extra.almacen_nombre && !extra.ubicacion)) {
            panel.classList.add('d-none');
            return;
        }
        var txtMay = document.getElementById('txtMetaMayorista');
        var txtUbi = document.getElementById('txtMetaUbicacion');
        var rowStock = document.getElementById('rowMetaStock');
        var txtStock = document.getElementById('txtMetaStockKg');
        if (txtMay) txtMay.textContent = extra.mayorista_nombre || '—';
        if (txtUbi) txtUbi.textContent = extra.ubicacion || extra.almacen_nombre || '—';
        if (extra.stock_kg != null && rowStock && txtStock) {
            txtStock.textContent = extra.stock_kg > 0
                ? Number(extra.stock_kg).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kg'
                : 'Sin stock';
            rowStock.classList.remove('d-none');
        } else {
            rowStock?.classList.add('d-none');
        }
        panel.classList.remove('d-none');
    }

    function cantidadSolicitada() {
        var v = parseFloat(String(document.getElementById('cantidad')?.value || '').trim());
        return isNaN(v) ? 0 : v;
    }

    function necesitaEsperaStock(extra, cantidad) {
        if (!extra) return false;
        var qty = cantidad != null ? cantidad : cantidadSolicitada();
        if (qty <= 0) return false;
        var disp = parseFloat(extra.stock_unidades || 0);
        if (extra.tiene_stock && disp > 0) return qty > disp;
        return true;
    }

    function avisoPresentacionSinStock(extra) {
        if (!extra || extra.tiene_stock) return;
        var producto = obtenerNombreProducto();
        var pres = extra.presentacion_nombre || 'esta presentación';
        var mensaje = 'No hay stock de ' + producto + ' en ' + pres + '. Elija otra presentación o producto con stock disponible.';
        if (window.Swal) {
            Swal.fire({ icon: 'info', title: 'Sin stock actual', text: mensaje, confirmButtonText: 'Entendido' });
        } else {
            window.alert(mensaje);
        }
    }

    function sugerirPresentacionAlternativa(extraActual, idActual) {
        if (!extraActual || extraActual.tiene_stock) return;
        var alternativa = null;
        var altId = null;
        Object.keys(presentacionesExtra).forEach(function (id) {
            if (String(id) === String(idActual)) return;
            var ex = presentacionesExtra[id];
            if (ex && ex.tiene_stock && parseFloat(ex.stock_unidades || 0) > 0) {
                alternativa = ex;
                altId = id;
            }
        });
        if (!alternativa || !window.Swal) return;

        Swal.fire({
            icon: 'question',
            title: 'Hay stock en otra presentación',
            html: 'Hay <strong>' + Math.floor(alternativa.stock_unidades) + ' ' + (alternativa.unidad_etiqueta || 'unidades') +
                '</strong> disponibles en <strong>' + (alternativa.presentacion_nombre || 'otra presentación') + '</strong>. ¿Desea cambiar?',
            showCancelButton: true,
            confirmButtonText: 'Cambiar presentación',
            cancelButtonText: 'Mantener mi elección',
        }).then(function (result) {
            if (result.isConfirmed) {
                var select = document.getElementById('selectPresentacionMayorista');
                if (select) {
                    select.value = String(altId);
                    manejarCambioPresentacion(alternativa, false);
                }
            }
        });
    }

    function manejarCambioPresentacion(extra, mostrarAviso) {
        if (mostrarAviso === undefined) mostrarAviso = true;
        aplicarPresentacionExtra(extra);
        if (mostrarAviso && extra && !extra.tiene_stock) {
            avisoPresentacionSinStock(extra);
            sugerirPresentacionAlternativa(extra, document.getElementById('selectPresentacionMayorista')?.value);
        }
        validarStockCantidad();
    }

    function avisoValidacion(titulo, texto) {
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: titulo, text: texto, confirmButtonText: 'Entendido' });
        } else {
            window.alert(texto || titulo);
        }
    }

    function limpiarPresentacion() {
        var select = document.getElementById('selectPresentacionMayorista');
        if (!select) return;
        select.innerHTML = '<option value="">Elegir producto primero…</option>';
        select.value = '';
        select.disabled = true;
        presentacionesExtra = {};
        document.getElementById('txtPresentacionAyuda')?.classList.add('d-none');
        document.getElementById('txtPresentacionVacia')?.classList.add('d-none');
        document.getElementById('pdvPresentacionPick')?.classList.add('is-locked');
        document.getElementById('panelEsperaStock')?.classList.add('d-none');
        stockMaxUnidades = null;
        actualizarPanelStock(null, null);
    }

    function aplicarPresentacionExtra(extra) {
        if (!extra) {
            document.getElementById('panelEsperaStock')?.classList.add('d-none');
            document.getElementById('alertaStockExcedido')?.classList.add('d-none');
            return;
        }
        actualizarUnidadPresentacion(extra);
        actualizarPanelStock(null, extra);
    }

    function cargarPresentaciones(insumoId, preseleccionId, extraProducto) {
        var select = document.getElementById('selectPresentacionMayorista');
        var ayuda = document.getElementById('txtPresentacionAyuda');
        var vacio = document.getElementById('txtPresentacionVacia');
        extraProducto = extraProducto || productoMayoristaExtra || {};
        if (!select || !insumoId) {
            limpiarPresentacion();
            return;
        }

        select.disabled = true;
        select.innerHTML = '<option value="">Cargando…</option>';
        ayuda?.classList.remove('d-none');
        vacio?.classList.add('d-none');
        presentacionesExtra = {};

        var params = new URLSearchParams({
            insumoid: String(insumoId),
            catalogo_mayorista_pdv: '1',
            per_page: '50',
            page: '1',
        });
        if (extraProducto.almacen_mayorista_origenid) {
            params.set('almacen_mayorista_origenid', String(extraProducto.almacen_mayorista_origenid));
        }

        fetch(presentacionesEndpoint + '?' + params.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Error al cargar presentaciones');
                return r.json();
            })
            .then(function (json) {
                var items = json.data || [];
                ayuda?.classList.add('d-none');
                select.innerHTML = '';

                if (!items.length) {
                    select.innerHTML = '<option value="">Sin presentaciones disponibles</option>';
                    select.disabled = true;
                    vacio?.classList.remove('d-none');
                    stockMaxUnidades = null;
                    actualizarPanelStock(null, null);
                    return;
                }

                select.appendChild(new Option('Seleccione presentación…', ''));
                items.forEach(function (item) {
                    var extra = item.extra || {};
                    presentacionesExtra[String(item.id)] = extra;
                    var label = item.label || ('Presentación #' + item.id);
                    if (extra.tiene_stock && item.meta) {
                        label += ' · ' + item.meta;
                    } else if (!extra.tiene_stock) {
                        label += ' · sin stock ahora';
                    }
                    select.appendChild(new Option(label, String(item.id)));
                });

                select.disabled = false;
                document.getElementById('pdvPresentacionPick')?.classList.remove('is-locked');

                var elegido = preseleccionId ? String(preseleccionId) : '';
                if (elegido && presentacionesExtra[elegido]) {
                    select.value = elegido;
                    manejarCambioPresentacion(presentacionesExtra[elegido], false);
                } else {
                    var conStock = items.find(function (it) { return it.extra && it.extra.tiene_stock; });
                    if (conStock) {
                        select.value = String(conStock.id);
                        manejarCambioPresentacion(presentacionesExtra[select.value], false);
                    }
                }
            })
            .catch(function () {
                ayuda?.classList.add('d-none');
                select.innerHTML = '<option value="">No se pudieron cargar las presentaciones</option>';
                select.disabled = true;
            });
    }

    function actualizarPanelStock(extraProducto, extraPresentacion) {
        if (extraProducto) actualizarMetaProducto(extraProducto);
        var panelDisp = document.getElementById('panelStockDisponible');
        var txtDisp = document.getElementById('txtStockDisponible');
        var inputCantidad = document.getElementById('cantidad');
        if (extraPresentacion && extraPresentacion.tiene_stock && parseFloat(extraPresentacion.stock_unidades || 0) > 0 && panelDisp && txtDisp) {
            stockMaxUnidades = parseFloat(extraPresentacion.stock_unidades);
            stockUnidadEtiqueta = extraPresentacion.unidad_etiqueta || 'unidades';
            var kgPres = extraPresentacion.stock_kg != null ? Number(extraPresentacion.stock_kg).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : null;
            txtDisp.textContent = 'Puede pedir hasta ' + Math.floor(stockMaxUnidades) + ' ' + stockUnidadEtiqueta + (kgPres ? ' (' + kgPres + ' kg)' : '');
            panelDisp.classList.remove('d-none');
            if (inputCantidad) inputCantidad.removeAttribute('max');
        } else {
            stockMaxUnidades = extraPresentacion ? parseFloat(extraPresentacion.stock_unidades || 0) : null;
            panelDisp?.classList.add('d-none');
            if (inputCantidad) inputCantidad.removeAttribute('max');
        }
        validarStockCantidad();
    }

    function validarStockCantidad() {
        actualizarPreviewCapacidadPdv();

        var wrap = document.getElementById('wrapCantidad');
        var alerta = document.getElementById('alertaStockExcedido');
        var txtAlerta = document.getElementById('txtAlertaStockExcedido');
        var extra = extraPresentacionSeleccionada();
        document.getElementById('panelEsperaStock')?.classList.add('d-none');

        if (!document.getElementById('cantidad') || !document.getElementById('selectPresentacionMayorista')?.value) {
            wrap?.classList.remove('is-invalid');
            alerta?.classList.add('d-none');
            return true;
        }

        var qty = cantidadSolicitada();
        var disp = extra ? parseFloat(extra.stock_unidades || 0) : 0;
        var tieneStock = extra && extra.tiene_stock && disp > 0;

        if (qty <= 0 || !extra) {
            wrap?.classList.remove('is-invalid');
            alerta?.classList.add('d-none');
            return true;
        }

        if (tieneStock && qty > disp) {
            wrap?.classList.add('is-invalid');
            if (txtAlerta) {
                txtAlerta.textContent = 'Supera el stock disponible (' + Math.floor(disp) + ' ' + (extra.unidad_etiqueta || 'unid.') + ').';
            }
            alerta?.classList.remove('d-none');
            return true;
        }

        if (!tieneStock) {
            wrap?.classList.add('is-invalid');
            if (txtAlerta) txtAlerta.textContent = 'Sin stock ahora para esta cantidad.';
            alerta?.classList.remove('d-none');
            return true;
        }

        wrap?.classList.remove('is-invalid');
        alerta?.classList.add('d-none');
        return true;
    }

    function puntosMapaFiltrados() {
        if (!esAdmin || !minoristaSeleccionadoId) return puntosVentaMapa;
        return puntosVentaMapa.filter(function (p) { return String(p.minorista_usuarioid) === String(minoristaSeleccionadoId); });
    }

    function getPdvWrap() { return document.getElementById('selector_wrap_dist_punto_venta'); }

    function syncBloqueoDestinoAdmin() {
        if (!esAdmin) return;
        var wrap = document.getElementById('wrapPdvSelector');
        var btnMapa = document.getElementById('btnVerMapaPdvDestino');
        var bloqueado = !minoristaSeleccionadoId;
        if (wrap) wrap.classList.toggle('opacity-50', bloqueado);
        if (btnMapa) btnMapa.disabled = bloqueado || !puntosMapaFiltrados().length;
    }

    function paramsPuntoVenta() {
        return esAdmin && minoristaSeleccionadoId ? { minorista_usuarioid: String(minoristaSeleccionadoId) } : {};
    }

    function aplicarPdvSeleccionado(pdv) {
        if (!pdv || !pdv.id) return;
        var wrap = getPdvWrap();
        if (!wrap) return;
        wrap.querySelector('.selector-catalogo-value').value = pdv.id;
        var display = wrap.querySelector('.selector-catalogo-label');
        if (display) { display.value = pdv.label; display.classList.remove('text-muted', 'is-empty'); }
        pdvSeleccionadoId = String(pdv.id);
        document.querySelectorAll('#listaPdvMapaDestino .pdv-mapa-chip').forEach(function (chip) {
            chip.classList.toggle('is-active', chip.getAttribute('data-pdv-id') === pdvSeleccionadoId);
        });
        actualizarPreviewCapacidadPdv();
    }

    function crearIconoPdv(pdv, seleccionado) {
        var nombre = (pdv.label || 'PDV').replace(/"/g, '&quot;');
        var cls = seleccionado ? 'pdv-mapa-pin is-selected' : 'pdv-mapa-pin';
        return L.divIcon({
            className: '',
            html: '<div class="' + cls + '" data-pdv-id="' + pdv.id + '">' +
                '<div class="pdv-mapa-pin__bubble">' +
                '<div class="pdv-mapa-pin__icon"><i class="fas fa-store"></i></div>' +
                '<span class="pdv-mapa-pin__label">' + nombre + '</span>' +
                '</div></div>',
            iconSize: [120, 58],
            iconAnchor: [60, 42],
            popupAnchor: [0, -42],
        });
    }

    function renderListaPdvMapa(puntos, onPick) {
        var lista = document.getElementById('listaPdvMapaDestino');
        if (!lista) return;
        lista.innerHTML = '';
        puntos.forEach(function (pdv) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'pdv-mapa-chip' + (String(pdv.id) === String(pdvSeleccionadoId) ? ' is-active' : '');
            chip.setAttribute('data-pdv-id', pdv.id);
            chip.textContent = pdv.label;
            chip.addEventListener('click', function () { onPick(pdv); });
            lista.appendChild(chip);
        });
    }

    function seleccionarPdvDesdeMapa(pdv) {
        aplicarPdvSeleccionado(pdv);
        window.jQuery('#modalMapaPdvDestino').modal('hide');
    }

    function actualizarUnidadPresentacion(extra) {
        var unidad = (extra && extra.unidad_etiqueta) ? String(extra.unidad_etiqueta).trim() : 'unidades';
        var badge = document.getElementById('badgeUnidad');
        var ayuda = document.getElementById('txtAyudaCantidad');
        if (badge) badge.textContent = unidad;
        if (ayuda) ayuda.textContent = 'Indique cuántas ' + unidad + ' necesita.';
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;
        var mapaPdv = null, capasPdvMapa = null;

        document.getElementById('btnVerMapaPdvDestino')?.addEventListener('click', function () {
            if (esAdmin && !minoristaSeleccionadoId) {
                avisoValidacion('Minorista requerido', 'Primero seleccione el minorista.');
                return;
            }
            if (!puntosMapaFiltrados().length) return;
            window.jQuery('#modalMapaPdvDestino').modal('show');
        });

        window.jQuery('#modalMapaPdvDestino').on('shown.bs.modal', function () {
            if (!window.L) return;
            var puntos = puntosMapaFiltrados();
            if (!mapaPdv) {
                mapaPdv = L.map('mapaPdvDestino').setView([-17.7833, -63.1821], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OSM' }).addTo(mapaPdv);
                capasPdvMapa = L.layerGroup().addTo(mapaPdv);
            }
            setTimeout(function () {
                mapaPdv.invalidateSize();
                capasPdvMapa.clearLayers();
                renderListaPdvMapa(puntos, seleccionarPdvDesdeMapa);
                var bounds = [];
                puntos.forEach(function (pdv) {
                    if (pdv.lat == null || pdv.lng == null) return;
                    var lat = parseFloat(pdv.lat);
                    var lng = parseFloat(pdv.lng);
                    var seleccionado = String(pdv.id) === String(pdvSeleccionadoId);
                    L.marker([lat, lng], { icon: crearIconoPdv(pdv, seleccionado) })
                        .bindTooltip('<strong>' + pdv.label + '</strong>' + (pdv.resumen ? '<br><span style="font-size:11px">' + pdv.resumen + '</span>' : ''), { direction: 'top', offset: [0, -36] })
                        .on('click', function () { seleccionarPdvDesdeMapa(pdv); })
                        .addTo(capasPdvMapa);
                    bounds.push([lat, lng]);
                });
                if (bounds.length === 1) {
                    mapaPdv.setView(bounds[0], 15);
                } else if (bounds.length > 1) {
                    mapaPdv.fitBounds(bounds, { padding: [48, 48], maxZoom: 15 });
                }
            }, 180);
        });

        @if($esAdmin ?? false)
        CatalogoSelector.register('dist_minorista', {
            endpoint: @json(route('catalogo-selector.usuarios')),
            title: 'Minorista', params: { roles: 'minorista' },
            onSelect: function (item) {
                minoristaSeleccionadoId = item.id ? String(item.id) : '';
                if (CatalogoSelector.instances.dist_punto_venta) CatalogoSelector.instances.dist_punto_venta.params = paramsPuntoVenta();
                CatalogoSelector.clear('dist_punto_venta');
                syncBloqueoDestinoAdmin();
            },
        });
        CatalogoSelector.register('dist_punto_venta', {
            endpoint: @json(route('catalogo-selector.puntos-venta')),
            title: 'Puntos de venta', params: paramsPuntoVenta(),
        });
        syncBloqueoDestinoAdmin();
        @endif

        CatalogoSelector.register('dist_producto_mayorista', {
            endpoint: @json(route('catalogo-selector.productos-mayorista-pdv')),
            title: 'Productos por mayorista',
            onSelect: function (item) {
                aplicarSeleccionProductoMayorista(item);
            },
            onMapClick: function (item) {
                abrirMapaMayoristaDesdeCatalogo(item.extra || {});
            },
        });

        document.getElementById('selector_wrap_dist_producto_mayorista')?.addEventListener('selector-catalogo:change', function (e) {
            if (!e.detail?.id) {
                nombreProductoActual = '';
                productoMayoristaExtra = null;
                document.getElementById('insumoid_real').value = '';
                document.getElementById('almacen_mayorista_origenid').value = '';
                document.getElementById('panelProductoMeta')?.classList.add('d-none');
                limpiarPresentacion();
                return;
            }
            aplicarSeleccionProductoMayorista(e.detail);
        });

        document.getElementById('selectPresentacionMayorista')?.addEventListener('change', function () {
            var id = this.value;
            manejarCambioPresentacion(id ? presentacionesExtra[id] : null);
        });

        if (oldInsumoId) {
            productoMayoristaExtra = {
                insumoid: oldInsumoId,
                almacen_mayorista_origenid: oldAlmacenMayoristaId || null,
            };
            cargarPresentaciones(oldInsumoId, oldPresentacionId, productoMayoristaExtra);
        }

        actualizarPreviewCapacidadPdv();

        document.getElementById('cantidad')?.addEventListener('input', validarStockCantidad);

        document.getElementById('selector_wrap_dist_punto_venta')?.addEventListener('selector-catalogo:change', function () {
            pdvSeleccionadoId = getPdvSeleccionadoId();
            actualizarPreviewCapacidadPdv();
        });

        document.getElementById('formPedidoDist').addEventListener('submit', function (e) {
            var form = this;
            if (esAdmin && !document.querySelector('#selector_wrap_dist_minorista .selector-catalogo-value')?.value) {
                e.preventDefault();
                avisoValidacion('Datos incompletos', 'Seleccione el minorista.');
                return;
            }
            if (!document.querySelector('#selector_wrap_dist_punto_venta .selector-catalogo-value')?.value) {
                e.preventDefault();
                avisoValidacion('Datos incompletos', 'Seleccione un punto de venta.');
                return;
            }
            if (!document.getElementById('fecha_entrega_deseada')?.value) {
                e.preventDefault();
                avisoValidacion('Datos incompletos', 'Indique la fecha de entrega deseada.');
                return;
            }
            if (!document.querySelector('#selector_wrap_dist_producto_mayorista .selector-catalogo-value')?.value) {
                e.preventDefault();
                avisoValidacion('Producto requerido', 'Seleccione un producto disponible en almacén mayorista.');
                return;
            }
            if (!document.getElementById('selectPresentacionMayorista')?.value) {
                e.preventDefault();
                avisoValidacion('Presentación requerida', 'Seleccione la presentación del producto.');
                return;
            }
            if (!cantidadSolicitada()) {
                e.preventDefault();
                avisoValidacion('Cantidad requerida', 'Indique cuántas unidades necesita.');
                return;
            }
            if (capacidadPdvExcede) {
                e.preventDefault();
                avisoValidacion(
                    'Capacidad del almacén excedida',
                    'El pedido supera el espacio disponible en el almacén del punto de venta. Reduzca la cantidad o elija otro punto de venta.'
                );
                return;
            }
            var extra = extraPresentacionSeleccionada();
            if (necesitaEsperaStock(extra, cantidadSolicitada())) {
                e.preventDefault();
                var qty = cantidadSolicitada();
                var unidad = extra?.unidad_etiqueta || 'unidades';
                var disp = parseFloat(extra?.stock_unidades || 0);
                var mensaje = disp > 0
                    ? 'Solicita ' + qty + ' ' + unidad + ' y solo hay ' + Math.floor(disp) + ' disponibles. Reduzca la cantidad o elija otra presentación.'
                    : 'No hay stock para esta presentación. Elija otra presentación o producto con stock.';
                avisoValidacion('Stock insuficiente', mensaje);
            }
        });
    });
})();
</script>
@endpush
