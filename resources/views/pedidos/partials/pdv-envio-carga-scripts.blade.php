<script>
(function () {
    const presentacionesEndpoint = @json(route('catalogo-selector.presentaciones-producto'));
    const insumosEndpoint = @json(route('catalogo-selector.insumos'));
    const oldDetalles = @json(old('detalles', []));
    const esOrigenMayorista = @json($esOrigenMayoristaPdv ?? $esAdminPdv ?? false);
    let filaSeq = 0;

    function fmtKg(n) {
        return Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function el(id) { return document.getElementById(id); }

    function almacenMayoristaId() {
        const sel = document.querySelector('#selector_wrap_pdv_unificado_almacen .selector-catalogo-value');
        if (sel && sel.value) return String(sel.value);
        const hidden = document.querySelector('#form-pedido-dist-pdv [name="almacen_mayorista_origenid"]');
        return hidden && hidden.value ? String(hidden.value) : '';
    }

    function paramsProductoCatalogo() {
        const p = {
            ambito_mayorista: '1',
            solo_con_stock: '1',
        };
        if (esOrigenMayorista) {
            p.requiere_almacen = '1';
            const alm = almacenMayoristaId();
            if (alm) p.almacenid = alm;
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

    function presentacionUsadaEnOtraFila(presId, excluirFila) {
        if (!presId) return false;
        let usada = false;
        document.querySelectorAll('.pdv-producto-row').forEach(function (otra) {
            if (otra === excluirFila) return;
            if (String(otra.querySelector('[data-field="presentacion"]')?.value || '') === String(presId)) {
                usada = true;
            }
        });
        return usada;
    }

    function cantidadReservadaEnOtrasFilas(presId, excluirFila) {
        if (!presId) return 0;
        let total = 0;
        document.querySelectorAll('.pdv-producto-row').forEach(function (otra) {
            if (otra === excluirFila) return;
            if (String(otra.querySelector('[data-field="presentacion"]')?.value || '') !== String(presId)) return;
            const qty = parseFloat(otra.querySelector('[data-field="cantidad"]')?.value || '0');
            if (Number.isFinite(qty) && qty > 0) total += qty;
        });
        return total;
    }

    function stockDisponiblePresentacion(extra, presId, excluirFila) {
        if (!extra || !presId) return 0;
        const base = parseFloat(extra.stock_unidades || 0);
        if (!extra.tiene_stock || base <= 0) return 0;
        const reservado = cantidadReservadaEnOtrasFilas(presId, excluirFila);
        return Math.max(0, Math.floor(base - reservado));
    }

    function refrescarTodasLasFilasPdv() {
        document.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
            actualizarFila(fila);
        });
        window.PedidoFase2?.validarCapacidadVehiculo?.();
        window.PedidoFase2?.actualizarSugerenciaVehiculo?.();
    }

    function renombrarCamposFilas() {
        document.querySelectorAll('.pdv-producto-row').forEach(function (fila, idx) {
            fila.dataset.idx = String(idx);
            const ins = fila.querySelector('[data-field="insumoid"]');
            const pres = fila.querySelector('[data-field="presentacion"]');
            const cant = fila.querySelector('[data-field="cantidad"]');
            if (ins) ins.name = 'detalles[' + idx + '][insumoid]';
            if (pres) pres.name = 'detalles[' + idx + '][insumo_presentacionid]';
            if (cant) cant.name = 'detalles[' + idx + '][cantidad]';
            const btnQuitar = fila.querySelector('.btn-quitar-producto-pdv');
            if (btnQuitar) {
                btnQuitar.classList.toggle('d-none', document.querySelectorAll('.pdv-producto-row').length <= 1);
            }
        });
    }

    function cargarPresentacionesFila(fila, insumoId, preseleccionId) {
        const select = fila.querySelector('[data-field="presentacion"]');
        const ayuda = fila.querySelector('[data-field="pres-ayuda"]');
        const vacio = fila.querySelector('[data-field="pres-vacia"]');
        const pick = fila.querySelector('[data-field="pres-pick"]');
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
        const almacenId = almacenMayoristaId();
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
                const presOcupadas = new Set();
                document.querySelectorAll('.pdv-producto-row').forEach(function (otra) {
                    if (otra === fila) return;
                    const pid = otra.querySelector('[data-field="presentacion"]')?.value;
                    if (pid) presOcupadas.add(String(pid));
                });
                const disponibles = items.filter(function (item) {
                    return !presOcupadas.has(String(item.id));
                });
                if (!disponibles.length) {
                    select.innerHTML = '<option value="">Sin empaques libres para otra línea</option>';
                    select.disabled = true;
                    vacio?.classList.remove('d-none');
                    if (vacio) {
                        vacio.textContent = items.length === 1
                            ? 'Este producto solo tiene un empaque y ya está en otra línea.'
                            : 'Todas las presentaciones de este producto ya están en otras líneas.';
                    }
                    pick?.classList.add('is-locked');
                    avisoPdv(items.length === 1
                        ? 'No quedan más empaques de este producto: solo tiene una presentación y ya está en uso.'
                        : 'No quedan presentaciones libres de este producto para otra línea. Elija otro producto o quite una línea.');
                    return;
                }
                disponibles.forEach(function (item) {
                    fila.presentacionesExtra = fila.presentacionesExtra || {};
                    fila.presentacionesExtra[String(item.id)] = item.extra || {};
                    let label = item.label || ('Presentación #' + item.id);
                    const disp = stockDisponiblePresentacion(item.extra, String(item.id), fila);
                    if (item.extra?.tiene_stock && disp > 0) {
                        label += ' · disp. ' + disp + ' ' + (item.extra.unidad_etiqueta || 'unid.');
                    } else if (!item.extra?.tiene_stock) {
                        label += ' · sin stock';
                    }
                    select.appendChild(new Option(label, String(item.id)));
                });
                select.disabled = false;
                pick?.classList.remove('is-locked');
                let elegido = preseleccionId ? String(preseleccionId) : '';
                if (elegido && presentacionUsadaEnOtraFila(elegido, fila)) {
                    elegido = '';
                }
                if (elegido && fila.presentacionesExtra[elegido]) {
                    select.value = elegido;
                } else {
                    const conStock = disponibles.find(function (it) {
                        return stockDisponiblePresentacion(it.extra, String(it.id), fila) > 0;
                    });
                    if (conStock) select.value = String(conStock.id);
                }
                actualizarFila(fila);
                refrescarTodasLasFilasPdv();
            })
            .catch(function () {
                ayuda?.classList.add('d-none');
                select.innerHTML = '<option value="">No se pudieron cargar las presentaciones</option>';
                select.disabled = true;
            });
    }

    function extraFila(fila) {
        const id = fila.querySelector('[data-field="presentacion"]')?.value || '';
        return id ? (fila.presentacionesExtra?.[id] || null) : null;
    }

    function actualizarFila(fila) {
        const presId = fila.querySelector('[data-field="presentacion"]')?.value || '';
        const extra = extraFila(fila);
        const lblCant = fila.querySelector('.js-lbl-cantidad-pdv');
        const stockSm = fila.querySelector('[data-field="stock-panel"]');
        const equiv = fila.querySelector('[data-field="equiv-kg"]');
        const alerta = fila.querySelector('[data-field="stock-alerta"]');
        const cantInput = fila.querySelector('[data-field="cantidad"]');
        const qty = parseFloat(String(cantInput?.value || '').trim()) || 0;
        const unidad = extra?.unidad_etiqueta || 'unidades';
        const disp = stockDisponiblePresentacion(extra, presId, fila);

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
            if (equiv) {
                equiv.textContent = 'Equivale a ' + fmtKg(qty * extra.peso_neto_kg) + ' kg';
            }
        } else {
            equiv?.classList.add('d-none');
        }

        const tieneStock = extra && extra.tiene_stock && disp > 0;
        const excede = !!(cantInput?.value && (!tieneStock || qty > disp));
        fila.classList.toggle('is-stock-error', excede);
        equiv?.classList.toggle('is-error', excede);
        if (alerta) {
            alerta.classList.toggle('d-none', !excede);
            if (excede) {
                alerta.innerHTML = disp > 0
                    ? '<i class="fas fa-exclamation-triangle"></i> Supera el stock disponible (' + disp + ' ' + unidad + ').'
                    : '<i class="fas fa-exclamation-triangle"></i> Sin stock disponible para esta presentación.';
            }
        }

        if (cantInput) {
            if (disp > 0) {
                cantInput.max = String(disp);
            } else {
                cantInput.removeAttribute('max');
            }
        }
    }

    function crearFila(detalle) {
        detalle = detalle || {};
        const idx = filaSeq++;
        const filaId = 'pdv_fila_' + idx;
        const selectorId = 'pdv_producto_' + idx;
        const container = el('pdv-productos-envio-container');
        if (!container) return null;

        const fila = document.createElement('div');
        fila.className = 'pdv-producto-row traslado-producto-row';
        fila.setAttribute('data-selector-id', selectorId);
        fila.dataset.idx = String(container.querySelectorAll('.pdv-producto-row').length);
        fila.innerHTML =
            '<div class="traslado-producto-row__head">' +
                '<label>Línea de producto</label>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary btn-quitar-producto-pdv" title="Quitar línea">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>' +
            '<div class="form-row">' +
                '<div class="col-lg-4 col-md-6 mb-2 mb-lg-0">' +
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
                '<div class="col-lg-4 col-md-6 mb-2 mb-lg-0">' +
                    '<span class="field-label">Presentación</span>' +
                    '<div class="pdv-envio-presentacion-pick is-locked" data-field="pres-pick">' +
                        '<select data-field="presentacion" class="form-control form-control-sm" disabled><option value="">Elegir producto primero…</option></select>' +
                        '<small data-field="pres-ayuda" class="form-text text-muted mb-0 d-none">Cargando presentaciones…</small>' +
                        '<small data-field="pres-vacia" class="form-text text-muted mb-0 d-none">Sin presentaciones con stock.</small>' +
                    '</div>' +
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
            if (document.querySelectorAll('.pdv-producto-row').length <= 1) return;
            fila.remove();
            renombrarCamposFilas();
            refrescarTodasLasFilasPdv();
        });

        fila.querySelector('[data-field="presentacion"]')?.addEventListener('change', function () {
            const presId = this.value;
            if (presId && presentacionUsadaEnOtraFila(presId, fila)) {
                avisoPdv('Esa presentación ya está en otra línea. Elija un empaque diferente.');
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
                params: paramsProductoCatalogo(),
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
                    cargarPresentacionesFila(fila, item.id, null);
                },
            });
            document.getElementById('selector_wrap_' + selectorId)?.addEventListener('selector-catalogo:change', function (e) {
                const det = e.detail || {};
                if (det.id) cargarPresentacionesFila(fila, det.id, null);
            });
        }

        renombrarCamposFilas();

        if (detalle.insumoid) {
            cargarPresentacionesFila(fila, detalle.insumoid, detalle.insumo_presentacionid || null);
        }

        return fila;
    }

    function validarFilas(opciones) {
        const mostrarModal = !!(opciones && opciones.mostrarModal);
        const filas = document.querySelectorAll('.pdv-producto-row');
        if (!filas.length) {
            return { ok: false, mensaje: 'Agregue al menos un producto al envío.' };
        }
        const presVistas = new Set();
        let algunaValida = false;
        for (const fila of filas) {
            const ins = fila.querySelector('[data-field="insumoid"]')?.value;
            const pres = fila.querySelector('[data-field="presentacion"]')?.value;
            const qty = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
            if (!ins || !pres || !Number.isFinite(qty) || qty <= 0) continue;
            if (presVistas.has(String(pres))) {
                const msg = 'Cada presentación solo puede usarse en una línea. Elija empaques diferentes.';
                if (mostrarModal && window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Empaque duplicado', text: msg });
                    return { ok: false, mensaje: msg, modalMostrado: true };
                }
                return { ok: false, mensaje: msg };
            }
            presVistas.add(String(pres));
            actualizarFila(fila);
            const alertaVisible = !fila.querySelector('[data-field="stock-alerta"]')?.classList.contains('d-none');
            if (alertaVisible) {
                const msg = 'Revise stock y cantidad en cada línea de producto.';
                if (mostrarModal && window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Stock insuficiente', text: msg });
                    return { ok: false, mensaje: msg, modalMostrado: true };
                }
                return { ok: false, mensaje: msg };
            }
            algunaValida = true;
        }
        if (!algunaValida) {
            return { ok: false, mensaje: 'Indique producto, presentación y cantidad en al menos una línea.' };
        }
        return { ok: true };
    }

    function textoResumen() {
        const partes = [];
        document.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
            const nombre = fila.querySelector('.selector-catalogo-label')?.value?.split('—')[0]?.trim();
            const extra = extraFila(fila);
            const pres = extra?.presentacion_nombre || fila.querySelector('[data-field="presentacion"]')?.selectedOptions?.[0]?.text?.split('·')[0]?.trim();
            const qty = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
            if (!nombre || !pres || qty <= 0) return;
            let t = nombre + ' · ' + pres + ' · ' + qty.toLocaleString('es-BO') + ' ' + (extra?.unidad_etiqueta || 'unid.');
            if (extra?.peso_neto_kg > 0) t += ' (' + fmtKg(qty * extra.peso_neto_kg) + ' kg)';
            partes.push(t);
        });
        return partes.length ? partes.join(' | ') : '—';
    }

    function resumenItems() {
        const items = [];
        let kg = 0;
        document.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
            const nombre = fila.querySelector('.selector-catalogo-label')?.value?.split('—')[0]?.trim() || 'Producto';
            const extra = extraFila(fila);
            const pres = extra?.presentacion_nombre || '';
            const qty = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
            if (qty <= 0) return;
            const peso = parseFloat(extra?.peso_neto_kg || 0);
            const kgLinea = peso > 0 ? qty * peso : 0;
            kg += kgLinea;
            let det = nombre;
            if (pres) det += ' · ' + pres + ' × ' + qty;
            if (kgLinea > 0) det += ' (' + fmtKg(kgLinea) + ' kg)';
            items.push(det);
        });
        return { items: items, kg: kg };
    }

    function init() {
        const btn = el('btnAgregarProductoPdv');
        btn?.addEventListener('click', function () {
            crearFila({});
        });

        document.getElementById('selector_wrap_pdv_unificado_almacen')?.addEventListener('selector-catalogo:change', function () {
            document.querySelectorAll('.pdv-producto-row').forEach(function (fila) {
                const ins = fila.querySelector('[data-field="insumoid"]')?.value;
                if (ins) {
                    cargarPresentacionesFila(fila, ins, fila.querySelector('[data-field="presentacion"]')?.value || null);
                }
            });
            if (window.CatalogoSelector?.instances) {
                Object.keys(window.CatalogoSelector.instances).forEach(function (key) {
                    if (key.startsWith('pdv_producto_') && window.CatalogoSelector.instances[key]) {
                        window.CatalogoSelector.instances[key].params = paramsProductoCatalogo();
                    }
                });
            }
        });

        if (Array.isArray(oldDetalles) && oldDetalles.length) {
            oldDetalles.forEach(function (d) { crearFila(d); });
        } else {
            crearFila({});
        }
    }

    window.PdvEnvioCarga = {
        init: init,
        validar: validarFilas,
        textoResumen: textoResumen,
        resumenItems: resumenItems,
        syncAlPaso2: function () {
            renombrarCamposFilas();
            refrescarTodasLasFilasPdv();
        },
    };

    window.EnvioPdvProductos = window.PdvEnvioCarga;

    document.addEventListener('DOMContentLoaded', init);
})();
</script>
