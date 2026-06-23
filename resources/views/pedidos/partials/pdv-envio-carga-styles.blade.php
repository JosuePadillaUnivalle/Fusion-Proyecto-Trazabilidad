<style>
#flujo-punto-venta #pdv-productos-envio-container {
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
#flujo-punto-venta .pdv-envio-presentacion-pick.is-locked select {
    background: #f8fafc;
    cursor: not-allowed;
}
#flujo-punto-venta .pdv-envio-presentacion-pick select:not(:disabled) {
    border-color: #fdba74;
}
#flujo-punto-venta .pdv-envio-stock-alerta {
    display: block;
    margin-top: .35rem;
    font-size: .75rem;
    font-weight: 600;
    color: #b45309;
}
#flujo-punto-venta .traslado-producto-row.is-stock-error .form-control {
    border-color: #fca5a5;
}
</style>
