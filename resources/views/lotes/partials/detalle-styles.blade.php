<style>
    :root {
        --lote-primary: #2c5530;
        --lote-secondary: #4a7c59;
        --lote-border: #e8edf2;
        --lote-muted: #64748b;
    }

    /* —— Hero —— */
    .lote-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #1e4620 0%, #2c5530 48%, #3d7a46 100%);
        color: #fff;
        border-radius: 18px;
        padding: 1.5rem 1.65rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 12px 32px rgba(30, 70, 32, .22);
    }
    .lote-hero__glow {
        position: absolute;
        top: -40%;
        right: -8%;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,.14) 0%, transparent 70%);
        pointer-events: none;
    }
    .lote-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        opacity: .85;
        margin-bottom: .35rem;
    }
    .lote-hero__title {
        margin: 0 0 .65rem;
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -.02em;
        line-height: 1.15;
    }
    .lote-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem .85rem;
    }
    .lote-hero__meta-item {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .86rem;
        opacity: .92;
        max-width: 100%;
    }
    .lote-hero__meta-item i { opacity: .75; font-size: .8rem; }
    .lote-hero__chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-start;
    }
    @media (min-width: 992px) {
        .lote-hero__chips { justify-content: flex-end; }
    }
    .lote-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .42rem .95rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .lote-hero-chip--cultivo {
        background: rgba(255,255,255,.16);
        color: #fff;
        border: 1px solid rgba(255,255,255,.32);
        backdrop-filter: blur(6px);
    }
    .lote-hero-chip--estado {
        background: #fff;
        border: 1px solid rgba(255,255,255,.85);
        box-shadow: 0 4px 14px rgba(15, 23, 42, .15);
    }
    .lote-hero-chip--estado-planificado,
    .lote-hero-chip--estado-default { color: #475569; }
    .lote-hero-chip--estado-sembrado { color: #1d4ed8; }
    .lote-hero-chip--estado-en_crecimiento,
    .lote-hero-chip--estado-en_crecimiento { color: #047857; }
    .lote-hero-chip--estado-listo_para_cosecha { color: #0369a1; }
    .lote-hero-chip--estado-cosechado { color: #b45309; }
    .lote-hero-chip--estado-finalizado { color: #334155; }

    /* —— Chips (panel blanco) —— */
    .lote-chip-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .55rem;
        margin-bottom: 1.1rem;
    }
    .lote-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .5rem .95rem;
        border-radius: 12px;
        font-size: .8rem;
        font-weight: 700;
        line-height: 1.2;
        border: 1px solid transparent;
    }
    .lote-chip__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 7px;
        font-size: .68rem;
        flex-shrink: 0;
    }
    .lote-chip--cultivo {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #065f46;
        border-color: #a7f3d0;
        box-shadow: 0 2px 8px rgba(5, 150, 105, .1);
    }
    .lote-chip--cultivo .lote-chip__icon {
        background: #059669;
        color: #fff;
    }
    .lote-chip--muted {
        background: #f8fafc;
        color: #64748b;
        border-color: #e2e8f0;
    }
    .lote-chip--muted .lote-chip__icon {
        background: #e2e8f0;
        color: #64748b;
    }
    .lote-chip--estado {
        background: #f8fafc;
        color: #475569;
        border-color: #e2e8f0;
    }
    .lote-chip--estado-planificado,
    .lote-chip--estado-default {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        color: #475569;
        border-color: #cbd5e1;
    }
    .lote-chip--estado-sembrado {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: #1d4ed8;
        border-color: #93c5fd;
    }
    .lote-chip--estado-en_crecimiento {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #047857;
        border-color: #6ee7b7;
    }
    .lote-chip--estado-listo_para_cosecha {
        background: linear-gradient(135deg, #ecfeff, #cffafe);
        color: #0e7490;
        border-color: #67e8f9;
    }
    .lote-chip--estado-cosechado {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        color: #b45309;
        border-color: #fcd34d;
    }
    .lote-chip--estado-finalizado {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        color: #334155;
        border-color: #cbd5e1;
    }

    /* —— KPI strip —— */
    .lote-kpi-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: .75rem;
        margin-bottom: 1.25rem;
    }
    @media (max-width: 1199px) { .lote-kpi-row { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 575px) { .lote-kpi-row { grid-template-columns: repeat(2, 1fr); } }
    .lote-kpi {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .85rem 1rem;
        border-radius: 14px;
        color: #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .08);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .lote-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .12);
    }
    .lote-kpi__icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        background: rgba(255,255,255,.22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .lote-kpi__val {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .lote-kpi__val small { font-size: .65rem; font-weight: 700; opacity: .9; }
    .lote-kpi__lbl {
        font-size: .72rem;
        opacity: .92;
        margin-top: .1rem;
        line-height: 1.2;
    }
    .lote-kpi--green { background: linear-gradient(135deg, #2c5530, #4a7c59); }
    .lote-kpi--blue { background: linear-gradient(135deg, #0369a1, #0ea5e9); }
    .lote-kpi--amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
    .lote-kpi--teal { background: linear-gradient(135deg, #0d9488, #14b8a6); }
    .lote-kpi--indigo { background: linear-gradient(135deg, #4338ca, #6366f1); }
    .lote-kpi--rose { background: linear-gradient(135deg, #be123c, #e11d48); }

    /* —— Tabs —— */
    .lote-tabs-wrap {
        background: #fff;
        border: 1px solid var(--lote-border);
        border-radius: 14px;
        padding: .4rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 12px rgba(15, 23, 42, .04);
    }
    .lote-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .lote-tabs .nav-item { margin: 0; }
    .lote-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 10px;
        padding: .6rem 1.15rem;
        font-weight: 600;
        font-size: .86rem;
        color: var(--lote-muted);
        border: 1px solid transparent;
        background: transparent;
        transition: background .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .lote-tabs .nav-link i { font-size: .82rem; opacity: .85; }
    .lote-tabs .nav-link:not(.active):hover {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #334155;
    }
    .lote-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--lote-primary), var(--lote-secondary));
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(44, 85, 48, .28);
    }
    .lote-tabs .nav-link.active i { opacity: 1; }

    /* —— Content grid —— */
    .lote-content-grid { margin-bottom: 1rem; }

    /* —— Datos del lote panel —— */
    .lote-datos-panel {
        background: #fff;
        border: 1px solid var(--lote-border);
        border-radius: 16px;
        padding: 1.25rem 1.35rem 1.15rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, .06);
        height: 100%;
    }
    .lote-datos-panel__header {
        display: flex;
        align-items: center;
        gap: .85rem;
        margin-bottom: 1.1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .lote-datos-panel__header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--lote-primary), var(--lote-secondary));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(44, 85, 48, .22);
    }
    .lote-datos-panel__title {
        margin: 0;
        font-weight: 700;
        font-size: 1.05rem;
        color: #1e293b;
    }
    .lote-datos-panel__subtitle {
        margin: .15rem 0 0;
        font-size: .78rem;
        color: var(--lote-muted);
    }
    .lote-datos-panel__metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .65rem;
        margin-bottom: 1rem;
    }
    .lote-datos-panel__metric {
        background: #f8fafc;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: .7rem .75rem;
        text-align: center;
        transition: border-color .2s, box-shadow .2s;
    }
    .lote-datos-panel__metric:hover {
        border-color: #c5d9c8;
        box-shadow: 0 2px 10px rgba(44, 85, 48, .06);
    }
    .lote-datos-panel__metric-label {
        display: block;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: .3rem;
    }
    .lote-datos-panel__metric-value {
        display: block;
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }
    .lote-datos-panel__metric-value--sm { font-size: .88rem; font-weight: 700; }
    .lote-datos-panel__metric-hint {
        display: block;
        font-size: .68rem;
        font-weight: 500;
        color: #94a3b8;
        margin-top: .15rem;
    }
    .lote-datos-panel__grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .6rem;
        margin-bottom: .75rem;
    }
    .lote-datos-panel__item {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        background: #f8fafc;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: .75rem .85rem;
    }
    .lote-datos-panel__item--wide { grid-column: 1 / -1; }
    .lote-datos-panel__item-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #fff;
        color: var(--lote-primary);
        border: 1px solid #e2ebe3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
    }
    .lote-datos-panel__item-body { min-width: 0; flex: 1; }
    .lote-datos-panel__item-label {
        display: block;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .lote-datos-panel__item-value {
        display: block;
        font-size: .9rem;
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
    }
    .lote-datos-panel__traz-code {
        display: inline-block;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .78rem;
        font-weight: 600;
        color: #9d174d;
        background: #fdf2f8;
        border: 1px solid #fbcfe8;
        border-radius: 8px;
        padding: .3rem .6rem;
        letter-spacing: .02em;
    }
    .lote-datos-panel__geo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .75rem;
        background: linear-gradient(135deg, #f0fdf4, #fff);
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: .85rem 1rem;
    }
    .lote-datos-panel__geo-info {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        min-width: 0;
        flex: 1;
    }
    .lote-datos-panel__geo-info > i {
        font-size: 1.15rem;
        color: var(--lote-primary);
        margin-top: .15rem;
    }
    .lote-datos-panel__map-btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .5rem 1rem;
        border-radius: 10px;
        font-size: .82rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, var(--lote-primary), var(--lote-secondary));
        border: 0;
        text-decoration: none;
        box-shadow: 0 3px 10px rgba(44, 85, 48, .22);
        transition: transform .15s ease, box-shadow .15s ease;
        white-space: nowrap;
    }
    .lote-datos-panel__map-btn:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(44, 85, 48, .28);
    }

    /* —— Resumen operativo —— */
    .lote-op-panel {
        background: #fff;
        border: 1px solid var(--lote-border);
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, .06);
    }
    .lote-op-panel__head {
        display: flex;
        align-items: center;
        gap: .85rem;
        margin-bottom: 1.1rem;
    }
    .lote-op-panel__head-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0369a1, #0ea5e9);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .lote-op-panel__head-icon--photo {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
    }
    .lote-op-panel__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
    }
    .lote-op-panel__subtitle {
        margin: .15rem 0 0;
        font-size: .78rem;
        color: var(--lote-muted);
    }
    .lote-op-panel__photo {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eef2f6;
    }
    .lote-op-panel__photo img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        display: block;
    }
    .lote-op-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: .65rem;
        margin-bottom: 1rem;
    }
    .lote-op-stat {
        background: #f8fafc;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: .85rem .75rem;
        text-align: center;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .lote-op-stat:hover {
        border-color: #dbeafe;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    }
    .lote-op-stat__icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .45rem;
        font-size: .95rem;
    }
    .lote-op-stat__icon--green { background: #dcfce7; color: #16a34a; }
    .lote-op-stat__icon--amber { background: #fef3c7; color: #d97706; }
    .lote-op-stat__icon--blue { background: #dbeafe; color: #2563eb; }
    .lote-op-stat__icon--teal { background: #ccfbf1; color: #0d9488; }
    .lote-op-stat__val {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.1;
    }
    .lote-op-stat__lbl {
        font-size: .72rem;
        color: var(--lote-muted);
        font-weight: 600;
        margin-top: .15rem;
    }
    .lote-op-panel__cta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .55rem;
        width: 100%;
        padding: .75rem 1rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: .88rem;
        color: #fff;
        background: linear-gradient(135deg, var(--lote-primary), var(--lote-secondary));
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(44, 85, 48, .22);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .lote-op-panel__cta:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(44, 85, 48, .28);
    }
    .lote-op-panel__cta-arrow {
        font-size: .75rem;
        opacity: .85;
        margin-left: auto;
    }

    /* —— Footer acciones —— */
    .lote-section-card {
        border-radius: 14px;
        border: 1px solid var(--lote-border);
        box-shadow: 0 2px 12px rgba(15, 23, 42, .04);
        margin-bottom: 0;
        background: #fff;
    }
    .btn-action {
        border-radius: 10px;
        padding: .55rem 1.1rem;
        font-weight: 600;
        font-size: .86rem;
    }

    @media (max-width: 575px) {
        .lote-datos-panel__metrics,
        .lote-datos-panel__grid { grid-template-columns: 1fr; }
        .lote-hero__title { font-size: 1.45rem; }
    }

    /* —— Legacy / otras vistas del lote —— */
    .estado-badge { padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: 0.9rem; }
    .info-table td { padding: 10px 0; border-bottom: 1px solid #f1f3f4; }
    .info-table td:first-child { font-weight: 600; color: #495057; width: 40%; }
    #map { height: 420px; width: 100%; border-radius: 12px; border: 1px solid var(--lote-border); }
    .timeline { position: relative; padding: 20px 0; }
    .timeline::before {
        content: ''; position: absolute; left: 20px; top: 0; bottom: 0; width: 3px;
        background: linear-gradient(to bottom, var(--lote-primary), #e9ecef);
    }
    .timeline-item { position: relative; padding-left: 60px; padding-bottom: 25px; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-icon {
        position: absolute; left: 5px; width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; z-index: 1;
    }
    .timeline-icon.success { background: #28a745; }
    .timeline-icon.info { background: #17a2b8; }
    .timeline-icon.warning { background: #ffc107; color: #1a252f; }
    .timeline-icon.primary { background: var(--lote-primary); }
    .timeline-content { background: #f8f9fc; border-radius: 10px; padding: 15px; border-left: 3px solid #dee2e6; }
    .timeline-content.success { border-left-color: #28a745; }
    .timeline-content.info { border-left-color: #17a2b8; }
    .timeline-content.warning { border-left-color: #ffc107; }
    .timeline-content.primary { border-left-color: var(--lote-primary); }
    .timeline-date { font-size: 0.8rem; color: #6c757d; margin-bottom: 5px; }
    .timeline-title { font-weight: 600; color: #1a252f; margin-bottom: 5px; }
    .timeline-desc { font-size: 0.9rem; color: #495057; margin: 0; }
    .timeline-user { font-size: 0.8rem; color: #6c757d; margin-top: 5px; }
    .empty-timeline { text-align: center; padding: 40px; color: #6c757d; }
    .empty-timeline i { font-size: 3rem; margin-bottom: 15px; opacity: 0.5; }

    /* Compat: small-box en trazabilidad si se usa */
    .small-box {
        border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05); transition: transform 0.3s ease; margin-bottom: 20px;
    }
    .small-box:hover { transform: translateY(-2px); }
    .small-box .icon { font-size: 70px !important; }
    .small-box-green { background: linear-gradient(135deg, #28a745, #34ce57) !important; }
    .small-box-blue { background: linear-gradient(135deg, #17a2b8, #20c997) !important; }
    .small-box-yellow { background: linear-gradient(135deg, #ffc107, #ffca2c) !important; }
    .small-box-red { background: linear-gradient(135deg, #dc3545, #e74a3b) !important; }
    .lote-section-nav .nav-link {
        border-radius: 8px; font-weight: 600; color: #6c757d; padding: 10px 18px; margin-right: 6px;
    }
    .lote-section-nav .nav-link.active {
        background: var(--lote-primary); color: #fff;
    }
</style>
