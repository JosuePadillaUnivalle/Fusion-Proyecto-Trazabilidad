@php
    $tema = $tema ?? 'success';
    $themeClass = match ($tema) {
        'primary' => 'rep-theme-primary',
        'warning' => 'rep-theme-warning',
        'info' => 'rep-theme-info',
        'secondary', 'purple' => 'rep-theme-purple',
        'brand' => 'rep-theme-brand',
        default => 'rep-theme-success',
    };
@endphp
<div class="rep-page-header {{ $themeClass }}">
    <div class="rep-page-header-inner">
        <div class="rep-page-header-icon">
            <i class="fas {{ $icono }}"></i>
        </div>
        <div class="rep-page-header-text">
            <h2>{{ $titulo }}</h2>
            <p>{{ $descripcion }}</p>
        </div>
    </div>
    <div class="rep-page-header-actions">
        <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-outline-light">
            <i class="fas fa-th-large mr-1"></i> Centro de reportes
        </a>
        @isset($moduloRuta)
        <a href="{{ $moduloRuta }}" class="btn btn-sm btn-light">
            <i class="fas {{ $moduloIcono ?? 'fa-external-link-alt' }} mr-1"></i> {{ $moduloLabel }}
        </a>
        @endisset
    </div>
</div>
