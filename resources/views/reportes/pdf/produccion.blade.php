@extends('reportes.pdf.layout')

@section('content')
    <div class="report-title">
        <h2>Reporte de Producción Agrícola</h2>
        <p>
            Cosechas registradas del {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        </p>
    </div>

    @php
        $totalCosechado = $datos->sum('cantidad');
        $numCosechas = $datos->count();
        $lotesUnicos = $datos->unique('loteid')->count();
    @endphp

    <table class="summary-cards">
        <tr>
            <td class="card">
                <span class="label">Total Cosechado</span>
                <span class="value">{{ number_format($totalCosechado, 2) }} <small style="font-size:10px">Unidades
                        Mixtas</small></span>
            </td>
            <td class="card">
                <span class="label">Eventos de Cosecha</span>
                <span class="value">{{ $numCosechas }}</span>
            </td>
            <td class="card">
                <span class="label">Lotes Activos</span>
                <span class="value">{{ $lotesUnicos }}</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%">Fecha</th>
                <th style="width: 25%">Cultivo</th>
                <th style="width: 25%">Lote Origen</th>
                <th style="width: 20%" class="numeric">Cantidad Cosechada</th>
                <th style="width: 15%">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datos as $prod)
                <tr>
                    <td>{{ $prod->fechacosecha instanceof \Carbon\Carbon ? $prod->fechacosecha->format('d/m/Y') : $prod->fechacosecha }}
                    </td>
                    <td>
                        <b>{{ $prod->lote->cultivo->nombre ?? 'Desconocido' }}</b>
                    </td>
                    <td>{{ $prod->lote->nombre ?? '-' }}</td>
                    <td class="numeric">
                        <span
                            style="font-weight:bold; color:#2c5530; font-size:12px;">{{ number_format($prod->cantidad, 2) }}</span>
                        <small style="color:#555;">{{ $prod->unidadMedida->abreviatura ?? '' }}</small>
                    </td>
                    <td>
                        <span style="font-size:9px; color:#666;">{{ Str::limit($prod->observaciones ?? '-', 50) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection