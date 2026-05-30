{{-- Estilos compartidos: módulo Producción (AdminLTE) --}}
<style>
.modulo-prod .small-box {
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: #fff;
}
.modulo-prod .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.modulo-prod .small-box .inner { position: relative; z-index: 1; }
.modulo-prod .small-box .inner h3 { font-size: 1.75rem; }
.modulo-prod .small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
}
.modulo-prod .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
}
.modulo-prod .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
}
.modulo-prod .small-box-yellow {
    background: linear-gradient(135deg, #f39c12, #ffc107) !important;
}
.modulo-prod .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
}
.modulo-prod .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74c3c) !important;
}
.modulo-prod .small-box-teal {
    background: linear-gradient(135deg, #28a745, #34ce57) !important;
}
.modulo-prod .card-modulo-main {
    border-top: 3px solid #2c5530;
}
.modulo-prod .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}
.modulo-prod .card-modulo-main > .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a252f;
}
.modulo-prod .card-form-modulo {
    border-top: 3px solid #4a7c59;
}
.modulo-prod .card-form-modulo > .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border: none;
}
.modulo-prod .card-form-modulo > .card-header .card-title {
    font-size: 0.95rem;
    font-weight: 600;
}
.modulo-prod .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-prod .view-toggle .btn {
    border-color: #ced4da;
    color: #6c757d;
}
.modulo-prod .view-toggle .btn.active {
    background: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
.modulo-prod .badge-registros { font-weight: normal; }
.modulo-prod .table-modulo thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.modulo-prod .table-modulo tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.modulo-prod .table-modulo tbody tr:hover {
    background: #f8fbf8;
}
.modulo-prod .btn-actions .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
}
.modulo-prod .item-card-prod {
    background: #fff;
    border-radius: 8px;
    border-left: 4px solid #2c5530;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    margin-bottom: 1rem;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.modulo-prod .item-card-prod:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.modulo-prod .item-card-prod .item-header {
    padding: 0.85rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f3f4;
}
.modulo-prod .item-card-prod .item-body { padding: 0.85rem 1.25rem; }
.modulo-prod .item-card-prod .item-footer {
    padding: 0.65rem 1.25rem;
    background: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modulo-prod .cantidad-destacada {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c5530;
}
.modulo-prod .info-label {
    display: block;
    font-size: 0.7rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 2px;
}
/* Clima */
.modulo-prod .weather-widget {
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(9, 132, 227, 0.25);
    margin-bottom: 1rem;
}
.modulo-prod .weather-widget.night {
    background: linear-gradient(135deg, #2d3436, #636e72);
}
.modulo-prod .weather-temp {
    font-size: 3.5rem;
    font-weight: 300;
    line-height: 1;
}
.modulo-prod .weather-icon img {
    width: 100px;
    height: 100px;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
}
.modulo-prod .weather-desc {
    font-size: 1.1rem;
    text-transform: capitalize;
    opacity: 0.95;
}
.modulo-prod .weather-details {
    display: flex;
    justify-content: space-around;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}
.modulo-prod .weather-detail-item { text-align: center; }
.modulo-prod .weather-detail-item .value {
    font-size: 1.1rem;
    font-weight: 600;
}
.modulo-prod .weather-detail-item .label {
    font-size: 0.75rem;
    opacity: 0.85;
}
.modulo-prod .sun-card {
    background: linear-gradient(135deg, #f39c12, #f1c40f);
    color: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.modulo-prod .sun-times {
    display: flex;
    justify-content: space-around;
    margin-top: 0.75rem;
}
.modulo-prod .sun-time { text-align: center; }
.modulo-prod .sun-time i { font-size: 1.75rem; margin-bottom: 0.35rem; }
.modulo-prod .forecast-day {
    text-align: center;
    padding: 0.75rem 0.5rem;
    border-radius: 8px;
    transition: background 0.2s;
}
.modulo-prod .forecast-day:hover { background: #f8f9fa; }
.modulo-prod .forecast-day .day-temp {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c5530;
}
.modulo-prod .historial-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #f1f3f4;
    transition: background 0.2s;
}
.modulo-prod .historial-item:hover { background: #f8fbf8; }
.modulo-prod .historial-item:last-child { border-bottom: none; }
.modulo-prod .dato-badge {
    padding: 0.25rem 0.65rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
.modulo-prod .dato-badge.temp { background: #fff3cd; color: #856404; }
.modulo-prod .dato-badge.hum { background: #d1ecf1; color: #0c5460; }
</style>
