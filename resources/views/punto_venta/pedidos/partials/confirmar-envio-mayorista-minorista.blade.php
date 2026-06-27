<div class="card pdv-card card-outline card-warning mb-3">
    <div class="card-body py-3">
        <div class="pdv-espera-mayorista mb-3 mb-md-2">
            <div class="pdv-espera-mayorista__icon"><i class="fas fa-truck-loading"></i></div>
            <div>
                <div class="pdv-espera-mayorista__title">Confirme el envío entrante</div>
                <p class="pdv-espera-mayorista__text mb-0">
                    El mayorista programó un envío hacia su punto de venta con transportista asignado.
                    Confirme para habilitar las condiciones del vehículo y la salida en ruta.
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('punto-venta.pedidos.confirmar-envio-mayorista', $pedido) }}" class="js-form-confirmar-envio-mayorista">
            @csrf
            <button type="submit" class="btn btn-success btn-block js-btn-confirmar-envio-mayorista"
                data-confirm-modal
                data-confirm-title="Confirmar envío"
                data-confirm-message="¿Confirma que espera este envío en su punto de venta? El transportista podrá continuar con el cierre operativo."
                data-confirm-tone="success"
                data-confirm-btn="Confirmar envío">
                <i class="fas fa-check mr-1"></i> Confirmar envío
            </button>
        </form>
    </div>
</div>
@once
@push('scripts')
<script>
document.addEventListener('submit', function (e) {
    const form = e.target.closest('.js-form-confirmar-envio-mayorista');
    if (!form) return;
    const btn = form.querySelector('.js-btn-confirmar-envio-mayorista');
    if (!btn || btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Confirmando…';
});
</script>
@endpush
@endonce
