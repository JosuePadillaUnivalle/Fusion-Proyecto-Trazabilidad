@extends('reportes.pdf.layout')

@section('content')
    <div class="report-title">
        <h2>Reporte de Actividades de Campo</h2>
        <p>
            Desde {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
            hasta {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        </p>
    </div>

    @php
        $total = $datos->count();
        $completadas = $datos->whereNotNull('fechafin')->count();
        $pendientes = $datos->whereNull('fechafin')->count();
    @endphp

    <table class="summary-cards">
        <tr>
            <td class="card">
                <span class="label">Total Actividades</span>
                <span class="value">{{ $total }}</span>
            </td>
            <td class="card">
                <span class="label">Completadas</span>
                <span class="value" style="color:#28a745">{{ $completadas }}</span>
            </td>
            <td class="card">
                <span class="label">Pendientes</span>
                <span class="value" style="color:#e67e22">{{ $pendientes }}</span>
            </td>
            <td class="card">
                <span class="label">Eficiencia</span>
                <span class="value">{{ $total > 0 ? round(($completadas / $total) * 100) : 0 }}%</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%">Inicio</th>
                <th style="width: 20%">Actividad / Tipo</th>
                <th style="width: 20%">Lote / Ubicación</th>
                <th style="width: 20%">Responsable</th>
                <th style="width: 15%">Estado</th>
                <th style="width: 10%">Fin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $act)
                @php
                    $isPending = is_null($act->fechafin);
                @endphp
                <tr>
                    <td>{{ $act->fechainicio instanceof \Carbon\Carbon ? $act->fechainicio->format('d/m/Y') : $act->fechainicio }}
                    </td>
                    <td>
                        <b>{{ $act->tipoActividad->nombre ?? 'General' }}</b><br>
                        <i style="font-size:9px; color:#555">{{ Str::limit($act->descripcion, 30) }}</i>
                    </td>
                    <td>{{ $act->lote->nombre ?? '-' }}</td>
                    <td>
                        <span style="font-size:10px">{{ $act->usuario->nombre ?? 'Sin asignar' }}</span>
                    </td>
                    <td>
                        @if($isPending)
                            <span style="color:#e67e22; font-weight:bold; font-size:10px;">PENDIENTE</span>
                        @else
                            <span style="color:#28a745; font-weight:bold; font-size:10px;">COMPLETADO</span>
                        @endif
                    </td>
                    <td>
                        {{ $act->fechafin ? (\Carbon\Carbon::parse($act->fechafin)->format('d/m/Y')) : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection