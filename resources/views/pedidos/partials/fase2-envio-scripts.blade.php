<script>
(function () {
    if (typeof window.PedidoFase2 === 'object') return;

    const ENVIO_API = @json(url('/pedidos/api'));
    const ETA_BUFFER_MIN = 15;
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const TRAYECTO_TRANSPORTE = {
        planta: 'CARGA_GENERAL',
        mayorista: 'REFRIGERADO',
        'punto-venta': 'REFRIGERADO',
    };
    const TRAYECTO_TRANSPORTE_TEXTO = {
        planta: 'agrícola → planta',
        mayorista: 'planta → mayorista',
        'punto-venta': 'mayorista → punto de venta',
    };
    const TRANSPORTE_ETIQUETAS = {
        CARGA_GENERAL: 'carga general',
        ISOTERMICO: 'isotérmico',
        REFRIGERADO: 'refrigerado',
    };
    const VEHICULOS_CATALOGO_URL = @json(route('catalogo-selector.vehiculos'));

    function vehiculoCumpleRequisito(codigosVehiculo, codigoReq) {
        const req = (codigoReq || 'CARGA_GENERAL').toUpperCase();
        if (req === 'CARGA_GENERAL') return true;
        const veh = (codigosVehiculo || []).map((c) => String(c).toUpperCase());
        if (veh.includes('MULTITEMPERATURA')) return true;
        if (req === 'REFRIGERADO') return veh.includes('REFRIGERADO');
        if (req === 'ISOTERMICO') return veh.includes('REFRIGERADO') || veh.includes('ISOTERMICO');
        return true;
    }

    function etiquetaTransporte(codigo) {
        return TRANSPORTE_ETIQUETAS[(codigo || 'CARGA_GENERAL').toUpperCase()] || 'carga general';
    }

    function fmtNum(n, dec) {
        return Number(n).toLocaleString('es-BO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: dec ?? 0,
        });
    }

    window.PedidoFase2 = {
        routeDurationS: null,
        sugerenciaVehiculoId: null,
        sugerenciaVehiculo: null,
        sugerenciaVehiculoResumen: '',
        catalogos: @json($catalogosEmpaque ?? ['tiposEmpaque' => [], 'tamanoConteo' => []]),

        calcularHoraEntrega(horaRecogida, durationS) {
            if (!horaRecogida || !durationS) return '';
            const parts = horaRecogida.split(':').map(Number);
            if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return '';
            const totalMin = parts[0] * 60 + parts[1] + Math.ceil(durationS / 60) + ETA_BUFFER_MIN;
            const h = Math.floor(totalMin / 60) % 24;
            const m = totalMin % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        },

        actualizarHoraEntrega() {
            const campos = this.camposHoraTrayecto();
            if (!campos.recogida || !campos.entrega || !this.routeDurationS) return;
            if (!campos.recogida.value) {
                campos.entrega.value = '';
                return;
            }
            campos.entrega.value = this.calcularHoraEntrega(campos.recogida.value, this.routeDurationS);
        },

        camposHoraTrayecto() {
            const trayecto = window.EnvioWizard?.flujoActivo?.() || 'planta';
            if (trayecto === 'mayorista') {
                return {
                    recogida: document.getElementById('hora_recogida_traslado'),
                    entrega: document.getElementById('hora_entrega_estimada_traslado'),
                };
            }
            if (trayecto === 'punto-venta') {
                return {
                    recogida: document.getElementById('hora_recogida_pdv'),
                    entrega: document.getElementById('hora_entrega_estimada_pdv'),
                };
            }
            return {
                recogida: document.getElementById('hora_recogida'),
                entrega: document.getElementById('hora_entrega_estimada'),
            };
        },

        setRouteDuration(seconds) {
            this.routeDurationS = seconds || null;
            this.actualizarHoraEntrega();
        },

        insumoIdDesdeProductoRef(ref) {
            if (!ref || typeof ref !== 'string') return null;
            const m = ref.match(/^insumo:(\d+)$/);
            return m ? parseInt(m[1], 10) : null;
        },

        insumoIdCalibreFila(fila) {
            const ds = parseInt(fila?.dataset?.insumoCalibreId, 10);
            if (ds > 0) return ds;
            const ref = fila?.querySelector('.selector-catalogo-value')?.value || '';
            return this.insumoIdDesdeProductoRef(ref);
        },

        actualizarHintEmpaque(fila) {
            const empaque = fila.querySelector('.js-tipo-empaque');
            const hint = fila.querySelector('.js-hint-tipo-empaque');
            if (!empaque || !hint) return;
            const tipo = (this.catalogos.tiposEmpaque || []).find(
                (t) => parseInt(t.id, 10) === parseInt(empaque.value, 10)
            );
            const texto = tipo?.ayuda || tipo?.descripcion || '';
            hint.textContent = texto;
            hint.classList.toggle('d-none', !texto);
        },

        cargaCodigoTransporteRequerido() {
            const trayecto = window.EnvioWizard?.flujoActivo?.() || 'planta';
            return TRAYECTO_TRANSPORTE[trayecto] || 'CARGA_GENERAL';
        },

        textoTrayectoTransporte() {
            const trayecto = window.EnvioWizard?.flujoActivo?.() || 'planta';
            return TRAYECTO_TRANSPORTE_TEXTO[trayecto] || TRAYECTO_TRANSPORTE_TEXTO.planta;
        },

        cargaRequiereFrio() {
            return this.cargaCodigoTransporteRequerido() === 'REFRIGERADO';
        },

        flujoEsMayorista() {
            return window.EnvioWizard?.flujoActivo?.() === 'mayorista';
        },

        flujoEsPdv() {
            return window.EnvioWizard?.flujoActivo?.() === 'punto-venta';
        },

        pesoTotalKgPdv() {
            let total = 0;
            document.querySelectorAll('#flujo-punto-venta .pdv-producto-row').forEach((fila) => {
                const presId = fila.querySelector('[data-field="presentacion"]')?.value;
                const qty = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value || '0');
                if (!presId || !Number.isFinite(qty) || qty <= 0) return;
                const extra = fila.presentacionesExtra?.[String(presId)] || null;
                const peso = parseFloat(extra?.peso_neto_kg || 0);
                if (peso > 0) total += qty * peso;
            });
            return total;
        },

        transportistaIdSeleccionado() {
            return document.getElementById('transportista_usuarioid_create')?.value || '';
        },

        vehiculoIdSeleccionado() {
            return document.getElementById('vehiculoid_create')?.value || '';
        },

        aplicarVehiculoSugerido(sugerido) {
            if (!sugerido?.id || this.vehiculoIdSeleccionado()) return;
            const inputVehiculo = document.getElementById('vehiculoid_create');
            const txtVehiculo = document.getElementById('txtVehiculoCreate');
            if (!inputVehiculo) return;
            inputVehiculo.value = String(sugerido.id);
            if (txtVehiculo) {
                txtVehiculo.value = sugerido.label || '';
                txtVehiculo.classList.remove('text-muted');
            }
            this.validarCapacidadVehiculo();
            if (window.EnvioWizard?.syncTarjetasAsignacion) {
                window.EnvioWizard.syncTarjetasAsignacion();
            }
        },

        elementosCapacidad() {
            return {
                alerta: document.getElementById('capacidad-vehiculo-alerta'),
                resumen: document.getElementById('capacidad-vehiculo-resumen'),
                wrap: document.getElementById('capacidad-vehiculo-resumen-wrap'),
                barraPeso: document.getElementById('capacidad-vehiculo-barra-peso'),
                barraVol: document.getElementById('capacidad-vehiculo-barra-vol'),
                pctPeso: document.getElementById('capacidad-vehiculo-pct-peso'),
                pctVol: document.getElementById('capacidad-vehiculo-pct-vol'),
                txtPeso: document.getElementById('capacidad-vehiculo-txt-peso'),
                txtVol: document.getElementById('capacidad-vehiculo-txt-vol'),
                btnPreview: document.getElementById('btnVerPreviewVehiculoAsignacion'),
                particion: document.getElementById('bloque-particion-envio'),
            };
        },

        abrirPreviewVehiculoAsignacion() {
            const vehiculoId = this.vehiculoIdSeleccionado();
            if (!vehiculoId || !window.VehiculoCargaPreview) return;
            const label = document.getElementById('txtVehiculoCreate')?.value || '';
            window.VehiculoCargaPreview.open(vehiculoId, label, {
                peso_kg: this.pesoTotalKg(),
                volumen_m3: this.volumenTotalM3(),
            });
        },

        async actualizarSugerenciaVehiculo() {
            const panel = document.getElementById('env-sugerencia-vehiculo');
            const texto = document.getElementById('env-sugerencia-vehiculo-texto');
            if (!panel || !texto) return;
            const peso = this.pesoTotalKg();
            this.sugerenciaVehiculoId = null;
            this.sugerenciaVehiculo = null;
            this.sugerenciaVehiculoResumen = '';
            if (peso <= 0) {
                panel.classList.add('d-none');
                texto.innerHTML = '';
                return;
            }
            const vehiculoYaElegido = !!this.vehiculoIdSeleccionado();
            const reqTransporte = this.cargaCodigoTransporteRequerido();
            let html = '';
            const tid = this.transportistaIdSeleccionado();
            if (!tid) {
                html = 'Carga: <strong>' + fmtNum(peso, 1) + ' kg</strong>. Elija un chofer para asignar vehículo.';
                texto.innerHTML = html;
                panel.classList.remove('d-none');
                return;
            }
            try {
                const fetchVehiculos = async (extraParams) => {
                    const params = new URLSearchParams({ per_page: '50', ...extraParams });
                    const res = await fetch(VEHICULOS_CATALOGO_URL + '?' + params.toString(), {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error('fetch');
                    const json = await res.json();
                    return json.data || [];
                };
                const trayecto = window.EnvioWizard?.flujoActivo?.() || 'planta';
                const ambitoFlota = trayecto === 'mayorista'
                    ? 'planta'
                    : (trayecto === 'punto-venta' ? 'mayorista' : 'agricola');
                let items = await fetchVehiculos({
                    solo_transportista: '1',
                    transportista_usuarioid: tid,
                });
                if (!items.length) {
                    items = await fetchVehiculos({ ambito_flota: ambitoFlota });
                }
                const vehiculos = items.map((v) => ({
                    id: v.id,
                    label: v.label,
                    cap: parseFloat(v.extra?.kg || 0),
                    transporte: v.extra?.transporte_principal || 'Carga general',
                    codigos: v.extra?.tipos_transporte || [],
                })).filter((v) => v.cap > 0).sort((a, b) => a.cap - b.cap);

                let candidatos = vehiculos;
                if (reqTransporte !== 'CARGA_GENERAL') {
                    const compatibles = vehiculos.filter((v) =>
                        vehiculoCumpleRequisito(v.codigos, reqTransporte)
                    );
                    if (compatibles.length) candidatos = compatibles;
                }
                const sugerido = candidatos.find((v) => v.cap >= peso) || candidatos[candidatos.length - 1];
                if (sugerido) {
                    this.sugerenciaVehiculoId = sugerido.id;
                    this.sugerenciaVehiculo = sugerido;
                    this.sugerenciaVehiculoResumen = sugerido.label + ' · ' + fmtNum(sugerido.cap) + ' kg · ' + sugerido.transporte;
                    if (!vehiculoYaElegido) {
                        this.aplicarVehiculoSugerido(sugerido);
                    }
                    if (vehiculoYaElegido) {
                        panel.classList.add('d-none');
                    } else {
                        html = 'Asignamos <strong>' + sugerido.label + '</strong> (' + fmtNum(sugerido.cap) + ' kg, '
                            + sugerido.transporte + '). Puede cambiarlo desde la tarjeta del vehículo.';
                        texto.innerHTML = html;
                        panel.classList.remove('d-none');
                    }
                } else {
                    html = 'Carga: <strong>' + fmtNum(peso, 1) + ' kg</strong>. No hay vehículos con capacidad registrada.';
                    texto.innerHTML = html;
                    panel.classList.remove('d-none');
                }
            } catch (e) {
                html = 'No se pudo consultar la flota disponible.';
                texto.innerHTML = html;
                panel.classList.remove('d-none');
            }
            if (window.EnvioWizard?.syncTarjetaVehiculo) {
                window.EnvioWizard.syncTarjetaVehiculo();
            }
            return;
        },

        datosCalibre(fila) {
            const calibre = fila.querySelector('.js-calibre');
            if (!calibre || !calibre.value) return null;
            const id = parseInt(calibre.value, 10);
            const fuente = fila._calibresActivos || this.catalogos.tamanoConteo || [];
            const cat = fuente.find((c) => parseInt(c.id, 10) === id);
            const opt = calibre.options[calibre.selectedIndex];
            return {
                id,
                nombre: cat?.nombre || opt?.textContent?.trim() || '',
                conteo: parseInt(cat?.conteo_por_empaque || opt?.dataset?.conteo || 0, 10) || 0,
                pesoKg: parseFloat(cat?.peso_promedio_kg || opt?.dataset?.pesoKg || 0) || 0,
                idTipoEmpaque: cat?.id_tipo_empaque ? parseInt(cat.id_tipo_empaque, 10) : null,
            };
        },

        deduplicarCalibresLista(calibres) {
            const vistos = new Set();
            const lista = [];
            (calibres || []).forEach((c) => {
                const clave = (c.nombre || '').trim().toLowerCase();
                if (!clave || vistos.has(clave)) return;
                vistos.add(clave);
                lista.push(c);
            });
            return lista;
        },

        conteoEfectivoPorEmpaque(calibre, empaque) {
            if (!calibre) return 0;
            const empId = empaque ? parseInt(empaque.id, 10) : null;
            const cap = empaque ? parseInt(empaque.capacidad, 10) : 0;
            if (calibre.idTipoEmpaque && empId && calibre.idTipoEmpaque === empId) {
                return calibre.conteo;
            }
            if (cap > 0) return cap;
            return calibre.conteo;
        },

        pesoNetoPorEmpaque(calibre, empaque) {
            const unidades = this.conteoEfectivoPorEmpaque(calibre, empaque);
            return unidades * (calibre?.pesoKg || 0);
        },

        estimarUnidadesDesdeKg(kg, calibre) {
            if (!calibre || calibre.pesoKg <= 0 || !kg) return null;
            return Math.round(kg / calibre.pesoKg);
        },

        datosEmpaque(empaqueId) {
            return (this.catalogos.tiposEmpaque || []).find((t) => parseInt(t.id, 10) === parseInt(empaqueId, 10)) || null;
        },

        stockFila(fila) {
            const kg = parseFloat(fila.dataset.stockKg);
            return Number.isFinite(kg) && kg > 0 ? kg : null;
        },

        nombreProductoFila(fila) {
            const label = fila.querySelector('.selector-catalogo-label')?.value || '';
            return label.split('—')[0].trim() || 'este producto';
        },

        limitesPedidoFila(fila) {
            const forma = fila.querySelector('.js-forma-pedido')?.value || 'kg';
            const stock = this.stockFila(fila);
            if (!stock) return null;
            const calibre = this.datosCalibre(fila);
            const empaqueId = fila.querySelector('.js-tipo-empaque')?.value;
            const empaque = empaqueId ? this.datosEmpaque(empaqueId) : null;

            if (forma === 'kg') {
                return { forma: 'kg', maxKg: stock };
            }
            if (!calibre || calibre.pesoKg <= 0) {
                return { forma, incompleto: true };
            }
            if (forma === 'unidades') {
                return {
                    forma: 'unidades',
                    maxUnidades: Math.floor(stock / calibre.pesoKg),
                    maxKg: stock,
                    calibre,
                };
            }
            if (!empaque) {
                return { forma: 'empaques', incompleto: true };
            }
            const pesoNeto = this.pesoNetoPorEmpaque(calibre, empaque);
            const maxEmp = pesoNeto > 0 ? Math.floor(stock / pesoNeto) : 0;
            return {
                forma: 'empaques',
                maxEmpaques: maxEmp,
                maxKg: stock,
                calibre,
                empaque,
                unidadesPorEmp: this.conteoEfectivoPorEmpaque(calibre, empaque),
            };
        },

        marcarErrorCantidad(fila, mensaje) {
            const forma = fila.querySelector('.js-forma-pedido')?.value || 'kg';
            const input = forma === 'kg'
                ? fila.querySelector('[data-field="cantidad"]')
                : fila.querySelector('.js-cantidad-pedido');
            const aviso = fila.querySelector('.js-aviso-limite');
            const resumen = fila.querySelector('.js-resumen-carga');
            if (input) {
                input.classList.toggle('is-invalid', !!mensaje);
            }
            if (aviso) {
                if (mensaje) {
                    aviso.textContent = mensaje;
                    aviso.classList.remove('d-none');
                } else {
                    aviso.textContent = '';
                    aviso.classList.add('d-none');
                }
            }
            if (resumen && mensaje) {
                resumen.textContent = '';
                resumen.classList.add('d-none');
            }
        },

        validarLimitesFila(fila) {
            const limites = this.limitesPedidoFila(fila);
            const forma = fila.querySelector('.js-forma-pedido')?.value || 'kg';
            const nombre = this.nombreProductoFila(fila);
            if (!limites) {
                this.marcarErrorCantidad(fila, null);
                return { ok: true };
            }
            if (limites.incompleto) {
                this.marcarErrorCantidad(fila, null);
                return { ok: true };
            }

            if (forma === 'kg') {
                const inputKg = fila.querySelector('[data-field="cantidad"]');
                const raw = inputKg?.value ?? '';
                const cant = raw === '' ? NaN : parseFloat(raw);
                if (raw !== '' && (!Number.isFinite(cant) || cant <= 0)) {
                    const msg = 'Indique una cantidad mayor a 0 kg.';
                    this.marcarErrorCantidad(fila, msg);
                    return { ok: false, mensaje: msg };
                }
                if (Number.isFinite(cant) && cant > limites.maxKg) {
                    const msg = 'No puede pedir más de ' + fmtNum(limites.maxKg, 1) + ' kg de ' + nombre
                        + ' (todo lo disponible en este almacén).';
                    this.marcarErrorCantidad(fila, msg);
                    return { ok: false, mensaje: msg };
                }
            } else if (forma === 'empaques') {
                const inputEmp = fila.querySelector('.js-cantidad-pedido');
                const raw = inputEmp?.value ?? '';
                const cant = raw === '' ? NaN : parseInt(raw, 10);
                if (raw !== '' && (!Number.isFinite(cant) || cant <= 0)) {
                    const msg = 'Debe pedir al menos 1 empaque.';
                    this.marcarErrorCantidad(fila, msg);
                    return { ok: false, mensaje: msg };
                }
                if (Number.isFinite(cant) && cant > limites.maxEmpaques) {
                    const msg = 'Solo puede armar hasta ' + fmtNum(limites.maxEmpaques) + ' '
                        + (limites.empaque?.nombre || 'empaques').toLowerCase()
                        + ' con el stock de este almacén.';
                    this.marcarErrorCantidad(fila, msg);
                    return { ok: false, mensaje: msg };
                }
            } else if (forma === 'unidades') {
                const inputUni = fila.querySelector('.js-cantidad-pedido');
                const raw = inputUni?.value ?? '';
                const cant = raw === '' ? NaN : parseInt(raw, 10);
                if (raw !== '' && (!Number.isFinite(cant) || cant <= 0)) {
                    const msg = 'Debe pedir al menos 1 unidad.';
                    this.marcarErrorCantidad(fila, msg);
                    return { ok: false, mensaje: msg };
                }
                if (Number.isFinite(cant) && cant > limites.maxUnidades) {
                    const msg = 'Solo puede pedir hasta ' + fmtNum(limites.maxUnidades) + ' unidades de '
                        + nombre + ' (todo lo disponible).';
                    this.marcarErrorCantidad(fila, msg);
                    return { ok: false, mensaje: msg };
                }
            }

            this.marcarErrorCantidad(fila, null);
            return { ok: true };
        },

        validarCargaPlanta() {
            const filas = document.querySelectorAll('.producto-recogida-row');
            if (!filas.length) {
                return { ok: false, mensaje: 'Elija almacenes de recogida y asigne al menos un producto.' };
            }
            let tieneProducto = false;
            let primerError = null;
            filas.forEach((fila) => {
                const ref = fila.querySelector('.selector-catalogo-value')?.value;
                const forma = fila.querySelector('.js-forma-pedido')?.value || 'kg';
                const limites = this.limitesPedidoFila(fila);
                if (ref && limites?.incompleto) {
                    if (!primerError) {
                        primerError = forma === 'empaques' || forma === 'kg'
                            ? 'Elija producto, empaque y calibre antes de continuar.'
                            : 'Elija producto y calibre antes de continuar.';
                    }
                }
                const empaqueId = fila.querySelector('.js-tipo-empaque')?.value;
                if (ref && (forma === 'empaques' || forma === 'kg') && !empaqueId) {
                    if (!primerError) {
                        primerError = 'Seleccione el tipo de empaque para cada producto del envío.';
                    }
                }
                let cant = 0;
                if (forma === 'kg') {
                    cant = parseFloat(fila.querySelector('[data-field="cantidad"]')?.value);
                } else {
                    cant = parseFloat(fila.querySelector('.js-cantidad-pedido')?.value);
                }
                if (ref && Number.isFinite(cant) && cant > 0) {
                    const v = this.validarLimitesFila(fila);
                    if (v.ok) {
                        tieneProducto = true;
                    } else if (!primerError) {
                        primerError = v.mensaje;
                    }
                } else if (ref && forma !== 'kg') {
                    const rawCant = fila.querySelector('.js-cantidad-pedido')?.value ?? '';
                    if (rawCant !== '') {
                        const v = this.validarLimitesFila(fila);
                        if (!v.ok && !primerError) {
                            primerError = v.mensaje;
                        }
                    }
                } else if (ref && forma === 'kg') {
                    const v = this.validarLimitesFila(fila);
                    if (!v.ok && !primerError) {
                        primerError = v.mensaje;
                    }
                }
            });
            if (!tieneProducto) {
                return { ok: false, mensaje: primerError || 'Indique producto y cantidad en cada recogida.' };
            }
            if (primerError) {
                return { ok: false, mensaje: primerError };
            }
            return { ok: true };
        },

        formatRutaResumen(opts) {
            const km = opts.km;
            const min = opts.min;
            const paradas = opts.paradas || 0;
            const straight = !!opts.straight;
            if (!km && !min) return '—';
            let t = straight
                ? 'Distancia aproximada en línea recta: unos ' + km + ' km'
                : 'Camino estimado: unos ' + km + ' km';
            if (min) {
                t += ', tiempo de viaje ~' + min + ' min';
            }
            if (paradas > 2) {
                t += ' (incluye ' + (paradas - 1) + ' punto' + (paradas - 1 === 1 ? '' : 's') + ' de recogida)';
            }
            return t;
        },

        refrescarFilaEmpaque(fila) {
            if (!fila) return;
            const forma = fila.querySelector('.js-forma-pedido')?.value || 'kg';
            this.actualizarSugerenciaKg(fila);
            if (forma !== 'kg') {
                this.actualizarEtiquetasForma(fila, forma);
                this.actualizarSugerenciasEmpaque(fila);
            }
        },

        actualizarSugerenciaKg(fila) {
            const el = fila.querySelector('.js-sugerencia-kg');
            const forma = fila.querySelector('.js-forma-pedido')?.value;
            const stock = this.stockFila(fila);
            const cantInput = fila.querySelector('[data-field="cantidad"]');
            const calibre = this.datosCalibre(fila);
            if (!el) return;

            if (forma !== 'kg') {
                el.classList.add('d-none');
                el.textContent = '';
                return;
            }

            const nombre = this.nombreProductoFila(fila);
            const cant = parseFloat(cantInput?.value);
            const tieneCalibres = (fila.querySelector('.js-calibre')?.options?.length || 0) > 1;

            if (!stock) {
                el.classList.add('d-none');
                el.textContent = '';
                return;
            }

            let texto = 'Disponible en almacén: <strong>' + fmtNum(stock, 1) + ' kg</strong> de ' + nombre + '.';

            if (!calibre && tieneCalibres) {
                texto += ' Elija el tamaño para ver cuántas unidades equivalen a los kg que pedirá.';
                el.innerHTML = texto;
                el.classList.remove('d-none');
                return;
            }

            if (Number.isFinite(cant) && cant > 0) {
                if (calibre && calibre.pesoKg > 0) {
                    const unidades = this.estimarUnidadesDesdeKg(cant, calibre);
                    const gUnit = Math.round(calibre.pesoKg * 1000);
                    texto += ' Con <strong>' + fmtNum(cant, 1) + ' kg</strong> son unas <strong>'
                        + fmtNum(unidades) + ' unidades</strong> (tamaño ' + calibre.nombre.toLowerCase()
                        + ', ~' + fmtNum(gUnit) + ' g c/u).';
                } else {
                    texto += ' Pedirá <strong>' + fmtNum(cant, 1) + ' kg</strong>.';
                }
            } else {
                texto += ' Puede pedir hasta <strong>' + fmtNum(stock, 1) + ' kg</strong>';
                if (calibre && calibre.pesoKg > 0) {
                    const maxU = this.estimarUnidadesDesdeKg(stock, calibre);
                    texto += ' (unas ' + fmtNum(maxU) + ' unidades si usa tamaño ' + calibre.nombre.toLowerCase() + ')';
                }
                texto += '.';
            }

            el.innerHTML = texto;
            el.classList.remove('d-none');
            this.validarLimitesFila(fila);
        },

        actualizarEtiquetasForma(fila, forma) {
            const lblCant = fila.querySelector('.js-lbl-cantidad-pedido');
            const inpCant = fila.querySelector('.js-cantidad-pedido');

            if (lblCant && inpCant) {
                if (forma === 'empaques') {
                    lblCant.textContent = '¿Cuántos empaques?';
                    inpCant.placeholder = 'Ej: 100';
                } else if (forma === 'unidades') {
                    lblCant.textContent = '¿Cuántas unidades?';
                    inpCant.placeholder = 'Ej: 1.200';
                }
            }
        },

        actualizarSugerenciasEmpaque(fila) {
            const sugerencia = fila.querySelector('.js-sugerencia-empaque');
            const forma = fila.querySelector('.js-forma-pedido')?.value;
            const empaqueId = fila.querySelector('.js-tipo-empaque')?.value;
            const stock = this.stockFila(fila);
            const calibre = this.datosCalibre(fila);
            const empaque = empaqueId ? this.datosEmpaque(empaqueId) : null;

            if (!sugerencia || forma === 'kg') {
                if (sugerencia) {
                    sugerencia.textContent = '';
                    sugerencia.classList.add('d-none');
                }
                return;
            }

            if (!stock) {
                sugerencia.textContent = 'Seleccione un producto con stock para ver la sugerencia.';
                sugerencia.classList.remove('d-none');
                return;
            }

            if (!calibre || calibre.pesoKg <= 0) {
                sugerencia.textContent = 'Elija el calibre para calcular cuántos caben con su stock.';
                sugerencia.classList.remove('d-none');
                return;
            }

            const nombreEmpaque = empaque?.nombre || 'este empaque';
            let texto = '';

            if (forma === 'unidades') {
                const maxU = Math.floor(stock / calibre.pesoKg);
                texto = 'Puede pedir hasta <strong>' + fmtNum(maxU) + ' unidades</strong> '
                    + '(hay ' + fmtNum(stock, 1) + ' kg en almacén).';
            } else {
                if (!empaqueId) {
                    sugerencia.innerHTML = 'Elija el tipo de empaque para saber cuántos puede armar.';
                    sugerencia.classList.remove('d-none');
                    return;
                }
                const pesoNetoEmpaque = this.pesoNetoPorEmpaque(calibre, empaque);
                if (pesoNetoEmpaque <= 0) {
                    sugerencia.classList.add('d-none');
                    sugerencia.textContent = '';
                    return;
                }
                const unidadesPorEmp = this.conteoEfectivoPorEmpaque(calibre, empaque);
                const maxEmp = Math.floor(stock / pesoNetoEmpaque);
                const kgUsados = Math.round(maxEmp * pesoNetoEmpaque);
                const unidades = maxEmp * unidadesPorEmp;
                texto = 'Con tamaño <strong>' + calibre.nombre + '</strong> puede armar hasta <strong>' + fmtNum(maxEmp) + ' '
                    + (nombreEmpaque.toLowerCase()) + 's</strong>'
                    + ' (unas ' + fmtNum(unidades) + ' unidades, ' + fmtNum(kgUsados, 1) + ' kg en total).';
            }

            sugerencia.innerHTML = texto;
            sugerencia.classList.remove('d-none');
            this.validarLimitesFila(fila);
        },

        limpiarResumenCarga(fila) {
            const resumen = fila.querySelector('.js-resumen-carga');
            if (resumen) {
                resumen.textContent = '';
                resumen.classList.add('d-none');
            }
            const cantKg = fila.querySelector('[data-field="cantidad"]');
            if (cantKg && fila.querySelector('.js-forma-pedido')?.value !== 'kg') {
                cantKg.value = '';
            }
        },

        syncObservacionesCargaFila(fila) {
            const obs = fila.querySelector('[data-field="observaciones"]');
            if (!obs) return;
            const forma = fila.querySelector('.js-forma-pedido')?.value || 'kg';
            let cantPedido = fila.querySelector('.js-cantidad-pedido')?.value || '';
            if (forma === 'kg') {
                cantPedido = fila.querySelector('[data-field="cantidad"]')?.value || cantPedido;
            }
            const empaque = fila.querySelector('.js-tipo-empaque')?.selectedOptions?.[0]?.textContent?.trim() || '';
            const calibre = fila.querySelector('.js-calibre')?.selectedOptions?.[0]?.textContent?.trim() || '';
            let usuario = obs.value || '';
            if (usuario.includes('@carga:')) {
                usuario = usuario.replace(/@carga:.+?@\s*/s, '').trim();
            } else if (/^\d+[\d.,]*\s+(empaques?|unidades)\b/im.test((usuario.split('\n')[0] || ''))) {
                usuario = usuario.split('\n').slice(1).join('\n').trim();
            }
            if (forma === 'kg' || !cantPedido) {
                if (forma === 'kg' && cantPedido && empaque) {
                    let meta = cantPedido + ' kg · ' + empaque;
                    if (calibre) meta += ' · ' + calibre;
                    obs.value = '@carga:' + meta + '@' + (usuario ? ' ' + usuario : '');
                    return;
                }
                obs.value = usuario;
                return;
            }
            let meta = '';
            if (forma === 'empaques' && empaque) {
                meta = cantPedido + ' empaques · ' + empaque;
                if (calibre) meta += ' · ' + calibre;
            } else if (forma === 'unidades') {
                meta = cantPedido + ' unidades';
                if (calibre) meta += ' · ' + calibre;
            }
            if (!meta) {
                obs.value = usuario;
                return;
            }
            obs.value = '@carga:' + meta + '@' + (usuario ? ' ' + usuario : '');
        },

        mostrarResumenCarga(fila, d, forma) {
            const resumen = fila.querySelector('.js-resumen-carga');
            if (!resumen) return;

            let texto = '';
            if (forma === 'unidades') {
                texto = '<i class="fas fa-check-circle text-success mr-1"></i> Enviará <strong>'
                    + fmtNum(d.unidades_totales ?? d.empaques_calculados) + ' unidades</strong>'
                    + ' → <strong>' + fmtNum(d.peso_neto_kg, 1) + ' kg</strong> de producto.';
            } else {
                const pallets = d.numero_pallets === 1 ? '1 pallet' : fmtNum(d.numero_pallets) + ' pallets';
                texto = '<i class="fas fa-check-circle text-success mr-1"></i> Enviará <strong>'
                    + fmtNum(d.empaques_calculados) + ' ' + (d.empaques_calculados === 1 ? 'empaque' : 'empaques')
                    + '</strong> en <strong>' + pallets + '</strong>'
                    + ' → <strong>' + fmtNum(d.peso_neto_kg, 1) + ' kg</strong> de producto'
                    + ' (con empaque: ' + fmtNum(d.peso_bruto_kg, 1) + ' kg).';
            }

            resumen.innerHTML = texto;
            resumen.classList.remove('d-none');
            this.validarLimitesFila(fila);
        },

        initFilaEmpaque(fila) {
            if (fila.dataset.fase2Init === '1') return;
            fila.dataset.fase2Init = '1';

            const forma = fila.querySelector('.js-forma-pedido');
            const empaque = fila.querySelector('.js-tipo-empaque');
            const calibre = fila.querySelector('.js-calibre');
            const cantPedido = fila.querySelector('.js-cantidad-pedido');
            const modoKg = fila.querySelector('.js-modo-kg');
            if (!forma) return;

            const tipos = this.catalogos.tiposEmpaque || [];
            if (empaque) {
                empaque.innerHTML = '<option value="">Seleccione…</option>';
            }
            tipos.forEach((t) => {
                if (!empaque) return;
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nombre;
                if (t.ayuda || t.descripcion) {
                    opt.title = t.ayuda || t.descripcion;
                }
                empaque.appendChild(opt);
            });

            const aplicarCalibreSugerido = () => {
                if (!calibre) return;
                const sug = fila.dataset.calibreSugeridoId;
                if (sug && calibre.querySelector('option[value="' + sug + '"]')) {
                    calibre.value = sug;
                }
            };

            const poblarOpcionesCalibres = (calibres) => {
                if (!calibre) return;
                const lista = this.deduplicarCalibresLista(calibres);
                fila._calibresActivos = lista;
                calibre.innerHTML = '<option value="">Seleccione calibre…</option>';
                if (!lista.length) {
                    const vacio = document.createElement('option');
                    vacio.value = '';
                    vacio.textContent = 'Sin calibres — Catálogos logística → Tamaño/conteo';
                    vacio.disabled = true;
                    calibre.appendChild(vacio);
                    return;
                }
                lista.forEach((c) => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.nombre;
                    opt.title = c.peso_promedio_kg
                        ? '~' + Math.round(parseFloat(c.peso_promedio_kg) * 1000) + ' g por unidad'
                        : '';
                    opt.dataset.conteo = c.conteo_por_empaque;
                    opt.dataset.pesoKg = c.peso_promedio_kg;
                    calibre.appendChild(opt);
                });
                aplicarCalibreSugerido();
                if (calibre.value) {
                    calibre.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            const calibresEstaticosFila = () => {
                let calibres = this.catalogos.tamanoConteo || [];
                const insumoId = this.insumoIdCalibreFila(fila);
                if (insumoId) {
                    const porInsumo = calibres.filter((c) => parseInt(c.id_producto, 10) === insumoId);
                    if (porInsumo.length) {
                        calibres = porInsumo;
                    } else {
                        const raiz = (this.nombreProductoFila(fila).split(/\s+/)[0] || '').toLowerCase();
                        if (raiz) {
                            calibres = calibres.filter((c) => {
                                const nombre = (c.producto || '').toLowerCase();
                                return nombre.includes(raiz);
                            });
                        }
                    }
                }
                return this.deduplicarCalibresLista(calibres);
            };

            const recargarCalibres = async () => {
                if (!calibre) return;
                const productoRef = fila.querySelector('.selector-catalogo-value')?.value;
                if (!productoRef) {
                    poblarOpcionesCalibres([]);
                    return;
                }
                calibre.disabled = true;
                try {
                    const params = new URLSearchParams({ producto_ref: productoRef });
                    const res = await fetch(ENVIO_API + '/calibres?' + params.toString(), {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (res.ok) {
                        const json = await res.json();
                        poblarOpcionesCalibres(json.data || []);
                    } else {
                        poblarOpcionesCalibres(calibresEstaticosFila());
                    }
                } catch (e) {
                    poblarOpcionesCalibres(calibresEstaticosFila());
                }
                calibre.disabled = false;
                const formaVal = forma?.value;
                if (formaVal === 'kg') {
                    this.actualizarSugerenciaKg(fila);
                } else {
                    this.actualizarSugerenciasEmpaque(fila);
                }
            };

            const toggleModo = () => {
                const val = forma.value;
                const campoTipo = fila.querySelector('.js-campo-tipo-empaque');
                const campoCalibre = fila.querySelector('.js-campo-calibre');
                const campoCant = fila.querySelector('.js-campo-cant-alt');
                const sugerenciaEmp = fila.querySelector('.js-sugerencia-empaque');
                const sugerenciaKg = fila.querySelector('.js-sugerencia-kg');

                if (modoKg) modoKg.classList.toggle('d-none', val !== 'kg');

                if (val === 'kg') {
                    campoTipo?.classList.remove('d-none');
                    campoCalibre?.classList.remove('d-none');
                    campoCant?.classList.add('d-none');
                    if (cantPedido) cantPedido.value = '';
                    const lblCal = campoCalibre?.querySelector('label');
                    if (lblCal) lblCal.textContent = 'Calibre';
                    sugerenciaEmp?.classList.add('d-none');
                    if (sugerenciaEmp) sugerenciaEmp.textContent = '';
                    this.limpiarResumenCarga(fila);
                    this.actualizarSugerenciaKg(fila);
                    recargarCalibres();
                } else if (val === 'empaques') {
                    campoTipo?.classList.remove('d-none');
                    campoCalibre?.classList.remove('d-none');
                    campoCant?.classList.remove('d-none');
                    const lblCal = campoCalibre?.querySelector('label');
                    if (lblCal) lblCal.textContent = 'Calibre';
                    sugerenciaKg?.classList.add('d-none');
                    if (sugerenciaKg) sugerenciaKg.textContent = '';
                    this.actualizarEtiquetasForma(fila, val);
                    this.actualizarSugerenciasEmpaque(fila);
                    recargarCalibres();
                } else if (val === 'unidades') {
                    campoTipo?.classList.add('d-none');
                    campoCalibre?.classList.remove('d-none');
                    campoCant?.classList.remove('d-none');
                    const lblCal = campoCalibre?.querySelector('label');
                    if (lblCal) lblCal.textContent = 'Calibre';
                    sugerenciaKg?.classList.add('d-none');
                    if (sugerenciaKg) sugerenciaKg.textContent = '';
                    this.actualizarEtiquetasForma(fila, val);
                    this.actualizarSugerenciasEmpaque(fila);
                    recargarCalibres();
                }
            };

            forma.addEventListener('change', () => {
                cantPedido.value = '';
                if (empaque) empaque.value = '';
                if (calibre) calibre.value = '';
                this.limpiarResumenCarga(fila);
                toggleModo();
            });
            toggleModo();

            const cantKgInput = fila.querySelector('[data-field="cantidad"]');
            if (cantKgInput && cantKgInput.dataset.kgSugerenciaListener !== '1') {
                cantKgInput.dataset.kgSugerenciaListener = '1';
                cantKgInput.addEventListener('input', () => {
                    this.actualizarSugerenciaKg(fila);
                    this.validarLimitesFila(fila);
                    this.syncObservacionesCargaFila(fila);
                });
                cantKgInput.addEventListener('change', () => {
                    this.actualizarSugerenciaKg(fila);
                    this.validarLimitesFila(fila);
                    this.syncObservacionesCargaFila(fila);
                });
            }

            fila.addEventListener('producto-seleccionado', () => {
                cantPedido.value = '';
                if (empaque) empaque.value = '';
                this.limpiarResumenCarga(fila);
                recargarCalibres();
                this.actualizarSugerenciaKg(fila);
            });

            const recalcular = async () => {
                const calibreId = parseInt(calibre?.value, 10);
                const empaqueId = parseInt(empaque?.value, 10);
                const cant = parseFloat(cantPedido?.value);
                const formaVal = forma?.value;

                this.actualizarSugerenciasEmpaque(fila);

                if (!calibreId || !formaVal || formaVal === 'kg' || !cant || cant <= 0) {
                    if (formaVal !== 'kg') {
                        this.limpiarResumenCarga(fila);
                        this.validarLimitesFila(fila);
                    }
                    if (formaVal === 'kg') {
                        this.syncObservacionesCargaFila(fila);
                    }
                    return;
                }

                try {
                    const res = await fetch(ENVIO_API + '/carga/calcular', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({
                            catalogo_tamano_conteo_id: calibreId,
                            tipo_empaque_id: empaqueId || null,
                            forma_pedido: formaVal,
                            cantidad_pedido: cant,
                        }),
                    });
                    const json = await res.json();
                    const d = json.data || {};
                    const cantKg = fila.querySelector('[data-field="cantidad"]');
                    if (cantKg && d.peso_neto_kg) {
                        cantKg.value = d.peso_neto_kg;
                    }
                    const v = this.validarLimitesFila(fila);
                    if (v.ok) {
                        this.mostrarResumenCarga(fila, d, formaVal);
                    } else {
                        this.limpiarResumenCarga(fila);
                    }
                    this.validarCapacidadVehiculo();
                    this.actualizarSugerenciaVehiculo();
                    this.syncObservacionesCargaFila(fila);
                } catch (e) {
                    console.warn('Cálculo carga', e);
                }
            };

            [calibre, empaque, cantPedido].forEach((el) => {
                if (el) el.addEventListener('change', recalcular);
                if (el) el.addEventListener('input', () => {
                    recalcular();
                    this.validarLimitesFila(fila);
                });
            });
            empaque?.addEventListener('change', () => {
                this.actualizarHintEmpaque(fila);
                recargarCalibres();
                this.actualizarSugerenciasEmpaque(fila);
                recalcular();
            });
            calibre?.addEventListener('change', () => {
                this.actualizarSugerenciasEmpaque(fila);
                this.actualizarSugerenciaKg(fila);
                recalcular();
            });
        },

        pesoTotalKg() {
            if (this.flujoEsPdv()) {
                return this.pesoTotalKgPdv();
            }
            let total = 0;
            if (this.flujoEsMayorista()) {
                document.querySelectorAll('#flujo-mayorista .traslado-producto-row').forEach((fila) => {
                    const cantKg = fila.querySelector('[data-field="cantidad"]');
                    total += parseFloat(cantKg?.value) || 0;
                });
                return total;
            }
            document.querySelectorAll('.producto-recogida-row').forEach((fila) => {
                const forma = fila.querySelector('.js-forma-pedido')?.value;
                const cantKg = fila.querySelector('[data-field="cantidad"]');
                if (forma === 'kg') {
                    total += parseFloat(cantKg?.value) || 0;
                } else if (cantKg?.value) {
                    total += parseFloat(cantKg.value) || 0;
                }
            });
            return total;
        },

        volumenTotalM3() {
            const peso = this.pesoTotalKg();
            if (peso <= 0) return null;
            return Math.round((peso / 200) * 1000) / 1000;
        },

        textoCapacidadAmigable(peso, cap, pct) {
            const restante = Math.max(0, cap - peso);
            let estado = 'Va con buen margen de espacio.';
            if (pct >= 100) estado = '¡Atención! La carga supera la capacidad del camión.';
            else if (pct >= 85) estado = 'Queda poco espacio libre.';
            else if (pct >= 50) estado = 'Va a la mitad de su capacidad.';
            else if (pct < 5) estado = 'Va casi vacío.';

            return 'Este camión admite hasta ' + fmtNum(cap) + ' kg. '
                + 'Lleva ' + fmtNum(peso, 1) + ' kg — quedan ~' + fmtNum(restante, 1) + ' kg libres. '
                + estado;
        },

        async validarCapacidadVehiculo() {
            const vehiculoId = this.vehiculoIdSeleccionado();
            const ui = this.elementosCapacidad();
            const alerta = ui.alerta;
            const resumen = ui.resumen;
            const wrap = ui.wrap;
            if (!vehiculoId || !alerta) return;

            const peso = this.pesoTotalKg();
            if (peso <= 0) {
                alerta.style.display = 'none';
                if (wrap) wrap.classList.add('d-none');
                if (resumen) resumen.textContent = '';
                ui.btnPreview?.classList.add('d-none');
                return;
            }

            const volumen = this.volumenTotalM3();
            const params = new URLSearchParams();
            params.set('peso_kg', String(peso));
            if (volumen != null && volumen > 0) {
                params.set('volumen_m3', String(volumen));
            }

            try {
                const url = VEHICULOS_CATALOGO_URL + '/' + encodeURIComponent(vehiculoId)
                    + '/preview-carga?' + params.toString();
                const res = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                const pctPeso = data.porcentaje_peso ?? 0;
                const pctVol = data.porcentaje_volumen ?? 0;
                const pct = data.porcentaje_uso ?? Math.max(pctPeso, pctVol);
                const capKg = data.capacidad_kg ?? 0;
                const capM3 = data.m3_util ?? data.capacidad_m3 ?? 0;

                if (wrap) wrap.classList.remove('d-none');
                if (resumen) {
                    resumen.textContent = data.recomendacion || this.textoCapacidadAmigable(peso, capKg, pctPeso);
                }
                if (ui.pctPeso) {
                    ui.pctPeso.textContent = fmtNum(pctPeso, 1) + '%';
                    ui.pctPeso.className = pctPeso >= 100 ? 'text-danger' : pctPeso >= 85 ? 'text-warning' : 'text-success';
                }
                if (ui.pctVol) {
                    ui.pctVol.textContent = pctVol > 0 ? fmtNum(pctVol, 1) + '%' : '—';
                    ui.pctVol.className = pctVol >= 100 ? 'text-danger' : pctVol >= 85 ? 'text-warning' : 'text-success';
                }
                if (ui.barraPeso) {
                    ui.barraPeso.style.width = Math.min(100, Math.max(0, pctPeso)) + '%';
                    ui.barraPeso.className = 'veh-carga-preview-bar__fill veh-carga-preview-bar__fill--peso'
                        + (pctPeso >= 100 ? ' is-danger' : pctPeso >= 85 ? ' is-warning' : '');
                }
                if (ui.barraVol) {
                    ui.barraVol.style.width = Math.min(100, Math.max(0, pctVol)) + '%';
                    ui.barraVol.className = 'veh-carga-preview-bar__fill veh-carga-preview-bar__fill--vol'
                        + (pctVol >= 100 ? ' is-danger' : pctVol >= 85 ? ' is-warning' : '');
                }
                if (ui.txtPeso) {
                    ui.txtPeso.textContent = fmtNum(peso, 1) + ' kg de ' + fmtNum(capKg, 0) + ' kg máx.';
                }
                if (ui.txtVol && data.carga_volumen_m3 != null && capM3 > 0) {
                    ui.txtVol.textContent = fmtNum(data.carga_volumen_m3, 2) + ' m³ de ' + fmtNum(capM3, 1) + ' m³ útiles';
                } else if (ui.txtVol) {
                    ui.txtVol.textContent = 'Volumen no estimado';
                }
                ui.btnPreview?.classList.remove('d-none');

                if (!data.ok || pct > 100) {
                    alerta.textContent = data.mensaje || 'La carga supera lo que puede llevar este camión. Reduzca cantidad o planifique otro camión.';
                    alerta.style.display = 'block';
                    ui.particion?.classList.remove('d-none');
                } else {
                    alerta.style.display = 'none';
                    ui.particion?.classList.add('d-none');
                }
            } catch (e) {
                console.warn('Capacidad', e);
            }
        },
    };

    document.getElementById('hora_recogida')?.addEventListener('change', () => window.PedidoFase2.actualizarHoraEntrega());
    document.getElementById('hora_recogida_traslado')?.addEventListener('change', () => window.PedidoFase2.actualizarHoraEntrega());
    document.getElementById('hora_recogida_pdv')?.addEventListener('change', () => window.PedidoFase2.actualizarHoraEntrega());
    document.getElementById('vehiculoid_create')?.addEventListener('change', () => {
        window.PedidoFase2.validarCapacidadVehiculo();
        window.PedidoFase2.actualizarSugerenciaVehiculo();
    });
    document.getElementById('btnVerPreviewVehiculoAsignacion')?.addEventListener('click', () => {
        window.PedidoFase2.abrirPreviewVehiculoAsignacion();
    });
    document.getElementById('transportista_usuarioid_create')?.addEventListener('change', () => {
        window.PedidoFase2.actualizarSugerenciaVehiculo();
    });
})();
</script>
