@extends('layouts.app')



@section('title', 'Detalle de cosecha | AgroFusion')

@section('page_title', 'Detalle de cosecha')



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>

    <li class="breadcrumb-item"><a href="{{ route('producciones.index') }}">Cosechas</a></li>

    <li class="breadcrumb-item active">{{ $produccion->lote->nombre ?? 'Detalle' }}</li>

@endsection



@php

    $lote = $produccion->lote;

    $almacenActivo = $produccion->almacenamientos

        ?->whereNull('fechasalida')

        ->sortByDesc('fechaentrada')

        ->first();

    $responsable = $lote?->usuario

        ? trim($lote->usuario->nombre.' '.($lote->usuario->apellido ?? ''))

        : null;

    $umAbbr = $produccion->unidadMedida->abreviatura ?? 'kg';

    $umNombre = $produccion->unidadMedida->nombre ?? 'Kilogramo';

    $fotoUrl = \App\Support\EvidenciaFoto::urlDesdeImagenUrl($produccion->imagenurl);

    $presentacion = $presentacionCosecha ?? ['ok' => false];
    $historialFilas = ($historialPlanta ?? ['filas' => collect()])['filas'] ?? collect();
    $historialTotalKg = ($historialPlanta ?? ['total_kg' => 0])['total_kg'] ?? 0;
    $historialTotalEnvios = ($historialPlanta ?? ['total_envios' => 0])['total_envios'] ?? 0;

@endphp



@push('styles')

