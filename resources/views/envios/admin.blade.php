@extends('layouts.app')

@section('title', 'Dashboard logístico | AgroFusion')
@section('page_title', 'Dashboard logístico')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Envíos</a></li>
    <li class="breadcrumb-item active">Dashboard logístico</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
@php $s = $stats ?? []; @endphp
<div class="modulo-env page-env-dashboard">

    <div class="env-page-intro d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <strong><i class="fas fa-chart-pie text-success mr-1"></i> Resumen operativo de envíos</strong>
            <span class="d-block small text-muted mt-1">Indicadores en tiempo real desde la base del sistema.</span>
        </div>
        <a href="{{ route('envios.seguimiento') }}" class="btn btn-sm btn-outline-success mt-2 mt-md-0">
            <i class="fas fa-route mr-1"></i> Ir a seguimiento
        </a>
    </div>

    <div class="row mb-2">
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $s['total'] ?? 0 }}</h3><p>Total envíos</p></div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-yellow">
                <div class="inner"><h3>{{ $s['pendientes'] ?? 0 }}</h3><p>Pendientes</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-blue">
                <div class="inner"><h3>{{ $s['curso'] ?? 0 }}</h3><p>En tránsito</p></div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-purple">
                <div class="inner"><h3>{{ $s['completados'] ?? 0 }}</h3><p>Entregados</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-teal">
                <div class="inner"><h3>{{ $s['asignados'] ?? 0 }}</h3><p>Asignados</p></div>
                <div class="icon"><i class="fas fa-user-tag"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-indigo">
                <div class="inner"><h3>{{ $s['transportistas'] ?? 0 }}</h3><p>Transportistas</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-orange">
                <div class="inner"><h3>{{ $s['vehiculos_activos'] ?? 0 }}</h3><p>Vehículos activos</p></div>
                <div class="icon"><i class="fas fa-truck-moving"></i></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $s['rutas_activas'] ?? 0 }}</h3><p>Rutas activas</p></div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box small-box-red">
                <div class="inner"><h3>{{ $s['incidentes_abiertos'] ?? 0 }}</h3><p>Incidentes abiertos</p></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-modulo-main">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-list-alt text-success mr-2"></i>Distribución por estado</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Colapsar">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <p class="small text-muted px-3 pt-2 mb-0">
                <i class="fas fa-info-circle mr-1"></i> Haz clic en una fila para ver los envíos de ese estado.
            </p>
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:32px"></th>
                        <th>Estado</th>
                        <th class="text-right" style="width:120px">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porEstado ?? [] as $estado => $cantidad)
                    @php $uid = 'estado-'.preg_replace('/[^a-z0-9]+/', '-', $estado); @endphp
                    <tr class="fila-estado-toggle" data-toggle="collapse" data-target="#{{ $uid }}" aria-expanded="false">
                        <td><i class="fas fa-chevron-right chevron-estado"></i></td>
                        <td><span class="badge badge-light border text-capitalize">{{ $estado }}</span></td>
                        <td class="text-right font-weight-bold">{{ $cantidad }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="p-0 border-0">
                            <div class="collapse detalle-estado-envios" id="{{ $uid }}" data-estado="{{ $estado }}">
                                <div class="lazy-envios-body text-center text-muted py-3 small">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Cargando envíos…
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-muted text-center py-4">
                            <i class="fas fa-inbox mr-1"></i> No hay envíos registrados.
                            <a href="{{ route('envios.mandar') }}" class="d-block mt-2">Crear primer envío</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(function () {
    const apiBase = @json(url('/envios/api/envios'));

    function renderListaEnvios($body, items) {
        if (!items.length) {
            $body.html('<p class="text-muted small text-center py-3 mb-0">Sin envíos en este estado.</p>');
            return;
        }
        const html = ['<ul class="list-group list-group-flush mb-0">'];
        items.forEach(function (envio) {
            const id = envio.externo_envio_id || ('#' + envio.id);
            const nombre = envio.nombre_remitente || '';
            const url = @json(url('/envios')) + '/' + envio.id;
            html.push(
                '<li class="list-group-item d-flex justify-content-between align-items-center py-2">' +
                '<span><strong>' + id + '</strong><span class="text-muted small ml-2">' + nombre + '</span></span>' +
                '<a href="' + url + '" class="btn btn-outline-success btn-sm"><i class="fas fa-eye"></i></a></li>'
            );
        });
        html.push('</ul>');
        $body.html(html.join(''));
    }

    $('.detalle-estado-envios').on('show.bs.collapse', function () {
        const $el = $(this);
        $('[data-target="#' + this.id + '"]').attr('aria-expanded', 'true');
        if ($el.data('loaded')) return;
        const estado = $el.data('estado');
        const $body = $el.find('.lazy-envios-body');
        fetch(apiBase + '?estado=' + encodeURIComponent(estado) + '&limit=50')
            .then(function (r) { return r.json(); })
            .then(function (json) {
                renderListaEnvios($body, json.data || []);
                $el.data('loaded', true);
            })
            .catch(function () {
                $body.html('<p class="text-danger small text-center py-3 mb-0">No se pudo cargar la lista.</p>');
            });
    }).on('hide.bs.collapse', function () {
        $('[data-target="#' + this.id + '"]').attr('aria-expanded', 'false');
    });
});
</script>
@endpush
