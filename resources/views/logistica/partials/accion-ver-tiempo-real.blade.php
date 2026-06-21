@if(! empty($urlTiempoReal))
<div class="env-accion-tiempo-real {{ $conBordeSuperior ?? false ? 'border-top pt-3 mt-3' : '' }}">
    <div class="env-accion-tiempo-real__head">
        <span class="env-accion-tiempo-real__icon"><i class="fas fa-satellite-dish"></i></span>
        <div>
            <strong class="d-block">Ruta en curso</strong>
            <span class="small text-muted">El camión avanza hacia el destino. Puede seguir el recorrido en el mapa en vivo.</span>
        </div>
    </div>
    <a href="{{ $urlTiempoReal }}" class="env-accion-tiempo-real__link">
        <i class="fas fa-map-marked-alt"></i> Ver en tiempo real
    </a>
</div>
@endif
