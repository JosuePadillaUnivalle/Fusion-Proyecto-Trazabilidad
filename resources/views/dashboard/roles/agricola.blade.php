@extends('layouts.app')

@section('title', 'Panel Agrícola | AgroFusion')
@section('page_title', 'Panel Agrícola')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Agrícola</li>
@endsection

@push('styles')
@include('dashboard.partials.panel-accesos-styles')
@endpush

@section('content')
<section class="content px-0">
    <div class="container-fluid px-0">

        <div class="role-panel-hero position-relative" style="z-index:1">
            <div class="role-panel-hero__title">
                <i class="fas fa-tractor"></i>Panel Agrícola
            </div>
            <p class="role-panel-hero__sub">
                Coordinación de lotes, cosechas, almacén agrícola y envíos hacia planta.
            </p>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'cultivos' => $cultivos ?? collect(),
            'lotes' => $lotes ?? collect(),
            'mostrarCultivo' => true,
            'mostrarLote' => true,
            'actionUrl' => url()->current(),
        ])

        <div class="role-metrics">
            <div class="role-metric" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                <i class="fas fa-map-marked-alt role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['lotes'] }}</div>
                <p class="role-metric__lbl">Lotes</p>
            </div>
            <div class="role-metric" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-tasks role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['actividades_pendientes'] }}</div>
                <p class="role-metric__lbl">Actividades pend.</p>
            </div>
            <div class="role-metric" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-seedling role-metric__icon"></i>
                <div class="role-metric__val">{{ number_format($stats['cosechas_mes'], 0) }}</div>
                <p class="role-metric__lbl">Cosecha ({{ $filtros->etiquetaPeriodo() }})</p>
            </div>
            <div class="role-metric" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6)">
                <i class="fas fa-shopping-cart role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['pedidos_pendientes'] }}</div>
                <p class="role-metric__lbl">Pedidos pend.</p>
            </div>
        </div>

        @if($lotesRecientes->isNotEmpty())
        <div class="role-block-card">
            <div class="role-block-card__head">
                <h3><i class="fas fa-map text-success mr-2"></i>Lotes recientes</h3>
                <a href="{{ route('lotes.index') }}" class="btn btn-sm btn-outline-success">Ver todos</a>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($lotesRecientes as $lote)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $lote->nombre }}</strong>
                        <div class="small text-muted">{{ $lote->cultivo?->nombre ?? 'Sin cultivo' }} · {{ $lote->estadoTipo?->nombre ?? '—' }}</div>
                    </div>
                    <a href="{{ route('lotes.show', $lote) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card role-acc-card">
            <div class="role-acc-card__head">
                <h3><i class="fas fa-bolt text-success mr-2"></i>Accesos rápidos</h3>
            </div>

            @canany(['lotes.view','lotes.create'])
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Campo</div>
                <div class="role-acc-grid">
                    @can('lotes.view')
                    <a href="{{ route('lotes.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-map"></i></span>
                        <span><span class="role-acc-tile__lbl">Lotes</span><span class="role-acc-tile__sub">Parcelas y trazabilidad</span></span>
                    </a>
                    <a href="{{ route('lotes.mapa') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-map-marked-alt"></i></span>
                        <span><span class="role-acc-tile__lbl">Mapa de lotes</span><span class="role-acc-tile__sub">Vista geográfica</span></span>
                    </a>
                    @endcan
                    <a href="{{ route('actividades.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-tasks"></i></span>
                        <span><span class="role-acc-tile__lbl">Actividades</span><span class="role-acc-tile__sub">Siembra, riego, cosecha</span></span>
                    </a>
                    <a href="{{ route('actividades.calendario') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-calendar-alt"></i></span>
                        <span><span class="role-acc-tile__lbl">Calendario</span><span class="role-acc-tile__sub">Planificación semanal</span></span>
                    </a>
                </div>
            </div>
            @endcanany

            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Producción</div>
                <div class="role-acc-grid">
                    <a href="{{ route('cultivos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-leaf"></i></span>
                        <span><span class="role-acc-tile__lbl">Cultivos</span><span class="role-acc-tile__sub">Catálogo de cultivos</span></span>
                    </a>
                    <a href="{{ route('producciones.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-tractor"></i></span>
                        <span><span class="role-acc-tile__lbl">Cosechas</span><span class="role-acc-tile__sub">Registro de producción</span></span>
                    </a>
                    @can('pedidos.view')
                    <a href="{{ route('agricola.pedidos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--log"><i class="fas fa-truck-loading"></i></span>
                        <span><span class="role-acc-tile__lbl">Pedidos de planta</span><span class="role-acc-tile__sub">Solicitudes y envíos</span></span>
                    </a>
                    @endcan
                    <a href="{{ route('climas.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-cloud-sun"></i></span>
                        <span><span class="role-acc-tile__lbl">Clima</span><span class="role-acc-tile__sub">Condiciones del campo</span></span>
                    </a>
                </div>
            </div>

            @canany(['inventario.view','inventario.create'])
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Inventario</div>
                <div class="role-acc-grid">
                    <a href="{{ route('insumos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-flask"></i></span>
                        <span><span class="role-acc-tile__lbl">Insumos</span><span class="role-acc-tile__sub">Stock de insumos</span></span>
                    </a>
                    <a href="{{ route('lote-insumos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-spray-can"></i></span>
                        <span><span class="role-acc-tile__lbl">Aplicación de insumos</span><span class="role-acc-tile__sub">Uso en lotes</span></span>
                    </a>
                </div>
            </div>
            @endcanany

            @can('almacen.movimientos.view')
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Almacén agrícola</div>
                <div class="role-acc-grid">
                    <a href="{{ route('almacen-agricola.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-warehouse"></i></span>
                        <span><span class="role-acc-tile__lbl">Almacenes</span><span class="role-acc-tile__sub">Depósitos y capacidad</span></span>
                    </a>
                    <a href="{{ route('almacen-agricola.movimientos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-exchange-alt"></i></span>
                        <span><span class="role-acc-tile__lbl">Movimientos</span><span class="role-acc-tile__sub">Ingresos y salidas</span></span>
                    </a>
                    @can('almacen.reportes.view')
                    <a href="{{ route('almacen-agricola.movimientos.reportes') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-chart-bar"></i></span>
                        <span><span class="role-acc-tile__lbl">Reportes</span><span class="role-acc-tile__sub">Resumen de almacén</span></span>
                    </a>
                    @endcan
                </div>
            </div>
            @endcan

            @can('certificaciones.view')
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Calidad</div>
                <div class="role-acc-grid">
                    <a href="{{ route('certificaciones.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--com"><i class="fas fa-certificate"></i></span>
                        <span><span class="role-acc-tile__lbl">Certificaciones</span><span class="role-acc-tile__sub">Lotes certificados</span></span>
                    </a>
                </div>
            </div>
            @endcan

            @can('usuarios.view')
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Equipo</div>
                <div class="role-acc-grid">
                    <a href="{{ route('gestion.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-users-cog"></i></span>
                        <span><span class="role-acc-tile__lbl">Gestión de usuarios</span><span class="role-acc-tile__sub">Agricultores del equipo</span></span>
                    </a>
                </div>
            </div>
            @endcan
        </div>
    </div>
</section>
@endsection
