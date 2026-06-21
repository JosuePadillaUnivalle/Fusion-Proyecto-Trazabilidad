<style>
/* Ruta en tiempo real — colores por trayecto, tono ejecutivo (no neón) */
:root {
    --rt-border: #e2e8f0;
    --rt-muted: #64748b;
    --rt-text: #1e293b;
    --rt-logistica: #2c5530;
    --rt-logistica-soft: #f0f7f1;
}

[data-sim-tipo="agricola"],
.rt-wrap--agricola {
    --rt-tipo-color: #2c5530;
    --rt-tipo-color-mid: #3d6b42;
    --rt-tipo-soft: #f0f7f1;
    --rt-tipo-border: #d1e7d4;
}
[data-sim-tipo="planta_mayorista"],
.rt-wrap--planta_mayorista {
    --rt-tipo-color: #6d28d9;
    --rt-tipo-color-mid: #7c3aed;
    --rt-tipo-soft: #f5f3ff;
    --rt-tipo-border: #ddd6fe;
}
[data-sim-tipo="distribucion"],
.rt-wrap--distribucion {
    --rt-tipo-color: #c2410c;
    --rt-tipo-color-mid: #ea580c;
    --rt-tipo-soft: #fff7ed;
    --rt-tipo-border: #fed7aa;
}
.rt-wrap--default {
    --rt-tipo-color: #1d4ed8;
    --rt-tipo-color-mid: #2563eb;
    --rt-tipo-soft: #eff6ff;
    --rt-tipo-border: #bfdbfe;
}

