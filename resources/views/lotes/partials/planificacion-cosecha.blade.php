<style>
    .plan-modo-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 1rem;
    }
    @media (max-width: 576px) {
        .plan-modo-grid { grid-template-columns: 1fr; }
    }
    .plan-modo-card {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        margin: 0;
        padding: 10px 12px;
        border: 2px solid #d1e7dd;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .plan-modo-card input { position: absolute; opacity: 0; pointer-events: none; }
    .plan-modo-card__icon {
        font-size: 1.1rem;
        color: #6b7280;
        margin-bottom: 2px;
    }
    .plan-modo-card__title {
        font-size: .82rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.25;
    }
    .plan-modo-card__desc {
        font-size: .72rem;
        color: #6b7280;
        line-height: 1.3;
    }
    .plan-modo-card:hover {
        border-color: #10b981;
        background: #f0fdf4;
    }
    .plan-modo-card.active {
        border-color: #10b981;
        background: #ecfdf5;
        box-shadow: 0 0 0 1px rgba(16, 185, 129, .25);
    }
    .plan-modo-card.active .plan-modo-card__icon,
    .plan-modo-card.active .plan-modo-card__title { color: #059669; }
    .plan-resultado-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 8px;
    }
    @media (max-width: 576px) {
        .plan-resultado-grid { grid-template-columns: 1fr; }
    }
    .plan-resultado-item {
        background: rgba(255,255,255,.7);
        border-radius: 8px;
        padding: 8px 10px;
        text-align: center;
    }
    .plan-resultado-item strong {
        display: block;
        font-size: 1rem;
        color: #065f46;
    }
    .plan-resultado-item span {
        font-size: .7rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .plan-superficie-calculada input {
        background: #ecfdf5 !important;
        border-color: #10b981;
    }
    .plan-superficie-calculada .campo-guia::before {
        content: 'Calculado automáticamente · ';
        color: #059669;
        font-weight: 600;
    }
</style>

<div id="planificacionCosechaPanel" class="d-none border rounded p-3 mb-3 bg-light">
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap:8px;">
        <strong class="text-success"><i class="fas fa-chart-line mr-1"></i> ¿Cómo quiere planificar?</strong>
        <small class="text-muted">Estimaciones según rendimiento y calibre</small>
    </div>

    <p class="small text-muted mb-2 mb-md-3">Elija una opción. Usted ingresa un dato y el sistema calcula el resto (hectáreas, semilla y cosecha).</p>

    <div class="plan-modo-grid" id="planModoGroup" role="radiogroup" aria-label="Modo de planificación">
        <label class="plan-modo-card active">
            <input type="radio" name="plan_modo_ui" value="hectareas" checked>
            <i class="fas fa-ruler-combined plan-modo-card__icon"></i>
            <span class="plan-modo-card__title">Ya sé las hectáreas</span>
            <span class="plan-modo-card__desc">Ingreso ha → estimo cosecha y semilla</span>
        </label>
        <label class="plan-modo-card">
            <input type="radio" name="plan_modo_ui" value="unidades">
            <i class="fas fa-carrot plan-modo-card__icon"></i>
            <span class="plan-modo-card__title">Quiero X unidades</span>
            <span class="plan-modo-card__desc">Ej. 50.000 cebollas → calculo ha y semilla</span>
        </label>
        <label class="plan-modo-card">
            <input type="radio" name="plan_modo_ui" value="empaques">
            <i class="fas fa-box plan-modo-card__icon"></i>
            <span class="plan-modo-card__title">Quiero X cajas</span>
            <span class="plan-modo-card__desc">Ej. 1.000 cajas → calculo ha y semilla</span>
        </label>
    </div>

    <input type="hidden" name="catalogotamanoconteoid" id="catalogotamanoconteoid"
           value="{{ old('catalogotamanoconteoid', $catalogoTamanoConteoId ?? '') }}">

    <div class="form-group mb-2">
        <label class="small font-weight-bold mb-1" for="planCalibreSelect">
            <i class="fas fa-balance-scale mr-1"></i> Tamaño al cosechar (pequeño / mediano / grande)
        </label>
        <select id="planCalibreSelect" class="form-control form-control-sm">
            <option value="">Cargando calibres…</option>
        </select>
        <p class="campo-guia mb-0">Elija el calibre de la verdura. Define cuántas unidades caben por caja y el peso de cada una.</p>
        <p id="planCalibreHint" class="campo-guia mb-0 text-success d-none"></p>
    </div>

    <div id="planHectareasHint" class="alert alert-light border small mb-2 py-2 px-3">
        <i class="fas fa-arrow-down text-success mr-1"></i>
        Ingrese las <strong>hectáreas</strong> en el campo <strong>«Superficie»</strong> que aparece debajo. Nosotros calculamos cosecha y semilla.
    </div>

    <div id="planObjetivoUnidadesWrap" class="form-group mb-2 d-none">
        <label class="small font-weight-bold mb-1" for="planObjetivoUnidades">
            <i class="fas fa-carrot mr-1"></i> ¿Cuántas unidades quiere cosechar?
        </label>
        <input type="number" step="1" min="1" id="planObjetivoUnidades" class="form-control"
               placeholder="Ej. 50000">
        <p class="campo-guia mb-0">Calculamos automáticamente las <strong>hectáreas</strong> y la <strong>semilla</strong> necesarias.</p>
    </div>

    <div id="planObjetivoEmpaquesWrap" class="form-group mb-2 d-none">
        <label class="small font-weight-bold mb-1" for="planObjetivoEmpaques">
            <i class="fas fa-box mr-1"></i> ¿Cuántas cajas quiere obtener?
        </label>
        <input type="number" step="1" min="1" id="planObjetivoEmpaques" class="form-control"
               placeholder="Ej. 1500">
        <p class="campo-guia mb-0">Según el calibre elegido. Calculamos <strong>hectáreas</strong> y <strong>semilla</strong>.</p>
    </div>

    <div id="planCosechaPreview" class="alert alert-success small mb-2 d-none py-2 px-3">
        <div class="font-weight-bold mb-2"><i class="fas fa-seedling mr-1"></i> Resultado estimado</div>
        <div class="plan-resultado-grid" id="planResultadoGrid"></div>
        <div id="planCosechaTexto" class="text-muted"></div>
    </div>

    @php
        $cantidadSemillaPlanificada = $cantidadSemillaPlanificada ?? old('cantidad_semilla_planificada', '');
        $cantidadSemillaUnidad = $cantidadSemillaUnidad ?? old('cantidad_semilla_unidad', 'kg');
    @endphp
    <div id="cantidadSemillaWrap" class="d-none mt-2 mb-2 border-top pt-3">
        <label class="small font-weight-bold mb-1 d-block" for="cantidad_semilla_planificada">
            <i class="fas fa-calculator mr-1"></i> Cantidad de semilla estimada
        </label>
        <div class="input-group">
            <input type="number"
                   step="0.001"
                   min="0"
                   name="cantidad_semilla_planificada"
                   id="cantidad_semilla_planificada"
                   class="form-control"
                   value="{{ $cantidadSemillaPlanificada !== '' && $cantidadSemillaPlanificada !== null ? $cantidadSemillaPlanificada : '' }}"
                   placeholder="Se calcula automáticamente">
            <div class="input-group-append">
                <span class="input-group-text" id="cantidadSemillaUnidad">{{ $cantidadSemillaUnidad }}</span>
            </div>
        </div>
        <p class="campo-guia mb-1">
            Calculada según dosis por hectárea. Puede ajustarla si sembrará más o menos.
        </p>
        <div id="dosisSiembraPreview" class="alert alert-light border small mb-0 d-none">
            <i class="fas fa-info-circle text-success mr-1"></i>
            <span id="dosisSiembraTexto"></span>
        </div>
    </div>

    <div id="planErrorPreview" class="alert alert-warning small mb-2 d-none py-2 px-3">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <span id="planErrorTexto"></span>
    </div>

    <p class="campo-guia mb-0 text-muted">
        <i class="fas fa-info-circle mr-1"></i>
        Son valores de referencia; el rendimiento real depende del clima, manejo y suelo.
    </p>
</div>
