@extends('layouts.app')

@section('title', 'Puntos de venta')
@section('page_title', 'Puntos de venta')

@push('styles')
@include('punto_venta.partials.modulo-styles')
<style>
.pdv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    padding: 1.25rem;
    align-items: stretch;
}
.pdv-tile {
    border: 1px solid #e8edf2;
    border-radius: 16px;
    background: #fff;
    padding: 1.15rem 1.2rem;
    transition: box-shadow .15s ease, transform .15s ease;
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.pdv-tile:hover { box-shadow: 0 10px 28px rgba(15, 23, 42, .1); transform: translateY(-2px); }
.pdv-tile::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #059669, #34d399);
}
.pdv-tile--inactivo::before { background: linear-gradient(90deg, #94a3b8, #cbd5e1); }
.pdv-tile__head { display: flex; align-items: flex-start; gap: .85rem; margin-bottom: .85rem; }
.pdv-tile__icon {
    width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #047857; font-size: 1.1rem;
}
.pdv-tile--inactivo .pdv-tile__icon { background: #f1f5f9; color: #64748b; }
.pdv-tile__name { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 .15rem; }
.pdv-tile__minorista { font-size: .78rem; color: #64748b; }
.pdv-tile__meta { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .85rem; }
.pdv-tile__stock {
    margin-bottom: .85rem;
    padding: .75rem .85rem;
    border-radius: 12px;
    background: linear-gradient(160deg, #f8fafc, #f1f5f9);
    border: 1px solid #e8edf2;
}
.pdv-tile__stock-top {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: .5rem;
    margin-bottom: .45rem;
}
.pdv-tile__stock-val {
    font-size: 1.45rem;
    font-weight: 800;
    color: #047857;
    line-height: 1;
}
.pdv-tile__stock-val--vacio { color: #94a3b8; }
.pdv-tile__stock-label { font-size: .72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.pdv-tile__stock-pct {
    font-size: .75rem;
    font-weight: 700;
    color: #475569;
    white-space: nowrap;
}
.pdv-tile__stock-bar {
    height: 7px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
    margin-bottom: .35rem;
}
.pdv-tile__stock-bar__fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #059669, #34d399);
}
.pdv-tile__stock-bar__fill--alto { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.pdv-tile__stock-bar__fill--lleno { background: linear-gradient(90deg, #dc2626, #f87171); }
.pdv-tile__stock-meta { font-size: .74rem; color: #64748b; }
.pdv-tile__footer {
    margin: auto -1.2rem -1.15rem;
    padding: .95rem 1.2rem 1.1rem;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f6 100%);
    border-top: 1px solid #e8edf2;
    border-radius: 0 0 16px 16px;
    flex-shrink: 0;
}
.pdv-tile__location {
    display: flex;
    align-items: flex-start;
    gap: .55rem;
    padding: .6rem .75rem;
    margin-bottom: .8rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    font-size: .78rem;
    color: #475569;
    line-height: 1.45;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    min-height: 3.35rem;
}
.pdv-tile__location span {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pdv-tile__location i {
    color: #059669;
    margin-top: .12rem;
    flex-shrink: 0;
    font-size: .85rem;
}
.pdv-tile__toolbar {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: .55rem;
    align-items: center;
}
.pdv-tile__btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    padding: .58rem 1rem;
    border-radius: 11px;
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff !important;
    font-weight: 700;
    font-size: .82rem;
    text-decoration: none;
    box-shadow: 0 3px 10px rgba(5, 150, 105, .22);
    transition: transform .12s ease, box-shadow .12s ease;
    border: none;
}
.pdv-tile__btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(5, 150, 105, .32);
    color: #fff !important;
}
.pdv-tile__btn-group { display: flex; gap: .4rem; }
.pdv-tile__btn-icon {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: .88rem;
    transition: all .12s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
}
.pdv-tile__btn-icon:hover { border-color: #cbd5e1; background: #f8fafc; color: #1e293b; text-decoration: none; }
.pdv-tile__btn-icon--danger { color: #dc2626; }
.pdv-tile__btn-icon--danger:hover { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
.pdv-tile__btn-icon--disabled,
.pdv-tile__btn-icon:disabled {
    opacity: .42;
    cursor: not-allowed;
    pointer-events: none;
    background: #f8fafc;
}
.pdv-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: #64748b;
}
</style>
@endpush

@section('content')
    <div class="card pdv-card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Minoristas y puntos de venta"
            icono="fa-store"
            :registros="$puntos->count()"
            filtros-target="#filtrosPdvPanel"
            :nuevo-href="route('punto-venta.puntos.create')"
            nuevo-text="Nuevo punto de venta"
            nuevo-can="punto_venta.create"
        />

        <div id="filtrosPdvPanel" class="collapse {{ request()->hasAny(['q','activo']) ? 'show' : '' }}">
            @include('partials.modulo-filtros-form', [
                'action' => route('punto-venta.puntos.index'),
                'campos' => [
                    ['name' => 'q', 'label' => 'Buscar', 'placeholder' => 'Nombre, dirección o minorista…', 'col' => 'col-md-5'],
                    ['name' => 'activo', 'label' => 'Estado', 'type' => 'select', 'col' => 'col-md-3', 'options' => ['1' => 'Activos', '0' => 'Inactivos']],
                ],
            ])
        </div>

        <div class="pdv-grid">
            @forelse($puntos as $punto)
                @php
                    $oc = $ocupacionPorPunto[$punto->puntoventaid] ?? ['ocupado_kg' => 0, 'capacidad_kg' => 0, 'disponible_kg' => 0, 'porcentaje' => 0];
                    $stockKg = (float) ($oc['ocupado_kg'] ?? 0);
                    $capKg = (float) ($oc['capacidad_kg'] ?? 0);
                    $pct = (float) ($oc['porcentaje'] ?? 0);
                    $tieneStock = $stockKg > 0.0001;
                    $barClass = $pct > 85 ? 'pdv-tile__stock-bar__fill--lleno' : ($pct >= 50 ? 'pdv-tile__stock-bar__fill--alto' : '');
                    $evalDel = $eliminacionPorPunto[$punto->puntoventaid] ?? ['ok' => false, 'mensaje' => 'No se puede eliminar mientras haya stock.'];
                @endphp
                <article class="pdv-tile {{ ! $punto->activo ? 'pdv-tile--inactivo' : '' }}">
                    <div class="pdv-tile__head">
                        <div class="pdv-tile__icon"><i class="fas fa-store"></i></div>
                        <div class="flex-grow-1 min-width-0">
                            <h3 class="pdv-tile__name">{{ $punto->nombre }}</h3>
                            @if($esAdmin)
                            <div class="pdv-tile__minorista"><i class="fas fa-user mr-1"></i>{{ $punto->nombreMinorista() }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="pdv-tile__meta">
                        <span class="badge badge-{{ $punto->activo ? 'success' : 'secondary' }}">{{ $punto->activo ? 'Activo' : 'Inactivo' }}</span>
                        <span class="badge badge-{{ $tieneStock ? 'info' : 'light' }}">{{ $tieneStock ? 'Con stock' : 'Sin stock' }}</span>
                    </div>
                    <div class="pdv-tile__stock">
                        <div class="pdv-tile__stock-top">
                            <div>
                                <div class="pdv-tile__stock-label">Stock en depósito</div>
                                <div class="pdv-tile__stock-val {{ $tieneStock ? '' : 'pdv-tile__stock-val--vacio' }}">
                                    {{ number_format($stockKg, $stockKg >= 100 ? 0 : 2, ',', '.') }} kg
                                </div>
                            </div>
                            @if($capKg > 0)
                            <span class="pdv-tile__stock-pct">{{ number_format($pct, 1) }}% ocupado</span>
                            @endif
                        </div>
                        @if($capKg > 0)
                        <div class="pdv-tile__stock-bar">
                            <div class="pdv-tile__stock-bar__fill {{ $barClass }}" style="width:{{ min(100, max(0, $pct)) }}%"></div>
                        </div>
                        <div class="pdv-tile__stock-meta">
                            {{ number_format((float) ($oc['disponible_kg'] ?? 0), 0, ',', '.') }} kg libres
                            · capacidad {{ number_format($capKg, 0, ',', '.') }} kg
                        </div>
                        @elseif(! $punto->almacenid)
                        <div class="pdv-tile__stock-meta">Sin almacén vinculado</div>
                        @endif
                    </div>
                    <div class="pdv-tile__footer">
                        <div class="pdv-tile__location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span @if($punto->direccion) title="{{ $punto->direccion }}" @endif>{{ $punto->direccion ?: 'Sin dirección registrada' }}</span>
                        </div>
                        <div class="pdv-tile__toolbar">
                            <a href="{{ route('punto-venta.puntos.show', $punto) }}" class="pdv-tile__btn-primary">
                                <i class="fas fa-boxes"></i> Ver inventario
                            </a>
                            <div class="pdv-tile__btn-group">
                                @can('punto_venta.update')
                                <a href="{{ route('punto-venta.puntos.edit', $punto) }}" class="pdv-tile__btn-icon" title="Editar punto de venta">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('punto_venta.delete')
                                    @if($evalDel['ok'])
                                    <form method="POST" action="{{ route('punto-venta.puntos.destroy', $punto) }}" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="pdv-tile__btn-icon pdv-tile__btn-icon--danger" title="Eliminar punto de venta"
                                            data-confirm-modal
                                            data-confirm-title="Eliminar punto de venta"
                                            data-confirm-message="¿Eliminar «{{ $punto->nombre }}»? Esta acción no se puede deshacer."
                                            data-confirm-tone="danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="pdv-tile__btn-icon pdv-tile__btn-icon--danger pdv-tile__btn-icon--disabled"
                                        title="{{ $evalDel['mensaje'] }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="pdv-empty">
                    <i class="fas fa-store fa-2x mb-2 d-block text-muted"></i>
                    No hay puntos de venta registrados.
                    @can('punto_venta.create')
                        <div class="mt-2"><a href="{{ route('punto-venta.puntos.create') }}">Crear el primero</a></div>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
@endsection
