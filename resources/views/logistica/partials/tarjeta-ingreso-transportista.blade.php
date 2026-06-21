@php
    $esMiAsignacion = (int) auth()->id() === (int) ($asignacion->transportista_usuarioid ?? 0);
    $costoBs = $asignacion->costo_bs !== null ? (float) $asignacion->costo_bs : null;
    $completado = \App\Support\TransporteIngresoCatalogo::envioCompletado($asignacion);
@endphp

@if($esMiAsignacion)
<div class="{{ !empty($conBordeSuperior) ? 'border-top pt-3 mt-3' : '' }}">
    <div class="env-det-label"><i class="fas fa-coins text-warning mr-1"></i>Tu ingreso por este servicio</div>
    @if($costoBs !== null)
        <div class="env-det-value text-success mb-1" style="font-size:1.35rem;">
            {{ number_format($costoBs, 2, ',', '.') }} Bs
        </div>
        <p class="small text-muted mb-0">
            @if($completado)
                <i class="fas fa-check-circle text-success mr-1"></i>
                Acreditado en
                <a href="{{ route('logistica.transportista.ingresos') }}">Mis ingresos</a>.
            @else
                <i class="fas fa-info-circle mr-1"></i>
                Se acredita cuando el envío sea recibido en planta.
            @endif
        </p>
    @else
        <p class="text-muted small mb-0">Logística aún no registró el monto de este servicio.</p>
    @endif
</div>
@endif
