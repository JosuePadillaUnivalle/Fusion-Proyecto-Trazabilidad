@php
    $resumen = $capacidadResumen ?? [];
    $tiposTransporte = $resumen['tipos_transporte'] ?? [];
    $genericos = $resumen['empaques_genericos'] ?? [];
    $porCalibre = $resumen['empaques_por_calibre'] ?? [];
    $cap = $resumen['capacidad'] ?? [];
    $dims = $resumen['dimensiones'] ?? [];
@endphp

<div class="row">
    <div class="col-lg-5 mb-3">
        @include('envios.vehiculos.partials.caja-3d', [
            'capacidadResumen' => $resumen,
            'vehiculo' => $vehiculo,
        ])
    </div>
    <div class="col-lg-7 mb-3">
        <div class="card veh-det-panel veh-det-panel--equipamiento h-100">
            <div class="card-header">
                <i class="fas fa-shipping-fast mr-1 text-success"></i> Equipamiento de transporte
            </div>
            <div class="card-body">
                @include('envios.vehiculos.partials.equipamiento-transporte', [
                    'tiposTransporte' => $tiposTransporte,
                    'vehiculo' => $vehiculo,
                    'cap' => $cap,
                    'dims' => $dims,
                ])
            </div>
        </div>
    </div>
</div>

<div class="card veh-det-panel mb-3">
    <div class="card-header">
        <i class="fas fa-boxes mr-1 text-success"></i>
        Capacidad por tipo de empaque
        <span class="text-muted small font-weight-normal ml-1">— min(peso, volumen)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 veh-empaque-cap">
                <thead class="thead-light">
                    <tr>
                        <th>Empaque</th>
                        <th class="text-right">Peso/caja</th>
                        <th class="text-right">Vol./caja</th>
                        <th class="text-center">Máx. cajas</th>
                        <th class="text-center">Restringido por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($genericos as $fila)
                    <tr>
                        <td>
                            <strong>{{ $fila['empaque_nombre'] }}</strong>
                            @if($fila['nota'])
                                <br><span class="small text-muted">{{ $fila['nota'] }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            {{ $fila['peso_bruto_kg'] !== null ? number_format($fila['peso_bruto_kg'], 2).' kg' : '—' }}
                        </td>
                        <td class="text-right">
                            {{ $fila['volumen_m3'] !== null ? number_format($fila['volumen_m3'], 3).' m³' : '—' }}
                        </td>
                        <td class="text-center">
                            @if($fila['max_efectivo'] !== null)
                                <span class="badge badge-primary badge-lg">{{ number_format($fila['max_efectivo']) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center">
                            @include('envios.vehiculos.partials._limite-por', ['limite' => $fila['limite_por']])
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No hay tipos de empaque activos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2 border-top small text-muted">
            <strong>Restringido por:</strong> indica si el tope lo marca el <em>peso máximo</em> del vehículo o el <em>espacio disponible</em> (m³ útil).
        </div>
    </div>
</div>

@if(count($porCalibre) > 0)
<div class="card veh-det-panel mb-3">
    <div class="card-header">
        <i class="fas fa-seedling mr-1 text-success"></i>
        Por producto y calibre
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 veh-empaque-cap">
                <thead class="thead-light">
                    <tr>
                        <th>Producto / calibre</th>
                        <th>Empaque</th>
                        <th class="text-right">Peso bruto/caja</th>
                        <th class="text-center">Máx. cajas</th>
                        <th class="text-center">Restringido por</th>
                        <th class="text-center">Frío</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($porCalibre as $fila)
                    <tr @if($fila['advertencia_termica']) class="table-warning" @endif>
                        <td>
                            <strong>{{ $fila['producto_nombre'] ?? '—' }}</strong>
                            <br><span class="small text-muted">{{ $fila['calibre_nombre'] }}</span>
                        </td>
                        <td>{{ $fila['empaque_nombre'] }}</td>
                        <td class="text-right">{{ number_format($fila['peso_bruto_kg'], 2) }} kg</td>
                        <td class="text-center">
                            <span class="badge badge-success badge-lg">{{ number_format($fila['max_efectivo']) }}</span>
                        </td>
                        <td class="text-center">
                            @include('envios.vehiculos.partials._limite-por', ['limite' => $fila['limite_por']])
                        </td>
                        <td class="text-center small">
                            @if($fila['advertencia_termica'])
                                <span class="text-warning" title="{{ $fila['advertencia_termica'] }}">
                                    <i class="fas fa-exclamation-triangle"></i> Requiere frío
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2 border-top small text-muted">
            <i class="fas fa-snowflake text-warning mr-1"></i> Fila en amarillo: producto que suele requerir frío y el vehículo no incluye transporte refrigerado.
        </div>
    </div>
</div>
@endif
