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
        const modoRadios = document.querySelectorAll('input[name="plan_modo_ui"]');
        const modoCards = document.querySelectorAll('.plan-modo-card');
        const resultadoGrid = document.getElementById('planResultadoGrid');
        const insumoUsoNombre = document.getElementById('planInsumoUsoNombre');
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

        function modoActual() {
            const checked = document.querySelector('input[name="plan_modo_ui"]:checked');
            return checked ? checked.value : 'hectareas';
        }

        function actualizarModoUi() {
            modo = modoActual();
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
            const emp = res.calibre?.empaque_label || 'Cajas';
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
                return;
            }
            errorTexto.textContent = msg;
            errorPreview.classList.remove('d-none');
            panel?.classList.remove('d-none');
            cosechaPreview?.classList.add('d-none');
        }

        function etiquetaEmpaqueCorto(c) {
            const label = c.empaque_label || 'Cajas';
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

            if (modo === 'empaques') {
                const empaques = valorNumericoPositivo(objetivoEmpaques);
                if (empaques === null) {
                    return { ok: false, mensaje: 'Indique cuántas cajas desea obtener (debe ser mayor a 0).' };
                }
            } else if (modo === 'unidades') {
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

            return { ok: true };
        }

        window.AgroFusionPlanificacionCosecha.validarEnvio = validarPlanificacionParaEnvio;

        function recalcular() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (!insumoId) {
                    return;
                }
                if ((!calibreSelect?.value || !modoAvanzadoDisponible(ultimoContexto || {})) && ultimoContexto?.tiene_dosis) {
                    recalcularBasico(ultimoContexto);
                    return;
                }
                if (!calibreSelect?.value) {
                    return;
                }
                const params = new URLSearchParams({
                    insumoid: insumoId,
                    calibre_id: calibreSelect.value,
                    modo: modo,
                });
                if (modo === 'unidades') {
                    const unidades = valorNumericoPositivo(objetivoUnidades);
                    if (unidades === null) {
                        mostrarError('Indique cuántas unidades desea cosechar.');
                        cosechaPreview?.classList.add('d-none');
                        if (modo !== 'hectareas') {
                            supInput.value = '';
                        }
                        return;
                    }
                    params.set('objetivo_unidades', String(unidades));
                } else if (modo === 'empaques') {
                    const empaques = valorNumericoPositivo(objetivoEmpaques);
                    if (empaques === null) {
                        mostrarError('Indique cuántos empaques (cajas/sacas) desea obtener.');
                        cosechaPreview?.classList.add('d-none');
                        supInput.value = '';
                        return;
                    }
                    params.set('objetivo_empaques', String(Math.floor(empaques)));
                } else {
                    const ha = valorNumericoPositivo(supInput);
                    if (ha === null) {
                        mostrarError('Indique la superficie en hectáreas.');
                        cosechaPreview?.classList.add('d-none');
                        return;
                    }
                    params.set('hectareas', String(ha));
                }

                fetch(urlPlanificar + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res.ok) {
                            mostrarError(res.mensaje || 'No se pudo calcular.');
                            return;
                        }
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
                    })
                    .catch(function () {
                        mostrarError('Error de conexión al calcular.');
                    });
            }, 250);
        }

        modoRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                actualizarModoUi();
                haEditadaManual = false;
                mostrarError('');
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

        [objetivoUnidades, objetivoEmpaques, calibreSelect].forEach(function (el) {
            el?.addEventListener('input', function () {
                sincronizarCalibreHidden();
                recalcular();
            });
            el?.addEventListener('change', function () {
                sincronizarCalibreHidden();
                recalcular();
            });
        });

        supInput.addEventListener('input', function () {
            if (modo === 'hectareas') {
                haEditadaManual = true;
                recalcular();
            }
        });

        cantInput?.addEventListener('input', function () {
            semillaEditadaManual = true;
        });

        semillaWrap.addEventListener('selector-catalogo:change', function (e) {
            semillaEditadaManual = false;
            haEditadaManual = false;
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
