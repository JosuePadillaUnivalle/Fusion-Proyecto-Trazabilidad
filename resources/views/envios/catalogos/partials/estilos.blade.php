<style>
.modulo-env.page-cat-log {
    --cat-accent: #2c5530;
    --cat-soft: #e8f4ec;
    --cat-mid: #4a7c59;
}
.modulo-env.page-cat-log .cat-log-layout {
    display: flex;
    gap: 0;
    align-items: flex-start;
}
@media (max-width: 991px) {
    .modulo-env.page-cat-log .cat-log-layout { flex-direction: column; }
}

/* — Panel lateral — */
.modulo-env .cat-log-side {
    width: 228px;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
}
@media (max-width: 991px) {
    .modulo-env .cat-log-side { width: 100%; }
}
.modulo-env .cat-log-side__head {
    padding: 0.85rem 1rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #fff;
    background: linear-gradient(135deg, #1e3a2f 0%, #2c5530 60%, #3d6b4f 100%);
    border-bottom: none;
}
.modulo-env .cat-log-side__head i { opacity: 0.85; margin-right: 0.35rem; }
.modulo-env .cat-log-side__nav {
    list-style: none;
    margin: 0;
    padding: 0.5rem 0.4rem;
    background: #f8fafb;
}
.modulo-env .cat-log-side__link {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.55rem 0.65rem;
    margin-bottom: 2px;
    color: #475569;
    font-size: 0.8125rem;
    text-decoration: none !important;
    border-radius: 0.4rem;
    border-left: 3px solid transparent;
    transition: background 0.12s ease, color 0.12s ease, border-color 0.12s ease;
}
.modulo-env .cat-log-side__icon-wrap {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    flex-shrink: 0;
    background: var(--link-soft, #e8f4ec);
    color: var(--link-accent, #2c5530);
}
.modulo-env .cat-log-side__link:hover {
    background: #fff;
    color: #1e293b;
}
.modulo-env .cat-log-side__link.is-active {
    background: #fff;
    color: var(--link-accent, #2c5530);
    font-weight: 600;
    border-left-color: var(--link-accent, #2c5530);
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
}
.modulo-env .cat-log-side__link.is-active .cat-log-side__icon-wrap {
    background: var(--link-accent, #2c5530);
    color: #fff;
}

/* — Contenido principal — */
.modulo-env .cat-log-main {
    flex: 1;
    min-width: 0;
    margin-left: 1rem;
}
@media (max-width: 991px) {
    .modulo-env .cat-log-main { margin-left: 0; margin-top: 0.75rem; }
}
.modulo-env .cat-log-card.card-modulo-main {
    border-top: 3px solid var(--cat-accent);
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
}
.modulo-env .cat-log-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 1rem 1.15rem;
    background: linear-gradient(90deg, var(--cat-soft) 0%, #fff 62%);
    border-bottom: 2px solid var(--cat-accent);
}
.modulo-env .cat-log-header__left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.modulo-env .cat-log-header__icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: none;
    background: var(--cat-accent);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.modulo-env .cat-log-header__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
}
.modulo-env .cat-log-header__sub {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.1rem;
}
.modulo-env .cat-log-header .btn-success {
    background: var(--cat-accent);
    border-color: var(--cat-accent);
}
.modulo-env .cat-log-header .btn-success:hover {
    background: var(--cat-mid);
    border-color: var(--cat-mid);
}

/* — Tabla — */
.modulo-env .cat-log-table thead th {
    background: var(--cat-soft);
    border-bottom: 2px solid var(--cat-accent);
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--cat-accent);
    font-weight: 700;
    white-space: nowrap;
}
.modulo-env .cat-log-table tbody td {
    vertical-align: middle;
    font-size: 0.875rem;
    color: #334155;
    border-top: 1px solid #f1f5f9;
}
.modulo-env .cat-log-table tbody td.cat-log-cell--primary {
    font-weight: 600;
    color: var(--cat-accent);
}
.modulo-env .cat-log-table tbody tr:hover {
    background: color-mix(in srgb, var(--cat-soft) 55%, #fff);
}
.modulo-env .cat-log-empty {
    padding: 2.5rem 1rem !important;
}
.modulo-env .cat-log-empty__icon {
    font-size: 2rem;
    color: var(--cat-mid);
    opacity: 0.55;
    margin-bottom: 0.5rem;
}
.modulo-env .cat-log-table .btn-outline-primary {
    color: var(--cat-accent);
    border-color: var(--cat-mid);
}
.modulo-env .cat-log-table .btn-outline-primary:hover {
    background: var(--cat-soft);
    color: var(--cat-accent);
    border-color: var(--cat-accent);
}

/* — Formulario — */
.modulo-env .cat-log-form {
    background: #fff;
}
.modulo-env .cat-log-form .form-group label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cat-accent);
    margin-bottom: 0.35rem;
}
.modulo-env .cat-log-form .form-control {
    border-radius: 0.4rem;
    border-color: #cbd5e1;
    font-size: 0.875rem;
}
.modulo-env .cat-log-form .form-control:focus {
    border-color: var(--cat-mid);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--cat-soft) 80%, transparent);
}
.modulo-env .cat-log-form .custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--cat-accent);
    border-color: var(--cat-accent);
}
.modulo-env .cat-log-form-actions {
    padding: 1rem 0 0.25rem;
    margin-top: 0.5rem;
    border-top: 2px solid var(--cat-soft);
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.modulo-env .cat-log-form-actions .btn-success {
    background: var(--cat-accent);
    border-color: var(--cat-accent);
}
.modulo-env .cat-log-form-actions .btn-success:hover {
    background: var(--cat-mid);
    border-color: var(--cat-mid);
}
.modulo-env .cat-log-card .card-footer {
    background: var(--cat-soft) !important;
    border-top: 1px solid color-mix(in srgb, var(--cat-accent) 20%, #dee2e6);
}
</style>
