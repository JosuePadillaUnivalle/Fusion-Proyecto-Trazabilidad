<style>
/* Layout inicio por rol — visual ejecutivo en estilos-dash-ejecutivo */
.inicio-dash-hero {
    border-radius: 8px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    position: relative;
    overflow: visible;
    border: 1px solid #dee2e6;
    background: #fff;
    border-top: 3px solid var(--inicio-accent, #2563eb);
}
.inicio-dash-hero__row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
}
.inicio-dash-hero__title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: .2rem;
}
.inicio-dash-hero__title i {
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
.inicio-dash-hero__sub { color: #64748b; font-size: .88rem; margin: 0; }
.inicio-dash-link-panel {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .9rem;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none !important;
    border: 1px solid #e2e8f0;
    color: #334155;
    background: #f8fafc;
    transition: all .15s ease;
}
.inicio-dash-link-panel:hover {
    background: #f1f5f9;
    color: #1e293b;
    border-color: #cbd5e1;
}
.inicio-chart-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: none;
    margin-bottom: 1.25rem;
    background: #fff;
    height: 100%;
}
.inicio-chart-card__head {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: .9rem 1.25rem;
}
.inicio-chart-card__head h3 {
    font-size: .92rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.inicio-chart-wrap { position: relative; height: 280px; padding: 1rem 1.15rem 1.1rem; }
.inicio-chart-wrap--sm { height: 240px; }
.inicio-kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.inicio-kpi {
    border-radius: 8px;
    padding: .95rem 1rem;
    color: #1e293b;
    background: #fff;
    border: 1px solid #dee2e6;
    position: relative;
    overflow: hidden;
    box-shadow: none;
}
.inicio-kpi__val { font-size: 1.45rem; font-weight: 700; line-height: 1.1; color: #1e293b; }
.inicio-kpi__lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; margin: .2rem 0 0; }
.inicio-kpi__icon {
    position: absolute; right: 10px; top: 10px;
    font-size: 2.4rem; line-height: 1;
    color: rgba(100, 116, 139, 0.16);
}
</style>
