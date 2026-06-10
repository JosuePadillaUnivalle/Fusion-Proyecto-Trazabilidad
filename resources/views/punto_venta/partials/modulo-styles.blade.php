<style>
.pdv-card { border-radius: 14px; border: 0; box-shadow: 0 1px 3px rgba(15,23,42,.08); }
.pdv-card .card-header { border-radius: 14px 14px 0 0 !important; }
.pdv-map { height: 340px; width: 100%; border-radius: 10px; border: 2px solid #dee2e6; z-index: 1; }
.pdv-campo-guia { font-size: .85rem; color: #64748b; margin-top: 4px; }
.pdv-picker-field {
    display: flex; align-items: stretch; gap: 0;
    border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #fff;
}
.pdv-picker-field:focus-within { border-color: #059669; box-shadow: 0 0 0 .15rem rgba(5,150,105,.12); }
.pdv-picker-field .picker-display {
    flex: 1; border: 0; background: transparent; padding: .55rem .85rem;
    font-size: .9rem; min-height: 42px;
}
.pdv-picker-field .picker-actions { display: flex; border-left: 1px solid #e5e7eb; }
.pdv-picker-field .picker-actions .btn { border-radius: 0; border: 0; padding: 0 .85rem; font-weight: 600; font-size: .85rem; }
.pdv-section-title {
    font-size: .72rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: #64748b; margin-bottom: .75rem;
}
.pdv-unidad-badge {
    display: inline-flex; align-items: center; min-width: 52px; justify-content: center;
    background: #ecfdf5; color: #047857; font-weight: 700; font-size: .85rem;
    border: 1px solid #a7f3d0; border-radius: 0 8px 8px 0; padding: 0 .75rem;
}
.pdv-acciones-grupo .btn { min-width: 88px; }
.modulo-filtros-panel {
    background: #f8faf9;
    border-bottom: 1px solid #e8f0ea;
    padding: 1.15rem 1.35rem;
}
.modulo-filtros-panel label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: .35rem;
}
.modulo-filtros-panel .form-control { border-radius: 8px; font-size: .9rem; }
.modulo-filtros-panel .btn-filtro-modulo {
    padding: .55rem 1.25rem;
    font-size: .875rem;
    font-weight: 600;
    border-radius: 8px;
    min-width: 110px;
}
.modulo-filtros-acciones { gap: .5rem; }
</style>
