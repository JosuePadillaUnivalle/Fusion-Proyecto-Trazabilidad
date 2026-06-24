@extends('layouts.app')

@section('title', 'Centro de reportes | AgroFusion')
@section('page_title', 'Centro de reportes')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@push('styles')
    @include('reportes.partials.estilos')
@endpush

@section('content')
<section class="content px-0 rpt-hub">
    <div class="container-fluid px-0">
        <div class="rpt-hub__intro">
            <div>
                <h2><i class="fas fa-chart-bar mr-2 text-muted"></i>Centro de reportes</h2>
                <p>Métricas operativas de envíos, inventario y distribución. Elija un reporte para filtrar, visualizar y exportar.</p>
            </div>
        </div>

        @if($items->isEmpty())
            <div class="alert alert-light border text-muted mb-0">
                No tiene reportes disponibles con su perfil actual.
            </div>
        @else
            <div class="rpt-grid">
                @foreach($items as $card)
                    @php $accent = $card['accent'] ?? 'forest'; @endphp
                    <a href="{{ route($card['route']) }}" class="rpt-card rpt-card--{{ $accent }}">
                        <div class="rpt-card__head">
                            <span class="rpt-card__icon">
                                <i class="fas {{ $card['icon'] ?? 'fa-file-alt' }}"></i>
                            </span>
                            <span class="rpt-card__title">{{ $card['title'] }}</span>
                        </div>
                        <p class="rpt-card__sub">{{ $card['subtitle'] ?? '' }}</p>
                        <div class="rpt-card__foot">
                            @if(!empty($card['preview']))
                                <span class="rpt-card__preview">{{ $card['preview'] }}</span>
                            @else
                                <span>&nbsp;</span>
                            @endif
                            <span class="rpt-card__cta">Abrir <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
