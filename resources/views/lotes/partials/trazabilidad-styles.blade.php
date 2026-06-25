<style>
.trz-dash .kpi-card {
    border-radius: 12px; border: none; box-shadow: 0 2px 12px rgba(18,38,63,.08);
    margin-bottom: 1rem; overflow: hidden;
}
.trz-dash .kpi-card .inner { padding: 1rem 1.1rem; }
.trz-dash .kpi-card h3 { font-size: 1.6rem; font-weight: 700; margin: 0; color: #1a252f; }
.trz-dash .kpi-card p { margin: 0; font-size: .85rem; color: #6c757d; }
.trz-dash .kpi-icon {
    position: absolute; right: 12px; top: 12px; font-size: 2.2rem; opacity: .15;
}
.trz-dash .filtros-trz {
    background: #f8faf9; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 1rem 1.15rem; margin-bottom: 1.25rem;
}
.trz-dash .chart-box {
    background: #fff; border-radius: 12px; border: 1px solid #e9ecef;
    padding: 1rem; margin-bottom: 1rem; min-height: 280px;
}
.trz-dash .chart-box h6 { font-weight: 600; color: #2c5530; margin-bottom: .75rem; }
.trz-dash .fase-pipeline { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1.25rem; }
.trz-dash .fase-step {
    flex: 1; min-width: 100px; text-align: center; padding: 10px 32px 10px 8px;
    border-radius: 10px; border: 2px solid #e9ecef; background: #fff;
    font-size: .78rem; font-weight: 600; color: #6c757d; position: relative;
}
.trz-dash .fase-step:not(.active) { padding-right: 8px; }
.trz-dash .fase-step.done { border-color: #28a745; background: #f0fdf4; color: #155724; }
.trz-dash .fase-step.active { border-color: #2c5530; background: #2c5530; color: #fff; box-shadow: 0 4px 12px rgba(44,85,48,.35); }
.trz-dash .fase-step.next {
    border-color: #17a2b8; background: #e8f7fb; color: #0c5460;
    cursor: pointer; text-decoration: none;
}
.trz-dash .fase-step.next:hover { background: #17a2b8; color: #fff; border-color: #138496; }
.trz-dash .fase-step-link {
    display: block;
    color: inherit;
    text-decoration: none;
    flex: 1;
    min-width: 100px;
}
.trz-dash .fase-step-link.fase-step.next { color: #0c5460; }
.trz-dash .fase-step-link.fase-step.next:hover,
.trz-dash .fase-step-link.fase-step.next:focus { color: #fff; text-decoration: none; }
.trz-dash .fase-step-link.fase-step.active:hover,
.trz-dash .fase-step-link.fase-step.active:focus { color: #fff; opacity: .95; text-decoration: none; }
.trz-dash button.fase-step-link {
    appearance: none;
    -webkit-appearance: none;
    font: inherit;
    text-align: center;
    cursor: pointer;
}
.trz-dash .fase-step.pending { opacity: .65; }
.trz-dash .fase-step.skipped {
    border-color: #dee2e6; background: #f8f9fa; color: #6c757d; opacity: .85;
    text-decoration: line-through; text-decoration-color: rgba(108,117,125,.45);
}
.trz-dash .fase-step .badge { font-size: .65rem; margin-top: 4px; }
.trz-dash .badge-fase-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.65rem;
    height: 1.65rem;
    margin-top: 6px;
    padding: 0 .4rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    font-size: .82rem;
    font-weight: 800;
    box-shadow: 0 2px 8px rgba(217, 119, 6, .35);
}
.trz-dash .fase-step.active .badge-fase-count {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    margin-top: 0;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
    box-shadow: none;
}
.trz-dash .progress-fase { height: 10px; border-radius: 8px; background: #e9ecef; margin-bottom: 1rem; }
.trz-dash .progress-fase .bar { height: 100%; border-radius: 8px; background: linear-gradient(90deg, #2c5530, #4a7c59); }
.trz-dash .tabla-trz tbody tr:hover { background: #f8fbf8; }
.trz-dash .badge-fase { font-size: .72rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; color: #fff; }
.trz-dash .trz-pasos-list li { margin-bottom: 2px; line-height: 1.35; }
.trz-dash .trz-pasos-panel { background: #f8faf8; }
.trz-dash .trz-meta-siguiente {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: .75rem 1rem;
}
.trz-dash .trz-checklist li { margin-bottom: .45rem; font-size: .88rem; line-height: 1.4; }
.trz-dash .trz-checklist li.is-done { color: #475569; }
.trz-dash .trz-checklist li.is-pending { color: #334155; }
.trz-dash .timeline-trz { max-height: 520px; overflow-y: auto; padding: .35rem .15rem .5rem; }
.trz-dash .evento-trz-wrap {
    display: flex;
    align-items: stretch;
    gap: .85rem;
    margin-bottom: 12px;
    position: relative;
}
.trz-dash .evento-trz-wrap:not(:last-child) .evento-trz-paso::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 2.35rem;
    bottom: -.75rem;
    width: 2px;
    transform: translateX(-50%);
    background: var(--paso-color, #cbd5e1);
    opacity: .4;
    border-radius: 2px;
}
.trz-dash .evento-trz-paso {
    flex-shrink: 0;
    width: 2.35rem;
    position: relative;
    display: flex;
    justify-content: center;
    padding-top: .65rem;
}
.trz-dash .evento-trz-paso__num {
    width: 2.35rem;
    height: 2.35rem;
    border-radius: 50%;
    background: var(--paso-color, #6c757d);
    color: #fff;
    font-weight: 800;
    font-size: .88rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(15, 23, 42, .15);
    border: 3px solid #fff;
    z-index: 1;
    line-height: 1;
}
.trz-dash .evento-trz {
    display: flex; align-items: center; gap: .75rem;
    flex: 1; min-width: 0;
    border-left: 4px solid #dee2e6; padding: 12px 14px; margin-bottom: 0;
    background: #f8f9fc; border-radius: 0 10px 10px 0; position: relative;
}
.trz-dash .evento-trz.tiene-accion { padding-right: 7.5rem; }
.trz-dash .evento-trz-cuerpo { flex: 1; min-width: 0; }
.trz-dash .evento-trz-accion {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    margin: 0; flex-shrink: 0;
}
.trz-dash .evento-trz-wrap[data-visible="false"],
.trz-dash .evento-trz[data-visible="false"] { display: none; }
.trz-dash .evento-trz-evidencia { max-width: 220px; }
.trz-dash .evento-trz-evidencia__link {
    display: block;
    text-align: left;
    cursor: zoom-in;
}
.trz-dash .evento-trz-evidencia__img {
    display: block;
    width: 100%;
    max-height: 140px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: box-shadow .15s ease;
}
.trz-dash .evento-trz-evidencia__link:hover .evento-trz-evidencia__img {
    box-shadow: 0 4px 14px rgba(0,0,0,.12);
}
.trz-dash .evento-trz-evidencia__caption {
    display: block;
    margin-top: .25rem;
}
.trz-dash .evento-trz-evidencia--icono .evento-trz-evidencia__icono {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    background: linear-gradient(135deg, #e0f2fe, #bae6fd);
    border: 1px solid #7dd3fc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0369a1;
    font-size: 1.75rem;
}
</style>
