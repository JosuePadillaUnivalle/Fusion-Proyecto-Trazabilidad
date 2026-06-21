<style>
.envios-wrap.page-envios-lista { padding: 0 .25rem; }
.envios-wrap .envios-alert { border-radius: 8px; padding: .65rem 1rem; margin-bottom: .75rem; }
.envios-wrap .env-resumen .metric-card {
    border: 0; border-radius: 8px;
    box-shadow: 0 2px 8px rgba(18, 38, 63, .06);
    margin-bottom: .75rem;
}
.envios-wrap .env-resumen .metric-card .inner { padding: .75rem 1rem; }
.envios-wrap .env-resumen .metric-card h3 { font-size: 1.5rem; font-weight: 800; margin: 0; }
.envios-wrap .env-resumen .metric-card p { font-size: .78rem; margin: 0; opacity: .85; }

.envios-wrap .envios-filtros-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: none;
    border-top: 3px solid #2c5530;
}
.envios-wrap .envios-lista-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: none;
    border-top: 3px solid #2c5530;
}
.envios-wrap .envios-lista-card__toolbar {
    padding: 1rem 1.15rem;
    background: #fff;
    border-bottom: 1px solid #dee2e6;
}

/* — Card envío (ejecutivo) — */
.envio-lista-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: 3px solid var(--env-accent, #2c5530);
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 4px rgba(15, 23, 42, .04);
    transition: box-shadow .15s ease;
}
.envio-lista-card:hover {
    box-shadow: 0 3px 12px rgba(15, 23, 42, .08);
}

.envio-lista-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    padding: .65rem 1rem;
    background: #fff;
    border-bottom: 1px solid #f1f5f9;
}
.envio-lista-card__tipo {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
}
.envio-lista-card__tipo i {
    color: var(--env-accent, #2c5530);
    font-size: .72rem;
}
.envio-lista-card__head-meta {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
}
.envio-lista-estado {
    font-size: .75rem;
    font-weight: 600;
}
.envio-lista-estado--agricola { color: #64748b; }
.envio-lista-estado--logistica { color: #4f46e5; }
.envio-lista-estado--confirmado { color: #15803d; }
.envio-lista-estado--produccion { color: #b45309; }
.envio-lista-estado--rechazado { color: #b91c1c; }
.envio-lista-estado--camino { color: #0369a1; }
.envio-lista-estado--recibido { color: #0f766e; }
.envio-lista-card__fecha {
    font-size: .75rem;
    color: #94a3b8;
}

.envio-lista-card__body {
    padding: .85rem 1rem .75rem;
    flex: 1;
}

/* Código + producto en bloque unificado */
.envio-lista-card__identidad {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding-bottom: .75rem;
    margin-bottom: .75rem;
    border-bottom: 1px solid #f1f5f9;
}
.envio-lista-card__codigo {
    font-size: 1rem;
    font-weight: 700;
    color: var(--env-accent, #2c5530);
    text-decoration: none !important;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    letter-spacing: .02em;
}
.envio-lista-card__codigo:hover { text-decoration: underline !important; }
.envio-lista-card__producto {
    text-align: right;
    min-width: 0;
}
.envio-lista-card__producto-nombre {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}
.envio-lista-card__producto-extra {
    display: block;
    font-size: .72rem;
    color: #64748b;
    margin-top: .15rem;
}

/* Trayecto destacado */
.envio-lista-ruta {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: .5rem;
    align-items: stretch;
    margin-bottom: .75rem;
    padding: .7rem .75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
@media (max-width: 575px) {
    .envio-lista-ruta { grid-template-columns: 1fr; }
    .envio-lista-ruta__separador { transform: rotate(90deg); justify-self: center; }
}
.envio-lista-ruta__punto {
    min-width: 0;
}
.envio-lista-ruta__etiqueta {
    display: block;
    font-size: .62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #94a3b8;
    margin-bottom: .2rem;
}
.envio-lista-ruta__nombre {
    display: block;
    font-size: .84rem;
    font-weight: 600;
    line-height: 1.35;
    word-break: break-word;
}
.envio-lista-ruta__punto--origen .envio-lista-ruta__nombre { color: #2c5530; }
.envio-lista-ruta__punto--destino .envio-lista-ruta__nombre { color: #9f1239; }
.envio-lista-ruta__separador {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: .85rem;
    padding: 0 .15rem;
}

/* Métricas */
.envio-lista-card__metricas {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .75rem;
}
.envio-lista-card__metricas--sin-destino {
    grid-template-columns: repeat(2, 1fr);
}
@media (max-width: 575px) {
    .envio-lista-card__metricas,
    .envio-lista-card__metricas--sin-destino { grid-template-columns: 1fr 1fr; }
}
.envio-lista-card__metrica-label {
    display: block;
    font-size: .62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #94a3b8;
    margin-bottom: .1rem;
}
.envio-lista-card__metrica-valor {
    font-size: .88rem;
    font-weight: 600;
    color: #334155;
}
.envio-lista-card__metrica-valor small {
    font-weight: 500;
    color: #64748b;
}

.envio-lista-card__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .65rem;
    padding: .65rem 1rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}
.envio-lista-card__operador {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem 1.1rem;
    font-size: .78rem;
    color: #475569;
}
.envio-lista-card__operador-item i {
    color: #64748b;
    margin-right: .25rem;
    width: .85rem;
    text-align: center;
}
.envio-lista-card__asignar {
    font-size: .78rem;
    font-weight: 600;
    color: #1d4ed8 !important;
    text-decoration: none !important;
}
.envio-lista-card__acciones {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .35rem;
}
.envio-lista-card__acciones .btn {
    border-radius: 6px;
    font-size: .76rem;
}

.envios-wrap .envios-lista-empty {
    padding: 3rem 1.5rem;
    text-align: center;
    color: #64748b;
}
.envios-wrap .envios-lista-empty i {
    font-size: 2.5rem;
    color: #94a3b8;
    margin-bottom: .75rem;
}
</style>
