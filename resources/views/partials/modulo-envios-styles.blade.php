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
.modulo-env .veh-estado {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .65rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
}
.modulo-env .veh-estado::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.modulo-env .veh-estado--operativo { background: #e8f5e9; color: #1b5e20; }
.modulo-env .veh-estado--operativo::before { background: #28a745; }
.modulo-env .veh-estado--mantenimiento { background: #fff8e1; color: #856404; }
.modulo-env .veh-estado--mantenimiento::before { background: #ffc107; }
.modulo-env .veh-estado--ruta { background: #e7f3ff; color: #0c5460; }
.modulo-env .veh-estado--ruta::before { background: #17a2b8; animation: veh-estado-pulse 1.4s ease-in-out infinite; }
@keyframes veh-estado-pulse { 0%,100%{opacity:1} 50%{opacity:.35} }
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
.modulo-env .veh-caja-3d,
.veh-caja-3d {
    min-height: 280px;
    height: 280px;
    width: 100%;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #e8eef4;
}
.veh-caja-3d canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
    cursor: grab;
}
.veh-caja-3d canvas:active { cursor: grabbing; }
.veh-caja-3d__swatch {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 3px;
    vertical-align: -2px;
    margin-right: 4px;
}
.veh-caja-3d__swatch--cabina { background: #3b6ea8; }
.veh-caja-3d__swatch--carga { background: rgba(72, 187, 120, 0.45); border: 1px solid #059669; }
.veh-caja-3d__swatch--fill { background: #f59e0b; }
</style>

@once
@push('scripts')
<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
    }
}
</script>
@endpush
@endonce
