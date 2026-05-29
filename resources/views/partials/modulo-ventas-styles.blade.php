{{-- Estilos compartidos: módulo Ventas (AdminLTE) — wrapper .modulo-ven --}}
<style>
.modulo-ven .small-box {
    border-radius: 10px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: #fff !important;
    border: none;
}
.modulo-ven .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.modulo-ven .small-box .inner { position: relative; z-index: 1; padding: 12px 14px; }
.modulo-ven .small-box .inner h3 { font-size: 1.75rem; font-weight: 700; color: inherit !important; }
.modulo-ven .small-box .inner p { color: rgba(255, 255, 255, 0.92) !important; margin-bottom: 0; }
.modulo-ven .small-box .icon,
.modulo-ven .small-box .icon > i {
    position: absolute;
    right: 12px;
    top: 10px;
    font-size: 70px;
    color: rgba(255, 255, 255, 0.28) !important;
    z-index: 0;
}
.modulo-ven .small-box-green { background: linear-gradient(135deg, #28a745, #20c997) !important; }
.modulo-ven .small-box-blue { background: linear-gradient(135deg, #17a2b8, #20c997) !important; }
.modulo-ven .small-box-yellow {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #1a252f !important;
}
.modulo-ven .small-box-yellow .inner h3 { color: #1a252f !important; }
.modulo-ven .small-box-yellow .inner p { color: rgba(26, 37, 47, 0.85) !important; }
.modulo-ven .small-box-yellow .icon,
.modulo-ven .small-box-yellow .icon > i { color: rgba(0, 0, 0, 0.12) !important; }
.modulo-ven .small-box-red { background: linear-gradient(135deg, #dc3545, #e74c3c) !important; }
.modulo-ven .small-box-brand { background: linear-gradient(135deg, #2c5530, #4a7c59) !important; }
.modulo-ven .small-box-footer {
    background: rgba(0, 0, 0, 0.15) !important;
    color: rgba(255, 255, 255, 0.95) !important;
    position: relative;
    z-index: 1;
    display: block;
}

.modulo-ven .ven-page-header {
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    color: #fff;
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.25);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.modulo-ven .ven-page-header.edit { background: linear-gradient(135deg, #856404, #ffc107); color: #1a252f; }
.modulo-ven .ven-page-header.edit .ven-page-header-text h2,
.modulo-ven .ven-page-header.edit .ven-page-header-text p { color: #1a252f; }
.modulo-ven .ven-page-header.show { background: linear-gradient(135deg, #2c5530, #4a7c59); }
.modulo-ven .ven-page-header-inner { display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 220px; }
.modulo-ven .ven-page-header-icon {
    width: 56px; height: 56px; border-radius: 14px;
    background: rgba(255, 255, 255, 0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.modulo-ven .ven-page-header-text h2 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.2rem; color: #fff; }
.modulo-ven .ven-page-header-text p { margin: 0; font-size: 0.9rem; opacity: 0.92; color: rgba(255,255,255,0.95); }
.modulo-ven .ven-total-badge {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 0.65rem 1.25rem;
    text-align: center;
}
.modulo-ven .ven-total-badge .amount { font-size: 1.75rem; font-weight: 700; line-height: 1.1; }
.modulo-ven .ven-total-badge .label { font-size: 0.8rem; opacity: 0.9; }

.modulo-ven .guia-venta {
    background: #f8fbf8;
    border-left: 3px solid #28a745;
    border-radius: 0 8px 8px 0;
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #495057;
}
.modulo-ven .guia-venta strong { color: #2c5530; }
.modulo-ven .guia-pasos { list-style: none; padding: 0; margin: 0; }
.modulo-ven .guia-pasos li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 0.55rem 0;
    border-bottom: 1px dashed #e9ecef;
    font-size: 0.88rem;
}
.modulo-ven .guia-pasos li:last-child { border-bottom: none; }
.modulo-ven .guia-pasos .paso-num {
    width: 26px; height: 26px; border-radius: 50%;
    background: #28a745; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
}
.modulo-ven .guia-pasos .paso-num.done { background: #2c5530; }
.modulo-ven .guia-pasos .paso-num.active { background: #ffc107; color: #1a252f; }

.modulo-ven .card-modulo-main { border-top: 3px solid #28a745; border-radius: 10px; overflow: hidden; }
.modulo-ven .card-modulo-main > .card-header {
    background: #fff; border-bottom: 1px solid #dee2e6; padding: 0.75rem 1rem;
}
.modulo-ven .card-modulo-main > .card-header .card-title {
    font-size: 1rem; font-weight: 600; color: #1a252f;
}
.modulo-ven .card-form-modulo { border-top: 3px solid #4a7c59; border-radius: 10px; overflow: hidden; }
.modulo-ven .card-form-modulo > .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff; border: none;
}
.modulo-ven .card-form-modulo > .card-header .card-title { font-size: 1rem; font-weight: 600; }
.modulo-ven .card-form-modulo.card-edit > .card-header {
    background: linear-gradient(135deg, #856404, #ffc107);
    color: #1a252f;
}
.modulo-ven .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-ven .view-toggle .btn { border-color: #ced4da; color: #6c757d; }
.modulo-ven .view-toggle .btn.active { background: #28a745; border-color: #28a745; color: #fff; }
.modulo-ven .badge-registros { font-weight: normal; }

.modulo-ven .item-card-ven {
    background: #fff;
    border-radius: 10px;
    border-left: 4px solid #28a745;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    margin-bottom: 1rem;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.modulo-ven .item-card-ven:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 18px rgba(0, 0, 0, 0.12);
}
.modulo-ven .item-card-ven .item-header {
    padding: 0.9rem 1.25rem;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid #f1f3f4;
}
.modulo-ven .item-card-ven .item-header h5 { margin: 0; font-weight: 600; font-size: 1rem; }
.modulo-ven .item-card-ven .item-total { font-size: 1.25rem; font-weight: 700; color: #28a745; }
.modulo-ven .item-card-ven .item-body { padding: 0.9rem 1.25rem; }
.modulo-ven .item-card-ven .item-footer {
    padding: 0.65rem 1.25rem;
    background: #f8f9fa;
    display: flex; justify-content: space-between; align-items: center;
}
.modulo-ven .info-label {
    display: block; font-size: 0.7rem; color: #6c757d;
    text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 2px;
}
.modulo-ven .info-grid { display: flex; flex-wrap: wrap; gap: 1rem; }
.modulo-ven .info-grid > div { flex: 1; min-width: 120px; }

.modulo-ven .table-modulo thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.modulo-ven .table-modulo tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.modulo-ven .table-modulo tbody tr:hover { background: #f8fbf8; }
.modulo-ven .btn-actions .btn { padding: 0.2rem 0.45rem; line-height: 1.2; }

.modulo-ven .panel-lateral {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    margin-bottom: 1rem;
    overflow: hidden;
}
.modulo-ven .panel-lateral > .panel-header {
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.95rem;
    border-bottom: 1px solid #f1f3f4;
    color: #2c5530;
}
.modulo-ven .panel-lateral > .panel-body { padding: 1rem; }
.modulo-ven .total-estimado-box {
    background: linear-gradient(135deg, #e8f5e9, #f1f8f2);
    border: 2px solid #28a745;
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
}
.modulo-ven .total-estimado-box .amount {
    font-size: 2rem;
    font-weight: 700;
    color: #28a745;
    line-height: 1.1;
}
.modulo-ven .producto-preview {
    background: #f8fbf8;
    border: 1px solid #d4edda;
    border-radius: 8px;
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
}
.modulo-ven .flujo-venta .flujo-item {
    display: flex; align-items: center; gap: 8px;
    padding: 0.4rem 0; font-size: 0.88rem;
}
.modulo-ven .flujo-venta .flujo-item.active { font-weight: 700; color: #28a745; }
.modulo-ven .detail-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    padding: 1.25rem;
    margin-bottom: 1rem;
    height: calc(100% - 1rem);
}
.modulo-ven .detail-card h5 {
    color: #2c5530;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f1f3f4;
}
.modulo-ven .resumen-linea {
    display: flex; justify-content: space-between;
    padding: 0.5rem 0; border-bottom: 1px solid #f1f3f4;
}
.modulo-ven .resumen-linea:last-child { border-bottom: none; font-weight: 700; font-size: 1.05rem; }
.modulo-ven .filtros-activos .badge { font-weight: 500; }
</style>
