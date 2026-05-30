<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box small-box-green">
            <div class="inner"><h3>{{ $lote->superficie }}</h3><p>Hectáreas</p></div>
            <div class="icon"><i class="fas fa-ruler-combined"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box small-box-blue">
            <div class="inner"><h3>{{ $estadisticas['dias_desde_siembra'] ?? '-' }}</h3><p>Días</p></div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box small-box-yellow">
            <div class="inner"><h3>{{ $estadisticas['total_insumos'] }}</h3><p>Insumos</p></div>
            <div class="icon"><i class="fas fa-flask"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box small-box-green">
            <div class="inner"><h3>{{ $estadisticas['total_actividades'] }}</h3><p>Actividades</p></div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box small-box-blue">
            <div class="inner"><h3>{{ $estadisticas['total_cosechas'] }}</h3><p>Cosechas</p></div>
            <div class="icon"><i class="fas fa-tractor"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box small-box-red">
            <div class="inner">
                <h3>{{ number_format($estadisticas['produccion_total'], 0) }}<span style="font-size: 14px;">kg</span></h3>
                <p>Producción</p>
            </div>
            <div class="icon"><i class="fas fa-weight-hanging"></i></div>
            <span class="small-box-footer">&nbsp;</span>
        </div>
    </div>
</div>
