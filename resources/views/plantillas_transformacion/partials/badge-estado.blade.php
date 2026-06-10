@php /** @var \App\Models\PlantillaTransformacion $plantilla */ @endphp
@if($plantilla->bloqueadaPorMantenimiento())
    <span class="badge badge-warning text-dark" title="Máquinas en mantenimiento: {{ $plantilla->maquinasEnMantenimiento()->pluck('nombre')->join(', ') }}">
        <i class="fas fa-wrench mr-1"></i>En mantenimiento
    </span>
@else
    <span class="badge badge-success">Activa</span>
@endif
