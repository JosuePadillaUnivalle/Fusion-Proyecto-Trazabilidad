<div class="lote-tabs-wrap">
    <ul class="nav lote-tabs flex-wrap">
        <li class="nav-item">
            <a href="{{ route('lotes.show', $lote) }}"
                class="nav-link {{ request()->routeIs('lotes.show') ? 'active' : '' }}">
                <i class="fas fa-info-circle"></i>
                <span>Información</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('lotes.trazabilidad', $lote) }}"
                class="nav-link {{ request()->routeIs('lotes.trazabilidad') ? 'active' : '' }}">
                <i class="fas fa-project-diagram"></i>
                <span>Trazabilidad</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('lotes.ubicacion', $lote) }}"
                class="nav-link {{ request()->routeIs('lotes.ubicacion') ? 'active' : '' }}">
                <i class="fas fa-map"></i>
                <span>Ubicación</span>
            </a>
        </li>
    </ul>
</div>
