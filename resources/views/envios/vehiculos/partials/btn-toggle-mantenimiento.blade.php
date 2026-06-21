@php
    /** @var \App\Models\Vehiculo $vehiculo */
    $compact = $compact ?? false;
    $enRuta = app(\App\Services\VehiculoFlotaEstadoService::class)->estaEnRuta($vehiculo);
@endphp
@if(!\App\Support\EstadoVehiculoCatalogo::enBaja($vehiculo) && !$enRuta)
<form method="POST" action="{{ route('envios.vehiculos.toggle-mantenimiento', $vehiculo) }}" class="d-inline">
    @csrf
    @method('PATCH')
    @if(\App\Support\EstadoVehiculoCatalogo::enMantenimiento($vehiculo))
        <button type="button"
            class="btn {{ $compact ? 'btn-outline-success' : 'btn-sm btn-success' }}"
            title="Marcar como operativo"
            data-confirm-modal
            data-confirm-tone="success"
            data-confirm-title="Volver a operativo"
            data-confirm-message="¿Marcar el vehículo {{ $vehiculo->placa }} como operativo? Podrá volver a asignarse en envíos y rutas.">
            <i class="fas fa-check{{ $compact ? '' : ' mr-1' }}"></i>@unless($compact) Marcar operativo @endunless
        </button>
    @else
        <button type="button"
            class="btn {{ $compact ? 'btn-outline-warning' : 'btn-sm btn-warning' }}"
            title="Poner en mantenimiento"
            data-confirm-modal
            data-confirm-tone="warning"
            data-confirm-title="Poner en mantenimiento"
            data-confirm-message="¿Marcar el vehículo {{ $vehiculo->placa }} como en mantenimiento? No podrá usarse en envíos ni rutas hasta reactivarlo.">
            <i class="fas fa-wrench{{ $compact ? '' : ' mr-1' }}"></i>@unless($compact) Poner en mantenimiento @endunless
        </button>
    @endif
</form>
@endif
