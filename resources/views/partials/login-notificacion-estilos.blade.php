@once
<style>
/* ═══ Notificaciones al iniciar sesión ═══ */
.login-notif-scrim {
    position: fixed;
    inset: 0;
    z-index: 1040;
    background: rgba(12, 28, 48, 0.78);
    backdrop-filter: blur(14px) saturate(120%);
    -webkit-backdrop-filter: blur(14px) saturate(120%);
    opacity: 0;
    visibility: hidden;
    transition: opacity .28s ease, visibility .28s ease;
}
.login-notif-scrim.is-visible { opacity: 1; visibility: visible; }

.login-notif-modal-root {
    z-index: 1050 !important;
}
.login-notif-modal-root .modal-dialog {
    max-width: 520px;
    transform: scale(0.94) translateY(12px);
    transition: transform .32s cubic-bezier(.22, 1, .36, 1);
}
.login-notif-modal-root.show .modal-dialog,
.login-notif-modal-root.in .modal-dialog {
    transform: scale(1) translateY(0);
}

body.login-notif-modal-open .ag-layout {
    filter: blur(5px) brightness(0.78);
    pointer-events: none;
    user-select: none;
    transition: filter .25s ease;
}

.login-notif-scrim,
.login-notif-modal-root {
    filter: none !important;
    pointer-events: auto;
}
.login-notif-modal-root .modal-dialog,
.login-notif-modal-root .modal-content {
    filter: none !important;
}

