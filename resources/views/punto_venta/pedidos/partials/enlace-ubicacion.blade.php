@props(['href'])

@if($href)
<a href="{{ $href }}" class="pdv-btn-ubicacion js-link-ubicacion-pedido">
    <i class="fas fa-map-marker-alt"></i>
    <span>Ver ubicación</span>
</a>
@endif
