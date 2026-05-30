<div class="lote-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-map-marked-alt mr-2"></i>{{ $lote->nombre }}</h2>
            <p class="mb-0 mt-2">
                <i class="fas fa-user mr-1"></i> {{ $lote->usuario->nombre ?? 'Sin asignar' }}
                {{ $lote->usuario->apellido ?? '' }}
                <span class="mx-2">|</span>
                <i class="fas fa-map-marker-alt mr-1"></i> {{ $lote->ubicacion ?? 'Sin ubicación' }}
            </p>
        </div>
        <div class="col-md-4 text-md-right">
            <span class="estado-badge {{ $estadoClass }}">{{ ucfirst($lote->estadoTipo->nombre ?? 'Sin estado') }}</span>
            @if($lote->cultivo)
                <br><span class="badge badge-light mt-2" style="font-size: 0.9rem;">
                    <i class="fas fa-seedling mr-1"></i> {{ $lote->cultivo->nombre }}
                </span>
            @endif
        </div>
    </div>
</div>
