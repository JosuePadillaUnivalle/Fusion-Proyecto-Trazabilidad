{{-- Estilos compartidos: módulo Envíos (AdminLTE) --}}
<style>
.modulo-env .small-box {
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}
.modulo-env .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.modulo-env .small-box.active-filter {
    outline: 3px solid rgba(255, 255, 255, 0.85);
    outline-offset: -3px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
}
.modulo-env .small-box .inner { position: relative; z-index: 1; }
.modulo-env .small-box .inner h3 { font-size: 1.75rem; }
.modulo-env .small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
}
.modulo-env .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.modulo-env .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.modulo-env .small-box-yellow {
    background: linear-gradient(135deg, #f39c12, #ffc107) !important;
    color: #fff;
}
.modulo-env .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
    color: #fff;
}
.modulo-env .small-box-orange {
    background: linear-gradient(135deg, #fd7e14, #e67e22) !important;
    color: #fff;
}
.modulo-env .small-box-teal {
    background: linear-gradient(135deg, #007bff, #17a2b8) !important;
    color: #fff;
}
.modulo-env .card-modulo-main {
    border-top: 3px solid #2c5530;
}
.modulo-env .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
}
.modulo-env .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-env .filtros-panel label {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}
.modulo-env .contador-filtro {
    font-size: 0.85rem;
    color: #6c757d;
}
.modulo-env .envio-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 8px;
}
.modulo-env .envio-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
}
.modulo-env .envio-route {
    border-left: 3px solid #2c5530;
    padding-left: 1rem;
}
.modulo-env .text-truncate-2lines {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4em;
    max-height: 2.8em;
}
/* Aviso de conexión: inline en la página, nunca flotante */
.modulo-env .env-conexion-aviso {
    font-size: 0.8rem;
    padding: 4px 10px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.modulo-env .env-conexion-aviso.is-offline {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffc107;
}
.modulo-env .env-conexion-aviso.is-syncing {
    background: #e8f4fd;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
.modulo-env .offline-banner {
    background: #fff8e6;
    border-left: 4px solid #ffc107;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 1rem;
}
.modulo-env .envio-local {
    border: 2px dashed #ffc107 !important;
    background: #fffdf5;
}
/* Crear envío — wizard */
.modulo-env .wizard-step { display: none; }
.modulo-env .wizard-step.active { display: block; }
.modulo-env .wizard-progress .step-item { text-align: center; }
.modulo-env .wizard-progress .step-badge {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
}
.modulo-env .wizard-progress .step-item.active .step-badge {
    background: #2c5530 !important;
    color: #fff;
}
.modulo-env .wizard-progress .step-item.done .step-badge {
    background: #28a745 !important;
    color: #fff;
}
.modulo-env .wizard-progress .step-item.pending .step-badge {
    background: #e9ecef !important;
    color: #6c757d;
}
.modulo-env #map { height: 100%; min-height: 420px; }
.modulo-env .readonly-input {
    background-color: #f4f6f9;
    cursor: not-allowed;
}
.modulo-env .equal-height-row {
    display: flex;
    flex-wrap: wrap;
}
.modulo-env .equal-height-row > [class*='col-'] {
    display: flex;
    flex-direction: column;
}
.modulo-env .equal-height-row .card { flex: 1; }
.modulo-env .cola-pendientes-card {
    background: #fff8e6;
    border-left: 4px solid #ffc107;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 1rem;
}
.modulo-env .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74c3c) !important;
    color: #fff;
}
.modulo-env .small-box-indigo {
    background: linear-gradient(135deg, #3c4b64, #5a6a85) !important;
    color: #fff;
}
.modulo-env .small-box:not(.filter-box) { cursor: default; }
.modulo-env .small-box:not(.filter-box):hover { transform: none; }
.modulo-env .env-page-intro {
    border-left: 4px solid #2c5530;
    background: #f8faf8;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 1rem;
}
.modulo-env .table-modulo thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #2c5530;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.modulo-env .table-modulo tbody tr:hover {
    background: #f8faf8;
}
.modulo-env .badge-estado {
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.25rem;
    padding: 0.35em 0.65em;
}
.modulo-env .fila-estado-toggle {
    cursor: pointer;
    user-select: none;
}
.modulo-env .fila-estado-toggle:hover {
    background: #f0f7f1 !important;
}
.modulo-env .fila-estado-toggle .chevron-estado {
    transition: transform 0.2s ease;
    width: 1rem;
    text-align: center;
    color: #2c5530;
}
.modulo-env .fila-estado-toggle[aria-expanded="true"] .chevron-estado {
    transform: rotate(90deg);
}
.modulo-env .detalle-estado-envios {
    background: #f8faf8;
    font-size: 0.875rem;
}
.modulo-env .crud-acciones {
    gap: 6px;
}
.modulo-env .crud-acciones--inline {
    flex-wrap: nowrap !important;
}
.modulo-env .crud-acciones .btn {
    min-width: 2rem;
}
.modulo-env .veh-det-toolbar__acciones {
    gap: 8px;
}
.modulo-env .veh-det-hero {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(44, 85, 48, 0.15);
}
.modulo-env .veh-det-hero__body {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.35rem 1.5rem;
    background: linear-gradient(135deg, #2c5530 0%, #4a7c59 100%);
    color: #fff;
}
.modulo-env .veh-det-hero__icon {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    background: rgba(255,255,255,.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}
.modulo-env .veh-det-hero__placa {
    font-size: 1.65rem;
    font-weight: 800;
    letter-spacing: .04em;
}
.modulo-env .veh-det-panel {
    border-radius: 10px;
    border: 1px solid #e9ecef;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.modulo-env .veh-det-panel > .card-header {
    background: #f8faf8;
    font-weight: 600;
    font-size: .9rem;
    border-bottom: 1px solid #e9ecef;
}
.modulo-env .veh-det-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.25rem;
}
.modulo-env .veh-det-item__label {
    display: block;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: .2rem;
}
.modulo-env .veh-det-item__value {
    font-weight: 600;
    color: #1a252f;
}
.modulo-env .veh-det-capacidad {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
}
.modulo-env .veh-det-capacidad__chip {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .65rem .9rem;
    background: #f0f7f1;
    border: 1px solid #d4e8d6;
    border-radius: 10px;
    min-width: 120px;
}
.modulo-env .veh-det-capacidad__chip i {
    color: #2c5530;
    font-size: 1.1rem;
}
.modulo-env .veh-det-capacidad__chip strong {
    display: block;
    font-size: 1.15rem;
    line-height: 1.1;
}
.modulo-env .veh-det-capacidad__chip span {
    font-size: .75rem;
    color: #6c757d;
}
.modulo-env .tipos-vehiculo-catalogo .card-header h6 {
    font-size: .9rem;
}
@media (max-width: 575.98px) {
    .modulo-env .veh-det-grid { grid-template-columns: 1fr; }
}
.modulo-env .veh-caja-3d {
    width: 100%;
    min-height: 260px;
    border-radius: 8px;
    overflow: hidden;
    background: linear-gradient(180deg, #e8eef4 0%, #f4f6f9 100%);
    border: 1px solid #e2e8f0;
}
.modulo-env .veh-caja-3d canvas {
    display: block;
    width: 100% !important;
}
.modulo-env .veh-caja-3d__leyenda-item {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
.modulo-env .veh-caja-3d__swatch {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
    vertical-align: middle;
}
.modulo-env .veh-caja-3d__swatch--cabina { background: #5a6a7a; }
.modulo-env .veh-caja-3d__swatch--carga { background: rgba(45, 106, 79, 0.55); border: 1px solid #1b4332; }
.modulo-env .veh-empaque-cap th,
.modulo-env .veh-empaque-cap td {
    vertical-align: middle;
}
.modulo-env .veh-empaque-cap .badge-lg {
    font-size: .95rem;
    min-width: 2.5rem;
}
.modulo-env .veh-limite-badge {
    display: inline-block;
    font-size: .75rem;
    font-weight: 600;
    padding: .2rem .55rem;
    border-radius: 4px;
    white-space: nowrap;
}
.modulo-env .veh-limite-badge--peso {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffc107;
}
.modulo-env .veh-limite-badge--volumen {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #17a2b8;
}

/* Badge transporte — mismo estilo plano que categoría */
.modulo-env .veh-tt-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    white-space: nowrap;
    vertical-align: middle;
}
.modulo-env .veh-tt-badge--sm { font-size: .7rem; padding: 0.3em 0.55em; }
.modulo-env .veh-tt-badge--md { font-size: .75rem; }
.modulo-env .veh-tt-badge__icon { font-size: .8em; opacity: .95; }
.modulo-env .veh-tt-badge.is-inactive { opacity: .5; }

/* Sección catálogo — pie de la tarjeta de flota */
.modulo-env .veh-catalogo-seccion {
    margin-top: 0;
}
.modulo-env .card-modulo-main .veh-catalogo-seccion__panel {
    border: none;
    border-radius: 0;
    box-shadow: none;
}
.modulo-env .veh-catalogo-seccion__panel {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    background: #fff;
    overflow: hidden;
}
.modulo-env .card-modulo-main .veh-catalogo-seccion__toggle {
    border-top: 1px solid #dee2e6;
}
.modulo-env .veh-catalogo-seccion__toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    text-align: left;
    transition: background .15s ease;
}
.modulo-env .veh-catalogo-seccion__toggle:hover,
.modulo-env .veh-catalogo-seccion__toggle:focus {
    background: #eef1f3;
    outline: none;
}
.modulo-env .veh-catalogo-seccion__toggle-icon {
    width: 32px;
    height: 32px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    background: #fff;
    color: #495057;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.modulo-env .veh-catalogo-seccion__toggle-text {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}
.modulo-env .veh-catalogo-seccion__toggle-text strong {
    font-size: 0.875rem;
    font-weight: 600;
    color: #343a40;
}
.modulo-env .veh-catalogo-seccion__toggle-text small {
    font-size: 0.78rem;
    color: #6c757d;
    margin-top: 0.1rem;
}
.modulo-env .veh-catalogo-seccion__chev {
    width: 24px;
    height: 24px;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    transition: transform .2s ease;
}
.modulo-env .veh-catalogo-seccion__toggle:not(.collapsed) .veh-catalogo-seccion__chev {
    transform: rotate(180deg);
}
.modulo-env .veh-catalogo-seccion__body {
    border-top: 1px solid #dee2e6;
    background: #fff;
}

/* Panel equipamiento transporte (detalle) */
.modulo-env .veh-equipamiento__hero {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 14px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
}
.modulo-env .veh-equipamiento__hero--general { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-color: #e2e8f0; }
.modulo-env .veh-equipamiento__hero--iso { background: linear-gradient(135deg, #fff7ed, #ffedd5); border-color: #fed7aa; }
.modulo-env .veh-equipamiento__hero--frio { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #bfdbfe; }
.modulo-env .veh-equipamiento__hero--multi { background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-color: #ddd6fe; }
.modulo-env .veh-equipamiento__hero-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
    background: rgba(255,255,255,.65);
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.modulo-env .veh-equipamiento__hero--general .veh-equipamiento__hero-icon { color: #475569; }
.modulo-env .veh-equipamiento__hero--iso .veh-equipamiento__hero-icon { color: #c2410c; }
.modulo-env .veh-equipamiento__hero--frio .veh-equipamiento__hero-icon { color: #1d4ed8; }
.modulo-env .veh-equipamiento__hero--multi .veh-equipamiento__hero-icon { color: #6d28d9; }
.modulo-env .veh-equipamiento__hero-label {
    display: block;
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 700;
    color: #64748b;
    margin-bottom: .2rem;
}
.modulo-env .veh-equipamiento__hero-title {
    margin: 0 0 .25rem;
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
}
.modulo-env .veh-equipamiento__hero-hint {
    font-size: .8rem;
    color: #64748b;
}
.modulo-env .veh-equipamiento__empty {
    text-align: center;
    padding: 1.5rem 1rem;
    color: #94a3b8;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 1rem;
}
.modulo-env .veh-equipamiento__empty i { font-size: 1.5rem; margin-bottom: .5rem; display: block; }
.modulo-env .veh-equipamiento__modos-title {
    display: block;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 700;
    color: #94a3b8;
    margin-bottom: .55rem;
}
.modulo-env .veh-equipamiento__modos-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .5rem;
}
@media (max-width: 575px) {
    .modulo-env .veh-equipamiento__modos-grid { grid-template-columns: 1fr; }
}
.modulo-env .veh-equipamiento__modo {
    position: relative;
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .55rem .65rem;
    border-radius: 10px;
    border: 1px solid #e8edf2;
    background: #fafbfc;
    opacity: .55;
    transition: opacity .15s ease, border-color .15s ease, background .15s ease;
}
.modulo-env .veh-equipamiento__modo.is-active {
    opacity: 1;
    border-width: 2px;
    background: #fff;
}
.modulo-env .veh-equipamiento__modo--general.is-active { border-color: #94a3b8; }
.modulo-env .veh-equipamiento__modo--iso.is-active { border-color: #fb923c; }
.modulo-env .veh-equipamiento__modo--frio.is-active { border-color: #3b82f6; }
.modulo-env .veh-equipamiento__modo--multi.is-active { border-color: #8b5cf6; }
.modulo-env .veh-equipamiento__modo-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
    background: #f1f5f9;
    color: #64748b;
}
.modulo-env .veh-equipamiento__modo--iso .veh-equipamiento__modo-icon { background: #ffedd5; color: #c2410c; }
.modulo-env .veh-equipamiento__modo--frio .veh-equipamiento__modo-icon { background: #dbeafe; color: #1d4ed8; }
.modulo-env .veh-equipamiento__modo--multi .veh-equipamiento__modo-icon { background: #ede9fe; color: #6d28d9; }
.modulo-env .veh-equipamiento__modo-name {
    font-size: .78rem;
    font-weight: 700;
    color: #334155;
}
.modulo-env .veh-equipamiento__modo-check {
    margin-left: auto;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #2c5530;
    color: #fff;
    font-size: .55rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.modulo-env .veh-equipamiento__stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .65rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
}
.modulo-env .veh-equipamiento__stat {
    background: #f8fafc;
    border: 1px solid #eef2f6;
    border-radius: 10px;
    padding: .6rem .7rem;
}
.modulo-env .veh-equipamiento__stat-label {
    display: block;
    font-size: .68rem;
    color: #94a3b8;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: .15rem;
}
.modulo-env .veh-equipamiento__stat strong {
    font-size: .95rem;
    color: #1e293b;
}
.modulo-env .veh-equipamiento__edit {
    margin-top: .75rem;
    color: #64748b;
}
.modulo-env .veh-equipamiento__edit a { font-weight: 600; }
.modulo-env .veh-det-panel--equipamiento .card-body { padding: 1.1rem 1.15rem; }
</style>
