@extends('layouts.app')

@section('title', 'Clima | Fusion-Proyectos')
@section('page_title', 'Información climática')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Clima</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@endpush

@section('content')
<div class="modulo-prod">

    @if(!empty($weatherData['error']) && empty($weatherData['actual']))
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ $weatherData['error'] }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if(!empty($weatherData['aviso']))
    <div class="alert alert-info alert-dismissible fade show py-2" id="avisoClimaFuente">
        <i class="fas fa-info-circle mr-1"></i> {{ $weatherData['aviso'] }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div id="avisoClimaDinamico"></div>

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3 id="card-temp">
                        @if(!empty($weatherData['actual']))
                            {{ round($weatherData['actual']['temperatura']) }}°C
                        @else
                            <i class="fas fa-spinner fa-spin"></i>
                        @endif
                    </h3>
                    <p>Temperatura</p>
                </div>
                <div class="icon"><i class="fas fa-thermometer-half"></i></div>
                <span class="small-box-footer">Condición actual</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3 id="card-humidity">
                        @if(!empty($weatherData['actual']))
                            {{ $weatherData['actual']['humedad'] ?? '--' }}%
                        @else
                            <i class="fas fa-spinner fa-spin"></i>
                        @endif
                    </h3>
                    <p>Humedad</p>
                </div>
                <div class="icon"><i class="fas fa-tint"></i></div>
                <span class="small-box-footer">Relativa</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3 id="card-wind">
                        @if(!empty($weatherData['actual']))
                            {{ $weatherData['actual']['viento_kmh'] ?? '--' }} km/h
                        @else
                            <i class="fas fa-spinner fa-spin"></i>
                        @endif
                    </h3>
                    <p>Viento</p>
                </div>
                <div class="icon"><i class="fas fa-wind"></i></div>
                <span class="small-box-footer">Velocidad</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3 id="card-pressure">
                        @if(!empty($weatherData['actual']))
                            {{ $weatherData['actual']['presion'] ?? '--' }} hPa
                        @else
                            <i class="fas fa-spinner fa-spin"></i>
                        @endif
                    </h3>
                    <p>Presión</p>
                </div>
                <div class="icon"><i class="fas fa-tachometer-alt"></i></div>
                <span class="small-box-footer">Atmosférica</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card card-outline card-success card-modulo-main elevation-1 mb-3">
                <div class="card-header py-2 d-flex align-items-center">
                    <h3 class="card-title mb-0 text-sm flex-grow-1">
                        <i class="fas fa-cloud-sun text-success mr-1"></i> Condiciones actuales
                    </h3>
                    @if(!blank(config('services.weather.key')))
                    <button type="button" class="btn btn-tool" id="btnRefreshClima" title="Actualizar desde OpenWeather">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="weather-widget m-3" id="weather-widget">
                        @if(empty($weatherData['actual']))
                        <div class="text-center py-4" id="weather-loading">
                            <i class="fas fa-spinner fa-spin fa-3x"></i>
                            <p class="mt-3 mb-0">Cargando datos del clima…</p>
                        </div>
                        @else
                        <div id="weather-loading" style="display:none;"></div>
                        @endif
                        <div id="weather-content" style="{{ empty($weatherData['actual']) ? 'display: none;' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="weather-temp" id="weather-temp">--°C</div>
                                    <div class="weather-desc" id="weather-desc">--</div>
                                    <small class="d-block mt-1 opacity-75" id="weather-fuente"></small>
                                </div>
                                <div class="weather-icon">
                                    <img id="weather-icon" src="" alt="Clima">
                                </div>
                            </div>
                            <div class="mt-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span id="weather-location">Santa Cruz de la Sierra, Bolivia</span>
                            </div>
                            <div class="weather-details">
                                <div class="weather-detail-item">
                                    <i class="fas fa-tint d-block mb-1"></i>
                                    <div class="value" id="detail-humidity">--%</div>
                                    <div class="label">Humedad</div>
                                </div>
                                <div class="weather-detail-item">
                                    <i class="fas fa-wind d-block mb-1"></i>
                                    <div class="value" id="detail-wind">-- km/h</div>
                                    <div class="label">Viento</div>
                                </div>
                                <div class="weather-detail-item">
                                    <i class="fas fa-compress-arrows-alt d-block mb-1"></i>
                                    <div class="value" id="detail-pressure">-- hPa</div>
                                    <div class="label">Presión</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sun-card">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-sun mr-2"></i>Amanecer y atardecer</h6>
                <div class="sun-times">
                    <div class="sun-time">
                        <i class="fas fa-sunrise d-block"></i>
                        <div class="font-weight-bold" id="sunrise">--:--</div>
                        <small>Amanecer</small>
                    </div>
                    <div class="sun-time">
                        <i class="fas fa-moon d-block"></i>
                        <div class="font-weight-bold" id="sunset">--:--</div>
                        <small>Atardecer</small>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-success card-modulo-main elevation-1">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-calendar-alt text-success mr-1"></i> Pronóstico 5 días
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row" id="forecast-container">
                        <div class="col text-center text-muted py-3">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-outline card-success card-modulo-main elevation-1">
                <x-modulo-index-header
                    titulo="Historial climático"
                    icono="fa-history"
                    :registros-text="'Últimos 30 días · '.$historial->total().' registros'"
                    filtros-target="#filtrosClimaPanel"
                />

                <div id="filtrosClimaPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','loteid','fecha_desde','fecha_hasta','temp_min','temp_max']) ? 'show' : '' }}">
                    <form method="GET" action="{{ route('climas.index') }}">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Buscar</label>
                                <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Observación, lote…">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted mb-1">Lote</label>
                                <select name="loteid" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    @foreach($lotesFiltro ?? [] as $l)
                                        <option value="{{ $l->loteid }}" @selected(request('loteid') == $l->loteid)>{{ $l->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted mb-1">Temp. mín.</label>
                                <input type="number" step="0.1" name="temp_min" class="form-control form-control-sm" value="{{ request('temp_min') }}" placeholder="°C">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted mb-1">Temp. máx.</label>
                                <input type="number" step="0.1" name="temp_max" class="form-control form-control-sm" value="{{ request('temp_max') }}" placeholder="°C">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-muted mb-1">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="small text-muted mb-1">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                            </div>
                            <div class="col-12">
                                <x-filtros-form-actions :limpiar-url="route('climas.index', ['filtros_abiertos' => 1])" />
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0" style="max-height: 520px; overflow-y: auto;">
                    @forelse($historial as $registro)
                    <div class="historial-item">
                        <div>
                            <div class="font-weight-bold text-dark">
                                <i class="fas fa-cloud text-muted mr-1"></i>
                                {{ $registro->fecha->format('d/m/Y') }}
                                <small class="text-muted font-weight-normal">({{ $registro->fecha->diffForHumans() }})</small>
                            </div>
                            <div class="text-muted small">
                                @if($registro->lote)
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $registro->lote->nombre }} ·
                                @endif
                                {{ $registro->observaciones ?? 'Sin descripción' }}
                            </div>
                        </div>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            <span class="dato-badge temp">
                                <i class="fas fa-thermometer-half mr-1"></i>{{ $registro->temperatura }}°C
                            </span>
                            <span class="dato-badge hum">
                                <i class="fas fa-tint mr-1"></i>{{ $registro->humedad }}%
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-cloud-sun fa-4x mb-3"></i>
                        <h5>Sin registros históricos</h5>
                        <p class="mb-0">El historial se irá llenando automáticamente cada día.</p>
                    </div>
                    @endforelse
                </div>
                @if($historial->hasPages())
                <div class="card-footer py-2">{{ $historial->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const WEATHER_DATA = @json($weatherData ?? ['actual' => null, 'pronostico' => [], 'error' => null]);
    const CARGAR_CLIMA_ASYNC = @json($cargarClimaAsync ?? false);
    const CLIMA_API_URL = @json(route('climas.datos-tiempo'));

    $(document).ready(function () {
        if (WEATHER_DATA.error && !WEATHER_DATA.actual) {
            mostrarErrorClima(WEATHER_DATA.error);
            return;
        }

        if (WEATHER_DATA.actual) {
            renderClima(WEATHER_DATA);
            if (CARGAR_CLIMA_ASYNC) {
                cargarClimaDesdeApi(false);
            }
            return;
        }

        if (CARGAR_CLIMA_ASYNC) {
            cargarClimaDesdeApi(false);
        } else {
            mostrarErrorClima('No hay datos climáticos disponibles.');
        }

        $('#btnRefreshClima').on('click', function () {
            const $icon = $(this).find('i');
            $icon.addClass('fa-spin');
            cargarClimaDesdeApi(true).finally(() => $icon.removeClass('fa-spin'));
        });
    });

    function renderClima(data) {
        mostrarClimaActual(data.actual);
        mostrarPronostico(data.pronostico || []);
        if (data.aviso) {
            $('#avisoClimaDinamico').html(
                `<div class="alert alert-info alert-dismissible fade show py-2 mb-3">
                    <i class="fas fa-info-circle mr-1"></i> ${data.aviso}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>`
            );
        }
    }

    function mostrarErrorClima(msg) {
        $('#weather-loading').show().html(`<i class="fas fa-exclamation-triangle fa-3x text-warning"></i><p class="mt-3 mb-0">${msg}</p>`);
        $('#weather-content').hide();
        $('#card-temp, #card-humidity, #card-wind, #card-pressure').text('--');
        $('#forecast-container').html('<div class="col text-center text-muted py-3">Sin datos de pronóstico.</div>');
    }

    function cargarClimaDesdeApi(refresh) {
        const url = CLIMA_API_URL + (refresh ? '?refresh=1' : '');
        return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.error && !data.actual) {
                    mostrarErrorClima(data.error);
                    return;
                }
                if (!data.actual) {
                    mostrarErrorClima('No hay datos climáticos disponibles.');
                    return;
                }
                $('#avisoClimaDinamico').empty();
                renderClima(data);
            })
            .catch(() => {
                if (!WEATHER_DATA.actual) {
                    mostrarErrorClima('Error de conexión al cargar el clima.');
                }
            });
    }

    function mostrarClimaActual(data) {
        $('#weather-loading').hide();
        $('#weather-content').show();

        const temp = Math.round(data.temperatura);
        $('#weather-temp').text(temp + '°C');
        $('#weather-desc').text(data.descripcion || '--');
        $('#weather-icon').attr('src', `https://openweathermap.org/img/wn/${data.icono || '01d'}@4x.png`);
        $('#weather-location').text(`${data.ciudad || 'Santa Cruz de la Sierra'}, ${data.pais || 'BO'}`);

        const fuente = data.fuente === 'registro_local'
            ? (data.registrado_el ? `Último registro: ${data.registrado_el}` : 'Datos del historial Fusion')
            : (data.fuente === 'openweather' ? 'OpenWeather · tiempo real' : '');
        $('#weather-fuente').text(fuente);

        $('#detail-humidity').text((data.humedad ?? '--') + '%');
        $('#detail-wind').text((data.viento_kmh ?? '--') + ' km/h');
        $('#detail-pressure').text((data.presion ?? '--') + ' hPa');

        $('#card-temp').text(temp + '°C');
        $('#card-humidity').text((data.humedad ?? '--') + '%');
        $('#card-wind').text((data.viento_kmh ?? '--') + ' km/h');
        $('#card-pressure').text((data.presion ?? '--') + ' hPa');

        $('#sunrise').text(data.amanecer || '--:--');
        $('#sunset').text(data.atardecer || '--:--');

        if (data.es_noche) {
            $('#weather-widget').addClass('night');
        } else {
            $('#weather-widget').removeClass('night');
        }
    }

    function mostrarPronostico(data) {
        if (!data.length) {
            $('#forecast-container').html('<div class="col text-center text-muted py-3">Sin pronóstico disponible por ahora.</div>');
            return;
        }

        let html = '';
        data.forEach(item => {
            html += `
                <div class="col forecast-day">
                    <div class="font-weight-bold text-dark">${item.dia || '--'}</div>
                    <div class="small text-muted mb-1">${item.fecha || '--/--'}</div>
                    <div class="mb-1">
                        <img src="https://openweathermap.org/img/wn/${item.icono || '01d'}@2x.png" alt="" width="50" height="50">
                    </div>
                    <div class="day-temp">${item.temperatura ?? '--'}°C</div>
                    <div class="small text-muted text-capitalize">${item.descripcion || '--'}</div>
                </div>
            `;
        });

        $('#forecast-container').html(html);
    }
</script>
@endpush
