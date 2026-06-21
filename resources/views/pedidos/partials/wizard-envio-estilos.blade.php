@include('partials.modulo-envios-styles')
<style>
.env-wizard-wrap { --ew-green: #2c5530; --ew-green-light: #f0f7f1; }
.env-wizard-wrap .wizard-step { display: none; }
.env-wizard-wrap .wizard-step.active { display: block; }
.env-wizard-wrap .card-modulo-main { border-top-color: var(--ew-green); }
.env-wizard-progress .step-item { cursor: default; padding: .35rem .25rem; }
.env-wizard-progress .step-item.can-jump { cursor: pointer; }
.env-wizard-progress .step-label { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
.env-wizard-progress .step-title { font-size: .92rem; font-weight: 700; color: #1e293b; }
.env-wizard-panel {
    border: 1px solid #e2e8f0; border-radius: 12px; background: #fff;
    margin-bottom: 1rem; overflow: hidden;
}
.env-wizard-panel__head {
    padding: .75rem 1.1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    font-weight: 700; font-size: .9rem; color: #334155;
}
.env-wizard-panel__head i { color: var(--ew-green); margin-right: .4rem; }
.env-wizard-panel__body { padding: 1.1rem; }
.env-wizard-nav {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 0 .25rem; border-top: 1px solid #e2e8f0; margin-top: .5rem;
}
.env-recurso-card {
    border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem;
    background: #fafbfc; transition: border-color .15s ease, box-shadow .15s ease;
    min-height: 120px; cursor: pointer;
    display: flex; flex-direction: column; height: 100%;
}
.env-asignacion-cards > [class*="col-"] { display: flex; flex-direction: column; }
.env-recurso-card:hover { border-color: #94a3b8; box-shadow: 0 4px 14px rgba(15,23,42,.06); }
.env-recurso-card.is-selected { border-color: var(--ew-green); background: var(--ew-green-light); }
.env-recurso-card__icon {
    width: 44px; height: 44px; border-radius: 10px; background: #fff;
    border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: var(--ew-green); margin-bottom: .5rem;
}
.env-recurso-card__title { font-weight: 700; color: #1e293b; font-size: .95rem; }
.env-recurso-card__meta { font-size: .8rem; color: #64748b; margin-top: .15rem; }
.env-recurso-card__top { display: flex; align-items: flex-start; gap: .75rem; flex: 1; }
.env-recurso-card__top .env-recurso-card__icon { margin-bottom: 0; flex-shrink: 0; }
.env-recurso-card__info { flex: 1; min-width: 0; }
.env-recurso-card__detalle { margin-top: .35rem; }
.env-recurso-card__line {
    font-size: .8rem; color: #475569; line-height: 1.45;
    margin-bottom: .2rem;
}
.env-recurso-card__line:last-child { margin-bottom: 0; }
.env-recurso-card__chip {
    display: inline-block; font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .03em; color: #047857; background: #d1fae5; border: 1px solid #6ee7b7;
    border-radius: 5px; padding: .05rem .35rem; margin-right: .35rem; vertical-align: middle;
}
.env-recurso-card__placa { font-weight: 700; color: #1e293b; }
.env-recurso-card__estado { font-size: .78rem; }
.env-recurso-card--vehiculo.is-selected .env-recurso-card__chip { display: none; }
.env-recurso-card__hint {
    display: block; font-size: .72rem; color: #94a3b8; font-weight: 400; margin-top: .25rem; line-height: 1.3;
}
.env-recurso-card__action {
    font-size: .78rem; color: var(--ew-green); font-weight: 600; margin-top: auto;
    padding-top: .55rem; border-top: 1px solid #e2e8f0;
}
.env-recurso-card.is-selected .env-recurso-card__action { border-top-color: #d1e7d4; }
.env-particion-card {
    border: 1px solid #dbeafe; border-radius: 12px; background: #f8fafc;
    padding: 1rem; margin-bottom: 1rem;
}
.env-particion-card__head {
    font-weight: 700; color: #1e40af; font-size: .85rem; margin-bottom: .75rem;
}
.env-btn-particion {
    border: 2px dashed #94a3b8; border-radius: 12px; background: #fff;
    color: #475569; font-weight: 600; padding: .85rem 1.25rem; width: 100%;
    transition: border-color .15s ease, color .15s ease;
}
.env-btn-particion:hover { border-color: var(--ew-green); color: var(--ew-green); background: var(--ew-green-light); }
.env-destino-pick--inline { margin-bottom: 0; }
.env-destino-pick--inline .env-destino-opcion { padding: .65rem .75rem; }
.env-destino-pick--inline .env-destino-opcion__title { font-size: .82rem; }
.env-destino-pick--inline .env-destino-opcion__desc { display: none; }
.env-destino-pick--inline .env-destino-opcion__icon { width: 32px; height: 32px; font-size: .9rem; margin-bottom: .35rem; }
.env-trayecto-icon {
    border-radius: 8px;
    box-shadow: none;
}
.env-trayecto-icon--planta {
    background: #f0f7f1 !important;
    color: #2c5530 !important;
    border: 1px solid #d1e7d4;
}
.env-trayecto-icon--mayorista {
    background: #f5f3ff !important;
    color: #6d28d9 !important;
    border: 1px solid #e9e5ff;
}
.env-trayecto-icon--pdv {
    background: #fff7ed !important;
    color: #c2410c !important;
    border: 1px solid #fed7aa;
}
.env-trayecto-inline { border-color: #e2e8f0 !important; }

/* Pasos de ruta — tema por trayecto */
.env-paso {
    border: 2px solid #e2e8f0; border-radius: 12px; padding: 1rem;
    margin-bottom: 1rem; background: #fafbfc;
}
.env-paso__num {
    width: 28px; height: 28px; border-radius: 50%; color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .85rem; margin-right: .5rem; flex-shrink: 0;
}
#flujo-planta .env-paso__num { background: #2c5530; }
#flujo-mayorista .env-paso__num,
#flujo-mayorista .tpm-paso__num { background: #6d28d9; }
#flujo-punto-venta .env-paso__num { background: #d97706; }

/* Botón mapa — ejecutivo, sin gradiente */
.env-btn-mapa-toggle {
    border: 1.5px solid #dee2e6; color: #475569; background: #fff;
    border-radius: 8px; font-weight: 600; font-size: .82rem;
    padding: .35rem .85rem; transition: background .15s ease, border-color .15s ease;
}
.env-btn-mapa-toggle:hover,
.env-btn-mapa-toggle:focus { box-shadow: none; }
.env-btn-mapa-toggle--planta { border-color: #2c5530; color: #2c5530; }
.env-btn-mapa-toggle--planta:hover,
.env-btn-mapa-toggle--planta.active { background: #f0f7f1; border-color: #2c5530; color: #2c5530; }
.env-btn-mapa-toggle--mayorista { border-color: #6d28d9; color: #6d28d9; }
.env-btn-mapa-toggle--mayorista:hover,
.env-btn-mapa-toggle--mayorista.active { background: #f5f3ff; border-color: #6d28d9; color: #6d28d9; }
.env-btn-mapa-toggle--pdv { border-color: #d97706; color: #d97706; }
.env-btn-mapa-toggle--pdv:hover,
.env-btn-mapa-toggle--pdv.active { background: #fff7ed; border-color: #d97706; color: #d97706; }

#flujo-planta .env-wizard-panel__head i { color: #2c5530; }
#flujo-mayorista .env-wizard-panel__head i { color: #6d28d9; }
#flujo-punto-venta .env-wizard-panel__head i { color: #d97706; }
#flujo-planta .btn-agregar-recogida { border-color: #2c5530; color: #2c5530; }
#flujo-planta .btn-agregar-recogida:hover { background: #f0f7f1; }
#flujo-planta .pedido-picker-field .btn-picker-accion { color: #2c5530; background: #f8fafc; }
#flujo-planta .pedido-picker-field .btn-picker-accion:hover { background: #f0f7f1; }
#flujo-mayorista .pedido-picker-field .btn-picker-accion { color: #6d28d9; background: #f8fafc; }
#flujo-mayorista .pedido-picker-field .btn-picker-accion:hover { background: #f5f3ff; }
#flujo-mayorista .btn-agregar-producto-traslado { border-color: #6d28d9; color: #6d28d9; }
#flujo-mayorista .btn-agregar-producto-traslado:hover { background: #f5f3ff; }
#flujo-punto-venta .pedido-picker-field .btn-picker-accion { color: #d97706; background: #f8fafc; }
#flujo-punto-venta .pedido-picker-field .btn-picker-accion:hover { background: #fff7ed; }

#env-wizard-unificado[data-tema="mayorista"] .wizard-progress .step-item.active .step-badge { background: #6d28d9 !important; }
#env-wizard-unificado[data-tema="punto-venta"] .wizard-progress .step-item.active .step-badge { background: #d97706 !important; }
#env-wizard-unificado[data-tema="planta"] .wizard-progress .step-item.done .step-badge { background: #3d6b42 !important; }
#env-wizard-unificado[data-tema="mayorista"] .wizard-progress .step-item.done .step-badge { background: #7c3aed !important; }
#env-wizard-unificado[data-tema="mayorista"] #wizard-pasos-compartidos .env-wizard-panel__head i { color: #6d28d9; }
#env-wizard-unificado[data-tema="mayorista"] #wizard-pasos-compartidos .env-recurso-card.is-selected { border-color: #6d28d9; background: #f5f3ff; }
#env-wizard-unificado[data-tema="mayorista"] #wizard-pasos-compartidos .env-recurso-card__icon { color: #6d28d9; }
#env-wizard-unificado[data-tema="mayorista"] #wizard-pasos-compartidos .env-recurso-card__action { color: #6d28d9; }
#env-wizard-unificado[data-tema="mayorista"] #wizard-pasos-compartidos .env-recurso-card__chip { color: #6d28d9; background: #ede9fe; border-color: #c4b5fd; }
#env-wizard-unificado[data-tema="mayorista"] #wizard-pasos-compartidos .env-btn-particion:hover { border-color: #6d28d9; color: #6d28d9; background: #f5f3ff; }
#env-wizard-unificado[data-tema="planta"] #wizard-pasos-compartidos .env-wizard-panel__head i { color: #2c5530; }
#env-wizard-unificado[data-tema="punto-venta"] .wizard-progress .step-item.done .step-badge { background: #ea580c !important; }
.env-confirm-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin-bottom: 1.25rem; }
.env-confirm-ruta-flow {
    display: grid; grid-template-columns: 1fr auto 1fr; gap: .75rem; align-items: stretch;
}
.env-confirm-ruta-punto {
    border-radius: 10px; padding: .85rem 1rem; border: 1px solid #e2e8f0;
}
.env-confirm-ruta-punto--origen {
    background: #f0f7f1; border-color: #d1e7d4; border-left: 4px solid #2c5530;
}
.env-confirm-ruta-punto--destino {
    background: #f8fafc; border-color: #cbd5e1; border-left: 4px solid #3b82f6;
}
.env-confirm-ruta-punto__lbl {
    font-size: .72rem; text-transform: uppercase; color: #64748b; letter-spacing: .03em; display: block;
}
.env-confirm-ruta-punto__val {
    font-weight: 700; color: #1e293b; font-size: .92rem; margin-top: .25rem; display: block; line-height: 1.35;
}
.env-confirm-ruta-flecha {
    display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.35rem; padding: 0 .15rem;
}
.env-confirm-carga-badge {
    display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: .65rem 1rem; color: #334155;
}
.env-confirm-carga-badge__lbl { font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; color: #64748b; }
.env-confirm-carga-badge__val { font-size: 1rem; color: #1e293b; }
.env-confirm-panel__head {
    display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem 1rem;
}
.env-confirm-panel__title { font-weight: 700; font-size: .95rem; color: #334155; }
.env-confirm-panel__title i { margin-right: .4rem; }
.env-confirm-panel__subtitle { font-size: .82rem; color: #64748b; margin-top: .15rem; }
.env-confirm-panel__body { background: linear-gradient(180deg, #fff 0%, #fafbfc 100%); }
.env-confirm-badge--trayecto { flex-shrink: 0; align-self: flex-start; }
.env-confirm-stats {
    display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); gap: .75rem;
}
@media (max-width: 575px) { .env-confirm-stats { grid-template-columns: 1fr; } }
.env-confirm-stat {
    display: flex; align-items: flex-start; gap: .75rem;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: .85rem 1rem;
}
.env-confirm-stat__icon {
    width: 38px; height: 38px; border-radius: 9px; background: #f8fafc; border: 1px solid #e2e8f0;
    display: inline-flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;
}
.env-confirm-stat--carga { border-left: 4px solid #64748b; }
.env-confirm-stat--carga .env-confirm-stat__icon { color: #475569; background: #f1f5f9; }
.env-confirm-stat--ruta { border-left: 4px solid #2c5530; }
.env-confirm-stat--ruta .env-confirm-stat__icon { color: #2c5530; background: #f0f7f1; border-color: #d1e7d4; }
.env-confirm-stat__lbl {
    display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #64748b;
}
.env-confirm-stat__val { display: block; font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-top: .1rem; }
.env-confirm-stat__val--sm { font-size: .88rem; font-weight: 600; line-height: 1.4; }
.env-confirm-horarios {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .65rem;
}
@media (max-width: 767px) { .env-confirm-horarios { grid-template-columns: 1fr; } }
.env-confirm-horarios__item {
    min-width: 0;
    border: 1px solid #e2e8f0; border-radius: 10px; padding: .75rem .85rem;
}
.env-confirm-horarios__item--fecha {
    background: #f0f7f1; border-color: #d1e7d4; border-left: 4px solid #2c5530;
}
.env-confirm-horarios__item--recogida {
    background: #eff6ff; border-color: #bfdbfe; border-left: 4px solid #3b82f6;
}
.env-confirm-horarios__item--llegada {
    background: #f8fafc; border-color: #cbd5e1; border-left: 4px solid #64748b;
}
.env-confirm-asignacion {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem;
}
@media (max-width: 767px) { .env-confirm-asignacion { grid-template-columns: 1fr; } }
.env-confirm-tile--chofer {
    border-left: 4px solid #3b82f6;
    background: #eff6ff; border-color: #bfdbfe;
}
.env-confirm-tile--vehiculo {
    border-left: 4px solid #64748b;
    background: #f8fafc; border-color: #cbd5e1;
}
.env-confirm-tile--costo {
    border-left: 4px solid #0d9488;
    background: #f0fdfa; border-color: #99f6e4;
}
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-stat--carga { border-left-color: #6d28d9; }
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-stat--carga .env-confirm-stat__icon {
    color: #6d28d9; background: #f5f3ff; border-color: #ddd6fe;
}
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-stat--ruta { border-left-color: #7c3aed; }
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-stat--ruta .env-confirm-stat__icon {
    color: #6d28d9; background: #f5f3ff; border-color: #ddd6fe;
}
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-tile--chofer { border-left-color: #6d28d9; }
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-tile--vehiculo { border-left-color: #7c3aed; }
#env-wizard-unificado[data-tema="mayorista"] .env-confirm-tile--costo { border-left-color: #9333ea; }
#env-wizard-unificado[data-tema="planta"] .env-confirm-stat--carga { border-left-color: #2c5530; }
#env-wizard-unificado[data-tema="planta"] .env-confirm-stat--carga .env-confirm-stat__icon {
    color: #2c5530; background: #f0f7f1; border-color: #d1e7d4;
}
.env-confirm-tile {
    border: 1px solid #e2e8f0; border-radius: 10px; padding: .85rem; background: #fafbfc;
}
.env-confirm-tile__label { font-size: .72rem; text-transform: uppercase; color: #64748b; letter-spacing: .03em; }
.env-confirm-tile__value { font-weight: 700; color: #1e293b; font-size: .9rem; margin-top: .2rem; }
.env-confirm-tile--origen { border-left: 4px solid #2c5530; }
.env-confirm-tile--destino { border-left: 4px solid #64748b; }
.env-confirm-tile--carga { border-left: 4px solid #0d9488; }
.env-confirm-badge {
    display: inline-block; font-size: .82rem; font-weight: 600; color: #475569;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: .4rem .75rem;
}
.env-confirm-ruta-card {
    display: flex; align-items: flex-start; gap: .75rem;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: .85rem 1rem;
}
.env-confirm-ruta-card__icon {
    width: 36px; height: 36px; border-radius: 8px; background: #f0f7f1; color: #2c5530;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.env-confirm-ruta-card__label { font-size: .72rem; text-transform: uppercase; color: #64748b; letter-spacing: .03em; }
.env-confirm-ruta-card__value { font-weight: 600; color: #1e293b; font-size: .9rem; }
.env-confirm-meta__lbl { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .03em; display: block; }
.env-confirm-meta__val { font-weight: 700; color: #1e293b; font-size: .92rem; }
.env-confirm-productos-card {
    border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fafbfc;
}
.env-confirm-productos-card__head {
    padding: .6rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    font-size: .78rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .03em;
}
.env-confirm-productos-list { padding: .75rem 1rem 1rem; }
.env-confirm-producto-item {
    display: flex; align-items: flex-start; gap: .5rem;
    color: #1e293b; font-size: .88rem; padding: .45rem 0; border-bottom: 1px solid #f1f5f9;
}
.env-confirm-producto-item:last-child { border-bottom: none; }
.env-confirm-producto-item__icon { color: #2c5530; margin-top: .15rem; flex-shrink: 0; }
.env-sugerencia-vehiculo-panel strong { color: #1e40af; }
.env-sugerencia-vehiculo-panel {
    display: flex; align-items: flex-start; gap: .75rem;
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
    padding: .75rem 1rem; margin-bottom: 1rem;
}
.env-sugerencia-vehiculo-panel__icon {
    width: 36px; height: 36px; border-radius: 8px; background: #dbeafe; color: #1d4ed8;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.env-sugerencia-vehiculo-panel__body { font-size: .86rem; color: #1e293b; line-height: 1.45; }
.env-confirm-productos-card--carga {
    border-color: #d1e7d4;
    background: #f0f7f1;
}
.env-confirm-productos-card--carga .env-confirm-productos-card__head {
    background: #e8f3ea;
    border-bottom-color: #d1e7d4;
    color: #2c5530;
}
.env-carga-ayuda--empaque {
    display: flex;
    align-items: flex-start;
    gap: .65rem;
    font-size: .84rem;
    color: #334155;
    background: linear-gradient(135deg, #f0f7f1 0%, #f8fafc 100%);
    border: 1px solid #d1e7d4;
    border-left: 4px solid #2c5530;
    border-radius: 10px;
    padding: .65rem .85rem;
    line-height: 1.45;
}
.env-carga-ayuda--empaque::before {
    content: "\f49e";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    color: #2c5530;
    flex-shrink: 0;
    margin-top: .1rem;
}
.env-carga-ayuda {
    font-size: .84rem; color: #475569; background: #f8fafc;
    border: 1px solid #e2e8f0; border-radius: 8px; padding: .55rem .75rem; line-height: 1.45;
}
.env-carga-resumen {
    font-size: .84rem; color: #1e293b; background: #f0f7f1;
    border: 1px solid #d1e7d4; border-radius: 8px; padding: .55rem .75rem;
}
.env-carga-error {
    font-size: .84rem; color: #b91c1c; background: #fef2f2;
    border: 1px solid #fecaca; border-radius: 8px; padding: .55rem .75rem;
}
.env-carga-compact {
    font-size: .82rem; color: #475569; background: #f1f5f9;
    border-radius: 8px; padding: .5rem .75rem; margin-top: .5rem;
}
.producto-recogida-row.env-producto-card {
    border: 1px solid #e2e8f0 !important; border-radius: 12px !important;
    background: #fafbfc !important; box-shadow: none !important;
}
@media (max-width: 768px) {
    .env-confirm-grid { grid-template-columns: 1fr; }
    .env-confirm-ruta-flow { grid-template-columns: 1fr; }
    .env-confirm-ruta-flecha { transform: rotate(90deg); padding: .25rem 0; }
    .env-wizard-progress .step-title { font-size: .8rem; }
}
</style>
