<div class="card lote-section-card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap">
            <a href="{{ route('lotes.index') }}" class="btn btn-secondary btn-action mb-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
            <div>
                @can('lotes.update')
                <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-warning btn-action mb-2">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                @endcan
                @can('lotes.delete')
                <form action="{{ route('lotes.destroy', $lote) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('¿Eliminar este lote?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-action mb-2">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>
