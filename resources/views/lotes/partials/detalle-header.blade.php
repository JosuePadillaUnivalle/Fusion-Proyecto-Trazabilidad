@php
    use App\Support\EstadoLoteCatalogo;
    $estadoNombre = $lote->estadoTipo->nombre ?? 'Sin estado';
    $estadoSlug = EstadoLoteCatalogo::slugFromNombre($estadoNombre) ?: 'default';
@endphp

<div class="lote-hero">
    <div class="lote-hero__glow" aria-hidden="true"></div>
    <div class="row align-items-start align-items-md-center">
        <div class="col-lg-8">
            <div class="lote-hero__title-row">
                @if($lote->imagenurl)
                <div class="lote-hero__thumb">
                    <img src="{{ $lote->imagenurl }}" alt="Foto del lote {{ $lote->nombre }}">
                </div>
                @endif
                <div class="lote-hero__title-block">
                    <div class="lote-hero__eyebrow">
                        <i class="fas fa-map-marked-alt"></i> Lote agrícola
                    </div>
                    <h1 class="lote-hero__title">{{ $lote->nombre }}</h1>
                    <div class="lote-hero__meta">
                        <span class="lote-hero__meta-item">
                            <i class="fas fa-user"></i>
                            {{ trim(($lote->usuario->nombre ?? '').' '.($lote->usuario->apellido ?? '')) ?: 'Sin asignar' }}
                        </span>
                        <span class="lote-hero__meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $lote->ubicacion_visible }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-3 mt-lg-0">
            <div class="lote-hero__chips">
                <span class="lote-hero-chip lote-hero-chip--estado lote-hero-chip--estado-{{ $estadoSlug }}">
                    {{ ucfirst($estadoNombre) }}
                </span>
                @if($lote->cultivo_etiqueta)
                <span class="lote-hero-chip lote-hero-chip--cultivo">
                    <i class="fas fa-seedling"></i>{{ $lote->cultivo_etiqueta }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
