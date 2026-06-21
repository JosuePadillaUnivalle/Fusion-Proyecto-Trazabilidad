<style>
    .almacen-section {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 20px;
        border: 2px dashed #6c757d;
        margin-top: 0;
        transition: all 0.3s ease;
    }
    .almacen-section.active {
        border-color: #28a745;
        border-style: solid;
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
    }
    .almacen-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 10px;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .almacen-card:hover {
        border-color: #28a745;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    }
    .almacen-card.selected {
        border-color: #28a745;
        background: #d4edda;
    }
    .almacen-card .almacen-icon {
        font-size: 1.8rem;
        color: #6c757d;
        width: 45px;
    }
    .almacen-card.selected .almacen-icon {
        color: #28a745;
    }
    .almacen-card .almacen-nombre {
        font-weight: 600;
        color: #1a252f;
    }
    .almacen-card .almacen-tipo {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .capacidad-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 8px;
    }
    .capacidad-bar .fill {
        height: 100%;
        border-radius: 3px;
    }
    .capacidad-bar .fill.low { background: #28a745; }
    .capacidad-bar .fill.medium { background: #ffc107; }
    .capacidad-bar .fill.high { background: #dc3545; }
    .capacidad-bar--stacked {
        display: flex;
        align-items: stretch;
    }
    .capacidad-bar--stacked .fill-actual {
        flex-shrink: 0;
        border-radius: 3px 0 0 3px;
    }
    .capacidad-bar--stacked .fill-proyeccion {
        flex-shrink: 0;
        background: rgba(40, 167, 69, 0.42);
        border-radius: 0 3px 3px 0;
        transition: width 0.25s ease;
    }
    .capacidad-bar--stacked .fill-proyeccion.excede {
        background: rgba(220, 53, 69, 0.55);
    }
    .almacen-preview-cosecha {
        font-size: 0.72rem;
        line-height: 1.35;
        color: #475569;
        margin-top: 0.45rem;
        padding-top: 0.45rem;
        border-top: 1px dashed rgba(40, 167, 69, 0.35);
    }
    .almacen-preview-cosecha__principal {
        color: #14532d;
        font-size: 0.78rem;
        margin-bottom: 0.15rem;
    }
    .almacen-preview-cosecha__detalle {
        color: #64748b;
        font-size: 0.72rem;
    }
    .almacen-preview-cosecha strong {
        color: #14532d;
    }
    .almacen-card.selected .almacen-preview-cosecha strong {
        color: #166534;
    }
    .guia-campo {
        background: #f8fbf8;
        border-left: 3px solid #2c5530;
        border-radius: 0 8px 8px 0;
        padding: 0.65rem 0.85rem;
        margin-bottom: 0.75rem;
        font-size: 0.85rem;
        color: #495057;
    }
    .guia-campo strong { color: #2c5530; }
    .almacen-section-extra {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(40, 167, 69, 0.35);
        border-radius: 10px;
        padding: 1rem 1.15rem;
        margin-top: 1rem;
    }
    .almacen-section-extra .form-control {
        background: #fff;
        border: 2px solid #dee2e6;
    }
    .almacen-section-extra .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.15rem rgba(40, 167, 69, 0.2);
    }
    .almacen-section-extra label {
        color: #1a252f;
    }
    .almacen-section-actions {
        margin-top: 1rem;
        padding-top: 0.25rem;
    }
    .almacen-modal-tabs .nav-link {
        font-weight: 600;
        color: #495057;
        border-radius: 8px 8px 0 0;
    }
    .almacen-modal-tabs .nav-link.active {
        color: #2c5530;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    .almacen-modal-lista {
        max-height: 420px;
        overflow-y: auto;
    }
    .almacen-modal-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: .5rem;
        cursor: pointer;
        transition: all .2s ease;
        background: #fff;
    }
    .almacen-modal-item:hover {
        border-color: #28a745;
        box-shadow: 0 2px 8px rgba(40, 167, 69, .15);
    }
    .almacen-modal-item.is-selected {
        border-color: #28a745;
        background: #d4edda;
    }
    .almacen-modal-item .almacen-modal-icon {
        width: 40px;
        text-align: center;
        font-size: 1.4rem;
        color: #6c757d;
    }
    .almacen-modal-item.is-selected .almacen-modal-icon { color: #28a745; }
    .almacen-modal-item .almacen-modal-body { flex: 1; min-width: 0; }
    .almacen-modal-item .almacen-modal-nombre {
        font-weight: 600;
        color: #1a252f;
    }
    .almacen-modal-item .almacen-modal-meta {
        font-size: .8rem;
        color: #6c757d;
    }
    .almacen-mapa-modal {
        height: 420px;
        min-height: 420px;
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }
    .almacen-mapa-marker { background: transparent !important; border: none !important; }
    .almacen-mapa-pin {
        width: 32px; height: 32px; border-radius: 50%; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.35); cursor: pointer;
        transition: transform .12s ease;
        background: #28a745;
    }
    .almacen-mapa-pin:hover { transform: scale(1.12); }
    .almacen-mapa-pin.is-selected {
        box-shadow: 0 0 0 3px #fbbf24, 0 2px 8px rgba(0,0,0,.35);
    }
    .leaflet-tooltip.almacen-mapa-tooltip {
        background: #1e293b; color: #fff; border: 0; border-radius: 8px;
        font-size: .8rem; font-weight: 600; padding: .35rem .65rem;
        box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    .leaflet-tooltip.almacen-mapa-tooltip::before { border-top-color: #1e293b; }
    .almacen-mapa-flash {
        position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%);
        background: #1e293b; color: #fff; padding: .4rem .85rem; border-radius: 8px;
        font-size: .8rem; display: none; pointer-events: none;
    }

    /* —— Modal confirmar envío (trazabilidad) —— */
    .envio-almacen-modal { border-radius: 14px; overflow: hidden; }
    .envio-almacen-modal__header {
        background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
        color: #fff;
        border: 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }
    .envio-almacen-modal__subtitle {
        font-size: 0.8rem;
        opacity: 0.92;
        margin-top: 0.2rem;
    }
    .envio-almacen-modal__footer { background: #f8fafc; }

    .envio-cosecha-resumen {
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        background: linear-gradient(180deg, #fffbeb 0%, #fff 100%);
    }
    .envio-cosecha-resumen__head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .envio-cosecha-resumen__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #dcfce7;
        color: #15803d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .envio-cosecha-resumen__titulo {
        display: block;
        color: #14532d;
        font-size: 1rem;
    }
    .envio-cosecha-resumen__calibre {
        display: block;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: normal;
    }
    .envio-cosecha-resumen__metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.65rem;
        margin-top: 0.85rem;
    }
    .envio-cosecha-metric {
        background: #fff;
        border: 1px solid #fef3c7;
        border-radius: 8px;
        padding: 0.55rem 0.5rem;
        text-align: center;
    }
    .envio-cosecha-metric--highlight {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }
    .envio-cosecha-metric__value {
        display: block;
        font-weight: 700;
        color: #1e293b;
        font-size: 1.05rem;
        line-height: 1.2;
    }
    .envio-cosecha-metric__value small { font-size: 0.72rem; font-weight: 600; }
    .envio-cosecha-metric__label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        margin-top: 0.15rem;
    }

    .envio-destino-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        background: #fff;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }
    .envio-destino-card--empty {
        text-align: center;
        padding: 1.5rem 1rem;
        background: #f8fafc;
    }
    .envio-destino-card__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .envio-destino-card__badge {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 600;
        color: #166534;
        background: #dcfce7;
        border-radius: 6px;
        padding: 0.2rem 0.5rem;
        margin-bottom: 0.35rem;
    }
    .envio-destino-card__nombre {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }
    .envio-destino-card__meta {
        font-size: 0.78rem;
        color: #64748b;
    }
    .envio-destino-card__ingreso-label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.03em;
    }
    .envio-destino-card__ingreso-valor {
        display: block;
        color: #b45309;
        font-size: 1.1rem;
    }
    .envio-destino-card__preview-principal {
        font-size: 0.82rem;
        color: #334155;
    }
    .envio-destino-card__preview-detalle {
        font-size: 0.76rem;
        color: #64748b;
        margin-top: 0.15rem;
    }

    .almacen-modal-preview {
        margin-top: 0.55rem;
        padding-top: 0.55rem;
        border-top: 1px dashed #e2e8f0;
    }
    .almacen-modal-item__chevron {
        align-self: center;
        padding-left: 0.25rem;
    }
    .almacen-preview-inline__bar {
        height: 5px;
        margin-bottom: 0.35rem;
    }
    .almacen-preview-inline__text {
        font-size: 0.74rem;
        line-height: 1.4;
        color: #475569;
    }
    .almacen-preview-inline--compact .almacen-preview-inline__text {
        font-size: 0.7rem;
    }

    .leaflet-tooltip.almacen-mapa-tooltip-rich {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0;
        font-size: 0.78rem;
        font-weight: 400;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
        min-width: 200px;
        max-width: 260px;
        width: max-content;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .leaflet-tooltip.almacen-mapa-tooltip-rich::before {
        border-top-color: #e2e8f0;
    }
    .almacen-mapa-tooltip-rich__inner {
        padding: 0.55rem 0.7rem;
        max-width: 260px;
        box-sizing: border-box;
    }
    .almacen-mapa-tooltip-rich__nombre {
        display: block;
        color: #14532d;
        font-size: 0.82rem;
        margin-bottom: 0.15rem;
        word-break: break-word;
    }
    .almacen-mapa-tooltip-rich__meta {
        font-size: 0.7rem;
        color: #64748b;
        margin-bottom: 0.35rem;
        line-height: 1.35;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .almacen-mapa-tooltip-rich__cap {
        font-size: 0.72rem;
        color: #475569;
        margin-bottom: 0.35rem;
        word-break: break-word;
    }
    .almacen-mapa-tooltip-rich__libre {
        font-size: 0.72rem;
        color: #15803d;
        font-weight: 600;
        word-break: break-word;
    }
    .leaflet-tooltip.almacen-mapa-tooltip-rich .almacen-preview-inline {
        max-width: 100%;
    }
    .leaflet-tooltip.almacen-mapa-tooltip-rich .almacen-preview-inline__text {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.35;
    }
</style>
