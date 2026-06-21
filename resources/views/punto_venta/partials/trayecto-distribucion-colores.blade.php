@php
    $partes = $trayectoPartes ?? null;
    $origen = $partes['origen'] ?? null;
    $destinos = $partes['destinos'] ?? [];
@endphp
@if($origen || $destinos !== [])
    @if($origen)
        <span class="text-success font-weight-bold">{{ $origen }}</span>
    @endif
    @if($destinos !== [])
        @if($origen)<span class="text-muted mx-1">a</span>@endif
        @foreach($destinos as $i => $dest)
            @if($i > 0)<span class="text-muted mx-1">→</span>@endif
            <span class="text-danger font-weight-bold">{{ $dest }}</span>
        @endforeach
    @endif
@endif
