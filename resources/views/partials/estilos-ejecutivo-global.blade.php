{{-- Overrides globales: quitar aspecto LED (pills, gradientes hero, KPI sólidos) --}}
<style>
/* Hero / banners */
.cosecha-hero,
.tpm-hero,
.log-ops-hero {
    background: #fff !important;
    border: 1px solid #dee2e6 !important;
    border-top: 3px solid #2c5530 !important;
    border-radius: 8px !important;
    color: #1e293b !important;
    box-shadow: none !important;
}
.cosecha-hero h2, .cosecha-hero p { color: inherit !important; opacity: 1 !important; }
.tpm-hero { border-top-color: #5b21b6 !important; }
.log-ops-hero--warn { border-top-color: #d97706 !important; }

/* KPI cards con gradiente → ejecutivo */
.cosecha-kpi,
.mov-kpi,
.page-mov-almacen .mov-kpi {
    background: #fff !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    box-shadow: none !important;
    border-radius: 8px !important;
}
.cosecha-kpi--green, .mov-kpi-ingreso { border-top: 3px solid #2c5530 !important; }
.cosecha-kpi--teal, .mov-kpi-total { border-top: 3px solid #0d9488 !important; }
.cosecha-kpi--amber, .mov-kpi-salida { border-top: 3px solid #d97706 !important; }
.cosecha-kpi--violet { border-top: 3px solid #6d28d9 !important; }
.cosecha-kpi__val, .mov-kpi-value { color: #1e293b !important; }
.cosecha-kpi__lbl, .mov-kpi-label { color: #64748b !important; opacity: 1 !important; }
.mov-kpi-icon { background: #f1f5f9 !important; color: #64748b !important; }
.mov-kpi:hover { transform: none !important; box-shadow: 0 2px 8px rgba(15,23,42,.06) !important; }

/* Chips / pills de datos → texto */
.cos-chip,
.stock-badge-high, .stock-badge-medium, .stock-badge-low,
.mov-tipo-pill,
.mov-tipo-pill.ingreso, .mov-tipo-pill.salida,
.rep-stat-pill,
.pdv-solicitud-pill,
.pdv-estado-pill,
.pdv-status-pill,
.log-ops-chip--abierto, .log-ops-chip--pendiente, .log-ops-chip--resuelto {
    background: transparent !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    font-weight: 600;
}
.cos-chip--emerald, .stock-badge-high { color: #15803d !important; }
.cos-chip--sky { color: #0369a1 !important; }
.cos-chip--amber { color: #b45309 !important; }
.stock-badge-medium { color: #b45309 !important; }
.stock-badge-low { color: #b91c1c !important; }
.mov-tipo-pill.ingreso, .rep-stat-pill.ingreso { color: #15803d !important; }
.mov-tipo-pill.salida, .rep-stat-pill.salida { color: #c2410c !important; }
.pdv-status-pill--activo { color: #15803d !important; }
.pdv-status-pill--inactivo { color: #64748b !important; }
.pdv-status-pill--stock { color: #0369a1 !important; }
.pdv-status-pill--sin-stock { color: #94a3b8 !important; }
.log-ops-chip--abierto { color: #b91c1c !important; }
.log-ops-chip--pendiente { color: #b45309 !important; }
.log-ops-chip--resuelto { color: #0f766e !important; }
.pdv-estado-pill--revision { color: #b45309 !important; }
.pdv-estado-pill--preparacion { color: #0369a1 !important; }
.pdv-estado-pill--asignado { color: #c2410c !important; }
.pdv-estado-pill--ruta { color: #1d4ed8 !important; }
.pdv-estado-pill--recibido { color: #15803d !important; }
.pdv-estado-pill--rechazado { color: #b91c1c !important; }
.pdv-solicitud-pill { color: #2c5530 !important; font-family: inherit !important; }

/* Catálogos logística — nav interno */
.modulo-env .cat-log-side__head {
    background: #f8fafc !important;
    color: #475569 !important;
    border-bottom: 1px solid #dee2e6 !important;
}
.modulo-env .cat-log-side__icon-wrap {
    background: transparent !important;
    color: #64748b !important;
    width: auto !important;
    height: auto !important;
}
.modulo-env .cat-log-side__link.is-active .cat-log-side__icon-wrap {
    background: transparent !important;
    color: var(--link-accent, #2c5530) !important;
}
.modulo-env .cat-log-header {
    background: #fff !important;
    border-bottom: 1px solid #dee2e6 !important;
}
.modulo-env .cat-log-header__icon {
    background: #f1f5f9 !important;
    color: var(--cat-accent) !important;
}

/* Métricas log-ops */
.log-ops-metric { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
.log-ops-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
.log-ops-btn-primary { background: #2c5530 !important; border: 1px solid #2c5530 !important; }
.log-ops-btn-primary:hover { background: #4a7c59 !important; }

/* Planificar distribución */
.ruta-plan-hero {
    background: #fff !important;
    border-bottom: 1px solid #dee2e6 !important;
    padding: 1rem 1.15rem !important;
}
.ruta-plan-hero::after { display: none !important; }
.ruta-plan-hero__title i {
    background: #f1f5f9 !important;
    color: #2c5530 !important;
    box-shadow: none !important;
}
.ruta-plan-btn-nueva {
    background: #2c5530 !important;
    box-shadow: none !important;
}
.ruta-plan-btn-nueva:hover {
    background: #4a7c59 !important;
    transform: none !important;
    box-shadow: none !important;
}
.ruta-plan-metric {
    background: #fff !important;
    border: 1px solid #dee2e6 !important;
    box-shadow: none !important;
}
.ruta-plan-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }

/* Clima historial */
.dato-badge, .dato-badge.temp, .dato-badge.hum {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    font-weight: 600;
}
.dato-badge.temp { color: #b45309 !important; }
.dato-badge.hum { color: #0369a1 !important; }

/* Clima widgets */
.modulo-prod .weather-widget,
.modulo-prod .weather-widget.night {
    background: #f8fafc !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
}
.modulo-prod .weather-temp { color: #1e293b !important; }
.modulo-prod .sun-card {
    background: #fff !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6 !important;
    border-top: 3px solid #d97706 !important;
    border-radius: 8px !important;
}
</style>
