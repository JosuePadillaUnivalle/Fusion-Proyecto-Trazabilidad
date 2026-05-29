@php
    $registro = $registro ?? null;
    $mostrarGuias = $mostrarGuias ?? false;
@endphp

@if($mostrarGuias)
<div class="guia-campo mb-4">
    <strong>Registro de trazabilidad.</strong> Documenta un cambio de estado en un lote: qué lote cambió,
    a qué estado pasó, cuándo y quién lo registró.
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="loteid">Lote <span class="text-danger">*</span></label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2"><strong>Parcela afectada.</strong> Selecciona el lote que cambió de estado.</div>
            @endif
            <select name="loteid" id="loteid" class="form-control @error('loteid') is-invalid @enderror" required>
                <option value="">Seleccione un lote…</option>
                @foreach($lotes as $lote)
                    <option value="{{ $lote->loteid }}" @selected(old('loteid', $registro->loteid ?? '') == $lote->loteid)>
                        {{ $lote->nombre }}
                    </option>
                @endforeach
            </select>
            @error('loteid')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="estadolotetipoid">Estado del lote <span class="text-danger">*</span></label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2"><strong>Nuevo estado.</strong> Tipo de estado al que pasó el lote.</div>
            @endif
            <select name="estadolotetipoid" id="estadolotetipoid" class="form-control @error('estadolotetipoid') is-invalid @enderror" required>
                <option value="">Seleccione un estado…</option>
                @foreach($tiposEstado as $tipo)
                    <option value="{{ $tipo->estadolotetipoid }}" @selected(old('estadolotetipoid', $registro->estadolotetipoid ?? '') == $tipo->estadolotetipoid)>
                        {{ $tipo->nombre }}
                    </option>
                @endforeach
            </select>
            @error('estadolotetipoid')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="usuarioid">Usuario responsable</label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2"><strong>Opcional.</strong> Quién registró o ejecutó el cambio.</div>
            @endif
            <select name="usuarioid" id="usuarioid" class="form-control @error('usuarioid') is-invalid @enderror">
                <option value="">Sin usuario asignado</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->usuarioid }}" @selected(old('usuarioid', $registro->usuarioid ?? '') == $usuario->usuarioid)>
                        {{ $usuario->nombre }} {{ $usuario->apellido }}
                    </option>
                @endforeach
            </select>
            @error('usuarioid')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="fecha_cambio">Fecha del cambio</label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2"><strong>Cuándo ocurrió.</strong> Si se deja vacío, se usa la fecha actual al guardar.</div>
            @endif
            <input type="datetime-local" name="fecha_cambio" id="fecha_cambio"
                class="form-control @error('fecha_cambio') is-invalid @enderror"
                value="{{ old('fecha_cambio', isset($registro->fecha_cambio) && $registro->fecha_cambio ? $registro->fecha_cambio->format('Y-m-d\TH:i') : '') }}">
            @error('fecha_cambio')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="observaciones">Observaciones</label>
    @if($mostrarGuias)
    <div class="guia-campo mb-2"><strong>Contexto.</strong> Motivo del cambio, condiciones del lote o notas operativas.</div>
    @endif
    <textarea name="observaciones" id="observaciones" rows="3"
        class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $registro->observaciones ?? '') }}</textarea>
    @error('observaciones')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group mb-0">
    <label for="imagenurl">URL de imagen <small class="text-muted">(opcional)</small></label>
    <input type="url" name="imagenurl" id="imagenurl" maxlength="250"
        class="form-control @error('imagenurl') is-invalid @enderror"
        value="{{ old('imagenurl', $registro->imagenurl ?? '') }}" placeholder="https://…">
    @error('imagenurl')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
