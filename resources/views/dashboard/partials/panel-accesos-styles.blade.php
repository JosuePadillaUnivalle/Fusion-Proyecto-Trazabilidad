<style>
/* Layout panel por rol — visual ejecutivo en estilos-dash-ejecutivo */
.role-panel-hero {
    border-radius: 8px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    position: relative;
    overflow: visible;
    border: 1px solid #dee2e6;
    background: #fff;
    border-top: 3px solid var(--rp-accent, #2c5530);
}
.role-panel-hero::after { display: none; }
.role-panel-hero__title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: .2rem;
}
.role-panel-hero__title i {
    display: inline-flex;
    align-items: center; justify-content: center;
    width: 34px; height: 34px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
    font-size: .9rem;
    margin-right: .5rem;
    box-shadow: none;
    vertical-align: middle;
}
.role-panel-hero__sub { color: #64748b; font-size: .88rem; margin: 0; }

.dash-filtros {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .75rem 1rem;
    box-shadow: none;
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
.dash-filtros__label i { color: #64748b; }
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
    border-radius: 8px;
    padding: .95rem 1rem;
    color: #1e293b;
    background: #fff;
    border: 1px solid #dee2e6;
    position: relative;
    overflow: hidden;
    box-shadow: none;
}
.role-metric__val { font-size: 1.45rem; font-weight: 700; line-height: 1.1; margin-bottom: .15rem; color: #1e293b; }
.role-metric__lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; margin: 0; }
.role-metric__sub { font-size: .7rem; color: #94a3b8; margin-top: .15rem; }
.role-metric__icon {
    position: absolute; right: 10px; top: 10px;
    font-size: 2.4rem; line-height: 1;
    color: rgba(100, 116, 139, 0.16);
}

.role-block-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: none;
    overflow: hidden;
    background: #fff;
    margin-bottom: 1.25rem;
}
.role-block-card__head {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
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

.role-acc-card { border: 1px solid #dee2e6; border-radius: 8px; box-shadow: none; overflow: hidden; background: #fff; }
.role-acc-card__head { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: .9rem 1.25rem; }
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
    border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;
    text-decoration: none !important; color: #334155;
    transition: border-color .15s, box-shadow .15s;
}
.role-acc-tile:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
    transform: none;
    color: #1e293b;
}
.role-acc-tile__icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .88rem; flex-shrink: 0;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
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
    border: 1px solid #dee2e6; border-radius: 8px; box-shadow: none;
    margin-bottom: 1.25rem; background: #fff; padding: 1rem 1.2rem;
}
.role-progress-wrap .progress-bar { background: #d97706 !important; border-radius: 5px; }
</style>
