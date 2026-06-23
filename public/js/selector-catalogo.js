(function (window, document) {
    'use strict';

    const DEBOUNCE_MS = 320;

    const CatalogoSelector = {
        instances: {},
        modalEl: null,
        activeId: null,
        _hidingForPreview: false,
        debounceTimer: null,
        almacenDebounceTimer: null,
        page: 1,

        ensureModalOnBody() {
            if (!this.modalEl || this.modalEl.parentElement === document.body) {
                return;
            }
            document.body.appendChild(this.modalEl);
        },

        modalesVisibles() {
            return document.querySelectorAll('.modal.show').length;
        },

        cleanupModalArtifacts() {
            if (this.modalesVisibles() > 0) {
                return;
            }
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
        },

        syncModalStacking() {
            if (!this.modalEl) {
                return;
            }
            const visibleCount = this.modalesVisibles();
            const zIndex = 1050 + (10 * Math.max(visibleCount, 1));
            this.modalEl.style.zIndex = String(zIndex);
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length) {
                backdrops[backdrops.length - 1].style.zIndex = String(zIndex - 1);
            }
        },

        restaurarModalPadre() {
            if (this.modalesVisibles() > 0) {
                document.body.classList.add('modal-open');
            }
        },

        init() {
            this.modalEl = document.getElementById('modalSelectorCatalogo');
            if (!this.modalEl) {
                return;
            }

            this.ensureModalOnBody();
            this.cleanupModalArtifacts();

            const searchInput = this.modalEl.querySelector('#selectorCatalogoBuscar');
            const filterSelect = this.modalEl.querySelector('#selectorCatalogoFiltro');
            const almacenSearch = this.modalEl.querySelector('#selectorCatalogoAlmacenBuscar');
            const almacenClear = this.modalEl.querySelector('#selectorCatalogoAlmacenLimpiar');
            const almacenLista = this.modalEl.querySelector('#selectorCatalogoAlmacenLista');
            const btnPrev = this.modalEl.querySelector('#selectorCatalogoPrev');
            const btnNext = this.modalEl.querySelector('#selectorCatalogoNext');

            searchInput.addEventListener('input', () => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.page = 1;
                    this.fetch();
                }, DEBOUNCE_MS);
            });

            filterSelect.addEventListener('change', () => {
                this.page = 1;
                this.fetch();
            });

            almacenSearch.addEventListener('input', () => {
                clearTimeout(this.almacenDebounceTimer);
                this.almacenDebounceTimer = setTimeout(() => this.loadAlmacenesLista(), DEBOUNCE_MS);
            });

            almacenClear.addEventListener('click', () => this.clearAlmacenFilter(true));

            almacenLista.addEventListener('click', (e) => {
                const card = e.target.closest('[data-almacen-id]');
                if (!card) {
                    return;
                }
                this.selectAlmacenFilter(
                    card.getAttribute('data-almacen-id') || '',
                    card.getAttribute('data-almacen-label') || ''
                );
            });

            btnPrev.addEventListener('click', () => {
                if (this.page > 1) {
                    this.page--;
                    this.fetch();
                }
            });

            btnNext.addEventListener('click', () => {
                this.page++;
                this.fetch();
            });

            this.modalEl.querySelector('#selectorCatalogoLista').addEventListener('click', (e) => {
                const verVehBtn = e.target.closest('.sel-row-ver-vehiculo-btn');
                if (verVehBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = verVehBtn.closest('[data-item-id]');
                    if (!row) {
                        return;
                    }
                    this.openVehiculoPreview({
                        id: row.getAttribute('data-item-id'),
                        label: row.getAttribute('data-item-label'),
                    });
                    return;
                }

                const mapBtn = e.target.closest('.sel-row-map-btn');
                if (mapBtn && this.activeId) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = mapBtn.closest('[data-item-id]');
                    if (!row) {
                        return;
                    }
                    let extra = {};
                    try {
                        extra = JSON.parse(row.getAttribute('data-item-extra') || '{}');
                    } catch (err) {
                        extra = {};
                    }
                    const cfg = this.instances[this.activeId] || {};
                    const payload = {
                        id: row.getAttribute('data-item-id'),
                        label: row.getAttribute('data-item-label'),
                        extra,
                    };
                    if (typeof cfg.onMapClick === 'function') {
                        cfg.onMapClick(payload);
                    } else if (extra.lat != null && extra.lng != null) {
                        const lat = parseFloat(extra.lat);
                        const lng = parseFloat(extra.lng);
                        window.open(
                            'https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lng + '#map=16/' + lat + '/' + lng,
                            '_blank',
                            'noopener'
                        );
                    }
                    return;
                }

                const actionBtn = e.target.closest('.sel-row-action-btn');
                if (actionBtn && this.activeId) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = actionBtn.closest('[data-item-id]');
                    if (!row) {
                        return;
                    }
                    let extra = {};
                    try {
                        extra = JSON.parse(row.getAttribute('data-item-extra') || '{}');
                    } catch (err) {
                        extra = {};
                    }
                    const cfg = this.instances[this.activeId] || {};
                    if (typeof cfg.onRowAction === 'function') {
                        cfg.onRowAction({
                            id: row.getAttribute('data-item-id'),
                            label: row.getAttribute('data-item-label'),
                            extra,
                        });
                    }
                    return;
                }

                const row = e.target.closest('tr.selector-catalogo-row[data-item-id]');
                if (!row || !this.activeId) {
                    return;
                }
                let extra = {};
                try {
                    extra = JSON.parse(row.getAttribute('data-item-extra') || '{}');
                } catch (err) {
                    extra = {};
                }
                this.select({
                    id: row.getAttribute('data-item-id'),
                    label: row.getAttribute('data-item-label'),
                    extra,
                });
            });

            document.addEventListener('click', (e) => {
                const openBtn = e.target.closest('[data-selector-open]');
                if (openBtn) {
                    e.preventDefault();
                    this.open(openBtn.getAttribute('data-selector-open'));
                }
                const clearBtn = e.target.closest('[data-selector-clear]');
                if (clearBtn) {
                    e.preventDefault();
                    this.clear(clearBtn.getAttribute('data-selector-clear'));
                }
            });

            window.jQuery(this.modalEl).on('hidden.bs.modal', () => {
                if (this._hidingForPreview) {
                    this._hidingForPreview = false;
                    return;
                }
                this.activeId = null;
                window.setTimeout(() => {
                    this.restaurarModalPadre();
                    this.cleanupModalArtifacts();
                }, 80);
            });

            window.jQuery(this.modalEl).on('show.bs.modal', () => {
                this.ensureModalOnBody();
                window.requestAnimationFrame(() => this.syncModalStacking());
            });

            window.jQuery(this.modalEl).on('shown.bs.modal', () => {
                this.syncModalStacking();
            });
        },

        register(id, config) {
            if (!config.theme && config.endpoint && String(config.endpoint).includes('vehiculos')) {
                config.theme = 'vehiculo';
            }
            if (config.theme === 'vehiculo') {
                config.colNombre = config.colNombre || 'Placa';
                config.colDetalle = config.colDetalle || 'Vehículo';
            }
            this.instances[id] = config;
        },

        resolveCargaContext(cfg) {
            if (typeof cfg.getCargaContext === 'function') {
                const ctx = cfg.getCargaContext();
                if (ctx && (parseFloat(ctx.peso_kg) > 0 || parseFloat(ctx.volumen_m3) > 0)) {
                    return ctx;
                }
            }
            if (window.PedidoFase2 && typeof window.PedidoFase2.pesoTotalKg === 'function') {
                const peso = window.PedidoFase2.pesoTotalKg();
                if (peso > 0) {
                    return {
                        peso_kg: peso,
                        volumen_m3: typeof window.PedidoFase2.volumenTotalM3 === 'function'
                            ? window.PedidoFase2.volumenTotalM3()
                            : null,
                    };
                }
            }
            if (window.PdvPedidoCargaContext) {
                return window.PdvPedidoCargaContext;
            }
            if (window.AsignacionPedidoCargaContext) {
                return window.AsignacionPedidoCargaContext;
            }
            if (typeof window.calcularCargaRutaDistribucion === 'function') {
                return window.calcularCargaRutaDistribucion();
            }
            return { peso_kg: 0, volumen_m3: null };
        },

        openVehiculoPreview(item) {
            if (!window.VehiculoCargaPreview || !item?.id) {
                return;
            }
            const cfg = this.instances[this.activeId] || {};
            const carga = this.resolveCargaContext(cfg);
            const catalogModal = this.modalEl;
            const $catalog = catalogModal && window.jQuery ? window.jQuery(catalogModal) : null;
            const catalogWasOpen = $catalog && $catalog.hasClass('show');

            const launch = function () {
                window.VehiculoCargaPreview.open(item.id, item.label, carga, {
                    onClose: function () {
                        if (catalogWasOpen && $catalog) {
                            $catalog.modal('show');
                        }
                    },
                });
            };

            if (catalogWasOpen && $catalog) {
                this._hidingForPreview = true;
                $catalog.one('hidden.bs.modal.catalogPreview', launch);
                $catalog.modal('hide');
            } else {
                launch();
            }
        },

        selectedItemId(id) {
            const cfg = this.instances[id];
            if (cfg && cfg.selectedItemId != null && cfg.selectedItemId !== '') {
                return String(cfg.selectedItemId);
            }
            const wrapper = document.getElementById('selector_wrap_' + id);
            const hidden = wrapper?.querySelector('.selector-catalogo-value');
            return hidden?.value != null && hidden.value !== '' ? String(hidden.value) : '';
        },

        open(id) {
            const cfg = this.instances[id];
            if (!cfg || !this.modalEl) {
                return;
            }

            this.activeId = id;
            this.page = 1;

            if (typeof cfg.beforeOpen === 'function') {
                cfg.beforeOpen(cfg);
            }

            cfg.selectedItemId = this.selectedItemId(id);

            this.modalEl.querySelector('#selectorCatalogoTitulo').textContent = cfg.title || 'Buscar y seleccionar';
            this.modalEl.querySelector('#selectorCatalogoBuscar').value = '';
            this.modalEl.querySelector('#selectorCatalogoBuscar').placeholder = cfg.searchPlaceholder || 'Escriba para buscar…';

            this.modalEl.classList.remove('sel-theme-planta', 'sel-theme-vehiculo', 'sel-theme-origen', 'sel-theme-chofer');
            if (cfg.theme) {
                this.modalEl.classList.add('sel-theme-' + cfg.theme);
            }

            const colNombre = this.modalEl.querySelector('#selectorCatalogoColNombre');
            const colDetalle = this.modalEl.querySelector('#selectorCatalogoColDetalle');
            if (colNombre) {
                colNombre.textContent = cfg.colNombre || 'Nombre';
            }
            if (colDetalle) {
                colDetalle.textContent = cfg.colDetalle || 'Detalle';
            }

            const buscarLabel = this.modalEl.querySelector('#selectorCatalogoBuscarLabel');
            if (buscarLabel) {
                buscarLabel.textContent = cfg.searchLabel || 'Buscar';
            }

            const filterWrap = this.modalEl.querySelector('#selectorCatalogoFiltroWrap');
            const filterSelect = this.modalEl.querySelector('#selectorCatalogoFiltro');
            if (cfg.filter && cfg.filter.options) {
                filterWrap.style.display = '';
                filterSelect.innerHTML = cfg.filter.options.map((o) =>
                    `<option value="${o.value ?? ''}">${o.label}</option>`
                ).join('');
                filterSelect.name = cfg.filter.param || 'filtro';
                const filterParam = cfg.filter.param || 'filtro';
                const preVal = cfg.params && cfg.params[filterParam] != null && cfg.params[filterParam] !== ''
                    ? String(cfg.params[filterParam])
                    : '';
                if (preVal && filterSelect.querySelector(`option[value="${preVal}"]`)) {
                    filterSelect.value = preVal;
                } else {
                    filterSelect.selectedIndex = 0;
                }
            } else {
                filterWrap.style.display = 'none';
                filterSelect.innerHTML = '';
            }

            const almacenWrap = this.modalEl.querySelector('#selectorCatalogoFiltroAlmacenWrap');
            const almacenSearch = this.modalEl.querySelector('#selectorCatalogoAlmacenBuscar');
            if (cfg.filterAlmacen && cfg.filterAlmacen.endpoint) {
                almacenWrap.style.display = '';
                almacenSearch.placeholder = cfg.filterAlmacen.placeholder || 'Buscar almacén por nombre…';
                this.clearAlmacenFilter(false);
                this.loadAlmacenesLista();
            } else {
                almacenWrap.style.display = 'none';
                this.clearAlmacenFilter(false);
            }

            window.jQuery(this.modalEl).modal('show');
            this.fetch();
        },

        loadAlmacenesLista() {
            const cfg = this.instances[this.activeId];
            if (!cfg || !cfg.filterAlmacen || !cfg.filterAlmacen.endpoint) {
                return;
            }

            const almacenSearch = this.modalEl.querySelector('#selectorCatalogoAlmacenBuscar');
            const lista = this.modalEl.querySelector('#selectorCatalogoAlmacenLista');
            const q = almacenSearch.value.trim();
            const selectedId = this.modalEl.querySelector('#selectorCatalogoAlmacenId')?.value || '';

            lista.innerHTML = '<div class="selector-almacen-loading text-muted small py-2 w-100"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando almacenes…</div>';

            const params = new URLSearchParams({ per_page: '50', page: '1' });
            if (q) {
                params.set('q', q);
            }
            if (cfg.filterAlmacen.params) {
                Object.entries(cfg.filterAlmacen.params).forEach(([k, v]) => {
                    if (v !== null && v !== undefined && v !== '') {
                        params.set(k, String(v));
                    }
                });
            }

            fetch(cfg.filterAlmacen.endpoint + '?' + params.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((r) => {
                    if (!r.ok) {
                        throw new Error('Error al cargar almacenes');
                    }
                    return r.json();
                })
                .then((json) => {
                    this.renderAlmacenesLista(json.data || [], selectedId, q);
                })
                .catch(() => {
                    lista.innerHTML = '<div class="text-danger small py-2 w-100">No se pudieron cargar los almacenes.</div>';
                });
        },

        renderAlmacenesLista(items, selectedId, q) {
            const lista = this.modalEl.querySelector('#selectorCatalogoAlmacenLista');
            const todosActive = !selectedId;
            let html = `
                <button type="button" class="selector-almacen-card selector-almacen-card--todos ${todosActive ? 'active' : ''}"
                        data-almacen-id="" data-almacen-label="">
                    <span class="alm-nombre"><i class="fas fa-th-large"></i>Todos los almacenes</span>
                    <span class="alm-meta">Ver productos de cualquier ubicación</span>
                </button>
            `;

            if (!items.length) {
                html += `<div class="selector-almacen-vacio text-muted small py-1 w-100">${q ? 'Sin coincidencias. Pruebe otro nombre.' : 'No hay almacenes con stock.'}</div>`;
            } else {
                html += items.map((item) => {
                    const active = String(item.id) === String(selectedId);
                    return `
                        <button type="button" class="selector-almacen-card ${active ? 'active' : ''}"
                                data-almacen-id="${item.id}"
                                data-almacen-label="${this.escape(item.label)}">
                            <span class="alm-nombre"><i class="fas fa-warehouse mr-1"></i>${this.escape(item.label)}</span>
                            ${item.meta ? `<span class="alm-meta">${this.escape(item.meta)}</span>` : ''}
                        </button>
                    `;
                }).join('');
            }

            lista.innerHTML = html;
        },

        selectAlmacenFilter(id, label) {
            const hidden = this.modalEl.querySelector('#selectorCatalogoAlmacenId');
            const almacenSearch = this.modalEl.querySelector('#selectorCatalogoAlmacenBuscar');

            hidden.value = id || '';
            if (id) {
                almacenSearch.value = '';
            }
            this.modalEl.querySelectorAll('.selector-almacen-card').forEach((card) => {
                const cardId = card.getAttribute('data-almacen-id') || '';
                card.classList.toggle('active', cardId === (id || ''));
            });
            this.updateAlmacenActivo(id ? label : '');
            this.page = 1;
            this.fetch();
        },

        clearAlmacenFilter(refetch) {
            const almacenSearch = this.modalEl.querySelector('#selectorCatalogoAlmacenBuscar');
            const hidden = this.modalEl.querySelector('#selectorCatalogoAlmacenId');

            if (!almacenSearch || !hidden) {
                return;
            }

            hidden.value = '';
            almacenSearch.value = '';
            this.modalEl.querySelectorAll('.selector-almacen-card').forEach((card) => {
                card.classList.toggle('active', (card.getAttribute('data-almacen-id') || '') === '');
            });
            this.updateAlmacenActivo('');
            if (refetch) {
                this.loadAlmacenesLista();
                this.page = 1;
                this.fetch();
            }
        },

        updateAlmacenActivo(label) {
            const badge = this.modalEl.querySelector('#selectorCatalogoAlmacenActivo');
            const nombre = this.modalEl.querySelector('#selectorCatalogoAlmacenActivoNombre');
            if (!badge || !nombre) {
                return;
            }
            if (label) {
                nombre.textContent = label;
                badge.classList.remove('d-none');
            } else {
                nombre.textContent = '';
                badge.classList.add('d-none');
            }
        },

        fetch() {
            const cfg = this.instances[this.activeId];
            if (!cfg) {
                return;
            }

            const lista = this.modalEl.querySelector('#selectorCatalogoLista');
            const meta = this.modalEl.querySelector('#selectorCatalogoMeta');
            lista.innerHTML = '<tr><td colspan="2" class="sel-modal-empty"><i class="fas fa-spinner fa-spin d-block mb-2"></i>Cargando…</td></tr>';

            const params = new URLSearchParams({
                page: String(this.page),
                per_page: '20',
            });

            const q = this.modalEl.querySelector('#selectorCatalogoBuscar').value.trim();
            if (q) {
                params.set('q', q);
            }

            const filterSelect = this.modalEl.querySelector('#selectorCatalogoFiltro');
            if (cfg.filter && filterSelect.value !== '') {
                params.set(cfg.filter.param, filterSelect.value);
            }

            if (cfg.filterAlmacen) {
                const almacenId = this.modalEl.querySelector('#selectorCatalogoAlmacenId')?.value || '';
                const param = cfg.filterAlmacen.param || 'almacenid';
                if (almacenId) {
                    params.set(param, almacenId);
                }
            }

            if (cfg.params) {
                Object.entries(cfg.params).forEach(([k, v]) => {
                    if (v !== null && v !== undefined && v !== '') {
                        params.set(k, String(v));
                    }
                });
            }

            fetch(cfg.endpoint + '?' + params.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((r) => {
                    if (!r.ok) {
                        throw new Error('Error al cargar');
                    }
                    return r.json();
                })
                .then((json) => {
                    this.renderList(json.data || []);
                    this.renderPagination(json.meta || {});
                })
                .catch(() => {
                    lista.innerHTML = '<tr><td colspan="2" class="text-center text-danger py-4">No se pudo cargar el catálogo.</td></tr>';
                    meta.textContent = '';
                });
        },

        renderList(items) {
            const lista = this.modalEl.querySelector('#selectorCatalogoLista');
            const cfg = this.instances[this.activeId] || {};

            if (!items.length && !cfg.allowEmpty) {
                lista.innerHTML = '<tr><td colspan="2" class="sel-modal-empty"><i class="fas fa-search d-block mb-2"></i>Sin resultados. Pruebe otro término o filtro.</td></tr>';
                return;
            }

            let html = '';
            if (cfg.allowEmpty && this.page === 1) {
                const todosLabel = cfg.emptyLabel || 'Todos';
                html += `
                <tr class="selector-catalogo-row selector-catalogo-row--todos"
                    data-item-id=""
                    data-item-label="${this.escape(todosLabel)}"
                    data-item-extra="{}"
                    role="button">
                    <td class="sel-col-nombre">
                        <span class="sel-row-icon"><i class="fas fa-times-circle"></i></span>
                        ${this.escape(todosLabel)}
                    </td>
                    <td class="sel-col-meta">Quitar filtro</td>
                </tr>`;
            }

            if (!items.length) {
                html += '<tr><td colspan="2" class="sel-modal-empty"><i class="fas fa-search d-block mb-2"></i>Sin resultados. Pruebe otro término o filtro.</td></tr>';
                lista.innerHTML = html;
                return;
            }

            const actionLabel = cfg.rowAction?.label || (typeof cfg.onRowAction === 'function' ? 'Ver' : null);
            const verVehiculoBtn = cfg.theme === 'vehiculo'
                ? '<div class="sel-ver-vehiculo-row"><button type="button" class="sel-row-ver-vehiculo-btn" title="Ver modelado 3D y ocupación"><i class="fas fa-cube"></i><span>Ver ocupación</span></button></div>'
                : '';
            const selectedId = this.selectedItemId(this.activeId);

            if (cfg.theme === 'vehiculo') {
                const placasVistas = new Set();
                items = items.filter((item) => {
                    const placa = String(item.label || '').trim().toUpperCase();
                    if (!placa || placasVistas.has(placa)) return false;
                    placasVistas.add(placa);
                    return true;
                });
            }

            html += items.map((item) => {
                const rowIcon = typeof cfg.rowIconFn === 'function'
                    ? cfg.rowIconFn(item)
                    : (cfg.rowIcon || 'fa-leaf');
                const isSuggested = (cfg.suggestedItemId != null
                    && String(item.id) === String(cfg.suggestedItemId))
                    || item.sugerido === true;
                const isSelected = selectedId !== '' && String(item.id) === selectedId;
                const rowClasses = ['selector-catalogo-row'];
                if (isSuggested) rowClasses.push('selector-catalogo-row--suggested');
                if (isSelected) rowClasses.push('selector-catalogo-row--selected');
                const hasMap = item.extra && item.extra.lat != null && item.extra.lng != null;
                const mapBtn = (typeof cfg.onMapClick === 'function') && hasMap
                    ? `<button type="button" class="btn btn-xs btn-outline-primary sel-row-map-btn" title="Ver ubicación en mapa"><i class="fas fa-map-marker-alt mr-1"></i>Ver en mapa</button>`
                    : '';
                const metaClass = cfg.theme === 'vehiculo'
                    ? ' sel-col-meta--vehiculo'
                    : (actionLabel ? ' sel-col-meta--with-action' : '');
                return `
                <tr class="${rowClasses.join(' ')}"
                    data-item-id="${item.id}"
                    data-item-label="${this.escape(item.label)}"
                    data-item-extra="${this.escape(JSON.stringify(item.extra || {}))}"
                    role="button">
                    <td class="sel-col-nombre">
                        <span class="sel-row-icon"><i class="fas ${rowIcon}"></i></span>
                        ${this.escape(item.label)}
                        ${isSelected ? '<span class="sel-badge-seleccionado">Seleccionado</span>' : ''}
                        ${!isSelected && isSuggested ? '<span class="sel-badge-sugerido">Recomendado</span>' : ''}
                    </td>
                    <td class="sel-col-meta${metaClass}">
                        ${item.meta_lineas && item.meta_lineas.length
                            ? `<div class="sel-meta-stack">${item.meta_lineas.map((ln) =>
                                `<div class="sel-meta-line"><i class="fas ${ln.icon || 'fa-info-circle'}"></i><span>${this.escape(ln.text || '')}</span></div>`
                            ).join('')}</div>`
                            : `<span class="sel-col-meta-text">${item.meta ? this.escape(item.meta) : '—'}</span>`
                        }
                        ${mapBtn}
                        ${verVehiculoBtn}
                        ${actionLabel ? `<button type="button" class="btn btn-sm btn-outline-success sel-row-action-btn" title="Ver productos en este almacén">${this.escape(actionLabel)}</button>` : ''}
                    </td>
                </tr>`;
            }).join('');
            lista.innerHTML = html;
        },

        renderPagination(meta) {
            const info = this.modalEl.querySelector('#selectorCatalogoMeta');
            const btnPrev = this.modalEl.querySelector('#selectorCatalogoPrev');
            const btnNext = this.modalEl.querySelector('#selectorCatalogoNext');
            const total = meta.total ?? 0;
            const current = meta.current_page ?? 1;
            const last = meta.last_page ?? 1;

            info.textContent = total ? `Mostrando página ${current} de ${last} (${total} registros)` : '';
            btnPrev.disabled = current <= 1;
            btnNext.disabled = current >= last;
            this.page = current;
        },

        syncFiltrosField(wrapper, itemId, label) {
            if (!wrapper || !wrapper.classList.contains('selector-catalogo--filtros')) {
                return;
            }

            const display = wrapper.querySelector('.selector-catalogo-label');
            const clearBtn = wrapper.querySelector('.selector-filtros-field__clear');
            const cfg = this.instances[wrapper.id.replace('selector_wrap_', '')] || {};
            const hasValue = itemId !== '' && itemId !== null && itemId !== undefined;

            if (display) {
                display.classList.toggle('is-empty', !hasValue);
                if (!hasValue) {
                    display.placeholder = cfg.allowEmpty
                        ? (cfg.placeholderEmpty || cfg.emptyLabel || 'Opcional — sin asignar')
                        : 'Elegir…';
                }
            }

            if (clearBtn) {
                clearBtn.classList.toggle('is-hidden', !hasValue);
            }
        },

        select(item) {
            const id = this.activeId;
            if (!id) {
                return;
            }

            const cfg = this.instances[id] || {};
            const wrapper = document.getElementById('selector_wrap_' + id);

            if (wrapper) {
                const hidden = wrapper.querySelector('.selector-catalogo-value');
                const display = wrapper.querySelector('.selector-catalogo-label');

                if (hidden) {
                    hidden.value = item.id;
                }
                if (display) {
                    display.value = item.label;
                    display.classList.remove('text-muted');

                    if (cfg.allowEmpty && item.id === '') {
                        display.value = '';
                        display.placeholder = cfg.placeholderEmpty || cfg.emptyLabel || 'Opcional — sin asignar';
                        display.classList.add('text-muted', 'is-empty');
                    } else {
                        display.classList.remove('is-empty');
                    }
                }

                this.syncFiltrosField(wrapper, item.id, item.label);
            }

            const finalize = () => {
                if (wrapper) {
                    wrapper.dispatchEvent(new CustomEvent('selector-catalogo:change', {
                        bubbles: true,
                        detail: { id: item.id, label: item.label, extra: item.extra || {} },
                    }));
                }
                if (typeof cfg.onSelect === 'function') {
                    cfg.onSelect(item);
                }
            };

            const $modal = window.jQuery(this.modalEl);
            if ($modal.hasClass('show')) {
                $modal.one('hidden.bs.modal', finalize);
                $modal.modal('hide');
            } else {
                finalize();
            }
        },

        clear(id) {
            const cfg = this.instances[id] || {};
            this.activeId = id;
            this.select({ id: '', label: '', extra: {} });
            this.activeId = null;
        },

        setValue(id, value, label) {
            const wrapper = document.getElementById('selector_wrap_' + id);
            if (!wrapper) {
                return;
            }
            wrapper.querySelector('.selector-catalogo-value').value = value;
            const display = wrapper.querySelector('.selector-catalogo-label');
            display.value = label;
            display.classList.toggle('text-muted', !value);
            display.classList.toggle('is-empty', !value);
            this.syncFiltrosField(wrapper, value, label);
            wrapper.dispatchEvent(new CustomEvent('selector-catalogo:change', {
                bubbles: true,
                detail: { id: value, label },
            }));
        },

        escape(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/"/g, '&quot;');
        },
    };

    document.addEventListener('DOMContentLoaded', () => CatalogoSelector.init());
    window.CatalogoSelector = CatalogoSelector;
})(window, document);