<style>

    .cos-det { --cos-green: #14532d; --cos-mint: #22c55e; }

    .cos-det__hero {

        position: relative;

        overflow: hidden;

        border-radius: 16px;

        background: linear-gradient(135deg, #052e16 0%, #14532d 42%, #16a34a 100%);

        color: #fff;

        padding: 1.5rem 1.65rem;

        margin-bottom: 1.25rem;

        box-shadow: 0 12px 32px rgba(5, 46, 22, .28);

    }

    .cos-det__hero::after {

        content: '';

        position: absolute;

        right: -40px;

        top: -50px;

        width: 200px;

        height: 200px;

        border-radius: 50%;

        background: rgba(255,255,255,.06);

        pointer-events: none;

    }

    .cos-det__kicker {

        font-size: .68rem;

        letter-spacing: .1em;

        text-transform: uppercase;

        opacity: .8;

        margin-bottom: .4rem;

    }

    .cos-det__title {

        font-size: 1.45rem;

        font-weight: 800;

        line-height: 1.25;

        margin: 0 0 .75rem;

    }

    .cos-det__chips { display: flex; flex-wrap: wrap; gap: .45rem; }

    .cos-chip {

        display: inline-flex;

        align-items: center;

        gap: .35rem;

        padding: .3rem .7rem;

        border-radius: 999px;

        font-size: .78rem;

        font-weight: 600;

        border: 1px solid transparent;

    }

    .cos-chip--glass {

        background: rgba(255,255,255,.14);

        border-color: rgba(255,255,255,.22);

        color: #fff;

    }

    .cos-chip--emerald { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }

    .cos-chip--amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }

    .cos-chip--rose { background: #fff1f2; color: #be123c; border-color: #fecdd3; }

    .cos-chip--slate { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

    .cos-chip--sky { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }

    .cos-chip--sky-wide {

        border-radius: 10px;

        padding: .42rem .8rem;

        gap: .55rem;

        max-width: 100%;

    }

    .cos-chip--sky-wide .cos-chip__sep {

        width: 1px;

        align-self: stretch;

        min-height: 1rem;

        background: rgba(14, 116, 144, .22);

        flex-shrink: 0;

    }

    .cos-chip--sky-wide .cos-chip__ubic {

        font-weight: 500;

        font-size: .74rem;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;

    }

    .cos-det__metric {

        background: rgba(255,255,255,.12);

        border: 1px solid rgba(255,255,255,.2);

        backdrop-filter: blur(6px);

        border-radius: 14px;

        padding: .85rem 1.1rem;

        text-align: center;

        min-width: 150px;

    }

    .cos-det__metric-val {

        font-size: 1.85rem;

        font-weight: 800;

        line-height: 1.1;

        letter-spacing: -.02em;

    }

    .cos-det__metric-lbl {

        font-size: .72rem;

        opacity: .88;

        margin-top: .2rem;

        text-transform: uppercase;

        letter-spacing: .05em;

    }

    .cos-det__panel {

        background: #fff;

        border: 1px solid #e2e8f0;

        border-radius: 14px;

        box-shadow: 0 4px 18px rgba(15, 23, 42, .05);

        padding: 1.15rem 1.25rem;

        margin-bottom: 1rem;

    }

    .cos-det__panel-title {

        font-size: .72rem;

        font-weight: 700;

        letter-spacing: .07em;

        text-transform: uppercase;

        color: #64748b;

        margin-bottom: 1rem;

        padding-bottom: .65rem;

        border-bottom: 1px solid #f1f5f9;

        display: flex;

        align-items: center;

        gap: .45rem;

    }

    .cos-det__panel-title i { color: #16a34a; }

    .cos-det__grid {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: .65rem 1rem;

        align-items: start;

    }

    .cos-det__grid-cell {

        display: flex;

        flex-direction: column;

        min-height: 3.35rem;

    }

    @media (max-width: 576px) {

        .cos-det__grid { grid-template-columns: 1fr; }

        .cos-chip--sky-wide { flex-wrap: wrap; }

        .cos-chip--sky-wide .cos-chip__ubic { white-space: normal; }

    }

    .cos-det__field-label {

        display: block;

        font-size: .68rem;

        font-weight: 600;

        letter-spacing: .06em;

        text-transform: uppercase;

        color: #94a3b8;

        margin-bottom: .35rem;

    }

    .cos-det__field-value {

        font-size: .95rem;

        font-weight: 600;

        color: #0f172a;

        line-height: 1.4;

    }

    .cos-det__field-value--accent { color: #15803d; font-size: 1.05rem; }

    .cos-det__field-sub {

        display: block;

        font-size: .78rem;

        font-weight: 500;

        color: #64748b;

        margin-top: .2rem;

    }

    .cos-det__field--wide { grid-column: 1 / -1; }

    .cos-det__photo-wrap {

        border-radius: 12px;

        overflow: hidden;

        border: 1px solid #e2e8f0;

        background: #fff;

    }

    .cos-det__photo-frame {

        padding: .85rem;

        text-align: center;

        background: #f8fafc;

        border-bottom: 1px solid #e2e8f0;

    }

    .cos-det__photo-wrap a {

        display: inline-block;

        max-width: 100%;

        line-height: 0;

    }

    .cos-det__photo-wrap img {

        width: auto;

        max-width: min(100%, 420px);

        height: auto;

        max-height: 260px;

        object-fit: contain;

        display: block;

        margin: 0 auto;

        cursor: zoom-in;

        image-rendering: -webkit-optimize-contrast;

    }

    .cos-det__photo-trigger {

        border: 0;

        background: transparent;

        padding: 0;

        cursor: zoom-in;

        line-height: 0;

        display: inline-block;

        max-width: 100%;

    }

    .cos-det__photo-cap {

        padding: .7rem 1rem;

        font-size: .78rem;

        color: #64748b;

        background: #fff;

        line-height: 1.5;

        display: flex;

        flex-direction: column;

        gap: .3rem;

    }

    .cos-det__photo-cap-text {

        display: flex;

        align-items: flex-start;

        gap: .35rem;

    }

    .cos-det__photo-cap-link {

        font-size: .78rem;

        font-weight: 600;

        color: #15803d;

        padding-left: 1.15rem;

        text-decoration: none;

        border: 0;

        background: none;

        text-align: left;

        cursor: pointer;

    }

    .cos-det__photo-cap-link:hover {

        color: #166534;

        text-decoration: underline;

    }

    .cos-det__field-traz-code {

        font-family: ui-monospace, monospace;

        font-size: .9rem;

        letter-spacing: .01em;

        word-break: break-all;

    }

    .cos-det__modal-foto .modal-dialog {

        max-width: min(94vw, 980px);

    }

    .cos-det__modal-foto-body {

        min-height: 480px;

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        background: #f8fafc;

        border-radius: 10px;

        margin: 0 .5rem;

        padding: 1.25rem;

    }

    .cos-det__modal-foto-img {

        width: min(100%, 760px);

        min-width: min(100%, 560px);

        max-height: 72vh;

        height: auto;

        object-fit: contain;

        border-radius: 8px;

    }

    @media (max-width: 576px) {

        .cos-det__modal-foto-body { min-height: 320px; padding: .75rem; }

        .cos-det__modal-foto-img { min-width: 100%; width: 100%; }

    }

    .cos-det__lote-card {

        background: linear-gradient(160deg, #f0fdf4, #fff);

        border: 1px solid #bbf7d0;

        border-radius: 14px;

        padding: 1.1rem 1.15rem;

        margin-bottom: 1rem;

    }

    .cos-det__lote-name {

        font-size: 1.05rem;

        font-weight: 800;

        color: #14532d;

        margin-bottom: .5rem;

    }

    .cos-det__avatar {

        width: 38px;

        height: 38px;

        border-radius: 50%;

        background: linear-gradient(135deg, #dcfce7, #86efac);

        color: #166534;

        font-weight: 800;

        font-size: .9rem;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        flex-shrink: 0;

    }

    .cos-det__person {

        display: flex;

        align-items: center;

        gap: .65rem;

        margin-bottom: .85rem;

    }

    .cos-det__btn-primary {

        display: flex;

        align-items: center;

        justify-content: center;

        gap: .45rem;

        width: 100%;

        padding: .7rem 1rem;

        border-radius: 11px;

        font-weight: 700;

        font-size: .88rem;

        border: none;

        background: linear-gradient(135deg, #14532d, #16a34a);

        color: #fff;

        box-shadow: 0 4px 14px rgba(22, 163, 74, .35);

        transition: filter .15s ease, transform .15s ease;

    }

    .cos-det__btn-primary:hover {

        color: #fff;

        filter: brightness(1.06);

        transform: translateY(-1px);

        text-decoration: none;

    }

    .cos-det__btn-ghost {

        display: flex;

        align-items: center;

        justify-content: center;

        gap: .4rem;

        width: 100%;

        padding: .6rem 1rem;

        border-radius: 11px;

        font-weight: 600;

        font-size: .86rem;

        border: 1px solid #e2e8f0;

        background: #fff;

        color: #475569;

        margin-top: .5rem;

    }

    .cos-det__btn-ghost:hover {

        background: #f8fafc;

        color: #334155;

        text-decoration: none;

    }

    .cos-det__hint {

        font-size: .78rem;

        color: #94a3b8;

        line-height: 1.45;

        margin-top: .85rem;

        padding-top: .85rem;

        border-top: 1px dashed #e2e8f0;

    }

    .cos-det__venta-row {

        display: flex;

        justify-content: space-between;

        align-items: center;

        padding: .55rem 0;

        border-bottom: 1px solid #f1f5f9;

        font-size: .88rem;

    }

    .cos-det__venta-row:last-child { border-bottom: 0; }

    .cos-det__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 767px) { .cos-det__stats { grid-template-columns: 1fr; } }
    .cos-det__stat {
        border-radius: 12px;
        padding: .85rem 1rem;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .cos-det__stat--vol { border-left: 4px solid #16a34a; background: linear-gradient(135deg, #f0fdf4, #fff); }
    .cos-det__stat--uni { border-left: 4px solid #334155; background: linear-gradient(135deg, #f8fafc, #fff); }
    .cos-det__stat--emp { border-left: 4px solid #0e7490; background: linear-gradient(135deg, #ecfeff, #fff); }
    .cos-det__stat-lbl {
        font-size: .68rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #64748b; margin-bottom: .25rem;
    }
    .cos-det__stat-val { font-size: 1.05rem; font-weight: 800; color: #0f172a; line-height: 1.3; }
    .cos-det__stat-sub { font-size: .76rem; color: #64748b; margin-top: .2rem; }

    .cos-det__envios-table { font-size: .84rem; margin: 0; }
    .cos-det__envios-table thead th {
        background: #f8fafc; border: 0; font-size: .68rem; text-transform: uppercase;
        letter-spacing: .05em; color: #64748b; padding: .65rem .75rem;
    }
    .cos-det__envios-table tbody td {
        padding: .75rem; vertical-align: middle; border-top: 1px solid #f1f5f9;
    }
    .cos-det__envios-empty {
        text-align: center; padding: 1.5rem 1rem; color: #64748b; font-size: .88rem;
    }
    .cos-det__vinculo-pill {
        display: inline-block; font-size: .65rem; font-weight: 700; letter-spacing: .03em;
        text-transform: uppercase; padding: .15rem .45rem; border-radius: 999px;
        background: #f1f5f9; color: #475569;
    }
    .cos-det__vinculo-pill--lote { background: #ecfdf5; color: #047857; }

</style>

@endpush



@section('content')

<div class="cos-det">



    <div class="cos-det__hero">

        <div class="d-flex flex-wrap justify-content-between align-items-start" style="position:relative;z-index:1;gap:1rem;">

            <div class="flex-grow-1" style="min-width:220px;">

                <div class="cos-det__kicker"><i class="fas fa-tractor mr-1"></i> Cosecha completada</div>

                <h1 class="cos-det__title">{{ $lote->nombre ?? 'Lote sin nombre' }}</h1>

                <div class="cos-det__chips">

                    @if($lote?->cultivo_etiqueta)

                        <span class="cos-chip cos-chip--glass"><i class="fas fa-seedling"></i>{{ $lote->cultivo_etiqueta }}</span>

                    @endif

                    @if($produccion->fechacosecha)

                        <span class="cos-chip cos-chip--glass"><i class="far fa-calendar-alt"></i>{{ \Carbon\Carbon::parse($produccion->fechacosecha)->format('d/m/Y') }}</span>

                    @endif

                </div>

            </div>

            <div class="cos-det__metric">

                <div class="cos-det__metric-val">{{ number_format($produccion->cantidad ?? 0, 2) }} <span style="font-size:1rem;font-weight:600;">{{ $umAbbr }}</span></div>

                <div class="cos-det__metric-lbl">Cantidad cosechada</div>

            </div>

        </div>

    </div>



    <div class="row">

        <div class="col-lg-8">

            <div class="cos-det__panel">

                <div class="cos-det__panel-title"><i class="fas fa-clipboard-list"></i> Resumen de la cosecha</div>

                <div class="cos-det__stats">
                    <div class="cos-det__stat cos-det__stat--vol">
                        <div class="cos-det__stat-lbl"><i class="fas fa-weight-hanging mr-1"></i> Volumen cosechado</div>
                        <div class="cos-det__stat-val">{{ number_format($produccion->cantidad ?? 0, 2, ',', '.') }} {{ $umAbbr }}</div>
                        <div class="cos-det__stat-sub">{{ $umNombre }}</div>
                    </div>
                    <div class="cos-det__stat cos-det__stat--uni">
                        <div class="cos-det__stat-lbl"><i class="fas fa-cubes mr-1"></i> Unidades estimadas</div>
                        <div class="cos-det__stat-val">
                            @if($presentacion['ok'] ?? false)
                                ~{{ number_format($presentacion['unidades'] ?? 0, 0, ',', '.') }}
                            @else
                                —
                            @endif
                        </div>
                        <div class="cos-det__stat-sub">
                            @if($presentacion['calibre_nombre'] ?? null)
                                Calibre {{ $presentacion['calibre_nombre'] }}
                            @else
                                Según calibre del lote
                            @endif
                        </div>
                    </div>
                    <div class="cos-det__stat cos-det__stat--emp">
                        <div class="cos-det__stat-lbl"><i class="fas fa-box-open mr-1"></i> Empaque estimado</div>
                        <div class="cos-det__stat-val">
                            @if($presentacion['ok'] ?? false)
                                ~{{ number_format($presentacion['empaques'] ?? 0, 0, ',', '.') }}
                                {{ $presentacion['empaque_label'] ?? 'empaques' }}
                            @else
                                —
                            @endif
                        </div>
                        <div class="cos-det__stat-sub">Referencia para planificar envíos</div>
                    </div>
                </div>

                <div class="cos-det__grid">

                    <div class="cos-det__grid-cell">

                        <span class="cos-det__field-label"><i class="fas fa-calendar-check mr-1"></i> Fecha</span>

                        <span class="cos-det__field-value">

                            {{ $produccion->fechacosecha ? \Carbon\Carbon::parse($produccion->fechacosecha)->format('d/m/Y') : '—' }}

                        </span>

                    </div>

                    <div class="cos-det__grid-cell">

                        <span class="cos-det__field-label"><i class="fas fa-seedling mr-1"></i> Semilla / cultivo</span>

                        <span class="cos-det__field-value">

                            @if($lote?->cultivo_etiqueta)

                                <span class="cos-chip cos-chip--emerald"><i class="fas fa-leaf"></i>{{ $lote->cultivo_etiqueta }}</span>

                            @else

                                <span class="text-muted font-weight-normal">Sin asignar</span>

                            @endif

                        </span>

                    </div>

                    <div class="cos-det__grid-cell">

                        <span class="cos-det__field-label"><i class="fas fa-warehouse mr-1"></i> Almacén</span>

                        <span class="cos-det__field-value">

                            @if($almacenActivo?->almacen)

                                <span class="cos-chip cos-chip--sky cos-chip--sky-wide">

                                    <i class="fas fa-box"></i>

                                    <span>{{ $almacenActivo->almacen->nombre }}</span>

                                    @if($almacenActivo->almacen->ubicacion)

                                        <span class="cos-chip__sep" aria-hidden="true"></span>

                                        <span class="cos-chip__ubic"><i class="fas fa-map-pin mr-1"></i>{{ $almacenActivo->almacen->ubicacion }}</span>

                                    @endif

                                </span>

                            @else

                                <span class="text-muted font-weight-normal">Pendiente de ingreso a almacén</span>

                            @endif

                        </span>

                    </div>

                    <div class="cos-det__grid-cell">

                        <span class="cos-det__field-label"><i class="fas fa-ruler-combined mr-1"></i> Superficie del lote</span>

                        <span class="cos-det__field-value">{{ $lote?->superficie_etiqueta ?? '—' }}</span>

                    </div>

                    @if($lote?->codigo_trazabilidad)

                    <div class="cos-det__grid-cell">

                        <span class="cos-det__field-label"><i class="fas fa-qrcode mr-1"></i> Código de trazabilidad</span>

                        <span class="cos-det__field-value cos-det__field-traz-code">{{ $lote->codigo_trazabilidad }}</span>

                    </div>

                    @endif

                </div>

            </div>



            <div class="cos-det__panel">

                <div class="cos-det__panel-title d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <span><i class="fas fa-truck-loading"></i> Registro de envíos a planta</span>

                    @if($historialTotalEnvios > 0)

                        <span class="cos-chip cos-chip--slate">{{ $historialTotalEnvios }} envío(s) · {{ number_format($historialTotalKg, 2, ',', '.') }} kg</span>

                    @endif

                </div>

                @if($historialFilas->isEmpty())

                    <div class="cos-det__envios-empty">

                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem;opacity:.45;"></i>

                        Aún no hay envíos registrados hacia planta con material de esta cosecha.

                    </div>

                @else

                    <div class="table-responsive">

                        <table class="table cos-det__envios-table mb-0">

                            <thead>

                                <tr>

                                    <th>Fecha</th>

                                    <th>Envío</th>

                                    <th>Cantidad / empaque</th>

                                    <th>Transportista</th>

                                    <th>Vehículo</th>

                                    <th>Estado</th>

                                    <th></th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($historialFilas as $fila)

                                <tr>

                                    <td class="text-nowrap">{{ $fila['fecha_fmt'] }}</td>

                                    <td>

                                        <strong>{{ $fila['codigo_envio'] }}</strong>

                                        @if($fila['vinculo'] === 'lote')

                                            <span class="cos-det__vinculo-pill cos-det__vinculo-pill--lote d-block mt-1">Lote directo</span>

                                        @endif

                                    </td>

                                    <td>

                                        <div>{{ $fila['presentacion_linea'] }}</div>

                                        @if($fila['empaque'])

                                            <small class="text-muted d-block">{{ $fila['empaque'] }}</small>

                                        @endif

                                    </td>

                                    <td>{{ $fila['transportista'] }}</td>

                                    <td>{{ $fila['vehiculo'] }}</td>

                                    <td><span class="badge badge-{{ $fila['estado_clase'] }}">{{ $fila['estado'] }}</span></td>

                                    <td class="text-right text-nowrap">

                                        <a href="{{ $fila['url_envio'] }}" class="btn btn-sm btn-outline-secondary">Ver envío</a>

                                    </td>

                                </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </div>



            @if($produccion->observaciones)

            <div class="cos-det__panel">

                <div class="cos-det__panel-title"><i class="fas fa-comment-dots"></i> Observaciones</div>

                <p class="mb-0 text-secondary" style="font-size:.92rem;line-height:1.55;">{{ $produccion->observaciones }}</p>

            </div>

            @endif



            @if($fotoUrl)

            <div class="cos-det__panel">

                <div class="cos-det__panel-title"><i class="fas fa-camera"></i> Evidencia fotográfica</div>

                <div class="cos-det__photo-wrap">

                    <div class="cos-det__photo-frame">

                        <button type="button" class="cos-det__photo-trigger" data-toggle="modal" data-target="#modalFotoCosecha" title="Ver imagen en tamaño completo">

                            <img src="{{ $fotoUrl }}" alt="Evidencia de cosecha — {{ $lote->nombre ?? '' }}" loading="eager" decoding="async">

                        </button>

                    </div>

                    <div class="cos-det__photo-cap">

                        <span class="cos-det__photo-cap-text"><i class="fas fa-image mt-1"></i> Registro visual de la cosecha en campo</span>

                        <button type="button" class="cos-det__photo-cap-link" data-toggle="modal" data-target="#modalFotoCosecha">Ver en tamaño completo <i class="fas fa-expand ml-1" style="font-size:.7rem;"></i></button>

                    </div>

                </div>

            </div>

            @endif

        </div>



        <div class="col-lg-4">

            @if($lote)

            <div class="cos-det__lote-card">

                <div class="cos-det__panel-title" style="border:0;padding:0;margin-bottom:.75rem;"><i class="fas fa-map-marked-alt"></i> Lote origen</div>

                <div class="cos-det__lote-name">{{ $lote->nombre }}</div>

                @if($responsable)

                <div class="cos-det__person">

                    <span class="cos-det__avatar">{{ mb_strtoupper(mb_substr($responsable, 0, 1)) }}</span>

                    <div>

                        <span class="cos-det__field-label" style="margin:0;">Encargado</span>

                        <span class="cos-det__field-value" style="font-size:.88rem;">{{ $responsable }}</span>

                    </div>

                </div>

                @endif

                <a href="{{ route('lotes.trazabilidad', $lote) }}" class="cos-det__btn-primary">

                    <i class="fas fa-route"></i> Ir al lote — trazabilidad

                </a>

                <a href="{{ route('lotes.show', $lote) }}" class="cos-det__btn-ghost">

                    <i class="fas fa-eye"></i> Ver ficha del lote

                </a>

            </div>

            @endif



            <div class="cos-det__panel">

                <a href="{{ route('producciones.index') }}" class="cos-det__btn-ghost" style="margin-top:0;">

                    <i class="fas fa-arrow-left"></i> Volver al listado de cosechas

                </a>

                <p class="cos-det__hint mb-0">

                    <i class="fas fa-info-circle mr-1"></i>

                    Esta pantalla es solo consulta. Certificación, almacén y siguientes pasos se gestionan desde el <strong>lote</strong>.

                </p>

            </div>

        </div>

    </div>

</div>

@if($fotoUrl)
<div class="modal fade cos-det__modal-foto" id="modalFotoCosecha" tabindex="-1" role="dialog" aria-labelledby="modalFotoCosechaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFotoCosechaLabel"><i class="fas fa-camera mr-2 text-success"></i>Evidencia fotográfica</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-3 px-3">
                <div class="cos-det__modal-foto-body">
                    <img src="{{ $fotoUrl }}" alt="Evidencia de cosecha — {{ $lote->nombre ?? '' }}" class="cos-det__modal-foto-img">
                </div>
                <p class="text-muted small mb-0 mt-3 text-center">Registro visual de la cosecha en campo</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

