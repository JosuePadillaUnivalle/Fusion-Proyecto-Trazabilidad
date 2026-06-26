@php
    $catalogoSubId = $catalogoSubId ?? 'default';
    $catalogoLogisticaOpen = $catalogoLogisticaOpen ?? request()->routeIs('envios.catalogos.*');
    $catalogoTipoActivo = $catalogoTipoActivo ?? ($catalogoLogisticaOpen ? request()->route('tipo') : null);
@endphp
@if(\App\Support\LogisticaCatalogoAcceso::puedeVer(auth()->user()))
<li class="ag-sub-li">
    <a href="#" class="ag-sub-a {{ $catalogoLogisticaOpen ? 'group-open active' : '' }}" data-toggle-sub="sub-env-catalogos-{{ $catalogoSubId }}">
        Catálogos logística
        <i class="fas fa-chevron-right ag-sub-arrow"></i>
    </a>
    <ul class="ag-subnav ag-subnav-nested {{ $catalogoLogisticaOpen ? 'open' : '' }}" id="sub-env-catalogos-{{ $catalogoSubId }}">
        <li class="ag-sub-li"><a href="{{ route('envios.catalogos.index', 'tipos-empaque') }}" class="ag-sub-a {{ $catalogoTipoActivo === 'tipos-empaque' ? 'active' : '' }}">Tipos de empaque</a></li>
        <li class="ag-sub-li"><a href="{{ route('envios.catalogos.index', 'tamano-conteo') }}" class="ag-sub-a {{ $catalogoTipoActivo === 'tamano-conteo' ? 'active' : '' }}">Tamaño / conteo</a></li>
        <li class="ag-sub-li"><a href="{{ route('envios.catalogos.index', 'tipos-vehiculo') }}" class="ag-sub-a {{ $catalogoTipoActivo === 'tipos-vehiculo' ? 'active' : '' }}">Tipos de vehículo</a></li>
        <li class="ag-sub-li"><a href="{{ route('envios.catalogos.index', 'tipos-transporte') }}" class="ag-sub-a {{ $catalogoTipoActivo === 'tipos-transporte' ? 'active' : '' }}">Tipos de transporte</a></li>
        <li class="ag-sub-li"><a href="{{ route('envios.catalogos.index', 'condiciones') }}" class="ag-sub-a {{ $catalogoTipoActivo === 'condiciones' ? 'active' : '' }}">Condiciones</a></li>
        <li class="ag-sub-li"><a href="{{ route('envios.catalogos.index', 'incidentes') }}" class="ag-sub-a {{ $catalogoTipoActivo === 'incidentes' ? 'active' : '' }}">Incidentes</a></li>
    </ul>
</li>
@endif
