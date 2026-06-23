@php
    $usuario = auth()->user();
    $rutaPrefijo = $rutaPrefijo ?? 'punto-venta.rutas';
    $puedeEmpezar = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($usuario, $ruta);
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
    $compacto = ! empty($compacto);
    $cierreSvc = app(\App\Services\CierreEnvioDistribucionPdvService::class);
@endphp

@if($puedeEmpezar)
    @if($cierreSvc->tieneCondicionesVehiculo($ruta))
    <form method="POST" action="{{ route($rutaPrefijo.'.empezar-ruta', $ruta) }}" class="d-inline m-0">
        @csrf
        @method('PATCH')
        <button type="submit"
                class="btn btn-sm {{ ($bloque ?? false) ? 'btn-success btn-block btn-lg' : ($compacto ? 'btn-success' : 'btn-success btn-lg') }}"
                title="Empezar ruta simulada">
            <i class="fas fa-play{{ ($bloque ?? false) || ! $compacto ? ' mr-1' : '' }}"></i>{{ ($bloque ?? false) || ! $compacto ? 'Empezar ruta' : '' }}
        </button>
    </form>
    @else
    <a href="{{ route($rutaPrefijo.'.cierre.panel', $ruta) }}"
       class="btn btn-sm {{ ($bloque ?? false) ? 'btn-success btn-block btn-lg' : ($compacto ? 'btn-success' : 'btn-success btn-lg') }}"
       title="Registrar condiciones del vehículo">
        <i class="fas fa-clipboard-check{{ ($bloque ?? false) || ! $compacto ? ' mr-1' : '' }}"></i>{{ ($bloque ?? false) || ! $compacto ? 'Verificar condiciones del Vehículo' : '' }}
    </a>
    @endif
@elseif($simulacionActiva)
    <a href="{{ route($rutaPrefijo.'.cierre.panel', $ruta) }}"
       class="btn btn-sm {{ ($bloque ?? false) ? 'btn-primary btn-block btn-lg' : 'btn-primary' }} font-weight-bold">
        <i class="fas fa-tasks{{ ($bloque ?? false) ? ' mr-1' : '' }}"></i>{{ ($bloque ?? false) ? 'Continuar cierre operativo' : '' }}
    </a>
@endif
