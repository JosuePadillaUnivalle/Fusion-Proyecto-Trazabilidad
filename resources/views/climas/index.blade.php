@extends('layouts.app')

@section('title', 'Clima | AgroNexus')
@section('page_title', 'Información Climática')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Clima</li>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
        }

        /* Cards estilo dashboard */
        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-2px);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-green {
            background: linear-gradient(135deg, #28a745, #34ce57) !important;
        }

        .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
        }

        .small-box-yellow {
            background: linear-gradient(135deg, #ffc107, #ffca2c) !important;
        }

        .small-box-red {
            background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
        }

        /* Widget clima principal */
        .weather-widget {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .weather-widget.night {
            background: linear-gradient(135deg, #2d3436, #636e72);
        }

        .weather-temp {
            font-size: 72px;
            font-weight: 300;
            line-height: 1;
        }

        .weather-icon img {
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        .weather-desc {
            font-size: 1.3rem;
            text-transform: capitalize;
            opacity: 0.95;
        }

        .weather-details {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .weather-detail-item {
            text-align: center;
        }

        .weather-detail-item i {
            font-size: 1.5rem;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .weather-detail-item .value {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .weather-detail-item .label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Pronóstico */
        .forecast-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .forecast-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f4;
        }

        .forecast-day {
            text-align: center;
            padding: 15px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .forecast-day:hover {
            background: #f8f9fc;
        }

        .forecast-day .day-name {
            font-weight: 600;
            color: #1a252f;
            margin-bottom: 5px;
        }

        .forecast-day .day-date {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .forecast-day .day-icon img {
            width: 50px;
            height: 50px;
        }

        .forecast-day .day-temp {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .forecast-day .day-desc {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: capitalize;
        }

        /* Historial */
        .historial-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .historial-card .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            font-weight: 600;
            padding: 15px 20px;
        }

        .historial-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            transition: background 0.2s;
        }

        .historial-item:hover {
            background: #f8f9fc;
        }

        .historial-item:last-child {
            border-bottom: none;
        }

        .historial-item .fecha {
            font-weight: 600;
            color: #1a252f;
        }

        .historial-item .descripcion {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: capitalize;
        }

        .historial-item .datos {
            display: flex;
            gap: 10px;
        }

        .dato-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .dato-badge.temp {
            background: #fff3cd;
            color: #856404;
        }

        .dato-badge.hum {
            background: #d1ecf1;
            color: #0c5460;
        }

        .dato-badge.viento {
            background: #d4edda;
            color: #155724;
        }

        .sun-card {
            background: linear-gradient(135deg, #f39c12, #f1c40f);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .sun-times {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }

        .sun-time {
            text-align: center;
        }

        .sun-time i {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
        }

        .empty-historial {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    @if(!empty($weatherData['error']))
        <div class="alert alert-warning">
            {{ $weatherData['error'] }}
        </div>
    @endif

    <!-- Cards de resumen -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3 id="card-temp"><i class="fas fa-spinner fa-spin"></i></h3>
                    <p>Temperatura</p>
                </div>
                <div class="icon"><i class="fas fa-thermometer-half"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3 id="card-humidity"><i class="fas fa-spinner fa-spin"></i></h3>
                    <p>Humedad</p>
                </div>
                <div class="icon"><i class="fas fa-tint"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3 id="card-wind"><i class="fas fa-spinner fa-spin"></i></h3>
                    <p>Viento</p>
                </div>
                <div class="icon"><i class="fas fa-wind"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3 id="card-pressure"><i class="fas fa-spinner fa-spin"></i></h3>
                    <p>Presión</p>
                </div>
                <div class="icon"><i class="fas fa-tachometer-alt"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Widget principal y pronóstico -->
        <div class="col-lg-5">
            <div class="weather-widget" id="weather-widget">
                <div class="loading-spinner" id="weather-loading">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-3">Cargando datos del clima...</p>
                </div>
                <div id="weather-content" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="weather-temp" id="weather-temp">--°C</div>
                            <div class="weather-desc" id="weather-desc">--</div>
                        </div>
                        <div class="weather-icon">
                            <img id="weather-icon" src="" alt="Clima">
                        </div>
                    </div>
                    <div class="mt-3">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span id="weather-location">Santa Cruz de la Sierra, Bolivia</span>
                    </div>
                    <div class="weather-details">
                        <div class="weather-detail-item">
                            <i class="fas fa-tint"></i>
                            <div class="value" id="detail-humidity">--%</div>
                            <div class="label">Humedad</div>
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-wind"></i>
                            <div class="value" id="detail-wind">-- km/h</div>
                            <div class="label">Viento</div>
                        </div>
                        <div class="weather-detail-item">
                            <i class="fas fa-compress-arrows-alt"></i>
                            <div class="value" id="detail-pressure">-- hPa</div>
                            <div class="label">Presión</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sol -->
            <div class="sun-card">
                <h6 class="mb-0"><i class="fas fa-sun mr-2"></i>Amanecer y Atardecer</h6>
                <div class="sun-times">
                    <div class="sun-time">
                        <i class="fas fa-sunrise"></i>
                        <div id="sunrise">--:--</div>
                        <small>Amanecer</small>
                    </div>
                    <div class="sun-time">
                        <i class="fas fa-moon"></i>
                        <div id="sunset">--:--</div>
                        <small>Atardecer</small>
                    </div>
                </div>
            </div>

            <!-- Pronóstico 5 días -->
            <div class="forecast-card">
                <h5><i class="fas fa-calendar-alt mr-2"></i>Pronóstico 5 Días</h5>
                <div class="row" id="forecast-container">
                    <div class="col text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial -->
        <div class="col-lg-7">
            <div class="historial-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history mr-2"></i>Historial Climático (Últimos 30 días)</span>
                    <span class="badge badge-info">{{ $historial->count() }} registros</span>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    @forelse($historial as $registro)
                        <div class="historial-item">
                            <div>
                                <div class="fecha">
                                    <i class="fas fa-cloud text-muted mr-2"></i>
                                    {{ $registro->fecha->format('d/m/Y') }}
                                    <small class="text-muted">({{ $registro->fecha->diffForHumans() }})</small>
                                </div>
                                <div class="descripcion">{{ $registro->observaciones ?? 'Sin descripción' }}</div>
                            </div>
                            <div class="datos">
                                <span class="dato-badge temp">
                                    <i class="fas fa-thermometer-half mr-1"></i>{{ $registro->temperatura }}°C
                                </span>
                                <span class="dato-badge hum">
                                    <i class="fas fa-tint mr-1"></i>{{ $registro->humedad }}%
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-historial">
                            <i class="fas fa-cloud-sun fa-4x mb-3 text-muted"></i>
                            <h5>Sin registros históricos</h5>
                            <p class="text-muted">El historial se irá llenando automáticamente cada día.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const WEATHER_DATA = @json($weatherData ?? ['actual' => null, 'pronostico' => [], 'error' => null]);

        $(document).ready(function () {
            if (WEATHER_DATA.error) {
                $('#weather-loading').html(`<i class="fas fa-exclamation-triangle fa-3x text-warning"></i><p class="mt-3">${WEATHER_DATA.error}</p>`);
                $('#card-temp, #card-humidity, #card-wind, #card-pressure').text('--');
                $('#forecast-container').html('<div class="col text-center text-muted">Sin datos de pronóstico.</div>');
                return;
            }

            if (!WEATHER_DATA.actual) {
                $('#weather-loading').html('<i class="fas fa-exclamation-triangle fa-3x text-danger"></i><p class="mt-3">No se pudo obtener la información climática en este momento.</p>');
                $('#card-temp, #card-humidity, #card-wind, #card-pressure').text('--');
                $('#forecast-container').html('<div class="col text-center text-muted">Sin datos de pronóstico.</div>');
                return;
            }

            mostrarClimaActual(WEATHER_DATA.actual);
            mostrarPronostico(WEATHER_DATA.pronostico || []);
        });

        function mostrarClimaActual(data) {
            $('#weather-loading').hide();
            $('#weather-content').show();

            const temp = Math.round(data.temperatura);
            $('#weather-temp').text(temp + '°C');
            $('#weather-desc').text(data.descripcion || '--');
            $('#weather-icon').attr('src', `https://openweathermap.org/img/wn/${data.icono || '01d'}@4x.png`);
            $('#weather-location').text(`${data.ciudad || 'Santa Cruz'}, ${data.pais || 'BO'}`);

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
                $('#forecast-container').html('<div class="col text-center text-muted">No se pudo obtener la información climática en este momento.</div>');
                return;
            }

            let html = '';
            data.forEach(item => {
                html += `
                <div class="col forecast-day">
                    <div class="day-name">${item.dia || '--'}</div>
                    <div class="day-date">${item.fecha || '--/--'}</div>
                    <div class="day-icon">
                        <img src="https://openweathermap.org/img/wn/${item.icono || '01d'}@2x.png" alt="">
                    </div>
                    <div class="day-temp">${item.temperatura ?? '--'}°C</div>
                    <div class="day-desc">${item.descripcion || '--'}</div>
                </div>
            `;
            });

            $('#forecast-container').html(html);
        }
    </script>
@endpush