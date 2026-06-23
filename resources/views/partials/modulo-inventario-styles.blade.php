{{-- Estilos compartidos: módulo Inventario (AdminLTE) --}}
<style>
.modulo-inv .small-box {
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.modulo-inv .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.modulo-inv .small-box .inner {
    position: relative;
    z-index: 1;
}
.modulo-inv .small-box .inner h3 { font-size: 1.75rem; }
.modulo-inv .small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
    transition: transform 0.3s ease, color 0.3s ease;
}
.modulo-inv .small-box:hover .icon {
    transform: scale(1.05);
    color: rgba(0, 0, 0, 0.22);
}
.modulo-inv .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.modulo-inv .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.modulo-inv .small-box-yellow {
    background: linear-gradient(135deg, #f39c12, #ffc107) !important;
    color: #fff;
}
.modulo-inv .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
    color: #fff;
}
.modulo-inv .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74c3c) !important;
    color: #fff;
}
.modulo-inv .small-box-orange {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #fff;
}
.modulo-inv .small-box-teal {
    background: linear-gradient(135deg, #28a745, #34ce57) !important;
    color: #fff;
}
.modulo-inv .card-modulo-main {
    border-top: 3px solid #2c5530;
}
.modulo-inv .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}
.modulo-inv .card-modulo-main > .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a252f;
}
.modulo-inv .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-inv .view-toggle .btn {
    border-color: #ced4da;
    color: #6c757d;
}
.modulo-inv .view-toggle .btn.active {
    background: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
.modulo-inv .badge-registros {
    font-weight: normal;
}
.modulo-inv .table-modulo thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.modulo-inv .table-modulo tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.modulo-inv .table-modulo tbody tr:hover {
    background: #f8fbf8;
}
.modulo-inv .btn-actions {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    vertical-align: middle;
}
.modulo-inv .btn-actions .btn {
    padding: 0.35rem 0.55rem;
    line-height: 1.25;
    font-size: 0.85rem;
}
.modulo-inv .btn-actions form {
    display: inline-flex;
    margin: 0;
    padding: 0;
}
.modulo-inv .table-modulo tbody td.btn-actions-cell {
    padding: 0.55rem 0.4rem;
    white-space: nowrap;
}
.modulo-inv .card-footer .pagination {
    margin-bottom: 0;
}
</style>
