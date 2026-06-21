<style>
.page-veh-form .veh-form-shell {
    max-width: 1080px;
    margin: 0 auto;
}
.page-veh-form .veh-form-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-radius: 14px;
    background: linear-gradient(135deg, #1e3a2f 0%, #2c5530 55%, #3d6b4f 100%);
    color: #fff;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 24px rgba(44, 85, 48, 0.22);
}
.page-veh-form .veh-form-hero__placa {
    font-size: 1.65rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}
.page-veh-form .veh-form-hero__meta {
    font-size: .85rem;
    opacity: .88;
    margin-top: .2rem;
}
.page-veh-form .veh-form-hero__badge {
    background: rgba(255, 255, 255, .14);
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 999px;
    padding: .45rem .9rem;
    font-size: .78rem;
    font-weight: 600;
    white-space: nowrap;
}
.page-veh-form .veh-form-section {
    background: #fff;
    border: 1px solid #e8edf2;
    border-radius: 14px;
    padding: 1.25rem 1.35rem 1.1rem;
    margin-bottom: 1.1rem;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .04);
}
.page-veh-form .veh-form-section__head {
    display: flex;
    align-items: center;
    gap: .65rem;
    margin-bottom: 1rem;
    padding-bottom: .75rem;
    border-bottom: 1px solid #f1f5f9;
}
.page-veh-form .veh-form-section__icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
}
.page-veh-form .veh-form-section__icon--ident { background: #e8f4ec; color: #2c5530; }
.page-veh-form .veh-form-section__icon--class { background: #e8f2ff; color: #2563eb; }
.page-veh-form .veh-form-section__icon--thermal { background: #e0f2fe; color: #0284c7; }
.page-veh-form .veh-form-section__icon--capacity { background: #fef3c7; color: #b45309; }
.page-veh-form .veh-form-section__icon--dims { background: #f3e8ff; color: #7c3aed; }
.page-veh-form .veh-form-section__title {
    margin: 0;
    font-size: .95rem;
    font-weight: 700;
    color: #1e293b;
}
.page-veh-form .veh-form-section__sub {
    margin: 0;
    font-size: .78rem;
    color: #64748b;
}
.page-veh-form .veh-field label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    margin-bottom: .35rem;
}
.page-veh-form .veh-field .form-control,
.page-veh-form .veh-field select.form-control {
    border-radius: 10px;
    border-color: #dbe3ec;
    min-height: 42px;
    font-size: .9rem;
}
.page-veh-form .veh-field .form-control:focus,
.page-veh-form .veh-field select.form-control:focus {
    border-color: #4a7c59;
    box-shadow: 0 0 0 3px rgba(74, 124, 89, .15);
}
.page-veh-form .veh-field--placa .form-control {
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}
.page-veh-form .veh-tipo-resumen {
    margin-top: .45rem;
    padding: .55rem .75rem;
    background: #f8fafc;
    border-radius: 8px;
    font-size: .78rem;
    color: #475569;
    border-left: 3px solid #4a7c59;
}
.page-veh-form .veh-transporte-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: .75rem;
}
.page-veh-form .veh-transporte-card {
    position: relative;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: .95rem 1rem .85rem;
    cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    background: #fafbfc;
    height: 100%;
}
.page-veh-form .veh-transporte-card:hover {
    border-color: #94a3b8;
    transform: translateY(-1px);
}
.page-veh-form .veh-transporte-card input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.page-veh-form .veh-transporte-card.is-selected {
    border-color: #2c5530;
    background: #f3faf5;
    box-shadow: 0 4px 14px rgba(44, 85, 48, .12);
}
.page-veh-form .veh-transporte-card__icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .55rem;
    font-size: 1rem;
}
.page-veh-form .veh-transporte-card__icon.tone-general { background: #f1f5f9; color: #475569; }
.page-veh-form .veh-transporte-card__icon.tone-iso { background: #ffedd5; color: #c2410c; }
.page-veh-form .veh-transporte-card__icon.tone-multi { background: #ede9fe; color: #6d28d9; }
.page-veh-form .veh-transporte-card__icon.tone-frio { background: #dbeafe; color: #1d4ed8; }
.page-veh-form .veh-transporte-card__name {
    font-weight: 700;
    font-size: .9rem;
    color: #1e293b;
    margin-bottom: .2rem;
}
.page-veh-form .veh-transporte-card__desc {
    font-size: .76rem;
    color: #64748b;
    line-height: 1.35;
    margin: 0;
}
.page-veh-form .veh-transporte-card__check {
    position: absolute;
    top: .65rem;
    right: .65rem;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .55rem;
    color: transparent;
    transition: all .15s ease;
}
.page-veh-form .veh-transporte-card.is-selected .veh-transporte-card__check {
    background: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
.page-veh-form .veh-capacity-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}
@media (max-width: 767px) {
    .page-veh-form .veh-capacity-grid { grid-template-columns: 1fr; }
}
.page-veh-form .veh-capacity-box {
    background: #f8fafc;
    border: 1px solid #e8edf2;
    border-radius: 12px;
    padding: 1rem;
}
.page-veh-form .veh-capacity-box label {
    display: flex;
    align-items: center;
    gap: .4rem;
}
.page-veh-form .veh-dims-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .85rem;
}
@media (max-width: 767px) {
    .page-veh-form .veh-dims-grid { grid-template-columns: 1fr; }
}
.page-veh-form .veh-form-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border: 1px solid #e8edf2;
    border-radius: 14px;
    margin-top: .25rem;
}
.page-veh-form .veh-toggle-activo {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    cursor: pointer;
    margin: 0;
    user-select: none;
}
.page-veh-form .veh-toggle-activo input { display: none; }
.page-veh-form .veh-toggle-track {
    width: 44px;
    height: 24px;
    background: #cbd5e1;
    border-radius: 999px;
    position: relative;
    transition: background .2s ease;
}
.page-veh-form .veh-toggle-track::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,.15);
}
.page-veh-form .veh-toggle-activo input:checked + .veh-toggle-track {
    background: #2c5530;
}
.page-veh-form .veh-toggle-activo input:checked + .veh-toggle-track::after {
    transform: translateX(20px);
}
.page-veh-form .veh-toggle-label {
    font-size: .88rem;
    font-weight: 600;
    color: #334155;
}
.page-veh-form .veh-form-actions .btn {
    border-radius: 10px;
    padding: .55rem 1.15rem;
    font-weight: 600;
}
.page-veh-form .veh-form-actions .btn-success {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    border: none;
}
</style>
