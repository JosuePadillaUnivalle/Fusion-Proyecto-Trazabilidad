@extends('layouts.app')



@section('title', 'Cosechas | AgroFusion')

@section('page_title', 'Registro de Cosechas')



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>

    <li class="breadcrumb-item active">Cosechas completadas</li>

@endsection



@push('styles')

@include('partials.modulo-produccion-styles')

<style>

.modulo-prod .cosecha-hero {

    background: linear-gradient(135deg, #14532d 0%, #166534 50%, #22c55e 100%);

    border-radius: 14px;

    color: #fff;

    padding: 1.35rem 1.5rem;

    margin-bottom: 1.25rem;

    box-shadow: 0 6px 24px rgba(20, 83, 45, .2);

}

.modulo-prod .cosecha-hero h2 {

    font-size: 1.35rem;

    font-weight: 800;

    margin: 0 0 .35rem;

}

.modulo-prod .cosecha-hero p {

    margin: 0;

    opacity: .92;

    font-size: .9rem;

    max-width: 42rem;

}

.modulo-prod .cosecha-kpi {

    border: none;

    border-radius: 12px;

    padding: 1rem 1.1rem;

    color: #fff;

    height: 100%;

    box-shadow: 0 2px 12px rgba(15, 23, 42, .08);

}

.modulo-prod .cosecha-kpi__val { font-size: 1.65rem; font-weight: 800; line-height: 1.1; }

.modulo-prod .cosecha-kpi__lbl { font-size: .78rem; opacity: .9; margin-top: .2rem; }

.modulo-prod .cosecha-kpi--green { background: linear-gradient(135deg, #166534, #22c55e); }

.modulo-prod .cosecha-kpi--teal { background: linear-gradient(135deg, #0f766e, #14b8a6); }

.modulo-prod .cosecha-kpi--amber { background: linear-gradient(135deg, #b45309, #f59e0b); }

.modulo-prod .cosecha-kpi--violet { background: linear-gradient(135deg, #5b21b6, #8b5cf6); }

.modulo-prod .cosecha-tabla thead th {

    background: #f0fdf4;

    border-bottom: 2px solid #bbf7d0;

    font-size: .72rem;

    text-transform: uppercase;

    letter-spacing: .04em;

    color: #166534;

    font-weight: 700;

    white-space: nowrap;

}

.modulo-prod .cosecha-tabla tbody tr:hover { background: #f8fffb; }

.modulo-prod .cosecha-lote-nombre {

    font-weight: 700;

    color: #14532d;

    font-size: .95rem;

}

.modulo-prod .cosecha-cantidad {

    font-size: 1.05rem;

    font-weight: 800;

    color: #15803d;

}

.modulo-prod .btn-ir-lote {

    background: #ecfdf5;

    border: 1px solid #86efac;

    color: #166534;

    font-weight: 600;

    font-size: .8rem;

    border-radius: 8px;

    padding: .35rem .75rem;

    white-space: nowrap;

}

.modulo-prod .btn-ir-lote:hover {

    background: #166534;

    border-color: #166534;

    color: #fff;

    text-decoration: none;

}

.modulo-prod .cosecha-meta {
    font-size: .78rem;
    color: #64748b;
}
.modulo-prod .cos-chip {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .28rem .62rem;
    border-radius: 999px;
    font-size: .74rem;
    font-weight: 600;
    border: 1px solid transparent;
    white-space: nowrap;
}
.modulo-prod .cos-chip--emerald { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.modulo-prod .cos-chip--amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.modulo-prod .cos-chip--rose { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
.modulo-prod .cos-chip--slate { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
.modulo-prod .cos-chip--sky { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.modulo-prod #filtrosProduccionPanel .selector-catalogo-wrapper {
    margin-bottom: 0;
}
.modulo-prod #filtrosProduccionPanel .filtros-form-actions {
    margin-top: 0;
}
</style>

@endpush



@section('content')

<div class="modulo-prod">



    <div class="cosecha-hero">

        <h2><i class="fas fa-tractor mr-2"></i>Cosechas completadas por lote</h2>

        <p>

            Consulta el resultado de cada parcela ya cosechada. Aquí aparece <strong>una cosecha por lote</strong>.

            Para registrar o gestionar el ciclo (certificación, almacén, etc.) use el <strong>lote</strong> correspondiente.

        </p>

    </div>



    <div class="row mb-3">

        <div class="col-6 col-lg-3 mb-2 mb-lg-0">

            <div class="cosecha-kpi cosecha-kpi--green">

                <div class="cosecha-kpi__val">{{ $stats['total'] ?? 0 }}</div>

                <div class="cosecha-kpi__lbl">Lotes cosechados</div>

            </div>

        </div>

        <div class="col-6 col-lg-3 mb-2 mb-lg-0">

            <div class="cosecha-kpi cosecha-kpi--teal">

                <div class="cosecha-kpi__val">{{ number_format($stats['kg_total'] ?? 0, 0) }}<span style="font-size:.85rem"> kg</span></div>

                <div class="cosecha-kpi__lbl">Volumen total</div>

            </div>

        </div>

        <div class="col-6 col-lg-3 mb-2 mb-lg-0">

            <div class="cosecha-kpi cosecha-kpi--amber">

                <div class="cosecha-kpi__val">{{ number_format($stats['promedio'] ?? 0, 0) }}<span style="font-size:.85rem"> kg</span></div>

                <div class="cosecha-kpi__lbl">Promedio por lote</div>

            </div>

        </div>

        <div class="col-6 col-lg-3">

            <div class="cosecha-kpi cosecha-kpi--violet">

                <div class="cosecha-kpi__val"><i class="fas fa-map-marked-alt" style="font-size:1.2rem"></i></div>

                <div class="cosecha-kpi__lbl">Solo lectura — gestione en Lotes</div>

            </div>

        </div>

    </div>



    <div class="card card-outline card-success card-modulo-main elevation-1">

        <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-2">

            <h3 class="card-title mb-0">

                <i class="fas fa-list-alt text-success mr-2"></i>

                Listado

                <span class="badge badge-light border ml-2">{{ $producciones->total() }} lote(s)</span>

            </h3>

            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#filtrosProduccionPanel">

                <i class="fas fa-filter mr-1"></i> Filtros

            </button>

        </div>



        <div id="filtrosProduccionPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','loteid','fecha_desde','fecha_hasta']) ? 'show' : '' }}">

            <form method="GET" action="{{ route('producciones.index') }}">

                <div class="row align-items-end">

                    <div class="col-lg-4 col-md-6 mb-2">

                        <label class="small text-muted mb-1">Buscar</label>

                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Lote, semilla, responsable…">

                    </div>

                    <div class="col-lg-4 col-md-6 mb-2">

                        <label class="small text-muted mb-1">Lote cosechado</label>

                        @include('partials.selector-catalogo', [
                            'id' => 'cos_filtro_lote',
                            'name' => 'loteid',
                            'value' => request('loteid'),
                            'labelSelected' => $loteFiltroSeleccionado?->nombre ?? '',
                            'endpoint' => route('catalogo-selector.lotes'),
                            'params' => ['loteids' => ($loteIdsCosechadosFilter ?? collect())->implode(',')],
                            'title' => 'Seleccionar lote cosechado',
                            'searchPlaceholder' => 'Nombre, código o ubicación…',
                            'searchLabel' => 'Buscar lote cosechado',
                            'modalIcon' => 'fa-map-marked-alt',
                            'rowIcon' => 'fa-seedling',
                            'colNombre' => 'Lote',
                            'colDetalle' => 'Cultivo / código',
                            'allowEmpty' => true,
                            'emptyLabel' => 'Todos los lotes',
                            'placeholderEmpty' => 'Todos los lotes cosechados',
                            'size' => 'sm',
                            'inputGroup' => true,
                            'showLabel' => false,
                            'variant' => 'filtros',
                        ])

                    </div>

                    <div class="col-lg-2 col-md-4 mb-2">

                        <label class="small text-muted mb-1">Desde</label>

                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">

                    </div>

                    <div class="col-lg-2 col-md-4 mb-2">

                        <label class="small text-muted mb-1">Hasta</label>

                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">

                    </div>

                    <div class="col-lg-4 col-md-4 mb-2">

                        <x-filtros-form-actions :limpiar-url="route('producciones.index', ['filtros_abiertos' => 1])" />

                    </div>

                </div>

            </form>

        </div>



        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0 cosecha-tabla">

                    <thead>

                        <tr>

                            <th>Lote</th>

                            <th>Semilla / cultivo</th>

                            <th class="text-right">Cantidad cosechada</th>

                            <th>Fecha</th>

                            <th>Superficie</th>

                            <th class="text-center" style="min-width:200px">Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($producciones as $p)

                            @php
                                $lote = $p->lote;
                                $almacenActivo = $p->almacenamientos->whereNull('fechasalida')->sortByDesc('fechaentrada')->first();
                            @endphp

                            <tr>

                                <td>

                                    <div class="cosecha-lote-nombre">{{ $lote->nombre ?? '—' }}</div>

                                    @if($lote?->usuario)

                                        <div class="cosecha-meta"><i class="fas fa-user mr-1"></i>{{ trim($lote->usuario->nombre.' '.($lote->usuario->apellido ?? '')) }}</div>

                                    @endif

                                </td>

                                <td>
                                    @if($lote?->cultivo_etiqueta)
                                        <span class="cos-chip cos-chip--emerald"><i class="fas fa-seedling"></i>{{ $lote->cultivo_etiqueta }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="cosecha-cantidad">{{ number_format($p->cantidad ?? 0, 2) }}</span>
                                    <span class="text-muted">{{ $p->unidadMedida->abreviatura ?? 'kg' }}</span>
                                    @if($almacenActivo?->almacen)
                                        <div class="mt-1">
                                            <span class="cos-chip cos-chip--sky"><i class="fas fa-warehouse"></i>{{ $almacenActivo->almacen->nombre }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-muted small">
                                    {{ $lote?->superficie_etiqueta ?? '—' }}
                                    @if($lote?->codigo_trazabilidad)
                                        <div class="cosecha-meta mt-1">Código de trazabilidad: <span style="font-family:ui-monospace,monospace;">{{ $lote->codigo_trazabilidad }}</span></div>
                                    @endif
                                </td>

                                <td class="text-center">

                                    <div class="d-flex flex-wrap justify-content-center" style="gap:6px;">

                                        <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-outline-info" title="Ver detalle de cosecha">

                                            <i class="fas fa-eye mr-1"></i> Detalle

                                        </a>

                                        @if($lote)

                                            <a href="{{ route('lotes.trazabilidad', $lote) }}" class="btn btn-sm btn-ir-lote" title="Ir al lote">

                                                <i class="fas fa-map-marked-alt mr-1"></i> Ir al lote

                                            </a>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="text-center py-5 text-muted">

                                    <i class="fas fa-tractor fa-3x mb-3 d-block opacity-50"></i>

                                    <p class="mb-1 font-weight-bold">No hay cosechas completadas</p>

                                    <p class="small mb-0">Cuando un lote pase a estado <em>Cosechado</em>, aparecerá aquí automáticamente.</p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>



        @if($producciones->hasPages())

        <div class="card-footer d-flex justify-content-center bg-white">

            {{ $producciones->links() }}

        </div>

        @endif

    </div>

</div>

@endsection

