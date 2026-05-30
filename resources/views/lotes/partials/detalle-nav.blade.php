<ul class="nav nav-pills lote-section-nav flex-wrap mb-3">
    <li class="nav-item">
        <a href="{{ route('lotes.show', $lote) }}"
            class="nav-link {{ request()->routeIs('lotes.show') ? 'active' : '' }}">
            <i class="fas fa-info-circle mr-1"></i> Información
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('lotes.trazabilidad', $lote) }}"
            class="nav-link {{ request()->routeIs('lotes.trazabilidad') ? 'active' : '' }}">
            <i class="fas fa-history mr-1"></i> Trazabilidad
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('lotes.ubicacion', $lote) }}"
            class="nav-link {{ request()->routeIs('lotes.ubicacion') ? 'active' : '' }}">
            <i class="fas fa-map mr-1"></i> Ubicación
        </a>
    </li>
</ul>
