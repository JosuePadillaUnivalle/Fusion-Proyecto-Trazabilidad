@php
    $filtros = $filtros ?? [];
    $cultivos = $cultivos ?? collect();
    $estados = $estados ?? collect();
    $responsables = $responsables ?? collect();
    $fases = $fases ?? \App\Support\LoteTrazabilidadService::FASES;
    $action = $action ?? request()->url();
    $anchor = $anchor ?? '';
    $actionUrl = $action.$anchor;
    $limpiarUrl = $action.'?filtros_abiertos=1'.$anchor;
    $tipos = [
        '' => 'Todos los tipos',
        'siembra' => 'Siembra',
        'estado' => 'Cambio de estado',
        'insumo' => 'Insumo',
        'actividad' => 'Actividad',
        'cosecha' => 'Cosecha',
        'almacenamiento' => 'Almacenamiento',
        'certificacion' => 'Certificación',
        'venta' => 'Venta',
    ];
@endphp
<form method="GET" action="{{ $actionUrl }}" class="filtros-trz">
    <div class="row align-items-end">
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Fase del producto</label>
            <select name="fase" class="form-control form-control-sm">
                <option value="">Todas las fases</option>
                @foreach($fases as $key => $meta)
                    <option value="{{ $key }}" @selected(($filtros['fase'] ?? '') === $key)>{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Tipo de evento</label>
            <select name="tipo" class="form-control form-control-sm">
                @foreach($tipos as $val => $label)
                    <option value="{{ $val }}" @selected(($filtros['tipo'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if($cultivos->isNotEmpty())
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Cultivo</label>
            <select name="cultivoid" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach($cultivos as $c)
                    <option value="{{ $c->cultivoid }}" @selected(($filtros['cultivoid'] ?? null) == $c->cultivoid)>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if(isset($estados) && $estados->isNotEmpty())
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Estado del lote</label>
            <select name="estadolotetipoid" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach($estados as $e)
                    <option value="{{ $e->estadolotetipoid }}" @selected(($filtros['estadolotetipoid'] ?? null) == $e->estadolotetipoid)>{{ ucfirst($e->nombre) }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if($responsables->isNotEmpty())
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Responsable</label>
            <select name="usuarioid" class="form-control form-control-sm">
                <option value="">Todos</option>
                @foreach($responsables as $u)
                    <option value="{{ $u->usuarioid }}" @selected(($filtros['usuarioid'] ?? null) == $u->usuarioid)>{{ $u->nombre }} {{ $u->apellido }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Desde</label>
            <input type="date" name="desde" class="form-control form-control-sm" value="{{ $filtros['desde'] ?? '' }}">
        </div>
        <div class="col-lg-2 col-md-4 mb-2">
            <label class="small text-muted mb-1">Hasta</label>
            <input type="date" name="hasta" class="form-control form-control-sm" value="{{ $filtros['hasta'] ?? '' }}">
        </div>
        @if(!isset($hideBuscar))
        <div class="col-lg-3 col-md-6 mb-2">
            <label class="small text-muted mb-1">Buscar lote</label>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Nombre o código…" value="{{ $filtros['q'] ?? '' }}">
        </div>
        @endif
        <div class="col-lg-auto col-md-6 mb-2">
            <button type="submit" class="btn btn-success btn-sm mr-1"><i class="fas fa-filter mr-1"></i> Filtrar</button>
            <a href="{{ $limpiarUrl }}" class="btn btn-outline-secondary btn-sm filtros-limpiar">Limpiar</a>
        </div>
    </div>
</form>
