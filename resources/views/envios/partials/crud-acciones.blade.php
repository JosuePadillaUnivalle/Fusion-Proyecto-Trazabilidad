@props([
    'showRoute',
    'editRoute',
    'destroyRoute',
    'entityName' => 'este registro',
    'readPermission' => null,
    'updatePermission' => null,
    'deletePermission' => null,
])
<div class="btn-group btn-group-sm" role="group">
    @if(!$readPermission || auth()->user()?->can($readPermission))
        <a href="{{ $showRoute }}" class="btn btn-outline-info" title="Ver detalle">
            <i class="fas fa-eye"></i>
        </a>
    @endif
    @if(!$updatePermission || auth()->user()?->can($updatePermission))
        <a href="{{ $editRoute }}" class="btn btn-outline-primary" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
    @endif
    @if(!$deletePermission || auth()->user()?->can($deletePermission))
        <form method="POST" action="{{ $destroyRoute }}" class="d-inline"
              onsubmit="return confirm('¿Eliminar {{ $entityName }}? Esta acción no se puede deshacer.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endif
</div>
