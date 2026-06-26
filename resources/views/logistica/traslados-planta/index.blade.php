@extends('layouts.app')

@section('title', ($esVistaMayorista ?? false) ? 'Recepciones de planta' : 'Traslados planta → mayorista')
@section('page_title', ($esVistaMayorista ?? false) ? 'Recepciones de planta' : 'Traslados planta → mayorista')

@push('styles')
@include('partials.logistica-modulo-styles')
@if($esVistaMayorista ?? false)
<style>
.may-rec-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #14532d 100%);
    color: #fff;
    border-radius: 14px;
    padding: 1.25rem 1.35rem;
    margin-bottom: 1rem;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .18);
}
.may-rec-hero h2 { font-size: 1.05rem; font-weight: 700; margin: 0 0 .35rem; }
.may-rec-hero p { margin: 0; font-size: .86rem; color: rgba(255,255,255,.82); line-height: 1.5; max-width: 52rem; }
.may-rec-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
    margin-bottom: 1rem;
}
@media (max-width: 991px) { .may-rec-stats { grid-template-columns: repeat(2, 1fr); } }
.may-rec-stat {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .85rem 1rem;
}
.may-rec-stat__n { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1; }
.may-rec-stat__l { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; font-weight: 700; margin-top: .25rem; }
.may-rec-filtros { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
.may-rec-filtros .btn { border-radius: 999px; font-size: .8rem; font-weight: 600; padding: .35rem .9rem; }
.may-rec-filtros .badge { font-size: .72rem; vertical-align: middle; }
.may-rec-list { display: grid; gap: .75rem; }
.may-rec-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) auto;
    gap: .85rem 1rem;
    align-items: center;
    transition: box-shadow .15s, border-color .15s;
}
.may-rec-card:hover { box-shadow: 0 8px 22px rgba(15,23,42,.08); border-color: #cbd5e1; }
@media (max-width: 767px) { .may-rec-card { grid-template-columns: 1fr; } }
.may-rec-card__code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-weight: 700;
    color: #5b21b6;
    font-size: .92rem;
}
.may-rec-card__route { font-size: .8rem; color: #475569; margin-top: .35rem; line-height: 1.45; }
.may-rec-card__route strong { color: #0f172a; font-weight: 600; }
.may-rec-card__meta { display: flex; flex-wrap: wrap; gap: .35rem .65rem; align-items: center; font-size: .78rem; color: #64748b; }
.may-rec-card__meta i { color: #94a3b8; }
.may-rec-card__actions { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; }
</style>
@endif
@endpush

@section('content')
<div class="modulo-inv">
    @if(($pendientesCount ?? 0) > 0 && ! ($esVistaMayorista ?? false))
        <div class="alert alert-warning">
            <i class="fas fa-bell mr-1"></i>
            Tiene <strong>{{ $pendientesCount }}</strong> traslado(s) pendiente(s) de aprobación del jefe de planta.
        </div>
    @endif

    @if(($esVistaMayorista ?? false) && ($conteosRecepcion['esperando_firma'] ?? 0) > 0)
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span>
                <i class="fas fa-file-signature mr-1"></i>
                Tiene <strong>{{ $conteosRecepcion['esperando_firma'] }}</strong> envío(s) esperando su firma de recepción.
            </span>
            <a href="{{ route('almacen-mayorista.traslados-planta.index', ['filtro' => 'esperando_firma']) }}"
               class="btn btn-warning btn-sm font-weight-bold">Firmar ahora</a>
        </div>
    @endif

    @if($esVistaMayorista ?? false)
        <div class="may-rec-hero">
            <h2><i class="fas fa-dolly-flatbed mr-2"></i>¿Para qué sirve esta pantalla?</h2>
            <p>
                Aquí el mayorista controla la <strong>mercadería que sale de planta hacia su almacén</strong>:
                firma la recepción cuando llega la carga y consulta el historial recibido.
                El comprobante legal (guía / POD) queda en <strong>Envíos → Documentos de entrega</strong>.
            </p>
        </div>

        <div class="may-rec-stats">
            <div class="may-rec-stat">
                <div class="may-rec-stat__n">{{ $conteosRecepcion['en_camino'] ?? 0 }}</div>
                <div class="may-rec-stat__l">En camino</div>
            </div>
            <div class="may-rec-stat">
                <div class="may-rec-stat__n">{{ $conteosRecepcion['esperando_firma'] ?? 0 }}</div>
                <div class="may-rec-stat__l">Esperando firma</div>
            </div>
            <div class="may-rec-stat">
                <div class="may-rec-stat__n">{{ $conteosRecepcion['recibidos'] ?? 0 }}</div>
                <div class="may-rec-stat__l">Recibidos</div>
            </div>
            <div class="may-rec-stat">
                <div class="may-rec-stat__n">{{ $traslados->total() }}</div>
                <div class="may-rec-stat__l">En listado</div>
            </div>
        </div>

        <div class="may-rec-filtros">
            @php
                $filtros = [
                    'todos' => ['Todos', null],
                    'en_camino' => ['En camino', $conteosRecepcion['en_camino'] ?? 0],
                    'esperando_firma' => ['Esperando mi firma', $conteosRecepcion['esperando_firma'] ?? 0],
                    'recibidos' => ['Recibidos', $conteosRecepcion['recibidos'] ?? 0],
                ];
                $filtroActivo = $filtroRecepcion ?? 'todos';
            @endphp
            @foreach($filtros as $key => [$label, $count])
                <a href="{{ route('almacen-mayorista.traslados-planta.index', ['filtro' => $key]) }}"
                   class="btn btn-sm {{ $filtroActivo === $key ? 'btn-success' : 'btn-outline-secondary' }}">
                    {{ $label }}
                    @if($count !== null && $key !== 'todos')
                        <span class="badge badge-light text-dark ml-1">{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="may-rec-list">
            @forelse($traslados as $t)
                @php
                    $estRec = ($estadosRecepcion ?? [])[$t->rutadistribucionid] ?? null;
                    $destino = \App\Support\TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($t) ?? '—';
                    $productos = \App\Support\TrasladoPlantaMayoristaPresentacion::conteoProductos($t);
                    $origen = $t->almacenPlantaOrigen?->nombre ?? '—';
                @endphp
                <article class="may-rec-card">
                    <div>
                        <div class="may-rec-card__code">{{ $t->codigo }}</div>
                        <div class="may-rec-card__route">
                            <span class="text-muted">Planta</span> <strong>{{ $origen }}</strong>
                            <i class="fas fa-long-arrow-alt-right mx-1 text-muted"></i>
                            <span class="text-muted">Su almacén</span> <strong>{{ $destino }}</strong>
                        </div>
                    </div>
                    <div class="may-rec-card__meta">
                        @if($estRec)
                            <span class="badge badge-{{ $estRec['clase'] }}">{{ $estRec['etiqueta'] }}</span>
                        @else
                            @php $badge = \App\Support\RutaDistribucionCatalogo::badgeEstado($t); @endphp
                            <span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>
                        @endif
                        <span><i class="fas fa-boxes mr-1"></i>{{ $productos }} producto(s)</span>
                        @if($t->transportista)
                            <span><i class="fas fa-user mr-1"></i>{{ trim($t->transportista->nombre.' '.$t->transportista->apellido) }}</span>
                        @endif
                    </div>
                    <div class="may-rec-card__actions">
                        @if($estRec['puede_firmar'] ?? false)
                            <a href="{{ $estRec['url_cierre'] }}" class="btn btn-sm btn-warning font-weight-bold">
                                <i class="fas fa-file-signature"></i> Firmar
                            </a>
                        @endif
                        <a href="{{ route(($rutaPrefijo ?? 'almacen-mayorista.traslados-planta').'.show', $t) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </div>
                </article>
            @empty
                <div class="alert alert-light border text-center text-muted mb-0">No hay recepciones de planta en este filtro.</div>
            @endforelse
        </div>

        @if($traslados->hasPages())
            <div class="mt-3">{{ $traslados->links() }}</div>
        @endif
    @else
        <div class="card card-outline card-success card-modulo-main elevation-1">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-truck-loading mr-2"></i>Traslados planta → mayorista</h3>
                @can('asignaciones.create')
                <a href="{{ route('pedidos.create', ['destino' => 'mayorista']) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo traslado
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Código</th>
                                <th>Origen (planta)</th>
                                <th>Destino (mayorista)</th>
                                <th>Estado</th>
                                <th>Productos</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($traslados as $t)
                                @php $badge = \App\Support\RutaDistribucionCatalogo::badgeEstado($t); @endphp
                                <tr>
                                    <td class="font-weight-bold text-nowrap">{{ $t->codigo }}</td>
                                    <td>{{ $t->almacenPlantaOrigen?->nombre ?? '—' }}</td>
                                    <td>{{ \App\Support\TrasladoPlantaMayoristaPresentacion::nombreDestinoMayorista($t) ?? '—' }}</td>
                                    <td><span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span></td>
                                    <td>{{ \App\Support\TrasladoPlantaMayoristaPresentacion::conteoProductos($t) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route(($rutaPrefijo ?? 'logistica.traslados-planta').'.show', $t) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No hay traslados registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($traslados->hasPages())
                <div class="card-footer">{{ $traslados->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
