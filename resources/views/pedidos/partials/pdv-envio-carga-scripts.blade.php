<script>
(function () {
    const presentacionesEndpoint = @json(route('catalogo-selector.presentaciones-producto'));
    const insumosEndpoint = @json(route('catalogo-selector.insumos'));
    const stockLoteEndpoint = @json(route('catalogo-selector.stock-presentacion-lote'));
    const oldDetalles = @json(old('detalles', []));
    const esOrigenMayorista = @json($esOrigenMayoristaPdv ?? $esAdminPdv ?? false);
    let filaSeq = 0;

    function fmtKg(n) {
        return Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function el(id) { return document.getElementById(id); }

    function recogidasActivas() {
        if (typeof window.recogidasConAlmacenPdv === 'function') {
            return window.recogidasConAlmacenPdv();
        }
        const alm = document.querySelector('#selector_wrap_pdv_unificado_almacen .selector-catalogo-value')?.value;
        if (!alm) return [];
        return [{
            key: 'principal',
            num: 1,
            almacenId: String(alm),
            almacenLabel: document.getElementById('txtNombreOrigenPdv')?.value || 'Recogida 1',
        }];
    }

    function almacenDeFila(fila) {
        return fila?.getAttribute('data-almacen-mayorista-id')
            || fila?.closest('.pdv-recogida-productos-card')?.getAttribute('data-almacen-mayorista-id')
            || '';
    }

    function paramsProductoCatalogo(almacenId) {
        const p = { ambito_mayorista: '1', solo_con_stock: '1' };
        if (esOrigenMayorista) {
            p.requiere_almacen = '1';
            if (almacenId) p.almacenid = String(almacenId);
        }
        return p;
    }

    function avisoPdv(mensaje) {
        if (window.ModalConfirmar?.aviso) {
            ModalConfirmar.aviso({ mensaje: mensaje, titulo: 'Aviso', tono: 'warning' });
        } else {
            window.alert(mensaje);
        }
    }

    function claveConsolidacionJs(nombre, lote, empaqueNombre, tipoEmpaque) {
        return [String(nombre || '').trim().toLowerCase(), String(lote || '').trim().toLowerCase(),
            String(empaqueNombre || '').trim().toLowerCase() + '|' + String(tipoEmpaque || '')].join('||');
    }

    function claveLineaFila(fila) {
        const nombre = fila.querySelector('.selector-catalogo-label')?.value?.split('—')[0]?.trim() || '';
        const extra = extraFila(fila);
        const loteOpt = fila.querySelector('[data-field="inventario_lote"]')?.selectedOptions?.[0];
        const lote = loteOpt?.dataset?.referenciaLote || extra?.referencia_lote || '';
        const empaque = extra?.presentacion_nombre || '';
        const tipoEmp = extra?.tipo_empaque || extra?.tipoempaqueid || '';
        return claveConsolidacionJs(nombre, lote, empaque, tipoEmp);
    }

    function presentacionUsadaEnOtraFila(presId, loteId, almacenId, excluirFila) {
        if (!presId) return false;
        let usada = false;
        document.querySelectorAll('.pdv-producto-row').forEach(function (otra) {
            if (otra === excluirFila) return;
            if (almacenDeFila(otra) !== String(almacenId)) return;
            const pres = otra.querySelector('[data-field="presentacion"]')?.value;
            const lote = otra.querySelector('[data-field="inventario_lote"]')?.value || '';
            if (String(pres) === String(presId) && String(lote) === String(loteId || '')) {
                usada = true;
            }
        });
        return usada;
    }

    function cantidadReservadaEnOtrasFilas(presId, loteId, almacenId, excluirFila) {
        if (!presId) return 0;
        let total = 0;
        document.querySelectorAll('.pdv-producto-row').forEach(function (otra) {
            if (otra === excluirFila) return;
            if (almacenDeFila(otra) !== String(almacenId)) return;
            if (String(otra.querySelector('[data-field="presentacion"]')?.value || '') !== String(presId)) return;
            const loteOtra = otra.querySelector('[data-field="inventario_lote"]')?.value || '';
            if (String(loteOtra) !== String(loteId || '')) return;
            const qty = parseFloat(otra.querySelector('[data-field="cantidad"]')?.value || '0');
            if (Number.isFinite(qty) && qty > 0) total += qty;
        });
        return total;
    }

    function stockDisponiblePresentacion(extra, presId, loteId, almacenId, excluirFila) {
        if (!extra || !presId) return 0;
        let base = parseFloat(extra.stock_unidades || 0);
        if (loteId && filaLoteExtra(excluirFila, loteId)) {
            base = parseFloat(filaLoteExtra(excluirFila, loteId).cantidad_unidades || 0);
        }
        if (!extra.tiene_stock && base <= 0) return 0;
        const reservado = cantidadReservadaEnOtrasFilas(presId, loteId, almacenId, excluirFila);
        return Math.max(0, Math.floor(base - reservado));
    }

    function filaLoteExtra(fila, loteId) {
        if (!fila?.lotesExtra) return null;
        return fila.lotesExtra[String(loteId)] || null;
    }

    function extraFila(fila) {
        const id = fila.querySelector('[data-field="presentacion"]')?.value || '';
        return id ? (fila.presentacionesExtra?.[id] || null) : null;
    }

    function actualizarFila(fila) {
        const presId = fila.querySelector('[data-field="presentacion"]')?.value || '';
        const loteId = fila.querySelector('[data-field="inventario_lote"]')?.value || '';
        const extra = extraFila(fila);
        const almacenId = almacenDeFila(fila);
        const lblCant = fila.querySelector('.js-lbl-cantidad-pdv');
        const stockSm = fila.querySelector('[data-field="stock-panel"]');
        const equiv = fila.querySelector('[data-field="equiv-kg"]');
        const alerta = fila.querySelector('[data-field="stock-alerta"]');
        const cantInput = fila.querySelector('[data-field="cantidad"]');
        const qty = parseFloat(String(cantInput?.value || '').trim()) || 0;
        const unidad = extra?.unidad_etiqueta || 'unidades';
        const disp = stockDisponiblePresentacion(extra, presId, loteId, almacenId, fila);

        if (lblCant) {
            lblCant.innerHTML = 'Cantidad <span class="text-muted font-weight-normal">(' + unidad + ')</span>';
        }

        if (extra && disp > 0) {
            stockSm?.classList.remove('d-none');
            if (stockSm) {
                let txt = 'Disponible: hasta ' + disp + ' ' + unidad;
                const pesoUnit = parseFloat(extra.peso_neto_kg || 0);
                if (pesoUnit > 0) txt += ' (' + fmtKg(disp * pesoUnit) + ' kg)';
                stockSm.textContent = txt;
            }
        } else {
            stockSm?.classList.add('d-none');
        }

        if (extra && qty > 0 && parseFloat(extra.peso_neto_kg || 0) > 0) {
            equiv?.classList.remove('d-none');
            if (equiv) equiv.textContent = 'Equivale a ' + fmtKg(qty * extra.peso_neto_kg) + ' kg';
        } else {
            equiv?.classList.add('d-none');
        }

        const excede = !!(cantInput?.value && qty > disp);
        fila.classList.toggle('is-stock-error', excede);
        equiv?.classList.toggle('is-error', excede);
        if (alerta) {
            alerta.classList.toggle('d-none', !excede);
            if (excede) {
                alerta.innerHTML = disp > 0
                    ? '<i class="fas fa-exclamation-triangle"></i> Supera el stock disponible (' + disp + ' ' + unidad + ').'
                    : '<i class="fas fa-exclamation-triangle"></i> Sin stock disponible para esta presentación/lote.';
            }
        }
        if (cantInput && disp > 0) cantInput.max = String(disp);
        else cantInput?.removeAttribute('max');
    }

    function cargarLotesFila(fila, presId, preseleccionId) {
        const selLote = fila.querySelector('[data-field="inventario_lote"]');
        const bloqueLote = fila.querySelector('.js-bloque-lote');
        const almacenId = almacenDeFila(fila);
        if (!selLote || !bloqueLote || !presId || !almacenId) {
            bloqueLote?.classList.add('d-none');
            return Promise.resolve();
        }

        selLote.disabled = true;
        selLote.innerHTML = '<option value="">Cargando lotes…</option>';
        bloqueLote.classList.remove('d-none');

        const params = new URLSearchParams({
            almacenid: String(almacenId),
            insumo_presentacionid: String(presId),
        });

        return fetch(stockLoteEndpoint + '?' + params.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (payload) {
                fila.lotesExtra = {};
                const lotes = payload.data || [];
                selLote.innerHTML = '';
                if (!lotes.length) {
                    selLote.innerHTML = '<option value="">Sin stock por lote</option>';
                    selLote.disabled = true;
                    return;
                }
                if (lotes.length > 1) {
                    selLote.appendChild(new Option('Seleccione lote…', ''));
                }
                lotes.forEach(function (l) {
                    fila.lotesExtra[String(l.id)] = l.extra || {};
                    const opt = new Option(l.label, String(l.id));
                    if (l.extra?.referencia_lote) {
                        opt.dataset.referenciaLote = l.extra.referencia_lote;
                    }
                    selLote.appendChild(opt);
                });
                selLote.disabled = false;
                let elegido = preseleccionId ? String(preseleccionId) : '';
                if (elegido && selLote.querySelector('option[value="' + elegido + '"]')) {
                    selLote.value = elegido;
                } else if (lotes.length === 1) {
                    selLote.value = String(lotes[0].id);
                }
            })
            .catch(function () {
                selLote.innerHTML = '<option value="">Error al cargar lotes</option>';
                selLote.disabled = true;
            });
    }

    function cargarPresentacionesFila(fila, insumoId, preseleccionId, lotePreseleccion) {
        const select = fila.querySelector('[data-field="presentacion"]');
        const ayuda = fila.querySelector('[data-field="pres-ayuda"]');
        const vacio = fila.querySelector('[data-field="pres-vacia"]');
        const pick = fila.querySelector('[data-field="pres-pick"]');
        const almacenId = almacenDeFila(fila);
        if (!select || !insumoId) {
            if (select) {
                select.innerHTML = '<option value="">Elegir producto primero…</option>';
                select.disabled = true;
            }
            return Promise.resolve();
        }

        select.disabled = true;
        select.innerHTML = '<option value="">Cargando…</option>';
        ayuda?.classList.remove('d-none');
        vacio?.classList.add('d-none');
        fila.presentacionesExtra = {};

        const params = new URLSearchParams({
            insumoid: String(insumoId),
            catalogo_mayorista_pdv: '1',
            per_page: '50',
            page: '1',
        });
        if (almacenId) params.set('almacen_mayorista_origenid', almacenId);

        return fetch(presentacionesEndpoint + '?' + params.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Error al cargar presentaciones');
                return r.json();
            })
            .then(function (json) {
                const items = json.data || [];
                ayuda?.classList.add('d-none');
                select.innerHTML = '';
                if (!items.length) {
                    select.innerHTML = '<option value="">Sin presentaciones disponibles</option>';
                    select.disabled = true;
                    vacio?.classList.remove('d-none');
                    pick?.classList.add('is-locked');
                    return;
                }
                select.appendChild(new Option('Seleccione presentación…', ''));
                items.forEach(function (item) {
                    fila.presentacionesExtra = fila.presentacionesExtra || {};
                    fila.presentacionesExtra[String(item.id)] = Object.assign({}, item.extra || {}, {
                        presentacion_nombre: item.label,
                        tiene_stock: true,
                    });
                    let label = item.label || ('Presentación #' + item.id);
                    const disp = stockDisponiblePresentacion(item.extra, String(item.id), '', almacenId, fila);
                    if (item.extra?.stock_unidades > 0 && disp > 0) {
                        label += ' · disp. ' + disp + ' ' + (item.extra.unidad_etiqueta || 'unid.');
                    }
                    select.appendChild(new Option(label, String(item.id)));
                });
                select.disabled = false;
                pick?.classList.remove('is-locked');
                let elegido = preseleccionId ? String(preseleccionId) : '';
                if (elegido && fila.presentacionesExtra[elegido]) {
                    select.value = elegido;
                }
                const presFinal = select.value;
                if (presFinal) {
                    return cargarLotesFila(fila, presFinal, lotePreseleccion).then(function () {
                        actualizarFila(fila);
                    });
                }
                actualizarFila(fila);
            })
            .catch(function () {
                ayuda?.classList.add('d-none');
                select.innerHTML = '<option value="">No se pudieron cargar las presentaciones</option>';
                select.disabled = true;
            });
    }

    function renombrarCamposFilas() {
        let idx = 0;
        document.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
            fila.dataset.idx = String(idx);
            const ins = fila.querySelector('[data-field="insumoid"]');
            const pres = fila.querySelector('[data-field="presentacion"]');
            const lote = fila.querySelector('[data-field="inventario_lote"]');
            const alm = fila.querySelector('[data-field="almacen_mayorista_origenid"]');
            const cant = fila.querySelector('[data-field="cantidad"]');
            if (ins) ins.name = 'detalles[' + idx + '][insumoid]';
            if (pres) pres.name = 'detalles[' + idx + '][insumo_presentacionid]';
            if (lote) lote.name = 'detalles[' + idx + '][inventario_presentacion_loteid]';
            if (alm) alm.name = 'detalles[' + idx + '][almacen_mayorista_origenid]';
            if (cant) cant.name = 'detalles[' + idx + '][cantidad]';
            idx++;
        });
    }

    function crearFila(detalle, innerContainer, almacenId, recogidaKey) {
        detalle = detalle || {};
        almacenId = almacenId || detalle.almacen_mayorista_origenid || '';
        const idx = filaSeq++;
        const filaId = 'pdv_fila_' + idx;
        const selectorId = 'pdv_producto_' + idx;
        const container = innerContainer || el('pdv-productos-envio-container');
        if (!container) return null;

        const fila = document.createElement('div');
        fila.className = 'pdv-producto-row traslado-producto-row';
        fila.setAttribute('data-selector-id', selectorId);
        fila.setAttribute('data-almacen-mayorista-id', String(almacenId));
        fila.setAttribute('data-recogida-key', recogidaKey || 'principal');
        fila.innerHTML =
            '<input type="hidden" data-field="almacen_mayorista_origenid" value="' + (almacenId || '') + '">' +
            '<div class="traslado-producto-row__head">' +
                '<label>Línea de producto</label>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-quitar-producto-pdv" title="Quitar línea">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>' +
            '<div class="form-row">' +
                '<div class="col-lg-3 col-md-6 mb-2 mb-lg-0">' +
                    '<span class="field-label">Producto</span>' +
                    '<div class="selector-catalogo-wrapper flex-grow-1 w-100 mb-0 selector-catalogo--filtros" id="selector_wrap_' + selectorId + '">' +
                        '<div class="selector-filtros-field">' +
                            '<input type="text" class="selector-filtros-field__input selector-catalogo-label is-empty" readonly placeholder="Buscar producto…" value="">' +
                            '<input type="hidden" data-field="insumoid" class="selector-catalogo-value" value="' + (detalle.insumoid || '') + '">' +
                            '<div class="selector-filtros-field__actions">' +
                                '<button type="button" class="selector-filtros-field__open" data-selector-open="' + selectorId + '">' +
                                    '<i class="fas fa-chevron-down"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-lg-3 col-md-6 mb-2 mb-lg-0">' +
                    '<span class="field-label">Presentación</span>' +
                    '<div class="pdv-envio-presentacion-pick is-locked" data-field="pres-pick">' +
                        '<select data-field="presentacion" class="form-control form-control-sm" disabled><option value="">Elegir producto primero…</option></select>' +
                        '<small data-field="pres-ayuda" class="form-text text-muted mb-0 d-none">Cargando presentaciones…</small>' +
                        '<small data-field="pres-vacia" class="form-text text-muted mb-0 d-none">Sin presentaciones con stock.</small>' +
                    '</div>' +
                '</div>' +
                '<div class="col-lg-2 col-md-6 mb-2 mb-lg-0 js-bloque-lote d-none">' +
                    '<span class="field-label">Lote</span>' +
                    '<select data-field="inventario_lote" class="form-control form-control-sm" disabled><option value="">—</option></select>' +
                '</div>' +
                '<div class="col-lg-4 col-md-6 mb-2 mb-lg-0">' +
                    '<span class="field-label js-lbl-cantidad-pdv">Cantidad <span class="text-muted font-weight-normal">(unidades)</span></span>' +
                    '<input type="number" step="1" min="1" data-field="cantidad" class="form-control form-control-sm" placeholder="Ej: 10" value="' + (detalle.cantidad || '') + '">' +
                    '<small data-field="stock-panel" class="lbl-stock-traslado d-none"></small>' +
                    '<small data-field="equiv-kg" class="lbl-equiv-traslado d-none"></small>' +
                    '<small data-field="stock-alerta" class="pdv-envio-stock-alerta d-none"><i class="fas fa-exclamation-triangle"></i> Supera el stock disponible.</small>' +
                '</div>' +
            '</div>';

        container.appendChild(fila);

        fila.querySelector('.btn-quitar-producto-pdv')?.addEventListener('click', function () {
            const inner = fila.closest('.pdv-productos-inner');
            if (inner && inner.querySelectorAll('.pdv-producto-row').length <= 1) return;
            fila.remove();
            renombrarCamposFilas();
            refrescarTodasLasFilasPdv();
        });

        fila.querySelector('[data-field="presentacion"]')?.addEventListener('change', function () {
            const presId = this.value;
            const loteId = fila.querySelector('[data-field="inventario_lote"]')?.value || '';
            if (presId && presentacionUsadaEnOtraFila(presId, loteId, almacenDeFila(fila), fila)) {
                avisoPdv('Esa presentación y lote ya están en otra línea del mismo almacén.');
                this.value = '';
            }
            cargarLotesFila(fila, presId, null).then(function () {
                actualizarFila(fila);
                refrescarTodasLasFilasPdv();
            });
        });

        fila.querySelector('[data-field="inventario_lote"]')?.addEventListener('change', function () {
            const presId = fila.querySelector('[data-field="presentacion"]')?.value;
            const loteId = this.value;
            if (presId && presentacionUsadaEnOtraFila(presId, loteId, almacenDeFila(fila), fila)) {
                avisoPdv('Esa presentación y lote ya están en otra línea del mismo almacén.');
                this.value = '';
            }
            actualizarFila(fila);
            refrescarTodasLasFilasPdv();
        });

        fila.querySelector('[data-field="cantidad"]')?.addEventListener('input', function () {
            actualizarFila(fila);
            refrescarTodasLasFilasPdv();
        });

        if (window.CatalogoSelector) {
            CatalogoSelector.register(selectorId, {
                endpoint: insumosEndpoint,
                title: 'Producto mayorista',
                searchPlaceholder: 'Nombre del producto…',
                params: paramsProductoCatalogo(almacenId),
                rowIcon: 'fa-box',
                theme: 'pdv',
                colNombre: 'Producto',
                onSelect: function (item) {
                    const val = fila.querySelector('[data-field="insumoid"]');
                    const lbl = fila.querySelector('.selector-catalogo-label');
                    if (val) val.value = item.id;
                    if (lbl) {
                        lbl.value = item.label;
                        lbl.classList.remove('is-empty');
                    }
                    cargarPresentacionesFila(fila, item.id, null, null);
                },
            });
            document.getElementById('selector_wrap_' + selectorId)?.addEventListener('selector-catalogo:change', function (e) {
                const det = e.detail || {};
                if (det.id) cargarPresentacionesFila(fila, det.id, null, null);
            });
        }

        renombrarCamposFilas();

        if (detalle.insumoid) {
            cargarPresentacionesFila(
                fila,
                detalle.insumoid,
                detalle.insumo_presentacionid || null,
                detalle.inventario_presentacion_loteid || null
            );
        }

        return fila;
    }

    function crearTarjetaRecogida(rec) {
        const card = document.createElement('div');
        card.className = 'pdv-recogida-productos-card traslado-recogida-productos-card border rounded p-3 mb-3';
        card.setAttribute('data-recogida-key', rec.key);
        card.setAttribute('data-almacen-mayorista-id', rec.almacenId);
        card.innerHTML =
            '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">' +
                '<strong class="lbl-pdv-recogida-titulo small mb-0"></strong>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-agregar-producto-pdv">' +
                    '<i class="fas fa-plus mr-1"></i> Agregar producto' +
                '</button>' +
            '</div>' +
            '<div class="pdv-productos-inner"></div>';
        card.querySelector('.lbl-pdv-recogida-titulo').innerHTML =
            '<i class="fas fa-warehouse mr-1 text-warning"></i> Recogida ' + rec.num + ' — ' + (rec.almacenLabel || 'Almacén mayorista');
        card.querySelector('.btn-agregar-producto-pdv').addEventListener('click', function () {
            crearFila({}, card.querySelector('.pdv-productos-inner'), rec.almacenId, rec.key);
            renombrarCamposFilas();
        });
        return card;
    }

    function syncRecogidas() {
        const container = el('pdv-productos-recogida-container');
        if (!container) return;

        const recogidas = recogidasActivas();
        if (!recogidas.length) {
            container.innerHTML = '<p class="text-muted small mb-0 env-carga-compact"><i class="fas fa-info-circle mr-1"></i> Complete el paso <strong>Ruta</strong> para indicar productos por cada almacén mayorista.</p>';
            return;
        }
        if (container.querySelector('.env-carga-compact')) {
            container.innerHTML = '';
        }

        const keysActivas = new Set();
        recogidas.forEach(function (rec) {
            keysActivas.add(rec.key);
            let card = container.querySelector('[data-recogida-key="' + rec.key + '"]');
            if (!card) {
                card = crearTarjetaRecogida(rec);
                container.appendChild(card);
            } else {
                card.setAttribute('data-almacen-mayorista-id', rec.almacenId);
                card.querySelector('.lbl-pdv-recogida-titulo').innerHTML =
                    '<i class="fas fa-warehouse mr-1 text-warning"></i> Recogida ' + rec.num + ' — ' + (rec.almacenLabel || 'Almacén mayorista');
            }
            const inner = card.querySelector('.pdv-productos-inner');
            inner?.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
                fila.setAttribute('data-almacen-mayorista-id', rec.almacenId);
                const hiddenAlm = fila.querySelector('[data-field="almacen_mayorista_origenid"]');
                if (hiddenAlm) hiddenAlm.value = rec.almacenId;
                const selId = fila.getAttribute('data-selector-id');
                const cfg = window.CatalogoSelector?.instances?.[selId];
                if (cfg) cfg.params = paramsProductoCatalogo(rec.almacenId);
            });
            if (inner && !inner.querySelector('.pdv-producto-row')) {
                crearFila({}, inner, rec.almacenId, rec.key);
            }
        });

        container.querySelectorAll('.pdv-recogida-productos-card').forEach(function (card) {
            if (!keysActivas.has(card.getAttribute('data-recogida-key'))) {
                card.remove();
            }
        });

        renombrarCamposFilas();
        window.syncOrdenRecogidaPdvHidden?.();
    }

    function refrescarTodasLasFilasPdv() {
        document.querySelectorAll('.pdv-producto-row').forEach(actualizarFila);
        window.PedidoFase2?.validarCapacidadVehiculo?.();
        window.PedidoFase2?.actualizarSugerenciaVehiculo?.();
    }

    function validarFilas(opciones) {
        const mostrarModal = !!(opciones && opciones.mostrarModal);
        const filas = document.querySelectorAll('.pdv-producto-row');
        if (!filas.length) {
            return { ok: false, mensaje: 'Agregue al menos un producto al envío.' };
        }
        const clavesVistas = new Set();
        let algunaValida = false;
        for (const fila of filas) {
            const ins = fila.querySelector('[data-field="insumoid"]')?.value;
            const pres = fila.querySelector('[data-field="presentacion"]')?.value;
            const loteSel = fila.querySelector('[data-field="inventario_lote"]');
            const loteRequerido = !!(loteSel && !loteSel.disabled && loteSel.options.length > 1);
            const lote = loteSel?.value || '';
            const qty = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
            if (!ins || !pres || !Number.isFinite(qty) || qty <= 0) continue;
            if (loteRequerido && !lote) continue;
            const clave = claveLineaFila(fila) + '|' + almacenDeFila(fila);
            if (clavesVistas.has(clave)) {
                const msg = 'Hay líneas duplicadas (mismo producto, lote y empaque en el mismo almacén).';
                if (mostrarModal && window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Línea duplicada', text: msg });
                    return { ok: false, mensaje: msg, modalMostrado: true };
                }
                return { ok: false, mensaje: msg };
            }
            clavesVistas.add(clave);
            actualizarFila(fila);
            if (fila.classList.contains('is-stock-error')) {
                const msg = 'Revise stock, lote y cantidad en cada línea.';
                if (mostrarModal && window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Stock insuficiente', text: msg });
                    return { ok: false, mensaje: msg, modalMostrado: true };
                }
                return { ok: false, mensaje: msg };
            }
            algunaValida = true;
        }
        if (!algunaValida) {
            return { ok: false, mensaje: 'Indique producto, presentación, lote (si aplica) y cantidad en al menos una línea.' };
        }
        window.syncOrdenRecogidaPdvHidden?.();
        return { ok: true };
    }

    function consolidarLineasResumen() {
        const grupos = {};
        document.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
            const nombre = fila.querySelector('.selector-catalogo-label')?.value?.split('—')[0]?.trim() || 'Producto';
            const extra = extraFila(fila);
            const loteOpt = fila.querySelector('[data-field="inventario_lote"]')?.selectedOptions?.[0];
            const lote = loteOpt?.dataset?.referenciaLote || loteOpt?.textContent?.split('·')[0]?.trim() || '';
            const pres = extra?.presentacion_nombre || '';
            const qty = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
            if (qty <= 0) return;
            const peso = parseFloat(extra?.peso_neto_kg || 0);
            const kgLinea = peso > 0 ? qty * peso : 0;
            const unidad = extra?.unidad_etiqueta || 'unidades';
            const clave = claveConsolidacionJs(nombre, lote, pres, extra?.tipo_empaque || '');
            if (!grupos[clave]) {
                grupos[clave] = { nombre: nombre, lote: lote, pres: pres, qty: 0, kg: 0, unidad: unidad };
            }
            grupos[clave].qty += qty;
            grupos[clave].kg += kgLinea;
        });
        return Object.values(grupos);
    }

    function resumenItems() {
        const items = [];
        let kg = 0;
        consolidarLineasResumen().forEach(function (g) {
            kg += g.kg;
            let titulo = g.nombre;
            if (g.lote) titulo += ' - ' + g.lote;
            let det = titulo + ' / ' + g.qty.toLocaleString('es-BO') + ' ' + g.unidad;
            if (g.kg > 0) det += ' (' + fmtKg(g.kg) + ' kg)';
            items.push(det);
        });
        return { items: items, kg: kg };
    }

    function textoResumen() {
        const carga = resumenItems();
        return carga.items.length ? carga.items.join(' | ') : '—';
    }

    async function poblarFilaDesdePreseleccion(fila, prod) {
        const selId = fila.getAttribute('data-selector-id');
        const val = fila.querySelector('[data-field="insumoid"]');
        const lbl = fila.querySelector('.selector-catalogo-label');
        if (window.CatalogoSelector && selId) {
            CatalogoSelector.setValue(selId, prod.id, prod.label);
        } else {
            if (val) val.value = prod.id;
            if (lbl) {
                lbl.value = prod.label;
                lbl.classList.remove('is-empty');
            }
        }
        await cargarPresentacionesFila(fila, prod.id, null, null);
    }

    async function aplicarPreseleccion(productos) {
        if (!Array.isArray(productos) || !productos.length) return;
        syncRecogidas();

        async function procesarProd(prod) {
            const key = prod.recogidaKey || 'principal';
            const almacenId = String(prod.almacenId || '');
            const card = document.querySelector('.pdv-recogida-productos-card[data-recogida-key="' + key + '"]');
            const inner = card?.querySelector('.pdv-productos-inner');
            if (!inner || !almacenId) return;

            let fila = Array.from(inner.querySelectorAll('.pdv-producto-row')).find(function (f) {
                return f.querySelector('[data-field="insumoid"]')?.value === String(prod.id);
            });

            if (!fila) {
                const vacia = Array.from(inner.querySelectorAll('.pdv-producto-row')).find(function (f) {
                    return !f.querySelector('[data-field="insumoid"]')?.value;
                });
                if (vacia && inner.querySelectorAll('.pdv-producto-row').length === 1) {
                    await poblarFilaDesdePreseleccion(vacia, prod);
                    return;
                }
                crearFila({ insumoid: prod.id }, inner, almacenId, key);
                fila = inner.querySelector('.pdv-producto-row:last-child');
                if (fila) await poblarFilaDesdePreseleccion(fila, prod);
                return;
            }

            await poblarFilaDesdePreseleccion(fila, prod);
        }

        for (const prod of productos) {
            await procesarProd(prod);
        }
        renombrarCamposFilas();
        refrescarTodasLasFilasPdv();
    }

    function init() {
        syncRecogidas();
        if (Array.isArray(oldDetalles) && oldDetalles.length) {
            oldDetalles.forEach(function (d) {
                const recKey = 'old-' + (d.almacen_mayorista_origenid || '0');
                let card = document.querySelector('[data-recogida-key="' + recKey + '"]');
                if (!card) {
                    const container = el('pdv-productos-recogida-container');
                    if (container && !container.querySelector('.env-carga-compact')) {
                        card = crearTarjetaRecogida({
                            key: recKey,
                            num: 1,
                            almacenId: d.almacen_mayorista_origenid,
                            almacenLabel: 'Almacén',
                        });
                        container.appendChild(card);
                    }
                }
                const inner = card?.querySelector('.pdv-productos-inner') || el('pdv-productos-envio-container');
                crearFila(d, inner, d.almacen_mayorista_origenid, recKey);
            });
        }
    }

    window.PdvEnvioCarga = {
        init: init,
        syncRecogidas: syncRecogidas,
        validar: validarFilas,
        textoResumen: textoResumen,
        resumenItems: resumenItems,
        aplicarPreseleccion: aplicarPreseleccion,
        syncAlPaso2: async function () {
            syncRecogidas();
            renombrarCamposFilas();
            refrescarTodasLasFilasPdv();
        },
        limpiar: function () {
            const c = el('pdv-productos-recogida-container');
            if (c) c.innerHTML = '';
            syncRecogidas();
        },
    };

    window.EnvioPdvProductos = window.PdvEnvioCarga;
    document.addEventListener('DOMContentLoaded', init);
})();
</script>
