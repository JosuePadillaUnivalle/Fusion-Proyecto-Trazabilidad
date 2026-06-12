<style>
.inicio-dash-hero {
    border-radius: 16px;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
    border: 1px solid var(--inicio-border, rgba(37, 99, 235, .15));
    background: var(--inicio-hero-bg, linear-gradient(135deg, #eff6ff 0%, #dbeafe 42%, #f8fafc 100%));
}
.inicio-dash-hero__row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    position: relative;
    z-index: 1;
}
.inicio-dash-hero__title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--inicio-title, #1e3a8a);
    margin-bottom: .2rem;
}
.inicio-dash-hero__title i {
    display: inline-flex;
    align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--inicio-icon-bg, linear-gradient(135deg, #2563eb, #3b82f6));
    color: #fff;
    font-size: .95rem;
    margin-right: .55rem;
    box-shadow: 0 4px 12px rgba(37, 99, 235, .25);
    vertical-align: middle;
}
.inicio-dash-hero__sub { color: #4b5563; font-size: .9rem; margin: 0; }
.inicio-dash-link-panel {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .9rem;
    border-radius: 999px;
    font-size: .8rem;
    font-weight: 700;
    text-decoration: none !important;
    border: 1px solid var(--inicio-border, #bfdbfe);
    color: var(--inicio-title, #1d4ed8);
    background: #fff;
    transition: all .15s ease;
}
.inicio-dash-link-panel:hover {
    background: var(--inicio-title, #1d4ed8);
    color: #fff;
    border-color: transparent;
}
.inicio-chart-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 8px 28px rgba(15, 23, 42, .08);
    margin-bottom: 1.25rem;
    background: #fff;
    height: 100%;
}
.inicio-chart-card__head {
    background: #fafbfc;
    border-bottom: 1px solid #e8edf2;
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
    border-radius: 14px;
    padding: .95rem 1rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(15, 23, 42, .1);
}
.inicio-kpi__val { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.inicio-kpi__lbl { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; opacity: .92; margin: .15rem 0 0; }
.inicio-kpi__icon { position: absolute; right: 10px; top: 10px; font-size: 1.4rem; opacity: .22; }
</style>
