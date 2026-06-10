@php /** @var \App\Models\MaquinaPlanta $maquina */ @endphp
<form method="POST" action="{{ route('maquinas-planta.toggle-activo', $maquina) }}" class="d-inline">
    @csrf
    @method('PATCH')
    @if($maquina->activo)
        <button type="button" class="btn btn-sm btn-warning" title="Poner en mantenimiento"
            data-confirm-modal
            data-confirm-tone="warning"
            data-confirm-title="Poner en mantenimiento"
            data-confirm-message="¿Marcar «{{ $maquina->nombre }}»@if($maquina->codigo) ({{ $maquina->codigo }})@endif como en mantenimiento? Los procesos de transformación que usen esta máquina quedarán automáticamente no disponibles.">
            <i class="fas fa-wrench"></i>
        </button>
    @else
        <button type="button" class="btn btn-sm btn-success" title="Marcar como activa"
            data-confirm-modal
            data-confirm-tone="success"
            data-confirm-title="Reactivar máquina"
            data-confirm-message="¿Marcar «{{ $maquina->nombre }}»@if($maquina->codigo) ({{ $maquina->codigo }})@endif como activa? Los procesos de transformación vinculados volverán a estar disponibles automáticamente.">
            <i class="fas fa-check"></i>
        </button>
    @endif
</form>
