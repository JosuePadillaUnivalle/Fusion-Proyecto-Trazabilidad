@if(($tipos ?? collect())->isNotEmpty())
<div class="veh-catalogo-seccion">
    <div class="veh-catalogo-seccion__panel">
        <button class="veh-catalogo-seccion__toggle collapsed"
                type="button"
                data-toggle="collapse"
                data-target="#catalogoTiposVeh"
                aria-expanded="false"
                aria-controls="catalogoTiposVeh">
            <span class="veh-catalogo-seccion__toggle-icon">
                <i class="fas fa-book"></i>
            </span>
            <span class="veh-catalogo-seccion__toggle-text">
                <strong>Referencia: tipos y tamaños de vehículo</strong>
                <small>Capacidad, volumen y licencia por categoría logística</small>
            </span>
            <span class="veh-catalogo-seccion__chev"><i class="fas fa-chevron-down"></i></span>
        </button>

        <div class="collapse" id="catalogoTiposVeh">
            <div class="veh-catalogo-seccion__body">
                @include('envios.partials.tipos-vehiculo-catalogo', [
                    'tipos' => $tipos,
                    'embebido' => true,
                ])
            </div>
        </div>
    </div>
</div>
@endif
