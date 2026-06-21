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
        const cosechaTexto = document.getElementById('planCosechaTexto');
        const errorPreview = document.getElementById('planErrorPreview');
        const errorTexto = document.getElementById('planErrorTexto');
        const modoRadios = document.querySelectorAll('input[name="plan_modo_ui"]');
        const modoCards = document.querySelectorAll('.plan-modo-card');
        const hectareasHint = document.getElementById('planHectareasHint');
        const resultadoGrid = document.getElementById('planResultadoGrid');
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
            hectareasHint?.classList.toggle('d-none', modo !== 'hectareas');
            objetivoUnidadesWrap?.classList.toggle('d-none', modo !== 'unidades');
            objetivoEmpaquesWrap?.classList.toggle('d-none', modo !== 'empaques');
            supInput.readOnly = modo !== 'hectareas';
            supInput.classList.toggle('bg-light', modo !== 'hectareas');
            if (superficieWrap) {
                superficieWrap.classList.remove('d-none');
                superficieWrap.classList.toggle('plan-superficie-calculada', modo !== 'hectareas');
            }
        }

        function renderResultado(res) {
            if (!resultadoGrid || !cosechaTexto) return;
            const emp = res.calibre?.empaque_label || 'Cajas';
            const porCaja = res.calibre?.conteo_por_empaque;
            resultadoGrid.innerHTML =
                '<div class="plan-resultado-item"><strong>' + fmtNum(res.unidades_estimadas, 0) + '</strong><span>Unidades</span></div>'
                + '<div class="plan-resultado-item"><strong>' + fmtNum(res.empaques_estimados, 0) + '</strong><span>' + emp + '</span></div>'
                + '<div class="plan-resultado-item"><strong>' + fmtNum(res.kg_cosecha_estimados, 0) + ' kg</strong><span>Cosecha</span></div>';
            let txt = res.resumen || '';
            if (porCaja) {
                txt += ' Aprox. ' + fmtNum(porCaja, 0) + ' unidades por caja.';
            }
            cosechaTexto.textContent = txt;
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
            hint.textContent = 'Cada caja llevará aprox. ' + fmtNum(c.conteo_por_empaque, 0) + ' unidades (' + empaque + ').';
            hint.classList.remove('d-none');
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
                    if (!ctx.tiene_rendimiento || !ctx.calibres || !ctx.calibres.length) {
                        panel?.classList.add('d-none');
                        mostrarError('');
                        return;
                    }
                    panel?.classList.remove('d-none');
                    mostrarError('');
                    poblarCalibres(ctx.calibres, calibreInicial || ctx.calibre_default_id);
                    if (cantWrap) cantWrap.classList.remove('d-none');
                    recalcular();
                })
                .catch(function () {
                    panel?.classList.add('d-none');
                });
        }

        function recalcular() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (!insumoId || !calibreSelect?.value) {
                    return;
                }
                const params = new URLSearchParams({
                    insumoid: insumoId,
                    calibre_id: calibreSelect.value,
                    modo: modo,
                });
                if (modo === 'unidades') {
                    params.set('objetivo_unidades', objetivoUnidades?.value || '0');
                } else if (modo === 'empaques') {
                    params.set('objetivo_empaques', objetivoEmpaques?.value || '0');
                } else {
                    params.set('hectareas', supInput.value || '0');
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
                            const semTxt = res.semilla_cantidad !== null
                                ? fmtNum(res.semilla_cantidad, 2) + ' ' + (res.semilla_unidad || 'kg') + ' de semilla'
                                : 'semilla según dosis';
                            dosisTexto.textContent = 'Referencia: '
                                + fmtNum(res.dosis_por_ha, 2) + ' ' + (res.semilla_unidad || 'kg') + '/ha × '
                                + fmtNum(res.hectareas, 2) + ' ha = ' + semTxt + '.';
                            dosisPreview.classList.remove('d-none');
                        }

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
                return;
            }
            cargarContexto(id);
        });

        actualizarModoUi();

        const inicial = semillaWrap.querySelector('.selector-catalogo-value')?.value || '';
        if (inicial) {
            cargarContexto(inicial);
        }
    };
</script>
