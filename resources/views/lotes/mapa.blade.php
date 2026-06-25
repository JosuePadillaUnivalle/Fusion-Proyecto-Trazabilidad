@extends('layouts.app')

@section('title', 'Mapa de lotes | AgroFusion')
@section('page_title', 'Mapa de Lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}" style="color: #2c5530;">Lotes</a></li>
    <li class="breadcrumb-item active">Mapa</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    :root {
        --primary-color: #2c5530;
        --secondary-color: #4a7c59;
        --accent-color: #e8f5e8;
        --success-color: #5c7a52;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --text-dark: #1a252f;
        --text-light: #6c757d;
        --border-color: #dee2e6;
    }

    .map-container {
        height: 550px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .map-filters {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        min-width: 280px;
        max-height: 90%;
        overflow-y: auto;
    }

    .filter-group { margin-bottom: 12px; }
    .filter-group:last-child { margin-bottom: 0; }
    .filter-group label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        display: block;
        font-size: 13px;
    }
    .filter-group select, .filter-group input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 13px;
    }
    .map-filters .selector-catalogo-wrapper {
        margin-bottom: 0;
    }
    .map-filters .selector-catalogo-wrapper .input-group-sm .form-control,
    .map-filters .selector-catalogo-wrapper .btn-sm {
        font-size: 12px;
        min-height: 32px;
    }
    .map-filters .filtros-form-actions {
        margin-top: 4px;
    }
    .filter-group select:focus, .filter-group input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(44, 85, 48, 0.2);
    }

    .legend-container {
        position: absolute;
        bottom: 15px;
        left: 15px;
        z-index: 1000;
        background: white;
        padding: 12px 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .legend-title {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
        font-size: 14px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
    }
    .legend-item:last-child { margin-bottom: 0; }
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 8px;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .legend-text { font-size: 13px; color: var(--text-dark); }

    .lot-info-panel {
        position: absolute;
        bottom: 15px;
        right: 15px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        min-width: 320px;
        max-width: 380px;
        display: none;
    }
    .lot-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f3f4;
    }
    .lot-info-title { font-size: 16px; font-weight: 600; color: var(--text-dark); }
    .close-panel {
        background: none; border: none;
        font-size: 18px; color: var(--text-light);
        cursor: pointer; padding: 0;
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; transition: all 0.3s ease;
    }
    .close-panel:hover { background: #f1f3f4; color: var(--text-dark); }

    .lot-detail {
        display: grid;
        grid-template-columns: 108px minmax(0, 1fr);
        gap: .35rem .75rem;
        align-items: start;
        padding: 7px 0;
        border-bottom: 1px solid #f8f9fc;
    }
    .lot-detail:last-child { border-bottom: none; }
    .lot-detail-label {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 13px;
        line-height: 1.35;
    }
    .lot-detail-value {
        color: var(--text-light);
        font-size: 13px;
        line-height: 1.4;
        text-align: right;
        word-break: break-word;
        overflow-wrap: anywhere;
        min-width: 0;
    }
    .lot-detail--ubicacion .lot-detail-value {
        text-align: left;
    }
    .lot-detail--estado {
        align-items: center;
    }
    .lot-detail--estado .lot-status-badge {
        justify-self: end;
    }

    .lot-status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
    }

    .lot-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 2px solid #f1f3f4;
    }
    .lot-action-btn {
        width: 100%;
        margin: 0 !important;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        display: block;
        box-sizing: border-box;
    }

    .mapa-kpi-panel {
        margin-bottom: 20px;
    }
    .mapa-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }
    .mapa-kpi-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: #fff;
        border: 1px solid #e8ecef;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        transition: box-shadow .2s ease, transform .15s ease;
    }
    .mapa-kpi-card:hover {
        box-shadow: 0 8px 22px rgba(44, 85, 48, .08);
        transform: translateY(-1px);
    }
    .mapa-kpi-card__icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .mapa-kpi-card--total .mapa-kpi-card__icon {
        background: #eef2f7;
        color: #475569;
    }
    .mapa-kpi-card--produccion .mapa-kpi-card__icon {
        background: #ecfdf5;
        color: #059669;
    }
    .mapa-kpi-card--cosecha .mapa-kpi-card__icon {
        background: #fffbeb;
        color: #b45309;
    }
    .mapa-kpi-card--hectareas .mapa-kpi-card__icon {
        background: #f0fdf4;
        color: #2c5530;
    }
    .mapa-kpi-card__body {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .mapa-kpi-card__value {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.02em;
        color: var(--text-dark);
    }
    .mapa-kpi-card--produccion .mapa-kpi-card__value { color: #059669; }
    .mapa-kpi-card--cosecha .mapa-kpi-card__value { color: #b45309; }
    .mapa-kpi-card--hectareas .mapa-kpi-card__value { color: #2c5530; }
    .mapa-kpi-card__label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
    }
    @media (max-width: 992px) {
        .mapa-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 520px) {
        .mapa-kpi-grid { grid-template-columns: 1fr; }
        .mapa-kpi-card { padding: 14px 16px; }
        .mapa-kpi-card__value { font-size: 1.45rem; }
    }

    .btn-primary { background: var(--primary-color); border-color: var(--primary-color); }
    .btn-primary:hover { background: var(--secondary-color); border-color: var(--secondary-color); }

    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .card-header { background: white; border-bottom: 2px solid #f1f3f4; font-weight: 600; }

    .lot-ranking-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f4;
    }
    .lot-ranking-item:last-child { border-bottom: none; }

    .mapa-panel-inferior {
        margin-top: 1.25rem;
    }
    .mapa-panel-inferior__card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, .07);
        overflow: hidden;
        height: 100%;
    }
    .mapa-panel-inferior__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .mapa-panel-inferior__head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
    }
    .mapa-panel-inferior__head p {
        margin: .15rem 0 0;
        font-size: .78rem;
        color: var(--text-light);
    }
    .mapa-panel-inferior__badge {
        background: #ecfdf5;
        color: #166534;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        padding: .25rem .65rem;
        white-space: nowrap;
    }
    .mapa-ranking-list {
        padding: .35rem 1rem 1rem;
    }
    .mapa-ranking-row {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr) auto;
        gap: .75rem;
        align-items: center;
        padding: .75rem .85rem;
        border-radius: 12px;
        border: 1px solid transparent;
        transition: background .15s, border-color .15s, transform .15s;
        cursor: pointer;
    }
    .mapa-ranking-row + .mapa-ranking-row { margin-top: .45rem; }
    .mapa-ranking-row:hover {
        background: #f0fdf4;
        border-color: #bbf7d0;
        transform: translateY(-1px);
    }
    .mapa-ranking-pos {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .82rem;
        font-weight: 800;
        color: #475569;
        background: #f1f5f9;
        border: 2px solid #e2e8f0;
    }
    .mapa-ranking-row--1 .mapa-ranking-pos { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
    .mapa-ranking-row--2 .mapa-ranking-pos { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }
    .mapa-ranking-row--3 .mapa-ranking-pos { background: #ffedd5; border-color: #fdba74; color: #9a3412; }
    .mapa-ranking-nombre {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }
    .mapa-ranking-meta {
        font-size: .76rem;
        color: #64748b;
        margin-top: .15rem;
    }
    .mapa-ranking-stats { text-align: right; }
    .mapa-ranking-kg {
        font-size: .95rem;
        font-weight: 800;
        color: #15803d;
        line-height: 1.2;
    }
    .mapa-ranking-ha {
        font-size: .74rem;
        color: #94a3b8;
        margin-top: .1rem;
    }
    .mapa-tools-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
        padding: 1rem 1.15rem 1.15rem;
    }
    @media (min-width: 992px) {
        .mapa-tools-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .mapa-tool-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 88px;
        padding: .75rem .5rem;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #334155;
        font-size: .76rem;
        font-weight: 600;
        text-align: center;
        line-height: 1.25;
        transition: background .15s, border-color .15s, transform .15s, box-shadow .15s;
        cursor: pointer;
        text-decoration: none !important;
    }
    .mapa-tool-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, .08);
        color: #0f172a;
    }
    .mapa-tool-tile__icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .mapa-tool-tile--blue { border-color: #bfdbfe; }
    .mapa-tool-tile--blue:hover { background: #eff6ff; border-color: #93c5fd; }
    .mapa-tool-tile--blue .mapa-tool-tile__icon { background: #dbeafe; color: #1d4ed8; }
    .mapa-tool-tile--green { border-color: #bbf7d0; }
    .mapa-tool-tile--green:hover { background: #f0fdf4; border-color: #86efac; }
    .mapa-tool-tile--green .mapa-tool-tile__icon { background: #dcfce7; color: #15803d; }
    .mapa-tool-tile--teal { border-color: #a5f3fc; }
    .mapa-tool-tile--teal:hover { background: #ecfeff; border-color: #67e8f9; }
    .mapa-tool-tile--teal .mapa-tool-tile__icon { background: #cffafe; color: #0e7490; }
    .mapa-tool-tile--amber { border-color: #fde68a; }
    .mapa-tool-tile--amber:hover { background: #fffbeb; border-color: #fcd34d; }
    .mapa-tool-tile--amber .mapa-tool-tile__icon { background: #fef3c7; color: #b45309; }
    .mapa-tool-tile--slate { border-color: #e2e8f0; }
    .mapa-tool-tile--slate:hover { background: #f8fafc; border-color: #cbd5e1; }
    .mapa-tool-tile--slate .mapa-tool-tile__icon { background: #f1f5f9; color: #475569; }
    .mapa-tool-tile--dark { border-color: #cbd5e1; }
    .mapa-tool-tile--dark:hover { background: #f8fafc; border-color: #94a3b8; }
    .mapa-tool-tile--dark .mapa-tool-tile__icon { background: #e2e8f0; color: #1e293b; }
    .mapa-ranking-empty {
        text-align: center;
        color: #94a3b8;
        padding: 2rem 1rem;
    }

    .mapa-lote-marker-wrap { background: transparent; border: none; }
    .mapa-lote-marker {
        width: 28px;
        height: 28px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.28);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
    }
    .mapa-lote-marker i { transform: rotate(45deg); font-size: 11px; }
    .mapa-lote-marker--grupo {
        border-radius: 50%;
        transform: none;
        font-weight: 700;
        font-size: 13px;
    }
    .mapa-lote-marker--grupo span { line-height: 1; }

    .leaflet-tooltip.mapa-export-label-pane {
        background: #fff;
        border: 1px solid #14532d;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .18);
        padding: 0;
        pointer-events: none;
        opacity: 1 !important;
    }
    .leaflet-tooltip.mapa-export-label-pane::before { border-top-color: #14532d; }
    .mapa-export-label {
        padding: .4rem .55rem;
        max-width: 190px;
    }
    .mapa-export-label strong {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #14532d;
        line-height: 1.25;
    }
    .mapa-export-label span {
        display: block;
        font-size: 9px;
        color: #64748b;
        line-height: 1.3;
    }
    .mapa-export-mode .leaflet-control-zoom { visibility: hidden; }

    .leaflet-tooltip.mapa-lote-tooltip-pane {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .14);
        padding: 0;
        color: #1e293b;
        pointer-events: none;
    }
    .leaflet-tooltip.mapa-lote-tooltip-pane::before { border-top-color: #e2e8f0; }
    .mapa-lote-tooltip {
        padding: .55rem .7rem;
        max-width: 280px;
        min-width: 220px;
        overflow: hidden;
    }
    .mapa-lote-tooltip strong { display: block; font-size: .88rem; margin-bottom: .2rem; color: #14532d; }
    .mapa-lote-tooltip__meta {
        font-size: .76rem;
        color: #64748b;
        line-height: 1.35;
        word-break: break-word;
        overflow-wrap: anywhere;
        white-space: normal;
    }
    .mapa-lote-tooltip__lista {
        list-style: none;
        margin: .45rem 0 0;
        padding: 0;
        max-height: none;
        overflow: visible;
    }
    .mapa-lote-tooltip__lista li {
        padding: .35rem 0;
        border-top: 1px solid #f1f5f9;
    }
    .mapa-lote-tooltip__lista li:first-child { border-top: 0; padding-top: 0; }
    .mapa-lote-tooltip__nombre { display: block; font-size: .8rem; font-weight: 600; color: #0f172a; }
    .mapa-lote-tooltip__mas {
        margin-top: .45rem;
        padding-top: .4rem;
        border-top: 1px dashed #e2e8f0;
        font-size: .78rem;
        font-weight: 700;
        color: #64748b;
        text-align: center;
    }
    .mapa-grupo-lista {
        max-height: none;
        overflow: visible;
    }
    .mapa-grupo-lista--scroll {
        max-height: 210px;
        overflow-y: auto;
        padding-right: .25rem;
        margin-right: -.15rem;
    }
    .mapa-grupo-lista--scroll::-webkit-scrollbar { width: 5px; }
    .mapa-grupo-lista--scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .mapa-grupo-item {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: .55rem .65rem !important;
    }
    .mapa-grupo-item:hover { border-color: #86efac !important; background: #f0fdf4 !important; }
    .panel-grupo-intro {
        font-size: .82rem;
        color: #64748b;
        margin-bottom: .65rem;
    }

    .mapa-export-loading {
        position: absolute;
        inset: 0;
        z-index: 2000;
        background: rgba(255, 255, 255, .82);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mapa-export-loading__box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        font-weight: 600;
        color: #334155;
    }
    .mapa-share-modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }
    .mapa-share-modal .modal-header {
        background: linear-gradient(135deg, #14532d, #166534);
        color: #fff;
        border: 0;
    }
    .mapa-share-modal .close { color: #fff; opacity: .9; text-shadow: none; }
    .mapa-share-modal .modal-body { padding: 1.25rem; }
    .mapa-share-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .mapa-share-feedback {
        border-radius: 10px;
        font-size: .88rem;
        margin-bottom: 0;
    }
    .mapa-toast-msg {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 3000;
        min-width: 260px;
        max-width: 420px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
    }
</style>
@endpush

@section('content')
<!-- Panel de Estadísticas -->
<div class="mapa-kpi-panel">
    <div class="mapa-kpi-grid">
        <div class="mapa-kpi-card mapa-kpi-card--total">
            <div class="mapa-kpi-card__icon"><i class="fas fa-th-large"></i></div>
            <div class="mapa-kpi-card__body">
                <span class="mapa-kpi-card__value">{{ $stats['total'] }}</span>
                <span class="mapa-kpi-card__label">Total lotes</span>
            </div>
        </div>
        <div class="mapa-kpi-card mapa-kpi-card--produccion">
            <div class="mapa-kpi-card__icon"><i class="fas fa-seedling"></i></div>
            <div class="mapa-kpi-card__body">
                <span class="mapa-kpi-card__value">{{ $stats['en_produccion'] }}</span>
                <span class="mapa-kpi-card__label">En crecimiento</span>
            </div>
        </div>
        <div class="mapa-kpi-card mapa-kpi-card--cosecha">
            <div class="mapa-kpi-card__icon"><i class="fas fa-wheat-awn"></i></div>
            <div class="mapa-kpi-card__body">
                <span class="mapa-kpi-card__value">{{ $stats['cosechados'] }}</span>
                <span class="mapa-kpi-card__label">Cosechados</span>
            </div>
        </div>
        <div class="mapa-kpi-card mapa-kpi-card--hectareas">
            <div class="mapa-kpi-card__icon"><i class="fas fa-ruler-combined"></i></div>
            <div class="mapa-kpi-card__body">
                <span class="mapa-kpi-card__value">{{ number_format($stats['hectareas'], 2, ',', '.') }}</span>
                <span class="mapa-kpi-card__label">Hectáreas total</span>
            </div>
        </div>
    </div>
</div>

<!-- Mapa Principal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-map mr-2"></i>
                    Visualizacion Geografica de Lotes
                </h3>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="centerMap()">
                        <i class="fas fa-crosshairs"></i> Centrar
                    </button>
                    @can('lotes.create')
                    <a href="{{ route('lotes.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Lote
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="map-container" id="mapContainer">
                    <!-- Mapa Leaflet -->
                    <div id="map" style="height: 100%; width: 100%;"></div>

                    <!-- Filtros del Mapa -->
                    <div class="map-filters" id="mapFilters">
                        <h6 style="margin-bottom: 12px; color: var(--text-dark); font-weight: 600;">
                            <i class="fas fa-filter mr-2"></i>Filtros
                        </h6>
                        @unless(\App\Support\UsuarioRol::debeAcotarPorAsignacion(auth()->user()))
                        <div class="filter-group">
                            <label>Propietario</label>
                            @include('partials.selector-catalogo', [
                                'id' => 'mapa_filtro_propietario',
                                'name' => 'mapa_filtro_usuarioid',
                                'value' => '',
                                'labelSelected' => '',
                                'endpoint' => route('catalogo-selector.usuarios'),
                                'params' => ['roles' => 'agricultor,jefe_agricultor'],
                                'title' => 'Filtrar por propietario',
                                'searchPlaceholder' => 'Nombre, correo o usuario…',
                                'searchLabel' => 'Buscar propietario',
                                'allowEmpty' => true,
                                'emptyLabel' => 'Todos los propietarios',
                                'placeholderEmpty' => 'Todos los propietarios',
                                'size' => 'sm',
                                'inputGroup' => true,
                                'showLabel' => false,
                                'variant' => 'filtros',
                            ])
                        </div>
                        @endunless
                        <div class="filter-group">
                            <label>Cultivo</label>
                            @include('partials.selector-catalogo', [
                                'id' => 'mapa_filtro_cultivo',
                                'name' => 'mapa_filtro_cultivoid',
                                'value' => '',
                                'labelSelected' => '',
                                'endpoint' => route('catalogo-selector.cultivos'),
                                'title' => 'Filtrar por cultivo',
                                'searchPlaceholder' => 'Nombre del cultivo…',
                                'searchLabel' => 'Buscar cultivo',
                                'allowEmpty' => true,
                                'emptyLabel' => 'Todos los cultivos',
                                'placeholderEmpty' => 'Todos los cultivos',
                                'size' => 'sm',
                                'inputGroup' => true,
                                'showLabel' => false,
                                'modalIcon' => 'fa-seedling',
                                'rowIcon' => 'fa-leaf',
                                'variant' => 'filtros',
                            ])
                        </div>
                        <div class="filter-group">
                            <label>Estado</label>
                            <select class="form-control form-control-sm" id="filtroEstado">
                                <option value="">Todos</option>
                                @foreach($estados as $e)
                                    @php $slugOpt = \App\Support\EstadoLoteCatalogo::slugFromNombre($e->nombre); @endphp
                                    <option value="{{ $e->estadolotetipoid }}">{{ $slugOpt ? \App\Support\EstadoLoteCatalogo::label($slugOpt) : ucfirst($e->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row mt-2 mx-0">
                            <div class="col-6 pl-0">
                                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="aplicarFiltros()">
                                    <i class="fas fa-search"></i> Aplicar
                                </button>
                            </div>
                            <div class="col-6 pr-0">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="limpiarFiltros()">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Leyenda -->
                    <div class="legend-container">
                        <div class="legend-title"><i class="fas fa-info-circle mr-1"></i> Estados</div>
                        @foreach($leyendaMapa ?? [] as $item)
                        <div class="legend-item">
                            <div class="legend-color" style="background: {{ $item['color'] }}"></div>
                            <span class="legend-text">{{ $item['label'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Panel de Info del Lote -->
                    <div class="lot-info-panel" id="lotInfoPanel">
                        <div class="lot-info-header">
                            <span class="lot-info-title" id="panelLoteName">Nombre del Lote</span>
                            <button type="button" class="close-panel" onclick="cerrarPanel()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="panelGrupoWrap" class="d-none">
                            <p class="panel-grupo-intro" id="panelGrupoIntro"></p>
                            <div id="panelGrupoLista" class="mapa-grupo-lista"></div>
                        </div>
                        <div id="panelDetalleUnico">
                            <div class="lot-detail lot-detail--estado">
                                <span class="lot-detail-label">Estado</span>
                                <span id="panelEstado" class="lot-status-badge status-produccion">En Produccion</span>
                            </div>
                            <div class="lot-detail">
                                <span class="lot-detail-label">Lote</span>
                                <span class="lot-detail-value" id="panelLoteId">—</span>
                            </div>
                            <div class="lot-detail">
                                <span class="lot-detail-label">Propietario</span>
                                <span class="lot-detail-value" id="panelPropietario">-</span>
                            </div>
                            <div class="lot-detail">
                                <span class="lot-detail-label">Cultivo</span>
                                <span class="lot-detail-value" id="panelCultivo">-</span>
                            </div>
                            <div class="lot-detail">
                                <span class="lot-detail-label">Superficie</span>
                                <span class="lot-detail-value" id="panelSuperficie">—</span>
                            </div>
                            <div class="lot-detail lot-detail--ubicacion">
                                <span class="lot-detail-label">Ubicacion</span>
                                <span class="lot-detail-value" id="panelUbicacion">-</span>
                            </div>
                            <div class="lot-detail" id="panelCodigoRow" style="display:none;">
                                <span class="lot-detail-label">Trazabilidad</span>
                                <span class="lot-detail-value" id="panelCodigo" style="font-family:ui-monospace,monospace;font-size:.78rem;">—</span>
                            </div>
                            <div class="lot-actions">
                                <button type="button" class="btn btn-outline-secondary btn-sm lot-action-btn d-none" id="btnVolverGrupo" onclick="volverPanelGrupo()">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
                                </button>
                                <a href="#" id="btnVerDetalle" class="btn btn-primary btn-sm lot-action-btn">
                                    <i class="fas fa-eye mr-1"></i> Ver detalle
                                </a>
                                <a href="#" id="btnTrazabilidad" class="btn btn-outline-success btn-sm lot-action-btn">
                                    <i class="fas fa-route mr-1"></i> Trazabilidad
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel inferior: ranking + herramientas -->
<div class="mapa-panel-inferior">
    <div class="row">
        <div class="col-lg-7 mb-3 mb-lg-0">
            <div class="card mapa-panel-inferior__card">
                <div class="mapa-panel-inferior__head">
                    <div>
                        <h3><i class="fas fa-trophy text-warning mr-2"></i>Top lotes por producción</h3>
                        <p>Mayor volumen cosechado registrado en el sistema</p>
                    </div>
                    <span class="mapa-panel-inferior__badge">{{ $topLotes->count() }} en ranking</span>
                </div>
                <div class="mapa-ranking-list">
                    @forelse($topLotes as $index => $lote)
                        <div class="mapa-ranking-row mapa-ranking-row--{{ min($index + 1, 3) }}"
                             role="button"
                             tabindex="0"
                             @if($lote->latitud && $lote->longitud)
                                 onclick="enfocarLoteEnMapa({{ $lote->latitud }}, {{ $lote->longitud }}, {{ $lote->loteid }})"
                                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();enfocarLoteEnMapa({{ $lote->latitud }}, {{ $lote->longitud }}, {{ $lote->loteid }});}"
                             @endif>
                            <div class="mapa-ranking-pos">{{ $index + 1 }}</div>
                            <div>
                                <div class="mapa-ranking-nombre">{{ $lote->nombre }}</div>
                                <div class="mapa-ranking-meta">
                                    <i class="fas fa-user mr-1"></i>{{ trim(($lote->usuario->nombre ?? '') . ' ' . ($lote->usuario->apellido ?? '')) ?: 'Sin propietario' }}
                                    · {{ $lote->cultivo->nombre ?? 'Sin cultivo' }}
                                </div>
                            </div>
                            <div class="mapa-ranking-stats">
                                <div class="mapa-ranking-kg">{{ number_format($lote->total_produccion, 0, ',', '.') }} kg</div>
                                <div class="mapa-ranking-ha">{{ $lote->superficie_etiqueta }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="mapa-ranking-empty">
                            <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                            No hay datos de producción todavía
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mapa-panel-inferior__card">
                <div class="mapa-panel-inferior__head">
                    <div>
                        <h3><i class="fas fa-tools text-secondary mr-2"></i>Herramientas del mapa</h3>
                        <p>Acciones rápidas sobre la vista geográfica</p>
                    </div>
                </div>
                <div class="mapa-tools-grid">
                    <button type="button" class="mapa-tool-tile mapa-tool-tile--blue" onclick="exportarMapa()">
                        <span class="mapa-tool-tile__icon"><i class="fas fa-download"></i></span>
                        Exportar mapa
                    </button>
                    <button type="button" class="mapa-tool-tile mapa-tool-tile--green" onclick="imprimirMapa()">
                        <span class="mapa-tool-tile__icon"><i class="fas fa-print"></i></span>
                        Imprimir mapa
                    </button>
                    <button type="button" class="mapa-tool-tile mapa-tool-tile--teal" onclick="compartirMapa()">
                        <span class="mapa-tool-tile__icon"><i class="fas fa-share-alt"></i></span>
                        Compartir
                    </button>
                    <a href="{{ route('producciones.index') }}" class="mapa-tool-tile mapa-tool-tile--amber">
                        <span class="mapa-tool-tile__icon"><i class="fas fa-chart-bar"></i></span>
                        Ver reportes
                    </a>
                    <button type="button" class="mapa-tool-tile mapa-tool-tile--slate" onclick="toggleCapaSatelite()">
                        <span class="mapa-tool-tile__icon"><i class="fas fa-satellite"></i></span>
                        Vista satélite
                    </button>
                    <button type="button" class="mapa-tool-tile mapa-tool-tile--dark" onclick="toggleFullscreen()">
                        <span class="mapa-tool-tile__icon"><i class="fas fa-expand"></i></span>
                        Pantalla completa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade mapa-share-modal" id="modalCompartirMapa" tabindex="-1" role="dialog" aria-labelledby="modalCompartirMapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCompartirMapaLabel">
                    <i class="fas fa-share-alt mr-2"></i>Compartir mapa de lotes
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Envíe el enlace a otra persona para que abra esta misma vista del mapa.</p>
                <label class="small font-weight-bold text-muted mb-1" for="shareMapaUrl">Enlace del mapa</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="shareMapaUrl" readonly>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-success" id="btnCopiarEnlaceMapa">
                            <i class="fas fa-copy mr-1"></i>Copiar
                        </button>
                    </div>
                </div>
                <div class="mapa-share-actions">
                    <a href="#" target="_blank" rel="noopener" class="btn btn-success btn-sm" id="shareMapaWhatsapp">
                        <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm" id="shareMapaEmail">
                        <i class="fas fa-envelope mr-1"></i>Correo
                    </a>
                </div>
                <div class="alert alert-success mapa-share-feedback mt-3 d-none" id="shareMapaFeedback" role="status"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js"></script>
<script>
var lotesData = @json($lotesConCoordenadas);
var mapaStatsPdf = @json($stats);
var rutasLote = {
    show: @json(route('lotes.show', ['lote' => '__ID__'])),
    trazabilidad: @json(route('lotes.trazabilidad', ['lote' => '__ID__']))
};

var coloresEstado = @json($coloresEstadoMapa ?? []);
var slugEstadoPorNombre = @json($slugEstadoPorNombre ?? []);
var leyendaMapaPdf = @json($leyendaMapa ?? []);

var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});
var sateliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© Esri'
});

var map = L.map('map', { layers: [osmLayer] }).setView([-17.7833, -63.1821], 10);
var currentLayer = 'osm';
var markers = [];
var circles = [];
var grupoActual = null;
var lotesMapaActivos = lotesData;
var exportacionMapaEstado = null;
var MAPA_GRUPO_VISIBLE = 3;

function obtenerConstructorPdf() {
    if (window.jspdf && window.jspdf.jsPDF) return window.jspdf.jsPDF;
    if (typeof window.jsPDF === 'function') return window.jsPDF;
    if (window.jspdf && typeof window.jspdf === 'function') return window.jspdf;
    return null;
}

function urlLote(plantilla, id) {
    return plantilla.replace('__ID__', id);
}

function escapeHtml(texto) {
    return String(texto == null ? '' : texto)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function normalizarEstado(estado) {
    return String(estado || 'planificacion').toLowerCase().trim()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function slugEstadoLote(lote) {
    if (lote && lote.estado_slug) {
        return lote.estado_slug;
    }
    var nombre = typeof lote === 'object' ? (lote.estado || '') : String(lote || '');
    var norm = normalizarEstado(nombre);
    return slugEstadoPorNombre[norm] || slugEstadoPorNombre[nombre.toLowerCase().trim()] || 'planificado';
}

function colorEstadoLote(lote) {
    if (lote && lote.estado_color) {
        return lote.estado_color;
    }
    return coloresEstado[slugEstadoLote(lote)] || '#6c757d';
}

function etiquetaEstadoLote(lote) {
    if (!lote) return '—';
    return lote.estado || 'Planificación';
}

function valorSelectorFiltro(id) {
    var wrap = document.getElementById('selector_wrap_' + id);
    if (!wrap) return '';
    var input = wrap.querySelector('.selector-catalogo-value');
    return input ? input.value : '';
}

function calcularRadio(ha) {
    return Math.sqrt((ha || 1) * 10000 / Math.PI);
}

function claveCoordenada(lote) {
    return Number(lote.latitud).toFixed(5) + ',' + Number(lote.longitud).toFixed(5);
}

function agruparLotesPorUbicacion(lotes) {
    var mapa = {};
    lotes.forEach(function (lote) {
        var key = claveCoordenada(lote);
        if (!mapa[key]) mapa[key] = [];
        mapa[key].push(lote);
    });
    return Object.keys(mapa).map(function (key) { return mapa[key]; });
}

function htmlLineaLoteGrupo(lote) {
    return '<strong>' + escapeHtml(lote.nombre) + '</strong><br>'
        + '<small class="text-muted">' + escapeHtml((lote.propietario || '').trim()) + ' · ' + escapeHtml(lote.cultivo || '—') + ' · ' + escapeHtml(lote.estado || '') + '</small>';
}

function htmlTooltipGrupo(grupo) {
    if (grupo.length === 1) {
        var lote = grupo[0];
        return '<div class="mapa-lote-tooltip">'
            + '<strong>' + escapeHtml(lote.nombre) + '</strong>'
            + '<div class="mapa-lote-tooltip__meta">' + escapeHtml(lote.cultivo || 'Sin cultivo') + ' · ' + escapeHtml(lote.estado || '') + '</div>'
            + '<div class="mapa-lote-tooltip__meta">' + escapeHtml(lote.propietario || '') + '</div>'
            + '<div class="mapa-lote-tooltip__meta">' + escapeHtml(lote.superficie_etiqueta || '') + '</div>'
            + '</div>';
    }

    var visibles = grupo.slice(0, MAPA_GRUPO_VISIBLE);
    var restantes = grupo.length - MAPA_GRUPO_VISIBLE;
    var html = '<div class="mapa-lote-tooltip"><strong>' + grupo.length + ' lotes en esta zona</strong><ul class="mapa-lote-tooltip__lista">';
    visibles.forEach(function (lote) {
        html += '<li><span class="mapa-lote-tooltip__nombre">' + escapeHtml(lote.nombre) + '</span>'
            + '<span class="mapa-lote-tooltip__meta">' + escapeHtml((lote.propietario || '').trim()) + ' · ' + escapeHtml(lote.cultivo || '—') + ' · ' + escapeHtml(lote.estado || '') + '</span></li>';
    });
    html += '</ul>';
    if (restantes > 0) {
        html += '<div class="mapa-lote-tooltip__mas">+' + restantes + '</div>';
    }
    html += '</div>';
    return html;
}

function crearIconoMarcador(grupo) {
    var lote = grupo[0];
    var color = colorEstadoLote(lote);

    if (grupo.length > 1) {
        return L.divIcon({
            className: 'mapa-lote-marker-wrap',
            html: '<div class="mapa-lote-marker mapa-lote-marker--grupo" style="background:' + color + '"><span>' + grupo.length + '</span></div>',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
    }

    return L.divIcon({
        className: 'mapa-lote-marker-wrap',
        html: '<div class="mapa-lote-marker" style="background:' + color + '"><i class="fas fa-map-marker-alt"></i></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 28]
    });
}

function bindInteraccionesCapa(capa, grupo) {
    capa._grupoLotes = grupo;
    capa.bindTooltip(htmlTooltipGrupo(grupo), {
        direction: 'top',
        offset: [0, -10],
        opacity: 1,
        className: 'mapa-lote-tooltip-pane',
        sticky: true,
        interactive: false
    });

    capa.on('click', function () {
        if (grupo.length === 1) {
            mostrarPanelLote(grupo[0]);
        } else {
            mostrarPanelGrupo(grupo);
        }
    });
}

function cargarLotes(lotes) {
    lotesMapaActivos = lotes;
    markers.forEach(function (m) { map.removeLayer(m); });
    circles.forEach(function (c) { map.removeLayer(c); });
    markers = [];
    circles = [];
    cerrarPanel();

    lotes.forEach(function (lote) {
        var color = colorEstadoLote(lote);
        var circle = L.circle([lote.latitud, lote.longitud], {
            color: color,
            fillColor: color,
            fillOpacity: 0.35,
            weight: 2,
            radius: calcularRadio(lote.superficie || 1)
        }).addTo(map);
        circles.push(circle);
    });

    agruparLotesPorUbicacion(lotes).forEach(function (grupo) {
        var lote = grupo[0];
        var marker = L.marker([lote.latitud, lote.longitud], {
            icon: crearIconoMarcador(grupo),
            zIndexOffset: grupo.length > 1 ? 500 : 100
        }).addTo(map);
        marker._grupoLotes = grupo;
        markers.push(marker);
        bindInteraccionesCapa(marker, grupo);

        circles.filter(function (circle) {
            var latLng = circle.getLatLng();
            return claveCoordenada({ latitud: latLng.lat, longitud: latLng.lng }) === claveCoordenada(lote);
        }).forEach(function (circle) {
            bindInteraccionesCapa(circle, grupo);
        });
    });

    if (markers.length > 0) {
        map.fitBounds(L.featureGroup(markers).getBounds().pad(0.1));
    }
}

function claseEstadoBadge(lote) {
    return 'lot-status-badge';
}

function aplicarBadgeEstado(el, lote) {
    if (!el) return;
    var color = colorEstadoLote(lote);
    el.className = 'lot-status-badge';
    el.style.background = color;
    el.style.color = slugEstadoLote(lote) === 'cosechado' ? '#1f2937' : '#fff';
    el.textContent = etiquetaEstadoLote(lote);
}

function mostrarPanelGrupo(grupo) {
    grupoActual = grupo;
    document.getElementById('panelGrupoWrap').classList.remove('d-none');
    document.getElementById('panelDetalleUnico').classList.add('d-none');
    document.getElementById('panelLoteName').textContent = grupo.length + ' lotes en la misma zona';

    var lista = document.getElementById('panelGrupoLista');
    var usarScroll = grupo.length > MAPA_GRUPO_VISIBLE;

    document.getElementById('panelGrupoIntro').textContent = usarScroll
        ? 'Seleccione un lote. Deslice la lista para ver más.'
        : 'Seleccione un lote para ver el detalle completo.';

    lista.className = 'mapa-grupo-lista' + (usarScroll ? ' mapa-grupo-lista--scroll' : '');
    lista.innerHTML = '';

    grupo.forEach(function (lote) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'mapa-grupo-item btn btn-light btn-sm btn-block text-left mb-2';
        btn.innerHTML = htmlLineaLoteGrupo(lote);
        btn.addEventListener('click', function () {
            mostrarPanelLote(lote, true);
        });
        lista.appendChild(btn);
    });

    document.getElementById('lotInfoPanel').style.display = 'block';
}

function mostrarPanelLote(lote, desdeGrupo) {
    if (!desdeGrupo) {
        grupoActual = [lote];
    }

    document.getElementById('panelGrupoWrap').classList.add('d-none');
    document.getElementById('panelDetalleUnico').classList.remove('d-none');
    document.getElementById('panelLoteName').textContent = lote.nombre;
    document.getElementById('panelLoteId').textContent = '#' + lote.id;
    document.getElementById('panelPropietario').textContent = (lote.propietario || '').trim() || '—';
    document.getElementById('panelCultivo').textContent = lote.cultivo || 'Sin cultivo';
    document.getElementById('panelSuperficie').textContent = lote.superficie_etiqueta || (lote.superficie + ' ha');
    document.getElementById('panelUbicacion').textContent = lote.ubicacion_visible || lote.ubicacion || 'Sin ubicación';

    var panelEstado = document.getElementById('panelEstado');
    aplicarBadgeEstado(panelEstado, lote);

    var codigoRow = document.getElementById('panelCodigoRow');
    if (lote.codigo_trazabilidad) {
        codigoRow.style.display = '';
        document.getElementById('panelCodigo').textContent = lote.codigo_trazabilidad;
    } else {
        codigoRow.style.display = 'none';
    }

    document.getElementById('btnVerDetalle').href = urlLote(rutasLote.show, lote.id);
    document.getElementById('btnTrazabilidad').href = urlLote(rutasLote.trazabilidad, lote.id);

    var btnVolver = document.getElementById('btnVolverGrupo');
    if (btnVolver) {
        btnVolver.classList.toggle('d-none', !(desdeGrupo && grupoActual && grupoActual.length > 1));
    }

    document.getElementById('lotInfoPanel').style.display = 'block';
}

function volverPanelGrupo() {
    if (grupoActual && grupoActual.length > 1) {
        mostrarPanelGrupo(grupoActual);
    }
}

function cerrarPanel() {
    document.getElementById('lotInfoPanel').style.display = 'none';
    grupoActual = null;
}

function toggleFilters() {
    var filters = document.getElementById('mapFilters');
    filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
}

function centerMap() {
    if (markers.length > 0) {
        map.fitBounds(L.featureGroup(markers).getBounds().pad(0.1));
    } else {
        map.setView([-17.7833, -63.1821], 10);
    }
}

function aplicarFiltros() {
    var usuario = valorSelectorFiltro('mapa_filtro_propietario');
    var cultivo = valorSelectorFiltro('mapa_filtro_cultivo');
    var estado = document.getElementById('filtroEstado').value;

    var lotesFiltrados = lotesData.filter(function (lote) {
        if (usuario && String(lote.usuarioid) !== String(usuario)) return false;
        if (cultivo && String(lote.cultivoid) !== String(cultivo)) return false;
        if (estado && String(lote.estadoid) !== String(estado)) return false;
        return true;
    });

    cargarLotes(lotesFiltrados);
}

function limpiarFiltros() {
    if (window.CatalogoSelector) {
        CatalogoSelector.clear('mapa_filtro_propietario');
        CatalogoSelector.clear('mapa_filtro_cultivo');
    }
    document.getElementById('filtroEstado').value = '';
    cargarLotes(lotesData);
}

function enfocarLote(lat, lng) {
    map.setView([lat, lng], 15);
}

function enfocarLoteEnMapa(lat, lng, loteId) {
    enfocarLote(lat, lng);
    var lote = lotesData.find(function (item) { return String(item.id) === String(loteId); });
    if (lote) {
        mostrarPanelLote(lote);
    }
}

function toggleCapaSatelite() {
    if (currentLayer === 'osm') {
        map.removeLayer(osmLayer);
        map.addLayer(sateliteLayer);
        currentLayer = 'satelite';
    } else {
        map.removeLayer(sateliteLayer);
        map.addLayer(osmLayer);
        currentLayer = 'osm';
    }
}

function toggleFullscreen() {
    var container = document.getElementById('mapContainer');
    if (!document.fullscreenElement) {
        container.requestFullscreen().catch(function () {
            mostrarToastMapa('No se pudo activar pantalla completa.', 'warning');
        });
    } else {
        document.exitFullscreen();
    }
}

function mostrarCargaMapaExport(mostrar, texto) {
    var container = document.getElementById('mapContainer');
    if (!container) return;
    var el = document.getElementById('mapaExportLoading');
    if (mostrar) {
        if (!el) {
            el = document.createElement('div');
            el.id = 'mapaExportLoading';
            el.className = 'mapa-export-loading';
            el.innerHTML = '<div class="mapa-export-loading__box"><i class="fas fa-spinner fa-spin mr-2"></i><span></span></div>';
            container.appendChild(el);
        }
        el.querySelector('span').textContent = texto || 'Generando documento…';
        el.style.display = 'flex';
    } else if (el) {
        el.style.display = 'none';
    }
}

function mostrarToastMapa(mensaje, tipo) {
    var existente = document.getElementById('mapaToastMsg');
    if (existente) existente.remove();
    var toast = document.createElement('div');
    toast.id = 'mapaToastMsg';
    toast.className = 'alert alert-' + (tipo || 'success') + ' mapa-toast-msg';
    toast.textContent = mensaje;
    document.body.appendChild(toast);
    setTimeout(function () {
        if (toast.parentNode) toast.remove();
    }, 4500);
}

function mostrarFeedbackCompartir(mensaje, tipo) {
    var feedback = document.getElementById('shareMapaFeedback');
    if (!feedback) return;
    feedback.className = 'alert alert-' + (tipo || 'success') + ' mapa-share-feedback mt-3';
    feedback.textContent = mensaje;
    feedback.classList.remove('d-none');
}

function htmlEtiquetaExportGrupo(grupo) {
    if (grupo.length === 1) {
        var lote = grupo[0];
        return '<div class="mapa-export-label"><strong>' + escapeHtml(lote.nombre) + '</strong>'
            + '<span>' + escapeHtml(lote.cultivo || 'Sin cultivo') + ' · ' + escapeHtml(lote.estado || '') + '</span></div>';
    }

    var html = '<div class="mapa-export-label"><strong>' + grupo.length + ' lotes en zona</strong>';
    grupo.slice(0, 4).forEach(function (lote) {
        html += '<span>' + escapeHtml(lote.nombre) + '</span>';
    });
    if (grupo.length > 4) {
        html += '<span>+' + (grupo.length - 4) + ' más</span>';
    }
    html += '</div>';
    return html;
}

function activarVistaExportacionMapa() {
    var container = document.getElementById('mapContainer');
    if (container) {
        container.classList.add('mapa-export-mode');
    }

    exportacionMapaEstado = { marcadores: [], circlesOcultos: [] };

    circles.forEach(function (circle) {
        if (map.hasLayer(circle)) {
            map.removeLayer(circle);
            exportacionMapaEstado.circlesOcultos.push(circle);
        }
    });

    markers.forEach(function (marker) {
        var grupo = marker._grupoLotes;
        if (!grupo) {
            return;
        }
        marker.closeTooltip();
        marker.unbindTooltip();
        marker.bindTooltip(htmlEtiquetaExportGrupo(grupo), {
            permanent: true,
            direction: 'top',
            offset: [0, -14],
            opacity: 1,
            className: 'mapa-export-label-pane',
            interactive: false
        });
        marker.openTooltip();
        exportacionMapaEstado.marcadores.push(marker);
    });
}

function restaurarVistaExportacionMapa() {
    if (!exportacionMapaEstado) {
        return;
    }

    exportacionMapaEstado.marcadores.forEach(function (marker) {
        marker.closeTooltip();
        marker.unbindTooltip();
        if (marker._grupoLotes) {
            bindInteraccionesCapa(marker, marker._grupoLotes);
        }
    });

    exportacionMapaEstado.circlesOcultos.forEach(function (circle) {
        circle.addTo(map);
    });

    var container = document.getElementById('mapContainer');
    if (container) {
        container.classList.remove('mapa-export-mode');
    }

    exportacionMapaEstado = null;
}

async function capturarContenedorMapa() {
    var container = document.getElementById('mapContainer');
    if (!container || typeof html2canvas !== 'function') {
        throw new Error('No se pudo preparar la captura del mapa.');
    }

    var ocultarIds = ['mapFilters', 'lotInfoPanel', 'mapaExportLoading'];
    var estados = {};
    ocultarIds.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            estados[id] = el.style.display;
            el.style.display = 'none';
        }
    });

    activarVistaExportacionMapa();
    map.invalidateSize();
    await new Promise(function (resolve) { setTimeout(resolve, 750); });

    try {
        return await html2canvas(container, {
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            scale: 2,
            logging: false,
            imageTimeout: 15000
        });
    } finally {
        restaurarVistaExportacionMapa();
        ocultarIds.forEach(function (id) {
            var el = document.getElementById(id);
            if (el && Object.prototype.hasOwnProperty.call(estados, id)) {
                el.style.display = estados[id];
            }
        });
    }
}

function hexToRgb(hex) {
    hex = String(hex || '#6c757d').replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(function (c) { return c + c; }).join('');
    }
    return {
        r: parseInt(hex.substring(0, 2), 16) || 108,
        g: parseInt(hex.substring(2, 4), 16) || 117,
        b: parseInt(hex.substring(4, 6), 16) || 125
    };
}

function resumenLotesPdf(lotes) {
    var resumen = {
        total: lotes.length,
        enProduccion: 0,
        cosechados: 0,
        hectareas: 0
    };
    lotes.forEach(function (lote) {
        var slug = slugEstadoLote(lote);
        if (slug === 'en_crecimiento' || slug === 'sembrado' || slug === 'listo_para_cosecha') {
            resumen.enProduccion++;
        }
        if (slug === 'cosechado' || slug === 'certificado' || slug === 'finalizado') {
            resumen.cosechados++;
        }
        resumen.hectareas += parseFloat(lote.superficie) || 0;
    });
    return resumen;
}

function dibujarPiePdf(pdf, pagina, total) {
    var pageW = pdf.internal.pageSize.getWidth();
    var pageH = pdf.internal.pageSize.getHeight();
    var margen = 12;

    pdf.setDrawColor(226, 232, 240);
    pdf.setLineWidth(0.3);
    pdf.line(margen, pageH - 10, pageW - margen, pageH - 10);

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(8);
    pdf.setTextColor(148, 163, 184);
    pdf.text('AgroFusion · Reporte geográfico de lotes', margen, pageH - 5);
    pdf.text('Página ' + pagina + ' de ' + total, pageW - margen, pageH - 5, { align: 'right' });
}

function dibujarEncabezadoPdf(pdf, compacto) {
    var pageW = pdf.internal.pageSize.getWidth();
    var margen = 12;

    pdf.setFillColor(20, 83, 45);
    pdf.rect(0, 0, pageW, compacto ? 16 : 28, 'F');

    pdf.setFillColor(34, 197, 94);
    pdf.rect(0, compacto ? 16 : 28, pageW, 1.2, 'F');

    pdf.setTextColor(255, 255, 255);
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(compacto ? 11 : 16);
    pdf.text('Mapa de lotes — AgroFusion', margen, compacto ? 10 : 14);

    if (!compacto) {
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        pdf.setTextColor(220, 252, 231);
        pdf.text(
            'Reporte geográfico · Generado ' + new Date().toLocaleString('es-BO'),
            margen,
            22
        );
    }

    return compacto ? 22 : 34;
}

function dibujarTarjetasResumenPdf(pdf, lotes, y) {
    var margen = 12;
    var pageW = pdf.internal.pageSize.getWidth();
    var ancho = (pageW - (margen * 2) - 9) / 4;
    var resumen = resumenLotesPdf(lotes);
    var tarjetas = [
        { label: 'Lotes en mapa', valor: String(resumen.total), color: [20, 83, 45] },
        { label: 'En producción', valor: String(resumen.enProduccion), color: [22, 163, 74] },
        { label: 'Cosechados', valor: String(resumen.cosechados), color: [217, 119, 6] },
        { label: 'Hectáreas', valor: resumen.hectareas.toFixed(1), color: [30, 64, 175] }
    ];

    tarjetas.forEach(function (tarjeta, index) {
        var x = margen + (index * (ancho + 3));
        pdf.setFillColor(248, 250, 252);
        pdf.setDrawColor(226, 232, 240);
        pdf.setLineWidth(0.3);
        pdf.roundedRect(x, y, ancho, 16, 2, 2, 'FD');

        pdf.setFillColor(tarjeta.color[0], tarjeta.color[1], tarjeta.color[2]);
        pdf.circle(x + 4, y + 4, 1.2, 'F');

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(12);
        pdf.setTextColor(tarjeta.color[0], tarjeta.color[1], tarjeta.color[2]);
        pdf.text(tarjeta.valor, x + 7, y + 7.5);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.5);
        pdf.setTextColor(100, 116, 139);
        pdf.text(tarjeta.label, x + 7, y + 12.5);
    });

    return y + 20;
}

function dibujarMarcoImagenPdf(pdf, x, y, w, h) {
    pdf.setFillColor(255, 255, 255);
    pdf.setDrawColor(226, 232, 240);
    pdf.setLineWidth(0.4);
    pdf.roundedRect(x - 1.5, y - 1.5, w + 3, h + 3, 2, 2, 'S');
}

function dibujarLeyendaEstadosPdf(pdf, y) {
    var margen = 12;
    var items = leyendaMapaPdf.length ? leyendaMapaPdf : [
        { label: 'Planificación', color: '#6366f1' },
        { label: 'En crecimiento', color: '#22c55e' },
        { label: 'Listo para cosecha', color: '#14b8a6' },
        { label: 'Cosechado', color: '#f59e0b' },
        { label: 'Certificado', color: '#7c3aed' },
        { label: 'Finalizado', color: '#475569' }
    ];

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(8);
    pdf.setTextColor(51, 65, 85);
    pdf.text('Leyenda de estados', margen, y);
    y += 4;

    var x = margen;
    items.forEach(function (item) {
        var rgb = hexToRgb(item.color);
        pdf.setFillColor(rgb.r, rgb.g, rgb.b);
        pdf.circle(x + 1.5, y + 1.2, 1.3, 'F');
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.5);
        pdf.setTextColor(71, 85, 105);
        pdf.text(item.label, x + 4, y + 2.2);
        x += 38;
    });

    return y + 7;
}

function dibujarTablaLotesPdf(pdf, lotes, startY) {
    var margen = 12;
    var pageW = pdf.internal.pageSize.getWidth();
    var pageH = pdf.internal.pageSize.getHeight();
    var anchoTabla = pageW - (margen * 2);
    var cols = [
        { key: 'num', titulo: '#', w: 8 },
        { key: 'lote', titulo: 'Lote', w: 42 },
        { key: 'cultivo', titulo: 'Cultivo', w: 28 },
        { key: 'estado', titulo: 'Estado', w: 30 },
        { key: 'superficie', titulo: 'Superficie', w: 24 },
        { key: 'ubicacion', titulo: 'Ubicación', w: anchoTabla - 132 }
    ];
    var y = startY;
    var rowH = 11;
    var footerReserva = 14;

    function dibujarEncabezadoTabla(doc, columnas, mx, yy, ancho) {
        doc.setFillColor(20, 83, 45);
        doc.roundedRect(mx, yy, ancho, 8, 1.5, 1.5, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7.5);
        doc.setTextColor(255, 255, 255);
        var cx = mx + 2;
        columnas.forEach(function (col) {
            doc.text(col.titulo, cx, yy + 5.2);
            cx += col.w;
        });
        return yy + 8;
    }

    function nuevaPaginaTabla() {
        pdf.addPage();
        y = dibujarEncabezadoPdf(pdf, true) + 4;
        y = dibujarEncabezadoTabla(pdf, cols, margen, y, anchoTabla);
    }

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(10);
    pdf.setTextColor(30, 58, 95);
    pdf.text('Detalle de lotes (' + lotes.length + ')', margen, y);
    y += 6;

    y = dibujarEncabezadoTabla(pdf, cols, margen, y, anchoTabla);

    lotes.forEach(function (lote, index) {
        if (y + rowH > pageH - footerReserva) {
            nuevaPaginaTabla();
        }

        if (index % 2 === 0) {
            pdf.setFillColor(248, 250, 252);
        } else {
            pdf.setFillColor(255, 255, 255);
        }
        pdf.rect(margen, y, anchoTabla, rowH, 'F');

        pdf.setDrawColor(226, 232, 240);
        pdf.setLineWidth(0.15);
        pdf.line(margen, y + rowH, margen + anchoTabla, y + rowH);

        var cx = margen + 2;
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7.5);
        pdf.setTextColor(51, 65, 85);
        pdf.text(String(index + 1), cx, y + 4.5);
        cx += cols[0].w;

        pdf.text(String(lote.nombre || 'Sin nombre').substring(0, 28), cx, y + 4.2);
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(6.5);
        pdf.setTextColor(100, 116, 139);
        pdf.text(String((lote.propietario || 'Sin propietario')).trim().substring(0, 32), cx, y + 8.2);
        cx += cols[1].w;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.2);
        pdf.setTextColor(71, 85, 105);
        pdf.text(String(lote.cultivo || '—').substring(0, 18), cx, y + 6);
        cx += cols[2].w;

        var estadoRgb = hexToRgb(colorEstadoLote(lote));
        pdf.setFillColor(estadoRgb.r, estadoRgb.g, estadoRgb.b);
        pdf.circle(cx + 1.5, y + 4.8, 1.1, 'F');
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(6.8);
        pdf.setTextColor(estadoRgb.r, estadoRgb.g, estadoRgb.b);
        pdf.text(String(lote.estado || '—').substring(0, 16), cx + 4, y + 6);
        cx += cols[3].w;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.2);
        pdf.setTextColor(51, 65, 85);
        pdf.text(String(lote.superficie_etiqueta || '—').substring(0, 14), cx, y + 6);
        cx += cols[4].w;

        pdf.setTextColor(100, 116, 139);
        pdf.text(
            pdf.splitTextToSize(String(lote.ubicacion_visible || lote.ubicacion || 'Sin ubicación'), cols[5].w - 2),
            cx,
            y + 4.5
        );

        y += rowH;
    });

    return y + 4;
}

function agregarListadoLotesPdf(pdf, lotes, inicioY) {
    return dibujarTablaLotesPdf(pdf, lotes, inicioY);
}

async function generarPdfMapa(opciones) {
    opciones = opciones || {};
    var lotes = lotesMapaActivos || [];
    var JsPDF = obtenerConstructorPdf();
    if (!JsPDF) {
        throw new Error('No se cargó la librería PDF.');
    }

    mostrarCargaMapaExport(true, opciones.imprimir ? 'Preparando impresión…' : 'Generando PDF…');

    try {
        var canvas = await capturarContenedorMapa();
        var imgData = canvas.toDataURL('image/jpeg', 0.92);
        var pdf = new JsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        var pageW = pdf.internal.pageSize.getWidth();
        var pageH = pdf.internal.pageSize.getHeight();
        var margen = 12;

        var y = dibujarEncabezadoPdf(pdf, false);
        y = dibujarTarjetasResumenPdf(pdf, lotes, y);

        var maxW = pageW - (margen * 2);
        var maxH = 92;
        var ratio = canvas.width / canvas.height;
        var imgW = maxW;
        var imgH = imgW / ratio;
        if (imgH > maxH) {
            imgH = maxH;
            imgW = imgH * ratio;
        }

        dibujarMarcoImagenPdf(pdf, margen, y, imgW, imgH);
        pdf.addImage(imgData, 'JPEG', margen, y, imgW, imgH, undefined, 'FAST');
        y += imgH + 5;

        y = dibujarLeyendaEstadosPdf(pdf, y);
        dibujarTablaLotesPdf(pdf, lotes, y + 2);

        var totalPaginas = pdf.internal.getNumberOfPages();
        for (var p = 1; p <= totalPaginas; p++) {
            pdf.setPage(p);
            dibujarPiePdf(pdf, p, totalPaginas);
        }

        var nombre = 'mapa-lotes-agrofusion-' + new Date().toISOString().slice(0, 10) + '.pdf';
        if (opciones.descargar) {
            pdf.save(nombre);
        }
        if (opciones.imprimir) {
            pdf.autoPrint();
            window.open(pdf.output('bloburl'), '_blank');
        }

        return pdf;
    } finally {
        mostrarCargaMapaExport(false);
    }
}

async function exportarMapa() {
    try {
        await generarPdfMapa({ descargar: true, imprimir: false });
        mostrarToastMapa('PDF del mapa descargado correctamente.');
    } catch (error) {
        mostrarCargaMapaExport(false);
        mostrarToastMapa(error.message || 'No se pudo exportar el mapa.', 'danger');
    }
}

async function imprimirMapa() {
    try {
        await generarPdfMapa({ descargar: false, imprimir: true });
    } catch (error) {
        mostrarCargaMapaExport(false);
        mostrarToastMapa(error.message || 'No se pudo preparar la impresión.', 'danger');
    }
}

function obtenerUrlMapaCompartir() {
    return window.location.href.split('#')[0];
}

function compartirMapa() {
    var url = obtenerUrlMapaCompartir();
    var input = document.getElementById('shareMapaUrl');
    var whatsapp = document.getElementById('shareMapaWhatsapp');
    var email = document.getElementById('shareMapaEmail');
    var feedback = document.getElementById('shareMapaFeedback');

    if (input) input.value = url;
    if (whatsapp) {
        whatsapp.href = 'https://wa.me/?text=' + encodeURIComponent('Mapa de lotes AgroFusion: ' + url);
    }
    if (email) {
        email.href = 'mailto:?subject=' + encodeURIComponent('Mapa de lotes AgroFusion')
            + '&body=' + encodeURIComponent('Consulta el mapa de lotes en este enlace:\n' + url);
    }
    if (feedback) feedback.classList.add('d-none');

    $('#modalCompartirMapa').modal('show');
}

function copiarEnlaceMapa() {
    var url = obtenerUrlMapaCompartir();
    var input = document.getElementById('shareMapaUrl');
    if (input) {
        input.value = url;
        input.focus();
        input.select();
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
            mostrarFeedbackCompartir('Enlace copiado al portapapeles.');
        }).catch(function () {
            mostrarFeedbackCompartir('Seleccione el enlace y cópielo manualmente (Ctrl+C).', 'warning');
        });
        return;
    }

    try {
        document.execCommand('copy');
        mostrarFeedbackCompartir('Enlace copiado al portapapeles.');
    } catch (e) {
        mostrarFeedbackCompartir('Seleccione el enlace y cópielo manualmente (Ctrl+C).', 'warning');
    }
}

$(document).ready(function () {
    cargarLotes(lotesData);
    $('.mapa-panel-inferior__card, .mapa-kpi-panel, .card-modulo-main').css('opacity', '0').animate({ opacity: 1 }, 800);

    var btnCopiar = document.getElementById('btnCopiarEnlaceMapa');
    if (btnCopiar) {
        btnCopiar.addEventListener('click', copiarEnlaceMapa);
    }
});
</script>
@endpush