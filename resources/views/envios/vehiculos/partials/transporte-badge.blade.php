@php
    use App\Support\VehiculoTransporteCatalogo;

    $codigo = $codigo ?? VehiculoTransporteCatalogo::codigoDesdeNombre($nombre ?? null);
    $meta = VehiculoTransporteCatalogo::metaUi($codigo);
    $size = $size ?? 'md';
    $activo = $activo ?? true;
    $badgeClass = VehiculoTransporteCatalogo::badgeClaseBootstrap($codigo);
@endphp
<span class="badge {{ $badgeClass }} badge-estado veh-tt-badge veh-tt-badge--{{ $size }} @if(!$activo) is-inactive @endif"
      title="{{ $meta['hint'] }}">
    <i class="fas {{ $meta['icon'] }} veh-tt-badge__icon"></i>
    <span>{{ $nombre ?? $meta['nombre'] }}</span>
</span>
