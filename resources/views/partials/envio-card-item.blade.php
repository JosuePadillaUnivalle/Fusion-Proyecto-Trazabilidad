@php
    $estado = strtolower(trim((string) ($envio['estado'] ?? 'sin estado')));
    $badges = [
        'pendiente' => ['Pendiente', 'badge-warning'],
        'sin estado' => ['Pendiente', 'badge-warning'],
        'asignado' => ['Asignado', 'badge-info'],
        'en_ruta' => ['En curso', 'badge-primary'],
        'en ruta' => ['En curso', 'badge-primary'],
        'entregado' => ['Completado', 'badge-success'],
        'finalizado' => ['Completado', 'badge-success'],
        'completado' => ['Completado', 'badge-success'],
    ];
    [$label, $badgeClass] = $badges[$estado] ?? [ucfirst($estado ?: 'Sin estado'), 'badge-secondary'];
    $codigo = $envio['externo_envio_id'] ?? ('#'.$envio['id']);
    $fecha = !empty($envio['fecha_creacion'])
        ? \Carbon\Carbon::parse($envio['fecha_creacion'])->locale('es')->isoFormat('ddd D MMM')
        : 'Sin fecha';
@endphp
<div class="col-xl-4 col-lg-6 mb-3">
    <div class="card card-outline card-success envio-card h-100 shadow-sm"
         role="button"
         onclick="window.location.href='{{ url('/envios/'.$envio['id']) }}'">
        <div class="card-header py-2 bg-white">
            <div class="d-flex justify-content-between align-items-start">
                <div class="mr-2 overflow-hidden">
                    <div class="font-weight-bold text-truncate">{{ $codigo }}</div>
                    @if(!empty($envio['numero_solicitud']))
                        <small class="text-muted">{{ $envio['numero_solicitud'] }}</small>
                    @endif
                </div>
                <span class="badge badge-estado {{ $badgeClass }}">{{ $label }}</span>
            </div>
        </div>
        <div class="card-body py-2">
            <p class="text-muted small mb-2"><i class="far fa-calendar mr-1"></i>{{ $fecha }}</p>
            <div class="envio-route mb-3">
                <div class="mb-2">
                    <span class="badge badge-light border text-success mr-1"><i class="fas fa-arrow-up"></i></span>
                    <small class="text-muted">Origen</small>
                    <div class="font-weight-bold text-truncate-2lines small pl-1">{{ $envio['direccion_origen'] ?? 'Sin origen' }}</div>
                </div>
                <div>
                    <span class="badge badge-light border text-danger mr-1"><i class="fas fa-arrow-down"></i></span>
                    <small class="text-muted">Destino</small>
                    <div class="font-weight-bold text-truncate-2lines small pl-1">{{ $envio['direccion_destino'] ?? 'Sin destino' }}</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                <div class="overflow-hidden mr-2">
                    <small class="text-muted">Remitente</small>
                    <div class="font-weight-bold small text-truncate">{{ $envio['nombre_remitente'] ?? 'Sin remitente' }}</div>
                </div>
                <span class="btn btn-sm btn-success flex-shrink-0"><i class="fas fa-eye"></i></span>
            </div>
        </div>
    </div>
</div>
