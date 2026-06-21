@php
    $pendienteAgr = $pendienteAgricola ?? false;
    $enCamino = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true);
    $recibido = $llegoDestino ?? false;
    $aceptado = $asignacion->pedido && ! $pendienteAgr;
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion);
    $modoRecorrido = $modo ?? ($recibido ? 'horizontal' : 'vertical');

    $estadoPaso1 = $pendienteAgr ? 'activo' : ($aceptado ? 'hecho' : 'pendiente');
    $estadoPaso2 = $recibido ? 'hecho' : ($enCamino || $simulacionActiva ? 'activo' : 'pendiente');
    $estadoPaso3 = $recibido ? 'hecho' : 'pendiente';

    $progreso = match (true) {
        $recibido => 100,
        $enCamino || $simulacionActiva => 66,
        $aceptado => 33,
        default => 8,
    };

    $pasos = [
        [
            'estado' => $estadoPaso1,
            'icon' => 'fa-warehouse',
            'titulo' => 'Preparación en almacén',
            'titulo_corto' => 'Almacén',
            'desc' => $pendienteAgr
                ? 'Producción agrícola debe aceptar el pedido y reservar el stock antes de que el camión salga.'
                : 'Pedido aceptado. Chofer y vehículo listos para cargar y salir del almacén agrícola.',
            'fecha' => $aceptado && $asignacion->pedido?->fecha_aceptacion_agricola
                ? $asignacion->pedido->fecha_aceptacion_agricola
                : ($asignacion->fecha_asignacion && ! $pendienteAgr ? $asignacion->fecha_asignacion : null),
        ],
        [
            'estado' => $estadoPaso2,
            'icon' => 'fa-check',
            'titulo' => 'Tránsito hacia planta',
            'titulo_corto' => 'Tránsito',
            'desc' => $simulacionActiva
                ? 'Ruta en curso. El progreso se actualiza en tiempo real hasta la planta de destino.'
                : 'El camión sale cargado desde el almacén agrícola hacia la planta de destino.',
            'fecha' => $recibido || $enCamino ? ($asignacion->simulacion_inicio_at ?? $asignacion->fecha_asignacion) : null,
        ],
        [
            'estado' => $estadoPaso3,
            'icon' => 'fa-check',
            'titulo' => 'Recepción en planta',
            'titulo_corto' => 'Planta',
            'desc' => 'La planta confirma la llegada y registra la mercadería en su almacén.',
            'fecha' => $asignacion->fecha_recepcion_planta,
        ],
    ];
@endphp

@if($modoRecorrido === 'horizontal')
<div class="env-recorrido-horizontal">
    <div class="env-recorrido-progreso mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small font-weight-bold text-muted text-uppercase" style="letter-spacing:.04em;font-size:.65rem;">Avance</span>
            <span class="env-recorrido-pct">{{ $progreso }}%</span>
        </div>
        <div class="env-recorrido-bar">
            <div class="env-recorrido-bar-fill" style="width:{{ $progreso }}%"></div>
        </div>
    </div>

    <div class="env-rh-track">
        @foreach($pasos as $paso)
            <div class="env-rh-step env-rh-step--{{ $paso['estado'] }}">
                <div class="env-rh-dot">
                    <i class="fas {{ $paso['estado'] === 'hecho' ? 'fa-check' : ($paso['estado'] === 'activo' ? $paso['icon'] : 'fa-circle') }}"></i>
                </div>
                <div class="env-rh-title">{{ $paso['titulo_corto'] }}</div>
                @if($paso['fecha'])
                    <div class="env-rh-date">{{ $paso['fecha']->format('d/m H:i') }}</div>
                @endif
            </div>
        @endforeach
    </div>

    @if($recibido)
        <div class="env-rh-footer">
            <i class="fas fa-warehouse text-success mr-1"></i>
            Recibido {{ $asignacion->fecha_recepcion_planta?->format('d/m/Y H:i') ?? '—' }}
            @if(trim(($asignacion->recepcionConfirmadaPor?->nombre ?? '').' '.($asignacion->recepcionConfirmadaPor?->apellido ?? '')))
                · {{ trim(($asignacion->recepcionConfirmadaPor?->nombre ?? '').' '.($asignacion->recepcionConfirmadaPor?->apellido ?? '')) }}
            @endif
            @if($asignacion->almacen?->nombre)
                · {{ $asignacion->almacen->nombre }}
            @endif
        </div>
    @endif
</div>
@else
<div class="env-recorrido-progreso mb-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="small font-weight-bold text-muted text-uppercase" style="letter-spacing:.04em;font-size:.65rem;">Avance del envío</span>
        <span class="env-recorrido-pct">{{ $progreso }}%</span>
    </div>
    <div class="env-recorrido-bar">
        <div class="env-recorrido-bar-fill" style="width:{{ $progreso }}%"></div>
    </div>
</div>

<ol class="env-pasos-lista">
    @foreach($pasos as $i => $paso)
        <li class="env-paso-item env-paso-item--{{ $paso['estado'] }}">
            <div class="env-paso-marker">
                <span class="env-paso-marker-num">{{ $i + 1 }}</span>
                <i class="fas {{ $paso['estado'] === 'hecho' ? 'fa-check' : $paso['icon'] }} env-paso-marker-icon"></i>
            </div>
            <div class="env-paso-body">
                <div class="env-paso-titulo">{{ $paso['titulo'] }}</div>
                @if($paso['estado'] !== 'hecho')
                    <p class="env-paso-desc mb-1">{{ $paso['desc'] }}</p>
                @endif
                @if($paso['fecha'])
                    <div class="env-paso-fecha">
                        <i class="far fa-clock mr-1"></i>{{ $paso['fecha']->format('d/m/Y H:i') }}
                    </div>
                @elseif($paso['estado'] === 'activo')
                    <div class="env-paso-fecha env-paso-fecha--activo">
                        <i class="fas fa-circle-notch fa-spin mr-1"></i>En curso
                    </div>
                @endif
            </div>
        </li>
    @endforeach
</ol>
@endif
