@php
    $parametrosRequeridos = $parametrosRequeridos ?? [];
@endphp

@if(!empty($parametrosRequeridos))
<div class="tp-parametros-etapa mb-3">
    <div class="small font-weight-bold text-success mb-2">
        <i class="fas fa-sliders-h mr-1"></i>Parámetros de la línea
    </div>
    <p class="small text-muted mb-2">Definidos por el jefe de planta en la línea de procesos. No se pueden modificar.</p>
    <div class="d-flex flex-wrap" style="gap:.35rem">
        @foreach($parametrosRequeridos as $p)
            <span class="badge badge-light border px-2 py-1">
                {{ $p['nombre'] }}@if(!empty($p['unidad'])) ({{ $p['unidad'] }})@endif:
                <strong>{{ number_format($p['valor_minimo'], 1) }}–{{ number_format($p['valor_maximo'], 1) }}</strong>
            </span>
        @endforeach
    </div>
</div>
@endif
