<style>
.role-panel-hero {
    border-radius: 16px;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
    border: 1px solid var(--rp-border, rgba(22, 163, 74, .15));
    background: var(--rp-hero-bg, linear-gradient(135deg, #ecfdf5 0%, #d1fae5 42%, #f8fafc 100%));
}
.role-panel-hero::after {
    content: '';
    position: absolute;
    top: -50px; right: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: var(--rp-glow, radial-gradient(circle, rgba(34, 197, 94, .16) 0%, transparent 70%));
    pointer-events: none;
}
.role-panel-hero__title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--rp-title, #14532d);
    margin-bottom: .2rem;
}
.role-panel-hero__title i {
    display: inline-flex;
    align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--rp-icon-bg, linear-gradient(135deg, #16a34a, #22c55e));
    color: #fff;
    font-size: .95rem;
    margin-right: .55rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    vertical-align: middle;
}
.role-panel-hero__sub { color: #4b5563; font-size: .9rem; margin: 0; }

.dash-filtros {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .75rem 1rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
}
.dash-filtros__inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .75rem;
}
.dash-filtros__label {
    display: flex;
    align-items: center;
    gap: .45rem;
    font-weight: 700;
    font-size: .85rem;
    color: #1e293b;
    white-space: nowrap;
}
.dash-filtros__label i { color: #2563eb; }
.dash-filtros__fields {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
    flex: 1;
}
.dash-filtros__select { min-width: 150px; max-width: 200px; border-radius: 8px; }
.dash-filtros__btn { border-radius: 8px; font-weight: 600; }

.role-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.role-metric {
    border-radius: 14px;
    padding: 1rem 1.1rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(15, 23, 42, .1);
}
.role-metric__val { font-size: 1.6rem; font-weight: 800; line-height: 1; margin-bottom: .15rem; }
.role-metric__lbl { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; opacity: .92; margin: 0; }
.role-metric__sub { font-size: .68rem; opacity: .8; margin-top: .15rem; }
.role-metric__icon { position: absolute; right: 10px; top: 10px; font-size: 1.5rem; opacity: .22; }

.role-block-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 8px 28px rgba(15, 23, 42, .08);
    overflow: hidden;
    background: #fff;
    margin-bottom: 1.25rem;
}
.role-block-card__head {
    background: #fafbfc;
    border-bottom: 1px solid #e8edf2;
    padding: .9rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
}
.role-block-card__head h3 {
    font-size: .95rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.role-block-card__head .btn { border-radius: 8px; font-size: .78rem; }

.role-acc-card { border: 0; border-radius: 16px; box-shadow: 0 8px 28px rgba(15, 23, 42, .08); overflow: hidden; background: #fff; }
.role-acc-card__head { background: #fafbfc; border-bottom: 1px solid #e8edf2; padding: .9rem 1.25rem; }
.role-acc-card__head h3 { font-size: .95rem; font-weight: 700; color: #1e293b; margin: 0; }
.role-acc-grupo { padding: 1rem 1.25rem 1.1rem; }
.role-acc-grupo + .role-acc-grupo { border-top: 1px dashed #e2e8f0; padding-top: 1.1rem; }
.role-acc-grupo__titulo {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #94a3b8; margin-bottom: .65rem;
}
.role-acc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .65rem;
}
.role-acc-tile {
    display: flex; align-items: flex-start; gap: .7rem;
    padding: .85rem .95rem;
    border: 1px solid #e2e8f0; border-radius: 12px; background: #fff;
    text-decoration: none !important; color: #334155;
    transition: border-color .15s, box-shadow .15s, transform .15s;
}
.role-acc-tile:hover {
    border-color: var(--rp-tile-hover, #86efac);
    box-shadow: 0 4px 14px rgba(0,0,0,.08);
    transform: translateY(-1px);
    color: var(--rp-title, #14532d);
}
.role-acc-tile__icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0; color: #fff;
}
.role-acc-tile__icon--log { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.role-acc-tile__icon--prod { background: linear-gradient(135deg, #15803d, #22c55e); }
.role-acc-tile__icon--com { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
.role-acc-tile__icon--adm { background: linear-gradient(135deg, #475569, #64748b); }
.role-acc-tile__icon--warn { background: linear-gradient(135deg, #c2410c, #f59e0b); }
.role-acc-tile__icon--trans { background: linear-gradient(135deg, #ea580c, #f59e0b); }
.role-acc-tile__lbl { font-size: .88rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.role-acc-tile__sub { font-size: .72rem; color: #94a3b8; margin-top: .15rem; display: block; }

.role-x-table thead th {
    background: #f8fafc; border-bottom: 0;
    font-size: .72rem; text-transform: uppercase; letter-spacing: .04em;
    color: #64748b; font-weight: 700;
}
.role-x-table tbody td { vertical-align: middle; font-size: .86rem; }
.role-code {
    font-family: ui-monospace, monospace; font-size: .8rem;
    background: #f1f5f9; padding: .15em .5em; border-radius: 6px; color: #334155;
}
.role-progress-wrap {
    border: 0; border-radius: 14px; box-shadow: 0 4px 16px rgba(15,23,42,.06);
    margin-bottom: 1.25rem; background: #fff; padding: 1rem 1.2rem;
}
</style>
