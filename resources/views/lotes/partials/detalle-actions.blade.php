<div class="lote-section-card mt-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between flex-wrap align-items-center" style="gap:.5rem;">
            <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary btn-action">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
            @if(empty($ocultarGestion))
            <div class="d-flex flex-wrap" style="gap:.4rem;">
                @can('lotes.update')
                <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-outline-warning btn-action">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                @endcan
                @can('lotes.delete')
                @if(\App\Support\EstadoLoteCatalogo::loteSePuedeEliminar($lote->estadoTipo->nombre ?? null))
                <form action="{{ route('lotes.destroy', $lote) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('¿Eliminar este lote?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-action">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </form>
                @endif
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>
