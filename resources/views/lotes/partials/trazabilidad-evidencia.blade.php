@php
    $tituloEvento = (string) ($evento['titulo'] ?? 'Evento');
    $tipoEvidencia = (string) ($evento['evidencia_tipo'] ?? '');
    $urlInsumo = ($tipoEvidencia === 'insumo' || $tipoEvidencia === 'insumo_foto')
        ? ($evento['evidencia_url'] ?? null)
        : null;
    $urlFoto = match ($tipoEvidencia) {
        'insumo_foto' => $evento['evidencia_foto_url'] ?? null,
        'foto' => $evento['evidencia_url'] ?? null,
        default => null,
    };
    $tieneIcono = !empty($evento['evidencia_icono']);
    $tieneGaleria = filled($urlInsumo) || filled($urlFoto);
@endphp

@if($tieneIcono)
    <div class="evento-trz-evidencia evento-trz-evidencia--icono mt-2">
        <div class="evento-trz-evidencia__icono" title="Actividad de riego">
            <i class="fas fa-{{ $evento['evidencia_icono'] }}"></i>
        </div>
        <span class="evento-trz-evidencia__caption small text-muted d-block mt-1">
            <i class="fas fa-tint mr-1"></i> Riego registrado
        </span>
    </div>
@elseif($tieneGaleria)
    <div class="evento-trz-evidencia-galeria mt-2" role="group" aria-label="Evidencias del evento">
        @if(filled($urlInsumo))
            <figure class="evento-trz-evidencia-card">
                <button type="button"
                        class="evento-trz-evidencia-card__btn btn-ver-evidencia"
                        data-url="{{ $urlInsumo }}"
                        data-titulo="{{ $tituloEvento }}"
                        title="Ver insumo en tamaño completo">
                    <span class="evento-trz-evidencia-card__frame">
                        <img src="{{ $urlInsumo }}"
                             alt="Insumo aplicado: {{ $tituloEvento }}"
                             class="evento-trz-evidencia-card__img"
                             loading="lazy"
                             decoding="async">
                    </span>
                    <figcaption class="evento-trz-evidencia-card__caption">
                        <i class="fas fa-flask mr-1"></i> Insumo utilizado
                    </figcaption>
                </button>
            </figure>
        @endif
        @if(filled($urlFoto))
            <figure class="evento-trz-evidencia-card">
                <button type="button"
                        class="evento-trz-evidencia-card__btn btn-ver-evidencia"
                        data-url="{{ $urlFoto }}"
                        data-titulo="{{ $tituloEvento }}"
                        title="Ver evidencia en tamaño completo">
                    <span class="evento-trz-evidencia-card__frame">
                        <img src="{{ $urlFoto }}"
                             alt="Evidencia: {{ $tituloEvento }}"
                             class="evento-trz-evidencia-card__img"
                             loading="lazy"
                             decoding="async">
                    </span>
                    <figcaption class="evento-trz-evidencia-card__caption">
                        <i class="fas fa-camera mr-1"></i> Evidencia fotográfica
                    </figcaption>
                </button>
            </figure>
        @endif
    </div>
@endif
