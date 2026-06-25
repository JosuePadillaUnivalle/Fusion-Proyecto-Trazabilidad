@php
    $superficieValor = $superficieValor ?? old('superficie', '');
    $cantidadSemillaPlanificada = $cantidadSemillaPlanificada ?? old('cantidad_semilla_planificada', '');
    $cantidadSemillaUnidad = $cantidadSemillaUnidad ?? old('cantidad_semilla_unidad', 'kg');
@endphp

<style>
    .plan-modo-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: .75rem;
    }
    @media (max-width: 576px) {
        .plan-modo-grid { grid-template-columns: 1fr; }
    }
    .plan-modo-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin: 0;
        padding: 10px 8px;
        border: 2px solid #d1e7dd;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        text-align: center;
        min-height: 64px;
        transition: border-color .15s, background .15s;
    }
    .plan-modo-card input { position: absolute; opacity: 0; pointer-events: none; }
    .plan-modo-card__icon { font-size: 1rem; color: #6b7280; }
    .plan-modo-card__title { font-size: .78rem; font-weight: 700; color: #1f2937; line-height: 1.2; }
    .plan-modo-card:hover { border-color: #10b981; background: #f0fdf4; }
    .plan-modo-card.active {
        border-color: #10b981;
        background: #ecfdf5;
        box-shadow: 0 0 0 1px rgba(16, 185, 129, .25);
    }
    .plan-modo-card.active .plan-modo-card__icon,
    .plan-modo-card.active .plan-modo-card__title { color: #059669; }
    .plan-modo-card.opacity-50 { opacity: 0.55; }
    .plan-resultado-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
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
    .plan-resultado-item strong { display: block; font-size: 1rem; color: #065f46; }
    .plan-resultado-item span {
        font-size: .68rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .plan-superficie-calculada input {
        background: #ecfdf5 !important;
        border-color: #10b981;
    }
</style>

<div id="planificacionCosechaPanel" class="d-none border rounded p-3 mb-3 bg-light">
    <strong class="text-success d-block mb-2">
        <i class="fas fa-chart-line mr-1"></i> ¿Cómo quiere planificar?
    </strong>

    <div class="plan-modo-grid" id="planModoGroup" role="radiogroup" aria-label="Modo de planificación">
        <label class="plan-modo-card active">
            <input type="radio" name="plan_modo_ui" value="hectareas" checked>
            <i class="fas fa-ruler-combined plan-modo-card__icon"></i>
            <span class="plan-modo-card__title">Por hectáreas</span>
        </label>
        <label class="plan-modo-card">
            <input type="radio" name="plan_modo_ui" value="unidades">
            <i class="fas fa-carrot plan-modo-card__icon"></i>
            <span class="plan-modo-card__title">Por unidades</span>
        </label>
        <label class="plan-modo-card">
            <input type="radio" name="plan_modo_ui" value="empaques">
            <i class="fas fa-box plan-modo-card__icon"></i>
            <span class="plan-modo-card__title">Por cajas</span>
        </label>
    </div>

    <input type="hidden" name="catalogotamanoconteoid" id="catalogotamanoconteoid"
           value="{{ old('catalogotamanoconteoid', $catalogoTamanoConteoId ?? '') }}">
    <input type="hidden" name="plan_modo" id="planModoHidden" value="{{ old('plan_modo', 'hectareas') }}">
    <input type="hidden" name="plan_objetivo_empaques" id="planObjetivoEmpaquesHidden" value="{{ old('plan_objetivo_empaques', '') }}">
    <input type="hidden" name="plan_objetivo_unidades" id="planObjetivoUnidadesHidden" value="{{ old('plan_objetivo_unidades', '') }}">

    <div class="form-group mb-2">
        <label class="small font-weight-bold mb-1" for="planCalibreSelect">Calibre al cosechar</label>
        <select id="planCalibreSelect" class="form-control form-control-sm">
            <option value="">Cargando…</option>
        </select>
        <p id="planCalibreHint" class="campo-guia mb-0 text-success d-none small"></p>
    </div>

    <div id="planObjetivoUnidadesWrap" class="form-group mb-2 d-none">
        <label class="small font-weight-bold mb-1" for="planObjetivoUnidades">Unidades a cosechar</label>
        <input type="text" inputmode="numeric" id="planObjetivoUnidades" class="form-control" placeholder="Ej. 50000" autocomplete="off">
    </div>

    <div id="planObjetivoEmpaquesWrap" class="form-group mb-2 d-none">
        <label class="small font-weight-bold mb-1" for="planObjetivoEmpaques">Cajas a obtener</label>
        <input type="text" inputmode="numeric" id="planObjetivoEmpaques" class="form-control" placeholder="Ej. 1500" autocomplete="off">
    </div>

    <div id="superficieWrap" class="form-group mb-2">
        <label class="small font-weight-bold mb-1" for="superficie">
            <i class="fas fa-ruler-combined mr-1"></i> Superficie (hectáreas) <span class="text-danger">*</span>
        </label>
        <input type="text" inputmode="decimal" name="superficie" id="superficie" class="form-control" required
               placeholder="Ej. 5" value="{{ $superficieValor }}" autocomplete="off">
        <p class="campo-guia mb-0 small">Se refleja en el círculo del mapa.</p>
    </div>

    <div id="planCosechaPreview" class="alert alert-success small mb-2 d-none py-2 px-3">
        <div class="font-weight-bold mb-1"><i class="fas fa-seedling mr-1"></i> Estimado</div>
        <div class="plan-resultado-grid" id="planResultadoGrid"></div>
    </div>

    <div id="cantidadSemillaWrap" class="form-group mb-2 d-none">
        <label class="small font-weight-bold mb-1" for="cantidad_semilla_planificada">
            Kg a utilizar <span id="planInsumoUsoNombre" class="font-weight-normal text-muted"></span>
        </label>
        <div class="input-group input-group-sm">
            <input type="text" inputmode="decimal" name="cantidad_semilla_planificada"
                   id="cantidad_semilla_planificada" class="form-control"
                   value="{{ $cantidadSemillaPlanificada !== '' && $cantidadSemillaPlanificada !== null ? $cantidadSemillaPlanificada : '' }}"
                   placeholder="Automático" autocomplete="off">
            <div class="input-group-append">
                <span class="input-group-text" id="cantidadSemillaUnidad">{{ $cantidadSemillaUnidad }}</span>
            </div>
        </div>
        <div id="dosisSiembraPreview" class="small text-muted mt-1 d-none">
            <span id="dosisSiembraTexto"></span>
        </div>
    </div>

    <div id="planLimiteAviso" class="alert alert-info small mb-2 d-none py-2 px-3">
        <i class="fas fa-info-circle mr-1"></i>
        <span id="planLimiteTexto"></span>
    </div>

    <div id="planErrorPreview" class="alert alert-warning small mb-0 d-none py-2 px-3">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <span id="planErrorTexto"></span>
    </div>
</div>
