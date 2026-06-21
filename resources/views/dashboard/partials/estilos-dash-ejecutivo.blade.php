{{-- Dashboards e inicio por rol — KPI y héroes ejecutivos (sin gradientes LED) --}}
<style>
/* —— Héroe —— */
.role-panel-hero,
.inicio-dash-hero {
    background: #fff !important;
    border: 1px solid #dee2e6 !important;
    border-top: 3px solid var(--rp-accent, var(--inicio-accent, #2c5530)) !important;
    border-radius: 8px !important;
    box-shadow: none !important;
    padding: 1.1rem 1.25rem !important;
    margin-bottom: 1.25rem;
    position: relative;
    overflow: visible;
}
.role-panel-hero::after,
.inicio-dash-hero::after { display: none !important; }
.role-panel-hero__title,
.inicio-dash-hero__title {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
}
.role-panel-hero__sub,
.inicio-dash-hero__sub { color: #64748b !important; font-size: .88rem !important; }
.role-panel-hero__title i,
.inicio-dash-hero__title i {
    width: 34px !important;
    height: 34px !important;
    border-radius: 8px !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-size: .9rem !important;
    box-shadow: none !important;
    margin-right: .5rem !important;
}
.inicio-dash-link-panel {
    border-radius: 8px !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #334155 !important;
    font-weight: 600 !important;
}
.inicio-dash-link-panel:hover {
    background: #f1f5f9 !important;
    color: #1e293b !important;
    border-color: #cbd5e1 !important;
}

/* —— KPI compartido (role-metric, inicio-kpi, admin) —— */
.role-metrics,
.inicio-kpi-row { gap: .75rem !important; }
.role-metric,
.inicio-kpi,
a.role-metric {
    background: #fff !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
    padding: .95rem 1rem !important;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s ease;
}
.role-metric:hover,
a.role-metric:hover {
    box-shadow: 0 2px 8px rgba(15, 23, 42, .06) !important;
    color: #1e293b !important;
    text-decoration: none !important;
}
.role-metric__val,
.inicio-kpi__val {
    font-size: 1.45rem !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
    color: #1e293b !important;
}
.role-metric__lbl,
.inicio-kpi__lbl {
    font-size: .72rem !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b !important;
    opacity: 1 !important;
    margin: .2rem 0 0 !important;
}
.role-metric__sub { font-size: .7rem !important; color: #94a3b8 !important; opacity: 1 !important; }
.role-metric__icon,
.inicio-kpi__icon {
    position: absolute !important;
    right: 10px !important;
    top: 10px !important;
    left: auto !important;
    bottom: auto !important;
    transform: none !important;
    font-size: 2.4rem !important;
    line-height: 1 !important;
    opacity: 1 !important;
    color: rgba(100, 116, 139, 0.16) !important;
}

/* Acento superior + valor */
.dash-kpi--green,
.admin-metric--lotes,
.role-metric--a1,
.role-metric--a4,
.planta-metric--pedidos { border-top: 3px solid #2c5530 !important; }
.dash-kpi--green .role-metric__val,
.dash-kpi--green .inicio-kpi__val,
.admin-metric--lotes .role-metric__val,
.role-metric--a1 .role-metric__val,
.role-metric--a4 .role-metric__val,
.planta-metric--pedidos .planta-metric__val { color: #2c5530 !important; }
.dash-kpi--green .role-metric__icon,
.dash-kpi--green .inicio-kpi__icon,
.admin-metric--lotes .role-metric__icon { color: rgba(44, 85, 48, 0.16) !important; }

.dash-kpi--blue,
.admin-metric--prod,
.role-metric--a3,
.planta-metric--asig { border-top: 3px solid #2563eb !important; }
.dash-kpi--blue .role-metric__val,
.dash-kpi--blue .inicio-kpi__val,
.admin-metric--prod .role-metric__val,
.role-metric--a3 .role-metric__val,
.planta-metric--asig .planta-metric__val { color: #2563eb !important; }
.dash-kpi--blue .role-metric__icon,
.dash-kpi--blue .inicio-kpi__icon,
.admin-metric--prod .role-metric__icon { color: rgba(37, 99, 235, 0.16) !important; }

.dash-kpi--amber,
.admin-metric--inv,
.role-metric--a2,
.planta-metric--inc { border-top: 3px solid #d97706 !important; }
.dash-kpi--amber .role-metric__val,
.dash-kpi--amber .inicio-kpi__val,
.admin-metric--inv .role-metric__val,
.role-metric--a2 .role-metric__val,
.planta-metric--inc .planta-metric__val { color: #d97706 !important; }
.dash-kpi--amber .role-metric__icon,
.dash-kpi--amber .inicio-kpi__icon,
.admin-metric--inv .role-metric__icon { color: rgba(217, 119, 6, 0.16) !important; }

.dash-kpi--purple,
.role-metric--a5 { border-top: 3px solid #6d28d9 !important; }
.dash-kpi--purple .role-metric__val,
.dash-kpi--purple .inicio-kpi__val,
.role-metric--a5 .role-metric__val { color: #6d28d9 !important; }
.dash-kpi--purple .role-metric__icon,
.dash-kpi--purple .inicio-kpi__icon,
.role-metric--a5 .role-metric__icon { color: rgba(109, 40, 217, 0.16) !important; }

.dash-kpi--teal,
.admin-metric--transporte,
.planta-metric--rutas { border-top: 3px solid #0d9488 !important; }
.dash-kpi--teal .role-metric__val,
.dash-kpi--teal .inicio-kpi__val,
.admin-metric--transporte .role-metric__val,
.planta-metric--rutas .planta-metric__val { color: #0d9488 !important; }
.dash-kpi--teal .role-metric__icon,
.dash-kpi--teal .inicio-kpi__icon,
.admin-metric--transporte .role-metric__icon { color: rgba(13, 148, 136, 0.16) !important; }

.dash-kpi--slate,
.planta-metric--doc { border-top: 3px solid #64748b !important; }
.dash-kpi--slate .role-metric__val,
.dash-kpi--slate .inicio-kpi__val,
.planta-metric--doc .planta-metric__val { color: #475569 !important; }
.dash-kpi--slate .role-metric__icon,
.dash-kpi--slate .inicio-kpi__icon { color: rgba(100, 116, 139, 0.16) !important; }

/* Planta métricas custom */
.planta-metric,
.planta-metrics .role-metric {
    background: #fff !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
}
.planta-metric__icon {
    position: absolute;
    right: 10px;
    top: 10px;
    font-size: 2.2rem;
    color: rgba(100, 116, 139, 0.16) !important;
}

/* Accesos rápidos */
.role-acc-tile__icon,
.planta-acc-tile__icon {
    background: #f1f5f9 !important;
    color: #475569 !important;
    box-shadow: none !important;
    border: 1px solid #e2e8f0;
    width: 36px !important;
    height: 36px !important;
    font-size: .88rem !important;
}
.role-acc-tile:hover,
.planta-acc-tile:hover {
    transform: none !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .05) !important;
}
.role-block-card,
.role-acc-card,
.inicio-chart-card,
.admin-chart-card {
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
}
.role-block-card__head,
.role-acc-card__head,
.inicio-chart-card__head,
.admin-chart-card .card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

    background: #f8fafc !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    border-top: 3px solid #0ea5e9 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
}
.admin-weather .weather-temp { color: #1e293b !important; font-weight: 700 !important; }
.admin-weather .weather-desc,
.admin-weather .weather-details { color: #64748b !important; opacity: 1 !important; }
.activity-icon {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 8px !important;
    width: 36px !important;
    height: 36px !important;
    font-size: .85rem !important;
}
.alert-icon {
    background: #fffbeb !important;
    color: #b45309 !important;
    border: 1px solid #fde68a;
}

/* Mapa de lotes — KPI */
.mapa-kpi-card {
    background: #fff !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
    padding: 1rem 1.1rem !important;
    display: block !important;
    position: relative;
    overflow: hidden;
    transition: box-shadow .15s ease;
}
.mapa-kpi-card:hover {
    transform: none !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .06) !important;
}
.mapa-kpi-card__icon {
    position: absolute !important;
    right: 10px !important;
    top: 10px !important;
    width: auto !important;
    height: auto !important;
    border-radius: 0 !important;
    background: transparent !important;
    font-size: 2.4rem !important;
    color: rgba(100, 116, 139, 0.16) !important;
    display: block !important;
}
.mapa-kpi-card--total { border-top: 3px solid #64748b; }
.mapa-kpi-card--total .mapa-kpi-card__value { color: #475569 !important; }
.mapa-kpi-card--total .mapa-kpi-card__icon { color: rgba(100, 116, 139, 0.16) !important; }
.mapa-kpi-card--produccion { border-top: 3px solid #2c5530; }
.mapa-kpi-card--produccion .mapa-kpi-card__value { color: #2c5530 !important; }
.mapa-kpi-card--produccion .mapa-kpi-card__icon { color: rgba(44, 85, 48, 0.16) !important; }
.mapa-kpi-card--cosecha { border-top: 3px solid #d97706; }
.mapa-kpi-card--cosecha .mapa-kpi-card__value { color: #d97706 !important; }
.mapa-kpi-card--cosecha .mapa-kpi-card__icon { color: rgba(217, 119, 6, 0.16) !important; }
.mapa-kpi-card--hectareas { border-top: 3px solid #0d9488; }
.mapa-kpi-card--hectareas .mapa-kpi-card__value { color: #0d9488 !important; }
.mapa-kpi-card--hectareas .mapa-kpi-card__icon { color: rgba(13, 148, 136, 0.16) !important; }
.mapa-kpi-card__value {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    letter-spacing: normal !important;
}
.mapa-kpi-card__label { color: #64748b !important; }

/* Panel almacén legacy */
.panel-card.metric-card,
.card.metric-card {
    background: #fff !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
}
.card.metric-card.bg-success { border-top: 3px solid #2c5530 !important; }
.card.metric-card.bg-warning { border-top: 3px solid #d97706 !important; }
.card.metric-card.bg-info { border-top: 3px solid #2563eb !important; }
.card.metric-card.bg-primary { border-top: 3px solid #1d4ed8 !important; }
.card.metric-card.bg-teal { border-top: 3px solid #0d9488 !important; }
.card.metric-card.bg-orange { border-top: 3px solid #ea580c !important; }
.card.metric-card .icon {
    color: rgba(100, 116, 139, 0.16) !important;
    opacity: 1 !important;
    font-size: 2.2rem !important;
}
.card.metric-card h3 { color: #1e293b !important; font-weight: 700; }
.card.metric-card p { color: #64748b !important; }

.dash-filtros { box-shadow: none !important; border-radius: 8px !important; }
.dash-filtros__label i { color: #64748b !important; }

/* Panel planta */
.planta-panel-hero {
    background: #fff !important;
    border: 1px solid #dee2e6 !important;
    border-top: 3px solid var(--rp-accent, #2c5530) !important;
    border-radius: 8px !important;
    box-shadow: none !important;
    overflow: visible !important;
}
.planta-panel-hero::after { display: none !important; }
.planta-panel-hero__title { font-size: 1.15rem !important; font-weight: 700 !important; color: #1e293b !important; }
.planta-panel-hero__title i {
    background: #f1f5f9 !important;
    color: #475569 !important;
    box-shadow: none !important;
    border-radius: 8px !important;
}
.planta-panel-hero__sub { color: #64748b !important; }
.planta-metric__val { color: #1e293b !important; font-weight: 700 !important; }
.planta-metric__lbl { color: #64748b !important; opacity: 1 !important; }
.planta-acc-card { border: 1px solid #dee2e6 !important; border-radius: 8px !important; box-shadow: none !important; }

/* Filtros dashboard */
.dash-filtros-card { border-radius: 8px !important; box-shadow: none !important; border: 1px solid #dee2e6 !important; }
.dash-filtros-card__title i {
    background: #f1f5f9 !important;
    color: #475569 !important;
}
.dash-filtros-chip.is-active {
    background: #f0f7f1 !important;
    border: 1px solid #2c5530 !important;
    color: #2c5530 !important;
    box-shadow: none !important;
}
.dash-filtros-chip { border-radius: 8px !important; }
</style>
