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
.pdv-unidad-badge--inline {
    min-width: auto;
    border-radius: 8px;
    font-size: .78rem;
    padding: .15rem .55rem;
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
.modulo-filtros-panel .selector-catalogo-wrapper.form-group { margin-bottom: 0; }
.modulo-filtros-panel .selector-catalogo-wrapper > label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: .35rem;
}
.pdv-pedidos-resumen > [class*="col-"] {
    display: flex;
    margin-bottom: 1rem;
}
.pdv-pedidos-resumen .small-box {
    width: 100%;
    min-height: 112px;
    margin-bottom: 0;
    display: flex;
    flex-direction: column;
}
.pdv-pedidos-resumen .small-box .inner {
    flex: 1;
    padding: 14px 16px;
}
.pdv-pedidos-resumen .small-box .inner h3 {
    font-size: 2rem;
    margin: 0 0 .25rem;
}
.pdv-pedidos-resumen .small-box .inner p {
    margin: 0;
    font-size: .875rem;
    line-height: 1.35;
    min-height: 2.5em;
}
.pdv-pedidos-filtros .modulo-filtros-acciones {
    width: 100%;
    flex-wrap: nowrap;
}
@media (max-width: 991.98px) {
    .pdv-pedidos-filtros .modulo-filtros-acciones {
        flex-wrap: wrap;
    }
}
.pdv-solicitud-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .03em;
    color: #14532d;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #86efac;
    border-radius: 8px;
    padding: .38rem .7rem;
    box-shadow: 0 1px 3px rgba(16, 185, 129, .15);
    white-space: nowrap;
}
.pdv-solicitud-pill i {
    color: #059669;
    font-size: .68rem;
    opacity: .9;
}
.pdv-estado-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .02em;
    padding: .38rem .75rem;
    border-radius: 999px;
    white-space: nowrap;
    line-height: 1.2;
}
.pdv-estado-pill i { font-size: .82rem; opacity: .95; }
.pdv-estado-pill--revision { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; border: 1px solid #fcd34d; }
.pdv-estado-pill--preparacion { background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #075985; border: 1px solid #7dd3fc; }
.pdv-estado-pill--asignado { background: linear-gradient(135deg, #ffedd5, #fed7aa); color: #9a3412; border: 1px solid #fdba74; }
.pdv-estado-pill--ruta { background: linear-gradient(135deg, #dbeafe, #93c5fd); color: #1e3a8a; border: 1px solid #60a5fa; }
.pdv-estado-pill--recibido { background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; border: 1px solid #34d399; }
.pdv-estado-pill--rechazado { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; border: 1px solid #f87171; }
.pdv-estado-pill--cancelado, .pdv-estado-pill--neutral { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
</style>
