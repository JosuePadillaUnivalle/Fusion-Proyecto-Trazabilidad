{{-- Estilos compartidos: Reportes (AdminLTE) — wrapper .modulo-rep --}}
<style>
/* ── KPIs (small-box) ── */
.modulo-rep .small-box {
    border-radius: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: #fff !important;
    border: none;
}
.modulo-rep .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.modulo-rep .small-box .inner { position: relative; z-index: 1; padding: 12px 14px; }
.modulo-rep .small-box .inner h3 {
    font-size: 1.75rem;
    font-weight: 700;
    color: inherit !important;
}
.modulo-rep .small-box .inner p {
    color: rgba(255, 255, 255, 0.92) !important;
    margin-bottom: 0;
}
.modulo-rep .small-box .icon,
.modulo-rep .small-box .icon > i {
    position: absolute;
    right: 12px;
    top: 10px;
    font-size: 70px;
    color: rgba(255, 255, 255, 0.28) !important;
    z-index: 0;
    transition: none;
}
.modulo-rep .small-box-green {
    background: linear-gradient(135deg, #28a745, #20c997) !important;
}
.modulo-rep .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
}
.modulo-rep .small-box-yellow {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #1a252f !important;
}
.modulo-rep .small-box-yellow .inner h3 { color: #1a252f !important; }
.modulo-rep .small-box-yellow .inner p { color: rgba(26, 37, 47, 0.85) !important; }
.modulo-rep .small-box-yellow .icon,
.modulo-rep .small-box-yellow .icon > i { color: rgba(0, 0, 0, 0.12) !important; }
.modulo-rep .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #9775fa) !important;
}
.modulo-rep .small-box-orange {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #1a252f !important;
}
.modulo-rep .small-box-orange .inner h3 { color: #1a252f !important; }
.modulo-rep .small-box-orange .inner p { color: rgba(26, 37, 47, 0.85) !important; }
.modulo-rep .small-box-orange .icon,
.modulo-rep .small-box-orange .icon > i { color: rgba(0, 0, 0, 0.12) !important; }
.modulo-rep .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74c3c) !important;
}
.modulo-rep .small-box-brand {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
}
/* ── Encabezado de subcategoría ── */
.modulo-rep .rep-page-header {
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    color: #fff;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.modulo-rep .rep-page-header-inner {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    min-width: 220px;
}
.modulo-rep .rep-page-header-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.modulo-rep .rep-page-header-text h2 {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 0.2rem;
    color: #fff;
}
.modulo-rep .rep-page-header-text p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.92;
    color: rgba(255, 255, 255, 0.95);
}
.modulo-rep .rep-page-header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.modulo-rep .rep-page-header-actions .btn-outline-light {
    border-color: rgba(255, 255, 255, 0.65);
    color: #fff;
}
.modulo-rep .rep-page-header-actions .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
}
.modulo-rep .rep-page-header-actions .btn-light {
    font-weight: 600;
}
.modulo-rep .rep-theme-success { background: linear-gradient(135deg, #28a745, #20c997); }
.modulo-rep .rep-theme-primary { background: linear-gradient(135deg, #1890ff, #40a9ff); }
.modulo-rep .rep-theme-warning { background: linear-gradient(135deg, #fd7e14, #ffc107); color: #1a252f; }
.modulo-rep .rep-theme-warning .rep-page-header-text h2,
.modulo-rep .rep-theme-warning .rep-page-header-text p { color: #1a252f; }
.modulo-rep .rep-theme-warning .rep-page-header-actions .btn-outline-light {
    border-color: rgba(0, 0, 0, 0.25);
    color: #1a252f;
}
.modulo-rep .rep-theme-info { background: linear-gradient(135deg, #17a2b8, #6dd5ed); }
.modulo-rep .rep-theme-purple { background: linear-gradient(135deg, #6f42c1, #9775fa); }
.modulo-rep .rep-theme-brand { background: linear-gradient(135deg, #2c5530, #4a7c59); }

.modulo-rep .guia-reporte {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #495057;
}
.modulo-rep .guia-reporte strong { color: #2c5530; }

/* ── Hub / tarjetas de acceso ── */
.modulo-rep .catalog-hub-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}
.modulo-rep .catalog-hub-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}
.modulo-rep .catalog-hub-icon {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #fff !important;
    margin: 0 auto 12px;
}
.modulo-rep .bg-rep-ventas { background: linear-gradient(135deg, #28a745, #20c997); }
.modulo-rep .bg-rep-inventario { background: linear-gradient(135deg, #1890ff, #40a9ff); }
.modulo-rep .bg-rep-produccion { background: linear-gradient(135deg, #fa8c16, #ffc53d); }
.modulo-rep .bg-rep-climatico { background: linear-gradient(135deg, #17a2b8, #6dd5ed); }
.modulo-rep .bg-rep-actividades { background: linear-gradient(135deg, #6f42c1, #9775fa); }
.modulo-rep .bg-rep-export { background: linear-gradient(135deg, #2c5530, #4a7c59); }

.modulo-rep .section-title {
    font-weight: 700;
    color: #1a252f;
    margin-bottom: 1.25rem;
    padding-bottom: 8px;
    border-bottom: 3px solid #2c5530;
    display: inline-block;
}
.modulo-rep .section-title i { color: #2c5530; margin-right: 8px; }

/* ── Panel de filtros (como catálogos) ── */
.modulo-rep .card-modulo-main {
    border-radius: 10px;
    border-top-width: 3px !important;
    border-top-style: solid !important;
    overflow: hidden;
}
.modulo-rep .card-modulo-main.card-success { border-top-color: #28a745 !important; }
.modulo-rep .card-modulo-main.card-primary { border-top-color: #007bff !important; }
.modulo-rep .card-modulo-main.card-warning { border-top-color: #fd7e14 !important; }
.modulo-rep .card-modulo-main.card-info { border-top-color: #17a2b8 !important; }
.modulo-rep .card-modulo-main.rep-filtros-purple { border-top-color: #6f42c1 !important; }
.modulo-rep .btn-purple {
    background: #6f42c1;
    border-color: #6f42c1;
    color: #fff;
}
.modulo-rep .btn-purple:hover {
    background: #5a32a3;
    border-color: #5a32a3;
    color: #fff;
}
.modulo-rep .text-purple { color: #6f42c1 !important; }
.modulo-rep .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}
.modulo-rep .card-modulo-main > .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a252f;
}
.modulo-rep .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}

/* ── Tarjetas de contenido ── */
.modulo-rep .card.card-outline {
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
}
.modulo-rep .card.card-outline > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
}
.modulo-rep .card.card-outline > .card-header .card-title {
    font-weight: 600;
    font-size: 1rem;
}
.modulo-rep .card-chart > .card-header { background: #fff; border-bottom: 1px solid #dee2e6; }

/* ── Tablas de detalle ── */
.modulo-rep .table-modulo thead th,
.modulo-rep .card .table thead.thead-light th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.modulo-rep .table-modulo tbody td,
.modulo-rep .card .table tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.modulo-rep .table-modulo tbody tr:hover,
.modulo-rep .card .table tbody tr:hover {
    background: #f8fbf8;
}

/* ── Info-box (inventario) ── */
.modulo-rep .info-box {
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    min-height: 88px;
}
.modulo-rep .info-box-icon {
    border-radius: 10px 0 0 10px;
    width: 80px;
    font-size: 1.6rem;
}
.modulo-rep .info-box-content { padding: 0.6rem 0.75rem; }
.modulo-rep .info-box-text { font-size: 0.85rem; font-weight: 600; }
.modulo-rep .info-box-number { font-size: 1rem; font-weight: 700; }

/* ── Gráficos y widgets ── */
.modulo-rep .chart-wrap { position: relative; height: 280px; }
.modulo-rep .chart-wrap-sm { position: relative; height: 190px; }
.modulo-rep .widget-list .product-title { font-weight: 600; }
.modulo-rep .widget-list .product-description { font-size: 0.85rem; color: #6c757d; }
.modulo-rep .products-list .product-img {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modulo-rep .legend-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
}

/* ── Clima ── */
.modulo-rep .weather-panel {
    background: linear-gradient(135deg, #17a2b8, #6dd5ed);
    border-radius: 12px;
    color: #fff;
    padding: 1.25rem;
    box-shadow: 0 4px 16px rgba(23, 162, 184, 0.25);
}
.modulo-rep .weather-panel .temp-main { font-size: 2.8rem; font-weight: 300; line-height: 1; }
.modulo-rep .weather-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-top: 12px;
}
.modulo-rep .weather-detail-grid .item {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 8px;
    text-align: center;
}
.modulo-rep .forecast-mini {
    background: #f8f9fc;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    border: 1px solid #e9ecef;
    transition: transform 0.15s ease;
}
.modulo-rep .forecast-mini:hover { transform: translateY(-2px); }

/* ── Badges de filtros activos ── */
.modulo-rep .filtros-activos .badge {
    font-weight: 500;
    padding: 0.4em 0.65em;
}
</style>
