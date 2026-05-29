{{-- Estilos compartidos: Gestión de usuarios (AdminLTE) --}}
<style>
.modulo-usu .small-box {
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: #fff;
}
.modulo-usu .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.modulo-usu .small-box .inner { position: relative; z-index: 1; }
.modulo-usu .small-box .inner h3 { font-size: 1.75rem; }
.modulo-usu .small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
}
.modulo-usu .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
}
.modulo-usu .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
}
.modulo-usu .small-box-yellow {
    background: linear-gradient(135deg, #f39c12, #ffc107) !important;
}
.modulo-usu .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
}
.modulo-usu .small-box-footer {
    background: rgba(0, 0, 0, 0.12);
    color: rgba(255, 255, 255, 0.9);
    position: relative;
    z-index: 1;
}
.modulo-usu .card-modulo-main {
    border-top: 3px solid #2c5530;
}
.modulo-usu .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}
.modulo-usu .card-modulo-main > .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a252f;
}
.modulo-usu .card-form-modulo {
    border-top: 3px solid #4a7c59;
}
.modulo-usu .card-form-modulo > .card-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border: none;
}
.modulo-usu .card-form-modulo.card-edit > .card-header {
    background: linear-gradient(135deg, #856404, #ffc107);
    color: #1a252f;
}
.modulo-usu .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-usu .badge-registros { font-weight: normal; }
.modulo-usu .table-modulo thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.modulo-usu .table-modulo tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.modulo-usu .table-modulo tbody tr:hover {
    background: #f8fbf8;
}
.modulo-usu .btn-actions .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
}
.modulo-usu .avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.modulo-usu .avatar-circle.activo { background: #2c5530; }
.modulo-usu .avatar-circle.inactivo { background: #6c757d; }
.modulo-usu .guia-campo {
    background: #f8fbf8;
    border-left: 3px solid #2c5530;
    border-radius: 0 8px 8px 0;
    padding: 0.65rem 0.85rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
    color: #495057;
}
.modulo-usu .guia-campo strong { color: #2c5530; }
</style>
