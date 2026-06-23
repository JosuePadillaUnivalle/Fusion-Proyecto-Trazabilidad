<script>
(function () {
    const TOTAL_PLANTA_MAYORISTA = 4;
    const PDV_CON_ASIGNACION = @json($pdvConAsignacion ?? false);

    function totalPasos() {
        if (flujoActivo() === 'punto-venta') {
            return PDV_CON_ASIGNACION ? TOTAL_PLANTA_MAYORISTA : 3;
        }
        return TOTAL_PLANTA_MAYORISTA;
    }

    function pasoDomEnFormulario(n) {
        if (flujoActivo() === 'punto-venta' && !PDV_CON_ASIGNACION && n === 3) return 4;
        return n;
    }

    function pasoLogicoDesdeIndicador(stepDom) {
        const n = parseInt(stepDom, 10);
        if (flujoActivo() === 'punto-venta' && !PDV_CON_ASIGNACION && n === 4) return 3;
        return n;
    }
    const DRAFT_KEY = 'agrofusion_envio_borrador_v1';
    let paso = 1;
    let pasoMaximo = 1;
    let guardandoBorrador = false;
    let restaurandoBorrador = false;
    let saveTimer = null;

    const TRAYECTO_DESC = {
        planta: 'Recogida de cosecha en almacenes agrícolas y entrega en planta procesadora.',
        mayorista: 'Traslado de producto procesado desde almacén de planta al centro mayorista.',
        'punto-venta': 'Distribución desde el almacén mayorista hacia un punto de venta minorista.',
    };

    function el(id) { return document.getElementById(id); }

    function flujoActivo() {
        if (!el('flujo-planta')?.classList.contains('d-none')) return 'planta';
        if (!el('flujo-mayorista')?.classList.contains('d-none')) return 'mayorista';
        return 'punto-venta';
    }

    function flujoRoot() {
        const f = flujoActivo();
        if (f === 'mayorista') return el('flujo-mayorista');
        if (f === 'punto-venta') return el('flujo-punto-venta');
        return el('flujo-planta');
    }

    function labelSelector(id) {
        return document.querySelector('#selector_wrap_' + id + ' .selector-catalogo-label')?.value
            || window.CatalogoSelector?.instances?.[id]?.labelInput?.value
            || '—';
    }

    function valorSelector(id) {
        return document.querySelector('#selector_wrap_' + id + ' .selector-catalogo-value')?.value
            || window.CatalogoSelector?.instances?.[id]?.valueInput?.value
            || '';
    }

    function collectFormFields(formId) {
        const form = el(formId);
        if (!form) return {};
        const data = {};
        form.querySelectorAll('input, select, textarea').forEach((field) => {
            if (!field.name || field.type === 'file') return;
            if (field.type === 'checkbox') {
                data[field.name] = field.checked;
            } else if (field.type === 'radio') {
                if (field.checked) data[field.name] = field.value;
            } else {
                data[field.name] = field.value;
            }
        });
        return data;
    }

    function restoreFormFields(formId, fields) {
        if (!fields) return;
        const form = el(formId);
        if (!form) return;
        Object.keys(fields).forEach((name) => {
            const nodes = form.querySelectorAll('[name="' + name.replace(/"/g, '\\"') + '"]');
            if (!nodes.length) return;
            const val = fields[name];
            nodes.forEach((field) => {
                if (field.type === 'checkbox') {
                    field.checked = !!val;
                } else if (field.type === 'radio') {
                    field.checked = field.value === String(val);
                } else {
                    field.value = val ?? '';
                }
            });
        });
    }

    function collectBorrador() {
        const trayecto = flujoActivo();
        const draft = {
            v: 1,
            trayecto: trayecto,
            paso: paso,
            pasoMaximo: pasoMaximo,
            savedAt: Date.now(),
        };
        if (trayecto === 'planta' && window.EnvioPlantaDraft) {
            draft.planta = window.EnvioPlantaDraft.collect();
        } else if (trayecto === 'mayorista') {
            draft.mayorista = { fields: collectFormFields('form-traslado-mayorista') };
        } else {
            draft.pdv = { fields: collectFormFields('form-pedido-dist-pdv') };
        }
        return draft;
    }

    function guardarBorrador() {
        /* Sin persistencia entre módulos, sesiones ni recargas: el wizard siempre inicia en cero. */
    }

    function programarGuardado() {
        /* noop */
    }

    function limpiarBorrador() {
        try { sessionStorage.removeItem(DRAFT_KEY); } catch (e) { /* noop */ }
    }

    function cargarBorrador() {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    async function restaurarBorrador(draft) {
        if (!draft || draft.v !== 1) return false;
        restaurandoBorrador = true;
        const trayecto = draft.trayecto || 'planta';
        const btn = document.querySelector('.js-trayecto-pick .env-destino-opcion[data-destino="' + trayecto + '"]');
        if (btn && typeof window.activarDestinoEnvio === 'function') {
            window.activarDestinoEnvio(trayecto, { keepPaso: true });
        } else if (btn) {
            btn.click();
        }
        await new Promise((r) => setTimeout(r, 60));
        if (trayecto === 'planta' && draft.planta && window.EnvioPlantaDraft) {
            window.EnvioPlantaDraft.restore(draft.planta);
        } else if (trayecto === 'mayorista' && draft.mayorista?.fields) {
            restoreFormFields('form-traslado-mayorista', draft.mayorista.fields);
        } else if (draft.pdv?.fields) {
            restoreFormFields('form-pedido-dist-pdv', draft.pdv.fields);
        }
        pasoMaximo = Math.min(totalPasos(), Math.max(1, parseInt(draft.pasoMaximo, 10) || 1));
        const pasoObjetivo = Math.min(totalPasos(), Math.max(1, parseInt(draft.paso, 10) || 1));
        irPaso(pasoObjetivo, { sinValidar: true, sinGuardar: true });
        restaurandoBorrador = false;
        return true;
    }

    function pintarProgreso() {
        const esPdv = flujoActivo() === 'punto-venta';
        document.querySelectorAll('#env-wizard-progress .step-item').forEach((item) => {
            const nDom = parseInt(item.dataset.step, 10);
            if (esPdv && !PDV_CON_ASIGNACION && nDom === 3) {
                item.classList.add('d-none');
                return;
            }
            item.classList.remove('d-none');
            const n = pasoLogicoDesdeIndicador(nDom);
            item.classList.remove('active', 'done', 'pending');
            if (n < paso) item.classList.add('done');
            else if (n === paso) item.classList.add('active');
            else item.classList.add('pending');
            item.classList.toggle('can-jump', n <= pasoMaximo && n !== paso);
            const badge = item.querySelector('.step-badge');
            if (badge) {
                badge.textContent = (esPdv && !PDV_CON_ASIGNACION && nDom === 4) ? '3' : String(nDom);
            }
        });
        const btnAnt = el('wizard-btn-anterior');
        const btnSig = el('wizard-btn-siguiente');
        const f = flujoActivo();
        const max = totalPasos();
        if (btnAnt) btnAnt.disabled = paso <= 1;
        if (btnSig) btnSig.classList.toggle('d-none', paso >= max);
        ['wizard-btn-guardar', 'wizard-btn-guardar-mayorista', 'wizard-btn-guardar-pdv'].forEach((id) => {
            const b = el(id);
            if (!b) return;
            const show = paso >= max && (
                (id === 'wizard-btn-guardar' && f === 'planta') ||
                (id === 'wizard-btn-guardar-mayorista' && f === 'mayorista') ||
                (id === 'wizard-btn-guardar-pdv' && f === 'punto-venta')
            );
            b.classList.toggle('d-none', !show);
        });
        enlazarPasosProgreso();
    }

    function enlazarPasosProgreso() {
        document.querySelectorAll('#env-wizard-progress .step-item').forEach((item) => {
            if (item.classList.contains('d-none')) return;
            const nDom = parseInt(item.dataset.step, 10);
            const n = pasoLogicoDesdeIndicador(nDom);
            const puede = n <= pasoMaximo && n !== paso;
            item.onclick = puede ? () => irPaso(n, { sinValidar: n < paso }) : null;
            item.style.cursor = puede ? 'pointer' : 'default';
        });
    }

    function irPaso(n, opts) {
        opts = opts || {};
        const max = totalPasos();
        if (n < 1 || n > max) return;
        const f = flujoActivo();
        const usaCompartidos = f === 'planta' || f === 'mayorista' || (f === 'punto-venta' && PDV_CON_ASIGNACION);
        const root = flujoRoot();
        const compartidos = el('wizard-pasos-compartidos');
        const domN = pasoDomEnFormulario(n);

        if (compartidos) {
            compartidos.classList.toggle('d-none', f === 'punto-venta' && !PDV_CON_ASIGNACION);
        }

        if (usaCompartidos && n >= 3) {
            root?.querySelectorAll('.wizard-step').forEach((s) => s.classList.remove('active'));
            compartidos?.querySelectorAll('.wizard-step').forEach((s) => {
                s.classList.toggle('active', parseInt(s.dataset.wizardStep, 10) === n);
            });
        } else if (root) {
            compartidos?.querySelectorAll('.wizard-step').forEach((s) => s.classList.remove('active'));
            root.querySelectorAll('.wizard-step').forEach((s) => {
                s.classList.toggle('active', parseInt(s.dataset.wizardStep, 10) === domN);
            });
        }
        paso = n;
        if (n > pasoMaximo) pasoMaximo = n;
        pintarProgreso();
        if (n === max) construirResumen();
        if (n === 2) {
            if (f === 'planta' && typeof window.syncFilasProductoPlanta === 'function') {
                window.syncFilasProductoPlanta();
            }
            if (f === 'mayorista' && window.EnvioTrasladoProductos?.syncAlPaso2) {
                window.EnvioTrasladoProductos.syncAlPaso2();
            }
            if (f === 'punto-venta' && window.EnvioPdvProductos?.syncAlPaso2) {
                window.EnvioPdvProductos.syncAlPaso2();
            }
        }
        if (n === 3 && window.PedidoFase2?.actualizarSugerenciaVehiculo) {
            window.PedidoFase2.actualizarSugerenciaVehiculo();
            window.PedidoFase2.validarCapacidadVehiculo?.();
        }
        if (!opts.sinGuardar) programarGuardado();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (n === 1 && f === 'mayorista' && window.ensureMapaTraslado) {
            window.ensureMapaTraslado();
            setTimeout(function () {
                if (window.refrescarTamanoMapaTraslado) window.refrescarTamanoMapaTraslado();
            }, 400);
        } else if (n === 1 && f === 'punto-venta' && window.ensureMapaPdv) {
            window.ensureMapaPdv();
            setTimeout(function () {
                if (window.refrescarTamanoMapaPdv) window.refrescarTamanoMapaPdv();
            }, 400);
        }
    }

    function avisoWizard(msg) {
        if (window.ModalConfirmar?.aviso) {
            ModalConfirmar.aviso({ mensaje: msg, titulo: 'Complete este paso', tono: 'warning' });
        } else {
            window.alert(msg);
        }
    }

    function validarPasoPlanta(n) {
        if (n === 1) {
            if (!el('fechaEntregaDeseada')?.value) {
                avisoWizard('Indique la fecha de entrega deseada.');
                return false;
            }
            if (!el('origen_latitud')?.value || !el('latitud')?.value) {
                avisoWizard('Complete la ruta: al menos una recogida y el destino en planta.');
                return false;
            }
            return true;
        }
        if (n === 2) {
            if (window.PedidoFase2?.validarCargaPlanta) {
                const v = window.PedidoFase2.validarCargaPlanta();
                if (!v.ok) {
                    avisoWizard(v.mensaje);
                    return false;
                }
                return true;
            }
            const filas = document.querySelectorAll('.producto-recogida-row');
            if (!filas.length) {
                avisoWizard('Elija almacenes de recogida y asigne al menos un producto.');
                return false;
            }
            let ok = false;
            filas.forEach((f) => {
                const ref = f.querySelector('.selector-catalogo-value')?.value;
                const cant = parseFloat(f.querySelector('[data-field="cantidad"]')?.value);
                if (ref && cant > 0) ok = true;
            });
            if (!ok) {
                avisoWizard('Indique producto y cantidad en cada recogida.');
                return false;
            }
            return true;
        }
        if (n === 3) {
            if (!el('transportista_usuarioid_create')?.value) {
                avisoWizard('Seleccione un chofer.');
                return false;
            }
            if (!el('vehiculoid_create')?.value) {
                avisoWizard('Seleccione un vehículo.');
                return false;
            }
            const costo = parseFloat(el('costo_bs')?.value);
            if (!Number.isFinite(costo) || costo < 0) {
                avisoWizard('Indique el costo del servicio.');
                return false;
            }
            const alertaCap = el('capacidad-vehiculo-alerta');
            if (alertaCap && alertaCap.style.display !== 'none') {
                avisoWizard('La carga supera la capacidad del camión. Reduzca cantidades o use el botón de planificar otro camión y divida el envío.');
                return false;
            }
            return true;
        }
        return true;
    }

    function validarPasoMayorista(n) {
        if (n === 1) {
            if (!document.querySelector('#form-traslado-mayorista [name="fecha_entrega_deseada"]')?.value) {
                avisoWizard('Indique la fecha de entrega deseada.');
                return false;
            }
            if (!valorSelector('traslado_planta_origen')) {
                avisoWizard('Seleccione el almacén de planta (origen).');
                return false;
            }
            if (!valorSelector('traslado_mayorista_destino')) {
                avisoWizard('Seleccione el almacén mayorista (destino).');
                return false;
            }
            return true;
        }
        if (n === 2) {
            const filas = document.querySelectorAll('.traslado-producto-row');
            if (!filas.length) {
                avisoWizard('Agregue al menos un producto al traslado.');
                return false;
            }
            let ok = false;
            let msgStock = '';
            filas.forEach((f) => {
                const ins = f.querySelector('[data-field="insumoid"]')?.value;
                if (!ins) return;
                if (typeof window.filaTrasladoTieneStockSuficiente === 'function' && !window.filaTrasladoTieneStockSuficiente(f)) {
                    msgStock = 'Revise stock, lote y cantidad en cada línea. No puede despachar más unidades de las disponibles.';
                    return;
                }
                const modo = f.dataset.modoCantidad || '';
                const cantKg = parseFloat(f.querySelector('[data-field="cantidad"]')?.value);
                const pres = f.querySelector('[data-field="presentacion"]')?.value;
                const unidades = parseInt(f.querySelector('[data-field="cantidad_unidades"]')?.value || '0', 10);
                const loteSel = f.querySelector('[data-field="inventario_lote"]');
                const loteRequerido = loteSel && !loteSel.disabled && loteSel.options.length > 1;
                if (modo === 'unidades') {
                    if (pres && unidades > 0 && cantKg > 0 && (!loteRequerido || loteSel.value)) ok = true;
                } else if (modo === 'sin_presentacion') {
                    msgStock = msgStock || 'Hay productos sin empaques configurados. Configure presentaciones comerciales o elija otro producto.';
                }
            });
            if (msgStock) {
                avisoWizard(msgStock);
                if (typeof window.marcarErroresValidacionTraslado === 'function') {
                    window.marcarErroresValidacionTraslado();
                }
                return false;
            }
            if (!ok) {
                if (typeof window.marcarErroresValidacionTraslado === 'function') {
                    window.marcarErroresValidacionTraslado();
                }
                avisoWizard('Indique producto, presentación, lote y cantidad en cada línea.');
                return false;
            }
            return true;
        }
        if (n === 3) {
            if (!el('transportista_usuarioid_create')?.value) {
                avisoWizard('Seleccione un chofer de flota planta.');
                return false;
            }
            if (!el('vehiculoid_create')?.value) {
                avisoWizard('Seleccione un vehículo de flota planta.');
                return false;
            }
            const costo = parseFloat(el('costo_bs')?.value);
            if (!Number.isFinite(costo) || costo < 1) {
                avisoWizard('Indique el costo del servicio.');
                return false;
            }
            const alertaCap = el('capacidad-vehiculo-alerta');
            if (alertaCap && alertaCap.style.display !== 'none') {
                avisoWizard('La carga supera la capacidad del camión. Reduzca cantidades o elija un vehículo con mayor capacidad.');
                return false;
            }
            return true;
        }
        return true;
    }

    function validarPasoPdv(n) {
        if (n === 1) {
            if (!document.querySelector('#form-pedido-dist-pdv [name="fecha_entrega_deseada"]')?.value) {
                avisoWizard('Indique la fecha de entrega deseada.');
                return false;
            }
            if (!valorSelector('pdv_unificado_punto')) {
                avisoWizard('Seleccione el punto de venta (destino).');
                return false;
            }
            const adminAlmacen = el('pdv_unificado_almacen');
            if (adminAlmacen && !valorSelector('pdv_unificado_almacen')) {
                avisoWizard('Seleccione el almacén mayorista (origen).');
                return false;
            }
            return true;
        }
        if (n === 2) {
            if (window.PdvEnvioCarga) {
                const v = window.PdvEnvioCarga.validar({ mostrarModal: true });
                if (!v.ok) {
                    if (!v.modalMostrado) avisoWizard(v.mensaje);
                    return false;
                }
                return true;
            }
            return false;
        }
        if (n === 3 && PDV_CON_ASIGNACION) {
            if (!el('transportista_usuarioid_create')?.value) {
                avisoWizard('Seleccione un chofer de flota mayorista.');
                return false;
            }
            if (!el('vehiculoid_create')?.value) {
                avisoWizard('Seleccione un vehículo de flota mayorista.');
                return false;
            }
            const costo = parseFloat(el('costo_bs')?.value);
            if (!Number.isFinite(costo) || costo < 1) {
                avisoWizard('Indique el costo del servicio.');
                return false;
            }
            const alertaCap = el('capacidad-vehiculo-alerta');
            if (alertaCap && alertaCap.style.display !== 'none') {
                avisoWizard('La carga supera la capacidad del camión. Reduzca cantidades o elija otro vehículo.');
                return false;
            }
            return true;
        }
        return true;
    }

    function validarPaso(n) {
        const f = flujoActivo();
        if (f === 'mayorista') return validarPasoMayorista(n);
        if (f === 'punto-venta') return validarPasoPdv(n);
        return validarPasoPlanta(n);
    }

    function resumenCargaPlanta() {
        const items = [];
        let kg = 0;
        document.querySelectorAll('.producto-recogida-row').forEach((f) => {
            const prod = f.querySelector('.selector-catalogo-label')?.value || 'Producto';
            const nombre = prod.split('—')[0].trim();
            const cant = parseFloat(f.querySelector('[data-field="cantidad"]')?.value) || 0;
            const forma = f.querySelector('.js-forma-pedido')?.value || 'kg';
            const cantPed = parseFloat(f.querySelector('.js-cantidad-pedido')?.value);
            if (cant > 0) {
                kg += cant;
                let detalle = nombre + ' — ' + cant.toLocaleString('es-BO', { maximumFractionDigits: 1 }) + ' kg';
                if (forma === 'empaques' && cantPed > 0) {
                    detalle = nombre + ' — ' + cantPed.toLocaleString('es-BO') + ' empaques (' + cant.toLocaleString('es-BO', { maximumFractionDigits: 1 }) + ' kg)';
                } else if (forma === 'unidades' && cantPed > 0) {
                    detalle = nombre + ' — ' + cantPed.toLocaleString('es-BO') + ' unidades (' + cant.toLocaleString('es-BO', { maximumFractionDigits: 1 }) + ' kg)';
                }
                items.push(detalle);
            }
        });
        return { items, kg };
    }

    function textoRutaAmigable(raw) {
        if (!raw || raw === '—') return '—';
        if (window.PedidoFase2?.formatRutaResumen && raw.includes('km')) {
            const km = parseInt((raw.match(/(\d+)\s*km/) || [])[1], 10);
            const min = parseInt((raw.match(/(\d+)\s*min/) || [])[1], 10);
            const paradas = parseInt((raw.match(/(\d+)\s+paradas/) || [])[1], 10) || 0;
            if (km) {
                return window.PedidoFase2.formatRutaResumen({
                    km, min, paradas: paradas || 0, straight: raw.includes('línea recta'),
                });
            }
        }
        return raw.replace('Ruta por calles', 'Camino estimado')
            .replace('Ruta estimada (línea recta)', 'Distancia aproximada en línea recta');
    }

    function formatFechaInput(iso) {
        if (!iso) return '—';
        const p = String(iso).split('-');
        if (p.length === 3) return p[2] + '/' + p[1] + '/' + p[0];
        return iso;
    }

    function construirResumenPlanta() {
        const carga = resumenCargaPlanta();
        const set = (id, val) => { const e = el(id); if (e) e.textContent = val || '—'; };
        set('conf-origen', el('txtNombreOrigen')?.value || el('origen_direccion')?.value || '—');
        set('conf-destino', el('txtNombreDestino')?.value || el('direccion_texto')?.value || '—');
        set('conf-fecha', formatFechaInput(el('fechaEntregaDeseada')?.value));
        set('conf-hora-rec', el('hora_recogida')?.value || '—');
        set('conf-hora-ent', el('hora_entrega_estimada')?.value || '—');
        set('conf-chofer', el('txtTransportistaCreate')?.value || '—');
        set('conf-vehiculo', el('txtVehiculoCreate')?.value || '—');
        set('conf-costo', el('costo_bs')?.value ? 'Bs ' + Number(el('costo_bs').value).toLocaleString('es-BO') : '—');
        set('conf-carga-kg', carga.kg > 0 ? carga.kg.toLocaleString('es-BO', { maximumFractionDigits: 1 }) + ' kg' : '—');
        set('conf-ruta', textoRutaAmigable(el('rutaResumen')?.textContent || '—'));
        const lista = el('conf-carga-lista');
        if (lista) {
            lista.innerHTML = carga.items.length
                ? carga.items.map((t) =>
                    '<li class="env-confirm-producto-item">' +
                    '<i class="fas fa-seedling env-confirm-producto-item__icon"></i>' +
                    '<span>' + t + '</span></li>'
                ).join('')
                : '<li class="env-confirm-producto-item text-muted"><span>Sin productos indicados</span></li>';
        }
        const tituloLista = el('conf-carga-lista-titulo');
        if (tituloLista) {
            tituloLista.innerHTML = '<i class="fas fa-box-open mr-1"></i> Qué se recoge';
        }
        el('wizard-confirm-meta-horarios')?.querySelectorAll('.col-md-4.col-6').forEach((col, idx) => {
            if (idx < 3) col.classList.remove('d-none');
        });
        el('wizard-confirm-observaciones-wrap')?.classList.remove('d-none');
        const trayecto = el('conf-trayecto-tipo');
        if (trayecto) trayecto.textContent = 'Agrícola → Planta';
    }

    function construirResumenMayorista() {
        const set = (id, val) => { const e = el(id); if (e) e.textContent = val || '—'; };
        set('conf-trayecto-tipo', 'Planta → Mayorista');
        set('conf-origen', labelSelector('traslado_planta_origen'));
        set('conf-destino', labelSelector('traslado_mayorista_destino'));
        set('conf-chofer', el('txtTransportistaCreate')?.value || '—');
        set('conf-vehiculo', el('txtVehiculoCreate')?.value || '—');
        set('conf-costo', el('costo_bs')?.value ? 'Bs ' + Number(el('costo_bs').value).toLocaleString('es-BO') : '—');
        set('conf-ruta', textoRutaAmigable(el('rutaResumenTraslado')?.textContent || '—'));
        set('conf-fecha', formatFechaInput(document.querySelector('#form-traslado-mayorista [name="fecha_entrega_deseada"]')?.value));
        set('conf-hora-rec', el('hora_recogida_traslado')?.value || '—');
        set('conf-hora-ent', el('hora_entrega_estimada_traslado')?.value || '—');

        let kg = 0;
        const items = [];
        document.querySelectorAll('.traslado-producto-row').forEach((f) => {
            const nombre = f.querySelector('.selector-catalogo-label')?.value || 'Producto';
            const presSel = f.querySelector('[data-field="presentacion"]');
            const presLabel = presSel && presSel.value ? presSel.options[presSel.selectedIndex]?.textContent : '';
            const unidades = f.querySelector('[data-field="cantidad_unidades"]')?.value;
            const cantKg = parseFloat(f.querySelector('[data-field="cantidad"]')?.value) || 0;
            if (cantKg > 0) kg += cantKg;
            let det = nombre.split('—')[0].trim();
            if (presLabel && unidades) {
                det += ' · ' + presLabel + ' × ' + unidades;
            }
            if (cantKg > 0) {
                det += ' (' + cantKg.toLocaleString('es-BO', { maximumFractionDigits: 2 }) + ' kg)';
                items.push(det);
            }
        });
        set('conf-carga-kg', kg > 0 ? kg.toLocaleString('es-BO', { maximumFractionDigits: 1 }) + ' kg' : '—');

        const tituloLista = el('conf-carga-lista-titulo');
        if (tituloLista) {
            tituloLista.innerHTML = '<i class="fas fa-box-open mr-1"></i> Productos del traslado';
        }
        const lista = el('conf-carga-lista');
        if (lista) {
            lista.innerHTML = items.length
                ? items.map((t) =>
                    '<li class="env-confirm-producto-item">' +
                    '<i class="fas fa-industry env-confirm-producto-item__icon"></i>' +
                    '<span>' + t + '</span></li>'
                ).join('')
                : '<li class="env-confirm-producto-item text-muted"><span>Sin productos indicados</span></li>';
        }

        el('wizard-confirm-meta-horarios')?.querySelectorAll('.col-md-4.col-6').forEach((col, idx) => {
            if (idx < 3) col.classList.add('d-none');
        });
        el('wizard-confirm-observaciones-wrap')?.classList.add('d-none');
    }

    function construirResumenPdv() {
        if (PDV_CON_ASIGNACION) {
            const set = (id, val) => { const e = el(id); if (e) e.textContent = val || '—'; };
            const origen = valorSelector('pdv_unificado_almacen')
                ? labelSelector('pdv_unificado_almacen')
                : 'Almacén mayorista asignado';
            const carga = window.PdvEnvioCarga?.resumenItems?.() || { items: [], kg: 0 };
            set('conf-trayecto-tipo', 'Mayorista → Punto de Venta');
            set('conf-origen', origen);
            set('conf-destino', labelSelector('pdv_unificado_punto'));
            set('conf-chofer', el('txtTransportistaCreate')?.value || '—');
            set('conf-vehiculo', el('txtVehiculoCreate')?.value || '—');
            set('conf-costo', el('costo_bs')?.value ? 'Bs ' + Number(el('costo_bs').value).toLocaleString('es-BO') : '—');
            set('conf-carga-kg', carga.kg > 0 ? carga.kg.toLocaleString('es-BO', { maximumFractionDigits: 1 }) + ' kg' : '—');
            set('conf-ruta', textoRutaAmigable(document.getElementById('rutaResumenPdv')?.textContent || '—'));
            set('conf-fecha', formatFechaInput(document.querySelector('#form-pedido-dist-pdv [name="fecha_entrega_deseada"]')?.value));
            set('conf-hora-rec', el('hora_recogida_pdv')?.value || '—');
            set('conf-hora-ent', el('hora_entrega_estimada_pdv')?.value || '—');
            const lista = el('conf-carga-lista');
            if (lista) {
                lista.innerHTML = carga.items.length
                    ? carga.items.map((t) =>
                        '<li class="env-confirm-producto-item"><i class="fas fa-box-open env-confirm-producto-item__icon"></i><span>' + t + '</span></li>'
                    ).join('')
                    : '<li class="env-confirm-producto-item text-muted"><span>Sin productos indicados</span></li>';
            }
            const tituloLista = el('conf-carga-lista-titulo');
            if (tituloLista) tituloLista.innerHTML = '<i class="fas fa-box-open mr-1"></i> Productos del envío';
            el('wizard-confirm-observaciones-wrap')?.classList.add('d-none');
            return;
        }

        const set = (id, val) => { const e = el(id); if (e) e.textContent = val || '—'; };
        const origen = valorSelector('pdv_unificado_almacen')
            ? labelSelector('pdv_unificado_almacen')
            : 'Almacén mayorista asignado';
        set('conf-pdv-origen', origen);
        set('conf-pdv-destino', labelSelector('pdv_unificado_punto'));
        const resumenCarga = window.PdvEnvioCarga?.textoResumen?.() || (function () {
            const prod = labelSelector('pdv_unificado_producto');
            const cant = el('pdv_cantidad')?.value;
            return prod !== '—' && cant ? prod.split('—')[0].trim() + ' · ' + cant : '—';
        })();
        set('conf-pdv-producto', resumenCarga);
        set('conf-pdv-fecha', formatFechaInput(document.querySelector('#form-pedido-dist-pdv [name="fecha_entrega_deseada"]')?.value));
        const horaRec = el('hora_recogida_pdv')?.value;
        const horaEnt = el('hora_entrega_estimada_pdv')?.value;
        set('conf-pdv-hora', horaEnt || horaRec || '—');
        set('conf-pdv-ruta', document.getElementById('rutaResumenPdv')?.textContent || '—');
    }

    function construirResumen() {
        const f = flujoActivo();
        if (f === 'mayorista') construirResumenMayorista();
        else if (f === 'punto-venta') construirResumenPdv();
        else construirResumenPlanta();
    }

    function iconoTipoTransporte(nombre) {
        const n = (nombre || '').toLowerCase();
        if (n.includes('refriger')) return 'fa-snowflake';
        if (n.includes('multi')) return 'fa-layer-group';
        if (n.includes('isot')) return 'fa-thermometer-half';
        return 'fa-box';
    }

    function syncTarjetaVehiculo() {
        const vCard = el('card-vehiculo');
        if (!vCard) return;
        const vNombre = el('txtVehiculoCreate')?.value;
        const tieneChofer = !!el('transportista_usuarioid_create')?.value;
        const tieneVehiculo = !!el('vehiculoid_create')?.value;
        const sug = window.PedidoFase2?.sugerenciaVehiculo || null;

        vCard.classList.toggle('is-selected', tieneVehiculo);
        const titulo = el('card-vehiculo-titulo');
        const sugWrap = el('card-vehiculo-sugerencia-wrap');
        const capWrap = el('card-vehiculo-cap-wrap');
        const tipoWrap = el('card-vehiculo-tipo-wrap');
        const estado = el('card-vehiculo-estado');
        const capEl = el('card-vehiculo-cap');
        const tipoEl = el('card-vehiculo-tipo');
        const tipoIcon = el('card-vehiculo-tipo-icon');
        const sugPlaca = el('card-vehiculo-sugerencia-placa');

        if (titulo) titulo.textContent = vNombre || (sug?.label && !tieneVehiculo ? 'Pendiente de confirmar' : 'Elegir vehículo');

        if (tieneVehiculo) {
            if (sugWrap) sugWrap.classList.add('d-none');
            const resumen = (sug && String(sug.id) === String(el('vehiculoid_create')?.value))
                ? (window.PedidoFase2?.sugerenciaVehiculoResumen || '')
                : '';
            if (capWrap && resumen) {
                capWrap.classList.remove('d-none');
                if (capEl) capEl.textContent = resumen;
            } else if (capWrap) {
                capWrap.classList.add('d-none');
            }
            if (tipoWrap) tipoWrap.classList.add('d-none');
            if (estado) {
                estado.classList.remove('d-none');
                estado.textContent = 'Vehículo asignado — clic para cambiar';
            }
        } else if (tieneChofer && sug) {
            if (sugWrap) {
                sugWrap.classList.remove('d-none');
                if (sugPlaca) sugPlaca.textContent = sug.label;
            }
            if (capWrap) {
                capWrap.classList.remove('d-none');
                if (capEl) capEl.textContent = 'Capacidad hasta ' + Number(sug.cap).toLocaleString('es-BO') + ' kg';
            }
            if (tipoWrap) {
                tipoWrap.classList.remove('d-none');
                if (tipoEl) tipoEl.textContent = sug.transporte || 'Carga general';
                if (tipoIcon) tipoIcon.className = 'fas ' + iconoTipoTransporte(sug.transporte) + ' mr-1 text-muted';
            }
            if (estado) {
                estado.classList.remove('d-none');
                estado.textContent = 'Confirme la sugerencia o elija otro en el catálogo';
            }
        } else if (tieneChofer) {
            if (sugWrap) sugWrap.classList.add('d-none');
            if (capWrap) capWrap.classList.add('d-none');
            if (tipoWrap) tipoWrap.classList.add('d-none');
            if (estado) {
                estado.classList.remove('d-none');
                estado.textContent = 'Clic para buscar en el catálogo';
            }
        } else {
            if (sugWrap) sugWrap.classList.add('d-none');
            if (capWrap) capWrap.classList.add('d-none');
            if (tipoWrap) tipoWrap.classList.add('d-none');
            if (estado) {
                estado.classList.remove('d-none');
                estado.textContent = 'Primero elija el chofer';
            }
        }
    }

    function syncTarjetasAsignacion() {
        const tCard = el('card-transportista');
        const tNombre = el('txtTransportistaCreate')?.value;
        if (tCard) {
            tCard.classList.toggle('is-selected', !!el('transportista_usuarioid_create')?.value);
            const t = tCard.querySelector('.env-recurso-card__title');
            const estado = el('card-chofer-estado');
            if (t) t.textContent = tNombre || 'Elegir chofer';
            if (estado) {
                estado.textContent = el('transportista_usuarioid_create')?.value
                    ? 'Chofer asignado — clic para cambiar'
                    : 'Clic para buscar en el catálogo';
            }
        }
        syncTarjetaVehiculo();
    }

    let particionCount = 1;
    function agregarParticion() {
        particionCount += 1;
        const wrap = el('particiones-extra-envio');
        if (!wrap) return;
        const div = document.createElement('div');
        div.className = 'env-particion-card';
        div.innerHTML =
            '<div class="env-particion-card__head"><i class="fas fa-truck-loading mr-1"></i> Camión adicional #' + particionCount + '</div>' +
            '<p class="small text-muted mb-2">Use este espacio para anotar cómo dividirá la carga entre dos envíos.</p>' +
            '<div class="alert alert-light border small mb-0 py-2">' +
            '<i class="fas fa-info-circle text-primary mr-1"></i> ' +
            'Por ahora registre este envío con lo que cabe en el camión actual y cree un <strong>segundo envío</strong> con el resto, ' +
            'o detalle la división en observaciones.</div>';
        wrap.appendChild(div);
    }

    function syncFormularioCompartido(trayecto) {
        let formId = 'form-pedido';
        if (trayecto === 'mayorista') {
            formId = 'form-traslado-mayorista';
        } else if (trayecto === 'punto-venta' && PDV_CON_ASIGNACION) {
            formId = 'form-pedido-dist-pdv';
        }
        ['transportista_usuarioid_create', 'vehiculoid_create', 'costo_bs'].forEach((id) => {
            const field = el(id);
            if (field) field.setAttribute('form', formId);
        });
        const obs = el('wizard-paso-confirmacion-observaciones');
        if (obs) {
            if (trayecto === 'mayorista' || (trayecto === 'punto-venta' && PDV_CON_ASIGNACION)) {
                obs.removeAttribute('form');
            } else {
                obs.setAttribute('form', 'form-pedido');
            }
        }
        const subtitulo = el('wizard-asignacion-subtitulo');
        if (subtitulo) {
            if (trayecto === 'mayorista') {
                subtitulo.textContent = 'Solo choferes y vehículos de flota planta. Si la carga supera la capacidad, elija otro vehículo.';
            } else if (trayecto === 'punto-venta') {
                subtitulo.textContent = 'Solo choferes y vehículos de flota mayorista. Si la carga supera la capacidad, elija otro vehículo.';
            } else {
                subtitulo.textContent = 'Elija chofer y vehículo. Si la carga supera la capacidad, puede planificar un segundo camión.';
            }
        }
        const compartidos = el('wizard-pasos-compartidos');
        if (compartidos) {
            compartidos.classList.toggle('d-none', trayecto === 'punto-venta' && !PDV_CON_ASIGNACION);
        }
        pintarProgreso();
        if (window.CatalogoSelector?.instances?.create_envio_transportista) {
            CatalogoSelector.instances.create_envio_transportista.params = {
                roles: 'transportista',
                ambito_flota: (trayecto === 'mayorista') ? 'planta' : ((trayecto === 'punto-venta') ? 'mayorista' : 'agricola'),
            };
        }
        actualizarParamsVehiculoCatalogo(trayecto);
    }

    function actualizarParamsVehiculoCatalogo(trayecto) {
        if (!window.CatalogoSelector?.instances?.create_envio_vehiculo) return;
        const t = trayecto || flujoActivo();
        const tid = el('transportista_usuarioid_create')?.value || '';
        if (t === 'mayorista') {
            CatalogoSelector.instances.create_envio_vehiculo.params = {
                ambito_flota: 'planta',
            };
        } else if (t === 'punto-venta') {
            CatalogoSelector.instances.create_envio_vehiculo.params = {
                ambito_flota: 'mayorista',
            };
        } else {
            CatalogoSelector.instances.create_envio_vehiculo.params = {
                transportista_usuarioid: tid,
                solo_transportista: '1',
            };
        }
    }

    function syncTrayectoDescripcion(destino) {
        document.querySelectorAll('.env-trayecto-desc').forEach((d) => {
            d.textContent = TRAYECTO_DESC[destino] || '';
        });
    }

    function enlazarPersistencia() {
        document.querySelectorAll('#form-pedido, #form-traslado-mayorista, #form-pedido-dist-pdv').forEach((form) => {
            form.addEventListener('submit', limpiarBorrador);
        });
        document.querySelector('#wizard-btn-cancelar')?.addEventListener('click', limpiarBorrador);

        const esPaginaEnvio = () => (window.location.pathname || '').toLowerCase().includes('/pedidos/create');

        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link || link.id === 'wizard-btn-cancelar') return;
            const href = link.getAttribute('href') || '';
            if (href === '#' || href.startsWith('#')) return;
            let path = link.pathname || '';
            try {
                path = new URL(link.href, window.location.origin).pathname.toLowerCase();
            } catch (err) { /* noop */ }
            if (path && !path.includes('/pedidos/create')) {
                limpiarBorrador();
            }
        }, true);

        window.addEventListener('pagehide', function () {
            if (esPaginaEnvio()) {
                limpiarBorrador();
            }
        });

        window.addEventListener('pageshow', function (e) {
            if (e.persisted && esPaginaEnvio()) {
                limpiarBorrador();
                window.location.reload();
            }
        });
    }

    async function init() {
        const shell = el('env-wizard-unificado');
        if (!shell || shell.dataset.wizardInit === '1') return;
        shell.dataset.wizardInit = '1';

        limpiarBorrador();
        paso = 1;
        pasoMaximo = 1;

        el('wizard-btn-anterior')?.addEventListener('click', () => irPaso(paso - 1, { sinValidar: true }));
        el('wizard-btn-siguiente')?.addEventListener('click', () => {
            if (!validarPaso(paso)) return;
            irPaso(paso + 1);
        });
        el('btnAgregarParticionEnvio')?.addEventListener('click', agregarParticion);

        el('card-transportista')?.addEventListener('click', () => {
            if (window.CatalogoSelector) CatalogoSelector.open('create_envio_transportista');
        });
        el('card-vehiculo')?.addEventListener('click', () => {
            if (el('transportista_usuarioid_create')?.value && window.CatalogoSelector) {
                CatalogoSelector.open('create_envio_vehiculo');
            }
        });

        enlazarPersistencia();
        pintarProgreso();
        syncFormularioCompartido(flujoActivo());
        syncTarjetasAsignacion();
    }

    document.addEventListener('DOMContentLoaded', init);
    window.EnvioWizard = {
        irPaso,
        construirResumen,
        syncTarjetasAsignacion,
        syncTarjetaVehiculo,
        syncTrayectoDescripcion,
        syncFormularioCompartido,
        actualizarParamsVehiculoCatalogo,
        flujoActivo,
        limpiarBorrador,
        guardarBorrador,
        cargarBorrador,
        restaurarBorrador,
    };
})();
</script>
