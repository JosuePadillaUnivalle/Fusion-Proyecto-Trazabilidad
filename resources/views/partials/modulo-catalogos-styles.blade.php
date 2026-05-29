{{-- Estilos compartidos: Catálogos (AdminLTE) --}}
<style>
.modulo-cat .small-box {
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: #fff;
}
.modulo-cat .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.modulo-cat .small-box .inner { position: relative; z-index: 1; }
.modulo-cat .small-box .inner h3 { font-size: 1.75rem; }
.modulo-cat .small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
}
.modulo-cat .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
}
.modulo-cat .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
}
.modulo-cat .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
}
.modulo-cat .small-box-orange {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
}
.modulo-cat .small-box-red {
    background: linear-gradient(135deg, #c0392b, #e74c3c) !important;
}
.modulo-cat .small-box-footer {
    background: rgba(0, 0, 0, 0.12);
    color: rgba(255, 255, 255, 0.9);
    position: relative;
    z-index: 1;
}
.modulo-cat .card-modulo-main {
    border-top: 3px solid #2c5530;
}
.modulo-cat .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}
.modulo-cat .card-modulo-main > .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a252f;
}
.modulo-cat .card-form-modulo {
    border-top: 3px solid #4a7c59;
}
.modulo-cat .card-form-modulo > .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border: none;
}
.modulo-cat .card-form-modulo.card-edit > .card-header {
    background: linear-gradient(135deg, #856404, #ffc107);
    color: #1a252f;
}
.modulo-cat .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-cat .badge-registros { font-weight: normal; }
.modulo-cat .table-modulo thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.modulo-cat .table-modulo tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.modulo-cat .table-modulo tbody tr:hover {
    background: #f8fbf8;
}
.modulo-cat .btn-actions .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
}
.modulo-cat .guia-campo {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.65rem 0.85rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
    color: #495057;
}
.modulo-cat .guia-campo strong { color: #2c5530; }
.modulo-cat .catalog-hub-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}
.modulo-cat .catalog-hub-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}
.modulo-cat .catalog-hub-icon {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #fff;
    margin: 0 auto 12px;
}
</style>
