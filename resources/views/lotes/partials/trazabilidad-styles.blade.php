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
    flex: 1; min-width: 100px; text-align: center; padding: 10px 8px;
    border-radius: 10px; border: 2px solid #e9ecef; background: #fff;
    font-size: .78rem; font-weight: 600; color: #6c757d; position: relative;
}
.trz-dash .fase-step.done { border-color: #28a745; background: #f0fdf4; color: #155724; }
.trz-dash .fase-step.active { border-color: #2c5530; background: #2c5530; color: #fff; box-shadow: 0 4px 12px rgba(44,85,48,.35); }
.trz-dash .fase-step.next {
    border-color: #17a2b8; background: #e8f7fb; color: #0c5460;
    cursor: pointer; text-decoration: none;
}
.trz-dash .fase-step.next:hover { background: #17a2b8; color: #fff; border-color: #138496; }
.trz-dash a.fase-step-link { display: block; color: inherit; text-decoration: none; }
.trz-dash a.fase-step-link.fase-step.next { color: #0c5460; }
.trz-dash a.fase-step-link.fase-step.next:hover { color: #fff; }
.trz-dash a.fase-step-link.fase-step.active:hover { color: #fff; opacity: .95; }
.trz-dash .fase-step.pending { opacity: .65; }
.trz-dash .fase-step.skipped {
    border-color: #dee2e6; background: #f8f9fa; color: #6c757d; opacity: .85;
    text-decoration: line-through; text-decoration-color: rgba(108,117,125,.45);
}
.trz-dash .fase-step .badge { font-size: .65rem; margin-top: 4px; }
.trz-dash .progress-fase { height: 10px; border-radius: 8px; background: #e9ecef; margin-bottom: 1rem; }
.trz-dash .progress-fase .bar { height: 100%; border-radius: 8px; background: linear-gradient(90deg, #2c5530, #4a7c59); }
.trz-dash .tabla-trz tbody tr:hover { background: #f8fbf8; }
.trz-dash .badge-fase { font-size: .72rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; color: #fff; }
.trz-dash .trz-pasos-list li { margin-bottom: 2px; line-height: 1.35; }
.trz-dash .trz-pasos-panel { background: #f8faf8; }
.trz-dash .timeline-trz { max-height: 520px; overflow-y: auto; }
.trz-dash .evento-trz {
    border-left: 4px solid #dee2e6; padding: 12px 14px; margin-bottom: 10px;
    background: #f8f9fc; border-radius: 0 8px 8px 0;
}
.trz-dash .evento-trz[data-visible="false"] { display: none; }
</style>
