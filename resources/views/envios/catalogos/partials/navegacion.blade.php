@php
    $tipoActivo = $tipo ?? '';
@endphp
<aside class="cat-log-side" aria-label="Catálogos de logística">
    <div class="cat-log-side__head">
        <i class="fas fa-layer-group"></i> Catálogos logística
    </div>
    <ul class="cat-log-side__nav">
        @foreach(\App\Support\LogisticaCatalogoRegistry::all() as $slug => $meta)
            @php $tema = $meta['tema'] ?? ['accent' => '#2c5530', 'soft' => '#e8f4ec']; @endphp
            <li>
                <a href="{{ route('envios.catalogos.index', $slug) }}"
                   class="cat-log-side__link {{ $slug === $tipoActivo ? 'is-active' : '' }}"
                   style="--link-accent: {{ $tema['accent'] }}; --link-soft: {{ $tema['soft'] }};">
                    @if(!empty($meta['icono']))
                        <span class="cat-log-side__icon-wrap"><i class="fas {{ $meta['icono'] }}"></i></span>
                    @endif
                    <span>{{ $meta['menu'] ?? $meta['titulo'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</aside>
