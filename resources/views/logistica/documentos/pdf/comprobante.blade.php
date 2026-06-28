<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $documento->titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 24px 28px; }
        .header { border-bottom: 3px solid #2c5530; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 20px; font-weight: bold; color: #2c5530; }
        .subbrand { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .tipo-badge { display: inline-block; background: #e8f5e9; color: #1b5e20; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-top: 8px; }
        h1 { font-size: 16px; color: #111827; margin: 0 0 4px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 16px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { padding: 7px 10px; vertical-align: top; border: 1px solid #e5e7eb; }
        .grid .label { width: 28%; background: #f9fafb; font-weight: bold; color: #374151; }
        table.productos { width: 100%; border-collapse: collapse; margin: 12px 0 18px; }
        table.productos th { background: #2c5530; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; }
        table.productos td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        table.productos tr:nth-child(even) td { background: #f9fafb; }
        .firmas { margin-top: 28px; }
        .firmas td { width: 50%; padding-top: 36px; text-align: center; border-top: 1px solid #9ca3af; }
        .nota { margin-top: 16px; padding: 10px 12px; background: #f0fdf4; border-left: 4px solid #2c5530; font-size: 10px; }
        .nota--alerta { background: #fef2f2; border-left-color: #dc2626; color: #991b1b; }
        .nota--alerta strong { color: #b91c1c; }
        .nota--personal { background: #fff7ed; border-left-color: #ea580c; color: #9a3412; }
        .nota--personal strong { color: #c2410c; }
        .cond-no { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 9px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">AgroFusion</div>
        <div class="subbrand">Sistema de gestión agrícola y logística</div>
        <div class="tipo-badge">{{ $tipoEtiqueta }}</div>
    </div>

    <h1>{{ $documento->titulo }}</h1>
    <div class="meta">
        Documento Nº {{ $documento->documentoentregaid }}
        · Emitido {{ optional($documento->created_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}
    </div>

    <table class="grid">
        <tr>
            <td class="label">Código de envío</td>
            <td>{{ $documento->externo_envio_id ?? '—' }}</td>
            <td class="label">Pedido</td>
            <td>{{ $pedidoReferencia ?? $pedido?->numero_solicitud ?? ($documento->pedidoid ? '#'.$documento->pedidoid : '—') }}</td>
        </tr>
        <tr>
            <td class="label">Destino / cliente</td>
            <td>{{ $destinoCliente ?? '—' }}</td>
            <td class="label">Dirección de entrega</td>
            <td>{{ $direccionEntrega ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Transportista</td>
            <td>{{ $transportistaNombre }}</td>
            <td class="label">Vehículo</td>
            <td>{{ $vehiculoRef ?? $envio?->vehiculo_ref ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Almacén origen</td>
            <td>{{ $almacenOrigenNombre ?? $documento->almacen?->nombre ?? $envio?->almacen?->nombre ?? '—' }}</td>
            <td class="label">Estado del vehículo</td>
            <td>{{ $estadoVehiculo ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Estado del envío</td>
            <td>{{ $estadoEnvio }}</td>
            <td class="label">Cargado por</td>
            <td>{{ $cargadoPor ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Ruta logística</td>
            <td colspan="3">{{ $rutaLogistica ?? '—' }}</td>
        </tr>
    </table>

    <strong>Detalle de productos</strong>
    <table class="productos">
        <thead>
            <tr>
                <th>Producto</th>
                <th style="width:16%">Cantidad</th>
                <th style="width:22%">Empaquetaje</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lineasProducto as $linea)
                <tr>
                    <td>{{ $linea['producto'] }}</td>
                    <td>{{ $linea['cantidad'] }}</td>
                    <td>{{ $linea['empaquetaje'] ?? '—' }}</td>
                    <td>{{ $linea['observaciones'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#6b7280;">Sin detalle de productos registrado para este envío.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="nota">
        <strong>Observaciones del documento:</strong>
        {{ $textoObservaciones }}
    </div>

    @if(!empty($condicionesLineas))
    <strong style="display:block;margin-top:16px;">Registro de condiciones de transporte</strong>
    <table class="productos">
        <thead>
            <tr><th>Condición</th><th style="width:15%">Estado</th></tr>
        </thead>
        <tbody>
            @foreach($condicionesLineas as $fila)
            <tr>
                <td>{{ $fila['titulo'] }}</td>
                <td @if($fila['valor'] === 'No') class="cond-no" @endif>{{ $fila['valor'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(!empty($observacionCondiciones['texto'] ?? null))
    <div class="nota @if($observacionCondiciones['alerta'] ?? false) nota--alerta @endif">
        <strong>Observación condiciones:</strong> {{ $observacionCondiciones['texto'] }}
    </div>
    @endif
    @if(!empty($observacionPersonalCondiciones))
    <div class="nota nota--personal">
        <strong>Observación personal:</strong> {{ $observacionPersonalCondiciones }}
    </div>
    @endif
    @endif

    @if(!empty($incidentesLineas))
    <strong style="display:block;margin-top:16px;">Registro de incidentes de transporte</strong>
    <table class="productos">
        <thead>
            <tr><th>Incidente</th><th style="width:15%">Ocurrió</th></tr>
        </thead>
        <tbody>
            @foreach($incidentesLineas as $fila)
            <tr>
                <td>{{ $fila['titulo'] }}</td>
                <td @if($fila['ocurrio'] === 'Sí') class="cond-no" @endif>{{ $fila['ocurrio'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(!empty($observacionIncidentes['texto'] ?? null))
    <div class="nota @if($observacionIncidentes['alerta'] ?? false) nota--alerta @endif">
        <strong>Observación incidentes:</strong> {{ $observacionIncidentes['texto'] }}
    </div>
    @endif
    @if(!empty($observacionPersonalIncidentes))
    <div class="nota nota--personal">
        <strong>Observación personal:</strong> {{ $observacionPersonalIncidentes }}
    </div>
    @endif
    @endif

    <table class="firmas" width="100%">
        <tr>
            <td>
                @if(!empty($firmaTransportistaImg))
                    <img src="{{ $firmaTransportistaImg }}" alt="Firma transportista" style="max-height:60px;max-width:180px;">
                @elseif(!empty($transportistaNombre))
                    <em>Firmado digitalmente</em>
                @endif
                <br>Firma del transportista<br><small>{{ $transportistaNombre }}</small>
            </td>
            <td>
                @if(!empty($firmaRecepcionImg))
                    <img src="{{ $firmaRecepcionImg }}" alt="Firma recepción" style="max-height:60px;max-width:180px;">
                @elseif(!empty($recepcionNombre))
                    <em>Firmado digitalmente</em>
                @endif
                <br>{{ $firmaRecepcionEtiqueta ?? 'Firma recepción en destino' }}<br><small>{{ $recepcionNombre ?? $destinoCliente ?? '—' }}</small>
            </td>
        </tr>
    </table>

    <div class="footer">
        Comprobante generado por AgroFusion · Trazabilidad agrícola y logística · {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
