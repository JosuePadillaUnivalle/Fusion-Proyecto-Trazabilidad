<script>
    window.AgroFusionLoteMapa = window.AgroFusionLoteMapa || {};

    AgroFusionLoteMapa.calcularRadioMetros = function (hectareas) {
        const ha = parseFloat(hectareas);
        if (!ha || ha <= 0) {
            return 0;
        }
        return Math.sqrt(ha * 10000 / Math.PI);
    };

    AgroFusionLoteMapa.actualizarCirculo = function (map, circleRef, lat, lng, hectareas, opciones) {
        const opts = Object.assign({
            color: '#28a745',
            fillColor: '#28a745',
            fillOpacity: 0.25,
            ajustarVista: true,
        }, opciones || {});

        if (circleRef.current) {
            map.removeLayer(circleRef.current);
            circleRef.current = null;
        }

        const radio = this.calcularRadioMetros(hectareas);
        if (!lat || !lng || radio <= 0) {
            return null;
        }

        circleRef.current = L.circle([lat, lng], {
            color: opts.color,
            fillColor: opts.fillColor,
            fillOpacity: opts.fillOpacity,
            radius: radio,
        }).addTo(map);

        if (opts.ajustarVista) {
            map.fitBounds(circleRef.current.getBounds(), { padding: [24, 24], maxZoom: 15 });
        }

        return circleRef.current;
    };

    AgroFusionLoteMapa.vincularDosisSiembra = function (config) {
        const supInput = document.getElementById(config.superficieInputId || 'superficie');
        const preview = document.getElementById(config.previewId || 'dosisSiembraPreview');
        const texto = document.getElementById(config.textoId || 'dosisSiembraTexto');
        const cantWrap = document.getElementById(config.cantidadWrapId || 'cantidadSemillaWrap');
        const cantInput = document.getElementById(config.cantidadInputId || 'cantidad_semilla_planificada');
        const unidadSpan = document.getElementById(config.unidadSpanId || 'cantidadSemillaUnidad');
        const wrap = document.getElementById('selector_wrap_' + (config.selectorId || 'lote_semilla'));
        const stockEl = document.getElementById('semillaStockDisponible_' + (config.selectorId || 'lote_semilla'));

        if (!supInput || !cantInput) {
            return;
        }

        let dosisPorHa = 0;
        let dosisUnidad = 'kg';
        let editadoManualmente = false;
        let ultimoSemillaId = wrap?.querySelector('.selector-catalogo-value')?.value || '';

        if (config.initialCantidad !== undefined && config.initialCantidad !== null && config.initialCantidad !== '') {
            editadoManualmente = true;
        }

        function renderStockDisponible(extra) {
            if (!stockEl) {
                return;
            }

            if (!extra || extra.stock === undefined || extra.stock === null) {
                stockEl.classList.add('d-none');
                stockEl.innerHTML = '';
                return;
            }

            const stock = parseFloat(extra.stock);
            const unidad = extra.unidad || 'ud';
            const sinStock = extra.sin_stock === true || !Number.isFinite(stock) || stock <= 0;

            stockEl.classList.remove('d-none');
            stockEl.classList.toggle('text-danger', sinStock);
            stockEl.classList.toggle('text-muted', !sinStock);
            stockEl.innerHTML = '<i class="fas fa-boxes mr-1"></i> Disponible en inventario: <strong>'
                + (Number.isFinite(stock) ? stock.toLocaleString('es-BO', { maximumFractionDigits: 2 }) : '—')
                + ' ' + unidad + '</strong>'
                + (sinStock ? ' <span class="text-danger">(sin stock)</span>' : '');
        }

        function calcularTotal() {
            const ha = parseFloat(supInput.value);
            if (!dosisPorHa || !ha || ha <= 0) {
                return null;
            }
            return Math.round(dosisPorHa * ha * 1000) / 1000;
        }

        function unidadTexto(unidad) {
            if (unidad === 'kg') {
                return 'kg de semilla';
            }
            return unidad;
        }

        function renderDosis(forzarAuto) {
            const semillaId = wrap?.querySelector('.selector-catalogo-value')?.value || '';
            const total = calcularTotal();

            if (semillaId !== ultimoSemillaId) {
                editadoManualmente = false;
                ultimoSemillaId = semillaId;
            }

            if (!semillaId || !dosisPorHa) {
                if (cantWrap) {
                    cantWrap.classList.add('d-none');
                }
                if (!editadoManualmente && !forzarAuto) {
                    cantInput.value = '';
                }
                if (preview) {
                    preview.classList.add('d-none');
                }
                return;
            }

            if (cantWrap) {
                cantWrap.classList.remove('d-none');
            }
            if (unidadSpan) {
                unidadSpan.textContent = dosisUnidad;
            }

            if ((!editadoManualmente || forzarAuto) && total !== null) {
                cantInput.value = total;
            }

            if (preview && texto) {
                const ha = parseFloat(supInput.value);
                if (total !== null && ha > 0) {
                    texto.textContent = 'Referencia: '
                        + dosisPorHa.toLocaleString('es-BO', { maximumFractionDigits: 3 })
                        + ' ' + dosisUnidad + '/ha × '
                        + ha.toLocaleString('es-BO', { maximumFractionDigits: 2 })
                        + ' ha = '
                        + total.toLocaleString('es-BO', { maximumFractionDigits: 3 })
                        + ' ' + unidadTexto(dosisUnidad) + '.';
                    preview.classList.remove('d-none');
                } else {
                    preview.classList.add('d-none');
                }
            }
        }

        function aplicarExtra(extra) {
            dosisPorHa = parseFloat(extra?.dosis_por_ha || 0) || 0;
            dosisUnidad = extra?.dosis_unidad_legible || extra?.dosis_unidad || extra?.unidad || 'kg';
            if (extra && extra.stock !== undefined && extra.stock !== null) {
                renderStockDisponible(extra);
            }
            renderDosis(true);
        }

        cantInput.addEventListener('input', function () {
            editadoManualmente = true;
            if (preview && texto && cantInput.value) {
                texto.textContent = 'Cantidad ajustada manualmente: '
                    + parseFloat(cantInput.value).toLocaleString('es-BO', { maximumFractionDigits: 3 })
                    + ' ' + unidadTexto(dosisUnidad) + '.';
                preview.classList.remove('d-none');
            }
        });

        supInput.addEventListener('input', function () {
            renderDosis(false);
        });
        supInput.addEventListener('change', function () {
            renderDosis(false);
        });

        if (wrap) {
            wrap.addEventListener('selector-catalogo:change', function (e) {
                if (!e.detail?.id) {
                    dosisPorHa = 0;
                    editadoManualmente = false;
                    cantInput.value = '';
                    renderStockDisponible(null);
                    renderDosis(true);
                    return;
                }
                aplicarExtra(e.detail?.extra || {});
            });
        }

        if (config.initialStock) {
            renderStockDisponible(config.initialStock);
        }

        if (config.initialDosis) {
            aplicarExtra(Object.assign({}, config.initialDosis, config.initialStock || {}));
        } else {
            renderDosis(false);
        }
    };
</script>
