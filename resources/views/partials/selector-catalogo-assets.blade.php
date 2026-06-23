@once
    @push('styles')
    <style>
        #modalSelectorCatalogo.sel-modal .sel-modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(18, 38, 63, 0.22);
        }
        #modalSelectorCatalogo .sel-modal-header {
            background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #3d7a46 100%);
            border: 0;
            padding: 0.95rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        #modalSelectorCatalogo .sel-modal-header .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #fff;
            line-height: 1.3;
            flex: 1;
            min-width: 0;
        }
        #modalSelectorCatalogo .sel-modal-header .close {
            opacity: 0.85;
            text-shadow: none;
            margin: 0;
            padding: 0.25rem;
        }
        #modalSelectorCatalogo .sel-modal-header .close:hover { opacity: 1; }
        #modalSelectorCatalogo .sel-modal-body { max-height: 68vh; overflow-y: auto; padding: 1.15rem 1.25rem; }
        #modalSelectorCatalogo .sel-modal-search-panel {
            background: #f8faf9;
            border: 1px solid #e5efe7;
            border-radius: 12px;
            padding: 0.9rem 1rem;
        }
        #modalSelectorCatalogo .sel-modal-search-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #1e4620;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.4rem;
            display: block;
        }
        #modalSelectorCatalogo .sel-modal-search-input .input-group-text {
            background: #fff;
            border-color: #c5dcc9;
            color: #2c5530;
        }
        #modalSelectorCatalogo .sel-modal-search-input .form-control {
            border-color: #c5dcc9;
            border-radius: 0 10px 10px 0;
        }
        #modalSelectorCatalogo .sel-modal-search-input .form-control:focus {
            border-color: #2c5530;
            box-shadow: 0 0 0 0.15rem rgba(44, 85, 48, 0.15);
        }
        #modalSelectorCatalogo .sel-modal-table-wrap {
            border: 1px solid #e5efe7;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        #modalSelectorCatalogo .sel-modal-table thead th {
            background: #f0fdf4;
            color: #1e4620;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-top: 0;
            border-bottom: 1px solid #d1fae5;
            padding: 0.65rem 1rem;
        }
        #modalSelectorCatalogo .selector-catalogo-row { cursor: pointer; transition: background 0.12s ease, transform 0.1s ease; }
        #modalSelectorCatalogo .selector-catalogo-row:hover { background: #f0fdf4; }
        #modalSelectorCatalogo .selector-catalogo-row:active { transform: scale(0.995); }
        #modalSelectorCatalogo .selector-catalogo-row td { padding: 0.8rem 1rem; vertical-align: middle; border-top: 1px solid #f3f4f6; }
        #modalSelectorCatalogo .sel-col-nombre { font-weight: 700; color: #1f2937; font-size: 0.92rem; }
        #modalSelectorCatalogo .sel-col-nombre .sel-row-icon {
            display: inline-flex;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: #fef3c7;
            color: #b45309;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 0.55rem;
            vertical-align: middle;
        }
        #modalSelectorCatalogo .sel-badge-sugerido {
            display: inline-block;
            margin-left: 0.45rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #047857;
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            border-radius: 6px;
            padding: 0.1rem 0.4rem;
            vertical-align: middle;
        }
        #modalSelectorCatalogo .selector-catalogo-row--suggested {
            background: #ecfdf5;
            box-shadow: inset 3px 0 0 #059669;
        }
        #modalSelectorCatalogo .selector-catalogo-row--suggested:hover { background: #d1fae5; }
        #modalSelectorCatalogo .selector-catalogo-row--selected {
            background: #eff6ff;
            box-shadow: inset 3px 0 0 #2563eb;
        }
        #modalSelectorCatalogo .selector-catalogo-row--selected:hover { background: #dbeafe; }
        #modalSelectorCatalogo .sel-badge-seleccionado {
            display: inline-block;
            margin-left: 0.45rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #1d4ed8;
            background: #dbeafe;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 0.1rem 0.4rem;
            vertical-align: middle;
        }
        #modalSelectorCatalogo .sel-col-meta { color: #6b7280; font-size: 0.84rem; line-height: 1.45; }
        #modalSelectorCatalogo .sel-meta-stack { display: flex; flex-direction: column; gap: 0.28rem; }
        #modalSelectorCatalogo .sel-meta-line {
            display: flex; align-items: flex-start; gap: 0.4rem;
            font-size: 0.8rem; color: #475569; line-height: 1.35;
        }
        #modalSelectorCatalogo .sel-meta-line i {
            width: 16px; text-align: center; color: #64748b; margin-top: 0.1rem; flex-shrink: 0;
        }
        #modalSelectorCatalogo .sel-meta-line span { flex: 1; }
        #modalSelectorCatalogo .sel-col-meta--with-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }
        #modalSelectorCatalogo .sel-col-meta--with-action .sel-col-meta-text { flex: 1; min-width: 0; }
        /* Columna vehículo: meta arriba, botón Ver en fila separada */
        #modalSelectorCatalogo .sel-col-meta--vehiculo {
            display: block !important;
            vertical-align: top;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }
        #modalSelectorCatalogo .sel-col-meta--vehiculo .sel-meta-stack {
            margin-bottom: 0;
        }
        #modalSelectorCatalogo .sel-ver-vehiculo-row {
            margin-top: 0.75rem;
            padding-top: 0.7rem;
            border-top: 1px dashed #cbd5e1;
        }
        #modalSelectorCatalogo .sel-row-ver-vehiculo-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            border: 1px solid #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        #modalSelectorCatalogo .sel-row-ver-vehiculo-btn:hover {
            background: #dbeafe;
            color: #1e40af;
            transform: translateY(-1px);
        }
        #modalSelectorCatalogo .selector-catalogo-row:hover .sel-row-ver-vehiculo-btn {
            border-color: #3b82f6;
        }

        /* Modelado 3D vehículo (compartido: detalle vehículo + selector) */
        #modalVehiculoCargaPreview { z-index: 1070 !important; }
        .veh-carga-preview-dialog { max-width: 680px; }
        .veh-carga-preview-modal.veh-det-panel {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.15) !important;
        }
        .veh-carga-preview-modal__head.veh-det-panel__head {
            background: #f8faf8;
            border-bottom: 1px solid #e9ecef;
            padding: .85rem 1.15rem;
            flex-direction: row;
            align-items: flex-start;
        }
        .veh-carga-preview-modal__head .modal-title {
            color: #1a252f;
            font-size: .95rem;
        }
        .veh-carga-preview-modal__head .close {
            color: #64748b;
            opacity: 1;
            position: absolute;
            right: 1rem;
            top: .85rem;
        }
        .veh-carga-preview-modal__tipo {
            font-size: .75rem;
            font-weight: 600;
        }
        .veh-carga-preview-modal__body {
            padding: 1rem 1.15rem 1.1rem;
            background: #fff;
        }
        .veh-caja-3d {
            min-height: 280px;
            height: 280px;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #e8eef4;
        }
        .veh-caja-3d canvas {
            display: block;
            width: 100% !important;
            height: 100% !important;
            cursor: grab;
        }
        .veh-caja-3d canvas:active { cursor: grabbing; }
        .veh-caja-3d__swatch {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 3px;
            vertical-align: -2px;
            margin-right: 4px;
        }
        .veh-caja-3d__swatch--cabina { background: #3b6ea8; }
        .veh-caja-3d__swatch--carga { background: rgba(72, 187, 120, 0.45); border: 1px solid #059669; }
        .veh-caja-3d__swatch--fill { background: #f59e0b; }
        .veh-carga-preview-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem;
        }
        @media (max-width: 520px) {
            .veh-carga-preview-stats { grid-template-columns: 1fr; }
        }
        .veh-carga-preview-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.85rem 1rem;
        }
        .veh-carga-preview-stat__head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: #475569;
            margin-bottom: 0.45rem;
        }
        .veh-carga-preview-stat__head strong {
            font-size: 0.95rem;
            color: #0f172a;
        }
        .veh-carga-preview-stat__foot {
            font-size: 0.76rem;
            color: #64748b;
            margin-top: 0.35rem;
        }
        .veh-carga-preview-bar {
            height: 12px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .veh-carga-preview-bar__fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.35s ease;
            max-width: 100%;
        }
        .veh-carga-preview-bar__fill--peso { background: linear-gradient(90deg, #3b82f6, #2563eb); }
        .veh-carga-preview-bar__fill--vol { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
        .veh-carga-preview-bar__fill.is-danger { background: linear-gradient(90deg, #f87171, #ef4444) !important; }
        .veh-carga-preview-bar__fill.is-warning { background: linear-gradient(90deg, #fbbf24, #f59e0b) !important; }
        .veh-carga-preview-alert {
            margin-top: 0.85rem;
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            font-size: 0.82rem;
        }
        .veh-carga-preview-alert--ok {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            color: #047857;
        }
        .veh-carga-preview-alert--err {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
        }
        .veh-carga-preview-modal__foot {
            background: #fff;
            padding: 0.85rem 1.35rem;
            border-top: 1px solid #e2e8f0;
        }
        .veh-carga-preview-modal__foot .btn-secondary {
            border-radius: 10px;
            font-weight: 600;
            background: #475569;
            border-color: #475569;
        }
        #modalSelectorCatalogo .sel-row-action-btn {
            flex-shrink: 0;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.2rem 0.65rem;
            border-radius: 6px;
        }
        #modalSelectorCatalogo .sel-row-map-btn {
            margin-top: 0.35rem;
            font-size: 0.75rem;
            padding: 0.15rem 0.55rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }
        #modalSelectorCatalogo .selector-catalogo-row:hover .sel-row-action-btn {
            background: #2c5530;
            border-color: #2c5530;
            color: #fff;
        }
        #modalSelectorCatalogo .sel-modal-empty {
            text-align: center;
            color: #9ca3af;
            padding: 2.5rem 1rem !important;
            font-size: 0.9rem;
        }
        #modalSelectorCatalogo .sel-modal-empty i { font-size: 1.5rem; opacity: 0.55; }
        #modalSelectorCatalogo .sel-modal-footer {
            background: #f8faf9;
            border-top: 1px solid #e5efe7;
            padding: 0.75rem 1.25rem;
        }
        #modalSelectorCatalogo .sel-modal-meta { color: #6b7280; font-size: 0.82rem; }
        #modalSelectorCatalogo .sel-pager-btn { border-radius: 8px; font-weight: 600; }
        #modalSelectorCatalogo .sel-close-btn { border-radius: 8px; font-weight: 600; background: #4b5563; border-color: #4b5563; }
        #modalSelectorCatalogo .selector-filtros-panel {
            background: linear-gradient(145deg, #f8fbf8 0%, #eef6ef 100%);
            border: 1px solid #cfe8d4;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            box-shadow: 0 2px 10px rgba(44, 85, 48, 0.06);
        }
        #modalSelectorCatalogo .selector-filtros-panel-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }
        #modalSelectorCatalogo .selector-filtros-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        #modalSelectorCatalogo .selector-filtros-title {
            font-weight: 700;
            color: #1e4620;
            font-size: 0.95rem;
            line-height: 1.2;
        }
        #modalSelectorCatalogo .selector-filtros-sub {
            font-size: 0.78rem;
            color: #5a6f5c;
            margin-top: 2px;
        }
        #modalSelectorCatalogo .selector-almacen-toolbar { margin-bottom: 0.75rem; }
        #modalSelectorCatalogo .selector-almacen-search .input-group-text {
            background: #fff;
            border-color: #b8d4be;
        }
        #modalSelectorCatalogo .selector-almacen-search .form-control {
            border-color: #b8d4be;
        }
        #modalSelectorCatalogo .selector-almacen-search .form-control:focus {
            border-color: #4a7c59;
            box-shadow: 0 0 0 0.15rem rgba(74, 124, 89, 0.2);
        }
        #modalSelectorCatalogo .selector-almacen-lista {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-height: 168px;
            overflow-y: auto;
            padding: 2px;
        }
        #modalSelectorCatalogo .selector-almacen-card {
            border: 2px solid #d4e5d8;
            background: #fff;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            min-width: 140px;
            max-width: 100%;
            flex: 1 1 calc(33.333% - 8px);
            cursor: pointer;
            text-align: left;
            transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        }
        @media (max-width: 576px) {
            #modalSelectorCatalogo .selector-almacen-card { flex: 1 1 100%; }
        }
        #modalSelectorCatalogo .selector-almacen-card:hover {
            border-color: #4a7c59;
            background: #fafffa;
            box-shadow: 0 2px 8px rgba(44, 85, 48, 0.1);
        }
        #modalSelectorCatalogo .selector-almacen-card.active {
            border-color: #2c5530;
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: #fff;
            box-shadow: 0 3px 12px rgba(44, 85, 48, 0.25);
        }
        #modalSelectorCatalogo .selector-almacen-card .alm-nombre {
            font-weight: 700;
            font-size: 0.82rem;
            line-height: 1.25;
            display: block;
        }
        #modalSelectorCatalogo .selector-almacen-card .alm-meta {
            font-size: 0.72rem;
            opacity: 0.85;
            margin-top: 2px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #modalSelectorCatalogo .selector-almacen-card:not(.active) .alm-meta { color: #6c757d; }
        #modalSelectorCatalogo .selector-almacen-card--todos .alm-nombre i { margin-right: 4px; }
        #modalSelectorCatalogo .selector-almacen-seleccionado {
            margin-top: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: #fff;
            border: 1px solid #b8dfc0;
            border-radius: 8px;
            font-size: 0.82rem;
            color: #1e4620;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #modalSelectorCatalogo .selector-almacen-seleccionado i { color: #28a745; }
        #modalSelectorCatalogo .selector-campo-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.35rem;
            display: block;
        }
        #modalSelectorCatalogo .selector-cultivo-select {
            border-color: #ced4da;
            border-radius: 6px;
        }
        #modalSelectorCatalogo .selector-producto-panel {
            padding: 0.15rem 0.1rem 0;
        }

        /* Tema: transportista de planta (azul) */
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #3b82f6 100%);
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-search-panel {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-search-label { color: #1e40af; }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-search-input .input-group-text {
            border-color: #93c5fd;
            color: #1d4ed8;
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-search-input .form-control {
            border-color: #93c5fd;
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-search-input .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.18);
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-table-wrap { border-color: #bfdbfe; }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-table thead th {
            background: #dbeafe;
            color: #1e40af;
            border-bottom-color: #93c5fd;
        }
        #modalSelectorCatalogo.sel-theme-planta .selector-catalogo-row:hover { background: #eff6ff; }
        #modalSelectorCatalogo.sel-theme-planta .sel-col-nombre .sel-row-icon {
            background: #dbeafe;
            color: #1d4ed8;
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-modal-footer {
            background: #f8fafc;
            border-top-color: #bfdbfe;
        }
        #modalSelectorCatalogo.sel-theme-planta .sel-pager-btn:not(:disabled):hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #1d4ed8;
        }

        /* Tema: vehículo (verde esmeralda) */
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-header {
            background: linear-gradient(135deg, #064e3b 0%, #047857 55%, #10b981 100%);
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-search-panel {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-search-label { color: #065f46; }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-search-input .input-group-text {
            border-color: #6ee7b7;
            color: #047857;
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-search-input .form-control {
            border-color: #6ee7b7;
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-search-input .form-control:focus {
            border-color: #059669;
            box-shadow: 0 0 0 0.15rem rgba(5, 150, 105, 0.18);
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-table-wrap { border-color: #a7f3d0; }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-table thead th {
            background: #d1fae5;
            color: #065f46;
            border-bottom-color: #6ee7b7;
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .selector-catalogo-row:hover { background: #ecfdf5; }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-col-nombre .sel-row-icon {
            background: #d1fae5;
            color: #047857;
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-modal-footer {
            background: #f0fdf4;
            border-top-color: #a7f3d0;
        }
        #modalSelectorCatalogo.sel-theme-vehiculo .sel-pager-btn:not(:disabled):hover {
            background: #ecfdf5;
            border-color: #059669;
            color: #047857;
        }

        /* Tema: chofer / transportista */
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 55%, #3b82f6 100%);
        }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-search-panel {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-search-label { color: #1e40af; }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-search-input .input-group-text {
            border-color: #93c5fd;
            color: #1d4ed8;
        }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-search-input .form-control { border-color: #93c5fd; }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-search-input .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.18);
        }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-table-wrap { border-color: #bfdbfe; }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-table thead th {
            background: #dbeafe;
            color: #1e40af;
            border-bottom-color: #93c5fd;
        }
        #modalSelectorCatalogo.sel-theme-chofer .selector-catalogo-row:hover { background: #eff6ff; }
        #modalSelectorCatalogo.sel-theme-chofer .sel-col-nombre .sel-row-icon {
            background: #dbeafe;
            color: #1d4ed8;
        }
        #modalSelectorCatalogo.sel-theme-chofer .sel-modal-footer {
            background: #f8fafc;
            border-top-color: #bfdbfe;
        }

        /* Tema: almacén origen planta (rojo) */
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-header {
            background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 55%, #ef4444 100%);
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-search-panel {
            background: #fef2f2;
            border-color: #fecaca;
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-search-label { color: #991b1b; }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-search-input .input-group-text {
            border-color: #fca5a5;
            color: #b91c1c;
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-search-input .form-control {
            border-color: #fca5a5;
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-search-input .form-control:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 0.15rem rgba(220, 38, 38, 0.18);
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-table-wrap { border-color: #fecaca; }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-table thead th {
            background: #fee2e2;
            color: #991b1b;
            border-bottom-color: #fca5a5;
        }
        #modalSelectorCatalogo.sel-theme-origen .selector-catalogo-row:hover { background: #fef2f2; }
        #modalSelectorCatalogo.sel-theme-origen .sel-col-nombre .sel-row-icon {
            background: #fee2e2;
            color: #b91c1c;
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-modal-footer {
            background: #fffafa;
            border-top-color: #fecaca;
        }
        #modalSelectorCatalogo.sel-theme-origen .sel-pager-btn:not(:disabled):hover {
            background: #fef2f2;
            border-color: #dc2626;
            color: #b91c1c;
        }

        /* Combobox filtros (variante natural, sin LED) */
        .selector-filtros-field {
            position: relative;
            display: flex;
            align-items: stretch;
            min-height: 38px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .selector-filtros-field:hover {
            border-color: #93c5fd;
            background: #fff;
        }
        .selector-filtros-field:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
            background: #fff;
        }
        .selector-filtros-field__input {
            flex: 1 1 auto;
            min-width: 0;
            border: 0;
            background: transparent;
            padding: .45rem .5rem .45rem .75rem;
            font-size: .86rem;
            color: #1e293b;
            outline: none;
            cursor: pointer;
        }
        .selector-filtros-field__input.is-empty::placeholder { color: #94a3b8; }
        .selector-filtros-field__actions {
            display: flex;
            align-items: center;
            gap: .15rem;
            padding: 0 .35rem 0 0;
            flex-shrink: 0;
        }
        .selector-filtros-field__clear {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border: 0;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: .68rem;
            cursor: pointer;
            transition: background .15s ease, color .15s ease, opacity .15s ease;
        }
        .selector-filtros-field__clear:hover {
            background: #fee2e2;
            color: #dc2626;
        }
        .selector-filtros-field__clear.is-hidden {
            opacity: 0;
            pointer-events: none;
            width: 0;
            padding: 0;
            overflow: hidden;
        }
        .selector-filtros-field__open {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            font-size: .72rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(37, 99, 235, .28);
            transition: transform .12s ease, box-shadow .15s ease;
        }
        .selector-filtros-field__open:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, .35);
        }
        .selector-filtros-field__open:active { transform: translateY(0); }
        .filtros-panel .selector-filtros-field,
        .modulo-filtros-panel .selector-filtros-field,
        .map-filters .selector-filtros-field {
            min-height: calc(1.8125rem + 2px);
        }
        .filtros-panel .selector-filtros-field__input,
        .modulo-filtros-panel .selector-filtros-field__input,
        .map-filters .selector-filtros-field__input {
            font-size: .875rem;
            padding-top: .3125rem;
            padding-bottom: .3125rem;
        }
        .filtros-panel .selector-filtros-field__open,
        .modulo-filtros-panel .selector-filtros-field__open,
        .map-filters .selector-filtros-field__open {
            width: 28px;
            height: 28px;
        }

        /* Formularios: misma altura que .form-control */
        .selector-catalogo-wrapper .selector-filtros-field {
            min-height: calc(1.5em + .75rem + 2px);
        }
        .selector-catalogo-wrapper .selector-filtros-field__input {
            font-size: .9rem;
            padding: .375rem .5rem .375rem .75rem;
        }

        /* Pickers legacy (modales / formularios) → mismo estilo combobox filtros */
        .picker-field,
        .pedido-picker-field,
        .asig-picker-field,
        .pdv-picker-field {
            position: relative;
            display: flex;
            align-items: stretch;
            min-height: calc(1.5em + .75rem + 2px);
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
            overflow: hidden;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .picker-field:hover,
        .pedido-picker-field:hover,
        .asig-picker-field:hover,
        .pdv-picker-field:hover {
            border-color: #93c5fd;
            background: #fff;
        }
        .picker-field:focus-within,
        .pedido-picker-field:focus-within,
        .asig-picker-field:focus-within,
        .pdv-picker-field:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
            background: #fff;
        }
        .picker-field .picker-display,
        .pedido-picker-field .picker-display,
        .asig-picker-field .picker-display,
        .pdv-picker-field .picker-display {
            flex: 1 1 auto;
            min-width: 0;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: .375rem .5rem .375rem .75rem !important;
            font-size: .9rem;
            color: #1e293b;
            outline: none;
            min-height: auto !important;
        }
        .picker-field .picker-display.text-muted,
        .pedido-picker-field .picker-display.text-muted,
        .asig-picker-field .picker-display.text-muted,
        .pdv-picker-field .picker-display.text-muted {
            color: #94a3b8 !important;
        }
        .picker-field .picker-actions,
        .pedido-picker-field .picker-actions,
        .asig-picker-field .picker-actions,
        .pdv-picker-field .picker-actions {
            display: flex;
            align-items: center;
            gap: .15rem;
            padding: 0 .35rem 0 0;
            border-left: 0 !important;
            flex-shrink: 0;
        }
        .picker-field .picker-actions .btn,
        .pedido-picker-field .picker-actions .btn,
        .asig-picker-field .picker-actions .btn,
        .pdv-picker-field .picker-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            min-width: 0;
            line-height: 1;
        }
        .picker-field .picker-actions .btn[id*="Limpiar"],
        .picker-field .picker-actions .btn[title="Quitar"],
        .picker-field .picker-actions .btn[title="Quitar selección"],
        .pedido-picker-field .picker-actions .btn[id*="Limpiar"],
        .pedido-picker-field .picker-actions .btn[title="Quitar"],
        .asig-picker-field .picker-actions .btn[id*="Limpiar"],
        .asig-picker-field .picker-actions .btn[title="Quitar"],
        .pdv-picker-field .picker-actions .btn[id*="Limpiar"],
        .pdv-picker-field .picker-actions .btn[title="Quitar"] {
            width: 26px;
            height: 26px;
            border-radius: 999px !important;
            background: #f1f5f9 !important;
            color: #64748b !important;
            font-size: .68rem !important;
        }
        .picker-field .picker-actions .btn[id*="Limpiar"]:hover,
        .picker-field .picker-actions .btn[title="Quitar"]:hover,
        .pedido-picker-field .picker-actions .btn[id*="Limpiar"]:hover,
        .pedido-picker-field .picker-actions .btn[title="Quitar"]:hover,
        .asig-picker-field .picker-actions .btn[id*="Limpiar"]:hover,
        .asig-picker-field .picker-actions .btn[title="Quitar"]:hover,
        .pdv-picker-field .picker-actions .btn[id*="Limpiar"]:hover,
        .pdv-picker-field .picker-actions .btn[title="Quitar"]:hover {
            background: #fee2e2 !important;
            color: #dc2626 !important;
        }
        .picker-field .picker-actions .btn[id*="Buscar"],
        .picker-field .picker-actions .btn[id*="buscar"],
        .picker-field .picker-actions .btn-buscar-recogida,
        .picker-field .picker-actions .btn-buscar-recogida-extra,
        .pedido-picker-field .picker-actions .btn[id*="Buscar"],
        .pedido-picker-field .picker-actions .btn[id*="buscar"],
        .pedido-picker-field .picker-actions .btn-buscar-recogida-extra,
        .asig-picker-field .picker-actions .btn[id*="Buscar"],
        .asig-picker-field .picker-actions .btn-buscar-recogida,
        .pdv-picker-field .picker-actions .btn[id*="Buscar"] {
            width: 34px;
            min-width: 34px;
            height: 34px;
            border-radius: 8px !important;
            background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
            color: #fff !important;
            font-size: 0 !important;
            letter-spacing: 0;
            white-space: nowrap;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(37, 99, 235, .28) !important;
        }
        .picker-field .picker-actions .btn[id*="Buscar"]:hover,
        .pedido-picker-field .picker-actions .btn[id*="Buscar"]:hover,
        .asig-picker-field .picker-actions .btn[id*="Buscar"]:hover,
        .pdv-picker-field .picker-actions .btn[id*="Buscar"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, .35) !important;
        }
        .picker-field .picker-actions .btn[id*="Buscar"] .fa-search,
        .picker-field .picker-actions .btn[id*="buscar"] .fa-search,
        .picker-field .picker-actions .btn-buscar-recogida .fa-search,
        .picker-field .picker-actions .btn-buscar-recogida-extra .fa-search,
        .pedido-picker-field .picker-actions .btn[id*="Buscar"] .fa-search,
        .pedido-picker-field .picker-actions .btn[id*="buscar"] .fa-search,
        .pedido-picker-field .picker-actions .btn-buscar-recogida-extra .fa-search,
        .asig-picker-field .picker-actions .btn[id*="Buscar"] .fa-search,
        .asig-picker-field .picker-actions .btn-buscar-recogida .fa-search,
        .pdv-picker-field .picker-actions .btn[id*="Buscar"] .fa-search {
            font-size: .78rem !important;
        }
        .picker-field .picker-actions .btn[id*="Buscar"] .fa-search::before,
        .pedido-picker-field .picker-actions .btn[id*="Buscar"] .fa-search::before,
        .pedido-picker-field .picker-actions .btn-buscar-recogida-extra .fa-search::before,
        .asig-picker-field .picker-actions .btn[id*="Buscar"] .fa-search::before,
        .pdv-picker-field .picker-actions .btn[id*="Buscar"] .fa-search::before,
        .picker-field .picker-actions .btn-buscar-recogida .fa-search::before,
        .picker-field .picker-actions .btn-buscar-recogida-extra .fa-search::before,
        .asig-picker-field .picker-actions .btn-buscar-recogida .fa-search::before {
            content: "\f002";
        }
        .picker-field .picker-actions .btn[id*="Buscar"] .mr-1,
        .pedido-picker-field .picker-actions .btn[id*="Buscar"] .mr-1,
        .asig-picker-field .picker-actions .btn[id*="Buscar"] .mr-1,
        .pdv-picker-field .picker-actions .btn[id*="Buscar"] .mr-1 {
            margin-right: 0 !important;
        }
        .picker-actions .btn[id*="VerProductos"],
        .picker-actions .btn[id*="VerProductos"] + .btn,
        .picker-actions .btn-outline-info.btn-sm {
            width: auto !important;
            min-width: 30px !important;
            height: 30px !important;
            padding: 0 .45rem !important;
            border-radius: 8px !important;
            font-size: .72rem !important;
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1px solid #bfdbfe !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
        }
    }
    </script>
    <script>
        window.VehiculoCargaPreviewUrl = @json(rtrim(route('catalogo-selector.vehiculos'), '/'));
    </script>
    <script type="module" src="{{ asset('js/vehiculo-carga-preview.js') }}"></script>
    <script src="{{ asset('js/selector-catalogo.js') }}"></script>
    @endpush
@endonce
