@php
    /** @var \App\Models\MaquinaPlanta $maquina */
    $src = $maquina->imagenSrc();
    $size = $size ?? 'thumb';
    $nombre = $maquina->nombre;
@endphp

@if($src)
    @if($size === 'thumb')
        <button type="button"
            class="btn p-0 border-0 foto-maquina-thumb"
            data-toggle="modal"
            data-target="#modalFotoMaquina"
            data-src="{{ $src }}"
            data-nombre="{{ $nombre }}"
            title="Ver foto de {{ $nombre }}">
            <img src="{{ $src }}" alt="{{ $nombre }}" class="rounded foto-maquina-img-thumb" loading="lazy">
        </button>
    @elseif($size === 'hero')
        <img src="{{ $src }}" alt="{{ $nombre }}" class="detalle-hero-img">
    @else
        <img src="{{ $src }}" alt="{{ $nombre }}" class="preview-imagen foto-maquina-img-lg">
    @endif
@else
    @if($size === 'thumb')
        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-muted foto-maquina-img-thumb" title="Sin foto">
            <i class="fas fa-cogs"></i>
        </span>
    @endif
@endif
