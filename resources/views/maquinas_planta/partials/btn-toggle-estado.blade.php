@php /** @var \App\Models\MaquinaPlanta $maquina */ @endphp
<form method="POST" action="{{ route('maquinas-planta.toggle-activo', $maquina) }}" class="d-inline">
    @csrf
    @method('PATCH')
    @if($maquina->activo)
        <button type="submit" class="btn btn-sm btn-warning" title="Poner en mantenimiento"
            onclick="return confirm('¿Marcar esta máquina como en mantenimiento?')">
            <i class="fas fa-wrench"></i>
        </button>
    @else
        <button type="submit" class="btn btn-sm btn-success" title="Marcar como activa">
            <i class="fas fa-check"></i>
        </button>
    @endif
</form>
