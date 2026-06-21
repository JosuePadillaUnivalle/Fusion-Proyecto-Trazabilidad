@props([
    'href',
    'clase' => 'pdv-btn-ubicacion',
    'compacto' => false,
])

@if($href)
<a href="{{ $href }}" class="{{ $clase }} {{ $compacto ? 'btn btn-sm btn-outline-primary' : '' }}">
    <i class="fas fa-satellite-dish"></i>
    <span>Ver en tiempo real</span>
</a>
@endif
