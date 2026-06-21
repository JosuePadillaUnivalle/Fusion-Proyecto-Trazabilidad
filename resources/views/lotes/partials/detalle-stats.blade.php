<div class="lote-kpi-row">
    <div class="lote-kpi lote-kpi--green">
        <div class="lote-kpi__icon"><i class="fas fa-ruler-combined"></i></div>
        <div class="lote-kpi__body">
            <div class="lote-kpi__val">{{ $lote->superficie }}</div>
            <div class="lote-kpi__lbl">Hectáreas</div>
        </div>
    </div>
    <div class="lote-kpi lote-kpi--blue">
        <div class="lote-kpi__icon"><i class="fas fa-calendar-day"></i></div>
        <div class="lote-kpi__body">
            <div class="lote-kpi__val">{{ $estadisticas['dias_desde_siembra'] ?? '—' }}</div>
            <div class="lote-kpi__lbl">Días desde siembra</div>
        </div>
    </div>
    <div class="lote-kpi lote-kpi--amber">
        <div class="lote-kpi__icon"><i class="fas fa-flask"></i></div>
        <div class="lote-kpi__body">
            <div class="lote-kpi__val">{{ $estadisticas['total_insumos'] }}</div>
            <div class="lote-kpi__lbl">Insumos</div>
        </div>
    </div>
    <div class="lote-kpi lote-kpi--teal">
        <div class="lote-kpi__icon"><i class="fas fa-tasks"></i></div>
        <div class="lote-kpi__body">
            <div class="lote-kpi__val">{{ $estadisticas['total_actividades'] }}</div>
            <div class="lote-kpi__lbl">Actividades</div>
        </div>
    </div>
    <div class="lote-kpi lote-kpi--indigo">
        <div class="lote-kpi__icon"><i class="fas fa-tractor"></i></div>
        <div class="lote-kpi__body">
            <div class="lote-kpi__val">{{ $estadisticas['total_cosechas'] }}</div>
            <div class="lote-kpi__lbl">Cosechas</div>
        </div>
    </div>
    <div class="lote-kpi lote-kpi--rose">
        <div class="lote-kpi__icon"><i class="fas fa-weight-hanging"></i></div>
        <div class="lote-kpi__body">
            <div class="lote-kpi__val">{{ number_format($estadisticas['produccion_total'], 0) }}<small> kg</small></div>
            <div class="lote-kpi__lbl">Producción</div>
        </div>
    </div>
</div>
