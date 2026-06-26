<style>
.inicio-operario-planta {
    --iop-accent: #15803d;
    --iop-accent-soft: #ecfdf5;
    --iop-border: rgba(22, 163, 74, .14);
    --inicio-border: var(--iop-border);
    --inicio-hero-bg: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 38%, #f8fafc 100%);
    --inicio-title: #14532d;
    --inicio-icon-bg: linear-gradient(135deg, #16a34a, #22c55e);
}

.iop-hero-user {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    min-width: 0;
}
.iop-hero-avatar {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #166534, #22c55e);
    color: #fff;
    font-weight: 800;
    font-size: 1.1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(22, 163, 74, .28);
    flex-shrink: 0;
}
.iop-hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    margin-top: .55rem;
}
.iop-hero-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .65rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, .82);
    border: 1px solid var(--iop-border);
    font-size: .76rem;
    font-weight: 600;
    color: #166534;
}

.iop-card {
    border: 1px solid #e2ebe3;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.iop-card__head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: .65rem;
    padding: 1rem 1.2rem;
    background: #fafcfb;
    border-bottom: 1px solid #eef2f0;
}
.iop-card__head h2 {
    font-size: 1rem;
    font-weight: 800;
    color: #14532d;
    margin: 0;
}
.iop-card__head p {
    font-size: .8rem;
    color: #64748b;
    margin: .15rem 0 0;
}
.iop-card__body { padding: 0; }
.iop-card__body--padded { padding: 1rem 1.2rem 1.15rem; }

.iop-tarea {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .95rem 1.2rem;
    border-bottom: 1px solid #f1f5f3;
    color: inherit;
    text-decoration: none !important;
    transition: background .15s ease;
}
.iop-tarea:hover { background: #f8fbf8; }
.iop-tarea:last-child { border-bottom: 0; }
.iop-tarea__icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    color: #c2410c;
    border: 1px solid #fed7aa;
}
.iop-tarea__main { flex: 1; min-width: 0; }
.iop-tarea__main strong {
    display: block;
    color: #1f2937;
    font-size: .95rem;
    margin-bottom: .15rem;
}
.iop-tarea__meta {
    font-size: .8rem;
    color: #64748b;
}
.iop-tarea__cta {
    font-size: .78rem;
    font-weight: 700;
    color: var(--iop-accent);
    white-space: nowrap;
}

.iop-al-dia {
    text-align: center;
    padding: 2rem 1.25rem;
    background: linear-gradient(180deg, #f0fdf4, #fff);
}
.iop-al-dia__icon {
    width: 64px;
    height: 64px;
    margin: 0 auto .85rem;
    border-radius: 50%;
    background: #dcfce7;
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}
.iop-al-dia h3 {
    font-size: 1.05rem;
    font-weight: 800;
    color: #14532d;
    margin-bottom: .35rem;
}
.iop-al-dia p {
    color: #64748b;
    font-size: .88rem;
    margin: 0;
    max-width: 320px;
    margin-inline: auto;
}

.iop-accesos {
    display: grid;
    gap: .65rem;
    padding: 1rem 1.2rem 1.15rem;
}
.iop-acceso {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem .95rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none !important;
    color: #334155;
    transition: border-color .15s, box-shadow .15s, transform .15s;
    background: #fff;
}
.iop-acceso:hover {
    border-color: #86efac;
    box-shadow: 0 4px 14px rgba(22, 163, 74, .1);
    transform: translateY(-1px);
    color: #14532d;
}
.iop-acceso__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--iop-accent-soft);
    color: var(--iop-accent);
    flex-shrink: 0;
}
.iop-acceso__lbl {
    display: block;
    font-size: .9rem;
    font-weight: 700;
    color: #1e293b;
}
.iop-acceso__sub {
    display: block;
    font-size: .74rem;
    color: #94a3b8;
    margin-top: .1rem;
}

.iop-lotes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: .85rem;
    padding: 1rem 1.2rem 1.15rem;
}
.iop-lote {
    display: block;
    border: 1px solid #e2ebe3;
    border-radius: 14px;
    padding: 1rem;
    text-decoration: none !important;
    color: inherit;
    background: #fff;
    transition: border-color .15s, box-shadow .15s, transform .15s;
    height: 100%;
}
.iop-lote:hover {
    border-color: #86efac;
    box-shadow: 0 8px 22px rgba(22, 163, 74, .1);
    transform: translateY(-2px);
}
.iop-lote__code {
    display: inline-block;
    font-family: ui-monospace, monospace;
    font-size: .72rem;
    font-weight: 700;
    color: #166534;
    background: #ecfdf5;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    padding: .2rem .5rem;
    margin-bottom: .55rem;
}
.iop-lote__name {
    display: block;
    font-size: .92rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.35;
    margin-bottom: .35rem;
}
.iop-lote__line {
    display: block;
    font-size: .78rem;
    color: #64748b;
}
.iop-lote__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: .85rem;
    padding-top: .75rem;
    border-top: 1px dashed #e2e8f0;
    font-size: .78rem;
    font-weight: 700;
    color: var(--iop-accent);
}

.iop-empty {
    text-align: center;
    padding: 2.5rem 1.25rem;
    color: #64748b;
}
.iop-empty i {
    font-size: 2rem;
    opacity: .35;
    margin-bottom: .75rem;
    display: block;
}

@media (max-width: 991px) {
    .iop-hero-user { flex-direction: column; align-items: flex-start; }
}
</style>
