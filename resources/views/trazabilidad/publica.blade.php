@extends('layouts.public-trazabilidad')

@section('title', 'Trazabilidad — '.$reporte['producto'])

@push('styles')
<style>
.trz-public-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-top: .5rem;
}
.trz-public-header .brand-logo {
    width: 48px;
    height: 48px;
    margin: 0 auto .75rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #059669, #10b981);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(5, 150, 105, .25);
}
.trz-public-header .brand-logo i { color: #fff; font-size: 1.2rem; }
.trz-public-header .brand-name {
    color: #2c5530;
    font-weight: 800;
    font-size: 1rem;
    letter-spacing: .02em;
}
.trz-public-header .brand-tagline {
    font-size: .72rem;
    color: #64748b;
    margin-bottom: 1rem;
}
.trz-public-header h1 {
    font-size: clamp(1.25rem, 4vw, 1.55rem);
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 .35rem;
    line-height: 1.25;
}
.trz-public-header .codigo {
    display: inline-block;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    border-radius: 999px;
    padding: .3rem .9rem;
    font-size: .75rem;
    font-weight: 700;
    word-break: break-all;
}
.trz-public-header .subtitle {
    font-size: .85rem;
    color: #64748b;
    margin-top: .65rem;
    line-height: 1.45;
}

.trz-public-meta {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem 1.15rem;
    margin-bottom: 1.25rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .85rem 1rem;
}
@media (max-width: 520px) {
    .trz-public-meta { grid-template-columns: 1fr; }
}
.trz-public-meta .meta-item dt {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    margin-bottom: .2rem;
    font-weight: 600;
}
.trz-public-meta .meta-item dd {
    font-weight: 600;
    color: #1e293b;
    font-size: .88rem;
    line-height: 1.4;
    margin: 0;
}
.trz-public-meta .meta-item dd small { font-weight: 500; color: #64748b; }

.trz-public-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, .06);
    overflow: hidden;
}
.trz-public-card-header {
    padding: 1rem 1.15rem .75rem;
    border-bottom: 1px solid #f1f5f9;
}
.trz-public-card-header h2 {
    font-size: .95rem;
    font-weight: 700;
    color: #047857;
    margin: 0;
}
.trz-public-card-body { padding: 1rem 1.15rem 1.15rem; }

.trz-timeline { position: relative; padding-left: .15rem; }
.trz-timeline-item {
    border-left: 3px solid #bbf7d0;
    padding: 0 0 1.15rem 1rem;
    margin-left: .55rem;
    position: relative;
}
.trz-timeline-item:last-child { padding-bottom: 0; }
.trz-timeline-item::before {
    content: '';
    position: absolute;
    left: -.72rem;
    top: .2rem;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: #059669;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #86efac;
}
.trz-timeline-item .etapa-badge {
    display: inline-block;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: .2rem .55rem;
    border-radius: 6px;
    margin-bottom: .4rem;
    background: #ecfdf5;
    color: #047857;
}
.trz-timeline-item h3 {
    font-size: .92rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 .3rem;
    line-height: 1.35;
}
.trz-timeline-item p {
    font-size: .84rem;
    color: #475569;
    margin: 0 0 .25rem;
    line-height: 1.5;
}
.trz-timeline-item .ubicacion {
    font-size: .8rem;
    color: #64748b;
    margin-bottom: .25rem;
}
.trz-timeline-item .ubicacion i { color: #94a3b8; }
.trz-timeline-item .fecha {
    font-size: .74rem;
    color: #94a3b8;
}

.trz-public-footer {
    text-align: center;
    margin-top: 1.5rem;
    font-size: .75rem;
    color: #94a3b8;
    line-height: 1.5;
}
</style>
@endpush

@section('content')
    <header class="trz-public-header">
        <div class="brand-logo"><i class="fas fa-seedling"></i></div>
        <div class="brand-name">AgroFusion</div>
        <div class="brand-tagline">Sistema integral de gestión agrícola</div>
        <h1>{{ $reporte['producto'] }}</h1>
        <span class="codigo">{{ $reporte['codigo'] }}</span>
        <p class="subtitle">Trazabilidad desde campo hasta punto de venta</p>
    </header>

    <dl class="trz-public-meta">
        @if($reporte['punto_venta'])
        <div class="meta-item">
            <dt>Punto de venta</dt>
            <dd>{{ $reporte['punto_venta'] }}</dd>
        </div>
        @endif
        @if($reporte['minorista'] && $reporte['minorista'] !== '—')
        <div class="meta-item">
            <dt>Minorista</dt>
            <dd>{{ $reporte['minorista'] }}</dd>
        </div>
        @endif
        @if($reporte['lote_agricola'])
        <div class="meta-item">
            <dt>Lote agrícola</dt>
            <dd>
                {{ $reporte['lote_agricola'] }}
                @if($reporte['lote_codigo'])
                    <small>({{ $reporte['lote_codigo'] }})</small>
                @endif
            </dd>
        </div>
        @endif
        @if($reporte['pedido'])
        <div class="meta-item">
            <dt>Pedido distribución</dt>
            <dd>{{ $reporte['pedido'] }}</dd>
        </div>
        @endif
        <div class="meta-item">
            <dt>Stock en tienda</dt>
            <dd>{{ number_format($reporte['stock_actual'], 2) }} {{ $reporte['unidad'] }}</dd>
        </div>
    </dl>

    <section class="trz-public-card">
        <div class="trz-public-card-header">
            <h2><i class="fas fa-route mr-1"></i> Recorrido del producto</h2>
        </div>
        <div class="trz-public-card-body">
            <div class="trz-timeline">
                @foreach($reporte['eventos'] as $evento)
                    <article class="trz-timeline-item">
                        <span class="etapa-badge">
                            <i class="fas fa-{{ $evento['icon'] }} mr-1"></i>{{ $evento['etapa_label'] }}
                        </span>
                        <h3>{{ $evento['titulo'] }}</h3>
                        <p>{{ $evento['descripcion'] }}</p>
                        @if($evento['ubicacion'])
                            <p class="ubicacion"><i class="fas fa-map-marker-alt mr-1"></i>{{ $evento['ubicacion'] }}</p>
                        @endif
                        <div class="fecha"><i class="far fa-clock mr-1"></i>{{ $evento['fecha_fmt'] }}</div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="trz-public-footer">
        © {{ date('Y') }} AgroFusion · Sistema de Gestión Agrícola
    </footer>
@endsection
