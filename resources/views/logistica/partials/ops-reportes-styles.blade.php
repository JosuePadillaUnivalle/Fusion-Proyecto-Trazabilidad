<style>
.log-ops-wrap { padding: 0 .15rem; }

.log-ops-hero {
    background: linear-gradient(135deg, #f8fafc 0%, #ecfdf5 55%, #f0f9ff 100%);
    border: 1px solid #d1fae5;
    border-radius: 16px;
    padding: 1.1rem 1.35rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .05);
}
.log-ops-hero--warn {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 45%, #fff7ed 100%);
    border-color: #fde68a;
}
.log-ops-hero__title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 .25rem;
}
.log-ops-hero__text {
    font-size: .86rem;
    color: #64748b;
    margin: 0;
}

.log-ops-metrics { margin-bottom: 1rem; }
.log-ops-metric {
    border: 0;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(18, 38, 63, .07);
    padding: .85rem 1rem;
    height: 100%;
    display: flex;
    align-items: center;
    gap: .75rem;
    background: #fff;
}
.log-ops-metric__icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.log-ops-metric__icon--green { background: #dcfce7; color: #15803d; }
.log-ops-metric__icon--blue { background: #dbeafe; color: #1d4ed8; }
.log-ops-metric__icon--amber { background: #fef3c7; color: #b45309; }
.log-ops-metric__icon--rose { background: #ffe4e6; color: #be123c; }
.log-ops-metric__val { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1; }
.log-ops-metric__lbl { font-size: .74rem; color: #64748b; margin: .15rem 0 0; }

.log-ops-card {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(18, 38, 63, .06);
    overflow: hidden;
    margin-bottom: 1rem;
}
.log-ops-card__head {
    background: #fff;
    border-bottom: 1px solid #eef2f6;
    padding: 1rem 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.log-ops-card__title {
    font-size: .98rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.log-ops-card__title i { color: #059669; }
.log-ops-card__count {
    font-size: .72rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: .3rem .75rem;
}

.log-ops-upload {
    padding: 1.15rem 1.25rem 1.25rem;
    background: #fff;
}
.log-ops-field label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    margin-bottom: .35rem;
}
.log-ops-field .form-control,
.log-ops-field .custom-file-label {
    border-radius: 10px;
    border-color: #e2e8f0;
    font-size: .88rem;
}
.log-ops-field .form-control:focus {
    border-color: #34d399;
    box-shadow: 0 0 0 .15rem rgba(52, 211, 153, .15);
}
.log-ops-file-wrap {
    position: relative;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem .65rem;
    min-height: calc(1.8125rem + 2px);
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .35rem .65rem .35rem .4rem;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.log-ops-file-wrap:hover,
.log-ops-file-wrap:focus-within {
    border-color: #34d399;
    box-shadow: 0 0 0 .12rem rgba(52, 211, 153, .12);
}
.log-ops-file-wrap.has-file {
    border-color: #6ee7b7;
    background: #f0fdf4;
}
.log-ops-file-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
.log-ops-file-btn {
    margin: 0;
    padding: .4rem .75rem;
    border-radius: 8px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border: 1px solid #6ee7b7;
    color: #047857;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .15s, border-color .15s;
}
.log-ops-file-btn:hover {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-color: #34d399;
    color: #065f46;
}
.log-ops-file-name {
    font-size: .8rem;
    color: #94a3b8;
    flex: 1 1 7rem;
    min-width: 0;
    line-height: 1.35;
}
.log-ops-file-wrap:not(.has-file) .log-ops-file-name {
    white-space: normal;
    overflow: visible;
}
.log-ops-file-wrap.has-file .log-ops-file-name {
    color: #0f172a;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.log-ops-btn-primary {
    border-radius: 10px;
    font-weight: 700;
    padding: .55rem 1.15rem;
    background: linear-gradient(135deg, #059669, #047857);
    border: 0;
}
.log-ops-btn-primary:hover {
    background: linear-gradient(135deg, #047857, #065f46);
}

.log-ops-filtros {
    padding: 1rem 1.25rem 1.1rem;
    background: #fafbfc;
    border-bottom: 1px solid #eef2f6;
}
.log-ops-filtros .form-row {
    margin-left: -.5rem;
    margin-right: -.5rem;
    align-items: flex-start;
}
.log-ops-filtros .form-row > [class*="col"] {
    padding-left: .5rem;
    padding-right: .5rem;
    margin-bottom: .65rem;
}
.log-ops-filtros .form-row > [class*="col"]:last-child {
    margin-bottom: 0;
}
.log-ops-filtros label {
    display: block;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    margin-bottom: .4rem;
    line-height: 1.25;
    white-space: nowrap;
}
.log-ops-filtros .form-control-sm,
.log-ops-filtros .custom-select-sm {
    border-radius: 10px;
    border-color: #e2e8f0;
    min-height: calc(1.8125rem + 2px);
    font-size: .88rem;
}
.log-ops-filtros .form-control-sm:focus {
    border-color: #34d399;
    box-shadow: 0 0 0 .12rem rgba(52, 211, 153, .12);
}
.log-ops-filtros__acciones {
    display: flex;
    align-items: center;
}
.log-ops-filtros__submit-col .btn {
    height: calc(1.8125rem + 2px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding-top: 0;
    padding-bottom: 0;
}
.log-ops-filtros__activos {
    margin-top: .15rem;
    padding-top: .75rem;
    border-top: 1px solid #eef2f6;
    font-size: .8rem;
    color: #64748b;
}
.log-ops-filtros__activos a {
    color: #059669;
    font-weight: 600;
}

.log-ops-filtros .modulo-filtros-panel {
    border-radius: 0;
    background: #fafbfc;
}

.log-ops-table thead th {
    background: #f8fafc;
    border-bottom: 1px solid #eef2f6;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    padding: .85rem 1rem;
    white-space: nowrap;
}
.log-ops-table tbody td {
    padding: .9rem 1rem;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
    font-size: .88rem;
    color: #334155;
}
.log-ops-table tbody tr:hover { background: #f8fafc; }
.log-ops-table .td-ref {
    font-weight: 700;
    color: #0f172a;
    font-size: .84rem;
}
.log-ops-table .td-muted { color: #94a3b8; font-size: .8rem; }

.log-ops-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .7rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .02em;
    white-space: nowrap;
}
.log-ops-chip--pod { background: #d1fae5; color: #047857; }
.log-ops-chip--nota { background: #e0e7ff; color: #4338ca; }
.log-ops-chip--guia { background: #dbeafe; color: #1d4ed8; }
.log-ops-chip--confirm { background: #ccfbf1; color: #0f766e; }
.log-ops-chip--evidencia { background: #fef3c7; color: #b45309; }
.log-ops-chip--default { background: #f1f5f9; color: #475569; }

.log-ops-chip--abierto {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    border-radius: 6px;
}
.log-ops-chip--pendiente {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fde68a;
    border-radius: 6px;
}
.log-ops-chip--resuelto {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
}

.log-ops-tipo-pill {
    display: inline-block;
    font-size: .8rem;
    font-weight: 600;
    color: #475569;
}

.log-ops-actions {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .35rem;
}
.log-ops-actions form { margin: 0; display: inline-flex; }
.log-ops-btn-icon {
    width: 34px;
    height: 34px;
    padding: 0;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .82rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    transition: all .15s;
}
.log-ops-btn-icon--view {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}
.log-ops-btn-icon--down {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}
.log-ops-btn-icon--edit {
    background: #fffbeb;
    border-color: #fde68a;
    color: #b45309;
}
.log-ops-btn-icon--del {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}
.log-ops-btn-icon:hover { text-decoration: none; }
.log-ops-btn-icon--view:hover { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }
.log-ops-btn-icon--down:hover { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
.log-ops-btn-icon--edit:hover { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
.log-ops-btn-icon--del:hover { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
.log-ops-btn-icon--resolve {
    width: auto;
    padding: 0 .65rem;
    font-size: .76rem;
    font-weight: 600;
    background: #f0fdf4;
    border-color: #86efac;
    color: #15803d;
}
.log-ops-btn-icon--resolve:hover { background: #dcfce7; border-color: #4ade80; color: #166534; }

.log-ops-actions--incidentes {
    gap: .3rem;
}
.log-ops-actions--incidentes .log-ops-btn-icon--resolve {
    margin-left: .35rem;
    padding-left: .85rem;
    border-left-width: 1px;
}

.log-ops-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    color: #94a3b8;
}
.log-ops-empty i {
    font-size: 2.2rem;
    opacity: .35;
    display: block;
    margin-bottom: .75rem;
}
.log-ops-footer {
    background: #fff;
    border-top: 1px solid #eef2f6;
    padding: .85rem 1.25rem;
}

.log-ops-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(15, 23, 42, .15);
}
.log-ops-modal .modal-header {
    background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
    border-bottom: 1px solid #d1fae5;
}
.log-ops-modal .modal-title { font-weight: 800; color: #0f172a; font-size: 1rem; }
</style>
