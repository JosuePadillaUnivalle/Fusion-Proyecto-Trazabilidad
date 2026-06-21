@extends('layouts.app')

@section('title', 'Rutas de distribución | AgroFusion')
@section('page_title', 'Planificar distribución')

@push('styles')
@include('punto_venta.partials.modulo-styles')
<style>
.ruta-plan-wrap { max-width: 100%; }

.ruta-plan-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 10px 40px rgba(15, 23, 42, .08);
    overflow: hidden;
    background: #fff;
}

/* ── Hero ── */
.ruta-plan-hero {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 38%, #f8fafc 100%);
    border-bottom: 1px solid rgba(22, 163, 74, .15);
    padding: 1.5rem 1.6rem 1.25rem;
    position: relative;
}
.ruta-plan-hero::after {
    content: '';
    position: absolute;
    top: -40px; right: -30px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34, 197, 94, .18) 0%, transparent 70%);
    pointer-events: none;
}
.ruta-plan-hero__title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #14532d;
    letter-spacing: -.01em;
    margin-bottom: .25rem;
}
.ruta-plan-hero__title i {
    display: inline-flex;
    align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff;
    font-size: .95rem;
    margin-right: .6rem;
    box-shadow: 0 4px 12px rgba(22, 163, 74, .3);
    vertical-align: middle;
}
.ruta-plan-hero__sub { color: #4b5563; font-size: .88rem; margin-bottom: 1rem; }
.ruta-plan-btn-nueva {
    background: linear-gradient(135deg, #15803d, #16a34a);
    border: 0;
    border-radius: 10px;
    padding: .6rem 1.35rem;
    font-weight: 700;
    font-size: .9rem;
    box-shadow: 0 4px 14px rgba(22, 163, 74, .35);
    transition: transform .15s ease, box-shadow .15s ease;
}
.ruta-plan-btn-nueva:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(22, 163, 74, .4);
    background: linear-gradient(135deg, #166534, #15803d);
    color: #fff;
}

/* ── Métricas ── */
.ruta-plan-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .65rem;
}
@media (max-width: 576px) {
    .ruta-plan-metrics { grid-template-columns: 1fr; }
}
.ruta-plan-metric {
    display: flex; align-items: center; gap: .7rem;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,.9);
    border-radius: 12px;
    padding: .65rem .85rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
}
.ruta-plan-metric__icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0;
}
.ruta-plan-metric__icon--info { background: #e0f2fe; color: #0284c7; }
.ruta-plan-metric__icon--primary { background: #dbeafe; color: #2563eb; }
.ruta-plan-metric__icon--success { background: #dcfce7; color: #16a34a; }
.ruta-plan-metric__val { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1; }
.ruta-plan-metric__lbl { font-size: .72rem; color: #64748b; font-weight: 600; margin-top: .1rem; }

/* ── Filtros ── */
.ruta-plan-filtros {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.1rem 1.6rem 1.25rem;
}
.ruta-plan-segment {
    display: inline-flex;
    flex-wrap: wrap;
    gap: .25rem;
    background: #e2e8f0;
    border-radius: 12px;
    padding: .3rem;
    margin-bottom: 1rem;
}
.ruta-plan-segment .seg-btn {
    border: 0;
    background: transparent;
    color: #64748b;
    border-radius: 9px;
    padding: .45rem 1rem;
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none !important;
    transition: all .18s ease;
    white-space: nowrap;
}
.ruta-plan-segment .seg-btn:hover {
    color: #15803d;
    background: rgba(255,255,255,.6);
}
.ruta-plan-segment .seg-btn.is-active {
    background: #fff;
    color: #15803d;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .1);
    font-weight: 700;
}
.ruta-plan-segment .seg-btn .seg-count {
    display: inline-block;
    margin-left: .2rem;
    font-size: .7rem;
    opacity: .75;
    font-weight: 600;
}
.ruta-plan-search {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .85rem 1rem;
    box-shadow: 0 2px 6px rgba(15, 23, 42, .04);
}
.ruta-plan-search label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #94a3b8;
    margin-bottom: .4rem;
}
.ruta-plan-search .input-group {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.ruta-plan-search .input-group:focus-within {
    border-color: #86efac;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, .12);
}
.ruta-plan-search .input-group-text {
    border: 0;
    background: #f8fafc;
    color: #94a3b8;
}
.ruta-plan-search .form-control {
    border: 0;
    font-size: .9rem;
    padding: .6rem .75rem;
    box-shadow: none !important;
}
.ruta-plan-search .btn-aplicar {
    background: linear-gradient(135deg, #15803d, #16a34a);
    border: 0;
    border-radius: 9px;
    font-weight: 600;
    padding: .55rem 1.1rem;
}
.ruta-plan-search .btn-limpiar {
    border-radius: 9px;
    font-weight: 600;
    border-color: #e2e8f0;
    color: #64748b;
}

/* ── Secciones ── */
.ruta-plan-bloque {
    padding: 0 1.6rem 1.25rem;
}
.ruta-plan-bloque + .ruta-plan-bloque {
    padding-top: .5rem;
    border-top: 1px dashed #e2e8f0;
}
.ruta-plan-bloque__head {
    display: flex;
    align-items: center;
    gap: .65rem;
    margin-bottom: .85rem;
    padding-top: 1rem;
}
.ruta-plan-bloque__icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; color: #fff; flex-shrink: 0;
}
.ruta-plan-bloque__icon--pedidos { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
.ruta-plan-bloque__icon--rutas { background: linear-gradient(135deg, #6366f1, #818cf8); }
.ruta-plan-bloque__titulo {
    font-size: .95rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.ruta-plan-bloque__count {
    margin-left: auto;
    font-size: .75rem;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 999px;
    padding: .2rem .65rem;
}

/* ── Tablas ── */
.ruta-plan-table-wrap {
    border: 1px solid #e8edf2;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}
.ruta-plan-table {
    margin: 0;
}
.ruta-plan-table thead th {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #94a3b8;
    font-weight: 700;
    border-top: 0;
    border-bottom: 1px solid #e8edf2;
    background: #fafbfc;
    padding: .75rem 1rem;
}
.ruta-plan-table tbody td {
    padding: .85rem 1rem;
    vertical-align: middle;
    border-color: #f1f5f9;
    font-size: .9rem;
    color: #334155;
}
.ruta-plan-table tbody tr {
    transition: background .12s ease;
}
.ruta-plan-table tbody tr:hover {
    background: #f8fafc;
}
.ruta-plan-table tbody tr:last-child td { border-bottom: 0; }
.ruta-plan-code {
    display: inline-block;
    font-family: ui-monospace, 'Cascadia Code', monospace;
    font-size: .8rem;
    font-weight: 700;
    color: #1e293b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: .2rem .55rem;
    letter-spacing: .02em;
}
.ruta-plan-planta {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    color: #15803d;
    font-weight: 600;
    font-size: .88rem;
}
.ruta-plan-planta i { font-size: .75rem; opacity: .8; }
.ruta-plan-pdv {
    display: flex; align-items: center; gap: .4rem;
}
.ruta-plan-pdv i { color: #94a3b8; font-size: .8rem; }
.ruta-plan-cantidad {
    font-weight: 700;
    color: #0f172a;
}
.ruta-plan-btn-ver {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center; justify-content: center;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
    transition: all .15s ease;
}
.ruta-plan-btn-ver:hover {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
    text-decoration: none;
}
.ruta-plan-empty {
    text-align: center;
    padding: 2.5rem 1.5rem;
    color: #94a3b8;
}
.ruta-plan-empty__icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: #f1f5f9;
    display: inline-flex;
    align-items: center; justify-content: center;
    font-size: 1.4rem;
    color: #cbd5e1;
    margin-bottom: .75rem;
}
.ruta-plan-empty p { margin: 0; font-size: .9rem; }

/* ── Footer ── */
.ruta-plan-footer {
    background: linear-gradient(180deg, #fafbfc 0%, #f8fafc 100%);
    border-top: 1px solid #e8edf2;
    padding: .85rem 1.6rem;
    font-size: .82rem;
    color: #64748b;
}
.ruta-plan-footer i { color: #16a34a; }
</style>
@endpush

@section('content')
<div class="ruta-plan-wrap">
    <div class="card ruta-plan-card">

        {{-- Hero + métricas --}}
        <div class="ruta-plan-hero">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 position-relative" style="z-index:1">
                <div class="flex-grow-1">
                    <div class="ruta-plan-hero__title">
                        <i class="fas fa-route"></i>Centro de planificación
                    </div>
                    <p class="ruta-plan-hero__sub mb-0">
                        Pedidos pendientes de ruta y repartos registrados en una sola vista.
                    </p>
                </div>
                <a href="{{ route('punto-venta.rutas.create') }}" class="btn btn-success ruta-plan-btn-nueva align-self-start">
                    <i class="fas fa-plus mr-1"></i>Nueva ruta
                </a>
            </div>
            <div class="ruta-plan-metrics mt-3 position-relative" style="z-index:1">
                <div class="ruta-plan-metric">
                    <span class="ruta-plan-metric__icon ruta-plan-metric__icon--info"><i class="fas fa-clipboard-check"></i></span>
                    <div>
                        <div class="ruta-plan-metric__val">{{ $stats['pedidos_listos'] }}</div>
                        <div class="ruta-plan-metric__lbl">Pedidos listos</div>
                    </div>
                </div>
                <div class="ruta-plan-metric">
                    <span class="ruta-plan-metric__icon ruta-plan-metric__icon--primary"><i class="fas fa-shipping-fast"></i></span>
                    <div>
                        <div class="ruta-plan-metric__val">{{ $stats['rutas_en_curso'] }}</div>
                        <div class="ruta-plan-metric__lbl">En curso</div>
                    </div>
                </div>
                <div class="ruta-plan-metric">
                    <span class="ruta-plan-metric__icon ruta-plan-metric__icon--success"><i class="fas fa-map-marked-alt"></i></span>
                    <div>
                        <div class="ruta-plan-metric__val">{{ $stats['rutas_total'] }}</div>
                        <div class="ruta-plan-metric__lbl">Rutas totales</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('punto-venta.rutas.index') }}" class="ruta-plan-filtros">
            <div class="ruta-plan-segment">
                @php
                    $tabs = [
                        'todos' => ['label' => 'Todo', 'count' => $stats['pedidos_listos'] + $stats['rutas_total']],
                        'pedidos' => ['label' => 'Pedidos listos', 'count' => $stats['pedidos_listos']],
                        'en_ruta' => ['label' => 'Rutas en curso', 'count' => $stats['rutas_en_curso']],
                        'historial' => ['label' => 'Historial', 'count' => $stats['rutas_total']],
                    ];
                @endphp
                @foreach($tabs as $key => $meta)
                <a href="{{ route('punto-venta.rutas.index', array_filter(['tab' => $key !== 'todos' ? $key : null, 'buscar' => request('buscar')])) }}"
                   class="seg-btn {{ $tab === $key ? 'is-active' : '' }}">
                    {{ $meta['label'] }}<span class="seg-count">{{ $meta['count'] }}</span>
                </a>
                @endforeach
            </div>
            <div class="ruta-plan-search">
                <label class="d-block mb-0">Búsqueda global</label>
                <div class="row align-items-center mt-1">
                    <div class="col-lg-8 mb-2 mb-lg-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}"
                                   placeholder="Solicitud, PDV, producto, planta, código de ruta o chofer…">
                        </div>
                    </div>
                    <div class="col-lg-4 d-flex" style="gap:.5rem">
                        <button type="submit" class="btn btn-success btn-aplicar flex-grow-1">
                            <i class="fas fa-filter mr-1"></i>Aplicar
                        </button>
                        <a href="{{ route('punto-venta.rutas.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-limpiar">
                            Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>

        {{-- Pedidos --}}
        @if($mostrarPedidos)
        <div class="ruta-plan-bloque">
            <div class="ruta-plan-bloque__head">
                <span class="ruta-plan-bloque__icon ruta-plan-bloque__icon--pedidos"><i class="fas fa-clipboard-list"></i></span>
                <h4 class="ruta-plan-bloque__titulo">Pedidos listos (sin ruta)</h4>
                <span class="ruta-plan-bloque__count">{{ $pedidosListos->count() }} registro{{ $pedidosListos->count() !== 1 ? 's' : '' }}</span>
            </div>
            @if($pedidosListos->isEmpty())
            <div class="ruta-plan-table-wrap">
                <div class="ruta-plan-empty">
                    <div class="ruta-plan-empty__icon"><i class="fas fa-inbox"></i></div>
                    <p>{{ $stats['pedidos_listos'] === 0 ? 'No hay pedidos listos para ruta.' : 'Ningún pedido coincide con el filtro.' }}</p>
                </div>
            </div>
            @else
            <div class="ruta-plan-table-wrap">
                <div class="table-responsive">
                    <table class="table ruta-plan-table mb-0">
                        <thead>
                            <tr>
                                <th>Solicitud</th>
                                <th>Punto de venta</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Planta origen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidosListos as $pedido)
                            @php $det = $pedido->detalles->first(); @endphp
                            <tr>
                                <td><span class="ruta-plan-code">{{ $pedido->numero_solicitud }}</span></td>
                                <td>
                                    <span class="ruta-plan-pdv">
                                        <i class="fas fa-store"></i>
                                        {{ $pedido->puntoVenta?->nombre ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ $det?->producto_nombre ?? '—' }}</td>
                                <td><span class="ruta-plan-cantidad">{{ $det ? number_format((float) $det->cantidad, 2) : '—' }}</span></td>
                                <td>
                                    <span class="ruta-plan-planta">
                                        <i class="fas fa-industry"></i>
                                        {{ $pedido->almacenMayoristaOrigen?->nombre ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Rutas --}}
        @if($mostrarRutas)
        <div class="ruta-plan-bloque">
            <div class="ruta-plan-bloque__head">
                <span class="ruta-plan-bloque__icon ruta-plan-bloque__icon--rutas"><i class="fas fa-route"></i></span>
                <h4 class="ruta-plan-bloque__titulo">
                    {{ $tab === 'en_ruta' ? 'Rutas en curso' : ($tab === 'historial' ? 'Historial de rutas' : 'Rutas registradas') }}
                </h4>
                <span class="ruta-plan-bloque__count">{{ $rutas->count() }} registro{{ $rutas->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div class="ruta-plan-table-wrap">
                <div class="table-responsive">
                    <table class="table ruta-plan-table mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Origen</th>
                                <th>Chofer</th>
                                <th>Pedidos</th>
                                <th>Estado</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rutas as $ruta)
                            @php $badge = \App\Support\RutaDistribucionCatalogo::badgeEstado($ruta); @endphp
                            <tr>
                                <td><span class="ruta-plan-code">{{ $ruta->codigo }}</span></td>
                                <td>
                                    <span class="ruta-plan-planta">
                                        <i class="fas fa-warehouse"></i>
                                        {{ $ruta->almacenOrigen?->nombre ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ruta-plan-pdv">
                                        <i class="fas fa-user"></i>
                                        {{ trim(($ruta->transportista?->nombre ?? '').' '.($ruta->transportista?->apellido ?? '')) ?: '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-light border font-weight-bold text-dark px-2 py-1">
                                        {{ $ruta->pedidos_count }}
                                    </span>
                                </td>
                                <td><span class="badge badge-{{ $badge['clase'] }} px-2 py-1">{{ $badge['etiqueta'] }}</span></td>
                                <td>
                                    <a href="{{ route('punto-venta.rutas.show', $ruta) }}" class="ruta-plan-btn-ver" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ruta-plan-empty py-4">
                                        <div class="ruta-plan-empty__icon"><i class="fas fa-route"></i></div>
                                        <p>{{ $tab === 'en_ruta' ? 'No hay rutas en curso.' : 'No hay rutas que mostrar.' }}</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="ruta-plan-footer">
            <i class="fas fa-lightbulb mr-1"></i>
            Use los filtros para acotar la vista. Para armar un reparto nuevo, pulse <strong>Nueva ruta</strong>.
        </div>
    </div>
</div>
@endsection
