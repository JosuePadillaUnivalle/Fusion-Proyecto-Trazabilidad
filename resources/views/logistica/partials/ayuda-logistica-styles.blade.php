<style>
.log-guia {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-left: 4px solid #059669;
    border-radius: 10px;
    padding: 1rem 1.15rem;
    margin-bottom: 1.25rem;
    font-size: .9rem;
    color: #334155;
}
.log-guia h4, .log-guia .log-guia-titulo {
    font-size: .95rem;
    font-weight: 700;
    color: #065f46;
    margin: 0 0 .5rem;
}
.log-guia ol, .log-guia ul { margin-bottom: 0; padding-left: 1.2rem; }
.log-guia li { margin-bottom: .35rem; }
.log-guia-compact { padding: .75rem 1rem; font-size: .85rem; }
.log-pasos {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.log-paso {
    flex: 1;
    min-width: 100px;
    text-align: center;
    padding: .5rem .4rem;
    border-radius: 8px;
    background: #e2e8f0;
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
}
.log-paso.activo { background: #059669; color: #fff; }
.log-paso.hecho { background: #d1fae5; color: #065f46; }
.envios-asignacion-filtros {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .85rem 1rem;
    margin-bottom: .85rem;
}
.envios-asignacion-meta {
    font-size: .82rem;
    color: #64748b;
    margin-bottom: .65rem;
}
.envios-tabla-scroll {
    max-height: min(52vh, 420px);
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    -webkit-overflow-scrolling: touch;
}
.envios-tabla-scroll .table { margin-bottom: 0; }
.envios-tabla-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8f9fa;
    box-shadow: 0 1px 0 #dee2e6;
    white-space: nowrap;
}
.envios-tabla-scroll tbody tr.d-none-filtro { display: none; }
</style>
