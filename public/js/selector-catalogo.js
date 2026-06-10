(function (window, document) {
    'use strict';

    const DEBOUNCE_MS = 320;

    const CatalogoSelector = {
        instances: {},
        modalEl: null,
        activeId: null,
        debounceTimer: null,
        almacenDebounceTimer: null,
        page: 1,

        init() {
            this.modalEl = document.getElementById('modalSelectorCatalogo');
            if (!this.modalEl) {
                return;
            }

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
                const row = e.target.closest('[data-item-id]');
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
                this.activeId = null;
            });

            window.jQuery(this.modalEl).on('show.bs.modal', () => {
                const visibleCount = document.querySelectorAll('.modal.show').length;
                const zIndex = 1050 + (10 * visibleCount);
                this.modalEl.style.zIndex = String(zIndex);
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length) {
                        backdrops[backdrops.length - 1].style.zIndex = String(zIndex - 1);
                    }
                }, 0);
            });
        },

        register(id, config) {
            this.instances[id] = config;
        },

        open(id) {
            const cfg = this.instances[id];
            if (!cfg || !this.modalEl) {
                return;
            }

            this.activeId = id;
            this.page = 1;

            this.modalEl.querySelector('#selectorCatalogoTitulo').textContent = cfg.title || 'Buscar y seleccionar';
            this.modalEl.querySelector('#selectorCatalogoBuscar').value = '';
            this.modalEl.querySelector('#selectorCatalogoBuscar').placeholder = cfg.searchPlaceholder || 'Escriba para buscar…';

            const filterWrap = this.modalEl.querySelector('#selectorCatalogoFiltroWrap');
            const filterSelect = this.modalEl.querySelector('#selectorCatalogoFiltro');
            if (cfg.filter && cfg.filter.options) {
                filterWrap.style.display = '';
                filterSelect.innerHTML = cfg.filter.options.map((o) =>
                    `<option value="${o.value ?? ''}">${o.label}</option>`
                ).join('');
                filterSelect.name = cfg.filter.param || 'filtro';
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
            lista.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando…</td></tr>';

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
            if (!items.length) {
                lista.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-4">Sin resultados. Pruebe otro término o filtro.</td></tr>';
                return;
            }

            lista.innerHTML = items.map((item) => `
                <tr class="selector-catalogo-row"
                    data-item-id="${item.id}"
                    data-item-label="${this.escape(item.label)}"
                    data-item-extra="${this.escape(JSON.stringify(item.extra || {}))}"
                    role="button">
                    <td><strong>${this.escape(item.label)}</strong></td>
                    <td class="text-muted small">${item.meta ? this.escape(item.meta) : '—'}</td>
                </tr>
            `).join('');
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
                        display.classList.add('text-muted');
                    }
                }

                wrapper.dispatchEvent(new CustomEvent('selector-catalogo:change', {
                    bubbles: true,
                    detail: { id: item.id, label: item.label, extra: item.extra || {} },
                }));
            }

            if (typeof cfg.onSelect === 'function') {
                cfg.onSelect(item);
            }

            window.jQuery(this.modalEl).modal('hide');
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
