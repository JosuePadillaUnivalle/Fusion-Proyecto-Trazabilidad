@php
    $esCierre = \App\Support\ProcesoPlantaCatalogo::esCierreTransformacion($paso->proceso?->nombre);
    $maqMantenimiento = $paso->maquina && ! $paso->maquina->activo;
@endphp
<div class="paso-det-card {{ $esCierre ? 'paso-det-card--cierre' : '' }}">
    <div class="paso-det-card__head">
        <span class="badge {{ $esUltimo ? 'badge-info' : 'badge-success' }}">Paso {{ $paso->orden }}</span>
        @if($esCierre)<span class="badge badge-light border">Cierra transformación</span>@endif
        @if($maqMantenimiento)<span class="badge badge-warning">Máquina en mantenimiento</span>@endif
    </div>
    <div class="paso-det-card__body">
        <div class="paso-det-card__media">
            @if($paso->maquina?->imagenSrc())
                <img src="{{ $paso->maquina->imagenSrc() }}" alt="{{ $paso->maquina->nombre }}">
            @else
                <div class="placeholder"><i class="fas fa-industry"></i>Sin imagen</div>
            @endif
        </div>
        <div class="paso-det-card__fields">
            <div class="font-weight-bold text-success">{{ $paso->proceso?->nombre ?? '—' }}</div>
            <div class="small text-muted mt-1">
                <i class="fas fa-cogs mr-1"></i>
                @if($paso->maquina)
                    {{ $paso->maquina->nombre }}@if($paso->maquina->codigo) ({{ $paso->maquina->codigo }})@endif
                @else
                    Cualquiera compatible
                @endif
            </div>
            @if($paso->notas)
                <div class="small text-secondary mt-1">{{ $paso->notas }}</div>
            @endif
            @if($paso->variables->isNotEmpty())
            <div class="paso-det-vars">
                @foreach($paso->variables as $v)
                <span class="paso-det-var">
                    <strong>{{ $v->variableEstandar?->nombre }}</strong>
                    {{ number_format($v->valor_minimo, 1) }}–{{ number_format($v->valor_maximo, 1) }}
                    @if($v->variableEstandar?->unidad)<span class="text-muted">{{ $v->variableEstandar->unidad }}</span>@endif
                    <span class="text-muted">· obligatorio</span>
                </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
