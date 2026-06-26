@php
    $paradas = $paradasMapa ?? [];
    $destino = $paradas !== [] ? $paradas[count($paradas) - 1] : null;
    $lat = $destino['lat'] ?? null;
    $lng = $destino['lng'] ?? null;
@endphp
@if($lat && $lng)
<a href="https://www.google.com/maps/search/?api=1&amp;query={{ $lat }},{{ $lng }}"
   class="btn btn-sm btn-outline-secondary {{ $bloque ?? false ? 'btn-block mt-2' : '' }}"
   target="_blank"
   rel="noopener noreferrer"
   title="Abrir el destino en Google Maps">
    <i class="fas fa-map-marker-alt mr-1"></i> {{ $etiqueta ?? 'Ver destino en Google Maps' }}
</a>
@endif
