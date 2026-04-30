@extends('layouts.app')

@section('title', 'Reporte Climático | AgroNexus')
@section('page_title', 'Reporte Climático')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" style="color: #2c5530;">Reportes</a></li>
    <li class="breadcrumb-item active">Climático</li>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #2c5530;
            --climate-blue: #17a2b8;
            --climate-orange: #fd7e14;
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

        .small-box-yellow {
            background: linear-gradient(135deg, #ffc107, #ffca2c) !important;
        }

        .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
        }

        .small-box-green {
            background: linear-gradient(135deg, #28a745, #34ce57) !important;
        }

        .small-box-red {
            background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            font-weight: 600;
            padding: 15px 20px;
        }

        .chart-container {
            height: 300px;
        }

        /* Widget clima */
        .weather-current {
            background: linear-gradient(135deg, #17a2b8, #6dd5ed);
            border-radius: 15px;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .weather-current .temp-main {
            font-size: 3.5rem;
            font-weight: 300;
            line-height: 1;
        }

        .weather-current .weather-desc {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 10px;
            text-transform: capitalize;
        }

        .weather-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 20px;
        }

        .weather-detail-box {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .weather-detail-box i {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .weather-detail-box .value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .weather-detail-box .label {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        /* Pronóstico */
        .forecast-mini {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .forecast-mini:hover {
            background: #e8f4f8;
        }

        .forecast-mini .day {
            font-weight: 600;
            font-size: 0.85rem;
        }

        .forecast-mini .temp {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--climate-blue);
        }

        /* Estadísticas */
        .stat-clima {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--climate-blue);
        }

        .stat-clima h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-clima.temp {
            border-left-color: #fd7e14;
        }

        .stat-clima.temp h3 {
            color: #fd7e14;
        }

        .stat-clima.hum {
            border-left-color: #17a2b8;
        }

        .stat-clima.hum h3 {
            color: #17a2b8;
        }

        .stat-clima.viento {
            border-left-color: #28a745;
        }

        .stat-clima.viento h3 {
            color: #28a745;
        }

        /* Historial */
        .historial-row {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .historial-row:last-child {
            border-bottom: none;
        }

        .historial-row .fecha {
            width: 120px;
            font-weight: 600;
        }

        .historial-row .icono {
            width: 50px;
            text-align: center;
        }

        .historial-row .desc {
            flex: 1;
            color: #6c757d;
            text-transform: capitalize;
        }

        .historial-row .datos {
            display: flex;
            gap: 10px;
        }

        .filter-card {
            background: #f8f9fc;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="{{ route('reportes.climatico') }}" class="row align-items-end">
            <div class="col-md-4 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Período</label>
                <select name="dias" class="form-control">
                    <option value="7" {{ $dias == 7 ? 'selected' : '' }}>Últimos 7 días</option>
                    <option value="15" {{ $dias == 15 ? 'selected' : '' }}>Últimos 15 días</option>
                    <option value="30" {{ $dias == 30 ? 'selected' : '' }}>Últimos 30 días</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <button type="submit" class="btn btn-info btn-block">
                    <i class="fas fa-filter mr-1"></i>Filtrar
                </button>
            </div>
            <div class="col-md-4 mb-2">
                <a href="{{ route('climas.index') }}" class="btn btn-outline-success btn-block">
                    <i class="fas fa-cloud-sun mr-1"></i>Ver Clima Actual
                </a>
            </div>
        </form>
    </div>

    <div class="row">
        <!-- Clima actual -->
        <div class="col-lg-4">
            <div class="weather-current mb-4" id="weather-current">
                <div id="weather-loading" class="py-3">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2 mb-0">Cargando...</p>
                </div>
                <div id="weather-content" style="display: none;">
                    <img id="weather-icon" src="" alt="" style="width: 80px;">
                    <div class="temp-main" id="weather-temp">--°C</div>
                    <div class="weather-desc" id="weather-desc">--</div>
                    <small><i class="fas fa-map-marker-alt mr-1"></i>Santa Cruz, Bolivia</small>

                    <div class="weather-details-grid">
                        <div class="weather-detail-box">
                            <i class="fas fa-tint"></i>
                            <div class="value" id="w-humidity">--%</div>
                            <div class="label">Humedad</div>
                        </div>
                        <div class="weather-detail-box">
                            <i class="fas fa-wind"></i>
                            <div class="value" id="w-wind">-- km/h</div>
                            <div class="label">Viento</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pronóstico -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt mr-2"></i>Pronóstico
                </div>
                <div class="card-body">
                    <div class="row" id="forecast-mini">
                        <div class="col text-center py-3">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas y gráfico -->
        <div class="col-lg-8">
            <!-- Promedios del período -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-clima temp">
                        <h3>{{ number_format($promedios['temperatura'], 1) }}°C</h3>
                        <p class="text-muted mb-0"><i class="fas fa-thermometer-half mr-1"></i>Temp. Promedio</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-clima hum">
                        <h3>{{ number_format($promedios['humedad'], 1) }}%</h3>
                        <p class="text-muted mb-0"><i class="fas fa-tint mr-1"></i>Humedad Promedio</p>
                    </div>
                </div>
                <!-- Se elimina la sección de Viento Promedio ya que no existe en BD -->
                <!-- 
                    <div class="col-md-4">
                        <div class="stat-clima viento">
                            <h3>{{ number_format($promedios['viento'] ?? 0, 1) }} km/h</h3>
                            <p class="text-muted mb-0"><i class="fas fa-wind mr-1"></i>Viento Promedio</p>
                        </div>
                    </div>
                    -->
            </div>

            <!-- Gráfico -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line mr-2"></i>Tendencias Climáticas ({{ $dias }} días)
                </div>
                <div class="card-body">
                    @if($datosGrafico->count() > 0)
                        <div class="chart-container">
                            <canvas id="climaChart"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <p>No hay datos suficientes para mostrar el gráfico</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history mr-2"></i>Historial de Registros</span>
                    <span class="badge badge-info">{{ $historialClima->count() }} registros</span>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    @forelse($historialClima as $registro)
                        <div class="historial-row">
                            <div class="fecha">{{ $registro->fecha->format('d/m/Y') }}</div>
                            <div class="icono">
                                <i class="fas fa-cloud text-muted"></i>
                            </div>
                            <div class="desc">{{ $registro->observaciones ?? '-' }}</div>
                            <div class="datos">
                                <span class="badge badge-warning">{{ $registro->temperatura }}°C</span>
                                <span class="badge badge-info">{{ $registro->humedad }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-cloud-sun fa-3x mb-3"></i>
                            <p>No hay registros en el período seleccionado</p>
                            <small>El historial se guarda automáticamente cada día</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        const API_KEY = '{{ env("OPENWEATHER_API_KEY", "") }}';

        $(document).ready(function () {
            if (API_KEY) {
                cargarClimaActual();
                cargarPronostico();
            } else {
                $('#weather-loading').html('<p class="text-warning">API no configurada</p>');
            }

            @if($datosGrafico->count() > 0)
                crearGrafico();
            @endif
        });

        function cargarClimaActual() {
            $.ajax({
                url: `https://api.openweathermap.org/data/2.5/weather?q=Santa Cruz de la Sierra,BO&appid=${API_KEY}&units=metric&lang=es`,
                timeout: 10000,
                success: function (data) {
                    $('#weather-loading').hide();
                    $('#weather-content').show();
                    $('#weather-temp').text(Math.round(data.main.temp) + '°C');
                    $('#weather-desc').text(data.weather[0].description);
                    $('#weather-icon').attr('src', `https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png`);
                    $('#w-humidity').text(data.main.humidity + '%');
                    $('#w-wind').text(Math.round(data.wind.speed * 3.6) + ' km/h');
                },
                error: function () {
                    $('#weather-loading').html('<p class="text-danger">Error de conexión</p>');
                }
            });
        }

        function cargarPronostico() {
            $.ajax({
                url: `https://api.openweathermap.org/data/2.5/forecast?q=Santa Cruz de la Sierra,BO&appid=${API_KEY}&units=metric&lang=es`,
                timeout: 10000,
                success: function (data) {
                    const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    let pronosticoDias = {};

                    data.list.forEach(item => {
                        const fecha = new Date(item.dt * 1000);
                        const key = fecha.toDateString();
                        if (!pronosticoDias[key]) pronosticoDias[key] = item;
                    });

                    let html = '';
                    Object.values(pronosticoDias).slice(0, 5).forEach(item => {
                        const fecha = new Date(item.dt * 1000);
                        html += `
                            <div class="col">
                                <div class="forecast-mini">
                                    <div class="day">${dias[fecha.getDay()]}</div>
                                    <img src="https://openweathermap.org/img/wn/${item.weather[0].icon}.png" style="width: 40px;">
                                    <div class="temp">${Math.round(item.main.temp)}°</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#forecast-mini').html(html);
                }
            });
        }

        @if($datosGrafico->count() > 0)
            function crearGrafico() {
                const datos = @json($datosGrafico);

                new Chart(document.getElementById('climaChart'), {
                    type: 'line',
                    data: {
                        labels: datos.map(d => d.dia),
                        datasets: [{
                            label: 'Temperatura (°C)',
                            data: datos.map(d => parseFloat(d.temp)),
                            borderColor: '#fd7e14',
                            backgroundColor: 'rgba(253, 126, 20, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        }, {
                            label: 'Humedad (%)',
                            data: datos.map(d => parseFloat(d.hum)),
                            borderColor: '#17a2b8',
                            backgroundColor: 'rgba(23, 162, 184, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: {
                                type: 'linear',
                                position: 'left',
                                title: { display: true, text: 'Temperatura (°C)' },
                                grid: { color: '#f1f3f4' }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                title: { display: true, text: 'Humedad (%)' },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            }
        @endif
    </script>
@endpush