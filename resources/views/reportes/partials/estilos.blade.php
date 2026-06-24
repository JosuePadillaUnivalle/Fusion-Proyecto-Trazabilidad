<style>
/* Paleta ejecutiva por reporte */
.rpt-accent--forest, .rpt-card--forest {
    --rpt-accent: #2d6a4f; --rpt-accent-dark: #1b4332; --rpt-accent-light: #e8f3ee;
    --rpt-a1: #1b4332; --rpt-a2: #2d6a4f; --rpt-a3: #40916c; --rpt-a4: #52b788;
    --rpt-chart: #2d6a4f, #40916c, #52b788, #74c69d, #1b4332;
}
.rpt-accent--teal, .rpt-card--teal {
    --rpt-accent: #1a6b7c; --rpt-accent-dark: #0f4c5c; --rpt-accent-light: #e6f4f7;
    --rpt-a1: #0f4c5c; --rpt-a2: #1a6b7c; --rpt-a3: #2a8fa3; --rpt-a4: #3eb5c8;
    --rpt-chart: #0f4c5c, #1a6b7c, #2a8fa3, #3eb5c8, #5cc6d6;
}
.rpt-accent--navy, .rpt-card--navy {
    --rpt-accent: #2c5282; --rpt-accent-dark: #1e3a5f; --rpt-accent-light: #e8eef5;
    --rpt-a1: #1e3a5f; --rpt-a2: #2c5282; --rpt-a3: #3d6ba8; --rpt-a4: #5a8fd4;
    --rpt-chart: #1e3a5f, #2c5282, #3d6ba8, #5a8fd4, #7aaee0;
}
.rpt-accent--bronze, .rpt-card--bronze {
    --rpt-accent: #92610e; --rpt-accent-dark: #6b4709; --rpt-accent-light: #f8f0e3;
    --rpt-a1: #6b4709; --rpt-a2: #92610e; --rpt-a3: #b07d14; --rpt-a4: #c9952a;
    --rpt-chart: #6b4709, #92610e, #b07d14, #c9952a, #d4a84b;
}
.rpt-accent--wine, .rpt-card--wine {
    --rpt-accent: #9b3848; --rpt-accent-dark: #6b2737; --rpt-accent-light: #f9ecef;
    --rpt-a1: #6b2737; --rpt-a2: #9b3848; --rpt-a3: #b84d5f; --rpt-a4: #d16b7c;
    --rpt-chart: #6b2737, #9b3848, #b84d5f, #d16b7c, #e08a98;
}
.rpt-accent--indigo, .rpt-card--indigo {
    --rpt-accent: #4338ca; --rpt-accent-dark: #312e81; --rpt-accent-light: #eef0ff;
    --rpt-a1: #312e81; --rpt-a2: #4338ca; --rpt-a3: #5b52e0; --rpt-a4: #7c75e8;
    --rpt-chart: #312e81, #4338ca, #5b52e0, #7c75e8, #9b95ef;
}