.sim-vivo-head {
    background: #fff;
    border: 1px solid var(--rt-border);
    border-top: 3px solid var(--rt-tipo-color, var(--rt-logistica));
    border-radius: 10px;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: .85rem 1.25rem;
}
.sim-vivo-head__main { min-width: 0; flex: 1; }
.sim-vivo-head__code {
    display: block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--rt-tipo-color, var(--rt-logistica));
    letter-spacing: .01em;
}
.sim-vivo-head__label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--rt-muted);
    margin-bottom: .15rem;
}
.sim-vivo-head__tipo {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    margin-top: .45rem;
    padding: .2rem .55rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 600;
    color: var(--rt-tipo-color, var(--rt-logistica));
    background: var(--rt-tipo-soft, var(--rt-logistica-soft));
    border: 1px solid var(--rt-tipo-border, #d1e7d4);
}
.sim-vivo-head__ruta {
    margin-top: .65rem;
}
.sim-vivo-ruta-flow {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem .65rem;
}
.sim-vivo-ruta-punto {
    display: inline-flex;
    align-items: flex-start;
    gap: .45rem;
    flex: 1 1 200px;
    min-width: 0;
    padding: .55rem .75rem;
    border-radius: 8px;
    font-size: .84rem;
    font-weight: 600;
    line-height: 1.4;
}
.sim-vivo-ruta-punto i {
    flex-shrink: 0;
    margin-top: .15rem;
    font-size: .78rem;
}
.sim-vivo-ruta-punto--origen {
    color: #2c5530;
    background: #f0f7f1;
    border: 1px solid #d1e7d4;
    border-left: 3px solid #2c5530;
}
.sim-vivo-ruta-punto--destino {
    color: #9f1239;
    background: #fff1f2;
    border: 1px solid #fecdd3;
    border-left: 3px solid #be123c;
}
.sim-vivo-ruta-flecha {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid var(--rt-tipo-border, #e2e8f0);
    color: var(--rt-tipo-color, #64748b);
    font-size: .95rem;
    box-shadow: 0 1px 4px rgba(15, 23, 42, .06);
}
.sim-vivo-head__ruta-sep { color: var(--rt-tipo-color-mid, #94a3b8); margin: 0 .35rem; }
.sim-vivo-head__actions { flex-shrink: 0; }

.rt-page-head {
    background: #fff;
    border: 1px solid var(--rt-border);
    border-top: 3px solid var(--rt-logistica);
    border-radius: 10px;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: .75rem 1rem;
}
.rt-page-head__title { font-size: .95rem; font-weight: 700; color: var(--rt-text); margin: 0; }
.rt-page-head__subtitle { font-size: .82rem; color: var(--rt-muted); margin: .15rem 0 0; }
.rt-page-head__actions { display: flex; flex-wrap: wrap; gap: .5rem .75rem; }

.rt-info-banner {
    background: var(--rt-logistica-soft);
    border: 1px solid var(--rt-tipo-border, #d1e7d4);
    border-left: 4px solid var(--rt-logistica);
    border-radius: 8px;
    padding: .75rem 1rem;
    color: var(--rt-text);
    font-size: .86rem;
    margin-bottom: 1rem;
}
.rt-info-banner i { color: var(--rt-logistica); }

.rt-leyenda-trayectos {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem .75rem;
    margin-bottom: 1rem;
}
.rt-leyenda-item {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .78rem;
    color: var(--rt-muted);
    padding: .35rem .65rem;
    background: #fff;
    border: 1px solid var(--rt-border);
    border-radius: 999px;
}
.rt-leyenda-item__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.sim-live-panel {
    border: 1px solid var(--rt-border);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    background: #fff;
}
.sim-live-panel .card-header {
    background: var(--rt-tipo-soft, #f8fafc);
    border-bottom: 1px solid var(--rt-tipo-border, var(--rt-border));
    padding: .85rem 1rem;
}
.sim-live-panel .card-title { font-size: .92rem; color: var(--rt-text); }
.sim-live-panel .card-title i { color: var(--rt-tipo-color, var(--rt-logistica)); }

.sim-live-badge {
    font-weight: 600;
    font-size: .72rem;
    letter-spacing: .02em;
    padding: .35rem .65rem;
    border-radius: 999px;
}
.sim-live-badge.sim-live--activo {
    background: var(--rt-tipo-soft, #eff6ff) !important;
    color: var(--rt-tipo-color, #1d4ed8) !important;
    border: 1px solid var(--rt-tipo-border, #bfdbfe);
}
.sim-live-badge.sim-live--completado {
    background: #f0fdf4 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0;
}
.sim-live-pct {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--rt-tipo-color, var(--rt-logistica));
}
.sim-live-pct.sim-live--completado { color: #15803d; }
.sim-live-bar {
    background: var(--rt-tipo-color-mid, var(--rt-tipo-color, var(--rt-logistica))) !important;
    border-radius: 999px;
}
.sim-live-bar.sim-live--completado { background: #16a34a !important; }
.sim-live-panel .progress { background: #e2e8f0; border-radius: 999px; }
.sim-live-eta { color: var(--rt-muted) !important; font-size: .84rem; }

.rt-live-card {
    border: 1px solid var(--rt-border);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
}
.rt-live-card .card-header {
    background: linear-gradient(180deg, var(--rt-logistica-soft) 0%, #fff 100%);
    border-bottom: 1px solid var(--rt-border);
}
.rt-live-card .card-title { color: var(--rt-text); font-size: .95rem; }
.rt-live-card .card-title i { color: var(--rt-logistica); }
.rt-live-card .sim-lista-total {
    background: var(--rt-logistica) !important;
    color: #fff !important;
    font-weight: 700;
    border-radius: 999px;
    min-width: 1.5rem;
}

.rt-filtros-card {
    border: 1px solid var(--rt-border);
    border-radius: 12px;
    box-shadow: 0 1px 6px rgba(15, 23, 42, .04);
}

.rt-tipo-pill,
.rt-estado-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .7rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .02em;
    white-space: nowrap;
    line-height: 1.2;
    color: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .12);
}
.rt-estado-pill {
    color: #0369a1 !important;
    background: #e0f2fe !important;
    border: 1px solid #bae6fd;
    box-shadow: none;
}
.rt-live-pct { font-weight: 800; }

.rt-btn-mapa {
    background: var(--rt-logistica);
    border-color: var(--rt-logistica);
    color: #fff;
}
.rt-btn-mapa:hover {
    background: #234829;
    border-color: #234829;
    color: #fff;
}

#mapaSimulacionVivo {
    height: 480px;
    width: 100%;
    min-height: 420px;
    border-radius: 10px;
    border: 1px solid var(--rt-border);
    z-index: 1;
}
[data-sim-tipo="agricola"] .sim-camion-pin { background: #2c5530 !important; }
[data-sim-tipo="planta_mayorista"] .sim-camion-pin { background: #7c3aed !important; }
[data-sim-tipo="distribucion"] .sim-camion-pin { background: #ea580c !important; }
[data-sim-tipo="agricola"] .sim-ruta-pendiente { stroke: #3d6b42 !important; }
[data-sim-tipo="agricola"] .sim-ruta-recorrida { stroke: #2c5530 !important; }
[data-sim-tipo="planta_mayorista"] .sim-ruta-pendiente { stroke: #8b5cf6 !important; }
[data-sim-tipo="planta_mayorista"] .sim-ruta-recorrida { stroke: #6d28d9 !important; }
[data-sim-tipo="distribucion"] .sim-ruta-pendiente { stroke: #fb923c !important; }
[data-sim-tipo="distribucion"] .sim-ruta-recorrida { stroke: #ea580c !important; }
</style>
