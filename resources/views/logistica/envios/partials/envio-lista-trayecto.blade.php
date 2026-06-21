@php
    $partes = $trayectoPartes ?? null;
    $recogidas = $partes['recogidas'] ?? [];
    $destino = $partes['destino'] ?? null;
    $origenTexto = $recogidas !== []
        ? implode(' · ', $recogidas)
        : null;
@endphp
@if($origenTexto || $destino)
<div class="envio-lista-ruta">
    <div class="envio-lista-ruta__punto envio-lista-ruta__punto--origen">
        <span class="envio-lista-ruta__etiqueta">Origen</span>
        <span class="envio-lista-ruta__nombre">{{ $origenTexto ?? '—' }}</span>
    </div>
    <div class="envio-lista-ruta__separador" aria-hidden="true">
        <i class="fas fa-arrow-right"></i>
    </div>
    <div class="envio-lista-ruta__punto envio-lista-ruta__punto--destino">
        <span class="envio-lista-ruta__etiqueta">Destino</span>
        <span class="envio-lista-ruta__nombre">{{ $destino ?? '—' }}</span>
    </div>
</div>
@endif
