@php
    $partes = $trayectoPartes ?? null;
    $recogidas = $partes['recogidas'] ?? [];
    $destino = $partes['destino'] ?? null;
@endphp
@if($recogidas !== [] || $destino)
    @foreach($recogidas as $i => $rec)
        @if($i > 0)<span class="text-muted mx-1">→</span>@endif
        <span class="text-success font-weight-bold">{{ $rec }}</span>
    @endforeach
    @if($destino)
        @if($recogidas !== [])<span class="text-muted mx-1">a</span>@endif
        <span class="text-danger font-weight-bold">{{ $destino }}</span>
    @endif
@endif