.rpt-hub { --rpt-gap: 1.25rem; }
.rpt-hub__intro {
    display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between;
    gap: 1rem; margin-bottom: 1.5rem;
}
.rpt-hub__intro h2 { font-size: 1.35rem; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -.02em; }
.rpt-hub__intro p { font-size: .9rem; color: #64748b; margin: .35rem 0 0; max-width: 42rem; line-height: 1.5; }
.rpt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.25rem;
}
.rpt-card {
    display: flex; flex-direction: column;
    background: #fff; border: 1px solid #e2e8f0; border-radius: .75rem;
    border-top: 3px solid var(--rpt-accent, #334155);
    padding: 1.35rem 1.4rem; text-decoration: none !important; color: inherit;
    transition: border-color .18s, box-shadow .18s, transform .14s;
    min-height: 168px;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
}
.rpt-card:hover {
    border-color: var(--rpt-accent, #94a3b8);
    border-top-color: var(--rpt-accent, #94a3b8);
    box-shadow: 0 8px 24px rgba(15,23,42,.1); transform: translateY(-2px);
}
.rpt-card__head { display: flex; align-items: flex-start; gap: .85rem; margin-bottom: .5rem; }
.rpt-card__icon {
    width: 2.75rem; height: 2.75rem; border-radius: .6rem; display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
    background: var(--rpt-accent-light, #f8fafc); color: var(--rpt-accent, #334155); border: 1px solid transparent;
}
.rpt-card__title { font-size: 1.02rem; font-weight: 700; color: #0f172a; line-height: 1.3; padding-top: .15rem; }
.rpt-card__sub { font-size: .84rem; color: #64748b; line-height: 1.45; margin: 0; flex: 1; }
.rpt-card__foot {
    display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 1rem;
    font-size: .78rem; color: #94a3b8; border-top: 1px solid #f1f5f9;
}
.rpt-card__preview { font-weight: 600; color: var(--rpt-accent, #475569); }
.rpt-card__cta { color: #64748b; font-weight: 500; }
.rpt-card:hover .rpt-card__cta { color: var(--rpt-accent-dark, #0f172a); }

.rpt-page .rpt-breadcrumb { font-size: .75rem; margin-bottom: .65rem; }
.rpt-page .rpt-breadcrumb a { color: var(--rpt-accent, #475569); }
.rpt-page .rpt-header { margin-bottom: .85rem; }
.rpt-page .rpt-header h2 { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
.rpt-page .rpt-header h2 .rpt-header__icon {
    color: var(--rpt-accent, #64748b);
    background: var(--rpt-accent-light, #f8fafc);
    width: 2rem; height: 2rem; border-radius: .45rem;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .9rem; vertical-align: middle;
}
.rpt-page .rpt-header p { font-size: .84rem; color: #64748b; margin: .25rem 0 0; }

.rpt-filtros {
    background: var(--rpt-accent-light, #f8fafc);
    border: 1px solid #e2e8f0; border-left: 3px solid var(--rpt-accent, #cbd5e1);
    border-radius: .55rem; padding: .85rem .95rem; margin-bottom: .85rem;
}
.rpt-filtros__row { display: flex; flex-wrap: wrap; gap: .65rem; align-items: flex-end; }
.rpt-filtros__grow { flex: 1 1 200px; min-width: 180px; }
.rpt-filtros__check { flex: 0 0 auto; }
.rpt-filtros label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; margin-bottom: .2rem; display: block; }
.rpt-filtros .form-control { font-size: .82rem; height: calc(1.5em + .55rem + 2px); padding: .25rem .5rem; }
.rpt-filtros__actions { display: flex; flex-wrap: wrap; gap: .4rem; margin-left: auto; align-items: center; }
.rpt-btn-accent {
    background: var(--rpt-accent) !important; border-color: var(--rpt-accent) !important; color: #fff !important;
}
.rpt-btn-accent:hover { background: var(--rpt-accent-dark) !important; border-color: var(--rpt-accent-dark) !important; color: #fff !important; }
.rpt-btn-outline-accent {
    color: var(--rpt-accent) !important; border-color: var(--rpt-accent) !important; background: #fff !important;
}
.rpt-btn-outline-accent:hover { background: var(--rpt-accent-light) !important; color: var(--rpt-accent-dark) !important; }

.rpt-kpis {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: .65rem; margin-bottom: .85rem;
}
.rpt-kpi {
    background: #fff; border: 1px solid #e2e8f0; border-radius: .55rem;
    padding: .75rem .85rem; border-left: 3px solid var(--rpt-accent, #334155);
}
.rpt-kpi:nth-child(1) { border-left-color: var(--rpt-a1, var(--rpt-accent)); }
.rpt-kpi:nth-child(2) { border-left-color: var(--rpt-a2, var(--rpt-accent)); }
.rpt-kpi:nth-child(3) { border-left-color: var(--rpt-a3, var(--rpt-accent)); }
.rpt-kpi:nth-child(4) { border-left-color: var(--rpt-a4, var(--rpt-accent)); }
.rpt-kpi__val { font-size: 1.25rem; font-weight: 700; color: var(--rpt-accent-dark, #0f172a); line-height: 1.1; }
.rpt-kpi__lbl { font-size: .72rem; color: #64748b; margin-top: .2rem; line-height: 1.3; }

.rpt-panel {
    background: #fff; border: 1px solid #e2e8f0; border-radius: .55rem;
    margin-bottom: .85rem; overflow: hidden;
}
.rpt-panel__head {
    padding: .6rem .8rem; border-bottom: 1px solid #f1f5f9;
    font-size: .8rem; font-weight: 700; color: var(--rpt-accent-dark, #334155);
    background: var(--rpt-accent-light, #f8fafc);
    border-left: 3px solid var(--rpt-accent, #cbd5e1);
}
.rpt-panel__head i { color: var(--rpt-accent, #64748b); }
.rpt-panel__body { padding: .75rem; }
.rpt-panel__body--table { padding: 0; }
.rpt-chart-wrap { position: relative; height: 280px; }

.rpt-page .table { font-size: .8rem; margin: 0; }
.rpt-page .table thead th {
    font-size: .66rem; text-transform: uppercase; letter-spacing: .04em;
    color: #64748b; border-top: 0; white-space: nowrap; padding: .45rem .55rem;
}
.rpt-page .table td { vertical-align: middle; padding: .4rem .55rem; }

.rpt-back { font-size: .78rem; margin-top: .5rem; }
.rpt-sin-resultados { font-size: .84rem; color: #475569; }
.rpt-modal-accent .modal-header { background: var(--rpt-accent-dark, #1e293b) !important; }

@media print {
    .no-print, .ag-sidebar, .ag-topbar, .rpt-filtros, .rpt-back { display: none !important; }
    .ag-main { margin: 0 !important; }
}
</style>
