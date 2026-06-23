@php
    $parametrosRequeridos = $parametrosRequeridos ?? [];
    $prefix = $prefix ?? 'parametros';
    $inputIdPrefix = $inputIdPrefix ?? 'param';
@endphp

@if(!empty($parametrosRequeridos))
<div class="tp-parametros-etapa mb-3">
    <div class="small font-weight-bold text-success mb-2">
        <i class="fas fa-sliders-h mr-1"></i>Parámetros obligatorios
    </div>
    <p class="small text-muted mb-2">Indique el <strong>valor exacto</strong> que usará dentro del rango. No se puede guardar si está fuera de límites.</p>
    @foreach($parametrosRequeridos as $i => $p)
        <div class="form-group mb-2">
            <label class="small font-weight-bold mb-1" for="{{ $inputIdPrefix }}-{{ $p['variableestandarid'] }}">
                {{ $p['nombre'] }}@if(!empty($p['unidad'])) <span class="text-muted font-weight-normal">({{ $p['unidad'] }})</span>@endif
            </label>
            <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                <input type="number"
                       step="0.01"
                       class="form-control form-control-sm"
                       style="max-width:140px"
                       id="{{ $inputIdPrefix }}-{{ $p['variableestandarid'] }}"
                       name="{{ $prefix }}[{{ $i }}][valor]"
                       min="{{ $p['valor_minimo'] }}"
                       max="{{ $p['valor_maximo'] }}"
                       value="{{ old($prefix.'.'.$i.'.valor') }}"
                       required
                       placeholder="Valor real">
                <input type="hidden" name="{{ $prefix }}[{{ $i }}][variableestandarid]" value="{{ $p['variableestandarid'] }}">
                <span class="small text-muted">
                    Rango: <strong>{{ number_format($p['valor_minimo'], 1) }}–{{ number_format($p['valor_maximo'], 1) }}</strong>
                    @if(!empty($p['maq_maximo']) && ($p['maq_maximo'] != $p['valor_maximo'] || $p['maq_minimo'] != $p['valor_minimo']))
                        <span class="d-block" style="font-size:.7rem">Máquina: {{ number_format($p['maq_minimo'], 1) }}–{{ number_format($p['maq_maximo'], 1) }}</span>
                    @endif
                </span>
            </div>
        </div>
    @endforeach
</div>
@endif
