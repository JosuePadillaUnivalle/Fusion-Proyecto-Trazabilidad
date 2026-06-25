<script>
    window.AgroFusionPlanificacionCosecha = window.AgroFusionPlanificacionCosecha || {};

    AgroFusionPlanificacionCosecha.vincular = function (config) {
        const panel = document.getElementById('planificacionCosechaPanel');
        const superficieWrap = document.getElementById('superficieWrap');
        const supInput = document.getElementById(config.superficieInputId || 'superficie');
        const cantInput = document.getElementById(config.cantidadInputId || 'cantidad_semilla_planificada');
        const cantWrap = document.getElementById(config.cantidadWrapId || 'cantidadSemillaWrap');
        const unidadSpan = document.getElementById(config.unidadSpanId || 'cantidadSemillaUnidad');
        const semillaWrap = document.getElementById('selector_wrap_' + (config.selectorId || 'lote_semilla'));
        const dosisPreview = document.getElementById('dosisSiembraPreview');
        const dosisTexto = document.getElementById('dosisSiembraTexto');
        const calibreSelect = document.getElementById('planCalibreSelect');
        const objetivoUnidades = document.getElementById('planObjetivoUnidades');
        const objetivoEmpaques = document.getElementById('planObjetivoEmpaques');
        const objetivoUnidadesWrap = document.getElementById('planObjetivoUnidadesWrap');
        const objetivoEmpaquesWrap = document.getElementById('planObjetivoEmpaquesWrap');
        const cosechaPreview = document.getElementById('planCosechaPreview');
        const errorPreview = document.getElementById('planErrorPreview');
        const errorTexto = document.getElementById('planErrorTexto');
        const limitePreview = document.getElementById('planLimiteAviso');
        const limiteTexto = document.getElementById('planLimiteTexto');
        const modoRadios = document.querySelectorAll('input[name="plan_modo_ui"]');
        const modoCards = document.querySelectorAll('.plan-modo-card');
        const resultadoGrid = document.getElementById('planResultadoGrid');
        const insumoUsoNombre = document.getElementById('planInsumoUsoNombre');
        const planModoHidden = document.getElementById('planModoHidden');
        const planObjetivoEmpaquesHidden = document.getElementById('planObjetivoEmpaquesHidden');
        const planObjetivoUnidadesHidden = document.getElementById('planObjetivoUnidadesHidden');
        const urlPlanificar = config.urlPlanificar;
        const onHectareasChange = config.onHectareasChange;

        if (!supInput || !semillaWrap || !urlPlanificar) {
            return;
        }

        let modo = 'hectareas';
        let insumoId = '';
        let calibres = [];
        const calibreHidden = document.getElementById('catalogotamanoconteoid');
        let calibreInicial = config.calibreInicial || calibreHidden?.value || '';
        let timer = null;
        let semillaEditadaManual = false;
        let haEditadaManual = false;
        let planificacionErrorActiva = false;
        let ultimoMensajeError = '';

        function nombreInsumoSeleccionado() {
            return semillaWrap?.querySelector('.selector-catalogo-label')?.value?.trim() || 'Material de siembra';
        }

        function actualizarEtiquetaInsumo() {
            const nombre = nombreInsumoSeleccionado();
            if (insumoUsoNombre) {
                insumoUsoNombre.textContent = nombre ? '(' + nombre + ')' : '';
            }
        }

        function toggleSuperficie(mostrar) {
            if (superficieWrap) {
                superficieWrap.classList.toggle('d-none', !mostrar);
            }
            if (mostrar) {
                supInput.setAttribute('required', 'required');
            } else {
                supInput.removeAttribute('required');
                supInput.value = '';
                if (typeof onHectareasChange === 'function') {
                    onHectareasChange(0);
                }
            }
        }

        function fmtNum(n, dec) {
            return Number(n).toLocaleString('es-BO', {
                minimumFractionDigits: dec ?? 0,
                maximumFractionDigits: dec ?? 0,
            });
        }

        function sanitizarEntradaNumerica(input, soloEnteros) {
            if (!input) {
                return;
            }
            let v = String(input.value ?? '');
            if (soloEnteros) {
                v = v.replace(/\D/g, '');
            } else {
                v = v.replace(/,/g, '.');
                v = v.replace(/[^\d.]/g, '');
                const punto = v.indexOf('.');
                if (punto !== -1) {
                    v = v.slice(0, punto + 1) + v.slice(punto + 1).replace(/\./g, '');
                }
            }
            if (input.value !== v) {
                input.value = v;
            }
        }

        function enlazarSoloNumeros(input, soloEnteros, onChange) {
            if (!input) {
                return;
            }
            input.addEventListener('keydown', function (e) {
                const teclasOk = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                if (teclasOk.includes(e.key) || e.ctrlKey || e.metaKey) {
                    return;
                }
                if (soloEnteros) {
                    if (!/^\d$/.test(e.key)) {
                        e.preventDefault();
                    }
                } else if (!/^[\d.,]$/.test(e.key)) {
                    e.preventDefault();
                }
            });
            input.addEventListener('input', function () {
                sanitizarEntradaNumerica(input, soloEnteros);
                if (typeof onChange === 'function') {
                    onChange();
                }
            });
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const texto = (e.clipboardData || window.clipboardData).getData('text') || '';
                const limpio = soloEnteros
                    ? texto.replace(/\D/g, '')
                    : texto.replace(/,/g, '.').replace(/[^\d.]/g, '');
                input.value = limpio;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        function modoActual() {
            const checked = document.querySelector('input[name="plan_modo_ui"]:checked');
            return checked ? checked.value : 'hectareas';
        }

        function actualizarModoUi() {
            modo = modoActual();
            if (planModoHidden) {
                planModoHidden.value = modo;
            }
            if (planObjetivoEmpaquesHidden) {
                planObjetivoEmpaquesHidden.value = modo === 'empaques' ? (objetivoEmpaques?.value || '') : '';
            }
            if (planObjetivoUnidadesHidden) {
                planObjetivoUnidadesHidden.value = modo === 'unidades' ? (objetivoUnidades?.value || '') : '';
            }
            modoCards.forEach(function (card) {
                const radio = card.querySelector('input[type="radio"]');
                card.classList.toggle('active', !!(radio && radio.checked));
            });
            objetivoUnidadesWrap?.classList.toggle('d-none', modo !== 'unidades');
            objetivoEmpaquesWrap?.classList.toggle('d-none', modo !== 'empaques');
            if (superficieWrap) {
                superficieWrap.classList.remove('d-none');
                superficieWrap.classList.toggle('plan-superficie-calculada', modo !== 'hectareas');
            }
            supInput.readOnly = modo !== 'hectareas';
            supInput.classList.toggle('bg-light', modo !== 'hectareas');
        }

        function renderResultado(res) {
            if (!resultadoGrid) return;
            const emp = etiquetaEmpaqueCosecha(res.calibre?.empaque_label || 'Cajas');
            resultadoGrid.innerHTML =
                '<div class="plan-resultado-item"><strong>' + fmtNum(res.unidades_estimadas, 0) + '</strong><span>Unidades</span></div>'
                + '<div class="plan-resultado-item"><strong>' + fmtNum(res.empaques_estimados, 0) + '</strong><span>' + emp + '</span></div>'
                + '<div class="plan-resultado-item"><strong>' + fmtNum(res.kg_cosecha_estimados, 0) + ' kg</strong><span>Cosecha</span></div>';
        }

        function mostrarError(msg) {
            if (!errorPreview || !errorTexto) return;
            if (!msg) {
                errorPreview.classList.add('d-none');
                errorTexto.textContent = '';
                planificacionErrorActiva = false;
                ultimoMensajeError = '';
                return;
            }
            errorTexto.textContent = msg;
            errorPreview.classList.remove('d-none');
            panel?.classList.remove('d-none');
            cosechaPreview?.classList.add('d-none');
            planificacionErrorActiva = true;
            ultimoMensajeError = msg;
        }

        function mostrarAvisoLimite(msg) {
            if (!limitePreview || !limiteTexto) return;
            if (!msg) {
                limitePreview.classList.add('d-none');
                limiteTexto.textContent = '';
                return;
            }
            limiteTexto.textContent = msg;
            limitePreview.classList.remove('d-none');
        }

        function calcularLimites(ctx, calibre) {
            const maxHa = parseFloat(ctx?.hectareas_max_stock ?? NaN);
            if (!Number.isFinite(maxHa) || maxHa < 0) {
                return { maxHa: null, maxUnidades: null, maxEmpaques: null };
            }
            if (maxHa <= 0) {
                return { maxHa: 0, maxUnidades: 0, maxEmpaques: 0 };
            }
            const rendimiento = parseFloat(ctx?.rendimiento_kg_ha || 0);
            if (!calibre || !Number.isFinite(rendimiento) || rendimiento <= 0) {
                return { maxHa: maxHa, maxUnidades: null, maxEmpaques: null };
            }
            const pesoUnit = Math.max(0.0001, parseFloat(calibre.peso_promedio_kg));
            const conteo = Math.max(1, parseInt(calibre.conteo_por_empaque, 10) || 1);
            const kgMax = rendimiento * maxHa;
            const maxUnidades = Math.round(kgMax / pesoUnit);
            const maxEmpaques = maxUnidades <= 0 ? 0 : Math.ceil(maxUnidades / conteo);

            return { maxHa: maxHa, maxUnidades: maxUnidades, maxEmpaques: maxEmpaques };
        }

        function actualizarAtributosMaximos() {
            const limites = calcularLimites(ultimoContexto, obtenerCalibreActivo());
            if (objetivoUnidades) {
                if (limites.maxUnidades !== null) {
                    objetivoUnidades.max = limites.maxUnidades;
                } else {
                    objetivoUnidades.removeAttribute('max');
                }
            }
            if (objetivoEmpaques) {
                if (limites.maxEmpaques !== null) {
                    objetivoEmpaques.max = limites.maxEmpaques;
                } else {
                    objetivoEmpaques.removeAttribute('max');
                }
            }
            if (supInput) {
                if (limites.maxHa !== null) {
                    supInput.max = limites.maxHa;
                } else {
                    supInput.removeAttribute('max');
                }
            }
            const stock = parseFloat(ultimoContexto?.stock_disponible ?? NaN);
            if (cantInput) {
                if (Number.isFinite(stock) && stock > 0) {
                    cantInput.max = stock;
                } else {
                    cantInput.removeAttribute('max');
                }
            }
        }

        function estaDentroDeLimite(input, max, usarEnteros) {
            if (!input || max === null || !Number.isFinite(max)) {
                return true;
            }
            const raw = String(input.value ?? '').trim();
            if (raw === '') {
                return true;
            }
            const n = parseFloat(raw);
            if (!Number.isFinite(n) || n <= 0) {
                return true;
            }
            const tol = usarEnteros ? 0.5 : 0.0005;
            return n <= max + tol;
        }

        function limitarValorInput(input, max, mensaje, usarEnteros) {
            if (!input || max === null || !Number.isFinite(max)) {
                return false;
            }
            const raw = String(input.value ?? '').trim();
            if (raw === '') {
                return false;
            }
            const n = parseFloat(raw);
            const tol = usarEnteros ? 0.5 : 0.0005;
            if (!Number.isFinite(n) || n <= max + tol) {
                return false;
            }

            const valorTope = usarEnteros ? Math.floor(max) : Math.round(max * 1000) / 1000;
            input.value = valorTope > 0 ? String(valorTope) : '';
            mostrarAvisoLimite(mensaje);
            return true;
        }

        function aplicarLimiteKg() {
            const stock = parseFloat(ultimoContexto?.stock_disponible ?? NaN);
            const unidad = ultimoContexto?.stock_unidad || ultimoContexto?.dosis_unidad || 'kg';
            if (!cantInput || !Number.isFinite(stock) || stock <= 0) {
                if (cantInput) {
                    cantInput.removeAttribute('max');
                }
                return false;
            }

            cantInput.max = stock;
            return limitarValorInput(
                cantInput,
                stock,
                'Lo máximo que puedes utilizar en kg es ' + fmtNum(stock, 2) + ' ' + unidad + '.',
                false
            );
        }

        function aplicarLimiteSegunModo() {
            const limites = calcularLimites(ultimoContexto, obtenerCalibreActivo());
            let capEstaVuelta = false;
            const modoCalc = modoActual();

            if (limites.maxHa !== null) {
                if (modoCalc === 'unidades' && limites.maxUnidades !== null) {
                    capEstaVuelta = limitarValorInput(
                        objetivoUnidades,
                        limites.maxUnidades,
                        'Lo máximo que puedes cosechar en unidades es ' + fmtNum(limites.maxUnidades, 0) + '.',
                        true
                    ) || capEstaVuelta;
                } else if (modoCalc === 'empaques' && limites.maxEmpaques !== null) {
                    capEstaVuelta = limitarValorInput(
                        objetivoEmpaques,
                        limites.maxEmpaques,
                        'Lo máximo que puedes cosechar en cajas es ' + fmtNum(limites.maxEmpaques, 0) + '.',
                        true
                    ) || capEstaVuelta;
                } else if (modoCalc === 'hectareas') {
                    capEstaVuelta = limitarValorInput(
                        supInput,
                        limites.maxHa,
                        'Lo máximo que puedes planificar en hectáreas es ' + fmtNum(limites.maxHa, 3) + '.',
                        false
                    ) || capEstaVuelta;
                }
            }

            capEstaVuelta = aplicarLimiteKg() || capEstaVuelta;

            if (!capEstaVuelta && todosLosValoresDentroDeLimite(limites, modoCalc)) {
                mostrarAvisoLimite('');
            }
        }

        function todosLosValoresDentroDeLimite(limites, modoCalc) {
            if (limites.maxHa !== null) {
                if (modoCalc === 'unidades' && limites.maxUnidades !== null
                    && !estaDentroDeLimite(objetivoUnidades, limites.maxUnidades, true)) {
                    return false;
                }
                if (modoCalc === 'empaques' && limites.maxEmpaques !== null
                    && !estaDentroDeLimite(objetivoEmpaques, limites.maxEmpaques, true)) {
                    return false;
                }
                if (modoCalc === 'hectareas' && !estaDentroDeLimite(supInput, limites.maxHa, false)) {
                    return false;
                }
            }

            const stock = parseFloat(ultimoContexto?.stock_disponible ?? NaN);
            if (Number.isFinite(stock) && stock > 0 && !estaDentroDeLimite(cantInput, stock, false)) {
                return false;
            }

            return true;
        }

        function semillaRequeridaEstimada() {
            const desdeInput = parseFloat(cantInput?.value || '');
            if (Number.isFinite(desdeInput) && desdeInput > 0) {
                return desdeInput;
            }
            const porHa = parseFloat(ultimoContexto?.dosis_por_ha ?? NaN);
            const ha = parseFloat(supInput?.value || '0');
            if (Number.isFinite(porHa) && porHa > 0 && ha > 0) {
                return Math.round(porHa * ha * 1000) / 1000;
            }
            return 0;
        }

        function etiquetaEmpaqueCosecha(label) {
            if (!label || /saco/i.test(label)) {
                return 'Cajas';
            }
            return label;
        }

        function etiquetaEmpaqueCorto(c) {
            const label = etiquetaEmpaqueCosecha(c.empaque_label || 'Cajas');
            return label === 'Cajas' ? 'Caja' : label.replace(/s$/, '');
        }

        function actualizarHintCalibre() {
            const hint = document.getElementById('planCalibreHint');
            if (!hint || !calibreSelect?.value) {
                if (hint) hint.classList.add('d-none');
                return;
            }
            const c = calibres.find(function (x) { return String(x.id) === String(calibreSelect.value); });
            if (!c) {
                hint.classList.add('d-none');
                return;
            }
            const empaque = etiquetaEmpaqueCorto(c);
            hint.textContent = '~' + fmtNum(c.conteo_por_empaque, 0) + ' u./caja · ' + fmtNum(c.peso_promedio_kg, 2) + ' kg/u.';
            hint.classList.remove('d-none');
        }

        function sincronizarCalibreHidden() {
            if (calibreHidden && calibreSelect?.value) {
                calibreHidden.value = calibreSelect.value;
            }
        }

        function poblarCalibres(lista, selectedId) {
            calibres = lista || [];
            if (!calibreSelect) return;
            calibreSelect.innerHTML = '';
            if (!calibres.length) {
                calibreSelect.innerHTML = '<option value="">Sin calibres — configure en Catálogos logística</option>';
                return;
            }
            const preferido = selectedId || calibreInicial || null;
            calibres.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = c.id;
                const empaque = etiquetaEmpaqueCorto(c);
                opt.textContent = c.nombre + ' (' + c.conteo_por_empaque + ' u./' + empaque + ', ' + c.peso_promedio_kg + ' kg/u.)';
                opt.title = 'Aprox. ' + c.conteo_por_empaque + ' unidades por caja';
                if (String(c.id) === String(preferido)) opt.selected = true;
                calibreSelect.appendChild(opt);
            });
            sincronizarCalibreHidden();
            actualizarHintCalibre();
            calibreInicial = '';
        }

        let ultimoContexto = null;
        let recalcSeq = 0;

        function obtenerCalibreActivo() {
            if (!calibreSelect?.value) {
                return null;
            }
            return calibres.find(function (c) {
                return String(c.id) === String(calibreSelect.value);
            }) || null;
        }

        function validarStockLocal(hectareas, semilla, ctx) {
            const stock = parseFloat(ctx.stock_disponible ?? NaN);
            const unidadStock = ctx.stock_unidad || ctx.dosis_unidad || 'kg';
            if (semilla !== null && Number.isFinite(stock) && semilla > stock + 0.0005) {
                return 'Stock insuficiente de semilla. Disponible: ' + fmtNum(stock, 2) + ' ' + unidadStock
                    + '; necesita: ' + fmtNum(semilla, 2) + ' ' + (ctx.dosis_unidad || 'kg') + '.';
            }
            const maxHa = parseFloat(ctx.hectareas_max_stock ?? NaN);
            if (Number.isFinite(maxHa) && hectareas > maxHa + 0.0005) {
                return 'La superficie supera lo planificable con el stock disponible (máx. '
                    + fmtNum(maxHa, 2) + ' ha).';
            }
            return null;
        }

        function calcularLocal() {
            const ctx = ultimoContexto;
            const calibre = obtenerCalibreActivo();
            if (!ctx || !calibre) {
                return { ok: false, mensaje: 'Seleccione el calibre al cosechar.' };
            }

            const rendimiento = parseFloat(ctx.rendimiento_kg_ha || 0);
            if (!Number.isFinite(rendimiento) || rendimiento <= 0) {
                return { ok: false, mensaje: 'No hay rendimiento de cosecha (kg/ha) para este cultivo.' };
            }

            const modoCalc = modoActual();
            const dosisPorHa = parseFloat(ctx.dosis_por_ha || 0);
            const pesoUnit = Math.max(0.0001, parseFloat(calibre.peso_promedio_kg));
            const conteoEmpaque = Math.max(1, parseInt(calibre.conteo_por_empaque, 10) || 1);

            let hectareas = 0;
            let unidades = 0;
            let empaques = 0;
            let kgCosecha = 0;

            if (modoCalc === 'unidades') {
                const objetivo = valorNumericoPositivo(objetivoUnidades);
                if (objetivo === null) {
                    return { ok: false, mensaje: 'Indique cuántas unidades desea cosechar.' };
                }
                unidades = Math.round(objetivo);
                kgCosecha = Math.round(unidades * pesoUnit * 100) / 100;
                hectareas = Math.round((kgCosecha / rendimiento) * 1000) / 1000;
                empaques = Math.ceil(unidades / conteoEmpaque);
            } else if (modoCalc === 'empaques') {
                const objetivo = valorNumericoPositivo(objetivoEmpaques);
                if (objetivo === null) {
                    return { ok: false, mensaje: 'Indique cuántas cajas desea obtener.' };
                }
                empaques = Math.ceil(objetivo);
                unidades = empaques * conteoEmpaque;
                kgCosecha = Math.round(unidades * pesoUnit * 100) / 100;
                hectareas = Math.round((kgCosecha / rendimiento) * 1000) / 1000;
            } else {
                const ha = valorNumericoPositivo(supInput);
                if (ha === null) {
                    return { ok: false, mensaje: 'Indique la superficie en hectáreas.' };
                }
                hectareas = Math.round(ha * 1000) / 1000;
                kgCosecha = Math.round(rendimiento * hectareas * 100) / 100;
                unidades = Math.round(kgCosecha / pesoUnit);
                empaques = Math.ceil(unidades / conteoEmpaque);
            }

            const semilla = dosisPorHa > 0
                ? Math.round(dosisPorHa * hectareas * 1000) / 1000
                : null;
            const errorStock = validarStockLocal(hectareas, semilla, ctx);
            if (errorStock) {
                return { ok: false, mensaje: errorStock };
            }

            return {
                ok: true,
                modo: modoCalc,
                hectareas: hectareas,
                semilla_cantidad: semilla,
                semilla_unidad: ctx.dosis_unidad || 'kg',
                kg_cosecha_estimados: kgCosecha,
                unidades_estimadas: unidades,
                empaques_estimados: empaques,
                rendimiento_kg_ha: rendimiento,
                dosis_por_ha: dosisPorHa,
                calibre: calibre,
            };
        }

        function aplicarResultadoPlan(res) {
            mostrarError('');

            if (modo !== 'hectareas' || !haEditadaManual) {
                supInput.value = res.hectareas;
                if (typeof onHectareasChange === 'function') {
                    onHectareasChange(res.hectareas);
                }
            }

            if (!semillaEditadaManual && res.semilla_cantidad !== null && cantInput) {
                cantInput.value = res.semilla_cantidad;
            }
            if (unidadSpan && res.semilla_unidad) {
                unidadSpan.textContent = res.semilla_unidad;
            }

            if (dosisPreview && dosisTexto) {
                const u = res.semilla_unidad || 'kg';
                const cant = res.semilla_cantidad;
                dosisTexto.textContent = fmtNum(res.dosis_por_ha, 2) + ' ' + u + '/ha × '
                    + fmtNum(res.hectareas, 2) + ' ha = '
                    + (cant !== null ? fmtNum(cant, 2) + ' ' + u : '—');
                dosisPreview.classList.remove('d-none');
            }
            actualizarEtiquetaInsumo();

            if (cosechaPreview) {
                renderResultado(res);
                cosechaPreview.classList.remove('d-none');
            }
            actualizarHintCalibre();
        }

        function modoAvanzadoDisponible(ctx) {
            return !!(ctx.tiene_rendimiento && ctx.calibres && ctx.calibres.length);
        }

        function actualizarModosDisponibles(ctx) {
            const avanzado = modoAvanzadoDisponible(ctx);
            modoCards.forEach(function (card) {
                const radio = card.querySelector('input[type="radio"]');
                const val = radio?.value;
                const bloqueado = !avanzado && val !== 'hectareas';
                card.classList.toggle('opacity-50', bloqueado);
                card.style.pointerEvents = bloqueado ? 'none' : '';
                if (bloqueado) {
                    radio.disabled = true;
                } else {
                    radio.disabled = false;
                }
            });
            if (!avanzado) {
                const haRadio = document.querySelector('input[name="plan_modo_ui"][value="hectareas"]');
                if (haRadio) {
                    haRadio.checked = true;
                }
                actualizarModoUi();
            }
        }

        function cargarContexto(id) {
            insumoId = id;
            if (!id) {
                toggleSuperficie(false);
                panel?.classList.add('d-none');
                mostrarError('');
                return;
            }

            toggleSuperficie(true);

            fetch(urlPlanificar + '?solo_contexto=1&insumoid=' + encodeURIComponent(id), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.json(); })
                .then(function (ctx) {
                    if (!ctx.ok) {
                        panel?.classList.add('d-none');
                        mostrarError(ctx.mensaje || 'No se pudo cargar planificación.');
                        return;
                    }

                    const puedePlanificar = ctx.tiene_dosis || modoAvanzadoDisponible(ctx);
                    if (!puedePlanificar) {
                        panel?.classList.add('d-none');
                        mostrarError('Este cultivo no tiene dosis ni rendimiento configurados. Ingrese las hectáreas manualmente.');
                        return;
                    }

                    panel?.classList.remove('d-none');
                    mostrarError('');
                    ultimoContexto = ctx;
                    actualizarAtributosMaximos();

                    if (modoAvanzadoDisponible(ctx)) {
                        poblarCalibres(ctx.calibres, calibreInicial || ctx.calibre_default_id);
                        actualizarModosDisponibles(ctx);
                        if (cantWrap) cantWrap.classList.remove('d-none');
                        recalcular();
                    } else {
                        actualizarModosDisponibles(ctx);
                        if (cantWrap) cantWrap.classList.remove('d-none');
                        mostrarError('Modo básico: ingrese hectáreas para calcular la semilla.');
                        recalcularBasico(ctx);
                    }
                })
                .catch(function () {
                    panel?.classList.add('d-none');
                    mostrarError('No se pudo conectar con el servidor de planificación.');
                });
        }

        function recalcularBasico(ctx) {
            const ha = parseFloat(supInput.value);
            if (!ha || ha <= 0 || !ctx.tiene_dosis) {
                return;
            }
            const semilla = Math.round((ctx.dosis_por_ha || 0) * ha * 1000) / 1000;
            const stock = parseFloat(ctx.stock_disponible ?? NaN);
            const unidadStock = ctx.stock_unidad || ctx.dosis_unidad || 'kg';
            if (Number.isFinite(stock) && semilla > stock + 0.0005) {
                mostrarError('Stock insuficiente de semilla. Disponible: ' + fmtNum(stock, 2) + ' ' + unidadStock
                    + '; necesita: ' + fmtNum(semilla, 2) + ' ' + (ctx.dosis_unidad || 'kg') + '.');
                if (cantInput) {
                    cantInput.value = '';
                }
                return;
            }
            mostrarError('');
            if (!semillaEditadaManual && cantInput && semilla > 0) {
                cantInput.value = semilla;
            }
            if (unidadSpan && ctx.dosis_unidad) {
                unidadSpan.textContent = ctx.dosis_unidad;
            }
            if (dosisPreview && dosisTexto) {
                dosisTexto.textContent = fmtNum(ctx.dosis_por_ha, 2) + ' ' + (ctx.dosis_unidad || 'kg') + '/ha × '
                    + fmtNum(ha, 2) + ' ha = ' + fmtNum(semilla, 2) + ' ' + (ctx.dosis_unidad || 'kg');
                dosisPreview.classList.remove('d-none');
            }
            actualizarEtiquetaInsumo();
            cosechaPreview?.classList.add('d-none');
        }

        function valorNumericoPositivo(input) {
            if (!input) {
                return null;
            }
            const raw = String(input.value ?? '').trim();
            if (raw === '') {
                return null;
            }
            const n = parseFloat(raw);
            return Number.isFinite(n) && n > 0 ? n : null;
        }

        function validarPlanificacionParaEnvio() {
            const panelVisible = panel && !panel.classList.contains('d-none');
            if (!panelVisible || !insumoId) {
                return { ok: true };
            }

            if (planificacionErrorActiva && ultimoMensajeError) {
                return { ok: false, mensaje: ultimoMensajeError };
            }

            const modoEnvio = modoActual();

            if (modoEnvio === 'empaques') {
                const empaques = valorNumericoPositivo(objetivoEmpaques);
                if (empaques === null) {
                    return { ok: false, mensaje: 'Indique cuántas cajas desea obtener (debe ser mayor a 0).' };
                }
            } else if (modoEnvio === 'unidades') {
                const unidades = valorNumericoPositivo(objetivoUnidades);
                if (unidades === null) {
                    return { ok: false, mensaje: 'Indique cuántas unidades desea cosechar (debe ser mayor a 0).' };
                }
            } else {
                const ha = valorNumericoPositivo(supInput);
                if (ha === null) {
                    return { ok: false, mensaje: 'Indique la superficie en hectáreas (debe ser mayor a 0).' };
                }
            }

            const haFinal = parseFloat(supInput?.value || '0');
            if (!haFinal || haFinal <= 0) {
                return { ok: false, mensaje: 'Complete la planificación: la superficie calculada debe ser mayor a 0.' };
            }

            if (modoAvanzadoDisponible(ultimoContexto || {}) && !calibreSelect?.value) {
                return { ok: false, mensaje: 'Seleccione el calibre al cosechar.' };
            }

            const stock = parseFloat(ultimoContexto?.stock_disponible ?? NaN);
            const unidadStock = ultimoContexto?.stock_unidad || ultimoContexto?.dosis_unidad || 'kg';
            const semillaReq = semillaRequeridaEstimada();
            if (Number.isFinite(stock) && semillaReq > stock + 0.0005) {
                return {
                    ok: false,
                    mensaje: 'Stock insuficiente de semilla. Disponible: ' + fmtNum(stock, 2) + ' ' + unidadStock
                        + '; necesita: ' + fmtNum(semillaReq, 2) + ' ' + unidadStock + '.',
                };
            }

            const maxHa = parseFloat(ultimoContexto?.hectareas_max_stock ?? NaN);
            if (Number.isFinite(maxHa) && haFinal > maxHa + 0.0005) {
                return {
                    ok: false,
                    mensaje: 'La superficie supera lo planificable con el stock disponible (máx. '
                        + fmtNum(maxHa, 2) + ' ha).',
                };
            }

            return { ok: true };
        }

        window.AgroFusionPlanificacionCosecha.validarEnvio = validarPlanificacionParaEnvio;
        window.AgroFusionPlanificacionCosecha.sincronizarAntesEnvio = function () {
            actualizarModoUi();
        };

        function recalcular() {
            clearTimeout(timer);
            actualizarModoUi();

            if (!insumoId) {
                return;
            }

            aplicarLimiteSegunModo();
            actualizarAtributosMaximos();

            if ((!calibreSelect?.value || !modoAvanzadoDisponible(ultimoContexto || {})) && ultimoContexto?.tiene_dosis) {
                recalcularBasico(ultimoContexto);
                return;
            }

            if (!calibreSelect?.value || !modoAvanzadoDisponible(ultimoContexto || {})) {
                return;
            }

            const seq = ++recalcSeq;
            const res = calcularLocal();

            if (seq !== recalcSeq) {
                return;
            }

            if (!res.ok) {
                mostrarError(res.mensaje || 'No se pudo calcular.');
                cosechaPreview?.classList.add('d-none');
                if (modo !== 'hectareas') {
                    if (cantInput) {
                        cantInput.value = '';
                    }
                }
                return;
            }

            aplicarResultadoPlan(res);
        }

        modoRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                actualizarModoUi();
                haEditadaManual = false;
                mostrarError('');
                mostrarAvisoLimite('');
                recalcular();
            });
        });

        modoCards.forEach(function (card) {
            card.addEventListener('click', function () {
                const radio = card.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });

        [calibreSelect].forEach(function (el) {
            el?.addEventListener('input', function () {
                sincronizarCalibreHidden();
                actualizarModoUi();
                recalcular();
            });
            el?.addEventListener('change', function () {
                sincronizarCalibreHidden();
                actualizarModoUi();
                recalcular();
            });
        });

        enlazarSoloNumeros(objetivoUnidades, true, function () {
            sincronizarCalibreHidden();
            actualizarModoUi();
            recalcular();
        });
        enlazarSoloNumeros(objetivoEmpaques, true, function () {
            sincronizarCalibreHidden();
            actualizarModoUi();
            recalcular();
        });
        enlazarSoloNumeros(supInput, false, function () {
            if (modo === 'hectareas') {
                haEditadaManual = true;
                recalcular();
            }
        });
        enlazarSoloNumeros(cantInput, false, function () {
            semillaEditadaManual = true;
            const capKg = aplicarLimiteKg();
            if (!capKg && todosLosValoresDentroDeLimite(
                calcularLimites(ultimoContexto, obtenerCalibreActivo()),
                modoActual()
            )) {
                mostrarAvisoLimite('');
            }
        });

        semillaWrap.addEventListener('selector-catalogo:change', function (e) {
            semillaEditadaManual = false;
            haEditadaManual = false;
            mostrarAvisoLimite('');
            const id = e.detail?.id || '';
            if (!id) {
                cargarContexto('');
                if (insumoUsoNombre) {
                    insumoUsoNombre.textContent = '';
                }
                return;
            }
            actualizarEtiquetaInsumo();
            cargarContexto(id);
        });

        actualizarModoUi();

        const inicial = semillaWrap.querySelector('.selector-catalogo-value')?.value || '';
        if (inicial) {
            cargarContexto(inicial);
        }
    };
</script>