.login-notif-modal {
    border-radius: 18px;
    overflow: hidden;
    border: none;
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.08),
        0 28px 56px rgba(15, 23, 42, 0.28),
        0 8px 20px rgba(15, 23, 42, 0.12);
    position: relative;
}
.login-notif-modal__accent {
    height: 5px;
    background: linear-gradient(
        90deg,
        var(--login-notif-accent-a, #059669) 0%,
        var(--login-notif-accent-b, #34d399) 45%,
        var(--login-notif-accent-c, #6ee7b7) 100%
    );
}
.login-notif-modal__head {
    background: linear-gradient(
        135deg,
        var(--login-notif-head-a, #0c1c30) 0%,
        var(--login-notif-head-b, #1a3a5c) 55%,
        var(--login-notif-head-c, #234a72) 100%
    );
    color: #fff;
    padding: 1.15rem 1.35rem 1.05rem;
    border-bottom: none;
    position: relative;
    overflow: hidden;
}
.login-notif-modal__head::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -8%;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
    pointer-events: none;
}
.login-notif-modal__head .close {
    color: #fff;
    opacity: .9;
    text-shadow: none;
    position: absolute;
    right: 1rem;
    top: 1rem;
    z-index: 2;
    font-size: 1.35rem;
}
.login-notif-modal__head-inner {
    position: relative;
    z-index: 1;
    padding-right: 1.75rem;
}
.login-notif-modal__badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .7rem;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    background: var(--login-notif-badge-bg, #c9a227);
    color: var(--login-notif-badge-fg, #1a1a1a);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    margin-bottom: .55rem;
}
.login-notif-modal__head .modal-title {
    font-size: 1.12rem;
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.3;
}
.login-notif-modal__body {
    padding: 1.2rem 1.35rem 1.1rem;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
}
.login-notif-modal__highlight {
    display: flex;
    align-items: flex-start;
    gap: .9rem;
    padding: 1rem 1.05rem;
    border-radius: 14px;
    background: var(--login-notif-highlight-bg, linear-gradient(135deg, #fff9eb 0%, #fef3c7 100%));
    border: 1px solid var(--login-notif-highlight-border, #e8c547);
    border-left: 4px solid var(--login-notif-accent-a, #c9a227);
    margin-bottom: 1.05rem;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.login-notif-modal__highlight-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, var(--login-notif-head-b, #1a3a5c), var(--login-notif-head-a, #0c1c30));
    color: var(--login-notif-highlight-icon, #e8c547);
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
}
.login-notif-modal__highlight-title {
    color: #0f172a;
    font-size: .96rem;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: .2rem;
}
.login-notif-modal__highlight-sub {
    color: #64748b;
    font-size: .82rem;
    line-height: 1.45;
}
.login-notif-modal__lista {
    display: flex;
    flex-direction: column;
    gap: .65rem;
    margin: 0;
    padding: 0;
    list-style: none;
}
.login-notif-modal__item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .85rem;
    padding: .9rem 1rem;
    border-radius: 13px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid var(--login-notif-item-accent, #1a3a5c);
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.login-notif-modal__item:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    border-color: var(--login-notif-highlight-border, #cbd5e1);
}
.login-notif-modal__item-body { flex: 1; min-width: 0; }
.login-notif-modal__codigo {
    display: block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    font-size: .9rem;
    color: var(--login-notif-codigo, #0f172a);
    margin-bottom: .2rem;
    letter-spacing: -.01em;
}
.login-notif-modal__meta {
    display: block;
    font-size: .8rem;
    color: #64748b;
    line-height: 1.4;
}
.login-notif-modal__qty {
    display: inline-block;
    margin-top: .4rem;
    padding: .18rem .55rem;
    font-size: .72rem;
    font-weight: 600;
    border-radius: 999px;
    background: var(--login-notif-qty-bg, #f1f5f9);
    border: 1px solid var(--login-notif-qty-border, #e2e8f0);
    color: #475569;
}
.login-notif-modal__cta {
    white-space: nowrap;
    font-weight: 700;
    border-radius: 10px;
    padding: .45rem .85rem;
    font-size: .78rem;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
    transition: transform .12s ease, box-shadow .12s ease;
}
.login-notif-modal__cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16);
}
.login-notif-modal__foot {
    background: #fff;
    border-top: 1px solid #e2e8f0;
    padding: .95rem 1.35rem;
}
.login-notif-modal__btn-secondary {
    border-radius: 10px;
    font-weight: 600;
    font-size: .82rem;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    padding: .45rem .9rem;
    transition: background .15s ease, border-color .15s ease;
}
.login-notif-modal__btn-secondary:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #334155;
    text-decoration: none;
}
.login-notif-modal__btn-cerrar {
    border-radius: 10px;
    font-weight: 700;
    font-size: .82rem;
    padding: .45rem 1.25rem;
    background: linear-gradient(135deg, var(--login-notif-head-b, #1a3a5c), var(--login-notif-head-a, #0c1c30));
    border: none;
    color: #fff;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.2);
    transition: transform .12s ease, box-shadow .12s ease;
}
.login-notif-modal__btn-cerrar:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.25);
    color: #fff;
}

/* Tema mayorista (ámbar) */
.login-notif-modal--mayorista {
    --login-notif-accent-a: #d97706;
    --login-notif-accent-b: #fbbf24;
    --login-notif-accent-c: #fcd34d;
    --login-notif-head-a: #78350f;
    --login-notif-head-b: #b45309;
    --login-notif-head-c: #d97706;
    --login-notif-badge-bg: #fde68a;
    --login-notif-badge-fg: #78350f;
    --login-notif-highlight-bg: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    --login-notif-highlight-border: #fde68a;
    --login-notif-highlight-icon: #fde68a;
    --login-notif-item-accent: #d97706;
    --login-notif-codigo: #92400e;
    --login-notif-qty-bg: #fffbeb;
    --login-notif-qty-border: #fde68a;
}
.login-notif-modal--mayorista .login-notif-modal__cta {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border: none;
    color: #1c1917;
}
.login-notif-modal--mayorista .login-notif-modal__cta:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
}

/* Tema transportista (verde) */
.login-notif-modal--transportista {
    --login-notif-accent-a: #16a34a;
    --login-notif-accent-b: #4ade80;
    --login-notif-accent-c: #86efac;
    --login-notif-head-a: #14532d;
    --login-notif-head-b: #166534;
    --login-notif-head-c: #15803d;
    --login-notif-badge-bg: #bbf7d0;
    --login-notif-badge-fg: #14532d;
    --login-notif-highlight-bg: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    --login-notif-highlight-border: #86efac;
    --login-notif-highlight-icon: #bbf7d0;
    --login-notif-item-accent: #16a34a;
    --login-notif-codigo: #14532d;
    --login-notif-qty-bg: #f0fdf4;
    --login-notif-qty-border: #bbf7d0;
}
.login-notif-modal--transportista .login-notif-modal__cta {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border: none;
    color: #fff;
}
.login-notif-modal--transportista .login-notif-modal__cta:hover {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
}

/* Tema operario planta (azul/dorado) */
.login-notif-modal--planta {
    --login-notif-accent-a: #c9a227;
    --login-notif-accent-b: #e8c547;
    --login-notif-accent-c: #f5d76e;
    --login-notif-head-a: #0c1c30;
    --login-notif-head-b: #1a3a5c;
    --login-notif-head-c: #234a72;
    --login-notif-badge-bg: #c9a227;
    --login-notif-badge-fg: #1a1a1a;
    --login-notif-highlight-bg: linear-gradient(135deg, #f8fafc 0%, #e8eef5 100%);
    --login-notif-highlight-border: #c9d4e0;
    --login-notif-highlight-icon: #e8c547;
    --login-notif-item-accent: #1a3a5c;
    --login-notif-codigo: #0c1c30;
}
.login-notif-modal--planta .login-notif-modal__cta {
    background: linear-gradient(135deg, #234a72, #1a3a5c);
    border: none;
    color: #fff;
}
.login-notif-modal--planta .login-notif-modal__cta:hover {
    background: linear-gradient(135deg, #1a3a5c, #0c1c30);
    color: #fff;
}

/* Tema agricultor (verde tierra) */
.login-notif-modal--agricultor {
    --login-notif-accent-a: #65a30d;
    --login-notif-accent-b: #84cc16;
    --login-notif-accent-c: #a3e635;
    --login-notif-head-a: #365314;
    --login-notif-head-b: #4d7c0f;
    --login-notif-head-c: #65a30d;
    --login-notif-badge-bg: #d9f99d;
    --login-notif-badge-fg: #365314;
    --login-notif-highlight-bg: linear-gradient(135deg, #f7fee7 0%, #ecfccb 100%);
    --login-notif-highlight-border: #bef264;
    --login-notif-highlight-icon: #d9f99d;
    --login-notif-item-accent: #65a30d;
    --login-notif-codigo: #365314;
    --login-notif-qty-bg: #f7fee7;
    --login-notif-qty-border: #d9f99d;
}
.login-notif-modal--agricultor .login-notif-modal__cta {
    background: linear-gradient(135deg, #84cc16, #65a30d);
    border: none;
    color: #fff;
}
.login-notif-modal--agricultor .login-notif-modal__cta:hover {
    background: linear-gradient(135deg, #65a30d, #4d7c0f);
    color: #fff;
}

/* Tema jefe agricultor (ámbar tierra) */
.login-notif-modal--jefe-agricultor {
    --login-notif-accent-a: #b45309;
    --login-notif-accent-b: #f59e0b;
    --login-notif-accent-c: #fbbf24;
    --login-notif-head-a: #78350f;
    --login-notif-head-b: #92400e;
    --login-notif-head-c: #b45309;
    --login-notif-badge-bg: #fde68a;
    --login-notif-badge-fg: #78350f;
    --login-notif-highlight-bg: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    --login-notif-highlight-border: #fcd34d;
    --login-notif-highlight-icon: #fde68a;
    --login-notif-item-accent: #d97706;
    --login-notif-codigo: #92400e;
}
.login-notif-modal--jefe-agricultor .login-notif-modal__cta {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border: none;
    color: #1c1917;
}
.login-notif-modal--jefe-agricultor .login-notif-modal__cta:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
}

/* Tema punto de venta / minorista (violeta) */
.login-notif-modal--minorista {
    --login-notif-accent-a: #7c3aed;
    --login-notif-accent-b: #a78bfa;
    --login-notif-accent-c: #c4b5fd;
    --login-notif-head-a: #4c1d95;
    --login-notif-head-b: #6d28d9;
    --login-notif-head-c: #7c3aed;
    --login-notif-badge-bg: #ddd6fe;
    --login-notif-badge-fg: #4c1d95;
    --login-notif-highlight-bg: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    --login-notif-highlight-border: #c4b5fd;
    --login-notif-highlight-icon: #ddd6fe;
    --login-notif-item-accent: #7c3aed;
    --login-notif-codigo: #5b21b6;
    --login-notif-qty-bg: #f5f3ff;
    --login-notif-qty-border: #ddd6fe;
}
.login-notif-modal--minorista .login-notif-modal__cta {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    border: none;
    color: #fff;
}
.login-notif-modal--minorista .login-notif-modal__cta:hover {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: #fff;
}
</style>
@endonce
