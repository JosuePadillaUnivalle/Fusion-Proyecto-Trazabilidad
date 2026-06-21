@php
    $limite = $limite ?? null;
@endphp
@if($limite === 'peso')
    <span class="veh-limite-badge veh-limite-badge--peso" title="El tope lo marca el peso máximo del vehículo">Peso</span>
@elseif($limite === 'volumen')
    <span class="veh-limite-badge veh-limite-badge--volumen" title="El tope lo marca el espacio disponible en la caja">Volumen</span>
@else
    <span class="text-muted">—</span>
@endif
