@php
    $tipoTema = match ($item['tipo'] ?? '') {
        'agricola' => ['accent' => '#2c5530', 'icon' => 'fa-seedling'],
        'traslado_planta_mayorista' => ['accent' => '#5b21b6', 'icon' => 'fa-store'],
        default => ['accent' => '#1d4ed8', 'icon' => 'fa-route'],
    };
    $estadoMod = str_replace('pedido-estado-', '', $item['estado_badge']['clase'] ?? 'agricola');
    $tieneTrayecto = $item['trayecto_partes']
        && (($item['trayecto_partes']['recogidas'] ?? []) !== [] || ($item['trayecto_partes']['destino'] ?? null));
@endphp
<div class="col-xl-6 col-lg-12 mb-3">
    <article class="envio-lista-card{{ (($item['destacar_pendiente'] ?? false) || ($item['pendiente_salida'] ?? false)) ? ' envio-lista-card--pendiente' : '' }}" style="--env-accent: {{ $tipoTema['accent'] }};">
        <div class="envio-lista-card__head">
            <span class="envio-lista-card__tipo">
                <i class="fas {{ $tipoTema['icon'] }}"></i>
                {{ $item['tipo_etiqueta'] }}
            </span>
            <div class="envio-lista-card__head-meta">
                <span class="envio-lista-estado envio-lista-estado--{{ $estadoMod }}"
                      title="{{ $item['estado_badge']['titulo'] ?? $item['estado_badge']['etiqueta'] }}">
                    {{ $item['estado_badge']['etiqueta'] }}
                </span>
                <span class="envio-lista-card__fecha">{{ $item['fecha']?->format('d/m/Y') ?? '—' }}</span>
            </div>
        </div>

        <div class="envio-lista-card__body">
            <div class="envio-lista-card__identidad">
                <a href="{{ $item['ver_url'] }}" class="envio-lista-card__codigo">{{ $item['codigo'] }}</a>
                <div class="envio-lista-card__producto">
                    <span class="envio-lista-card__producto-nombre">{{ $item['producto_label'] }}</span>
                    @if($item['producto_extra'])
                        <span class="envio-lista-card__producto-extra">{{ $item['producto_extra'] }}</span>
                    @endif
                </div>
            </div>

            @if($tieneTrayecto)
                @include('logistica.envios.partials.envio-lista-trayecto', ['trayectoPartes' => $item['trayecto_partes']])
            @endif

            <div class="envio-lista-card__metricas @if($tieneTrayecto) envio-lista-card__metricas--sin-destino @endif">
                <div class="envio-lista-card__metrica">
                    <span class="envio-lista-card__metrica-label">Total</span>
                    <span class="envio-lista-card__metrica-valor">
                        @if($item['total_kg'])
                            {{ number_format($item['total_kg'], 2) }} <small>kg</small>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="envio-lista-card__metrica">
                    <span class="envio-lista-card__metrica-label">Costo servicio</span>
                    <span class="envio-lista-card__metrica-valor">
                        @if($item['costo_bs'] !== null)
                            {{ number_format($item['costo_bs'], 2, ',', '.') }} <small>Bs</small>
                        @else
                            —
                        @endif
                    </span>
                </div>
                @unless($tieneTrayecto)
                <div class="envio-lista-card__metrica">
                    <span class="envio-lista-card__metrica-label">Destino</span>
                    <span class="envio-lista-card__metrica-valor">{{ $item['destino_label'] ?: '—' }}</span>
                </div>
                @endunless
            </div>

            @if(!empty($item['carga_resumen']))
                <p class="envio-lista-card__carga-resumen" title="Detalle de la carga">
                    <i class="fas fa-box-open"></i> {{ $item['carga_resumen'] }}
                </p>
            @endif
        </div>

        <div class="envio-lista-card__foot">
            <div class="envio-lista-card__operador">
                <span class="envio-lista-card__operador-item">
                    <i class="fas fa-user-tie"></i>
                    @if($item['chofer_nombre'])
                        {{ $item['chofer_nombre'] }}
                    @elseif($item['puede_asignar'])
                        @can('pedidos.update')
                        <button type="button" class="btn btn-link btn-sm p-0 envio-lista-card__asignar btn-asignar-transportista"
                            data-pedido-id="{{ $item['pedido']->pedidoid }}"
                            data-pedido-label="{{ $item['pedido']->numero_solicitud }}">
                            Asignar chofer
                        </button>
                        @else
                        <span class="text-muted">Sin asignar</span>
                        @endcan
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </span>
                <span class="envio-lista-card__operador-item">
                    <i class="fas fa-truck"></i>
                    {{ $item['vehiculo_placa'] ?? '—' }}
                </span>
            </div>
            <div class="envio-lista-card__acciones">
                @include('logistica.envios.partials.envio-lista-acciones', ['item' => $item])
            </div>
        </div>
    </article>
</div>
