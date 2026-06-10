(function (window, document) {
    'use strict';

    const DEBOUNCE_MS = 320;

    const PlantillaSelector = {
        modalEl: null,
        endpoint: '',
        page: 1,
        debounceTimer: null,
        selectedItem: null,
        onSelect: null,

        init() {
            this.modalEl = document.getElementById('modalSelectorPlantilla');
            if (!this.modalEl) {
                return;
            }

            const searchInput = this.modalEl.querySelector('#selectorPlantillaBuscar');
            const filterSelect = this.modalEl.querySelector('#selectorPlantillaDisponibilidad');
            const btnPrev = this.modalEl.querySelector('#selectorPlantillaPrev');
            const btnNext = this.modalEl.querySelector('#selectorPlantillaNext');
            const btnConfirm = this.modalEl.querySelector('#selectorPlantillaConfirmar');
            const lista = this.modalEl.querySelector('#selectorPlantillaLista');

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

            btnConfirm.addEventListener('click', () => this.confirmSelection());

            lista.addEventListener('click', (e) => {
                const card = e.target.closest('[data-plantilla-id]');
                if (!card) {
                    return;
                }
                let extra = {};
                try {
                    extra = JSON.parse(card.getAttribute('data-plantilla-extra') || '{}');
                } catch (err) {
                    extra = {};
                }
                this.preview({
                    id: card.getAttribute('data-plantilla-id'),
                    label: card.getAttribute('data-plantilla-label') || '',
                    meta: card.getAttribute('data-plantilla-meta') || '',
                    extra,
                });
            });

            if (window.jQuery) {
                window.jQuery(this.modalEl).on('hidden.bs.modal', () => {
                    this.selectedItem = null;
                    this.modalEl.querySelector('#selectorPlantillaConfirmar').disabled = true;
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
            }
        },

        configure(config) {
            this.endpoint = config.endpoint || '';
            this.onSelect = typeof config.onSelect === 'function' ? config.onSelect : null;
        },

        open() {
            if (!this.modalEl || !this.endpoint) {
                return;
            }

            this.page = 1;
            this.selectedItem = null;
            this.modalEl.querySelector('#selectorPlantillaBuscar').value = '';
            this.modalEl.querySelector('#selectorPlantillaDisponibilidad').value = 'operativas';
            this.modalEl.querySelector('#selectorPlantillaConfirmar').disabled = true;
            this.renderDetalleVacio();

            if (window.jQuery) {
                window.jQuery(this.modalEl).modal('show');
            }
            this.fetch();
        },

        fetch() {
            if (!this.endpoint) {
                return;
            }

            const lista = this.modalEl.querySelector('#selectorPlantillaLista');
            lista.innerHTML = '<div class="text-center text-muted py-4 small"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando…</div>';

            const params = new URLSearchParams({
                page: String(this.page),
                per_page: '15',
                disponibilidad: this.modalEl.querySelector('#selectorPlantillaDisponibilidad').value || 'operativas',
            });

            const q = this.modalEl.querySelector('#selectorPlantillaBuscar').value.trim();
            if (q) {
                params.set('q', q);
            }

            fetch(this.endpoint + '?' + params.toString(), {
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
                    lista.innerHTML = '<div class="text-center text-danger py-4 small">No se pudieron cargar los procesos.</div>';
                    this.modalEl.querySelector('#selectorPlantillaMeta').textContent = '';
                });
        },

        renderList(items) {
            const lista = this.modalEl.querySelector('#selectorPlantillaLista');
            if (!items.length) {
                lista.innerHTML = '<div class="text-center text-muted py-4 small">Sin resultados. Pruebe otro término o filtro.</div>';
                return;
            }

            const selectedId = this.selectedItem?.id;
            lista.innerHTML = items.map((item) => {
                const extra = item.extra || {};
                const estadoBadge = extra.estado === 'mantenimiento'
                    ? '<span class="badge badge-warning text-dark ml-1">Mantenimiento</span>'
                    : '<span class="badge badge-success ml-1">Disponible</span>';
                const active = String(item.id) === String(selectedId) ? ' active' : '';

                return `
                    <button type="button" class="selector-plantilla-card${active}"
                            data-plantilla-id="${item.id}"
                            data-plantilla-label="${this.escape(item.label)}"
                            data-plantilla-meta="${this.escape(item.meta || '')}"
                            data-plantilla-extra="${this.escape(JSON.stringify(extra))}">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong class="text-dark">${this.escape(item.label)}</strong>
                            ${estadoBadge}
                        </div>
                        <small class="text-muted d-block mt-1">${item.meta ? this.escape(item.meta) : '—'}</small>
                    </button>
                `;
            }).join('');
        },

        renderPagination(meta) {
            const info = this.modalEl.querySelector('#selectorPlantillaMeta');
            const btnPrev = this.modalEl.querySelector('#selectorPlantillaPrev');
            const btnNext = this.modalEl.querySelector('#selectorPlantillaNext');
            const total = meta.total ?? 0;
            const current = meta.current_page ?? 1;
            const last = meta.last_page ?? 1;

            info.textContent = total ? `Página ${current} de ${last} (${total} procesos)` : '';
            btnPrev.disabled = current <= 1;
            btnNext.disabled = current >= last;
            this.page = current;
        },

        preview(item) {
            this.selectedItem = item;
            this.modalEl.querySelectorAll('.selector-plantilla-card').forEach((card) => {
                card.classList.toggle('active', card.getAttribute('data-plantilla-id') === String(item.id));
            });

            const extra = item.extra || {};
            const btnConfirm = this.modalEl.querySelector('#selectorPlantillaConfirmar');
            btnConfirm.disabled = extra.seleccionable === false;

            this.renderDetalle(item);
        },

        renderDetalleVacio() {
            const panel = this.modalEl.querySelector('#selectorPlantillaDetalle');
            panel.innerHTML = `
                <div class="selector-plantilla-detalle-vacio text-muted text-center py-5 px-3">
                    <i class="fas fa-hand-pointer fa-2x mb-2 d-block opacity-50"></i>
                    Seleccione un proceso de la lista para ver sus etapas y detalles.
                </div>
            `;
        },

        renderDetalle(item) {
            const extra = item.extra || {};
            const panel = this.modalEl.querySelector('#selectorPlantillaDetalle');
            const palabras = (extra.palabras_clave || []).map((k) =>
                `<span class="badge badge-light border mr-1 mb-1">${this.escape(k)}</span>`
            ).join('');

            const pasosHtml = (extra.pasos || []).map((paso) => {
                const cierre = paso.es_cierre
                    ? '<span class="badge badge-info ml-1">Cierra transformación</span>'
                    : '';
                const mant = paso.maquina_mantenimiento
                    ? '<br><small class="text-warning"><i class="fas fa-wrench mr-1"></i>Máquina en mantenimiento</small>'
                    : '';

                return `
                    <div class="selector-plantilla-paso ${paso.es_cierre ? 'selector-plantilla-paso--cierre' : ''}">
                        <span class="selector-plantilla-paso-num">${paso.orden}</span>
                        <div>
                            <strong>${this.escape(paso.proceso)}</strong>${cierre}
                            <br><small class="text-muted"><i class="fas fa-cogs mr-1"></i>${this.escape(paso.maquina)}</small>
                            ${paso.notas ? `<br><small class="text-secondary">${this.escape(paso.notas)}</small>` : ''}
                            ${mant}
                        </div>
                    </div>
                `;
            }).join('');

            const avisoMant = extra.estado === 'mantenimiento'
                ? `<div class="alert alert-warning py-2 px-3 small mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Este proceso no está disponible: hay máquinas en mantenimiento.
                   </div>`
                : '';

            panel.innerHTML = `
                <div class="p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="font-weight-bold text-success mb-1">${this.escape(item.label)}</h6>
                            ${extra.producto_ejemplo ? `<small class="text-muted">Producto de referencia: <strong>${this.escape(extra.producto_ejemplo)}</strong></small>` : ''}
                        </div>
                        ${extra.url ? `<a href="${this.escape(extra.url)}" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-external-link-alt mr-1"></i>Ver ficha</a>` : ''}
                    </div>
                    ${avisoMant}
                    ${extra.descripcion ? `<p class="small text-muted mb-3">${this.escape(extra.descripcion)}</p>` : ''}
                    ${palabras ? `<div class="mb-3"><span class="small text-muted d-block mb-1">Palabras clave</span>${palabras}</div>` : ''}
                    <div class="small font-weight-bold text-uppercase text-muted mb-2" style="letter-spacing:.05em;">
                        <i class="fas fa-list-ol mr-1"></i> Etapas (${(extra.pasos || []).length})
                    </div>
                    <div class="selector-plantilla-pasos">
                        ${pasosHtml || '<p class="text-muted small mb-0">Sin pasos definidos.</p>'}
                    </div>
                </div>
            `;
        },

        confirmSelection() {
            if (!this.selectedItem || !this.onSelect) {
                return;
            }

            const extra = this.selectedItem.extra || {};
            if (extra.seleccionable === false) {
                return;
            }

            this.onSelect({
                id: this.selectedItem.id,
                label: this.selectedItem.label,
                extra,
            });

            if (window.jQuery) {
                window.jQuery(this.modalEl).modal('hide');
            }
        },

        escape(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/"/g, '&quot;');
        },
    };

    window.PlantillaSelector = PlantillaSelector;

    document.addEventListener('DOMContentLoaded', () => PlantillaSelector.init());
})(window, document);
