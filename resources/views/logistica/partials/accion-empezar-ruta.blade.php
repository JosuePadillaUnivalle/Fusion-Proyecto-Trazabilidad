@php
    $usuario = auth()->user();
    $puedeEmpezar = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarAgricola($usuario, $asignacion);
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion);
    $compacto = !empty($compacto);
@endphp

@if($puedeEmpezar)
    <form method="POST" action="{{ route('logistica.asignaciones.empezar-ruta', $asignacion) }}" class="d-inline m-0">
        @csrf
        @method('PATCH')
        <button type="submit"
                class="btn btn-sm {{ ($bloque ?? false) ? 'btn-success btn-block btn-lg' : ($compacto ? 'btn-success' : 'btn-success btn-lg') }}"
                title="Empezar ruta simulada">
            <i class="fas fa-play{{ ($bloque ?? false) || ! $compacto ? ' mr-1' : '' }}"></i>{{ ($bloque ?? false) || ! $compacto ? 'Empezar ruta' : '' }}
        </button>
    </form>
@elseif($simulacionActiva)
    @include('logistica.partials.progreso-simulacion-transportista', [
        'tipo' => 'agricola',
        'id' => $asignacion->envioasignacionmultipleid,
    ])
@endif
