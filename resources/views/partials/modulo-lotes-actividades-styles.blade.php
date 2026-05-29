{{-- Estilos compartidos: Lotes, Mapa, Actividades (AdminLTE) --}}
<style>
.modulo-la .small-box {
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.modulo-la .small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.modulo-la .small-box .inner {
    position: relative;
    z-index: 1;
}
.modulo-la .small-box .inner h3 { font-size: 1.75rem; }
.modulo-la .small-box .icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 70px;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
    transition: transform 0.3s ease, color 0.3s ease;
}
.modulo-la .small-box:hover .icon {
    transform: scale(1.05);
    color: rgba(0, 0, 0, 0.22);
}
.modulo-la .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.modulo-la .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.modulo-la .small-box-yellow {
    background: linear-gradient(135deg, #f39c12, #ffc107) !important;
    color: #fff;
}
.modulo-la .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
    color: #fff;
}
.modulo-la .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74c3c) !important;
    color: #fff;
}
.modulo-la .small-box-teal {
    background: linear-gradient(135deg, #28a745, #34ce57) !important;
    color: #fff;
}
.modulo-la .small-box-footer {
    background: rgba(0, 0, 0, 0.12);
    color: rgba(255, 255, 255, 0.9);
    position: relative;
    z-index: 1;
}
.modulo-la .small-box-footer:hover {
    background: rgba(0, 0, 0, 0.18);
    color: #fff;
}
.modulo-la .card-modulo-main {
    border-top: 3px solid #2c5530;
}
.modulo-la .card-modulo-main > .card-header {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}
.modulo-la .card-modulo-main > .card-header .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1a252f;
}
.modulo-la .filtros-panel {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.25rem;
}
.modulo-la .view-toggle .btn {
    border-color: #ced4da;
    color: #6c757d;
}
.modulo-la .view-toggle .btn.active {
    background: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
.modulo-la .badge-registros {
    font-weight: normal;
}
</style>
