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
            padding: 1rem 1.25rem;
        }
        #modalSelectorCatalogo .sel-modal-header-inner {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        #modalSelectorCatalogo .sel-modal-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
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
        #modalSelectorCatalogo .sel-col-meta { color: #6b7280; font-size: 0.84rem; line-height: 1.45; }
        #modalSelectorCatalogo .sel-col-meta--with-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }
        #modalSelectorCatalogo .sel-col-meta--with-action .sel-col-meta-text { flex: 1; min-width: 0; }
        #modalSelectorCatalogo .sel-row-action-btn {
            flex-shrink: 0;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.2rem 0.65rem;
            border-radius: 6px;
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
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('js/selector-catalogo.js') }}"></script>
    @endpush
@endonce
