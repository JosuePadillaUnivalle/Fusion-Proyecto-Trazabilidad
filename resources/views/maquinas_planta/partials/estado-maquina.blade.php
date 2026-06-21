@php /** @var \App\Models\MaquinaPlanta $maquina */ @endphp
@if($maquina->activo)
    <span class="badge badge-success">Activa</span>
@else
    <span class="badge badge-warning text-dark">En mantenimiento</span>
@endif
