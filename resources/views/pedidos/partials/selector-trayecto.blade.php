<div class="env-trayecto-inline mb-3 pb-3 border-bottom">
    <label class="small font-weight-bold text-muted d-block mb-2">Tipo de trayecto</label>
    <div class="env-destino-pick env-destino-pick--inline js-trayecto-pick mb-0">
        <button type="button" class="env-destino-opcion env-destino-opcion--planta is-active" data-destino="planta">
            <span class="env-destino-opcion__icon env-trayecto-icon env-trayecto-icon--planta"><i class="fas fa-seedling"></i></span>
            <span class="env-destino-opcion__title">Agrícola → Planta</span>
        </button>
        <button type="button" class="env-destino-opcion env-destino-opcion--mayorista" data-destino="mayorista">
            <span class="env-destino-opcion__icon env-trayecto-icon env-trayecto-icon--mayorista"><i class="fas fa-industry"></i></span>
            <span class="env-destino-opcion__title">Planta → Mayorista</span>
        </button>
        <button type="button" class="env-destino-opcion env-destino-opcion--pdv" data-destino="punto-venta">
            <span class="env-destino-opcion__icon env-trayecto-icon env-trayecto-icon--pdv"><i class="fas fa-store"></i></span>
            <span class="env-destino-opcion__title">Mayorista → Punto de Venta</span>
        </button>
    </div>
    <p class="small text-muted mb-0 mt-2 env-trayecto-desc" id="env-trayecto-desc">
        Recogida de cosecha en almacenes agrícolas y entrega en planta procesadora.
    </p>
</div>
