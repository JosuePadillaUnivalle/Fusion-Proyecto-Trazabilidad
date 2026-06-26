{{-- Modal reutilizable de confirmación (sustituye confirm() del navegador) --}}
<div class="modal fade" id="modalConfirmarAccion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 py-3 px-4" id="modalConfirmarHeader" style="background: linear-gradient(135deg, #7f1d1d, #dc2626); color: #fff;">
                <h5 class="modal-title font-weight-bold mb-0" id="modalConfirmarTitulo">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar acción
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-4">
                <p class="mb-0 text-dark" id="modalConfirmarMensaje" style="font-size: 1rem; line-height: 1.5;"></p>
            </div>
            <div class="modal-footer border-0 bg-light px-4 py-3">
                <button type="button" class="btn btn-light px-4" id="btnCancelarConfirmar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger px-4 font-weight-bold" id="btnConfirmarAccion">
                    <i class="fas fa-check mr-1"></i>Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const state = window.__modalConfirmarState || (window.__modalConfirmarState = {
        formPendiente: null,
        callbackPendiente: null,
        modoAviso: false,
    });

    function mostrarModalConfirmar(titulo, mensaje, tono, btnText, esAviso) {
        state.modoAviso = !!esAviso;
        const cancelBtn = document.getElementById('btnCancelarConfirmar');
        if (cancelBtn) cancelBtn.style.display = state.modoAviso ? 'none' : '';
        const tituloEl = document.getElementById('modalConfirmarTitulo');
        const mensajeEl = document.getElementById('modalConfirmarMensaje');
        const headerEl = document.getElementById('modalConfirmarHeader');
        const btnEl = document.getElementById('btnConfirmarAccion');
        const iconos = { success: 'check-circle', warning: 'exclamation-circle', danger: 'exclamation-triangle', info: 'info-circle' };
        const fondos = {
            success: 'linear-gradient(135deg, #1e4620, #2c5530)',
            warning: 'linear-gradient(135deg, #b45309, #f59e0b)',
            danger: 'linear-gradient(135deg, #7f1d1d, #dc2626)',
            info: 'linear-gradient(135deg, #1e3a5f, #2563eb)',
        };
        const botones = {
            success: 'btn btn-success px-4 font-weight-bold',
            warning: 'btn btn-warning px-4 font-weight-bold text-dark',
            danger: 'btn btn-danger px-4 font-weight-bold',
            info: 'btn btn-primary px-4 font-weight-bold',
        };
        const t = ['success', 'warning', 'danger', 'info'].includes(tono) ? tono : (state.modoAviso ? 'warning' : 'danger');
        if (headerEl) headerEl.style.background = fondos[t];
        if (btnEl) {
            btnEl.className = botones[t];
            btnEl.innerHTML = '<i class="fas fa-check mr-1"></i>' + (btnText || (state.modoAviso ? 'Entendido' : 'Aceptar'));
        }
        if (tituloEl) {
            tituloEl.innerHTML = '<i class="fas fa-' + iconos[t] + ' mr-2"></i>' + (titulo || 'Confirmar acción');
        }
        if (mensajeEl) mensajeEl.textContent = mensaje || '¿Desea continuar?';
        if (window.jQuery) {
            window.jQuery('#modalConfirmarAccion').modal('show');
        }
    }

    function enviarFormularioPendiente(form) {
        if (!form || !form.isConnected) return;
        let oculto = form.querySelector('input[type="submit"].js-modal-confirm-submit');
        if (!oculto) {
            oculto = document.createElement('input');
            oculto.type = 'submit';
            oculto.className = 'js-modal-confirm-submit d-none';
            oculto.setAttribute('aria-hidden', 'true');
            oculto.tabIndex = -1;
            form.appendChild(oculto);
        }
        oculto.click();
    }

    function restaurarBotonConfirmar() {
        const btnConfirmar = document.getElementById('btnConfirmarAccion');
        if (btnConfirmar) {
            btnConfirmar.dataset.submitting = '0';
            btnConfirmar.disabled = false;
        }
    }

    function ejecutarConfirmacion() {
        if (state.modoAviso) {
            if (window.jQuery) window.jQuery('#modalConfirmarAccion').modal('hide');
            return;
        }
        const btnConfirmar = document.getElementById('btnConfirmarAccion');
        if (btnConfirmar?.dataset.submitting === '1') {
            return;
        }
        if (btnConfirmar) {
            btnConfirmar.dataset.submitting = '1';
            btnConfirmar.disabled = true;
        }
        if (state.callbackPendiente) {
            const cb = state.callbackPendiente;
            state.callbackPendiente = null;
            restaurarBotonConfirmar();
            if (window.jQuery) window.jQuery('#modalConfirmarAccion').modal('hide');
            cb(true);
            return;
        }
        if (!state.formPendiente) return;

        const form = state.formPendiente;
        state.formPendiente = null;

        if (form.classList.contains('js-lp-guardar-scroll')) {
            try {
                sessionStorage.setItem('lp_procesamiento_scroll', String(window.scrollY));
            } catch (e) {}
        }

        if (form.dataset.ajaxLpAction === 'completar-etapa' && window.LpProcesamientoAjax) {
            if (window.jQuery) window.jQuery('#modalConfirmarAccion').modal('hide');
            window.LpProcesamientoAjax.completarEtapa(form);
            return;
        }

        const enviar = function () {
            enviarFormularioPendiente(form);
        };

        if (window.jQuery && window.jQuery('#modalConfirmarAccion').length) {
            window.jQuery('#modalConfirmarAccion').one('hidden.bs.modal', enviar).modal('hide');
        } else {
            enviar();
        }
    }

    function abrirConfirmacionDesdeForm(btn) {
        const form = btn.closest('form');
        if (!form) return;
        const attrs = form.dataset;
        window.ModalConfirmar.abrir(
            form,
            attrs.confirmTitle || btn.getAttribute('data-confirm-title') || 'Confirmar acción',
            attrs.confirmMessage || btn.getAttribute('data-confirm-message') || '¿Desea continuar?',
            attrs.confirmTone || btn.getAttribute('data-confirm-tone') || 'danger',
            attrs.confirmBtn || btn.getAttribute('data-confirm-btn') || undefined
        );
    }

    window.ModalConfirmar = {
        abrir(form, titulo, mensaje, tono, btnText) {
            state.formPendiente = form;
            state.callbackPendiente = null;
            mostrarModalConfirmar(titulo, mensaje, tono, btnText, false);
        },
        confirmar(opts) {
            opts = opts || {};
            return new Promise(function (resolve) {
                state.callbackPendiente = resolve;
                state.formPendiente = null;
                mostrarModalConfirmar(opts.titulo, opts.mensaje, opts.tono, opts.btnText, false);
            });
        },
        aviso(opts) {
            opts = typeof opts === 'string' ? { mensaje: opts } : (opts || {});
            state.callbackPendiente = null;
            state.formPendiente = null;
            mostrarModalConfirmar(
                opts.titulo || 'Aviso',
                opts.mensaje || '',
                opts.tono || 'warning',
                opts.btnText || 'Entendido',
                true
            );
        },
    };

    if (!window.__modalConfirmarBound) {
        window.__modalConfirmarBound = true;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-confirm-modal], [data-confirm-submit]');
            if (!btn) return;
            e.preventDefault();
            abrirConfirmacionDesdeForm(btn);
        });

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('#btnConfirmarAccion');
            if (!btn) return;
            e.preventDefault();
            ejecutarConfirmacion();
        });

        document.getElementById('modalConfirmarAccion')?.addEventListener('hidden.bs.modal', function () {
            if (state.callbackPendiente) {
                state.callbackPendiente(false);
                state.callbackPendiente = null;
            }
            state.formPendiente = null;
            state.modoAviso = false;
            const cancelBtn = document.getElementById('btnCancelarConfirmar');
            if (cancelBtn) cancelBtn.style.display = '';
            restaurarBotonConfirmar();
        });

        if (window.jQuery) {
            window.jQuery('#modalConfirmarAccion').on('hidden.bs.modal', function () {
                if (state.callbackPendiente) {
                    state.callbackPendiente(false);
                    state.callbackPendiente = null;
                }
                state.formPendiente = null;
                state.modoAviso = false;
                const cancelBtn = document.getElementById('btnCancelarConfirmar');
                if (cancelBtn) cancelBtn.style.display = '';
                restaurarBotonConfirmar();
            });
        }
    }
})();
</script>
@endpush
@endonce
