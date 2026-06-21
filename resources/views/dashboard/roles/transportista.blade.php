@extends('layouts.app')

@section('title', 'Panel Transportista | AgroFusion')
@section('page_title', 'Panel Transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Transportista</li>
@endsection

@push('styles')
@include('dashboard.partials.panel-accesos-styles')
<style>.role-panel-wrap--transportista { --rp-accent: #d97706; }</style>
@endpush

@section('content')
<section class="content px-0 role-panel-wrap role-panel-wrap--transportista">
    <div class="container-fluid px-0">

        <div class="role-panel-hero position-relative" style="z-index:1">
            <div class="role-panel-hero__title">
                <i class="fas fa-truck-moving"></i>Panel Transportista
            </div>
            <p class="role-panel-hero__sub">
                Hola, <strong>{{ auth()->user()->nombre ?? 'Transportista' }}</strong> · {{ now()->format('d/m/Y') }} · Gestión de envíos asignados.
            </p>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => url()->current(),
        ])

        <div class="role-metrics">
            <div class="role-metric role-metric--a1 dash-kpi--green">
                <i class="fas fa-clipboard-check role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['asignados'] }}</div>
                <p class="role-metric__lbl">Asignados</p>
                <div class="role-metric__sub">envíos en total</div>
            </div>
            <div class="role-metric role-metric--a2 dash-kpi--amber">
                <i class="fas fa-box role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['por_recoger'] }}</div>
                <p class="role-metric__lbl">Por recoger</p>
                <div class="role-metric__sub">pendientes pickup</div>
            </div>
            <div class="role-metric role-metric--a3 dash-kpi--blue">
                <i class="fas fa-shipping-fast role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['en_camino'] }}</div>
                <p class="role-metric__lbl">En camino</p>
                <div class="role-metric__sub">en tránsito ahora</div>
            </div>
            <div class="role-metric role-metric--a4 dash-kpi--green">
                <i class="fas fa-check-circle role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['entregados_hoy'] }}</div>
                <p class="role-metric__lbl">Entregados ({{ $filtros->etiquetaPeriodo() }})</p>
            </div>
            <div class="role-metric role-metric--a5 dash-kpi--purple">
                <i class="fas fa-coins role-metric__icon"></i>
                <div class="role-metric__val">{{ number_format($stats['ingresos_bs'] ?? 0, 0, ',', '.') }}</div>
                <p class="role-metric__lbl">Ingresos Bs</p>
                <div class="role-metric__sub">{{ $filtros->etiquetaPeriodo() }}</div>
            </div>
        </div>

        <div class="d-flex flex-wrap mb-3" style="gap:.5rem;">
            <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-sm btn-success">
                <i class="fas fa-truck mr-1"></i> Mis envíos
            </a>
            <a href="{{ route('logistica.transportista.ingresos') }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-coins mr-1"></i> Ver ingresos ({{ $stats['servicios_completados'] ?? 0 }} completados)
            </a>
        </div>

        @if($stats['asignados'] > 0)
        <div class="role-progress-wrap">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-weight-bold text-secondary" style="font-size:.85rem">Progreso general</span>
                <span class="badge badge-light border">{{ $stats['productividad'] }}% completado</span>
            </div>
            <div class="progress" style="height:10px;border-radius:5px;background:#f1f5f9;">
                <div class="progress-bar" style="width:{{ $stats['productividad'] }}%;"></div>
            </div>
            <div class="d-flex justify-content-between mt-2 small text-muted">
                <span>{{ $stats['asignados'] }} asignados</span>
                @if($stats['incidentes_abiertos'] > 0)
                    <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{ $stats['incidentes_abiertos'] }} incidente(s)</span>
                @else
                    <span class="text-success"><i class="fas fa-shield-alt"></i> Sin incidentes</span>
                @endif
            </div>
        </div>
        @endif

        <div class="role-block-card">
            <div class="role-block-card__head">
                <h3><i class="fas fa-box text-warning mr-2"></i>Mis últimas asignaciones</h3>
                <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-sm btn-outline-success">Ver todas</a>
            </div>
            <div class="table-responsive">
                <table class="table role-x-table mb-0">
                    <thead><tr><th>Envío</th><th>Vehículo</th><th>Estado</th><th>Fecha</th><th>Acción</th></tr></thead>
                    <tbody>
                        @forelse($mis_asignaciones as $a)
                        <tr>
                            <td><span class="role-code">{{ $a->externo_envio_id ?? '#'.$a->id }}</span></td>
                            <td>{{ $a->vehiculo_ref ?? '—' }}</td>
                            <td>
                                @php $color = ['entregado'=>'success','en_ruta'=>'info','asignado'=>'warning','cancelado'=>'danger'][$a->estado] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $color }}">{{ ucfirst(str_replace('_',' ',$a->estado)) }}</span>
                            </td>
                            <td class="text-muted small">{{ optional($a->fecha_asignacion)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if(in_array($a->estado, ['recibido_planta', 'entregado', 'entregada'], true) || $a->fecha_recepcion_planta)
                                    <span class="text-success small"><i class="fas fa-check mr-1"></i>Recibido</span>
                                @elseif(in_array($a->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true))
                                    @include('logistica.partials.accion-llegada-destino', ['asignacion' => $a])
                                @else
                                    @include('logistica.partials.accion-empezar-ruta', ['asignacion' => $a, 'compacto' => true])
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox mr-1"></i>Sin asignaciones registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card role-acc-card">
            <div class="role-acc-card__head">
                <h3><i class="fas fa-bolt text-warning mr-2"></i>Accesos rápidos</h3>
            </div>
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Operación</div>
                <div class="role-acc-grid">
                    @can('asignaciones.view')
                    <a href="{{ route('logistica.asignaciones.listado') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--trans"><i class="fas fa-truck"></i></span>
                        <span>
                            <span class="role-acc-tile__lbl">Mis envíos</span>
                            <span class="role-acc-tile__sub">Listado y acciones de entrega</span>
                        </span>
                    </a>
                    @endcan
                    @can('documentos.view')
                    <a href="{{ route('logistica.documentos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-file-alt"></i></span>
                        <span>
                            <span class="role-acc-tile__lbl">Documentos</span>
                            <span class="role-acc-tile__sub">Notas y comprobantes</span>
                        </span>
                    </a>
                    @endcan
                    @can('incidentes.view')
                    <a href="{{ route('logistica.incidentes.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--warn"><i class="fas fa-exclamation-circle"></i></span>
                        <span>
                            <span class="role-acc-tile__lbl">Incidentes</span>
                            <span class="role-acc-tile__sub">{{ $stats['incidentes_abiertos'] }} abierto(s)</span>
                        </span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</section>
@include('partials.modal-confirmar-accion')
@endsection
